<?php

namespace App\Http\Controllers;

use App\Models\VninSlipRequest;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VninSlipController extends Controller
{
    protected $transactionService;
    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->id();
    }

    public function index()
    {
        $records = VninSlipRequest::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $service = Service::firstOrCreate(
            ['service_code' => 'V101'],
            [
                'name' => 'VNIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 500.00,
                'description' => 'VNIN Slip Fee',
                'status' => 'enabled'
            ]
        );

        $fee = $service->amount;

        return view('user.vnin-slip-form', compact('records', 'fee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nin' => 'required|digits:11',
        ]);

        $service = Service::firstOrCreate(
            ['service_code' => 'V101'],
            [
                'name' => 'VNIN Slip',
                'category' => 'Verifications',
                'type' => 'Uncategorized',
                'amount' => 500.00,
                'description' => 'VNIN Slip Fee',
                'status' => 'enabled'
            ]
        );

        if ($service->status !== 'enabled') {
            return redirect()->back()->with('error', 'Service not available at the moment.');
        }

        $fee = $service->amount;
        $wallet = Wallet::where('user_id', $this->loginId)->first();

        if (!$wallet || $wallet->balance < $fee) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        // Deduct from wallet
        $wallet->balance -= $fee;
        $wallet->save();

        $serviceDesc = 'Wallet debited for VNIN Slip Request fee of ₦' . number_format($fee, 2);
        $transaction = $this->transactionService->createTransaction(
            $this->loginId,
            $fee,
            'VNIN Slip Request',
            $serviceDesc,
            'Wallet',
            'Approved'
        );

        VninSlipRequest::create([
            'user_id' => $this->loginId,
            'refno' => $transaction->referenceId,
            'tnx_id' => $transaction->id,
            'nin' => $request->nin,
            'status' => 'submitted',
        ]);

        return redirect()->route('user.vnin-slip')->with('success', 'VNIN Slip request submitted successfully!');
    }

    // Admin Methods
    public function list(Request $request)
    {
        $pending = VninSlipRequest::where('status', 'submitted')->count();
        $processing = VninSlipRequest::where('status', 'processing')->count();
        $resolved = VninSlipRequest::where('status', 'successful')->count();
        $rejected = VninSlipRequest::where('status', 'rejected')->count();
        $total_request = VninSlipRequest::count();

        $query = VninSlipRequest::with(['user', 'transactions']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%")
                    ->orWhere('nin', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->orderByRaw("
            CASE
                WHEN status = 'submitted' THEN 1
                WHEN status = 'processing' THEN 2
                ELSE 3
            END
        ")->orderByDesc('id')->paginate(10);

        $request_type = 'vnin-slip';

        return view('admin.vnin-slip-list', compact(
            'pending',
            'processing',
            'resolved',
            'rejected',
            'total_request',
            'records',
            'request_type'
        ));
    }

    public function showRequests($id, $type)
    {
        $requests = VninSlipRequest::with(['user', 'transactions'])->findOrFail($id);
        $request_type = 'vnin-slip';

        return view('admin.vnin-slip-view', compact('requests', 'request_type'));
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $vninRequest = VninSlipRequest::findOrFail($id);
        $vninRequest->status = $request->status;
        $vninRequest->reason = $request->comment;

        // Handle Result File Upload
        if ($request->hasFile('result_file')) {
            // Delete old file if it exists
            if ($vninRequest->result_file && file_exists(public_path($vninRequest->result_file))) {
                unlink(public_path($vninRequest->result_file));
            }

            $file = $request->file('result_file');
            $filename = time() . '_result_' . $id . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/vnin_slip/results');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);
            $vninRequest->result_file = 'uploads/vnin_slip/results/' . $filename;
        }

        if ($request->status === 'rejected' && $request->filled('refundAmount')) {
            $refundAmount = $request->refundAmount;
            $wallet = Wallet::where('user_id', $vninRequest->user_id)->first();
            $wallet->balance += $refundAmount;
            $wallet->save();

            $vninRequest->refunded_at = Carbon::now();

            $serviceDesc = 'Wallet credited with a Refund for VNIN Slip Request of ₦' . number_format($refundAmount, 2);
            $this->transactionService->createTransaction(
                $vninRequest->user_id,
                $refundAmount,
                'Refund VNIN Slip',
                $serviceDesc,
                'Wallet',
                'Approved'
            );
        }

        $vninRequest->save();

        return redirect()->route('admin.vnin-slip.index')->with('success', 'Request status updated successfully.');
    }
}
