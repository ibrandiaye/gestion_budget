@extends('layouts.app')

@section('title', 'Nouvelle Commande')

@section('content')
    <div style="max-width: 900px; margin: 0 auto;">
        <a href="{{ route('commandes.index') }}" style="display: inline-block; margin-bottom: 1.5rem; color: var(--text-muted); text-decoration: none;">
            ← Retour à la liste
        </a>

        @if ($errors->any())
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">📋 Nouveau Bon de Commande</h3>

            <form action="{{ route('commandes.store') }}" method="POST" id="commandeForm">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Budgétaire *</label>
                        <select name="compte_id" id="compteSelect" required onchange="loadFournisseurs()"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="">Sélectionner un compte...</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}">{{ $compte->numero }} - {{ $compte->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fournisseur *</label>
                        <select name="fournisseur_id" id="fournisseurSelect" required onchange="loadArticlesFournisseur()"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="">Sélectionner d'abord un compte...</option>
                        </select>
                        <input type="hidden" name="fournisseur" id="fournisseurNom">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Référence</label>
                        <input type="text" name="reference" placeholder="N° facture/bon"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Caisse</label>
                        <input type="text" name="caisse" value="Caisse d'avance"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <label style="font-weight: 700;">Articles de la commande</label>
                        <button type="button" onclick="addArticleLine()" class="btn-secondary" style="padding: 0.5rem 1rem;">+ Ajouter article</button>
                    </div>
                    
                    <div id="articlesContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- Article lines will be added here -->
                    </div>
                </div>

                <div style="background: var(--primary-50); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
                        <span style="font-weight: 600;">TOTAL TTC:</span>
                        <span id="totalDisplay" style="font-size: 1.5rem; font-weight: 800; color: var(--primary-700);">0 FCFA</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" onclick="history.back()" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">Créer la Commande</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let articleCount = 0;
        let currentFournisseurArticles = [];
        let selectedCompteId = null;

        async function loadFournisseurs() {
            const compteId = document.getElementById('compteSelect').value;
            const fournisseurSelect = document.getElementById('fournisseurSelect');
            
            selectedCompteId = compteId;
            currentFournisseurArticles = [];
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            
            if (!compteId) {
                fournisseurSelect.innerHTML = '<option value="">Sélectionner d\'abord un compte...</option>';
                return;
            }

            try {
                const response = await fetch(`/api/fournisseurs/compte/${compteId}`);
                const fournisseurs = await response.json();
                
                let options = '<option value="">Choisir un fournisseur...</option>';
                fournisseurs.forEach(f => {
                    options += `<option value="${f.id}" data-nom="${f.nom}" data-articles='${JSON.stringify(f.articles)}'>${f.nom}</option>`;
                });
                
                fournisseurSelect.innerHTML = options;
            } catch (e) {
                console.error('Erreur chargement fournisseurs:', e);
                fournisseurSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            }
        }

        function loadArticlesFournisseur() {
            const fournisseurSelect = document.getElementById('fournisseurSelect');
            const option = fournisseurSelect.options[fournisseurSelect.selectedIndex];
            
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            
            if (!option.value) {
                currentFournisseurArticles = [];
                document.getElementById('fournisseurNom').value = '';
                return;
            }

            document.getElementById('fournisseurNom').value = option.getAttribute('data-nom');
            currentFournisseurArticles = JSON.parse(option.getAttribute('data-articles') || '[]');
            
            // Add first article line
            addArticleLine();
        }

        function addArticleLine() {
            const container = document.getElementById('articlesContainer');
            const index = articleCount++;

            const div = document.createElement('div');
            div.className = 'article-line';
            div.id = 'article-line-' + index;
            div.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 0.5rem; align-items: end; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;';

            let articleOptions = '<option value="">Choisir article...</option>';
            currentFournisseurArticles.forEach(a => {
                articleOptions += `<option value="${a.id}" data-designation="${a.designation}" data-prix="${a.pivot.prix}">${a.designation} (${new Intl.NumberFormat().format(a.pivot.prix)} FCFA)</option>`;
            });

            div.innerHTML = `
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Article *</label>
                    <select name="articles[${index}][article_id]" onchange="selectArticle(this)" class="article-select"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        ${articleOptions}
                    </select>
                    <input type="hidden" name="articles[${index}][designation]" class="designation-hidden">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Conditionnement</label>
                    <input type="text" name="articles[${index}][conditionnement]" placeholder="ex: 38 unités"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Quantité *</label>
                    <input type="number" name="articles[${index}][quantite]" required min="1" value="1" onchange="calculateTotal()" class="qte-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: center;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Prix Unitaire *</label>
                    <input type="number" name="articles[${index}][prix_unitaire]" required min="0" value="0" onchange="calculateTotal()" class="prix-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: right;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Montant HT</label>
                    <input type="text" class="montant-display" readonly value="0 FCFA"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: right; background: var(--slate-100);">
                </div>
                <button type="button" onclick="removeArticleLine(${index})" 
                    style="background: #fee2e2; color: #b91c1c; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer;">🗑️</button>
            `;

            container.appendChild(div);
            calculateTotal();
        }

        function selectArticle(select) {
            const option = select.options[select.selectedIndex];
            const line = select.closest('.article-line');
            
            if (option.value) {
                const prix = parseFloat(option.getAttribute('data-prix')) || 0;
                const designation = option.getAttribute('data-designation');
                
                line.querySelector('.prix-input').value = prix;
                line.querySelector('.designation-hidden').value = designation;
            } else {
                line.querySelector('.prix-input').value = 0;
                line.querySelector('.designation-hidden').value = '';
            }
            
            calculateTotal();
        }

        function removeArticleLine(index) {
            const line = document.getElementById('article-line-' + index);
            if (document.querySelectorAll('.article-line').length > 1) {
                line.remove();
                calculateTotal();
            }
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.article-line').forEach(line => {
                const qte = parseInt(line.querySelector('.qte-input').value) || 0;
                const prix = parseFloat(line.querySelector('.prix-input').value) || 0;
                const montant = qte * prix;
                line.querySelector('.montant-display').value = new Intl.NumberFormat().format(montant) + ' FCFA';
                total += montant;
            });
            document.getElementById('totalDisplay').textContent = new Intl.NumberFormat().format(total) + ' FCFA';
        }
    </script>
@endsection
