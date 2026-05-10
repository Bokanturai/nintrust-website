<?php

namespace App\Http\Controllers\Agency;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;

class BvnManualSearchController extends Controller
{
    /**
     * Display the service form and submission history for Manual BVN Search.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Search for the specific Manual BVN Search service
        $bvnService = Service::where('service_code', '046')->first();

        // Query only this user's submissions
        $query = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_type', 'MANUAL_BVN_SEARCH')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('phone_number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('middle_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(10);
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('bvn.manual-search', [
            'submissions' => $submissions,
            'wallet'      => $wallet,
            'bvnService'  => $bvnService,
            'title'       => 'Manual BVN Search'
        ]);
    }

    /**
     * Handle the Manual BVN Search submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone_number' => 'required|string|digits_between:10,11',
            'first_name'   => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'last_name'    => 'required|string|max:255',
        ]);

        // Find the service
        $service = Service::where('service_code', '046')
            ->where('status', 'enabled')
            ->first();

        if (!$service) {
            return back()->with(['status' => 'error', 'message' => 'Manual BVN Search Service is currently unavailable.'])->withInput();
        }

        $servicePrice = $service->amount;

        // Check wallet
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $servicePrice) {
            return back()->with(['status' => 'error', 'message' => 'Insufficient wallet balance.'])->withInput();
        }

        $reference = 'MNL-' . strtoupper(bin2hex(random_bytes(6)));

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
                'service_type'        => 'MANUAL_BVN_SEARCH',
                'service_description' => "Manual BVN Search Request (Phone: {$request->phone_number})",
                'status'              => 'Approved',
                'performed_by'        => $user->id,
                'metadata'            => [
                    'phone_number' => $request->phone_number,
                    'first_name'   => $request->first_name,
                    'middle_name'  => $request->middle_name,
                    'last_name'    => $request->last_name,
                    'service'      => 'MANUAL_BVN_SEARCH'
                ]
            ]);

            // 3. Create Agent Service record
            AgentService::create([
                'user_id'         => $user->id,
                'service_id'      => $service->id,
                'transaction_id'  => $transaction->id,
                'reference'       => $reference,
                'phone_number'    => $request->phone_number,
                'first_name'      => $request->first_name,
                'middle_name'     => $request->middle_name,
                'last_name'       => $request->last_name,
                'status'          => 'processing',
                'amount'          => $servicePrice,
                'submission_date' => now(),
                'service_type'    => 'MANUAL_BVN_SEARCH',
                'service_name'    => $service->name,
                'field_code'      => '046',
            ]);

            DB::commit();

            return redirect()->route('user.manual-bvn-search')->with([
                'status'  => 'success',
                'message' => 'Request submitted successfully. An administrator will process it manually.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual BVN Search Submission Error: ' . $e->getMessage());
            return back()->with(['status' => 'error', 'message' => 'An error occurred during submission.'])->withInput();
        }
    }

    /**
     * Placeholder for status check.
     */
    public function checkStatus(Request $request, $id)
    {
        return response()->json(['status' => 'info', 'message' => 'Manual request is still being processed by an administrator.']);
    }

    /**
     * Admin view for all Manual BVN Search requests.
     */
    public function adminIndex(Request $request)
    {
        $query = AgentService::with('user', 'transaction')
            ->where('service_type', 'MANUAL_BVN_SEARCH')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('phone_number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('middle_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
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

        return view('admin.manual-search.index', [
            'submissions' => $submissions,
            'title'       => 'Admin - Manual BVN Search'
        ]);
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
            Log::error('Admin Manual BVN Search Update Error: ' . $e->getMessage());
            return back()->with('error', 'Error updating request.');
        }
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
                'service_type'        => 'MANUAL_BVN_SEARCH',
                'service_description' => "Refund for Failed Manual BVN Search (Ref: {$submission->reference})",
                'status'              => 'Approved',
                'performed_by'        => 1,
                'metadata'            => ['original_ref' => $submission->reference]
            ]);
        }
    }
}
