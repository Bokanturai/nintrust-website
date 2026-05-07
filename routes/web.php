<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentSyncController;
use App\Http\Controllers\IpeController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ScratchCardController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SuspendedNinController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserScratchCardController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Requests\LoginRequest;

use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('welcome');
});

Route::post('/palmpay/webhook', [PaymentWebhookController::class, 'handlePalmPay']);

Route::post('/update-bvn-enrollment-status', [EnrollmentSyncController::class, 'updateStatus']);

// Route::group(['as' => 'auth.', 'prefix' => 'auth', 'middleware' => 'guest'], function () {
//     Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
//     Route::post('login', [AuthController::class, 'login']);
//     Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');

//     Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');

//     Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
//     Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

//     Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
//     Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
// });

Route::group(['as' => 'auth.', 'prefix' => 'auth', 'middleware' => 'guest'], function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
// User Routes
Route::middleware(['auth', 'user.active'])->group(function () {
    // User dashboard
    Route::group(['as' => 'user.', 'prefix' => 'user'], function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/verify-user', [VerificationController::class, 'verifyUser'])->name('verify-user');

        Route::middleware(['user.active'])->group(function () {
            Route::get('/verify-nin', [VerificationController::class, 'ninVerify'])->name('verify-nin');
            Route::get('/verify-nin2', [VerificationController::class, 'ninVerify2'])->name('verify-nin2');
            Route::get('/verify-nin-phone', [VerificationController::class, 'phoneVerify'])->name('verify-nin-phone');
            Route::get('/verify-bvn', [VerificationController::class, 'bvnVerify'])->name('verify-bvn');
            Route::get('/nin-personalize', [VerificationController::class, 'ninPersonalize'])->name('personalize-nin');
            Route::get('/ipe', [VerificationController::class, 'showIpe'])->name('ipe');
            Route::get('/bvn-enrollment', [EnrollmentController::class, 'bvnEnrollment'])->name('bvn-enrollment');
            Route::get('/verify-demo', [VerificationController::class, 'demoVerify'])->name('verify-demo');

            // Ipe request

            Route::post('/ipe-request', [VerificationController::class, 'ipeRequest'])->name('ipe-request');

            Route::get('/ipeStatus/{id}', [VerificationController::class, 'ipeRequestStatus'])->name('ipeStatus');

            // Enrollment-----------------------------------------------------------------------------------------------------
            Route::post('/bvn-enrollment', [EnrollmentController::class, 'enrollBVN'])->name('enroll-bvn');
            // Wallet
            Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
            Route::get('claim-bonus/{id}', [WalletController::class, 'claimBonus'])->name('claim-bonus');

            // Transactions -----------------------------------------------------------------------------------------------------
            Route::get('/receipt/{referenceId}', [TransactionController::class, 'reciept'])->name('reciept');

            // Verification-----------------------------------------------------------------------------------------------------
            // NIN
            Route::post('/nin-retrieve', [VerificationController::class, 'ninRetrieve'])->name('ninRetrieve');
            Route::post('/nin-v2-retrieve', [VerificationController::class, 'ninV2Retrieve'])->name('nin-v2-Retrieve');
            Route::post('/nin-demo-retrieve', [VerificationController::class, 'ninDemoRetrieve'])->name('nin-demo-Retrieve');
            Route::post('/nin-phone-retrieve', [VerificationController::class, 'ninPhoneRetrieve'])->name('ninPhoneRetrieve');
            Route::post('/nin-track-retrieve', [VerificationController::class, 'ninTrackRetrieve'])->name('ninTrackRetrieve');

            // BVN
            Route::post('/bvn-retrieve', [VerificationController::class, 'bvnRetrieve'])->name('bvnRetrieve');

            // PDF Downloads -----------------------------------------------------------------------------------------------------
            Route::get('/standardBVN/{id}', [VerificationController::class, 'standardBVN'])->name('standardBVN');
            Route::get('/premiumBVN/{id}', [VerificationController::class, 'premiumBVN'])->name('premiumBVN');
            Route::get('/plasticBVN/{id}', [VerificationController::class, 'plasticBVN'])->name('plasticBVN');

            Route::get('/regularSlip/{id}', [VerificationController::class, 'regularSlip'])->name('regularSlip');
            Route::get('/standardSlip/{id}', [VerificationController::class, 'standardSlip'])->name('standardSlip');
            Route::get('/premiumSlip/{id}', [VerificationController::class, 'premiumSlip'])->name('premiumSlip');
            Route::get('/basicSlip/{id}', [VerificationController::class, 'basicSlip'])->name('basicSlip');

            // NIN Services
            Route::get('/nin-services', [ServicesController::class, 'ninServices'])->name('nin.services');
            Route::post('/nin-services/request', [ServicesController::class, 'requestNinService'])->name('nin.services.request');

            // Airtime and Data
            // Route::get('/airtime', [UtilityController::class, 'airtime'])->name('airtime');
            // Route::post('/buy-airtime', [UtilityController::class, 'buyAirtime'])->name('buyairtime');

            // Route::get('/variation', [UtilityController::class, 'getVariation'])->name('variation');

            // Route::get('/data', [UtilityController::class, 'data'])->name('data');
            // Route::post('/buy-data', [UtilityController::class, 'buydata'])->name('buydata');
            // Route::get('/fetch-data-bundles', [UtilityController::class, 'fetchBundles']);
            // Route::get('/fetch-data-bundles-price', [UtilityController::class, 'fetchBundlePrice']);

            Route::get('/verify-bvn2', [VerificationController::class, 'bvnPhoneVerify'])->name('verify-bvn2');
            Route::post('/bvn-retrieve2', [VerificationController::class, 'bvnPhoneRetrieve'])->name('bvnRetrieve2');

            Route::get('/nin-mod', [ServicesController::class, 'ninModification'])->name('nin.mod');
            Route::post('/nin-services/mod', [ServicesController::class, 'requestModification'])->name('nin.services.mod');

            Route::get('/bvn-phone-search', [VerificationController::class, 'bvnPhoneSearch'])->name('bvn-phone-search');
            Route::post('bvn-phone-search', [VerificationController::class, 'bvnPhoneRequest'])->name('bvn-phone-request');

            Route::get('nin/suspended', [SuspendedNinController::class, 'index'])->name('suspended-nin.form');
            Route::post('nin/suspended/store', [SuspendedNinController::class, 'store'])->name('suspended-nin.store');


            // Whatsapp API Support--------------------------------------------------------------------------
            Route::get('/support', function () {
                $phoneNumber = env('phoneNumber');
                $message = urlencode(env('message'));
                $url = env('API_URL')."{$phoneNumber}&text={$message}";

                return redirect($url);
            })->name('support');
        });

        Route::get('/profile', function () {
            return view('user.profile');
        })->name('profile');

        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

        Route::get('scratch-cards', [UserScratchCardController::class, 'index'])
            ->name('scratch_cards.index');

        Route::post('scratch-cards/purchase', [UserScratchCardController::class, 'purchase'])
            ->name('scratch_cards.purchase');
        Route::get('user/scratch-cards/{card}/download', [UserScratchCardController::class, 'download'])->name('scratch_cards.download');

    });

    // Logout Route
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'user.active', 'user.admin']], function () {

    Route::get('/receipt/{referenceId}', [TransactionController::class, 'recieptAdmin'])->name('reciept');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('user.activate');

    Route::get('/view-ipe-request/{id}/edit', [IpeController::class, 'showIpeRequest'])->name('ipe.view-request');
    Route::post('/requests/ipe/{id}/update-status', [IpeController::class, 'updateIpeStatus'])->name('ipe.update-request-status');

    Route::get('/bvn-services', [ServicesController::class, 'bvnServicesList'])->name('bvn.services.list');
    Route::post('/requests/{id}/{type}/update-bvn-status', [ServicesController::class, 'updateBvnRequestStatus'])->name('bvn-update-request-status');
    Route::get('/view-bvn-request/{id}/{type}/edit', [ServicesController::class, 'showBvnRequests'])->name('bvn-view-request');

    Route::get('/transactions', [TransactionController::class, 'transactions'])->name('transactions');

    // BVN User Rerquest
    Route::get('/enrollment-list', [EnrollmentController::class, 'index'])->name('enroll.index');
    Route::post('/requests/{id}/{type}/update-status2', [EnrollmentController::class, 'updateRequestStatus'])->name('update-request-status2');
    Route::get('/view-request2/{id}/{type}/edit', [EnrollmentController::class, 'showRequests'])->name('view-request2');

    // Services
    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::get('/services/edit/{id}', [ServicesController::class, 'edit'])->name('services.edit');
    Route::put('/services/update/{id}', [ServicesController::class, 'update'])->name('services.update');

    // NIN Services
    Route::get('/nin-services', [ServicesController::class, 'ninServicesList'])->name('nin.services.list');
    Route::post('/requests/{id}/{type}/update-status', [ServicesController::class, 'updateRequestStatus'])->name('update-request-status');
    Route::get('/view-request/{id}/{type}/edit', [ServicesController::class, 'showRequests'])->name('view-request');

    Route::get('/mod-services', [ServicesController::class, 'modServicesList'])->name('mod.services.list');
    Route::post('/requests/{id}/{type}/update-mod-status', [ServicesController::class, 'updateModRequestStatus'])->name('mod-update-request-status');
    Route::get('/view-mod-request/{id}/{type}/edit', [ServicesController::class, 'showModRequests'])->name('mod-view-request');



    Route::get('/scratch-cards', [ScratchCardController::class, 'index'])->name('scratch_cards.index');
    Route::get('/scratch-cards/create', [ScratchCardController::class, 'create'])->name('scratch_cards.create');
    Route::post('/scratch-cards', [ScratchCardController::class, 'store'])->name('scratch_cards.store');
    Route::patch('/scratch-cards/{card}/activate', [ScratchCardController::class, 'activate'])->name('scratch_cards.activate');

    Route::get('/scratch-cards/{card}/edit', [ScratchCardController::class, 'edit'])->name('scratch_cards.edit');
    Route::put('/scratch-cards/{card}', [ScratchCardController::class, 'update'])->name('scratch_cards.update');

    Route::get('ipe-index', [IpeController::class, 'ipeIndex'])->name('ipe.index');
    Route::get('ipe/download-template', [IpeController::class, 'downloadTemplateIPE'])->name('ipe.download-template');
    Route::post('ipe/upload-excel', [IpeController::class, 'uploadExcelIPE'])->name('ipe.upload-excel');
    Route::get('/ipe/refund-failed', [IpeController::class, 'refundFailedTransactions'])->name('ipe.refund');

    Route::get('/suspended-nin-list', [SuspendedNinController::class, 'list'])->name('suspended-nin.index');
    Route::get('/view-suspended-nin/{id}/{type}/edit', [SuspendedNinController::class, 'showRequests'])->name('suspended-nin.view');
    Route::post('/requests/{id}/{type}/update-suspended-status', [SuspendedNinController::class, 'updateRequestStatus'])->name('update-suspended-nin-status');

});
