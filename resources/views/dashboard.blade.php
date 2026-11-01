@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <style>
        .grid-stats {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 1.25rem !important;
            margin-bottom: 2rem !important;
        }
        @media (max-width: 1100px) {
            .grid-stats {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 640px) {
            .grid-stats {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <!-- En-tête avec sélecteur d'exercice annuel -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--slate-800);">
                Suivi Budgétaire par Compte — Exercice {{ $data['annee'] }}
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                Synthèse des dotations annuelles par compte, commandes et engagements
            </p>
        </div>

        @if(isset($data['budgets']) && $data['budgets']->count() > 1)
            <form action="{{ route('dashboard') }}" method="GET" style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; padding: 0.45rem 0.85rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin: 0;">Exercice :</label>
                <select name="budget_id" onchange="this.form.submit()" style="padding: 0.35rem 0.6rem; border-radius: 0.375rem; border: 1px solid var(--border-color); font-size: 0.85rem; font-weight: 600; cursor: pointer; background: var(--slate-50);">
                    @foreach($data['budgets'] as $b)
                        <option value="{{ $b->id }}" {{ ($data['budget'] && $data['budget']->id == $b->id) ? 'selected' : '' }}>
                            {{ $b->annee }} ({{ ucfirst($b->statut) }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if(Auth::user()->role !== 'service')
    <div class="grid-stats">
        <!-- Dotation Totale -->
        <div class="stat-card card" style="background-color: white;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="stat-title">Dotation Totale ({{ $data['annee'] }})</h3>
                    <div class="stat-value" style="color: var(--slate-900); font-size: 1.35rem;">
                        {{ number_format($data['total_dotation'], 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
                    </div>
                </div>
                <div style="background: var(--primary-50); color: var(--primary-600); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    💰
                </div>
            </div>
            <div class="stat-trend">
                <span style="background: var(--success-light); color: var(--success);">
                    ● {{ $data['budget'] ? ucfirst($data['budget']->statut) : 'Actif' }}
                </span>
                <span style="color: var(--text-muted); font-size: 0.75rem;">Allocations par compte</span>
            </div>
        </div>

        <!-- Total Commandé -->
        <div class="stat-card card" style="background-color: white;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="stat-title">Total Commandé</h3>
                    <div class="stat-value" style="color: #0284c7; font-size: 1.35rem;">
                        {{ number_format($data['total_commandes'], 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
                    </div>
                </div>
                <div style="background: #e0f2fe; color: #0284c7; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    📋
                </div>
            </div>
            <div class="stat-trend">
                <span style="color: var(--text-muted); font-size: 0.75rem;">
                    Dispo commande : <strong>{{ number_format($data['disponible_commande'], 0, ',', ' ') }} FCFA</strong>
                </span>
            </div>
        </div>

        <!-- Montant Engagé -->
        <div class="stat-card card" style="background-color: white;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="stat-title">Montant Engagé</h3>
                    <div class="stat-value" style="color: var(--primary-600); font-size: 1.35rem;">
                        {{ number_format($data['total_engage'], 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
                    </div>
                </div>
                <div style="background: var(--primary-50); color: var(--primary-600); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    📝
                </div>
            </div>
            <div class="stat-trend" style="background-color: white;">
                <div style="flex: 1; height: 6px; background: var(--slate-100); border-radius: 3px; overflow: hidden; margin-top: 5px;">
                    <div style="width: {{ min($data['taux_execution'], 100) }}%; height: 100%; background: var(--primary-500); border-radius: 3px;"></div>
                </div>
                <span style="font-weight: 700; color: {{ $data['taux_execution'] > 80 ? 'var(--danger)' : 'var(--primary-700)' }}">
                    {{ $data['taux_execution'] }}%
                </span>
            </div>
        </div>

        <!-- Solde Disponible -->
        <div class="stat-card card" style="background-color: white;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 class="stat-title">Solde Disponible</h3>
                    <div class="stat-value" style="color: var(--success); font-size: 1.35rem;">
                        {{ number_format($data['total_disponible'], 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
                    </div>
                </div>
                <div style="background: var(--success-light); color: var(--success); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    🔓
                </div>
            </div>
            <div class="stat-trend">
                <span style="color: var(--text-muted); font-size: 0.75rem;">Reste à engager sur l'exercice</span>
            </div>
        </div>
    </div>
    @endif

    @if(Auth::user()->role === 'service')
    <div class="card" style="margin-bottom: 2rem; padding: 2.5rem; text-align: center;">
        <h3 style="margin-bottom: 1rem; color: var(--slate-800);">Tableau de bord - Chef de Service</h3>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 2rem auto;">
            Bienvenue sur votre espace de suivi budgétaire. Les allocations sont gérées par compte budgétaire pour l'exercice {{ $data['annee'] }}.
        </p>
        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('commandes.index') }}" class="btn-primary" style="text-decoration: none; padding: 0.75rem 1.75rem;">
                📋 Mes Bons de Commande
            </a>
            <a href="{{ route('engagements.index') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.75rem;">
                📄 Voir les Engagements
            </a>
        </div>
    </div>
    @else
    <!-- ADMIN / COMPTABLE / DIRECTEUR DASHBOARD -->

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card card">
            <h3 class="stat-title" style="margin-bottom: 1rem;">Dotation vs Exécution par Compte (Principaux Comptes)</h3>
            <canvas id="chartComptesBar" height="240"></canvas>
        </div>
        <div class="stat-card card">
            <h3 class="stat-title" style="margin-bottom: 1rem;">Répartition des Allocations par Compte (Top 8)</h3>
            <canvas id="chartComptes" height="240"></canvas>
        </div>
    </div>

    <div class="stat-card card" style="margin-bottom: 2rem;">
        <h3 class="stat-title" style="margin-bottom: 1rem;">Évolution Mensuelle des Engagements ({{ $data['annee'] }})</h3>
        <canvas id="chartEvolution" height="90"></canvas>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Chart 1: Dotation vs Engagé par Compte
            const elBar = document.getElementById('chartComptesBar');
            if (elBar) {
                new Chart(elBar.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($data['chart_comptes_bar']['labels'] ?? []),
                        datasets: [
                            {
                                label: 'Dotation Allouée',
                                data: @json($data['chart_comptes_bar']['alloue'] ?? []),
                                backgroundColor: '#cbd5e1',
                                borderRadius: 4
                            },
                            {
                                label: 'Commandé',
                                data: @json($data['chart_comptes_bar']['commande'] ?? []),
                                backgroundColor: '#38bdf8',
                                borderRadius: 4
                            },
                            {
                                label: 'Engagé',
                                data: @json($data['chart_comptes_bar']['engage'] ?? []),
                                backgroundColor: '#2563eb',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: commonOptions
                });
            }

            // Chart 2: Répartition par Compte
            const elPie = document.getElementById('chartComptes');
            if (elPie) {
                new Chart(elPie.getContext('2d'), {
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
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        },
                        cutout: '65%'
                    }
                });
            }

            // Chart 3: Évolution mensuelle des engagements
            const elEvo = document.getElementById('chartEvolution');
            if (elEvo) {
                new Chart(elEvo.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($data['chart_evolution']['labels'] ?? []),
                        datasets: [{
                            label: 'Montant Engagé (FCFA)',
                            data: @json($data['chart_evolution']['values'] ?? []),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: commonOptions
                });
            }
        });
    </script>
    @endpush

    <div style="display: grid; grid-template-columns: 2.3fr 1fr; gap: 2rem;">
        <!-- Suivi par Compte -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">
                    Exécution Budgétaire par Compte ({{ $data['annee'] }})
                </h3>
                <a href="{{ route('reporting.accounts', ['budget_id' => $data['budget']?->id]) }}" style="font-size: 0.85rem; color: var(--primary-600); text-decoration: none; font-weight: 600;">
                    Voir tous les comptes &rarr;
                </a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.8rem; color: var(--text-muted);">
                            <th style="padding: 0.7rem;">Compte</th>
                            <th style="padding: 0.7rem; text-align: right;">Dotation</th>
                            <th style="padding: 0.7rem; text-align: right;">Commandé</th>
                            <th style="padding: 0.7rem; text-align: right;">Engagé</th>
                            <th style="padding: 0.7rem; text-align: right;">Disponible</th>
                            <th style="padding: 0.7rem; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_comptes'] as $stat)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.7rem;">
                                    <span style="font-family: monospace; font-weight: 700; color: var(--primary-700);">{{ $stat->compte->numero }}</span>
                                    <span style="color: var(--text-muted);">—</span>
                                    <span style="font-weight: 500; font-size: 0.9rem;">{{ $stat->compte->libelle }}</span>
                                </td>
                                <td style="padding: 0.7rem; text-align: right; font-family: monospace;">
                                    {{ number_format($stat->dotation_totale, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.7rem; text-align: right; font-family: monospace; color: #0284c7;">
                                    {{ number_format($stat->total_commandes, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.7rem; text-align: right; font-family: monospace; font-weight: 600;">
                                    {{ number_format($stat->total_engage, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.7rem; text-align: right; font-family: monospace; font-weight: 600; color: {{ $stat->disponible < 0 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ number_format($stat->disponible, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.7rem; text-align: right;">
                                    <span style="
                                        padding: 0.2rem 0.55rem;
                                        border-radius: 99px;
                                        font-size: 0.75rem;
                                        font-weight: 600;
                                        background: {{ $stat->taux > 80 ? 'var(--danger-light)' : 'var(--success-light)' }};
                                        color: {{ $stat->taux > 80 ? 'var(--danger)' : 'var(--success)' }};
                                    ">
                                        {{ round($stat->taux, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                                    Aucune allocation ou exécution enregistrée pour cet exercice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.25rem; font-size: 1.05rem; font-weight: 700;">Accès Rapide</h3>
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <a href="{{ route('reporting.accounts', ['budget_id' => $data['budget']?->id]) }}" class="btn-primary"
                    style="text-align: center; text-decoration: none;">📊 Budget par Compte</a>
                <a href="{{ route('reporting.global', ['budget_id' => $data['budget']?->id]) }}" class="btn-secondary"
                    style="text-align: center; text-decoration: none;">📈 Reporting Global</a>
                <a href="{{ route('commandes.index') }}" class="btn-secondary"
                    style="text-align: center; text-decoration: none;">📋 Bons de Commande</a>
                <a href="{{ route('engagements.index') }}" class="btn-secondary"
                    style="text-align: center; text-decoration: none;">📄 Engagements</a>
                <a href="{{ route('comptes.index') }}" class="btn-secondary"
                    style="text-align: center; text-decoration: none;">📑 Gérer les Comptes & Dotations</a>
            </div>
        </div>
    </div>
    @endif
@endsection
