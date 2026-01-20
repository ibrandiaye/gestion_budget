@extends('layouts.app')

@section('title', 'Arbitrage par Compte')

@section('content')
    <div class="actions-header" style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.25rem; color: var(--slate-800); margin-bottom: 0.5rem;">Sélectionnez un compte à arbitrer</h3>
        <p style="color: var(--text-muted);">Voici la liste des comptes budgétaires ayant des expressions de besoins en attente de validation.</p>
    </div>

    @if($comptes->isEmpty())
        <div class="card" style="padding: 4rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
            <h4 style="color: var(--slate-800); margin-bottom: 0.5rem;">Tout est traité !</h4>
            <p style="color: var(--text-muted);">Aucun besoin en attente d'arbitrage pour le moment.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="display: inline-block; margin-top: 1.5rem; text-decoration: none;">Retour au Tableau de bord</a>
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Compte</th>
                        <th style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Demandes</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Dotation</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Estimation</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Écart</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comptes as $compte)
                    @php 
                        $estimation = ($compte->total_quantite ?? 0) * ($compte->prix_unitaire_ref ?? 0);
                        $gap = ($compte->dotation_initiale ?? 0) - $estimation;
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;">
                            <div style="font-weight: 700; color: var(--primary-700);">{{ $compte->numero }}</div>
                            <div style="font-size: 0.85rem; font-weight: 500;">{{ $compte->libelle }}</div>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="background: var(--slate-100); padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.8rem; font-weight: 600;">
                                {{ $compte->besoins_count }} demande(s)
                            </span>
                            @if($compte->besoins_en_attente_count > 0)
                                <div style="font-size: 0.75rem; color: var(--warning-700); font-weight: 700; margin-top: 0.25rem;">
                                    dont {{ $compte->besoins_en_attente_count }} à arbitrer
                                </div>
                            @else
                                <div style="font-size: 0.75rem; color: var(--success-700); font-weight: 700; margin-top: 0.25rem;">
                                    ✓ Tout arbitré
                                </div>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 600;">
                            {{ number_format($compte->dotation_initiale, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 600; color: var(--warning-700);">
                            {{ number_format($estimation, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <span style="font-weight: 700; color: {{ $gap >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ number_format($gap, 0, ',', ' ') }}
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <a href="{{ route('budget.arbitrage', ['compte_id' => $compte->id]) }}" 
                               class="btn-primary" style="text-decoration: none; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                Arbitrer
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection