<?php

namespace App\Http\Controllers;

use App\Models\ScratchCard;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserScratchCardController extends Controller
{
    protected $transactionService;

    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function index()
    {

        $availableCards = ScratchCard::where('status', 'available')
            ->where('active', '1')
            ->select('type', 'fee', DB::raw('COUNT(*) as available_count'))
            ->groupBy('type', 'fee')
            ->orderBy('type')
            ->get();

        $purchasedCards = ScratchCard::where('user_id', Auth::id())
            ->where('status', 'purchased')
            ->latest()
            ->paginate(10);

        return view('user.scratch_cards.index', compact('availableCards', 'purchasedCards'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:0',
        ]);

        $quantities = collect($request->quantities)
            ->filter(fn ($q) => $q > 0);

        if ($quantities->isEmpty()) {
            return back()
                ->withErrors(['quantities' => 'Please select at least one quantity to purchase.'])
                ->withInput();
        }

        $purchased = [];

        try {

            DB::transaction(function () use ($quantities, &$purchased) {
                $userId = $this->loginId;
                $wallet = Wallet::lockForUpdate()->where('user_id', $userId)->first();

                if (! $wallet) {
                    abort(400, 'No wallet found for your account. Please contact support.');
                }

                $totalCost = 0;
                foreach ($quantities as $key => $quantity) {
                    [$type, $fee] = explode('_', $key);
                    $totalCost += ($quantity * $fee);
                }

                if ($wallet->balance < $totalCost) {
                    throw new \Exception('Insufficient wallet balance. You need ₦'.number_format($totalCost, 2));
                }

                $wallet->decrement('balance', $totalCost);

                $serviceDesc = 'Wallet debited with ₦'.number_format($totalCost, 2).' for scratch card purchase.';

                $transaction = $this->transactionService->createTransaction(
                    $this->loginId,
                    $totalCost,
                    'Scratch Card Purchase',
                    $serviceDesc,
                    'Wallet',
                    'Approved'
                );

                foreach ($quantities as $key => $quantity) {
                    [$type, $fee] = explode('_', $key);

                    $cards = ScratchCard::where('status', 'available')
                        ->where('type', $type)
                        ->where('fee', $fee)
                        ->limit($quantity)
                        ->lockForUpdate()
                        ->get();

                    if ($cards->count() < $quantity) {
                        throw new \Exception("Not enough cards available for $type at ₦$fee.");
                    }

                    foreach ($cards as $card) {
                        $card->update([
                            'status' => 'purchased',
                            'user_id' => $userId,
                            'purchased_at' => now(),
                            'tnx_id' => $transaction->id,
                            'refno'=> $transaction->referenceId
                        ]);
                        $purchased[] = $card;
                    }
                }
            });

        } catch (\Exception $e) {
            return back()
                ->withErrors(['quantities' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('user.scratch_cards.index')
            ->with('success', count($purchased).' card(s) purchased successfully!');
    }

    public function download(ScratchCard $card)
    {
        abort_unless((int) $card->user_id === (int) Auth::id(), 403);

        $filename = "{$card->type}_card_{$card->serial_number}.txt";
        $content = "Type: {$card->type}\nFee: ₦{$card->fee}\nSerial: {$card->serial_number}\nPIN: {$card->pin}\nPurchased: {$card->purchased_at}";

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
