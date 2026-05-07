<?php

namespace App\Http\Controllers;

use App\Models\BvnPhoneSearch;
use App\Models\NinModification;
use App\Models\NinService;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    protected $transactionService;

    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Service::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate($perPage)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function edit($id)
    {

        $service = Service::findOrFail($id);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'description' => 'nullable',
            'status' => 'required|in:enabled,disabled',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return redirect()->route('admin.services.index')->with('success', 'Service Updated Successfully!');
    }

    public function ninServices()
    {

        $services = Service::where('type', 'nin_services')->get();

        $ninServices = NinService::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('nin-services', compact('services', 'ninServices'));
    }

    public function requestNinService(Request $request)
    {
        $rules = [
            'service' => ['required', 'exists:services,service_code'],
        ];

        switch ($request->input('service')) {
            // case '113':
            // NIN + Email
            //   $rules += [
            //      'nin'   => ['required', 'digits:11'],
            //      'email' => ['required', 'email'],
            // ];
            //  break;
            case '113':
            case '114':
            case '115':
            case '116':
            case '117':
            case '118':
            case '119':
            case '120':
            case '121':
                // NIN only
                $rules += [
                    'nin' => ['required', 'digits:11'],
                ];
                break;

                //  case '115':
                // Full details
                //     $rules += [
                //       'nin'         => ['required', 'digits:11'],
                //       'tracking_id' => ['required', 'regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]{15}$/'],
                //      'surname'     => ['required', 'string', 'max:100'],
                //     'firstname'   => ['required', 'string', 'max:100'],
                //     // 'middlename'  => ['string', 'max:100'],
                //       'dob'         => ['required', 'date', 'before_or_equal:today'],
                //   ];
                // break;
        }

        $validated = $request->validate($rules);

        // NIN Services Fee
        $ServiceFee = 0;

        $Service = Service::where('service_code', $request->input('service'))
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'NIN Validation Request', $serviceDesc, 'Wallet', 'Approved');

            $trx_id = $transaction->id;

            NinService::create([
                'user_id' => $this->loginId,
                'tnx_id' => $trx_id,
                'refno' => $transaction->referenceId,
                'trackingId' => $request->tracking_id,
                'service_type' => $serviceType,
                'nin' => $request->nin,
                'email' => $request->email,
                'surname' => $request->surname,
                'middle_name' => $request->middlename,
                'first_name' => $request->firstname,
                'dob' => $request->dob,
            ]);

            return redirect()->back()->with('success', 'NIN Service Request was successfully');
        }
    }

    public function ninServicesList(Request $request)
    {

        // Services
        $pending = NinService::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = NinService::where('status', 'resolved')
            ->count();

        $rejected = NinService::where('status', 'rejected')
            ->count();

        $total_request = NinService::count();

        $query = NinService::with(['user', 'transactions']); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('nin', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('trackingId', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $nin_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'nin-services';

        return view('nin-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function showRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':

                break;
            case 'upgrade':

                break;

            case 'nin-services':
                $requests = NinService::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
                break;

            case 'vnin-to-nibss':

                break;

            default:
                $requests = NinService::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'rejected') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'view-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $requestDetails = NinService::findOrFail($id);
        $route = 'admin.nin.services.list';
        $status = $request->status;

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'rejected') {

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'NIN Validation Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }

    public function ninModification()
    {

        $services = Service::where('type', 'nin_mod')->get();

        $ninServices = NinModification::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('nin-mod', compact('services', 'ninServices'));
    }

    public function requestModification(Request $request)
    {

        $request->validate([
            'nin' => 'required|digits:11',
            'firstname' => 'nullable|string',
            'middlename' => 'nullable|string',
            'surname' => 'nullable|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|digits_between:10,15',
            'address' => 'nullable|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'full_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            // Store the image in storage/app/public/photos and return the relative path
            $photoPath = $image->store('photos', 'public');
        }

        // NIN Services Fee
        $ServiceFee = 0;

        $Service = Service::where('service_code', $request->input('service'))
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'NIN Modification Request', $serviceDesc, 'Wallet', 'Approved');

            $trx_id = $transaction->id;

            NinModification::create([
                'user_id' => $this->loginId,
                'tnx_id' => $trx_id,
                'refno' => $transaction->referenceId,
                'nin_number' => $request->nin,
                'address' => $request->address,
                'surname' => $request->surname,
                'middle_name' => $request->middlename,
                'description' => $serviceType,
                'first_name' => $request->firstname,
                'phone_number' => $request->phone,
                'dob' => $request->dob,
                'photo' => $photoPath,
                'full_address' => $request->full_address,
                'origin_address' => $request->origin_address,
            ]);

            return redirect()->back()->with('success', 'NIN Modification Service Request was successfully');
        }
    }

    public function modServicesList(Request $request)
    {

        // Services
        $pending = ninModification::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = ninModification::where('status', 'resolved')
            ->count();

        $rejected = ninModification::where('status', 'rejected')
            ->count();

        $total_request = ninModification::count();

        $query = ninModification::with(['user', 'transactions']); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('nin_number', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $nin_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'mod-services';

        return view('admin.mod-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function showModRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':
                $requests = NinModification::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';

                break;
            case 'upgrade':

                break;

            case 'nin-services':

                break;

            case 'vnin-to-nibss':

                break;

            default:
                $requests = NinModification::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'rejected') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-mod-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateModRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        $requestDetails = ninModification::findOrFail($id);
        $route = 'admin.mod.services.list';
        $status = $request->status;

        if ($request->hasFile('document')) {
            $doc = $request->file('document');
            $requestDetails->document = $doc->store('docs', 'public');
        }

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'rejected') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'Modification Service Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }

    public function bvnServicesList(Request $request)
    {

        // Services
        $pending = BvnPhoneSearch::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = BvnPhoneSearch::where('status', 'resolved')
            ->count();

        $rejected = BvnPhoneSearch::where('status', 'rejected')
            ->count();

        $total_request = BvnPhoneSearch::count();

        $query = BvnPhoneSearch::with(['user', 'transactions']); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('phone_number', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('name', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $bvn_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'bvn-services';

        return view('admin.bvn-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'bvn_services',
            'request_type'
        ));
    }

    public function showBvnRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':

                break;
            case 'upgrade':

                break;

            case 'nin-services':
                $requests = BvnPhoneSearch::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
                break;

            case 'vnin-to-nibss':

                break;

            default:
                $requests = BvnPhoneSearch::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'rejected') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-bvn-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateBvnRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $requestDetails = BvnPhoneSearch::findOrFail($id);
        $route = 'admin.bvn.services.list';
        $status = $request->status;

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'rejected') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'BVN Search Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }
}
