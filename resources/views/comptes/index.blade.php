@extends('layouts.app')

@section('title', 'Plan Comptable Budgétaire')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List of Comptes -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Comptes Budgétaires</h3>
            <ul style="list-style: none; padding: 0;">
                @foreach($comptes as $compte)
                    <li style="margin-bottom: 0.5rem; border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span
                                    style="font-weight: 700; color: var(--primary-700); margin-right: 0.5rem;">{{ $compte->numero }}</span>
                                <span style="font-weight: 600;">{{ $compte->libelle }}</span>
                                <span
                                    style="font-size: 0.75rem; background: var(--slate-100); padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: 0.5rem;">{{ $compte->type }}</span>
                            </div>
                        </div>

                        <!-- Children -->
                        @if($compte->children->count() > 0)
                            <ul
                                style="list-style: none; padding-left: 2rem; margin-top: 0.5rem; border-left: 2px solid var(--slate-100);">
                                @foreach($compte->children as $child)
                                    <li style="margin-bottom: 0.25rem; display: flex; justify-content: space-between;">
                                        <div>
                                            <span
                                                style="font-weight: 600; color: var(--slate-600); margin-right: 0.5rem;">{{ $child->numero }}</span>
                                            <span>{{ $child->libelle }}</span>
                                        </div>
                                        <form action="{{ route('comptes.destroy', $child) }}" method="POST" style="display: inline;"
                                            onsubmit="return confirm('Confirmer suppression ?')">
                                            @csrf @method('DELETE')
                                            <button
                                                style="border: none; background: none; color: var(--text-muted); cursor: pointer; font-size: 0.8rem;">🗑️</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Create Compte Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Nouveau Compte</h3>
            <form action="{{ route('comptes.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Numéro de Compte</label>
                    <input type="text" name="numero" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé</label>
                    <input type="text" name="libelle" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Type</label>
                    <select name="type" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="Fonctionnement">Fonctionnement</option>
                        <option value="Investissement">Investissement</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Parent</label>
                    <select name="parent_id"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <option value="">Aucun (Racine)</option>
                        @foreach($allComptes as $c)
                            <option value="{{ $c->id }}">{{ $c->numero }} - {{ $c->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Ajouter Compte</button>
            </form>
        </div>
    </div>
@endsection