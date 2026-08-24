@extends('layouts.app')

@section('title', 'Réceptions')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin:0;">📦 Réceptions de Commandes</h2>
            <p style="color: var(--text-muted); margin: 0.3rem 0 0; font-size: 0.9rem;">Historique des réceptions et procès-verbaux</p>
        </div>
    </div>

    @if($pvs->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucune réception enregistrée pour le moment.</p>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Les réceptions s'effectuent directement depuis les commandes validées.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">N° Réception</th>
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Commande</th>
                        <th style="padding: 0.75rem; text-align: left;">Fournisseur</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant TTC</th>
                        <th style="padding: 0.75rem; text-align: center;">Type / PV</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pvs as $pv)
                    @php $isPvPrintable = ($pv->montant_ttc > 300000); @endphp
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600;">REC-{{ str_pad($pv->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td style="padding: 0.75rem;">{{ $pv->date_reception ? $pv->date_reception->format('d/m/Y') : '—' }}</td>
                        <td style="padding: 0.75rem;">
                            <a href="{{ route('commandes.show', $pv->commande_id) }}" style="color: var(--primary-600); text-decoration: none; font-weight: 500;">
                                {{ $pv->commande->numero }}
                            </a>
                        </td>
                        <td style="padding: 0.75rem;">{{ $pv->commande->fournisseur ?? '-' }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($pv->montant_ttc, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            @if($isPvPrintable)
                                <span style="background: #dbeafe; color: #1e40af; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">
                                    📄 PV Imprimable
                                </span>
                            @else
                                <span style="background: #f1f5f9; color: #64748b; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 500;">
                                    📦 Réception standard
                                </span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="{{ route('pv-receptions.show', $pv) }}" class="btn-primary" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; text-decoration: none;">
                                Consulter
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
