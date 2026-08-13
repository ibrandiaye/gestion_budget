@extends('layouts.app')

@section('title', 'Reporting Global')

@section('content')
    <style>
        .grid-stats {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 1.5rem !important;
            margin-bottom: 2rem !important;
        }
        @media (max-width: 768px) {
            .grid-stats {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    <div class="grid-stats" style="margin-bottom: 2rem;">
        <div class="stat-card card">
            <h3 class="stat-title">Budget Global</h3>
            <div class="stat-value">
                {{ number_format($totalAlloue, 0, ',', ' ') }} <span style="font-size: 0.9rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend">
                <span>Total alloué sur l'exercice</span>
            </div>
        </div>

        <div class="stat-card card">
            <h3 class="stat-title">Total Engagé</h3>
            <div class="stat-value" style="color: var(--primary-600);">
                {{ number_format($totalEngage, 0, ',', ' ') }} <span style="font-size: 0.9rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend" style="margin-top: 5px;">
                <div style="flex: 1; height: 6px; background: var(--slate-100); border-radius: 3px; overflow: hidden;">
                    <div style="width: {{ min($tauxExecution, 100) }}%; height: 100%; background: var(--primary-500);"></div>
                </div>
                <span style="font-weight: 700;">{{ number_format($tauxExecution, 1) }}%</span>
            </div>
        </div>

        <div class="stat-card card">
            <h3 class="stat-title">Solde Disponible</h3>
            <div class="stat-value" style="color: var(--success);">
                {{ number_format($totalAlloue - $totalEngage, 0, ',', ' ') }} <span style="font-size: 0.9rem; color: var(--text-muted);">FCFA</span>
            </div>
            <div class="stat-trend">
                <span>Reste disponible</span>
            </div>
        </div>
    </div>

    <!-- Exports Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Exports</h3>
        
        <!-- Excel Exports -->
        <div style="margin-bottom: 1.5rem;">
            <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.75rem; font-weight: 600;">📊 Rapports Budgétaires</h4>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('reporting.mshp') }}" class="btn-primary" style="text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa fa-table"></i> Rapport MSHP
                </a>
                <a href="{{ route('reporting.mfb') }}" class="btn-primary" style="text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa fa-table"></i> Rapport MFB (Agrégé)
                </a>
            </div>
        </div>
        
        <!-- CSV Exports -->
        <div>
            <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.75rem; font-weight: 600;">📥 Exports CSV</h4>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('export.budget.service') }}" class="btn-secondary" style="text-decoration: none; font-size: 0.9rem;">
                    📥 Budget par Service
                </a>
                <a href="{{ route('export.budget.compte') }}" class="btn-secondary" style="text-decoration: none; font-size: 0.9rem;">
                    📥 Budget par Compte
                </a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Stats per Service (Admin) or per Account (Service Manager) -->
        <div class="card">
            @if(Auth::user()->role === 'service')
                <h3 style="margin-bottom: 1.5rem;">Détail par Compte</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
                            <th style="padding: 0.75rem;">Compte</th>
                            <th style="padding: 0.75rem; text-align: right;">Budget Alloué</th>
                            <th style="padding: 0.75rem; text-align: right;">Engagé</th>
                            <th style="padding: 0.75rem; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statsComptes as $stat)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem;">
                                    <div style="font-weight: 500;">{{ $stat->compte->libelle }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $stat->compte->numero }}</div>
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    {{ number_format($stat->total_alloue, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    {{ number_format($stat->total_engage, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <span style="font-weight: 600; color: {{ $stat->taux > 90 ? 'var(--danger)' : 'inherit' }}">
                                        {{ number_format($stat->taux, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h3 style="margin-bottom: 1.5rem;">Détail par Service</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
                            <th style="padding: 0.75rem;">Service</th>
                            <th style="padding: 0.75rem; text-align: right;">Budget Alloué</th>
                            <th style="padding: 0.75rem; text-align: right;">Engagé</th>
                            <th style="padding: 0.75rem; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statsServices as $stat)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem; font-weight: 500;">
                                    {{ $stat->service->nom }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    {{ number_format($stat->total_alloue, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    {{ number_format($stat->total_engage, 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <span style="font-weight: 600; color: {{ $stat->taux > 90 ? 'var(--danger)' : 'inherit' }}">
                                        {{ number_format($stat->taux, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Top Engagements -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Top 5 Lignes</h3>
            @foreach($topLignes as $ligne)
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--slate-100);">
                    <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">{{ $ligne->compte->libelle }}
                    </div>
                    @if(Auth::user()->role !== 'service')
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        {{ $ligne->service->nom }}
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div
                            style="height: 4px; flex: 1; background: var(--slate-100); border-radius: 2px; margin-right: 1rem;">
                            <div
                                style="height: 100%; background: var(--primary-500); width: {{ min(($ligne->montant_engage / max($ligne->montant_alloue, 1)) * 100, 100) }}%;">
                            </div>
                        </div>
                        <div style="font-weight: 600; font-size: 0.85rem;">
                            {{ number_format($ligne->montant_engage, 0, ',', ' ') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection