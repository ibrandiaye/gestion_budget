@extends('layouts.app')

@section('title', 'Modifier le Marché')

@section('content')
    <style>
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.5rem;
            box-sizing: border-box;
            font-family: inherit;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .btn-secondary {
            padding: 0.5rem 1rem;
            background: var(--slate-200, #e2e8f0);
            color: var(--slate-700, #334155);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-danger {
            padding: 0.5rem 1rem;
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
    <div style="margin-bottom: 2rem;">
        <h2>✏️ Modifier le Marché : {{ $marche->intitule }}</h2>
        <a href="{{ route('marches.show', $marche) }}" style="color: var(--text-muted); text-decoration: none;">&larr; Retour au marché</a>
    </div>

    <div class="card">
        @if ($errors->any())
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('marches.update', $marche) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label>Date</label>
                    <input type="date" name="date" required class="form-control" value="{{ old('date', $marche->date ? $marche->date->format('Y-m-d') : date('Y-m-d')) }}">
                </div>
                <div>
                    <label>Intitulé du marché</label>
                    <input type="text" name="intitule" required class="form-control" value="{{ old('intitule', $marche->intitule) }}" placeholder="Ex: Achat de matériel informatique">
                </div>
                
                <div>
                    <label>Compte d'imputation</label>
                    <select name="compte_id" id="compteSelect" required class="form-control">
                        <option value="">Sélectionnez un compte...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" 
                                    data-articles="{{ json_encode($compte->articles) }}"
                                    {{ (old('compte_id', $marche->compte_id) == $compte->id) ? 'selected' : '' }}>
                                {{ $compte->numero }} - {{ $compte->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Année / Exercice</label>
                    <select name="budget_id" required class="form-control">
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->id }}" {{ (old('budget_id', $marche->budget_id) == $budget->id) ? 'selected' : '' }}>
                                {{ $budget->annee }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Nature</label>
                    <select name="nature" required class="form-control">
                        <option value="Fonctionnement" {{ (old('nature', $marche->nature) == 'Fonctionnement') ? 'selected' : '' }}>Fonctionnement</option>
                        <option value="Investissement" {{ (old('nature', $marche->nature) == 'Investissement') ? 'selected' : '' }}>Investissement</option>
                    </select>
                </div>

                <div>
                    <label>Type de prestation</label>
                    <select name="type" required class="form-control">
                        <option value="Fourniture" {{ (old('type', $marche->type) == 'Fourniture') ? 'selected' : '' }}>Fourniture</option>
                        <option value="Travaux" {{ (old('type', $marche->type) == 'Travaux') ? 'selected' : '' }}>Travaux</option>
                        <option value="Prestation de service" {{ (old('type', $marche->type) == 'Prestation de service') ? 'selected' : '' }}>Prestation de service</option>
                        <option value="Prestation Intellectuelle" {{ (old('type', $marche->type) == 'Prestation Intellectuelle') ? 'selected' : '' }}>Prestation Intellectuelle</option>
                    </select>
                </div>

                <div>
                    <label>Fournisseur (Titulaire)</label>
                    <select name="fournisseur_id" required class="form-control">
                        <option value="">Sélectionnez un fournisseur...</option>
                        @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}" {{ (old('fournisseur_id', $marche->fournisseur_id) == $fournisseur->id) ? 'selected' : '' }}>
                                {{ $fournisseur->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Montant (FCFA) - Calculé automatiquement</label>
                    <input type="number" name="montant" id="montant-input" required class="form-control" value="{{ old('montant', $marche->montant) }}" readonly style="background: var(--slate-100); font-weight: bold; color: var(--primary);">
                </div>
            </div>

            <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Articles du Marché</h3>
            
            @php
                $currentCompte = $comptes->firstWhere('id', old('compte_id', $marche->compte_id));
                $currentArticles = $currentCompte ? $currentCompte->articles : collect();
                $existingRows = $marche->articles->isNotEmpty() ? $marche->articles : [null];
            @endphp

            <div id="articles-container">
                @foreach($existingRows as $idx => $art)
                <div class="article-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                    <div>
                        <label>Article</label>
                        <select name="articles[{{ $idx }}][article_id]" required class="form-control article-select">
                            <option value="">Choisir un article...</option>
                            @foreach($currentArticles as $compteArticle)
                                @php
                                    $isSelected = false;
                                    if (old("articles.{$idx}.article_id")) {
                                        $isSelected = old("articles.{$idx}.article_id") == $compteArticle->id;
                                    } elseif ($art) {
                                        $isSelected = ($art->article_id == $compteArticle->id) || ($art->designation == $compteArticle->designation);
                                    }
                                @endphp
                                <option value="{{ $compteArticle->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $compteArticle->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Conditionnement</label>
                        <input type="text" name="articles[{{ $idx }}][conditionnement]" class="form-control" value="{{ old("articles.{$idx}.conditionnement", $art?->conditionnement) }}">
                    </div>
                    <div>
                        <label>Quantité</label>
                        <input type="number" name="articles[{{ $idx }}][quantite]" required class="form-control" min="1" value="{{ old("articles.{$idx}.quantite", $art?->quantite ?? 1) }}">
                    </div>
                    <div>
                        <label>Prix Unitaire</label>
                        <input type="number" name="articles[{{ $idx }}][prix_unitaire]" required class="form-control" min="0" value="{{ old("articles.{$idx}.prix_unitaire", $art?->prix_unitaire ?? 0) }}">
                    </div>
                    <div>
                        <button type="button" class="btn-danger remove-article" style="{{ count($existingRows) > 1 ? '' : 'display: none;' }}">X</button>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" id="add-article" class="btn-secondary" style="margin-bottom: 2rem;">+ Ajouter un article</button>

            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('marches.show', $marche) }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('add-article').addEventListener('click', function() {
            const container = document.getElementById('articles-container');
            const rowCount = container.children.length;
            
            const newRow = container.children[0].cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${rowCount}]`);
                if (el.tagName === 'INPUT') {
                    if (el.type !== 'number' || el.name.includes('prix')) el.value = '';
                    if (el.name.includes('quantite')) el.value = '1';
                }
            });
            
            newRow.querySelector('.remove-article').style.display = 'block';
            newRow.querySelector('.remove-article').addEventListener('click', function() {
                this.closest('.article-row').remove();
                calculateTotal();
            });
            
            container.appendChild(newRow);
        });

        document.querySelectorAll('.remove-article').forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelectorAll('.article-row').length > 1) {
                    this.closest('.article-row').remove();
                    calculateTotal();
                }
            });
        });

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.article-row').forEach(row => {
                const qteInput = row.querySelector('input[name$="[quantite]"]');
                const prixInput = row.querySelector('input[name$="[prix_unitaire]"]');
                if (qteInput && prixInput) {
                    const qte = parseFloat(qteInput.value) || 0;
                    const prix = parseFloat(prixInput.value) || 0;
                    total += (qte * prix);
                }
            });
            document.getElementById('montant-input').value = total;
        }

        document.getElementById('articles-container').addEventListener('input', calculateTotal);
        
        document.getElementById('compteSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const articles = selectedOption.value ? JSON.parse(selectedOption.getAttribute('data-articles') || '[]') : [];
            
            let optionsHtml = '<option value="">Choisir un article...</option>';
            articles.forEach(a => {
                optionsHtml += `<option value="${a.id}">${a.designation}</option>`;
            });
            
            if (!selectedOption.value) {
                optionsHtml = '<option value="">Sélectionnez d\'abord un compte...</option>';
            }

            document.querySelectorAll('.article-select').forEach(select => {
                select.innerHTML = optionsHtml;
            });
        });
    </script>
@endsection
