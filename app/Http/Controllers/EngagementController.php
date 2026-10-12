<?php

namespace App\Http\Controllers;

use App\Models\Engagement;
use App\Models\Mandat;
use App\Models\Compte;
use App\Models\Budget;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngagementController extends Controller
{
    public function index()
    {
        $query = Engagement::with(['commande.compte', 'commande.service', 'compte', 'service'])
            ->orderByDesc('created_at');

        if (Auth::user()->role === 'service') {
            $query->where(function ($q) {
                $q->whereHas('commande', function ($q2) {
                    $q2->where('service_id', Auth::user()->service_id);
                })->orWhere('service_id', Auth::user()->service_id);
            });
        }

        $engagements = $query->paginate(20);

        return view('engagements.index', compact('engagements'));
    }

    public function createDirect()
    {
        $comptes = Compte::where('etat', 'actif')
            ->whereNotNull('compte_parent_id')
            ->get();
        
        $budget = Budget::whereIn('statut', ['brouillon', 'actif'])->latest('annee')->first();
        
        // Only admin can create direct engagements or maybe service too? 
        // We'll pass the current user's service logic in the view
        $services = Service::all();

        return view('engagements.create_direct', compact('comptes', 'budget', 'services'));
    }

    public function storeDirect(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'date' => 'required|date',
            'objet' => 'required|string|max:191',
            'montant' => 'required|numeric|min:1',
            'nom_fournisseur' => 'nullable|string|max:191',
        ]);

        $budget = Budget::whereIn('statut', ['brouillon', 'actif'])->latest('annee')->first();
        if (!$budget) {
            return back()->withErrors(['budget' => 'Aucun budget actif trouvé.']);
        }

        $service_id = Auth::user()->role === 'admin' && $request->filled('service_id') 
            ? $request->service_id 
            : Auth::user()->service_id;

        $compte = Compte::findOrFail($request->compte_id);
        $dotation = $compte->getDotationForBudget($budget->id);
        
        // Calcul du disponible
        $totalEngageCompte = Engagement::where(function($query) use ($budget, $request) {
            $query->where(function($q) use ($budget, $request) {
                $q->where('budget_id', $budget->id)
                  ->where('compte_id', $request->compte_id);
            })->orWhereHas('commande', function($q) use ($budget, $request) {
                $q->where('budget_id', $budget->id)
                  ->where('compte_id', $request->compte_id);
            });
        })->sum('montant');
        
        $disponible = $dotation - $totalEngageCompte;

        if ($request->montant > $disponible) {
            return back()->withErrors([
                'budget' => "Budget insuffisant sur le compte " . $compte->numero . ". Dotation: " . number_format($dotation, 0, ',', ' ') . " FCFA. Disponible: " . number_format($disponible, 0, ',', ' ') . " FCFA. Demandé: " . number_format($request->montant, 0, ',', ' ') . " FCFA."
            ])->withInput();
        }

        Engagement::create([
            'numero' => Engagement::generateNumero(),
            'commande_id' => null,
            'compte_id' => $request->compte_id,
            'service_id' => $service_id,
            'budget_id' => $budget->id,
            'date' => $request->date,
            'objet' => $request->objet,
            'nature' => 'Engagement direct',
            'montant' => $request->montant,
            'nom_fournisseur' => $request->nom_fournisseur,
            'statut' => 'cree',
        ]);

        return redirect()->route('engagements.index')->with('success', 'Engagement direct créé avec succès.');
    }

    public function show(Engagement $engagement)
    {
        $engagement->load(['commande.compte', 'commande.service', 'commande.articlesValides', 'mandat']);
        return view('engagements.show', compact('engagement'));
    }

    public function valider(Engagement $engagement)
    {
        if ($engagement->statut !== 'cree') {
            return back()->withErrors(['statut' => 'Cet engagement ne peut pas être validé.']);
        }

        $engagement->update([
            'statut' => 'valide',
            'valide_par' => Auth::id(),
            'valide_at' => now(),
        ]);

        // Create mandat
        $compte = $engagement->compte ?? ($engagement->commande ? $engagement->commande->compte : null);
        
        Mandat::create([
            'numero' => Mandat::generateNumero(),
            'engagement_id' => $engagement->id,
            'date' => now()->toDateString(),
            'objet_depense' => $engagement->objet,
            'montant' => $engagement->montant,
            'beneficiaire' => $engagement->nom_fournisseur,
            'compte_principal' => $compte ? substr($compte->numero, 0, 2) : '',
            'compte_imputation' => $compte->numero ?? '',
            'libelle_imputation' => $compte->libelle ?? '',
            'statut' => 'cree',
        ]);

        return redirect()->route('mandats.index')->with('success', 'Engagement validé et mandat créé.');
    }

    public function rejeter(Request $request, Engagement $engagement)
    {
        if ($engagement->statut !== 'cree') {
            return back()->withErrors(['statut' => 'Cet engagement ne peut pas être rejeté.']);
        }

        $request->validate([
            'motif' => 'required|string|max:1000',
        ]);

        $engagement->update([
            'statut' => 'rejete',
            'valide_par' => Auth::id(),
            'valide_at' => now(),
            'motif_rejet' => $request->motif,
        ]);

        return back()->with('success', 'Engagement rejeté.');
    }

    public function document(Engagement $engagement)
    {
        $engagement->load(['commande.compte', 'commande.service', 'commande.articlesValides']);
        return view('engagements.document', compact('engagement'));
    }
}
