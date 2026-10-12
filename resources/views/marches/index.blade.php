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
                            <div style="display: flex; gap: 0.4rem; justify-content: center; align-items: center;">
                                <a href="{{ route('marches.show', $marche) }}" class="btn-primary" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; text-decoration: none;">
                                    👁 Voir
                                </a>
                                @if(Auth::user()->can('marches.edit') || Auth::user()->role === 'admin')
                                    <a href="{{ route('marches.edit', $marche) }}" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; text-decoration: none; background: #fef9c3; color: #92400e; border-radius: 0.4rem; border: 1px solid #fde68a;">
                                        ✏️ Modifier
                                    </a>
                                @endif
                                @if(Auth::user()->can('marches.delete') || Auth::user()->role === 'admin')
                                    <form action="{{ route('marches.destroy', $marche) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce marché ? Cette action est irréversible.');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 0.4rem; cursor: pointer;">
                                            🗑 Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
