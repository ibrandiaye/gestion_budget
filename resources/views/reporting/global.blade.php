@extends('layouts.app')

@section('title', 'Reporting Global')

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

    <!-- En-tête et sélecteur d'exercice -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--slate-800);">
                Reporting Budgétaire Global @if(isset($budget) && $budget) — Exercice {{ $budget->annee }} @endif
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                Consolidation des allocations annuelles par compte, commandes et engagements
            </p>
        </div>

        @if(isset($budgets) && $budgets->count() > 1)
            <form action="{{ route('reporting.global') }}" method="GET" style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; padding: 0.45rem 0.85rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin: 0;">Exercice :</label>
                <select name="budget_id" onchange="this.form.submit()" style="padding: 0.35rem 0.6rem; border-radius: 0.375rem; border: 1px solid var(--border-color); font-size: 0.85rem; font-weight: 600; cursor: pointer; background: var(--slate-50);">
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}" {{ (isset($budget) && $budget && $budget->id == $b->id) ? 'selected' : '' }}>
                            {{ $b->annee }} ({{ ucfirst($b->statut) }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    <div class="grid-stats">
        <!-- Dotation Globale -->
        <div class="stat-card card">
            <h3 class="stat-title">Dotation Globale ({{ $budget->annee ?? 'N/A' }})</h3>
            <div class="stat-value" style="font-size: 1.35rem;">
                {{ number_format($totalAlloue, 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend">
                <span>Total des allocations par compte</span>
            </div>
        </div>

        <!-- Total Commandé -->
        <div class="stat-card card">
            <h3 class="stat-title">Total Commandé</h3>
            <div class="stat-value" style="color: #0284c7; font-size: 1.35rem;">
                {{ number_format($totalCommandes ?? 0, 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend">
                <span>Dispo commande : {{ number_format($totalAlloue - ($totalCommandes ?? 0), 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- Total Engagé -->
        <div class="stat-card card">
            <h3 class="stat-title">Total Engagé</h3>
            <div class="stat-value" style="color: var(--primary-600); font-size: 1.35rem;">
                {{ number_format($totalEngage, 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend" style="margin-top: 5px;">
                <div style="flex: 1; height: 6px; background: var(--slate-100); border-radius: 3px; overflow: hidden;">
                    <div style="width: {{ min($tauxExecution, 100) }}%; height: 100%; background: var(--primary-500);"></div>
                </div>
                <span style="font-weight: 700;">{{ number_format($tauxExecution, 1) }}%</span>
            </div>
        </div>

        <!-- Solde Disponible -->
        <div class="stat-card card">
            <h3 class="stat-title">Solde Disponible</h3>
            <div class="stat-value" style="color: var(--success); font-size: 1.35rem;">
                {{ number_format($totalAlloue - $totalEngage, 0, ',', ' ') }} <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend">
                <span>Reste disponible à engager</span>
            </div>
        </div>
    </div>

    <!-- Rapports et Exports Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem; font-size: 1.05rem;">Rapports Budgétaires & Exports ({{ $budget->annee ?? 'N/A' }})</h3>

        <div style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('reporting.accounts', ['budget_id' => $budget?->id]) }}" class="btn-primary" style="text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                📊 Tableau Budget par Compte
            </a>
            <a href="{{ route('reporting.mshp', ['budget_id' => $budget?->id]) }}" class="btn-primary" style="text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa fa-table"></i> Rapport MSHP (Détaillé)
            </a>
            <a href="{{ route('reporting.mfb', ['budget_id' => $budget?->id]) }}" class="btn-primary" style="text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa fa-table"></i> Rapport MFB (Agrégé)
            </a>
            <a href="{{ route('export.budget.compte', ['budget_id' => $budget?->id]) }}" class="btn-secondary" style="text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                📥 Exporter CSV (Budget par Compte)
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 2rem;">
        <!-- Détail par Compte -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="margin: 0; font-size: 1.05rem;">Détail des Allocations et Exécutions par Compte</h3>
                <a href="{{ route('reporting.accounts', ['budget_id' => $budget?->id]) }}" style="font-size: 0.85rem; color: var(--primary-600); text-decoration: none; font-weight: 600;">
                    Vue complète & impression &rarr;
                </a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 0.82rem;">
                            <th style="padding: 0.75rem;">Compte</th>
                            <th style="padding: 0.75rem; text-align: right;">Dotation Allouée</th>
                            <th style="padding: 0.75rem; text-align: right;">Commandé</th>
                            <th style="padding: 0.75rem; text-align: right;">Engagé</th>
                            <th style="padding: 0.75rem; text-align: right;">Disponible</th>
                            <th style="padding: 0.75rem; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statsComptes as $stat)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem;">
                                    <div style="font-weight: 600; color: var(--slate-800);">{{ $stat->compte->libelle }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                                        N° {{ $stat->compte->numero }}
                                        @if($stat->compte->compteParent)
                                            &bull; {{ $stat->compte->compteParent->type }}
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 600;">
                                    {{ number_format($stat->total_alloue, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace; color: #0284c7;">
                                    {{ number_format($stat->total_commandes, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 600;">
                                    {{ number_format($stat->total_engage, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 600; color: {{ $stat->disponible_engage < 0 ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ number_format($stat->disponible_engage, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <span style="font-weight: 700; color: {{ $stat->taux > 90 ? 'var(--danger)' : 'var(--primary-700)' }}">
                                        {{ number_format($stat->taux, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                                    Aucun compte actif trouvé pour cet exercice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne droite : Synthèse par Catégorie & Top 5 Comptes -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Synthèse par Catégorie -->
            <div class="card">
                <h3 style="margin-bottom: 1.25rem; font-size: 1.05rem;">Synthèse par Catégorie</h3>
                @forelse($statsCategories ?? [] as $cat)
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--slate-100);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.9rem; font-weight: 600;">
                                {{ $cat->parent ? ($cat->parent->numero . ' - ' . $cat->parent->libelle) : 'Autres' }}
                            </span>
                            <span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 99px; background: var(--primary-50); color: var(--primary-700); font-weight: 600;">
                                {{ round($cat->taux, 1) }}%
                            </span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                            Alloué : <strong>{{ number_format($cat->total_alloue, 0, ',', ' ') }}</strong> &bull;
                            Engagé : <strong>{{ number_format($cat->total_engage, 0, ',', ' ') }}</strong>
                        </div>
                        <div style="height: 5px; background: var(--slate-100); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; background: var(--primary-500); width: {{ min($cat->taux, 100) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Aucune catégorie disponible.</p>
                @endforelse
            </div>

            <!-- Top 5 Comptes -->
            <div class="card">
                <h3 style="margin-bottom: 1.25rem; font-size: 1.05rem;">Top 5 Comptes (Engagements)</h3>
                @forelse($topComptes as $stat)
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--slate-100);">
                        <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem;">
                            {{ $stat->compte->numero }} — {{ $stat->compte->libelle }}
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            Dotation : {{ number_format($stat->dotation_totale, 0, ',', ' ') }} FCFA
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="height: 5px; flex: 1; background: var(--slate-100); border-radius: 2px; margin-right: 1rem; overflow: hidden;">
                                <div style="height: 100%; background: var(--primary-500); width: {{ min($stat->taux, 100) }}%;"></div>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; font-family: monospace;">
                                {{ number_format($stat->total_engage, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Aucun engagement enregistré.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
