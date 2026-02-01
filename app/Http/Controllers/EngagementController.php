<?php

namespace App\Http\Controllers;

use App\Models\Engagement;
use App\Models\Mandat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngagementController extends Controller
{
    public function index()
    {
        $query = Engagement::with(['commande.compte', 'commande.service'])
            ->orderByDesc('created_at');

        if (Auth::user()->role !== 'admin') {
            $query->whereHas('commande', function ($q) {
                $q->where('service_id', Auth::user()->service_id);
            });
        }

        $engagements = $query->paginate(20);

        return view('engagements.index', compact('engagements'));
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
        $commande = $engagement->commande;
        
        Mandat::create([
            'numero' => Mandat::generateNumero(),
            'engagement_id' => $engagement->id,
            'date' => now()->toDateString(),
            'objet_depense' => $engagement->objet,
            'montant' => $engagement->montant,
            'beneficiaire' => $engagement->nom_fournisseur,
            'compte_principal' => substr($commande->compte->numero ?? '', 0, 2),
            'compte_imputation' => $commande->compte->numero ?? '',
            'libelle_imputation' => $commande->compte->libelle ?? '',
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
