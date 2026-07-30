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
        $comptes = Compte::all();
        $budgets = Budget::all();
        $fournisseurs = Fournisseur::all();
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
            'articles.*.designation' => 'required|string',
            'articles.*.quantite' => 'required|integer',
            'articles.*.prix_unitaire' => 'required|numeric',
        ]);

        // Deduce Mode de Passation
        $mode_passation = $this->deduceModePassation($request->type, $request->montant);

        $marche = Marche::create([
            'date' => $request->date,
            'intitule' => $request->intitule,
            'compte_id' => $request->compte_id,
            'budget_id' => $request->budget_id,
            'nature' => $request->nature,
            'type' => $request->type,
            'montant' => $request->montant,
            'mode_passation' => $mode_passation,
            'fournisseur_id' => $request->fournisseur_id,
        ]);

        foreach ($request->articles as $art) {
            $marche->articles()->create([
                'designation' => $art['designation'],
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
