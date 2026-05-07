<?php

namespace App\Http\Controllers;

use App\Models\SuspendedNinRequest;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuspendedNinController extends Controller
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
        $records = SuspendedNinRequest::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $service = Service::where('type', 'Suspended NIN')
            ->where('status', 'enabled')
            ->first();

        $fee = $service ? $service->amount : 0;

        return view('user.suspended-nin-form', compact('records', 'fee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // 'title' => 'nullable|string',
            'nin' => 'required|digits:11',
            // 'surname' => 'required|string',
            // 'first_name' => 'required|string',
            // 'middle_name' => 'nullable|string',
            // 'gender' => 'required|string',
            // 'dob' => 'required|date',
            // 'town_city' => 'required|string',
            // 'state_residence' => 'required|string',
            // 'lga_residence' => 'required|string',
            // 'address_residence' => 'required|string',
            // 'state_origin' => 'required|string',
            // 'lga_origin' => 'required|string',
            // 'phone' => 'required|digits:11',
            // 'email' => 'required|email',
            // 'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $serviceCodes = ['1111'];

        $service = Service::whereIn('service_code', $serviceCodes)
            ->where('status', 'enabled')
            ->first();

        if (!$service) {
            return redirect()->back()->with('error', 'Service not available at the moment.');
        }

        $fee = $service->amount;
        $wallet = Wallet::where('user_id', $this->loginId)->first();

        if ($wallet->balance < $fee) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        // $photoPath = $request->file('photo')->store('suspended_nin_photos', 'public');

        // Deduct from wallet
        $wallet->balance -= $fee;
        $wallet->save();

        $serviceDesc = 'Wallet debited for Suspended NIN Request fee of ₦' . number_format($fee, 2);
        $transaction = $this->transactionService->createTransaction(
            $this->loginId,
            $fee,
            'Suspended NIN Request',
            $serviceDesc,
            'Wallet',
            'Approved'
        );

        SuspendedNinRequest::create([
            'user_id' => $this->loginId,
            'refno' => $transaction->referenceId,
            'tnx_id' => $transaction->id,
            // 'title' => $request->title,
            'nin' => $request->nin,
            'surname' => '', // $request->surname,
            'first_name' => '', // $request->first_name,
            // 'middle_name' => $request->middle_name,
            // 'gender' => $request->gender,
            // 'dob' => $request->dob,
            // 'town_city' => $request->town_city,
            // 'state_residence' => $request->state_residence,
            // 'lga_residence' => $request->lga_residence,
            // 'address_residence' => $request->address_residence,
            // 'state_origin' => $request->state_origin,
            // 'lga_origin' => $request->lga_origin,
            // 'phone' => $request->phone,
            // 'email' => $request->email,
            // 'photo' => $photoPath,
            'status' => 'submitted',
        ]);

        return redirect()->route('user.suspended-nin.form')->with('success', 'Suspended NIN request submitted successfully!');
    }

    // Admin Methods
    public function list(Request $request)
    {
        $pending = SuspendedNinRequest::where('status', 'submitted')->count();
        $processing = SuspendedNinRequest::where('status', 'processing')->count();
        $resolved = SuspendedNinRequest::where('status', 'successful')->count();
        $rejected = SuspendedNinRequest::where('status', 'rejected')->count();
        $total_request = SuspendedNinRequest::count();

        $query = SuspendedNinRequest::with(['user', 'transactions']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name', 'like', "%{$searchTerm}%")
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

        $request_type = 'suspended-nin';

        return view('admin.suspended-nin-list', compact(
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
        $requests = SuspendedNinRequest::with(['user', 'transactions'])->findOrFail($id);
        $request_type = 'suspended-nin';

        return view('admin.suspended-nin-view', compact('requests', 'request_type'));
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $suspendedRequest = SuspendedNinRequest::findOrFail($id);
        $suspendedRequest->status = $request->status;
        $suspendedRequest->reason = $request->comment;

        // Handle Result File Upload
        if ($request->hasFile('result_file')) {
            // Delete old file if it exists
            if ($suspendedRequest->result_file && file_exists(public_path($suspendedRequest->result_file))) {
                unlink(public_path($suspendedRequest->result_file));
            }

            $file = $request->file('result_file');
            $filename = time() . '_result_' . $id . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/suspended_nin/results');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);
            $suspendedRequest->result_file = 'uploads/suspended_nin/results/' . $filename;
        }

        if ($request->status === 'rejected' && $request->filled('refundAmount')) {
            $refundAmount = $request->refundAmount;
            $wallet = Wallet::where('user_id', $suspendedRequest->user_id)->first();
            $wallet->balance += $refundAmount;
            $wallet->save();

            $suspendedRequest->refunded_at = Carbon::now();

            $serviceDesc = 'Wallet credited with a Refund for Suspended NIN Request of ₦' . number_format($refundAmount, 2);
            $this->transactionService->createTransaction(
                $suspendedRequest->user_id,
                $refundAmount,
                'Refund Suspended NIN',
                $serviceDesc,
                'Wallet',
                'Approved'
            );
        }

        $suspendedRequest->save();

        return redirect()->route('admin.suspended-nin.index')->with('success', 'Request status updated successfully.');
    }
}
