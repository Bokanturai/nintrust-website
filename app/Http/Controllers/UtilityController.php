<?php

namespace App\Http\Controllers;

use App\Http\Helpers\RequestIdHelper;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UtilityController extends Controller
{
    protected $loginUserId;

    // Constructor to initialize the property
    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    public function airtime(Request $request)
    {
        return view('buy-airtime');
    }

    public function buyAirtime(Request $request)
    {

        $request->validate([
            'network' => ['required', 'string', 'in:mtn,airtel,glo,etisalat'],
            'mobileno' => 'required|numeric|digits:11',
            'amount' => 'required|numeric|min:50|max:5000',
        ]);

        $network = $request->network;
        $amount = $request->amount;
        $mobile = $request->mobileno;
        $requestId = RequestIdHelper::generateRequestId();

        // Minimum Purchase Airtime
        $service_code = '';

        // Use a switch-case to set the service code based on the network
        switch (strtolower($network)) {
            case 'mtn':
                $service_code = '122';
                break;
            case 'airtel':
                $service_code = '123';
                break;
            case 'glo':
                $service_code = '124';
                break;
            case 'etisalat':
                $service_code = '125';
                break;
            default:
                // Do nothing
                break;
        }

        // Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', $service_code)
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return redirect()->back()->with('error', 'Service not available at the moment !');
        }

        $ServiceFee = $ServiceFee->amount;

        if ($ServiceFee > $amount) {
            return redirect()->back()->with('error', 'Please note that the minimum amount for airtime purchase on the '.$network.' network is ₦'.$amount);
        }

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $amount) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {
            $response = Http::withHeaders([
                'api-key' => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id' => $requestId,
                'serviceID' => $network,
                'amount' => $amount,
                'phone' => $mobile,
            ]);

            Log::error('Registration error: '.$response);
            if ($response->successful()) {

                $data = $response->json();

                if ($data['code'] == 000) {
                    // Airtime purchase was successful
                    $balance = $wallet->balance - $amount;

                    $affected = Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $payer_name = auth()->user()->name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $requestId,
                        'service_type' => 'Airtime Purchase',
                        'service_description' => strtoupper($network).''.' Airtime purchase of '.number_format($request->amount, 2).' successfully on '.$mobile,
                        'amount' => $request->amount,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $successMessage = strtoupper($network).' Airtime purchase successfully on '.$mobile;

                    // Correctly format the link
                    $link = '<br/> <a href="'.route('user.reciept', $requestId).'"><i class="bi bi-download"></i> Download Receipt</a>';

                    // Use session flash to store the success message with HTML
                    return redirect()->back()->with('success', $successMessage.' '.$link);
                } else {

                    return redirect()->back()->with('error', 'Airtime Purchase Failed');
                }
            } else {

                return redirect()->back()->with('error', 'Failed to purchase airtime. Please try again later.');
            }
        }
    }

    public function data(Request $request)
    {

        $servicename = DB::table('data_variations')
            ->select(['service_id', 'service_name'])
            ->where('status', 'enabled')
            ->distinct()
            ->limit(6)
            ->get();

        return view('buy-data', ['servicename' => $servicename]);
    }

    public function getVariation()
    {

        $types = ['mtn-data', 'airtel-data', 'glo-data', 'etisalat-data'];
        $successCount = 0;
        $failedTypes = [];

        try {

            DB::table('data_variations')->truncate();
            Log::info('Truncated data_variations table before inserting new data.');

            foreach ($types as $type) {

                $response = Http::get(env('VARIATION_URL').$type);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['content'])) {
                        $service_name = $data['content']['ServiceName'] ?? null;
                        $service_id = $data['content']['serviceID'] ?? null;
                        $convenience_fee = $data['content']['convinience_fee'] ?? null;

                        $insertData = [];

                        foreach ($data['content']['varations'] as $variation) {
                            $insertData[] = [
                                'variation_code' => $variation['variation_code'],
                                'service_name' => $service_name,
                                'service_id' => $service_id,
                                'convinience_fee' => $convenience_fee,
                                'name' => $variation['name'],
                                'variation_amount' => $variation['variation_amount'],
                                'fixedPrice' => $variation['fixedPrice'],
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                        }

                        DB::table('data_variations')->insert($insertData);
                        $successCount++;
                        Log::info("Successfully inserted variations for: $type");
                    }
                } else {
                    $failedTypes[] = $type;
                    Log::error("Failed to fetch variation for: $type. Response: ".$response->body());
                }
            }

            if ($successCount > 0) {
                Session::flash('success', "$successCount variations updated successfully.");
            }

            if (! empty($failedTypes)) {
                Session::flash('error', 'Failed to fetch variations for: '.implode(', ', $failedTypes));
            }
        } catch (\Exception $e) {
            Log::error('Error in getVariation(): '.$e->getMessage());
            Session::flash('error', 'An error occurred while updating variations.');
        }

        return redirect()->back();
    }

    public function buydata(Request $request)
    {

        $request->validate([
            'network' => ['required', 'string', 'in:airtel-data,mtn-data,glo-data,etisalat-data,spectranet,smile-direct'],
            'mobileno' => 'required|numeric|digits:11',
        ]);

        $requestId = RequestIdHelper::generateRequestId();

        // Get service fee
        $fee = DB::table('data_variations')
            ->where('variation_code', $request->bundle)->value('variation_amount');

        $getDescriotion = DB::table('data_variations')
            ->where('variation_code', $request->bundle)->first();

        $description = $getDescriotion->name;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $fee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $response = Http::withHeaders([
                'api-key' => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id' => $requestId,
                'serviceID' => $request->network,
                'billersCode' => env('BIILER_CODE'),
                'variation_code' => $request->bundle,
                'phone' => $request->mobileno,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['code'] == 000) {
                    // Airtime purchase was successful
                    $balance = $wallet->balance - $fee;

                    $affected = Wallet::where('user_id', $this->loginUserId)
                        ->update(['balance' => $balance]);

                    $payer_name = auth()->user()->name;
                    $payer_email = auth()->user()->email;
                    $payer_phone = auth()->user()->phone_number;

                    Transaction::create([
                        'user_id' => $this->loginUserId,
                        'payer_name' => $payer_name,
                        'payer_email' => $payer_email,
                        'payer_phone' => $payer_phone,
                        'referenceId' => $requestId,
                        'service_type' => 'Data Purchase',
                        'service_description' => ' Data purchase of '.$description.' successfully on '.$request->mobileno,
                        'amount' => $fee,
                        'gateway' => 'Wallet',
                        'status' => 'Approved',
                    ]);

                    $successMessage = 'Data purchase successfully on '.$request->mobileno;
                    // Correctly format the link
                    $link = '<br /> <a href="'.route('user.reciept', $requestId).'"><i class="bi bi-download"></i>
                                   Download Receipt</a>';

                    // Use session flash to store the success message with HTML
                    return redirect()->back()->with('success', $successMessage.' '.$link);
                } else {
                    return redirect()->back()->with('error', 'Data Purchase Failed. Please try again later.');
                }
            } else {

                return redirect()->back()->with('error', 'Failed to purchase data bundle. Please try again later.');
            }
        }
    }

    public function fetchBundles(Request $request)
    {

        $bundles = DB::table('data_variations')
            ->select(['name', 'variation_code'])
            ->where('service_id', $request->id)
            ->where('status', 'enabled')
            ->get();

        return response()->json($bundles);
    }

    public function fetchBundlePrice(Request $request)
    {

        $priceCollection = DB::table('data_variations')
            ->select('variation_amount')
            ->where('variation_code', $request->id)
            ->get();

        $price = $priceCollection->first()->variation_amount;
        $formattedPrice = number_format((float) $price, 2);

        return response()->json($formattedPrice);
    }
}
