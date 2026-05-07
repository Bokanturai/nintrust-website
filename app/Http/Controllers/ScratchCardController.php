<?php

namespace App\Http\Controllers;

use App\Models\ScratchCard;
use Illuminate\Http\Request;

class ScratchCardController extends Controller
{
    public function index(Request $request)
    {

        $query = ScratchCard::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('pin', 'like', "%{$search}%")
                     ->orWhere('refno', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $cards = $query->latest()->paginate(20)->withQueryString();

        $baseQuery = ScratchCard::query();

        $total = $baseQuery->count();
        $feeTotal = $baseQuery->sum('fee');

        $available = (clone $baseQuery)->where('status', 'available')->count();
        $feeAvailable = (clone $baseQuery)->where('status', 'available')->sum('fee');

        $purchased = (clone $baseQuery)->where('status', 'purchased')->count();
        $feePurchased = (clone $baseQuery)->where('status', 'purchased')->sum('fee');

        $types = ScratchCard::select('type')->distinct()->pluck('type');

        return view('admin.scratch_cards.index', compact(
            'cards',
            'types',
            'total',
            'feeTotal',
            'available',
            'feeAvailable',
            'purchased',
            'feePurchased'
        ));
    }

    public function create()
    {
        return view('admin.scratch_cards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'fee' => 'required|numeric|min:0',
            'serial_number' => 'required|string|unique:scratch_cards',
            'pin' => 'required|string|max:100',
        ]);

        ScratchCard::create($validated);

        return redirect()->route('admin.scratch_cards.index')->with('success', 'Scratch card added successfully.');
    }

    public function activate(ScratchCard $card)
    {
        $card->active = ! $card->active;
        $card->save();

        return back()->with('success', 'Scratch card '.($card->active ? 'activated' : 'deactivated').' successfully.');
    }

    public function edit($id)
    {

        $card = ScratchCard::findOrFail($id);

        return view('admin.scratch_cards.edit', compact('card'));
    }

    public function update(Request $request, ScratchCard $card)
    {

        $validated = $request->validate([
            // 'type' => 'required|string|max:50',
            'fee' => 'required|numeric|min:0',
            'serial_number' => 'required|string|max:100',
            'pin' => 'required|string|max:100',
        ]);

        $card->fill($request->only([
            'fee',
            'serial_number',
            'pin',
        ]));

        $card->save();

        return redirect()->route('admin.scratch_cards.index', compact('card'))->with('success', 'Scratch Card updated successfully.');
    }
}
