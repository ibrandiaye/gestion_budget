@extends('layouts.app')

@section('title', 'Détails du PV')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>PV de Réception : PV-{{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}</h2>
            <a href="{{ route('pv-receptions.index') }}" style="color: var(--text-muted); text-decoration: none;">&larr; Retour à la liste</a>
        </div>
        <button class="btn-primary" onclick="window.print()">🖨️ Imprimer</button>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h4 style="color: var(--slate-500); margin-bottom: 0.5rem;">Informations</h4>
                <p><strong>Commande N°:</strong> {{ $pv_reception->commande->numero }}</p>
                <p><strong>Date de réception:</strong> {{ $pv_reception->date_reception->format('d/m/Y') }}</p>
                <p><strong>Enregistré par:</strong> {{ $pv_reception->createdBy->name ?? '-' }}</p>
                @if($pv_reception->signataires)
                    <p><strong>Signataires:</strong> {{ implode(', ', $pv_reception->signataires) }}</p>
                @endif
                @if($pv_reception->pieces_justificatives)
                    <p><strong>Pièces Justificatives:</strong> {{ implode(', ', $pv_reception->pieces_justificatives) }}</p>
                @endif
            </div>
            <div>
                <h4 style="color: var(--slate-500); margin-bottom: 0.5rem;">Montants</h4>
                <p><strong>Montant Total (HT):</strong> {{ number_format($pv_reception->montant_total, 0, ',', ' ') }} FCFA</p>
                <p><strong>TVA:</strong> {{ $pv_reception->tva }}%</p>
                <p><strong>Montant TTC:</strong> <span style="font-size: 1.25rem; font-weight: bold; color: var(--primary);">{{ number_format($pv_reception->montant_ttc, 0, ',', ' ') }} FCFA</span></p>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Articles Réceptionnés</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: var(--slate-50);">
                    <th style="padding: 0.75rem; text-align: left;">Désignation</th>
                    <th style="padding: 0.75rem; text-align: right;">Qté Commandée</th>
                    <th style="padding: 0.75rem; text-align: right;">Qté Reçue</th>
                    <th style="padding: 0.75rem; text-align: right;">P.U (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pv_reception->articles as $pv_art)
                <tr style="border-top: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem;">{{ $pv_art->commandeArticle->designation }}</td>
                    <td style="padding: 0.75rem; text-align: right;">{{ $pv_art->commandeArticle->quantite }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-weight: bold; color: var(--success);">{{ $pv_art->quantite_receptionnee }}</td>
                    <td style="padding: 0.75rem; text-align: right;">{{ number_format($pv_art->commandeArticle->prix_unitaire, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
