@extends('layouts.app')

@section('title', 'Nouveau Marché')

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
        <h2>+ Nouveau Marché</h2>
        <a href="{{ route('marches.index') }}" style="color: var(--text-muted); text-decoration: none;">&larr; Retour à la liste</a>
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

        <form action="{{ route('marches.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label>Date</label>
                    <input type="date" name="date" required class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label>Intitulé du marché</label>
                    <input type="text" name="intitule" required class="form-control" placeholder="Ex: Achat de matériel informatique">
                </div>
                
                <div>
                    <label>Compte d'imputation</label>
                    <select name="compte_id" id="compteSelect" required class="form-control">
                        <option value="">Sélectionnez un compte...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" data-articles="{{ json_encode($compte->articles) }}">{{ $compte->numero }} - {{ $compte->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Année / Exercice</label>
                    <select name="budget_id" required class="form-control">
                        @foreach($budgets as $budget)
                            <option value="{{ $budget->id }}">{{ $budget->annee }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Nature</label>
                    <select name="nature" required class="form-control">
                        <option value="Fonctionnement">Fonctionnement</option>
                        <option value="Investissement">Investissement</option>
                    </select>
                </div>

                <div>
                    <label>Type de prestation</label>
                    <select name="type" required class="form-control">
                        <option value="Fourniture">Fourniture</option>
                        <option value="Travaux">Travaux</option>
                        <option value="Prestation de service">Prestation de service</option>
                        <option value="Prestation Intellectuelle">Prestation Intellectuelle</option>
                    </select>
                </div>

                <div>
                    <label>Fournisseur (Titulaire)</label>
                    <select name="fournisseur_id" required class="form-control">
                        <option value="">Sélectionnez un fournisseur...</option>
                        @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Montant (FCFA) - Calculé automatiquement</label>
                    <input type="number" name="montant" id="montant-input" required class="form-control" value="0" readonly style="background: var(--slate-100); font-weight: bold; color: var(--primary);">
                </div>
            </div>

            <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Articles du Marché</h3>
            
            <div id="articles-container">
                <div class="article-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                    <div>
                        <label>Article</label>
                        <select name="articles[0][article_id]" required class="form-control article-select">
                            <option value="">Sélectionnez d'abord un compte...</option>
                        </select>
                    </div>
                    <div>
                        <label>Conditionnement</label>
                        <input type="text" name="articles[0][conditionnement]" class="form-control">
                    </div>
                    <div>
                        <label>Quantité</label>
                        <input type="number" name="articles[0][quantite]" required class="form-control" min="1" value="1">
                    </div>
                    <div>
                        <label>Prix Unitaire</label>
                        <input type="number" name="articles[0][prix_unitaire]" required class="form-control" min="0">
                    </div>
                    <div>
                        <button type="button" class="btn-danger remove-article" style="display: none;">X</button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-article" class="btn-secondary" style="margin-bottom: 2rem;">+ Ajouter un article</button>

            <div style="text-align: right;">
                <button type="submit" class="btn-primary">Créer le Marché</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('add-article').addEventListener('click', function() {
            const container = document.getElementById('articles-container');
            const rowCount = container.children.length;
            
            const newRow = container.children[0].cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\[0\]/, `[${rowCount}]`);
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
