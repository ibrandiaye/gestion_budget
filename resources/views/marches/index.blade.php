@extends('layouts.app')

@section('title', 'Gestion des Marchés')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>📝 Liste des Marchés</h2>
        <a href="{{ route('marches.create') }}" class="btn-primary">+ Nouveau Marché</a>
    </div>

    @if($marches->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucun marché enregistré pour le moment.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Intitulé</th>
                        <th style="padding: 0.75rem; text-align: left;">Compte</th>
                        <th style="padding: 0.75rem; text-align: left;">Fournisseur</th>
                        <th style="padding: 0.75rem; text-align: left;">Mode Passation</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marches as $marche)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem;">{{ $marche->date ? $marche->date->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 0.75rem; font-weight: 600;">{{ $marche->intitule }}</td>
                        <td style="padding: 0.75rem;">{{ $marche->compte?->numero ?? '-' }}</td>
                        <td style="padding: 0.75rem;">{{ $marche->fournisseur->nom ?? '-' }}</td>
                        <td style="padding: 0.75rem;">{{ $marche->mode_passation }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($marche->montant, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <a href="{{ route('marches.show', $marche) }}" style="color: var(--primary); text-decoration: none;">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
