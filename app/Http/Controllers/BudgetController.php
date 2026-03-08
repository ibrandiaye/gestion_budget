<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Budget;
use App\Models\Compte;
use App\Models\LigneBudget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    // Admin View: Arbitrage
    // Admin View: Arbitrage
    public function arbitrage(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->withErrors(['msg' => 'Accès réservé aux administrateurs.']);
        }

        $compteId = $request->query('compte_id');

        if ($compteId) {
            // View specific account needs
            $compte = Compte::findOrFail($compteId);
            $besoins = Besoin::with(['service'])
                ->where('compte_id', $compteId)
                ->whereIn('statut', ['cree', 'valide', 'rejete'])
                ->where('budget_id', Budget::where('statut', 'actif')->first()?->id)
                ->get();

            if ($besoins->isEmpty()) {
                return redirect()->route('budget.arbitrage')->with('info', "Aucun besoin trouvé pour ce compte sur l'exercice en cours.");
            }

            // Calculate total estimated needs based on account reference price
            $activeBudget = Budget::where('statut', 'actif')->first();
            $prixRef = $compte->getPrixUnitaireRefForBudget($activeBudget ? $activeBudget->id : 0);
            $totalEstimation = $besoins->sum('quantite') * $prixRef;

            return view('budget.arbitrage_detail', compact('compte', 'besoins', 'totalEstimation'));
        }

        // View list of accounts with needs for the active budget
    $budgetActif = Budget::where('statut', 'actif')->first();
    $comptes = Compte::whereHas('besoins', function($query) use ($budgetActif) {
        $query->whereIn('statut', ['cree', 'valide', 'rejete']);
        if ($budgetActif) $query->where('budget_id', $budgetActif->id);
    })
    ->withCount(['besoins' => function($query) use ($budgetActif) {
        $query->whereIn('statut', ['cree', 'valide', 'rejete']);
        if ($budgetActif) $query->where('budget_id', $budgetActif->id);
    }])
    ->withCount(['besoins as besoins_en_attente_count' => function($query) use ($budgetActif) {
        $query->where('statut', 'cree');
        if ($budgetActif) $query->where('budget_id', $budgetActif->id);
    }])
    ->withSum(['besoins as total_quantite' => function($query) use ($budgetActif) {
        $query->whereIn('statut', ['cree', 'valide', 'rejete']);
        if ($budgetActif) $query->where('budget_id', $budgetActif->id);
    }], 'quantite')
    ->get();

    return view('budget.arbitrage', compact('comptes'));
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
            'besoins.*.quantite' => 'nullable|integer|min:0',
            'besoins.*.prix_unitaire' => 'nullable|numeric|min:0',
            'besoins.*.action' => 'required|in:valider,rejetter',
        ]);

        DB::transaction(function () use ($request) {
            // Get Active Budget
            $budget = Budget::where('statut', 'actif')->first();

            if (!$budget) {
                // If no budget, create one? Or fail? Logic suggests creating one here.
                $budget = Budget::create([
                    'annee' => date('Y'),
                    'statut' => 'actif',
                    'date_debut' => date('Y-01-01'),
                    'date_fin' => date('Y-12-31')
                ]);
            }

            // --- VALIDATION START ---
            // Calculate total projected amount for this account
            // We need to fetch all needs for this account, not just the submitted ones, 
            // to ensure the TOTAL sum respects the dotation.
            // However, the request might contain only a subset or all. 
            // A safer approach: fetch the account first.
            
            // Assume all needs in request belong to the same account (or we check first)
            $firstBesoinId = array_key_first($request->besoins);
            $firstBesoin = Besoin::findOrFail($firstBesoinId);
            $compte = Compte::findOrFail($firstBesoin->compte_id);
            
            // Calculate Current Validated Total (excluding needs being updated in this request)
            $existingTotal = Besoin::where('compte_id', $compte->id)
                ->where('budget_id', $budget->id)
                ->where('statut', 'valide')
                ->whereNotIn('id', array_keys($request->besoins))
                ->sum('montant_accorde');

            $newPropsalTotal = 0;
            foreach ($request->besoins as $id => $data) {
                if ($data['action'] === 'valider') {
                    $qte = $data['quantite'] ?? 0;
                    $prix = $data['prix_unitaire'] ?? 0;
                    $newPropsalTotal += ($qte * $prix);
                }
            }

            $projectedTotal = $existingTotal + $newPropsalTotal;

            $dotation = $compte->getDotationForBudget($budget->id);

            if ($dotation > 0 && $projectedTotal > $dotation) {
                $depassement = $projectedTotal - $dotation;
                $message = "Le montant total accordé (" . number_format($projectedTotal, 0, ',', ' ') . " FCFA) dépasse la dotation du compte (" . number_format($dotation, 0, ',', ' ') . " FCFA). Dépassement de : " . number_format($depassement, 0, ',', ' ') . " FCFA.";
                
                throw \Illuminate\Validation\ValidationException::withMessages(['error' => $message]);
            }
            // --- VALIDATION END ---

            foreach ($request->besoins as $id => $data) {
                $besoin = Besoin::findOrFail($id);
                $ancienStatut = $besoin->statut;
                $ancienMontant = $besoin->montant_accorde ?? 0;

                if ($data['action'] === 'rejetter') {
                    $besoin->update(['statut' => 'rejete', 'montant_accorde' => 0]);
                    
                    // If it was valid before, subtract from budget line
                    if ($ancienStatut === 'valide') {
                        $ligne = LigneBudget::where([
                            'budget_id' => $budget->id,
                            'compte_id' => $besoin->compte_id,
                            'service_id' => $besoin->service_id,
                        ])->first();
                        if ($ligne) {
                            $ligne->montant_alloue -= $ancienMontant;
                            $ligne->save();
                        }
                    }
                    continue;
                }

                // If Validated
                $qte = $data['quantite'] ?? $besoin->quantite;
                // Admins set price during arbitration. If it was already validated, we might have a price.
                $prix = $data['prix_unitaire'] ?? 0;
                $montant = $qte * $prix;

                $besoin->update([
                    'statut' => 'valide',
                    'quantite' => $qte,
                    'prix_unitaire' => $prix,
                    'montant_accorde' => $montant
                ]);

                // Create or Update Budget Line
                $ligne = LigneBudget::firstOrNew([
                    'budget_id' => $budget->id,
                    'compte_id' => $besoin->compte_id,
                    'service_id' => $besoin->service_id,
                ]);
                
                // Adjust delta: deduct old if it was valid, add new
                $delta = ($ancienStatut === 'valide') ? ($montant - $ancienMontant) : $montant;
                $ligne->montant_alloue += $delta;
                $ligne->save();
            }
        });

        return redirect()->route('budget.arbitrage')->with('success', 'Arbitrage enregistré avec succès. Le budget a été mis à jour.');
    }
}
