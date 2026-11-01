@extends('layouts.app')

@section('title', 'Détails du Budget ' . $budget->annee)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="margin: 0;">Budget Exercice {{ $budget->annee }}</h2>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0 0;">
            Période : {{ \Carbon\Carbon::parse($budget->date_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($budget->date_fin)->format('d/m/Y') }}
            &bull; Statut : 
            <span style="font-weight: 600; color: {{ $budget->statut == 'actif' ? 'var(--success)' : 'var(--text-muted)' }}">{{ strtoupper($budget->statut) }}</span>
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('reporting.accounts', ['budget_id' => $budget->id]) }}" class="btn-secondary" style="text-decoration: none; background: #fee2e2; color: #b91c1c; border-color: #fecaca;">📋 Vue Budget par Compte</a>
        <a href="{{ route('budgets.index') }}" class="btn-secondary" style="text-decoration: none;">← Retour</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.82rem; text-transform: uppercase;">Dotation Totale</h4>
        <div style="font-size: 1.4rem; font-weight: 800;">{{ number_format($totalAlloue, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.82rem; text-transform: uppercase;">Total Commandé</h4>
        <div style="font-size: 1.4rem; font-weight: 800; color: #f59e0b;">{{ number_format($totalCommandes ?? 0, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.82rem; text-transform: uppercase;">Montant Engagé</h4>
        <div style="font-size: 1.4rem; font-weight: 800; color: var(--primary-600);">{{ number_format($totalEngage, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.82rem; text-transform: uppercase;">Solde Disponible</h4>
        <div style="font-size: 1.4rem; font-weight: 800; color: var(--success);">{{ number_format($totalAlloue - $totalEngage, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">Allocations Budgétaires par Compte ({{ $comptesBudget->count() }})</h3>
    
    @if($comptesBudget->count() > 0)
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted); font-size: 0.82rem; background: var(--slate-50);">
                    <th style="padding: 0.75rem;">N° Compte</th>
                    <th style="padding: 0.75rem;">Libellé Compte</th>
                    <th style="padding: 0.75rem;">Catégorie</th>
                    <th style="padding: 0.75rem; text-align: right;">Dotation Initiale</th>
                    <th style="padding: 0.75rem; text-align: right;">Virement</th>
                    <th style="padding: 0.75rem; text-align: right;">Dotation Totale</th>
                    <th style="padding: 0.75rem; text-align: right;">Commandé</th>
                    <th style="padding: 0.75rem; text-align: right;">Engagé</th>
                    <th style="padding: 0.75rem; text-align: right;">Disponible</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comptesBudget as $item)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem; font-family: monospace; font-weight: 700; color: var(--primary-700);">
                        {{ $item->compte->numero }}
                    </td>
                    <td style="padding: 0.75rem; font-weight: 500;">
                        {{ $item->compte->libelle }}
                    </td>
                    <td style="padding: 0.75rem; font-size: 0.82rem; color: var(--text-muted);">
                        {{ $item->compte->compteParent->libelle ?? '-' }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">
                        {{ number_format($item->dotation_initiale, 0, ',', ' ') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace; color: {{ $item->virement > 0 ? '#166534' : ($item->virement < 0 ? '#dc2626' : 'var(--text-muted)') }};">
                        {{ $item->virement > 0 ? '+' : '' }}{{ number_format($item->virement, 0, ',', ' ') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 700;">
                        {{ number_format($item->dotation_totale, 0, ',', ' ') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">
                        {{ number_format($item->total_commandes, 0, ',', ' ') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace; color: var(--primary-600);">
                        {{ number_format($item->total_engage, 0, ',', ' ') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 700; color: {{ $item->disponible < 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($item->disponible, 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: var(--slate-100); font-weight: 800; border-top: 2px solid var(--border-color);">
                    <td colspan="3" style="padding: 0.75rem;">TOTAL EXERCICE {{ $budget->annee }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">{{ number_format($comptesBudget->sum('dotation_initiale'), 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">{{ number_format($comptesBudget->sum('virement'), 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">{{ number_format($totalAlloue, 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">{{ number_format($totalCommandes, 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace;">{{ number_format($totalEngage, 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-family: monospace; color: var(--success);">{{ number_format($totalAlloue - $totalEngage, 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
        <p>Aucun compte actif trouvé pour cet exercice.</p>
    </div>
    @endif
</div>
@endsection
