<?php

namespace App\Http\Controllers;

use App\Models\Execution;
use App\Models\LigneBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExecutionController extends Controller
{
    public function index()
    {
        $query = LigneBudget::with(['compte', 'service', 'budget', 'compte.besoins' => function($q) {
            $q->where('statut', 'valide');
        }]);

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('service_id', Auth::user()->service_id);
            $query->with(['compte.besoins' => function($q) {
                $q->where('statut', 'valide')
                  ->where('service_id', Auth::user()->service_id);
            }]);
        }

        $lignesRaw = $query->get();

        // Group by Account (Compte)
        $comptes = $lignesRaw->groupBy('compte_id')->map(function ($items) {
            $compte = $items->first()->compte;
            
            return (object) [
                'compte' => $compte,
                'total_alloue' => $items->sum('montant_alloue'),
                'total_engage' => $items->sum('montant_engage'),
                'solde_disponible' => $items->sum('montant_alloue') - $items->sum('montant_engage'),
                'lignes' => $items // Keep original lines for service selection in modal
            ];
        });

        return view('execution.index', compact('comptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ligne_budget_id' => 'required|exists:lignes_budget,id',
            'items' => 'required|array|min:1',
            'items.*.besoin_id' => 'required|exists:besoins,id',
            'items.*.quantite' => 'required|integer|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $ligne = LigneBudget::findOrFail($request->ligne_budget_id);
        
        // Calculate global total and prepare details
        $totalMontant = 0;
        $itemsToProcess = [];

        foreach ($request->items as $itemData) {
            $besoin = \App\Models\Besoin::findOrFail($itemData['besoin_id']);
            
            // Security check
            if ($besoin->compte_id !== $ligne->compte_id || $besoin->service_id !== $ligne->service_id) {
                continue; // Or abort
            }

            $itemMontant = $itemData['quantite'] * ($besoin->prix_unitaire ?? 0);
            $totalMontant += $itemMontant;
            
            $itemsToProcess[] = [
                'besoin_id' => $besoin->id,
                'quantite' => $itemData['quantite'],
                'prix_unitaire' => $besoin->prix_unitaire ?? 0,
                'montant' => $itemMontant
            ];
        }

        if (empty($itemsToProcess)) {
            return back()->withErrors(['msg' => "Aucun article valide sélectionné."]);
        }

        // Check Availability
        $disponible = $ligne->montant_alloue - $ligne->montant_engage;

        if ($totalMontant > $disponible) {
            return back()->withErrors(['montant' => "Montant insuffisant. Total Requis: " . number_format($totalMontant, 0, ',', ' ') . " FCFA, Disponible: " . number_format($disponible, 0, ',', ' ') . " FCFA"]);
        }

        \DB::transaction(function() use ($ligne, $request, $totalMontant, $itemsToProcess) {
            // Create Execution (Engagement)
            $execution = Execution::create([
                'ligne_budget_id' => $ligne->id,
                'montant' => $totalMontant,
                'date' => $request->date,
                'type' => 'engagement',
                'description' => $request->description ?? ("Dépense groupée (" . count($itemsToProcess) . " articles)"),
            ]);

            // Save Details
            foreach ($itemsToProcess as $item) {
                $execution->details()->create($item);
            }

            // Update Ligne stats
            $ligne->increment('montant_engage', $totalMontant);
        });

        return back()->with('success', 'Dépense groupée engagée avec succès.');
    }

    public function history()
    {
        $query = Execution::with(['ligneBudget.compte', 'ligneBudget.service', 'ligneBudget.budget'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if (Auth::user()->role !== 'admin') {
            $query->whereHas('ligneBudget', function ($q) {
                $q->where('service_id', Auth::user()->service_id);
            });
        }

        $executions = $query->paginate(20);

        return view('execution.history', compact('executions'));
    }

    public function invoice(Execution $execution)
    {
        $execution->load(['ligneBudget.compte', 'ligneBudget.service', 'ligneBudget.budget', 'details.besoin']);
        
        // Authorization check
        if (Auth::user()->role !== 'admin' && Auth::user()->service_id !== $execution->ligneBudget->service_id) {
            abort(403, "Vous n'êtes pas autorisé à voir cette facture.");
        }

        return view('execution.invoice', compact('execution'));
    }
}
