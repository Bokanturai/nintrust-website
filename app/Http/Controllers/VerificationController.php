<?php

namespace App\Http\Controllers;

use App\Http\Repositories\BVN_PDF_Repository;
use App\Http\Repositories\NIN_PDF_Repository;
use App\Http\Repositories\VirtualAccountRepository;
use App\Http\Repositories\WalletRepository;
use App\Models\BvnPhoneSearch;
use App\Models\IpeRequest;
use App\Models\Service;
use App\Models\Verification;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    protected $transactionService;

    protected $loginId;

    const RESP_STATUS_SUCCESS = true;

    const RESP_MESSAGE = null;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function demoVerify()
    {

        $serviceCodes = ['126', '106', '107', '105', '127'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('126') ?? new \App\Models\Service(['amount' => 0.00]);
        $standard_nin_fee = $services->get('106') ?? new \App\Models\Service(['amount' => 0.00]);
        $premium_nin_fee = $services->get('107') ?? new \App\Models\Service(['amount' => 0.00]);
        $regular_nin_fee = $services->get('105') ?? new \App\Models\Service(['amount' => 0.00]);
        $basic_nin_fee = $services->get('127') ?? new \App\Models\Service(['amount' => 0.00]);

        $user = auth()->user();

        $latestVerifications = $user->verifications()->latest()->paginate(5);

        return view('verification.demo-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee', 'basic_nin_fee', 'latestVerifications'));
    }

    public function ninDemoRetrieve(Request $request)
    {

        $request->validate([
            'gender' => ['required', 'in:MALE,FEMALE'],
            'dob' => ['required', 'date'],
            'lastName' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
        ]);

        $ServiceFee = Service::getAmountByCode('126');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = [
                    'firstName' => $request->input('firstName'),
                    'lastName' => $request->input('lastName'),
                    'gender' => $request->input('gender') == 'MALE' ? 'M' : 'F',
                    'dateOfBirth' => Carbon::parse($request->input('dob'))->format('d-m-Y'),
                    'ref' => 'nintrust_' . time(),
                ];

                $url = env('AREWA_URL').'/nin/demo';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                    curl_close($ch);
                    throw new \Exception('cURL Error: '.$error);
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN DEMO Verification', ['success' => $response['success'] ?? false]);

                // Check for success - handle nested api_response structure
                if ((isset($response['status']) && $response['status'] === true) && 
                    (isset($response['api_response']['status']) && $response['api_response']['status'] === true) &&
                    (isset($response['api_response']['data']['status']) && $response['api_response']['data']['status'] === true)) {

                    // Extract data from nested structure: response.api_response.data.data
                    $data = $response['api_response']['data']['data'] ?? [];
                    $data['mobile'] = $data['mobile'] ?? $data['phoneNumber'] ?? $data['telephoneno'] ?? $data['phone'] ?? '';
                    $data['photo'] = $data['photo'] ?? $data['face'] ?? $data['image'] ?? $data['passport'] ?? '';

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);
                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Demo Verification', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForNINDEMO($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return response()->json(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => [$response['message'] ?? 'Verification Failed: Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninv2Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        $ServiceFee = Service::getAmountByCode('128');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('AREWA_URL').'/nin/verify';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN V2 Verification', ['success' => $response['success'] ?? false]);

                if ((isset($response["success"]) && $response["success"] === true) || (isset($response["status"]) && $response["status"] == "success") || (isset($response["respCode"]) && ($response["respCode"] == "111111" || $response["respCode"] == "200"))) {

                    $data = $response['data'] ?? $response['message'];

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);
                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V2 Verification', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForNIN($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => [$response['message'] ?? 'Verification Failed: Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ShowIpe()
    {
        $serviceCodes = ['112'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('112') ?? new \App\Models\Service(['amount' => 0.00]);

        $ipes = IpeRequest::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('verification.ipe', compact('ServiceFee', 'ipes'));
    }

    public function ninPersonalize()
    {
        $serviceCodes = ['108', '105'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('108') ?? new \App\Models\Service(['amount' => 0.00]);
        $regular_nin_fee = $services->get('105') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.nin-track', compact('ServiceFee', 'regular_nin_fee'));
    }

    public function ninVerify()
    {

        $serviceCodes = ['104', '106', '107'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('104') ?? new \App\Models\Service(['amount' => 0.00]);
        $standard_nin_fee = $services->get('106') ?? new \App\Models\Service(['amount' => 0.00]);
        $premium_nin_fee = $services->get('107') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.nin-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee'));
    }

    public function ninVerify2()
    {

        $serviceCodes = ['128', '105', '106', '107', '127'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('128') ?? new \App\Models\Service(['amount' => 0.00]);
        $standard_nin_fee = $services->get('106') ?? new \App\Models\Service(['amount' => 0.00]);
        $regular_nin_fee = $services->get('105') ?? new \App\Models\Service(['amount' => 0.00]);
        $premium_nin_fee = $services->get('107') ?? new \App\Models\Service(['amount' => 0.00]);
        $basic_nin_fee = $services->get('127') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.nin-verifyv2', compact('ServiceFee', 'regular_nin_fee', 'standard_nin_fee', 'premium_nin_fee', 'basic_nin_fee'));
    }

    public function bvnVerify()
    {
        // Fetch all required service fees in one query
        $serviceCodes = ['101', '102', '103', '109'];
        $services = Service::whereIn('service_code', $serviceCodes)->get()->keyBy('service_code');

        $BVNFee = $services->get('101') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_standard_fee = $services->get('102') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_premium_fee = $services->get('103') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_plastic_fee = $services->get('109') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.bvn-verify', compact('BVNFee', 'bvn_standard_fee', 'bvn_premium_fee', 'bvn_plastic_fee'));
    }

    public function phoneVerify()
    {

        $serviceCodes = ['111', '105', '106', '107', '127'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('111') ?? new \App\Models\Service(['amount' => 0.00]);
        $standard_nin_fee = $services->get('106') ?? new \App\Models\Service(['amount' => 0.00]);
        $regular_nin_fee = $services->get('105') ?? new \App\Models\Service(['amount' => 0.00]);
        $premium_nin_fee = $services->get('107') ?? new \App\Models\Service(['amount' => 0.00]);
        $basic_nin_fee = $services->get('127') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.nin-phone-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee', 'basic_nin_fee'));
    }

    private function createAccounts($userId)
    {

        $repObj = new WalletRepository;
        $repObj->createWalletAccount($userId);

        $repObj2 = new VirtualAccountRepository;
        $repObj2->createVirtualAccount($userId);
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'bvn' => 'required|numeric|digits:11',
        ]);

        $bvn = $request->input('bvn');

        return $this->verifyUserBVN($bvn);
    }

    private function verifyUserBVN($bvn)
    {
        try {

            $data = ['bvn' => $bvn];

            $url = env('AREWA_URL').'/bvn/verify';
            $token = env('AREWA_TOKEN');
            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if ((isset($response["success"]) && $response["success"] === true) || (isset($response["status"]) && $response["status"] == "success") || (isset($response["respCode"]) && ($response["respCode"] == "111111" || $response["respCode"] == "200"))) {

                $data = $response['data'];

                $updateData = [
                    'name' => ucwords(strtolower($data['firstName']).' '.strtolower($data['middleName']).' '.strtolower($data['lastName'])),
                    'dob' => $data['birthday'],
                    'gender' => $data['gender'],
                    'kyc_status' => 'Verified',
                    'idNumber' => $bvn,
                ];

                if (! empty($data['phoneNumber'])) {
                    $updateData['phone_number'] = $data['phoneNumber'];
                }

                if (! empty($data['photo'])) {
                    $updateData['profile_pic'] = $data['photo'];
                }

                auth()->user()->update($updateData);

                $this->createAccounts(auth()->user()->id);

                return redirect()->back()->with('success', 'Your identity verification is complete, and youre all set to explore our services. Thank you for verifying your account!');
            } else {
                Log::error('Error Verifiying User '.auth()->user()->id.': '.$response);

                return redirect()->back()->with('error', 'An error occurred while making the BVN Verification (System Err)');
            }
        } catch (\Exception $e) {
            Log::error('Error Verifiying User '.auth()->user()->id.': '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while making the BVN Verification');
        }
    }

    public function ninRetrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        $ServiceFee = Service::getAmountByCode('104');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('AREWA_URL').'/nin/verify';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if ((isset($response['success']) && $response['success'] === true) || (isset($response['status']) && $response['status'] == 'success') || (isset($response['respCode']) && ($response['respCode'] == '111111' || $response['respCode'] == '200'))) {

                    $data = $response['data'] ?? $response['message'];

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForNIN($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => [$response['message'] ?? 'Verification Failed: Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninPhoneRetrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The Phone number is required.',
                'nin.numeric' => 'The Phone number must be a numeric value.',
                'nin.digits' => 'The Phone must be exactly 11 digits.',
            ]
        );

        $ServiceFee = Service::getAmountByCode('111');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = [
                    'value' => $request->input('nin'),
                    'ref' => 'nintrust_' . time(),
                ];

                $url = env('AREWA_URL').'/nin/phone';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Check for success - handle nested api_response structure
                if ((isset($response['status']) && $response['status'] === true) && 
                    (isset($response['api_response']['status']) && $response['api_response']['status'] === true) &&
                    (isset($response['api_response']['data']['status']) && $response['api_response']['data']['status'] === true)) {

                    // Extract data from nested structure: response.api_response.data.data
                    $data = $response['api_response']['data']['data'] ?? [];

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Phone Verification', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForNINPhone($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => [$response['message'] ?? 'Verification Failed: Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ipeRequest(Request $request)
    {
        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        $ServiceFee = Service::getAmountByCode('112');

        if ($ServiceFee === 0) {
            return redirect()->route('user.ipe')
                ->with('error', 'Sorry Action not Allowed !');
        }

        $loginUserId = auth()->user()->id;
        $trackingId = $request->input('trackingId');

        $wallet = Wallet::where('user_id', $loginUserId)->first();
        if (! $wallet) {
            return redirect()->route('user.ipe')
                ->with('error', 'Wallet not found.');
        }

        if ($wallet->balance < $ServiceFee) {
            return redirect()->route('user.ipe')
                ->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        }

        $existingRequest = IpeRequest::where('user_id', $loginUserId)
            ->where('trackingId', $trackingId)
            ->exists();

        if ($existingRequest) {
            return redirect()->route('user.ipe')
                ->with('error', 'This tracking number has already been submitted.');
        }

        try {
            $data = [
                'field_code' => '002',
                'tracking_id' => $trackingId,
                'description' => 'NINTrust IPE Request'
            ];

            $url = env('AREWA_URL').'/nin/ipe';
            $token = env('AREWA_TOKEN');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('cURL Error: '.$error);
            }

            curl_close($ch);

            $response = json_decode($response, true);
            if (! is_array($response)) {
                throw new \Exception('Invalid response from the IPE API.');
            }

            $status = strtolower($response['status'] ?? 'pending');
            $respCode = $response['respCode'] ?? $response['response_code'] ?? null;
            $comment = $response['comment'] ?? $response['message'] ?? 'No response message returned.';

            $isAccepted = (isset($response['success']) && $response['success'] === true)
                || in_array($status, ['success', 'successful'], true)
                || in_array($respCode, ['111111', '200'], true);

            if (! $isAccepted) {
                throw new \Exception('IPE request is not successful: ' . $comment);
            }

            DB::transaction(function () use ($loginUserId, $wallet, $ServiceFee, $trackingId, $status, $respCode, $comment) {
                $newBalance = $wallet->balance - $ServiceFee;
                Wallet::where('user_id', $loginUserId)->update(['balance' => $newBalance]);

                $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);
                $tnx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'IPE Request', $serviceDesc, 'Wallet', 'Approved');

                IpeRequest::create([
                    'user_id' => $loginUserId,
                    'tnx_id' => $tnx->id,
                    'trackingId' => $trackingId,
                    'status' => 'processing',
                    'reply' => $comment,
                    'resp_code' => $respCode,
                ]);
            });

            return redirect()->route('user.ipe')
                ->with('success', 'IPE request submitted successfully. Please check status shortly.');
        } catch (\Exception $e) {
            Log::error('IPE request failed for user '.$loginUserId.' tracking '.$trackingId.': '.$e->getMessage());

            return redirect()->route('user.ipe')
                ->with('error', $e->getMessage());
        }
    }

    public function ipeRequestStatus($trackingId)
    {
        try {

            $data = ['trackingId' => $trackingId];

            $url = env('AREWA_URL').'/nin/ipe?tracking_id=' . $trackingId;
            $token = env('AREWA_TOKEN');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('cURL Error: '.$error);
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (! is_array($response)) {
                return redirect()->route('user.ipe')->with('error', 'Invalid response from the IPE API.');
            }

            $status = strtolower($response['status'] ?? 'pending');
            $respCode = $response['respCode'] ?? $response['response_code'] ?? null;
            $comment = $response['comment'] ?? $response['message'] ?? 'No response message returned.';

            $isSuccessful = (isset($response['success']) && $response['success'] === true)
                || $status === 'success'
                || $status === 'successful'
                || in_array($respCode, ['111111', '200'], true);

            if ($isSuccessful) {
                IpeRequest::where('trackingId', $trackingId)
                    ->where('user_id', $this->loginId)
                    ->update(['reply' => $comment, 'status' => 'successful', 'resp_code' => $respCode]);

                return redirect()->route('user.ipe')
                    ->with('success', 'IPE request is successful: ' . $comment);
            }

            if ($status === 'rejected' || $status === 'failed') {
                // process refund
                $ServiceFee = Service::getAmountByCode('112');
                if ($ServiceFee) {
                    $wallet = Wallet::where('user_id', $this->loginId)->first();
                    $requestRecord = IpeRequest::where('trackingId', $trackingId)
                        ->where('user_id', $this->loginId)
                        ->whereNull('refunded_at')
                        ->first();

                    if ($requestRecord) {
                        $balance = $wallet->balance + $ServiceFee;
                        Wallet::where('user_id', $this->loginId)->update(['balance' => $balance]);
                        IpeRequest::where('trackingId', $trackingId)
                            ->where('user_id', $this->loginId)
                            ->update(['refunded_at' => Carbon::now(), 'reply' => $comment, 'status' => 'failed', 'resp_code' => $respCode]);

                        $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'IPE Refund', "IPE Refund for Tracking ID: {$trackingId}", 'Wallet', 'Approved');
                    }
                }

                return redirect()->route('user.ipe')->with('error', 'IPE Request Failed: ' . $comment);
            }

            // Update any other statuses as processing/pending
            $normalizedStatus = in_array($status, ['pending', 'processing'], true) ? $status : 'processing';
            IpeRequest::where('trackingId', $trackingId)
                ->where('user_id', $this->loginId)
                ->update(['status' => $normalizedStatus, 'reply' => $comment, 'resp_code' => $respCode]);
            return redirect()->route('user.ipe')->with('success', 'IPE Status: ' . ucfirst($normalizedStatus));
        } catch (\Exception $e) {

            return redirect()->route('user.ipe')
                ->with('error', 'An error occurred while making the API request');
        }
    }

    public function bvnRetrieve(Request $request)
    {

        $request->validate(['bvn' => 'required|numeric|digits:11']);

        // BVN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::getAmountByCode('101');


        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['bvn' => $request->input('bvn')];

                $url = env('AREWA_URL').'/bvn/verify';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if ((isset($response["success"]) && $response["success"] === true) || (isset($response["status"]) && $response["status"] == "success") || (isset($response["respCode"]) && ($response["respCode"] == "111111" || $response["respCode"] == "200"))) {

                    $data = $response['data'];

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'BVN Verification', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForBVN($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '99120010') {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninTrackRetrieve(Request $request)
    {

        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        $ServiceFee = Service::getAmountByCode('108');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $trackingId = $request->input('trackingId');
                $url = env('AREWA_URL')."/nin/verify?tracking_id={$trackingId}";
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if ((isset($response["success"]) && $response["success"] === true) || (isset($response["status"]) && $response["status"] == "success") || (isset($response["respCode"]) && ($response["respCode"] == "111111" || $response["respCode"] == "200"))) {

                    $data = $response['data'] ?? $response['message'];

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $trx = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Personalize', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForNINTracking($data, $loginUserId, $ServiceFee, $trx);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => [$response['message'] ?? 'Verification Failed: Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function processResponseDataForNIN($data, $userId = null, $amount = 0, $trx = null)
    {
        return Verification::create([
            'user_id' => $userId ?? auth()->user()->id,
            'idno' => $data['nin'] ?? '',
            'type' => 'NIN',
            'nin' => $data['nin'] ?? '',
            'reference' => $trx ? $trx->referenceId : null,
            'transaction_id' => $trx ? $trx->id : null,
            'amount' => $amount,
            'status' => 'successful',
            'submission_date' => now(),
            
            // Name mapping - directly from API response
            'firstname' => $data['firstName'] ?? '',
            'middlename' => $data['middleName'] ?? '',
            'surname' => $data['surname'] ?? '',
            'first_name' => $data['firstName'] ?? '',
            'middle_name' => $data['middleName'] ?? '',
            'last_name' => $data['surname'] ?? '',
            
            // Date of birth - handle various formats
            'dob' => isset($data['birthDate']) ? (strpos($data['birthDate'], '-') !== false ? (strlen(explode('-', $data['birthDate'])[0]) == 4 ? $data['birthDate'] : \Carbon\Carbon::createFromFormat('d-m-Y', $data['birthDate'])->format('Y-m-d')) : $data['birthDate']) : '1970-01-01',
            'birthdate' => $data['birthDate'] ?? '1970-01-01',
            
            // Personal details
            'gender' => $data['gender'] ?? '',
            'phoneno' => $data['phoneNumber'] ?? '',
            'telephoneno' => $data['phoneNumber'] ?? '',
            'photo' => $data['photo'] ?? '',
            'photo_path' => $data['photo'] ?? '',
        ]);
    }

    public function processResponseDataForBVN($data, $userId = null, $amount = 0, $trx = null)
    {
        return Verification::create([
            'user_id' => $userId ?? auth()->user()->id,
            'idno' => $data['bvn'] ?? '',
            'type' => 'BVN',
            'reference' => $trx ? $trx->referenceId : null,
            'transaction_id' => $trx ? $trx->id : null,
            'amount' => $amount,
            'status' => 'successful',
            'submission_date' => now(),
            
            // Name mapping
            'firstname' => $data['firstName'] ?? '',
            'middlename' => $data['middleName'] ?? '',
            'surname' => $data['lastName'] ?? '',
            'first_name' => $data['firstName'] ?? '',
            'middle_name' => $data['middleName'] ?? '',
            'last_name' => $data['lastName'] ?? '',
            
            // Other details
            'phoneno' => $data['phoneNumber'] ?? '',
            'telephoneno' => $data['phoneNumber'] ?? '',
            'dob' => $data['birthday'] ?? '',
            'birthdate' => $data['birthday'] ?? '',
            'gender' => $data['gender'] ?? '',
            'photo' => $data['photo'] ?? '',
            'photo_path' => $data['photo'] ?? '',
            'enrollmentBank' => $data['enrollmentBank'] ?? '',
            'enrollmentBranch' => $data['enrollmentBranch'] ?? '',
        ]);
    }

    public function processResponseDataForNINTracking($data, $userId = null, $amount = 0, $trx = null)
    {
        return Verification::create([
            'user_id' => $userId ?? auth()->user()->id,
            'idno' => $data['nin'] ?? '',
            'type' => 'NIN',
            'nin' => $data['nin'] ?? '',
            'reference' => $trx ? $trx->referenceId : null,
            'transaction_id' => $trx ? $trx->id : null,
            'amount' => $amount,
            'status' => 'successful',
            'submission_date' => now(),
            'trackingId' => $data['trackingid'] ?? '',
            
            // Name mapping
            'firstname' => $data['firstname'] ?? '',
            'middlename' => $data['middlename'] ?? '',
            'surname' => $data['lastname'] ?? '',
            'first_name' => $data['firstname'] ?? '',
            'middle_name' => $data['middlename'] ?? '',
            'last_name' => $data['lastname'] ?? '',
            
            'dob' => '1970-01-01',
            'birthdate' => '1970-01-01',
            'gender' => ($data['gender'] ?? '') == 'm' || ($data['gender'] ?? '') == 'Male' ? 'Male' : 'Female',
            'state' => $data['state'] ?? '',
            'self_origin_state' => $data['state'] ?? '',
            'lga' => $data['town'] ?? '',
            'self_origin_lga' => $data['town'] ?? '',
            'address' => $data['address'] ?? '',
            'residentialAddress' => $data['address'] ?? '',
            'photo' => $data['face'] ?? '',
            'photo_path' => $data['face'] ?? '',
        ]);
    }

    public function processResponseDataForNINPhone($data, $userId = null, $amount = 0, $trx = null)
    {
        try {
            return Verification::create([
                'user_id' => $userId ?? auth()->user()->id,
                'idno' => $data['nin'] ?? '',
                'type' => 'NIN',
                'nin' => $data['nin'] ?? '',
                'reference' => $trx ? $trx->referenceId : null,
                'transaction_id' => $trx ? $trx->id : null,
                'amount' => $amount,
                'status' => 'successful',
                'submission_date' => now(),
                'trackingId' => $data['trackingId'] ?? '',
                
                // Name mapping - directly from API response
                'firstname' => $data['firstname'] ?? '',
                'middlename' => '', // API doesn't provide middlename in phone response
                'surname' => $data['surname'] ?? '',
                'first_name' => $data['firstname'] ?? '',
                'middle_name' => '',
                'last_name' => $data['surname'] ?? '',
                
                // Contact information
                'phoneno' => $data['telephoneno'] ?? '',
                'telephoneno' => $data['telephoneno'] ?? '',
                'email' => $data['email'] ?? '',
                
                // Date of birth - handle d-m-Y format from API
                'dob' => isset($data['birthdate']) ? (strpos($data['birthdate'], '-') !== false ? \Carbon\Carbon::createFromFormat('d-m-Y', $data['birthdate'])->format('Y-m-d') : $data['birthdate']) : '1970-01-01',
                'birthdate' => $data['birthdate'] ?? '1970-01-01',
                
                // Personal details
                'gender' => ($data['gender'] ?? '') == 'm' ? 'Male' : 'Female',
                'title' => $data['title'] ?? '',
                'maritalstatus' => $data['maritalstatus'] ?? '',
                'religion' => $data['religion'] ?? '',
                'profession' => $data['profession'] ?? '',
                'educationallevel' => $data['educationallevel'] ?? '',
                'heigth' => $data['heigth'] ?? '',
                
                // Birth information
                'birthstate' => $data['birthstate'] ?? '',
                'birthlga' => $data['birthlga'] ?? '',
                'birthcountry' => $data['birthcountry'] ?? '',
                
                // Residence information
                'residence_state' => $data['residence_state'] ?? '',
                'residence_lga' => $data['residence_lga'] ?? '',
                'residence_town' => $data['residence_Town'] ?? '',
                'address' => $data['residence_AdressLine1'] ?? '',
                'residentialAddress' => $data['residence_AdressLine1'] ?? '',
                
                // Origin information
                'self_origin_state' => $data['self_origin_state'] ?? '',
                'self_origin_lga' => $data['self_origin_lga'] ?? '',
                
                // NOK information
                'nok_firstname' => $data['nok_firstname'] ?? '',
                'nok_middlename' => $data['nok_middlename'] ?? '',
                'nok_surname' => $data['nok_surname'] ?? '',
                'nok_address1' => $data['nok_address1'] ?? '',
                'nok_lga' => $data['nok_lga'] ?? '',
                'nok_state' => $data['nok_state'] ?? '',
                'nok_town' => $data['nok_town'] ?? '',
                'nok_postalcode' => $data['nok_postalcode'] ?? '',
                
                // Documents
                'photo' => $data['photo'] ?? '',
                'photo_path' => $data['photo'] ?? '',
                'signature' => $data['signature'] ?? '',
                'signature_path' => $data['signature'] ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error('Verification creation failed: '.$e->getMessage());
            return null;
        }
    }

    public function processResponseDataIpe($userId, $trxId,$trackingNo)
    {
        try {
            IpeRequest::create([
                'user_id' => $userId,
                'tnx_id'=>$trxId,
                'trackingId' => $trackingNo,
            ]);
        } catch (\Exception $e) {
            Log::error('IPE Request creation failed: '.$e->getMessage());
            throw $e; // Re-throw to be handled by caller
        }
    }

    public function processResponseDataForNINDEMO($data, $userId = null, $amount = 0, $trx = null)
    {
        try {
            return Verification::create([
                'user_id' => $userId ?? auth()->user()->id,
                'idno' => $data['nin'] ?? '',
                'type' => 'NIN',
                'nin' => $data['nin'] ?? '',
                'reference' => $trx ? $trx->referenceId : null,
                'transaction_id' => $trx ? $trx->id : null,
                'amount' => $amount,
                'status' => 'successful',
                'submission_date' => now(),
                'trackingId' => $data['trackingId'] ?? '',
                
                // Name mapping - directly from API response
                'firstname' => $data['firstname'] ?? '',
                'middlename' => $data['middlename'] ?? '',
                'surname' => $data['surname'] ?? '',
                'first_name' => $data['firstname'] ?? '',
                'middle_name' => $data['middlename'] ?? '',
                'last_name' => $data['surname'] ?? '',
                
                // Contact information
                'phoneno' => $data['telephoneno'] ?? '',
                'telephoneno' => $data['telephoneno'] ?? '',
                'email' => $data['email'] ?? '',
                
                // Date of birth - handle d-m-Y format from API
                'dob' => isset($data['birthdate']) ? (strpos($data['birthdate'], '-') !== false ? \Carbon\Carbon::createFromFormat('d-m-Y', $data['birthdate'])->format('Y-m-d') : $data['birthdate']) : '1970-01-01',
                'birthdate' => $data['birthdate'] ?? '1970-01-01',
                
                // Personal details
                'gender' => ($data['gender'] ?? '') == 'm' ? 'Male' : 'Female',
                'title' => $data['title'] ?? '',
                'maritalstatus' => $data['maritalstatus'] ?? '',
                'religion' => $data['religion'] ?? '',
                'profession' => $data['profession'] ?? '',
                'educationallevel' => $data['educationallevel'] ?? '',
                'heigth' => $data['heigth'] ?? '',
                
                // Birth information
                'birthstate' => $data['birthstate'] ?? '',
                'birthlga' => $data['birthlga'] ?? '',
                'birthcountry' => $data['birthcountry'] ?? '',
                
                // Residence information
                'residence_state' => $data['residence_state'] ?? '',
                'residence_lga' => $data['residence_lga'] ?? '',
                'residence_town' => $data['residence_Town'] ?? '',
                'address' => $data['residence_AdressLine1'] ?? '',
                'residentialAddress' => $data['residence_AdressLine1'] ?? '',
                
                // Origin information
                'self_origin_state' => $data['self_origin_state'] ?? '',
                'self_origin_lga' => $data['self_origin_lga'] ?? '',
                
                // NOK information
                'nok_firstname' => $data['nok_firstname'] ?? '',
                'nok_middlename' => $data['nok_middlename'] ?? '',
                'nok_surname' => $data['nok_surname'] ?? '',
                'nok_address1' => $data['nok_address1'] ?? '',
                'nok_lga' => $data['nok_lga'] ?? '',
                'nok_state' => $data['nok_state'] ?? '',
                'nok_town' => $data['nok_town'] ?? '',
                'nok_postalcode' => $data['nok_postalcode'] ?? '',
                
                // Documents
                'photo' => $data['photo'] ?? '',
                'photo_path' => $data['photo'] ?? '',
                'signature' => $data['signature'] ?? '',
                'signature_path' => $data['signature'] ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error('Verification creation failed: '.$e->getMessage());
            return null;
        }
    }

    public function regularSlip($nin_no)
    {

        // NIN Services Fee
        $ServiceFee = Service::getAmountByCode('105');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Regular NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->regularPDF($nin_no);

            return $response;
        }
    }

    public function standardSlip($nin_no)
    {

        // NIN Services Fee
        $ServiceFee = Service::getAmountByCode('106');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Standard NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->standardPDF($nin_no);

            return $response;
        }
    }

    public function premiumSlip($nin_no)
    {
        // NIN Services Fee
        $ServiceFee = Service::getAmountByCode('107');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Premium NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->premiumPDF($nin_no);

            return $response;
        }
    }

    public function premiumBVN($bvnno)
    {

        // BVN Services Fee
        $ServiceFee = Service::getAmountByCode('103');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Premium BVN Slip', $serviceDesc, 'Wallet', 'Approved');

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('verification.PremiumBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    'message' => 'Error',
                    'errors' => ['Not Found' => 'Verification record not found !'],
                ], 422);
            }
        }
    }

    public function standardBVN($bvnno)
    {

        $ServiceFee = Service::getAmountByCode('102');

        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Standard BVN Slip', $serviceDesc, 'Wallet', 'Approved');

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('verification.freeBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    'message' => 'Error',
                    'errors' => ['Not Found' => 'Verification record not found !'],
                ], 422);
            }
        }
    }

    public function plasticBVN($bvnno)
    {
        // Services Fee
        $ServiceFee = Service::getAmountByCode('109');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Plastic ID Card', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new BVN_PDF_Repository;
            $response = $repObj->plasticPDF($bvnno);

            return $response;
        }
    }

    public function basicSlip($nin_no)
    {
        // NIN Services Fee
        $ServiceFee = Service::getAmountByCode('127');

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Basic NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->basicPDF($nin_no);

            return $response;
        }
    }

    public function bvnPhoneSearch()
    {
        $serviceCodes = ['133'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        $ServiceFee = $services->get('133') ?? new \App\Models\Service(['amount' => 0.00]);

        $bvns = BvnPhoneSearch::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        return view('verification.phone-search', compact('ServiceFee', 'bvns'));
    }

    public function bvnPhoneRequest(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits:11',
            'name' => 'required|string',
        ]);

        $ServiceFee = Service::getAmountByCode('133');

        if ($ServiceFee === 0) {
            return redirect()->route('user.bvn-phone-search')
                ->with('error', 'Sorry Action not Allowed !');
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {

            return redirect()->route('user.bvn-phone-search')
                ->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet_balance - $ServiceFee;

            Wallet::where('user_id', $loginUserId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $trx_id = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'BVN Phone Search', $serviceDesc, 'Wallet', 'Approved');
            $refno = $this->transactionService->generateReferenceNumber();

            // Save NIN validation
            BvnPhoneSearch::create([
                'user_id' => $loginUserId,
                'tnx_id' => $trx_id->id,
                'refno' => $refno,
                'phone_number' => $request->phone_number,
                'name' => $request->name,
            ]);

            return redirect()->route('user.bvn-phone-search')
                ->with('success', 'BVN Search submitted successfully !');
        }
    }

    public function bvnPhoneVerify()
    {
        // Fetch all required service fees in one query
        $serviceCodes = ['134', '102', '103', '109'];
        $services = Service::whereIn('service_code', $serviceCodes)->get()->keyBy('service_code');

        $BVNFee = $services->get('134') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_standard_fee = $services->get('102') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_premium_fee = $services->get('103') ?? new \App\Models\Service(['amount' => 0.00]);
        $bvn_plastic_fee = $services->get('109') ?? new \App\Models\Service(['amount' => 0.00]);

        return view('verification.bvn-phone-verify', compact('BVNFee', 'bvn_standard_fee', 'bvn_premium_fee', 'bvn_plastic_fee'));
    }

    public function bvnPhoneRetrieve(Request $request)
    {

        $request->validate(['phoneNumber' => 'required|numeric|digits:11']);

        $ServiceFee = Service::getAmountByCode('134');

        if ($ServiceFee === 0) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['bvn' => $request->input('phoneNumber')];

                $url = env('AREWA_URL').'/bvn/verify';
                $token = env('AREWA_TOKEN');

                $headers = [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('Verification record:', $response);

                if (isset($response['response_code']) && $response['response_code'] === '00') {

                    $data = $response['data'];

                    // Log::info('Verification record:', ['data' => $data]);

                    // $this->processResponseDataForBVNPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'BVN Phone Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['response_code']) && $response['response_code'] === '01') {

                    return response()->json([
                        'status' => 'Record Not Found',
                        'errors' => ['Succesfully Verified with no record found'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }
}

