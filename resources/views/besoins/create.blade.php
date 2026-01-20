@extends('layouts.app')

@section('title', 'Nouveau Besoin')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <a href="{{ route('besoins.index') }}"
            style="display: inline-block; margin-bottom: 1.5rem; color: var(--text-muted); text-decoration: none;">
            ← Retour à la liste
        </a>

        <div class="card">
            <form action="{{ route('besoins.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Budgétaire</label>
                    <select name="compte_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit;">
                        <option value="">Sélectionner un compte...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}">{{ $compte->numero }} - {{ $compte->libelle }}</option>
                        @endforeach
                    </select>
                    @error('compte_id') <div style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé du besoin</label>
                    <input type="text" name="libelle" required placeholder="Ex: Achat de rames de papier"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit;">
                    @error('libelle') <div style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Quantité</label>
                    <input type="number" name="quantite" required min="1" value="1"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit;">
                    @error('quantite') <div style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Justification / Détails supplémentaires</label>
                    <textarea name="justification" required rows="4"
                        placeholder="Expliquez pourquoi ce budget est nécessaire..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit; resize: vertical;"></textarea>
                    @error('justification') <div style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $message }}</div> @enderror
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" onclick="history.back()"
                        style="padding: 0.75rem 1.5rem; background: white; border: 1px solid var(--border-color); border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer le besoin</button>
                </div>
            </form>
        </div>
    </div>
@endsection