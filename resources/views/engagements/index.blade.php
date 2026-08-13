@extends('layouts.app')

@section('title', 'Engagements')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>📄 Bons d'Engagement</h2>
        <a href="{{ route('engagements.create-direct') }}" class="btn-primary" style="text-decoration:none;">+ Engagement Direct</a>
    </div>

    @if($engagements->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucun engagement pour le moment.</p>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Les engagements sont générés lors de la validation des commandes ou créés directement.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">N°</th>
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Objet</th>
                        <th style="padding: 0.75rem; text-align: left;">Fournisseur</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant</th>
                        <th style="padding: 0.75rem; text-align: center;">Statut</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($engagements as $engagement)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600;">{{ $engagement->numero }}</td>
                        <td style="padding: 0.75rem;">{{ $engagement->date->format('d/m/Y') }}</td>
                        <td style="padding: 0.75rem;">{{ Str::limit($engagement->objet, 40) }}</td>
                        <td style="padding: 0.75rem;">{{ $engagement->nom_fournisseur }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            @php
                                $colors = [
                                    'cree' => 'background: var(--warning); color: white;',
                                    'valide' => 'background: var(--success); color: white;',
                                    'rejete' => 'background: var(--danger); color: white;',
                                ];
                            @endphp
                            <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; {{ $colors[$engagement->statut] ?? '' }}">
                                {{ ucfirst($engagement->statut) }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="{{ route('engagements.show', $engagement) }}" style="color: var(--primary); text-decoration: none;">Voir</a>
                            | <a href="{{ route('engagements.document', $engagement) }}" target="_blank" style="color: var(--success); text-decoration: none;">📄</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $engagements->links() }}
        </div>
    @endif
@endsection
