@extends('layouts.app')

@section('title', 'Rapport MSHP')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Rapport MSHP - Exécution Budgétaire</h3>
                <p style="color: var(--text-muted);">Rapport détaillé par compte</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('export.mshp') }}" class="btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa fa-download"></i> Exporter CSV
                </a>
                <a href="{{ route('reporting.global') }}" class="btn-secondary">Retour</a>
            </div>
        </div>

        @foreach($data as $parentData)
            @php
                $parent = $parentData['parent'];
                $taux_total = $parentData['total_alloue'] > 0 ? ($parentData['total_engage'] / $parentData['total_alloue']) * 100 : 0;
            @endphp
            
            <div style="margin-bottom: 2.5rem;">
                <!-- Parent Header -->
                <div style="background: var(--primary-50); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <h4 style="margin: 0; font-size: 1rem; font-weight: 600; color: var(--primary-700);">
                        {{ $parent->numero }} - {{ $parent->libelle }} ({{ $parent->type }})
                    </h4>
                </div>

                <!-- Comptes Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left; font-size: 0.85rem; color: var(--text-muted);">
                            <th style="padding: 0.75rem;">Numéro</th>
                            <th style="padding: 0.75rem;">Libellé</th>
                            <th style="padding: 0.75rem; text-align: right;">Total Alloué</th>
                            <th style="padding: 0.75rem; text-align: right;">Total Exécuté</th>
                            <th style="padding: 0.75rem; text-align: right;">Taux (%)</th>
                            <th style="padding: 0.75rem; text-align: right;">Restant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parentData['comptes'] as $compte)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem; font-family: monospace; font-weight: 500;">{{ $compte['numero'] }}</td>
                                <td style="padding: 0.75rem;">{{ $compte['libelle'] }}</td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace;">
                                    {{ number_format($compte['alloue'], 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace;">
                                    {{ number_format($compte['engage'], 0, ',', ' ') }}
                                </td>
                                <td style="padding: 0.75rem; text-align: right;">
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; background: {{ $compte['taux'] > 90 ? 'var(--danger-light)' : 'var(--slate-100)' }}; color: {{ $compte['taux'] > 90 ? 'var(--danger)' : 'var(--slate-700)' }};">
                                        {{ round($compte['taux'], 2) }}%
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-family: monospace; color: var(--success-dark);">
                                    {{ number_format($compte['restant'], 0, ',', ' ') }}
                                </td>
                            </tr>
                        @endforeach
                        
                        <!-- Total Row -->
                        <tr style="background: var(--warning-light); font-weight: 700;">
                            <td colspan="2" style="padding: 1rem;">TOTAL {{ $parent->libelle }}</td>
                            <td style="padding: 1rem; text-align: right; font-family: monospace;">
                                {{ number_format($parentData['total_alloue'], 0, ',', ' ') }}
                            </td>
                            <td style="padding: 1rem; text-align: right; font-family: monospace;">
                                {{ number_format($parentData['total_engage'], 0, ',', ' ') }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <span style="padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; background: {{ $taux_total > 90 ? 'var(--danger-light)' : 'var(--primary-100)' }}; color: {{ $taux_total > 90 ? 'var(--danger)' : 'var(--primary-700)' }};">
                                    {{ round($taux_total, 2) }}%
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right; font-family: monospace;">
                                {{ number_format($parentData['total_alloue'] - $parentData['total_engage'], 0, ',', ' ') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endsection
