<?php
namespace App\Http\Controllers;

use App\Models\PersonalizeNinRequest;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonalizeNinController extends Controller
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
        $records = PersonalizeNinRequest::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $service = Service::where('service_code', '108')->first();
        $fee = $service ? $service->amount : 300.00;

        return view('user.personalize-nin-form', compact('records', 'fee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|string|max:50',
        ]);

        $service = Service::where('service_code', '108')->first();

        if (!$service || $service->status !== 'enabled') {
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

        $serviceDesc = 'Wallet debited for Personalize NIN Request fee of ₦' . number_format($fee, 2);
        $transaction = $this->transactionService->createTransaction(
            $this->loginId,
            $fee,
            'Personalize NIN Request',
            $serviceDesc,
            'Wallet',
            'Approved'
        );

        PersonalizeNinRequest::create([
            'user_id' => $this->loginId,
            'refno' => $transaction->referenceId,
            'tnx_id' => $transaction->id,
            'tracking_id' => $request->tracking_id,
            'status' => 'submitted',
        ]);

        return redirect()->route('user.personalize-nin')->with('success', 'Personalize NIN request submitted successfully!');
    }

    // Admin Methods
    public function list(Request $request)
    {
        $pending = PersonalizeNinRequest::where('status', 'submitted')->count();
        $processing = PersonalizeNinRequest::where('status', 'processing')->count();
        $resolved = PersonalizeNinRequest::where('status', 'successful')->count();
        $rejected = PersonalizeNinRequest::where('status', 'rejected')->count();
        $total_request = PersonalizeNinRequest::count();

        $query = PersonalizeNinRequest::with(['user', 'transactions']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%")
                    ->orWhere('tracking_id', 'like', "%{$searchTerm}%")
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

        $request_type = 'personalize-nin';

        return view('admin.personalize-nin-list', compact(
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
        $requests = PersonalizeNinRequest::with(['user', 'transactions'])->findOrFail($id);
        $request_type = 'personalize-nin';

        return view('admin.personalize-nin-view', compact('requests', 'request_type'));
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $personalizeRequest = PersonalizeNinRequest::findOrFail($id);
        $personalizeRequest->status = $request->status;
        $personalizeRequest->reason = $request->comment;

        // Handle Result File Upload
        if ($request->hasFile('result_file')) {
            if ($personalizeRequest->result_file && file_exists(public_path($personalizeRequest->result_file))) {
                unlink(public_path($personalizeRequest->result_file));
            }

            $file = $request->file('result_file');
            $filename = time() . '_result_' . $id . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/personalize_nin/results');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);
            $personalizeRequest->result_file = 'uploads/personalize_nin/results/' . $filename;
        }

        if ($request->status === 'rejected' && $request->filled('refundAmount')) {
            $refundAmount = $request->refundAmount;
            $wallet = Wallet::where('user_id', $personalizeRequest->user_id)->first();
            $wallet->balance += $refundAmount;
            $wallet->save();

            $personalizeRequest->refunded_at = Carbon::now();

            $serviceDesc = 'Wallet credited with a Refund for Personalize NIN Request of ₦' . number_format($refundAmount, 2);
            $this->transactionService->createTransaction(
                $personalizeRequest->user_id,
                $refundAmount,
                'Refund Personalize NIN',
                $serviceDesc,
                'Wallet',
                'Approved'
            );
        }

        $personalizeRequest->save();

        return redirect()->route('admin.personalize-nin.index')->with('success', 'Request status updated successfully.');
    }
}
