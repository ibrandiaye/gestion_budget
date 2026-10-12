<?php

namespace App\Http\Controllers;

use App\Models\Marche;
use App\Models\MarcheArticle;
use App\Models\Compte;
use App\Models\Budget;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class MarcheController extends Controller
{
    public function index()
    {
        $marches = Marche::with(['compte', 'fournisseur'])->latest()->get();
        return view('marches.index', compact('marches'));
    }

    public function create()
    {
        $comptes = Compte::where('is_marche', true)
            ->where('etat', 'actif')
            ->with(['articles' => fn($q) => $q->where('actif', true)])
            ->orderBy('numero')
            ->get();
        $budgets = Budget::all();
        $fournisseurs = Fournisseur::with('articles')->orderBy('nom')->get();
        return view('marches.create', compact('comptes', 'budgets', 'fournisseurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'intitule' => 'required|string|max:255',
            'compte_id' => 'required|exists:comptes,id',
            'budget_id' => 'required|exists:budgets,id',
            'nature' => 'required|string',
            'type' => 'required|string',
            'montant' => 'required|numeric',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
            'articles.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $compte = Compte::findOrFail($request->compte_id);
        if (!$compte->is_marche) {
            return back()->withErrors([
                'compte_id' => "Les lignes budgétaires à engagement direct ne peuvent pas être utilisées pour un marché. Veuillez sélectionner une ligne budgétaire à marché."
            ])->withInput();
        }

        // Calculate total amount securely in the backend
        $montant_calcule = 0;
        foreach ($request->articles as $art) {
            $montant_calcule += $art['quantite'] * $art['prix_unitaire'];
        }

        // Deduce Mode de Passation
        $mode_passation = $this->deduceModePassation($request->type, $montant_calcule);

        $marche = Marche::create([
            'date' => $request->date,
            'intitule' => $request->intitule,
            'compte_id' => $request->compte_id,
            'budget_id' => $request->budget_id,
            'nature' => $request->nature,
            'type' => $request->type,
            'montant' => $montant_calcule,
            'mode_passation' => $mode_passation,
            'fournisseur_id' => $request->fournisseur_id,
        ]);

        foreach ($request->articles as $art) {
            $articleModel = \App\Models\Article::find($art['article_id']);
            $designation = $articleModel ? $articleModel->designation : ($art['designation'] ?? 'Article inconnu');

            $marche->articles()->create([
                'designation' => $designation,
                'conditionnement' => $art['conditionnement'] ?? null,
                'quantite' => $art['quantite'],
                'prix_unitaire' => $art['prix_unitaire'],
                'prix_total' => $art['quantite'] * $art['prix_unitaire'],
            ]);
        }

        return redirect()->route('marches.index')->with('success', 'Marché créé avec succès.');
    }

    public function show(Marche $marche)
    {
        $marche->load(['compte', 'fournisseur', 'articles']);
        return view('marches.show', compact('marche'));
    }

    public function edit(Marche $marche)
    {
        $marche->load(['compte.articles', 'fournisseur', 'articles']);
        $comptes = Compte::where('etat', 'actif')
            ->where(function($q) use ($marche) {
                $q->where('is_marche', true)->orWhere('id', $marche->compte_id);
            })
            ->with(['articles' => fn($q) => $q->where('actif', true)])
            ->orderBy('numero')
            ->get();
        $budgets = Budget::all();
        $fournisseurs = Fournisseur::with('articles')->orderBy('nom')->get();

        // Resolve each marche article's corresponding article_id in the compte (if any)
        foreach ($marche->articles as $art) {
            $matchingArticle = \App\Models\Article::where('compte_id', $marche->compte_id)
                ->where('designation', $art->designation)
                ->first();
            $art->article_id = $matchingArticle ? $matchingArticle->id : null;
        }

        return view('marches.edit', compact('marche', 'comptes', 'budgets', 'fournisseurs'));
    }

    public function update(Request $request, Marche $marche)
    {
        $request->validate([
            'date' => 'required|date',
            'intitule' => 'required|string|max:255',
            'compte_id' => 'required|exists:comptes,id',
            'budget_id' => 'required|exists:budgets,id',
            'nature' => 'required|string',
            'type' => 'required|string',
            'montant' => 'required|numeric',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
            'articles.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $compte = Compte::findOrFail($request->compte_id);
        if (!$compte->is_marche) {
            return back()->withErrors([
                'compte_id' => "Les lignes budgétaires à engagement direct ne peuvent pas être utilisées pour un marché. Veuillez sélectionner une ligne budgétaire à marché."
            ])->withInput();
        }

        $montant_calcule = 0;
        foreach ($request->articles as $art) {
            $montant_calcule += $art['quantite'] * $art['prix_unitaire'];
        }

        $mode_passation = $this->deduceModePassation($request->type, $montant_calcule);

        $marche->update([
            'date' => $request->date,
            'intitule' => $request->intitule,
            'compte_id' => $request->compte_id,
            'budget_id' => $request->budget_id,
            'nature' => $request->nature,
            'type' => $request->type,
            'montant' => $montant_calcule,
            'mode_passation' => $mode_passation,
            'fournisseur_id' => $request->fournisseur_id,
        ]);

        // Recreate articles
        $marche->articles()->delete();
        foreach ($request->articles as $art) {
            $articleModel = \App\Models\Article::find($art['article_id']);
            $designation = $articleModel ? $articleModel->designation : ($art['designation'] ?? 'Article inconnu');

            $marche->articles()->create([
                'designation' => $designation,
                'conditionnement' => $art['conditionnement'] ?? null,
                'quantite' => $art['quantite'],
                'prix_unitaire' => $art['prix_unitaire'],
                'prix_total' => $art['quantite'] * $art['prix_unitaire'],
            ]);
        }

        return redirect()->route('marches.show', $marche)->with('success', 'Marché mis à jour avec succès.');
    }

    public function destroy(Marche $marche)
    {
        if ($marche->commandes()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce marché car il est associé à une ou plusieurs commandes.');
        }

        $marche->articles()->delete();
        $marche->delete();

        return redirect()->route('marches.index')->with('success', 'Marché supprimé avec succès.');
    }

    private function deduceModePassation($type, $montant)
    {
        $montant = (float) $montant;
        
        if (in_array($type, ['Fourniture', 'Service'])) {
            if ($montant < 3000000) return 'DRP Simple';
            if ($montant >= 3000000 && $montant < 15000000) return 'DRP Restreinte';
            if ($montant >= 15000000 && $montant < 50000000) return 'DRP à Compétition Ouverte';
            return 'AO Ouvert';
        }
        
        if ($type == 'Travaux') {
            if ($montant < 5000000) return 'DRP Simple';
            if ($montant >= 5000000 && $montant < 25000000) return 'DRP Restreinte';
            if ($montant >= 25000000 && $montant < 70000000) return 'DRP à Compétition Ouverte';
            return 'AO Ouvert';
        }

        if ($type == 'Prestation Intellectuelle') {
            if ($montant < 25000000) return 'DRP Simple';
            if ($montant >= 25000000 && $montant < 50000000) return 'DRP à Compétition Ouverte';
            return 'AO Ouvert';
        }

        return 'Inconnu';
    }
}
