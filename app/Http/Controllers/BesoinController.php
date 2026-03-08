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
            ->whereNotNull('compte_parent_id') // Only leaf accounts usually
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

        // --- DEBUT MODIFICATION : Contrôle Budget Global Compte ---
        $compte = Compte::findOrFail($request->compte_id);
        $dotation = $compte->getDotationForBudget($budget->id);
        $prixReference = $compte->getPrixUnitaireRefForBudget($budget->id);
        
        // Coût du besoin actuel
        // On utilise le prix de référence s'il existe, sinon on supposerait un montant estimé (ici on se base sur ref)
        // Si prix_unitaire_ref est 0, on ne peut pas estimer le coût sans saisie utilisateur.
        // Supposons que le montant_demande soit calculé ou saisissable, mais ici on a quantite * prix_ref
        $coutActuel = $request->quantite * $prixReference;

        // Somme des besoins existants pour ce compte dans ce budget (tous services confondus)
        // On calcule le coût estimé des besoins existants : quantité * prix_ref du compte
        $quantiteTotaleExistante = Besoin::where('budget_id', $budget->id)
            ->where('compte_id', $request->compte_id)
            ->sum('quantite');
            
        $coutTotalExistant = $quantiteTotaleExistante * $prixReference;

        if (($coutTotalExistant + $coutActuel) > $dotation) {
             return back()->withErrors(['msg' => "Budget insuffisant sur le compte " . $compte->numero . ". Dotation: " . number_format($dotation, 0, ',', ' ') . " FCFA. Disponible théorique: " . number_format($dotation - $coutTotalExistant, 0, ',', ' ') . " FCFA."]);
        }
        // --- FIN MODIFICATION ---

        Besoin::create([
            'budget_id' => $budget->id,
            'service_id' => Auth::user()->service_id,
            'compte_id' => $request->compte_id,
            'libelle' => $request->libelle,
            'quantite' => $request->quantite,
            'prix_unitaire' => $compte->prix_unitaire_ref, // Enregistrement du prix ref
            'montant_demande' => $coutActuel, // Enregistrement du montant estimé
            'justification' => $request->justification,
            'statut' => 'cree',
        ]);

        return redirect()->route('besoins.index')->with('success', 'Besoin enregistré avec succès.');
    }
}
