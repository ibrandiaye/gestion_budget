@extends('layouts.app')

@section('title', 'Détails du Budget ' . $budget->annee)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin: 0;">Budget Exercice {{ $budget->annee }}</h2>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0 0;">
            Période : {{ \Carbon\Carbon::parse($budget->date_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($budget->date_fin)->format('d/m/Y') }}
            &bull; Statut : 
            <span style="font-weight: 600; color: {{ $budget->statut == 'actif' ? 'var(--success)' : 'var(--text-muted)' }}">{{ strtoupper($budget->statut) }}</span>
        </p>
    </div>
    <a href="{{ route('budgets.index') }}" class="btn-secondary" style="text-decoration: none;">Retour</a>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Montant Total Alloué</h4>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($totalAlloue, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Montant Total Engagé</h4>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-600);">{{ number_format($totalEngage, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="card" style="text-align: center;">
        <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Solde Disponible</h4>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ number_format($totalAlloue - $totalEngage, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">Lignes Budgétaires ({{ $budget->lignes->count() }})</h3>
    
    @if($budget->lignes->count() > 0)
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted); font-size: 0.85rem;">
                <th style="padding: 0.75rem;">N° Compte</th>
                <th style="padding: 0.75rem;">Libellé Compte</th>
                <th style="padding: 0.75rem;">Service</th>
                <th style="padding: 0.75rem; text-align: right;">Montant Alloué</th>
                <th style="padding: 0.75rem; text-align: right;">Engagé</th>
                <th style="padding: 0.75rem; text-align: right;">Disponible</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budget->lignes as $ligne)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem; font-family: monospace;">{{ $ligne->compte->numero ?? '-' }}</td>
                <td style="padding: 0.75rem; font-weight: 500;">{{ $ligne->compte->libelle ?? '-' }}</td>
                <td style="padding: 0.75rem;">{{ $ligne->service ? $ligne->service->nom : 'Global' }}</td>
                <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($ligne->montant_alloue, 0, ',', ' ') }}</td>
                <td style="padding: 0.75rem; text-align: right;">{{ number_format($ligne->montant_engage, 0, ',', ' ') }}</td>
                <td style="padding: 0.75rem; text-align: right; color: var(--success);">{{ number_format($ligne->montant_alloue - $ligne->montant_engage, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
        <p>Aucune ligne budgétaire pour cet exercice.</p>
        <p style="font-size: 0.85rem;">Vous pouvez importer un budget via la page précédente.</p>
    </div>
    @endif
</div>
@endsection
