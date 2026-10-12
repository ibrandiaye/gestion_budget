@extends('layouts.app')

@section('title', 'Détails du Marché')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0 0 0.5rem 0;">Marché : {{ $marche->intitule }}</h2>
            <a href="{{ route('marches.index') }}" style="color: var(--text-muted); text-decoration: none;">&larr; Retour à la liste</a>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <span style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                Mode de Passation : <strong>{{ $marche->mode_passation }}</strong>
            </span>
            @if(Auth::user()->can('marches.edit') || Auth::user()->role === 'admin')
                <a href="{{ route('marches.edit', $marche) }}" class="btn-primary" style="padding: 0.5rem 1rem; text-decoration: none; background: #fef9c3; color: #92400e; border: 1px solid #fde68a;">
                    ✏️ Modifier
                </a>
            @endif
            @if(Auth::user()->can('marches.delete') || Auth::user()->role === 'admin')
                <form action="{{ route('marches.destroy', $marche) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce marché ? Cette action est irréversible.');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 0.5rem 1rem; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">
                        🗑 Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <div class="card">
            <h3>Informations Générales</h3>
            <ul style="list-style: none; padding: 0; margin-top: 1rem; line-height: 2;">
                <li><strong>Date :</strong> {{ $marche->date ? $marche->date->format('d/m/Y') : '-' }}</li>
                <li><strong>Compte :</strong> {{ $marche->compte?->numero ?? '-' }} - {{ $marche->compte?->libelle ?? '-' }}</li>
                <li><strong>Nature :</strong> {{ $marche->nature }}</li>
                <li><strong>Type :</strong> {{ $marche->type }}</li>
                <li><strong>Fournisseur :</strong> {{ $marche->fournisseur->nom ?? 'Non défini' }}</li>
                <li><strong>Montant Total :</strong> <span style="color: var(--success); font-weight: bold;">{{ number_format($marche->montant, 0, ',', ' ') }} FCFA</span></li>
            </ul>
        </div>

        <div class="card">
            <h3>Articles du Marché</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">Désignation</th>
                        <th style="padding: 0.75rem; text-align: left;">Conditionnement</th>
                        <th style="padding: 0.75rem; text-align: right;">Qté</th>
                        <th style="padding: 0.75rem; text-align: right;">P.U (FCFA)</th>
                        <th style="padding: 0.75rem; text-align: right;">Total (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marche->articles as $article)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem;">{{ $article->designation }}</td>
                        <td style="padding: 0.75rem;">{{ $article->conditionnement ?? '-' }}</td>
                        <td style="padding: 0.75rem; text-align: right;">{{ $article->quantite }}</td>
                        <td style="padding: 0.75rem; text-align: right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: bold;">{{ number_format($article->prix_total, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--slate-50); border-top: 2px solid var(--border-color);">
                        <td colspan="4" style="padding: 0.75rem; text-align: right; font-weight: bold;">TOTAL :</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: bold; color: var(--success); font-size: 1.1rem;">{{ number_format($marche->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
