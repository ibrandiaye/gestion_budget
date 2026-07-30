@extends('layouts.app')

@section('title', 'PV de Réception')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>📦 Procès-Verbaux de Réception</h2>
        <!-- La création se fait depuis une Commande spécifique -->
    </div>

    @if($pvs->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucun PV de réception enregistré.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">ID PV</th>
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Commande N°</th>
                        <th style="padding: 0.75rem; text-align: left;">Fournisseur</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant TTC</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pvs as $pv)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600;">PV-{{ str_pad($pv->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td style="padding: 0.75rem;">{{ $pv->date_reception->format('d/m/Y') }}</td>
                        <td style="padding: 0.75rem;">{{ $pv->commande->numero }}</td>
                        <td style="padding: 0.75rem;">{{ $pv->commande->fournisseur ?? '-' }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($pv->montant_ttc, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="{{ route('pv-receptions.show', $pv) }}" style="color: var(--primary); text-decoration: none;">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
