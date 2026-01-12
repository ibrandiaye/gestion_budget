<?php

namespace App\Http\Controllers;

use App\Models\Ajustement;
use App\Models\Budget;
use App\Models\LigneBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjustementController extends Controller
{
    public function create()
    {
        // Get active budget lines
        $activeBudget = Budget::where('statut', 'actif')->first();

        if (!$activeBudget) {
            return redirect()->back()->withErrors(['msg' => 'Aucun budget actif pour effectuer des virements.']);
        }

        $lignes = LigneBudget::with(['compte', 'service'])
            ->where('budget_id', $activeBudget->id)
            ->get();

        return view('ajustements.create', compact('lignes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_ligne_id' => 'required|exists:lignes_budget,id|different:dest_ligne_id',
            'dest_ligne_id' => 'required|exists:lignes_budget,id',
            'montant' => 'required|numeric|min:1',
            'motif' => 'required|string|max:255',
        ]);

        $source = LigneBudget::findOrFail($request->source_ligne_id);
        $dest = LigneBudget::findOrFail($request->dest_ligne_id);
        $budget = $source->budget; // Assuming same budget

        // Check availability
        if ($source->solde_disponible < $request->montant) {
            return back()->withErrors(['montant' => 'Solde insuffisant sur la ligne source. Disponible: ' . $source->solde_disponible]);
        }

        DB::transaction(function () use ($request, $source, $dest, $budget) {
            // Record Adjustment
            Ajustement::create([
                'budget_id' => $budget->id,
                'source_ligne_id' => $source->id,
                'dest_ligne_id' => $dest->id,
                'montant' => $request->montant,
                'date' => now(),
                'motif' => $request->motif,
            ]);

            // Update Balances
            $source->decrement('montant_alloue', $request->montant);
            $dest->increment('montant_alloue', $request->montant);
        });

        return back()->with('success', 'Virement de crédit effectué avec succès.');
    }
}
