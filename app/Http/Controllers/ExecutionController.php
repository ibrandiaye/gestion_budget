<?php

namespace App\Http\Controllers;

use App\Models\Execution;
use App\Models\LigneBudget;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExecutionController extends Controller
{
    public function index()
    {
        $query = LigneBudget::with(['compte', 'service', 'budget', 'compte.articles' => function($q) {
            $q->where('actif', true);
        }]);

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('service_id', Auth::user()->service_id);
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
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.montant' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'fournisseur' => 'nullable|string|max:255',
        ]);

        $ligne = LigneBudget::with('compte')->findOrFail($request->ligne_budget_id);
        
        // Check if fournisseur is required for marché compte
        if ($ligne->compte->is_marche && empty($request->fournisseur)) {
            return back()->withErrors(['fournisseur' => "Le fournisseur est obligatoire pour les comptes à marché."]);
        }
        
        // Calculate global total and prepare details
        $totalMontant = 0;
        $itemsToProcess = [];

        foreach ($request->items as $itemData) {
            $article = Article::findOrFail($itemData['article_id']);
            
            // Security check
            if ($article->compte_id !== $ligne->compte_id) {
                continue; // Or abort
            }

            $itemMontant = floatval($itemData['montant']);
            $totalMontant += $itemMontant;
            
            $itemsToProcess[] = [
                'article_id' => $article->id,
                'quantite' => $itemData['quantite'],
                'prix_unitaire' => $itemData['quantite'] > 0 ? $itemMontant / $itemData['quantite'] : 0,
                'montant' => $itemMontant,
                'fournisseur' => $request->fournisseur,
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

        return back()->with('success', 'Dépense groupée engagée avec succès.')->with('execution_id', $execution->id);
    }

    public function history()
    {
        $query = Execution::with(['ligneBudget.compte', 'ligneBudget.service', 'ligneBudget.budget', 'details.article'])
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
        $execution->load(['ligneBudget.compte', 'ligneBudget.service', 'ligneBudget.budget', 'details.article']);
        
        // Authorization check
        if (Auth::user()->role !== 'admin' && Auth::user()->service_id !== $execution->ligneBudget->service_id) {
            abort(403, "Vous n'êtes pas autorisé à voir cette facture.");
        }

        return view('execution.invoice', compact('execution'));
    }
}
