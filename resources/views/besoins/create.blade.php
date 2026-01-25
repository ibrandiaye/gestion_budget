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
                    <select name="compte_id" id="compteSelect" required onchange="loadArticles()"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit;">
                        <option value="">Sélectionner un compte...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" data-articles="{{ $compte->articles->toJson() }}">
                                {{ $compte->numero }} - {{ $compte->libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('compte_id') <div style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Article</label>
                    <select name="article_id" id="articleSelect"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-family: inherit;">
                        <option value="">Sélectionner d'abord un compte...</option>
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">Choisissez un article ou laissez vide pour saisir manuellement</small>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Libellé du besoin</label>
                    <input type="text" name="libelle" id="libelleInput" required placeholder="Ex: Achat de rames de papier"
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

    <script>
        function loadArticles() {
            const compteSelect = document.getElementById('compteSelect');
            const articleSelect = document.getElementById('articleSelect');
            const libelleInput = document.getElementById('libelleInput');
            
            const selectedOption = compteSelect.options[compteSelect.selectedIndex];
            
            if (!selectedOption.value) {
                articleSelect.innerHTML = '<option value="">Sélectionner d\'abord un compte...</option>';
                return;
            }
            
            const articles = JSON.parse(selectedOption.getAttribute('data-articles') || '[]');
            
            let options = '<option value="">-- Aucun article (saisie manuelle) --</option>';
            articles.forEach(article => {
                if (article.actif) {
                    options += `<option value="${article.id}" data-designation="${article.designation}">${article.designation}</option>`;
                }
            });
            
            articleSelect.innerHTML = options;
        }

        // When an article is selected, populate the libelle field
        document.getElementById('articleSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const libelleInput = document.getElementById('libelleInput');
            
            if (selectedOption.value && selectedOption.getAttribute('data-designation')) {
                libelleInput.value = selectedOption.getAttribute('data-designation');
            }
        });
    </script>
@endsection