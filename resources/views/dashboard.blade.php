@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    @if(Auth::user()->role === 'admin')
    <div class="grid-stats">
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
    @endif

    @if(Auth::user()->role === 'service')
    <!-- SERVICE MANAGER DASHBOARD -->
    
    <!-- Besoins Statistics -->
    @if(isset($data['besoins_stats']))
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 600;">Mes Besoins Exprimés</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div style="text-align: center; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $data['besoins_stats']['total'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Total Besoins</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--slate-900);">{{ $data['besoins_stats']['total_quantite'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Quantité Totale</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: var(--warning-light); border-radius: 0.5rem;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">{{ $data['besoins_stats']['cree'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">En attente</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: var(--success-light); border-radius: 0.5rem;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $data['besoins_stats']['valide'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Validés</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: var(--danger-light); border-radius: 0.5rem;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);">{{ $data['besoins_stats']['rejete'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Rejetés</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Budget info for service heads simplified to hide amounts -->
    <div class="card" style="margin-bottom: 2rem; padding: 2.5rem; text-align: center;">
        <h3 style="margin-bottom: 1rem; color: var(--slate-800);">Gestion des Activités Budgétaires</h3>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 2rem auto;">
            En tant que Chef de Service, vous pouvez exprimer vos besoins en spécifiant les articles et quantités nécessaires. L'administration se chargera de l'arbitrage financier.
        </p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <a href="{{ route('besoins.create') }}" class="btn-primary" style="text-decoration: none; padding: 0.75rem 2rem;">
                Exprimer un nouveau besoin
            </a>
            <a href="{{ route('besoins.index') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 2rem;">
                Suivi de mes demandes
            </a>
        </div>
    </div>

    @else
    <!-- ADMIN DASHBOARD -->

    <!-- Charts Section -->
    @if(isset($data['chart_services']))
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card">
            <h3 class="stat-title">Budget vs Engagement par Service</h3>
            <canvas id="chartServices" height="250"></canvas>
        </div>
        <div class="stat-card">
            <h3 class="stat-title">Par Compte (Top 8)</h3>
            <canvas id="chartComptes" height="250"></canvas>
        </div>
    </div>
    <div class="stat-card" style="margin-bottom: 2rem;">
        <h3 class="stat-title">Evolution des Dépenses (Mensuel)</h3>
        <canvas id="chartEvolution" height="100"></canvas>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Common Options
            const commonOptions = {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            };

            // Chart 1: Services
            const ctx1 = document.getElementById('chartServices').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: @json($data['chart_services']['labels'] ?? []),
                    datasets: [
                        {
                            label: 'Alloué',
                            data: @json($data['chart_services']['alloue'] ?? []),
                            backgroundColor: '#e2e8f0',
                            borderRadius: 4
                        },
                        {
                            label: 'Engagé',
                            data: @json($data['chart_services']['engage'] ?? []),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }
                    ]
                },
                options: commonOptions
            });

            // Chart 2: Comptes
            const ctx2 = document.getElementById('chartComptes').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: @json($data['chart_comptes']['labels'] ?? []),
                    datasets: [{
                        data: @json($data['chart_comptes']['values'] ?? []),
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                            '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12 } }
                    },
                    cutout: '70%'
                }
            });

            // Chart 3: Evolution
            const ctx3 = document.getElementById('chartEvolution').getContext('2d');
            new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: @json($data['chart_evolution']['labels'] ?? []),
                    datasets: [{
                        label: 'Dépenses',
                        data: @json($data['chart_evolution']['values'] ?? []),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: commonOptions
            });
        });
    </script>
    @endpush

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
    @endif
@endsection