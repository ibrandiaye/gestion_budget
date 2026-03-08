@extends('layouts.app')

@section('title', 'Budget par Compte')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Consolidation par Compte Budgétaire
                </h3>
                <p style="color: var(--text-muted);">Cumul de tous les services pour l'exercice actif</p>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="{{ route('reporting.mshp') }}" class="btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa fa-table"></i> Rapport MSHP
                </a>
                <a href="{{ route('reporting.mfb') }}" class="btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa fa-table"></i> Rapport MFB
                </a>
                <a href="{{ route('reporting.global') }}" class="btn-secondary">Vue Globale</a>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr
                    style="border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.85rem; color: var(--text-muted);">
                    <th style="padding: 1rem;">Compte</th>
                    <th style="padding: 1rem;">Libellé</th>
                    <th style="padding: 1rem;">Dotation Initiale</th>
                    <th style="padding: 1rem;">Total Besoins</th>
                    <th style="padding: 1rem;">Total Engagé</th>
                    <th style="padding: 1rem;">Disponible</th>
                    <th style="padding: 1rem;">Taux</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsComptes as $stat)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem; font-family: monospace; font-weight: 600; color: var(--primary-700);">
                            {{ $stat->compte->numero }}
                        </td>
                        <td style="padding: 1rem; font-weight: 500;">
                            {{ $stat->compte->libelle }}
                        </td>
                        <td style="padding: 1rem; font-family: monospace;">
                            {{ number_format($stat->total_alloue, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem; font-family: monospace; color: var(--text-muted);">
                            {{ number_format($stat->total_besoins, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem; font-family: monospace;">
                            {{ number_format($stat->total_engage, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem; font-family: monospace; color: var(--success-dark);">
                            {{ number_format($stat->total_alloue - $stat->total_engage, 0, ',', ' ') }}
                        </td>
                        <td style="padding: 1rem;">
                            @php
                                $percent = $stat->total_alloue > 0 ? ($stat->total_engage / $stat->total_alloue) * 100 : 0;
                            @endphp
                            <span style="
                                padding: 0.2rem 0.6rem; 
                                border-radius: 99px; 
                                font-size: 0.75rem; 
                                background: {{ $percent > 90 ? 'var(--danger-light)' : 'var(--slate-100)' }};
                                color: {{ $percent > 90 ? 'var(--danger)' : 'var(--slate-700)' }};
                            ">
                                {{ round($percent, 1) }}%
                            </span>
                        </td>
                    </tr>
                @endforeach

                <!-- Total Footer Row -->
                <tr style="background: var(--slate-50); font-weight: 700;">
                    <td colspan="2" style="padding: 1rem; text-align: right;">TOTAL GÉNÉRAL</td>
                    <td style="padding: 1rem;">{{ number_format($statsComptes->sum('total_alloue'), 0, ',', ' ') }}</td>
                    <td style="padding: 1rem;">{{ number_format($statsComptes->sum('total_besoins'), 0, ',', ' ') }}</td>
                    <td style="padding: 1rem;">{{ number_format($statsComptes->sum('total_engage'), 0, ',', ' ') }}</td>
                    <td style="padding: 1rem;">
                        {{ number_format($statsComptes->sum('total_alloue') - $statsComptes->sum('total_engage'), 0, ',', ' ') }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection