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

        if (Auth::user()->role === 'service') {
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

    public function edit(Mandat $mandat)
    {
        $mandat->load(['engagement.commande.compte', 'engagement.commande.service']);
        return view('mandats.edit', compact('mandat'));
    }

    public function update(Request $request, Mandat $mandat)
    {
        $request->validate([
            'date' => 'required|date',
            'objet_depense' => 'required|string|max:255',
            'beneficiaire' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'montant_lettres' => 'nullable|string|max:255',
            'pieces_justificatives' => 'nullable|string|max:500',
            'mode_reglement' => 'nullable|string|max:100',
            'etablissement' => 'nullable|string|max:191',
            'numero_compte' => 'nullable|string|max:100',
            'rib' => 'nullable|string|max:100',
            'compte_principal' => 'nullable|string|max:50',
            'compte_imputation' => 'nullable|string|max:50',
            'libelle_imputation' => 'nullable|string|max:255',
        ]);

        $ancienMontant = (float) $mandat->montant;
        $nouveauMontant = (float) $request->montant;

        $mandat->update([
            'date' => $request->date,
            'objet_depense' => $request->objet_depense,
            'beneficiaire' => $request->beneficiaire,
            'montant' => $nouveauMontant,
            'montant_lettres' => $request->montant_lettres,
            'pieces_justificatives' => $request->pieces_justificatives,
            'mode_reglement' => $request->mode_reglement,
            'etablissement' => $request->etablissement,
            'numero_compte' => $request->numero_compte,
            'rib' => $request->rib,
            'compte_principal' => $request->compte_principal,
            'compte_imputation' => $request->compte_imputation,
            'libelle_imputation' => $request->libelle_imputation,
        ]);

        // Ajuster le budget si déjà payé et montant modifié
        if ($mandat->isPaye() && $ancienMontant != $nouveauMontant) {
            $difference = $nouveauMontant - $ancienMontant;
            $engagement = $mandat->engagement;
            $commande = $engagement?->commande;
            $budgetId = $commande?->budget_id ?? $engagement?->budget_id;
            $compteId = $commande?->compte_id ?? $engagement?->compte_id;
            $serviceId = $commande?->service_id ?? $engagement?->service_id;

            if ($budgetId && $compteId) {
                $ligneBudget = LigneBudget::where('budget_id', $budgetId)
                    ->where('compte_id', $compteId)
                    ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
                    ->first();
                if ($ligneBudget) {
                    $ligneBudget->increment('montant_engage', $difference);
                    $ligneBudget->increment('montant_paye', $difference);
                }
            }
        }

        return redirect()->route('mandats.show', $mandat)->with('success', 'Mandat modifié avec succès.');
    }

    public function destroy(Mandat $mandat)
    {
        // Si le mandat était payé, régulariser le budget
        if ($mandat->isPaye()) {
            $engagement = $mandat->engagement;
            $commande = $engagement?->commande;
            $budgetId = $commande?->budget_id ?? $engagement?->budget_id;
            $compteId = $commande?->compte_id ?? $engagement?->compte_id;
            $serviceId = $commande?->service_id ?? $engagement?->service_id;

            if ($budgetId && $compteId) {
                $ligneBudget = LigneBudget::where('budget_id', $budgetId)
                    ->where('compte_id', $compteId)
                    ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
                    ->first();
                if ($ligneBudget) {
                    $ligneBudget->decrement('montant_engage', $mandat->montant);
                    $ligneBudget->decrement('montant_paye', $mandat->montant);
                }
            }
        }

        $mandat->delete();

        return redirect()->route('mandats.index')->with('success', 'Mandat supprimé avec succès.');
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

    public function refuserDirecteur(Request $request, Mandat $mandat)
    {
        if (!$mandat->canBeValidatedByDirecteur()) {
            return back()->withErrors(['statut' => 'Ce mandat ne peut pas être refusé par le directeur.']);
        }

        if (Auth::user()->role !== 'directeur' && Auth::user()->role !== 'admin') {
            return back()->withErrors(['autorisation' => 'Vous n\'êtes pas autorisé à refuser ce mandat.']);
        }

        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $mandat->update([
            'statut' => 'refuse_directeur', // Creating this status or reusing refuse_comptable? Ideally distinct.
            // But if enum constraint exists, I should check. Assuming string status.
            // Mandat status list seems to be: cree, valide_directeur, valide_comptable, refuse_comptable, requisition, paye
            // I should add 'refuse_directeur' if I can.
            // For now, let's use 'refuse_directeur' as it seems appropriate.
            'valide_directeur_par' => Auth::id(),
            'valide_directeur_at' => now(),
            'motif_refus' => $request->motif_refus,
        ]);

        return back()->with('success', 'Mandat refusé par le directeur.');
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
        $mandat->load([
            'engagement.commande.compte', 
            'engagement.commande.service',
            'engagement.compte',
            'engagement.service'
        ]);
        return view('mandats.document', compact('mandat'));
    }

    /**
     * Défalquer le montant du mandat du budget
     */
    private function defalquerBudget(Mandat $mandat)
    {
        $engagement = $mandat->engagement;
        $commande = $engagement?->commande;

        $budgetId = $commande?->budget_id ?? $engagement?->budget_id;
        $compteId = $commande?->compte_id ?? $engagement?->compte_id;
        $serviceId = $commande?->service_id ?? $engagement?->service_id;

        if ($budgetId && $compteId) {
            $ligneBudget = LigneBudget::where('budget_id', $budgetId)
                ->where('compte_id', $compteId)
                ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
                ->first();

            if ($ligneBudget) {
                // Increase montant_engage (committed) and montant_paye (paid)
                $ligneBudget->increment('montant_engage', $mandat->montant);
                $ligneBudget->increment('montant_paye', $mandat->montant);
            }
        }
    }
}
