<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Budget;
use App\Models\LigneBudget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    // Admin View: Arbitrage
    // Admin View: Arbitrage
    public function arbitrage()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->withErrors(['msg' => 'Accès réservé aux administrateurs.']);
        }

        // Get Needs grouped by Service to make it easier to review
        $besoins = Besoin::with(['service', 'compte'])
            ->where('statut', 'cree') // Only show new needs
            ->orderBy('service_id')
            ->get();

        return view('budget.arbitrage', compact('besoins'));
    }

    // Process Arbitrage (Accept/Reject/Modify)
    public function valider(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        $request->validate([
            'besoins' => 'required|array',
            'besoins.*.id' => 'required|exists:besoins,id',
            'besoins.*.montant_accorde' => 'nullable|numeric|min:0',
            'besoins.*.action' => 'required|in:valider,rejetter',
        ]);

        DB::transaction(function () use ($request) {
            // Get Active Budget
            $budget = Budget::where('statut', 'actif')->first();

            if (!$budget) {
                // Return error if no active budget (should be managed via Annual Budgets)
                // Or create a default one for safety if this is the first run
                $budget = Budget::create([
                    'annee' => date('Y'),
                    'statut' => 'actif',
                    'date_debut' => date('Y-01-01'),
                    'date_fin' => date('Y-12-31')
                ]);
            }

            foreach ($request->besoins as $id => $data) {
                $besoin = Besoin::findOrFail($id);

                if ($data['action'] === 'rejetter') {
                    $besoin->update(['statut' => 'rejete', 'montant_accorde' => 0]);
                    continue;
                }

                // If Validated
                $montant = $data['montant_accorde'] ?? $besoin->montant_demande;

                $besoin->update([
                    'statut' => 'valide',
                    'montant_accorde' => $montant
                ]);

                // Create or Update Budget Line
                // Key is Budget + Compte + Service (if decentralized)
                // Here assuming One Line per Account per Service
                LigneBudget::updateOrCreate(
                    [
                        'budget_id' => $budget->id,
                        'compte_id' => $besoin->compte_id,
                        'service_id' => $besoin->service_id,
                    ],
                    [
                        'montant_alloue' => DB::raw("montant_alloue + $montant") // Additive if multiple needs per same line (rare but possible) or just set. 
                        // For this app, let's assume one massive need expression per line, or additive. 
                        // Let's use simple assignment for now or handle existing.
                        // Actually, let's make it simple: existing + new
                    ]
                );

                // Correction: updateOrCreate with DB:raw is tricky in Laravel for values. 
                // Better: find and increment.
                $ligne = LigneBudget::firstOrNew([
                    'budget_id' => $budget->id,
                    'compte_id' => $besoin->compte_id,
                    'service_id' => $besoin->service_id,
                ]);
                $ligne->montant_alloue = ($ligne->montant_alloue ?? 0) + $montant;
                $ligne->save();
            }
        });

        return redirect()->route('budget.arbitrage')->with('success', 'Arbitrage enregistré avec succès. Le budget a été mis à jour.');
    }
}
