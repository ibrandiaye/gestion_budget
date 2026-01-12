@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="grid-stats">
        <style>
            .grid-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .stat-card {
                background: white;
                padding: 1.5rem;
                border-radius: 1rem;
                border: 1px solid var(--border-color);
                box-shadow: var(--shadow-sm);
            }

            .stat-title {
                color: var(--text-muted);
                font-size: 0.85rem;
                font-weight: 600;
                text-transform: uppercase;
                margin-bottom: 0.5rem;
            }

            .stat-value {
                font-size: 2rem;
                font-weight: 700;
                color: var(--slate-900);
            }

            .stat-trend {
                font-size: 0.85rem;
                margin-top: 0.5rem;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .trend-up {
                color: var(--success);
            }

            .trend-down {
                color: var(--danger);
            }
        </style>

        <div class="stat-card">
            <h3 class="stat-title">Budget Total ({{ $data['annee'] }})</h3>
            <div class="stat-value" style="color: var(--primary);">{{ number_format($data['total_alloue'], 0, ',', ' ') }}
                FCFA</div>
            <div class="stat-trend">
                <span>Budget Actif</span>
            </div>
        </div>

        <div class="stat-card">
            <h3 class="stat-title">Montant Engagé</h3>
            <div class="stat-value">{{ number_format($data['total_engage'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-trend">
                <span style="color: {{ $data['taux_execution'] > 80 ? 'var(--danger)' : 'var(--warning)' }}">
                    {{ $data['taux_execution'] }}% du budget
                </span>
            </div>
        </div>

        <div class="stat-card">
            <h3 class="stat-title">Solde Disponible</h3>
            <div class="stat-value" style="color: var(--success);">
                {{ number_format($data['total_disponible'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-trend">
                <span>Reste à dépenser</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Top Services -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 600;">Top 5 Services (Engagement)</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr
                        style="border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.85rem; color: var(--text-muted);">
                        <th style="padding: 0.75rem;">Service</th>
                        <th style="padding: 0.75rem;">Alloué</th>
                        <th style="padding: 0.75rem;">Engagé</th>
                        <th style="padding: 0.75rem;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['top_services'] as $stat)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem; font-weight: 500;">
                                {{ $stat->service->nom }}
                            </td>
                            <td style="padding: 0.75rem; font-family: monospace;">
                                {{ number_format($stat->total_alloue, 0, ',', ' ') }}
                            </td>
                            <td style="padding: 0.75rem; font-family: monospace; font-weight: 600;">
                                {{ number_format($stat->total_engage, 0, ',', ' ') }}
                            </td>
                            <td style="padding: 0.75rem;">
                                @php
                                    $percent = $stat->total_alloue > 0 ? ($stat->total_engage / $stat->total_alloue) * 100 : 0;
                                @endphp
                                <span style="
                                        padding: 0.2rem 0.6rem; 
                                        border-radius: 99px; 
                                        font-size: 0.75rem; 
                                        background: {{ $percent > 80 ? 'var(--danger-light)' : 'var(--success-light)' }};
                                        color: {{ $percent > 80 ? 'var(--danger)' : 'var(--success)' }};
                                    ">
                                    {{ round($percent, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                                Aucune donnée d'exécution pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Quick Actions or Other Stats -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 600;">Accès Rapide</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @if(Auth::user()->role === 'service')
                    <a href="{{ route('besoins.create') }}" class="btn-primary"
                        style="text-align: center; text-decoration: none;">Exprimer un besoin</a>
                    <a href="{{ route('execution.index') }}" class="btn-secondary"
                        style="text-align: center; text-decoration: none; margin-top: 0.5rem;">Mes Dépenses</a>
                @elseif(Auth::user()->role === 'admin')
                    <a href="{{ route('budget.arbitrage') }}" class="btn-primary"
                        style="text-align: center; text-decoration: none;">Arbitrage Budgétaire</a>
                    <a href="{{ route('ajustements.create') }}" class="btn-secondary"
                        style="text-align: center; text-decoration: none; margin-top: 0.5rem;">Faire un virement</a>
                    <a href="{{ route('reporting.global') }}" class="btn-secondary"
                        style="text-align: center; text-decoration: none; margin-top: 0.5rem;">Voir Reporting Complet</a>
                @endif
            </div>
        </div>
    </div>
@endsection