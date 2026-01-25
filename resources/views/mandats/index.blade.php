@extends('layouts.app')

@section('title', 'Mandats de Paiement')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>💳 Mandats de Paiement</h2>
    </div>

    @if($mandats->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucun mandat pour le moment.</p>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Les mandats sont créés automatiquement lors de la validation des engagements.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">N°</th>
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Objet</th>
                        <th style="padding: 0.75rem; text-align: left;">Bénéficiaire</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant</th>
                        <th style="padding: 0.75rem; text-align: center;">Statut</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mandats as $mandat)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600;">{{ $mandat->numero }}</td>
                        <td style="padding: 0.75rem;">{{ $mandat->date->format('d/m/Y') }}</td>
                        <td style="padding: 0.75rem;">{{ Str::limit($mandat->objet_depense, 40) }}</td>
                        <td style="padding: 0.75rem;">{{ $mandat->beneficiaire }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($mandat->montant, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            @php
                                $colors = [
                                    'cree' => 'background: var(--slate-300); color: var(--slate-700);',
                                    'valide_directeur' => 'background: var(--warning); color: white;',
                                    'valide_comptable' => 'background: var(--success); color: white;',
                                    'refuse_comptable' => 'background: var(--danger); color: white;',
                                    'requisition' => 'background: #9c27b0; color: white;',
                                    'paye' => 'background: var(--success); color: white;',
                                ];
                                $labels = [
                                    'cree' => 'En attente Directeur',
                                    'valide_directeur' => 'Attente Comptable',
                                    'valide_comptable' => 'Payé',
                                    'refuse_comptable' => 'Refusé Comptable',
                                    'requisition' => 'Réquisition',
                                    'paye' => 'Payé',
                                ];
                            @endphp
                            <span style="font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 4px; {{ $colors[$mandat->statut] ?? '' }}">
                                {{ $labels[$mandat->statut] ?? ucfirst($mandat->statut) }}
                            </span>
                            @if($mandat->requisition)
                                <br><span style="font-size: 0.65rem; color: #9c27b0;">⚠️ Réquisition</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="{{ route('mandats.show', $mandat) }}" style="color: var(--primary); text-decoration: none;">Voir</a>
                            | <a href="{{ route('mandats.document', $mandat) }}" target="_blank" style="color: var(--success); text-decoration: none;">📄</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $mandats->links() }}
        </div>
    @endif
@endsection
