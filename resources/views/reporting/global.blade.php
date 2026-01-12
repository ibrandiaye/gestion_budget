@extends('layouts.app')

@section('title', 'Reporting Global')

@section('content')
    <div class="grid-stats" style="margin-bottom: 2rem;">
        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Budget
                Global</h3>
            <div style="font-size: 2rem; font-weight: 700; color: var(--slate-900);">
                {{ number_format($totalAlloue, 0, ',', ' ') }} <span style="font-size: 1rem;">FCFA</span></div>
        </div>

        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total
                Engagé</h3>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-600);">
                {{ number_format($totalEngage, 0, ',', ' ') }} <span style="font-size: 1rem;">FCFA</span></div>
        </div>

        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Taux
                d'Exécution Global</h3>
            <div
                style="font-size: 2rem; font-weight: 700; color: {{ $tauxExecution > 90 ? 'var(--danger)' : ($tauxExecution > 50 ? 'var(--warning)' : 'var(--success)') }};">
                {{ number_format($tauxExecution, 1) }}%
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Stats per Service -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Détail par Service</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr
                        style="text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
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
        </div>

        <!-- Top Engagements -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Top 5 Lignes</h3>
            @foreach($topLignes as $ligne)
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--slate-100);">
                    <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">{{ $ligne->compte->libelle }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        {{ $ligne->service->nom }}
                    </div>
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