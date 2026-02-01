<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Budget;
use App\Models\Compte;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BesoinController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Find Active or Draft Budget for display context
        // Ideally we should filter by year selector, but for now defaults to latest relevant
        $budget = Budget::orderByDesc('annee')->first();

        $query = Besoin::with(['compte', 'service', 'budget'])
            ->where('service_id', $user->service_id);

        if ($budget) {
            $query->where('budget_id', $budget->id);
        }

        $besoins = $query->latest()->get();

        return view('besoins.index', compact('besoins'));
    }

    public function create()
    {

        $comptes = Compte::with(['articles' => function($q) {
                $q->where('actif', true);
            }])
           // ->where('type', 'Fonctionnement')
            ->whereNotNull('parent_id') // Only leaf accounts usually
            ->where('etat', 'actif')
            ->get();

        return view('besoins.create', compact('comptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'libelle' => 'required|string|max:255',
            'quantite' => 'required|integer|min:1',
            'justification' => 'required|string|max:1000',
        ]);

        // Check for active or draft budget
        $budget = Budget::whereIn('statut', ['brouillon', 'actif'])->latest('annee')->first();

        if (!$budget) {
            return back()->withErrors(['msg' => 'Aucun exercice budgétaire ouvert pour recevoir les demandes.']);
        }

        Besoin::create([
            'budget_id' => $budget->id,
            'service_id' => Auth::user()->service_id,
            'compte_id' => $request->compte_id,
            'libelle' => $request->libelle,
            'quantite' => $request->quantite,
            'justification' => $request->justification,
            'statut' => 'cree',
        ]);

        return redirect()->route('besoins.index')->with('success', 'Besoin enregistré avec succès.');
    }
}
