<?php

namespace App\Http\Controllers;

use App\Models\BvnPhoneSearch;
use App\Models\Enrollment;
use App\Models\IpeRequest;
use App\Models\NinModification;
use App\Models\NinService;
use App\Models\ScratchCard;
use App\Models\SuspendedNinRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VninSlipRequest;
use App\Models\PersonalizeNinRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {

        $status = auth()->user()->kyc_status;

        $kycPending = session('kyc_pending', false);

        // if ($status == 'Pending') {
        //     $kycPending = true;
        // }

        if (auth()->user()->role == 'admin') {
            $totalRevenue = Transaction::where('status', 'Approved')->sum(DB::raw('CAST(amount AS DECIMAL(15,2))'));

            $totalUsers = User::count();

            $approvedToday = DB::table('transactions')
                ->where('status', 'Approved')
                ->whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                ->value('total');

            $ninModifications = NinModification::whereIn('status', ['pending'])->count();

            $bvnSearch = BvnPhoneSearch::whereIn('status', ['pending'])->count();

            $validationPending = NinService::where('status', 'pending')->count();

            $cards = ScratchCard::where('active', 1)->whereIn('status', ['available'])->count();

             $enrollmentsCount = Enrollment::whereIn('status', ['submitted', 'procesing'])
                ->count();

            $totalWalletBalance = DB::table('wallets')->selectRaw('SUM(balance) as total')->value('total');
            $totalBonusBalance = DB::table('bonus_histories')->selectRaw('SUM(amount) as total')->value('total');
            $ipePending = IpeRequest::whereIn('resp_code', ['100', '101'])->count();
             $suspendedNinPending = SuspendedNinRequest::whereIn('status', ['submitted', 'processing'])->count();
             $vninSlipPending = VninSlipRequest::whereIn('status', ['submitted', 'processing'])->count();
             $personalizeNinPending = PersonalizeNinRequest::whereIn('status', ['submitted', 'processing'])->count();
            $metrics = [
                [
                    'title' => 'Total Revenue',
                    'value' => '₦'.number_format($totalRevenue, 2),
                    'icon' => 'bi-cash-stack',
                    'bg' => 'success',
                    'href' => '#',
                ],
                [
                    'title' => 'Total Wallet Balance',
                    'value' => '₦'.number_format($totalWalletBalance, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'warning',
                    'href' => '#',
                ],
                [
                    'title' => 'Total Bonus Balance',
                    'value' => '₦'.number_format($totalBonusBalance, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'info',
                    'href' => '#',
                ],
                [
                    'title' => 'Funding Today',
                    'value' => '₦'.number_format($approvedToday, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'primary',
                    'href' => '#',
                ],
                [
                    'title' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'icon' => 'bi-people-fill',
                    'bg' => 'danger',
                    'href' => 'admin.users.index',
                ],
                [
                    'title' => 'NIN Modifications',
                    'value' => number_format($ninModifications),
                    'icon' => 'bi-fingerprint',
                    'bg' => 'dark',
                    'href' => 'admin.mod.services.list',
                ],
                 [
                    'title' => 'Suspended NIN',
                    'value' => number_format($suspendedNinPending),
                    'icon' => 'bi bi-person-slash',
                    'bg' => 'danger',
                    'href' => 'admin.suspended-nin.index',
                ],
                [
                    'title' => 'BVN Search (Phone)',
                    'value' => number_format($bvnSearch),
                    'icon' => 'bi-search',
                    'bg' => 'primary',
                    'href' => 'admin.bvn.services.list',
                ],
                [
                    'title' => 'IPE Clearance',
                    'value' => number_format($ipePending),
                    'icon' => 'bi-check2-circle',
                    'bg' => 'warning',
                    'href' => 'admin.ipe.index',
                ],
                [
                    'title' => 'NIN Validation',
                    'value' => number_format($validationPending),
                    'icon' => 'bi-check2-circle',
                    'bg' => 'warning',
                    'href' => 'admin.nin.services.list',
                ],
                 [
                    'title' => 'BVN User',
                    'value' => number_format($enrollmentsCount),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'primary',
                    'href' => 'admin.enroll.index',
                ],
                [
                    'title' => 'Scratch Cards',
                    'value' => $cards,
                    'icon' => 'bi-check-circle',
                    'bg' => 'success',
                    'href' => 'admin.scratch_cards.index',
                ],
                [
                    'title' => 'VNIN Manual',
                    'value' => number_format($vninSlipPending),
                    'icon' => 'bi-card-text',
                    'bg' => 'info',
                    'href' => 'admin.vnin-slip.index',
                ],
                [
                    'title' => 'Personalize NIN',
                    'value' => number_format($personalizeNinPending),
                    'icon' => 'bi-search',
                    'bg' => 'primary',
                    'href' => 'admin.personalize-nin.index',
                ],
            ];

            $depositChartData = [
                'Approved' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Approved')->sum('amount'),
                'Pending' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Pending')->sum('amount'),
                'Rejected' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Rejected')->sum('amount'),
            ];

            $depositChartData = [
                'Funding' => DB::table('transactions')
                    ->where('status', 'Approved')
                    ->whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                    ->value('total'),

                'Expenses' => DB::table('transactions')
                    ->where('status', 'Approved')
                    ->whereNotIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                    ->value('total'),

            ];

            $topFunders = DB::table('transactions as t')
                ->join('users as u', 't.user_id', '=', 'u.id')
                ->where('t.status', 'Approved')
                ->whereIn('t.service_type', ['Wallet Topup', 'Admin Top Up'])
                ->whereBetween('t.created_at', [now()->startOfDay(), now()->endOfDay()])
                ->select(
                    'u.name',
                    'u.email',
                    DB::raw('SUM(CAST(t.amount AS DECIMAL(15,2))) as total_funding')
                )
                ->groupBy('u.id', 'u.name', 'u.email')
                ->orderByDesc('total_funding')
                ->limit(5)
                ->get();

        }

        return view('user.dashboard', [
            'kycPending' => $kycPending,
            'status' => $status,
            'metrics' => $metrics ?? null,
            'depositChartData' => $depositChartData ?? null,
            'topFunders' => $topFunders ?? collect(),
        ]);
    }
}
