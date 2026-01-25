<?php

namespace App\Http\Controllers;

use App\Models\Mandat;
use App\Models\LigneBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MandatController extends Controller
{
    public function index()
    {
        $query = Mandat::with(['engagement.commande.compte', 'engagement.commande.service'])
            ->orderByDesc('created_at');

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'directeur' && Auth::user()->role !== 'comptable') {
            $query->whereHas('engagement.commande', function ($q) {
                $q->where('service_id', Auth::user()->service_id);
            });
        }

        $mandats = $query->paginate(20);

        return view('mandats.index', compact('mandats'));
    }

    public function show(Mandat $mandat)
    {
        $mandat->load(['engagement.commande.compte', 'engagement.commande.service', 'engagement.commande.articlesValides']);
        return view('mandats.show', compact('mandat'));
    }

    public function validerDirecteur(Mandat $mandat)
    {
        if (!$mandat->canBeValidatedByDirecteur()) {
            return back()->withErrors(['statut' => 'Ce mandat ne peut pas être validé par le directeur.']);
        }

        if (Auth::user()->role !== 'directeur' && Auth::user()->role !== 'admin') {
            return back()->withErrors(['autorisation' => 'Vous n\'êtes pas autorisé à valider ce mandat.']);
        }

        $mandat->update([
            'statut' => 'valide_directeur',
            'valide_directeur_par' => Auth::id(),
            'valide_directeur_at' => now(),
        ]);

        return back()->with('success', 'Mandat validé par le directeur. En attente de validation comptable.');
    }

    public function validerComptable(Mandat $mandat)
    {
        if (!$mandat->canBeValidatedByComptable()) {
            return back()->withErrors(['statut' => 'Ce mandat ne peut pas être validé par le comptable.']);
        }

        if (Auth::user()->role !== 'comptable' && Auth::user()->role !== 'admin') {
            return back()->withErrors(['autorisation' => 'Vous n\'êtes pas autorisé à valider ce mandat.']);
        }

        $mandat->update([
            'statut' => 'paye',
            'valide_comptable_par' => Auth::id(),
            'valide_comptable_at' => now(),
        ]);

        // Deduct from budget
        $this->defalquerBudget($mandat);

        return back()->with('success', 'Mandat validé et payé. Budget mis à jour.');
    }


    public function refuserComptable(Request $request, Mandat $mandat)
    {
        if (!$mandat->canBeValidatedByComptable()) {
            return back()->withErrors(['statut' => 'Ce mandat ne peut pas être refusé.']);
        }

        if (Auth::user()->role !== 'comptable' && Auth::user()->role !== 'admin') {
            return back()->withErrors(['autorisation' => 'Vous n\'êtes pas autorisé à refuser ce mandat.']);
        }

        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $mandat->update([
            'statut' => 'refuse_comptable',
            'valide_comptable_par' => Auth::id(),
            'valide_comptable_at' => now(),
            'motif_refus' => $request->motif_refus,
        ]);

        return back()->with('success', 'Mandat refusé par le comptable.');
    }

    public function requisition(Mandat $mandat)
    {
        if (!$mandat->canBeRequisitioned()) {
            return back()->withErrors(['statut' => 'La réquisition n\'est pas possible pour ce mandat.']);
        }

        if (Auth::user()->role !== 'directeur' && Auth::user()->role !== 'admin') {
            return back()->withErrors(['autorisation' => 'Seul le directeur peut faire une réquisition.']);
        }

        $mandat->update([
            'statut' => 'paye',
            'requisition' => true,
        ]);

        // Deduct from budget (même en réquisition)
        $this->defalquerBudget($mandat);

        return back()->with('success', 'Réquisition effectuée. Le mandat est maintenant payé. Budget mis à jour.');
    }

    public function document(Mandat $mandat)
    {
        $mandat->load(['engagement.commande.compte', 'engagement.commande.service']);
        return view('mandats.document', compact('mandat'));
    }

    /**
     * Défalquer le montant du mandat du budget
     */
    private function defalquerBudget(Mandat $mandat)
    {
        $engagement = $mandat->engagement;
        $commande = $engagement->commande;

        $ligneBudget = LigneBudget::where('budget_id', $commande->budget_id)
            ->where('compte_id', $commande->compte_id)
            ->where('service_id', $commande->service_id)
            ->first();

        if ($ligneBudget) {
            // Increase montant_engage (committed) and montant_paye (paid)
            $ligneBudget->increment('montant_engage', $mandat->montant);
            $ligneBudget->increment('montant_paye', $mandat->montant);
        }
    }
}
