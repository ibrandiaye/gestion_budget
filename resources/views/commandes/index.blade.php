@extends('layouts.app')

@section('title', 'Bons de Commande')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>📋 Bons de Commande</h2>
        <a href="{{ route('commandes.create') }}" class="btn-primary">+ Nouvelle Commande</a>
    </div>

    @if($commandes->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--text-muted);">Aucune commande pour le moment.</p>
            <a href="{{ route('commandes.create') }}" class="btn-primary" style="margin-top: 1rem;">Créer une commande</a>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--slate-50);">
                        <th style="padding: 0.75rem; text-align: left;">N°</th>
                        <th style="padding: 0.75rem; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; text-align: left;">Compte</th>
                        <th style="padding: 0.75rem; text-align: left;">Fournisseur</th>
                        <th style="padding: 0.75rem; text-align: right;">Montant</th>
                        <th style="padding: 0.75rem; text-align: center;">Statut</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commandes as $commande)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem; font-weight: 600;">{{ $commande->numero }}</td>
                        <td style="padding: 0.75rem;">{{ $commande->date->format('d/m/Y') }}</td>
                        <td style="padding: 0.75rem;">{{ $commande->compte->numero }} - {{ $commande->compte->libelle }}</td>
                        <td style="padding: 0.75rem;">{{ $commande->fournisseur ?? '-' }}</td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($commande->montant_ttc, 0, ',', ' ') }} FCFA</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            @php
                                $colors = [
                                    'brouillon' => 'background: var(--slate-200); color: var(--slate-700);',
                                    'soumis' => 'background: var(--warning); color: white;',
                                    'valide' => 'background: var(--success); color: white;',
                                    'rejete' => 'background: var(--danger); color: white;',
                                ];
                            @endphp
                            <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; {{ $colors[$commande->statut] ?? '' }}">
                                {{ ucfirst($commande->statut) }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <div style="display: flex; gap: 0.35rem; justify-content: center; align-items: center; flex-wrap: wrap;">
                                <a href="{{ route('commandes.show', $commande) }}" class="btn-primary" style="padding: 0.3rem 0.65rem; font-size: 0.8rem; text-decoration: none;">
                                    👁 Voir
                                </a>
                                <a href="{{ route('commandes.document', $commande) }}" target="_blank" style="padding: 0.3rem 0.65rem; font-size: 0.8rem; text-decoration: none; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; border-radius: 0.4rem;" title="Imprimer le bon">
                                    🖨️ Imprimer
                                </a>
                                
                                @if(in_array($commande->statut, ['brouillon', 'rejete']))
                                    @if(Auth::user()->can('commandes.edit') || Auth::user()->role === 'admin')
                                        <a href="{{ route('commandes.edit', $commande) }}" style="padding: 0.3rem 0.65rem; font-size: 0.8rem; text-decoration: none; background: #fef9c3; color: #92400e; border-radius: 0.4rem; border: 1px solid #fde68a;" title="Modifier">
                                            ✏️ Modifier
                                        </a>
                                    @endif
                                    @if(Auth::user()->can('commandes.delete') || Auth::user()->role === 'admin')
                                        <form action="{{ route('commandes.destroy', $commande) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="padding: 0.3rem 0.65rem; font-size: 0.8rem; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 0.4rem; cursor: pointer;" title="Supprimer">
                                                🗑 Supprimer
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $commandes->links() }}
        </div>
    @endif
@endsection
