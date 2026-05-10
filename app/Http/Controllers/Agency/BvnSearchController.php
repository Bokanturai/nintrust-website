<?php

namespace App\Http\Controllers\Agency;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;

class BvnSearchController extends Controller
{
    /**
     * Display the service form and submission history for BVN Search.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Search for the specific BVN Search service
        $bvnService = Service::where('service_code', '045')->first();

        // Query only this user's submissions for BVN Search
        $query = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_type', 'BVN_SEARCH')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('phone_number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(10);
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('bvn.search', [
            'submissions' => $submissions,
            'wallet'      => $wallet,
            'bvnService'  => $bvnService,
            'title'       => 'BVN Search API'
        ]);
    }

    /**
     * Handle the BVN Search submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone_number' => 'required|string|digits_between:10,11',
        ]);

        // Find the service
        $service = Service::where('service_code', '045')
            ->where('status', 'enabled')
            ->first();

        if (!$service) {
            return back()->with(['status' => 'error', 'message' => 'BVN Search Service is currently unavailable.'])->withInput();
        }

        $servicePrice = $service->amount;

        // Check wallet
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $servicePrice) {
            return back()->with(['status' => 'error', 'message' => 'Insufficient wallet balance.'])->withInput();
        }

        $reference = 'PHN-' . strtoupper(bin2hex(random_bytes(6)));

        DB::beginTransaction();
        try {
            // 1. Deduct from wallet
            $wallet->update(['balance' => $wallet->balance - $servicePrice]);

            // 2. Create Transaction record
            $transaction = Transaction::create([
                'user_id'             => $user->id,
                'referenceId'         => $reference,
                'amount'              => $servicePrice,
                'type'                => 'Debit',
                'service_type'        => 'BVN_SEARCH',
                'service_description' => "BVN Search Request (Phone: {$request->phone_number})",
                'status'              => 'Approved',
                'performed_by'        => $user->id,
                'metadata'            => [
                    'phone_number' => $request->phone_number,
                    'service'      => 'BVN_SEARCH'
                ]
            ]);

            // 3. Create Agent Service record
            $agentService = AgentService::create([
                'user_id'         => $user->id,
                'service_id'      => $service->id,
                'transaction_id'  => $transaction->id,
                'reference'       => $reference,
                'phone_number'    => $request->phone_number,
                'status'          => 'processing',
                'amount'          => $servicePrice,
                'submission_date' => now(),
                'service_type'    => 'BVN_SEARCH',
                'service_name'    => $service->name,
                'field_code'      => '45',
            ]);

            // 4. Call Arewa API
            $apiToken = env('AREWA_TOKEN');
            $apiUrl   = env('AREWA_URL') . '/bvn/phone-search';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept'        => 'application/json',
            ])->withoutVerifying()->post($apiUrl, [
                'field_code'   => '45',
                'phone_number' => $request->phone_number,
                'reference'    => $reference,
            ]);

            if ($response->successful()) {
                $apiData = $response->json();
                
                // Extract API reference and identifiers from multiple possible locations
                $apiRef = $apiData['reference'] ?? ($apiData['data']['reference'] ?? ($apiData['tracking_id'] ?? null));
                $ticketId = $apiData['ticket_id'] ?? ($apiData['data']['ticket_id'] ?? null);
                $batchId = $apiData['batch_id'] ?? ($apiData['data']['batch_id'] ?? null);

                $agentService->update([
                    'tracking_id' => $apiRef,
                    'ticket_id'   => $ticketId,
                    'batch_id'    => $batchId,
                    'status'      => $this->normalizeStatus($apiData['status'] ?? ($apiData['data']['status'] ?? 'processing')),
                    'comment'     => $apiData['comment'] ?? ($apiData['message'] ?? ($apiData['data']['comment'] ?? $agentService->comment)),
                ]);
            } else {
                Log::error('Arewa API Error (BVN Search)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'ref'    => $reference
                ]);
            }

            DB::commit();

            return redirect()->route('user.verify-bvn2')->with([
                'status'  => 'success',
                'message' => 'BVN Search request submitted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BVN Search Submission Error: ' . $e->getMessage());
            return back()->with(['status' => 'error', 'message' => 'An error occurred during submission.'])->withInput();
        }
    }

    /**
     * Check status of a BVN Search request.
     */
    public function checkStatus(Request $request, $id)
    {
        $user = Auth::user();
        $submission = AgentService::findOrFail($id);
        
        // Security check
        if ($submission->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        if (in_array($submission->status, ['successful', 'failed', 'resolved', 'rejected'])) {
            return response()->json(['status' => 'info', 'message' => 'Request is already in a final state: ' . ucfirst($submission->status)]);
        }

        $apiToken = env('AREWA_TOKEN');
        $apiUrl   = env('AREWA_URL') . '/bvn/phone-search';

        try {
            // Using phone_number as requested for status check
            $query = [
                'phone_number' => $submission->phone_number,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept'        => 'application/json',
            ])->withoutVerifying()->get($apiUrl, $query);

            if ($response->status() === 404) {
                $apiData = $response->json();
                return response()->json(['status' => 'error', 'message' => $apiData['message'] ?? 'Record not found in API.'], 404);
            }

            if ($response->successful()) {
                $apiData = $response->json();
                
                // Extract status and comment using the same nested patterns as CRM
                $apiStatus = $apiData['status'] ?? ($apiData['data']['status'] ?? null);
                $apiComment = $apiData['comment'] ?? ($apiData['message'] ?? ($apiData['data']['comment'] ?? null));
                $apiFile = $apiData['file_url'] ?? ($apiData['data']['file_url'] ?? null);
                $apiRef = $apiData['reference'] ?? ($apiData['data']['reference'] ?? ($apiData['tracking_id'] ?? null));

                if (!$apiStatus && isset($apiData['success']) && $apiData['success'] === false) {
                    return response()->json(['status' => 'error', 'message' => $apiData['message'] ?? 'API reports request not found.'], 404);
                }

                $newStatus = $this->normalizeStatus($apiStatus ?? $submission->status);
                
                DB::beginTransaction();
                
                $submission->update([
                    'status'      => $newStatus,
                    'comment'     => $apiComment ?? $submission->comment,
                    'file_url'    => $apiFile ?? $submission->file_url,
                    'tracking_id' => $apiRef ?? $submission->tracking_id,
                ]);

                if (in_array($newStatus, ['failed', 'rejected'])) {
                    $this->processRefund($submission);
                }

                DB::commit();

                return response()->json(['status' => 'success', 'message' => 'Status updated: ' . ucfirst($newStatus)]);
            }

            Log::error('BVN Search Status Check Failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'ref'    => $submission->reference
            ]);

            return response()->json(['status' => 'error', 'message' => 'API Error (' . $response->status() . '). Please contact support.'], 400);

        } catch (\Exception $e) {
            Log::error('BVN Search Status Check Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal error checking status.'], 500);
        }
    }

    /**
     * Admin view for all BVN Search requests.
     */
    /**
     * Admin update for status/comment.
     */
    public function adminUpdate(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|string',
            'comment'     => 'nullable|string',
            'result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,zip|max:5120',
        ]);

        $submission = AgentService::findOrFail($id);
        
        $oldStatus = $submission->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            $data = [
                'status'  => $newStatus,
                'comment' => $request->comment ?? $submission->comment,
            ];

            // Handle file upload
            if ($request->hasFile('result_file')) {
                $file = $request->file('result_file');
                $path = $file->store('bvn_results', 'public');
                $data['file_url'] = $path;
            }

            $submission->update($data);

            // Handle refund if moving to failed/rejected from a non-failed state
            if (in_array($newStatus, ['failed', 'rejected']) && !in_array($oldStatus, ['failed', 'rejected'])) {
                $this->processRefund($submission);
            }

            DB::commit();
            return back()->with('success', 'Request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin BVN Search Update Error: ' . $e->getMessage());
            return back()->with('error', 'Error updating request.');
        }
    }

    public function adminIndex(Request $request)
    {
        $query = AgentService::with('user', 'transaction')
            ->where('service_type', 'BVN_SEARCH')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('phone_number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('ticket_id', 'like', "%{$s}%")
                  ->orWhere('batch_id', 'like', "%{$s}%")
                  ->orWhereHas('user', function($u) use ($s) {
                      $u->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(10);

        return view('admin.bvn-search.index', [
            'submissions' => $submissions,
            'title'       => 'Admin - BVN Search Requests'
        ]);
    }

    private function normalizeStatus($status)
    {
        $status = strtolower($status);
        return match($status) {
            'success', 'successful', 'resolved' => 'successful',
            'fail', 'failed', 'rejected'       => 'failed',
            'query', 'remark'                  => 'query',
            default                            => 'processing'
        };
    }

    private function processRefund($submission)
    {
        $existingRefund = Transaction::where('user_id', $submission->user_id)
            ->where('service_description', 'like', "%Refund%{$submission->reference}%")
            ->exists();

        if ($existingRefund) return;

        $wallet = Wallet::where('user_id', $submission->user_id)->first();
        if ($wallet) {
            $refundAmount = $submission->amount;
            $wallet->update(['balance' => $wallet->balance + $refundAmount]);

            Transaction::create([
                'user_id'             => $submission->user_id,
                'referenceId'         => 'REF-' . strtoupper(bin2hex(random_bytes(6))),
                'amount'              => $refundAmount,
                'type'                => 'Credit',
                'service_type'        => 'BVN_SEARCH',
                'service_description' => "Refund for Failed BVN Search (Ref: {$submission->reference})",
                'status'              => 'Approved',
                'performed_by'        => 1,
                'metadata'            => ['original_ref' => $submission->reference]
            ]);
        }
    }
}
