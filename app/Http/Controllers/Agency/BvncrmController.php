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

class BvncrmController extends Controller
{
    /**
     * Display the service form and submission history for CRM.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Search for the specific CRM service
        $crmService = Service::where('service_code', '021')->first();

        // Query only this user's submissions
        $query = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('ticket_id', 'like', "%{$s}%")
                  ->orWhere('batch_id', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(10);
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('bvn.crm', [
            'submissions' => $submissions,
            'wallet'      => $wallet,
            'crmService'  => $crmService,
            'title'       => 'BVN CRM'
        ]);
    }

    /**
     * Handle the CRM submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'batch_id'  => 'required|string|size:7',
            'ticket_id' => 'required|string|size:8',
        ]);

        // Find the CRM service
        $service = Service::where('service_code', '021')
            ->where('status', 'enabled')
            ->first();

        if (!$service) {
            return back()->with(['status' => 'error', 'message' => 'CRM Service is currently unavailable.'])->withInput();
        }

        $servicePrice = $service->amount;

        // Check wallet
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $servicePrice) {
            return back()->with(['status' => 'error', 'message' => 'Insufficient wallet balance.'])->withInput();
        }

        $reference = 'CRM-' . strtoupper(bin2hex(random_bytes(6)));

        DB::beginTransaction();
        try {
            // 1. Deduct from wallet
            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance - $servicePrice;
            $wallet->update(['balance' => $newBalance]);

            // 2. Create Transaction record
            $transaction = Transaction::create([
                'user_id'             => $user->id,
                'referenceId'         => $reference,
                'transaction_ref'     => null,
                'amount'              => $servicePrice,
                'type'                => 'Debit',
                'service_type'        => 'CRM',
                'service_description' => "BVN CRM Request Fee (Ticket: {$request->ticket_id})",
                'status'              => 'Approved',
                'performed_by'        => $user->id,
                'metadata'            => [
                    'batch_id'  => $request->batch_id,
                    'ticket_id' => $request->ticket_id,
                    'service'   => 'CRM'
                ]
            ]);

            // 3. Create Agent Service record
            $agentService = AgentService::create([
                'user_id'         => $user->id,
                'service_id'      => $service->id,
                'transaction_id'  => $transaction->id,
                'reference'       => $reference,
                'batch_id'        => $request->batch_id,
                'ticket_id'       => $request->ticket_id,
                'status'          => 'processing',
                'amount'          => $servicePrice,
                'submission_date' => now(),
                'service_type'    => 'CRM',
                'service_name'    => $service->name,
                'field_code'      => '021',
                'phone_number'    => $user->phone_number,
            ]);

            // 4. Call Arewa API
            $apiToken = env('AREWA_TOKEN');
            $apiUrl   = env('AREWA_URL') . '/bvn/crm';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept'        => 'application/json',
            ])->withoutVerifying()->post($apiUrl, [
                'batch_id'  => $request->batch_id,
                'ticket_id' => $request->ticket_id,
                'field_code' => '021', // Using 021 as the default CRM field code
                'reference'  => $reference,
            ]);

            if ($response->successful()) {
                $apiData = $response->json();
                $apiRef  = $apiData['reference'] ?? null;

                $agentService->update([
                    'tracking_id' => $apiRef,
                    'status'      => $this->normalizeStatus($apiData['status'] ?? 'processing'),
                    'comment'     => $apiData['message'] ?? $agentService->comment,
                ]);
            } else {
                // If API fails immediately, we keep it as processing for manual check or handle error
                Log::error('Arewa API Error (CRM Submission)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'ref'    => $reference
                ]);
                
                // Optional: Auto-refund if API is definitely down/error
                // For now, we leave as processing to allow manual resolution or retry
            }

            DB::commit();

            return redirect()->route('user.bvn-crm')->with([
                'status'  => 'success',
                'message' => 'CRM request submitted successfully. Charged: ₦' . number_format($servicePrice, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CRM Submission Error: ' . $e->getMessage());
            return back()->with(['status' => 'error', 'message' => 'An error occurred during submission. Please try again.'])->withInput();
        }
    }

    /**
     * Check status of a CRM request.
     */
    public function checkStatus(Request $request, $id)
    {
        $user = Auth::user();
        $submission = AgentService::findOrFail($id);
        
        // Security: Ensure the user owns the request or is an admin
        if ($submission->user_id !== $user->id && $user->role !== 'admin') {
            $msg = 'Unauthorized access.';
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 403);
            }
            return back()->with(['status' => 'error', 'message' => $msg]);
        }
        
        if (in_array($submission->status, ['successful', 'failed', 'resolved', 'rejected'])) {
            $msg = 'Request is already in a final state: ' . ucfirst($submission->status);
            if ($request->wantsJson()) {
                return response()->json(['status' => 'info', 'message' => $msg]);
            }
            return back()->with(['status' => 'info', 'message' => $msg]);
        }

        $apiToken = env('AREWA_TOKEN');
        $apiUrl   = env('AREWA_URL') . '/bvn/crm';

        try {
            // Using Ticket ID and Batch ID for status check as requested
            $query = [
                'ticket_id' => $submission->ticket_id,
                'batch_id'  => $submission->batch_id,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept'        => 'application/json',
            ])->withoutVerifying()->get($apiUrl, $query);

            if ($response->successful()) {
                $apiData = $response->json();

                $newStatus = $this->normalizeStatus($apiData['status'] ?? ($apiData['data']['status'] ?? $submission->status));
                
                DB::beginTransaction();
                
                $submission->update([
                    'status'  => $newStatus,
                    'comment' => $apiData['comment'] ?? ($apiData['message'] ?? ($apiData['data']['comment'] ?? $submission->comment)),
                    'file_url'=> $apiData['file_url'] ?? ($apiData['data']['file_url'] ?? $submission->file_url),
                    'tracking_id' => $apiData['reference'] ?? ($apiData['tracking_id'] ?? $submission->tracking_id),
                ]);

                // Handle Auto-Refund if failed
                if ($newStatus === 'failed' || $newStatus === 'rejected') {
                    $this->processRefund($submission);
                }

                DB::commit();

                $message = 'Status updated: ' . ucfirst($newStatus);
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'success', 'message' => $message]);
                }
                return back()->with(['status' => 'success', 'message' => $message]);
            }

            Log::error('CRM Status Check Failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'url'    => $apiUrl,
                'query'  => $query
            ]);

            $message = 'Could not fetch status from API. Please try again later.';
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with(['status' => 'error', 'message' => $message]);

        } catch (\Exception $e) {
            Log::error('CRM Status Check Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $message = 'Error checking status.';
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 500);
            }
            return back()->with(['status' => 'error', 'message' => $message]);
        }
    }

    /**
     * Admin view for all CRM requests.
     */
    public function adminIndex(Request $request)
    {
        $query = AgentService::with('user', 'transaction')
            ->where('service_type', 'CRM')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('ticket_id', 'like', "%{$s}%")
                  ->orWhere('batch_id', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
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

        return view('admin.crm.index', compact('submissions'));
    }

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
            return back()->with('error', 'Error updating request.');
        }
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
        // Avoid double refund
        $existingRefund = Transaction::where('user_id', $submission->user_id)
            ->where('service_description', 'like', "%Refund%{$submission->reference}%")
            ->exists();

        if ($existingRefund) return;

        $wallet = Wallet::where('user_id', $submission->user_id)->first();
        if ($wallet) {
            $refundAmount = $submission->amount;
            $wallet->update(['balance' => $wallet->balance + $refundAmount]);

            Transaction::create([
                'user_id'     => $submission->user_id,
                'referenceId' => 'REF-' . strtoupper(bin2hex(random_bytes(6))),
                'amount'      => $refundAmount,
                'type'        => 'Credit',
                'service_type'=> 'CRM',
                'service_description' => "Refund for Failed CRM Request (Ref: {$submission->reference})",
                'status'      => 'Approved',
                'performed_by'=> 1, // System
                'metadata'    => ['original_ref' => $submission->reference]
            ]);
        }
    }
}
