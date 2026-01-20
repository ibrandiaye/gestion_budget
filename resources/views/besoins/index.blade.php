@extends('layouts.app')

@section('title', 'Expression des Besoins')

@section('content')
    <div class="actions-header"
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="font-size: 1.1rem; color: var(--text-muted);">Vos demandes budgétaires</h3>
        </div>
        <a href="{{ route('besoins.create') }}" class="btn-primary" style="text-decoration: none;">
            + Nouveau Besoin
        </a>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--slate-50); border-bottom: 1px solid var(--border-color);">
                <tr>
                    <th
                        style="text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Budget</th>
                    <th
                        style="text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Compte</th>
                    <th
                        style="text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Besoin (Détails)</th>
                    <th
                        style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Quantité</th>
                    <th
                        style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Statut</th>
                    @if(Auth::user()->role === 'admin')
                    <th
                        style="text-align: right; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Montant Accordé</th>
                    @endif
                    <th
                        style="text-align: right; padding: 1rem; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                        Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($besoins as $besoin)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600; color: var(--primary-700);">
                            {{ $besoin->budget->annee ?? 'N/A' }}
                        </td>
                        <td style="padding: 0.75rem;">
                            <div style="font-weight: 500;">{{ $besoin->compte->libelle }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $besoin->compte->numero }}</div>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="font-weight: 600;">{{ $besoin->libelle }}</div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">{{ Str::limit($besoin->justification, 100) }}</div>
                        </td>
                        <td style="padding: 1rem; text-align: center; font-weight: 600;">
                            {{ $besoin->quantite }}
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="
                                                 display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;
                                                 background: {{ $besoin->statut === 'valide' ? 'var(--primary-100)' : ($besoin->statut === 'cree' ? 'var(--slate-100)' : '#fee2e2') }};
                                                 color: {{ $besoin->statut === 'valide' ? 'var(--primary-700)' : ($besoin->statut === 'cree' ? 'var(--slate-600)' : '#b91c1c') }};
                                             ">
                                 {{ ucfirst($besoin->statut) }}
                             </span>
                         </td>
                         @if(Auth::user()->role === 'admin')
                         <td style="padding: 1rem; text-align: right; font-weight: 700; color: var(--primary-700);">
                             {{ number_format($besoin->montant_accorde ?? 0, 0, ',', ' ') }} FCFA
                         </td>
                         @endif
                         <td style="padding: 1rem; text-align: right;">
                             <button style="border: none; background: transparent; cursor: pointer;">✏️</button>
                             <button
                                 style="border: none; background: transparent; cursor: pointer; color: var(--danger);">🗑️</button>
                         </td>
                     </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                            Aucun besoin exprimé pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection