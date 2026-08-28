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

            <form action="{{ route('commandes.store') }}" method="POST" id="commandeForm" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte Budgétaire *</label>
                        <select name="compte_id" id="compteSelect" required onchange="loadFournisseurs()"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="">Sélectionner un compte...</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}" 
                                    data-fournisseur-requis="{{ $compte->fournisseur_requis ? '1' : '0' }}"
                                    data-articles="{{ json_encode($compte->articles) }}">
                                    {{ $compte->numero }} - {{ $compte->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">A partir de  *</label>
                        <select name="source" id="sourceSelect" required onchange="toggleSource()"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="Facture Pro-forma">Facture Pro-forma</option>
                            <option value="Marché">Marché</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div id="marcheContainer" style="display: none;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Marché lié *</label>
                        <select name="marche_id" id="marcheSelect" onchange="showMarcheDetails()"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="">Sélectionner un marché...</option>
                            @foreach($marches ?? [] as $marche)
                                <option value="{{ $marche->id }}" data-details="{{ json_encode($marche) }}">{{ $marche->numero ?? $marche->intitule }}</option>
                            @endforeach
                        </select>
                        
                        <div id="marcheDetails" style="display: none; margin-top: 1rem; padding: 1rem; background: var(--slate-50); border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 0.9rem;">
                            <!-- details injected via JS -->
                        </div>
                    </div>
                </div>

                <div id="fournisseurMainWrapper">
                <div id="fournisseurSection" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">
                            Fournisseur <span id="fournisseurRequired">*</span>
                        </label>
                        <select name="fournisseur_id" id="fournisseurSelect" onchange="loadArticlesFournisseur()"
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
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fichier du Marché (PDF/IMG)</label>
                        <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png"
                            style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: white;">
                    </div>
                   <!--  <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Caisse</label>
                        <input type="text" name="caisse" value="Caisse d'avance"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    </div> -->
                  
                </div>

                <!-- Mode sans fournisseur - saisie libre des articles -->
                <div id="sansFournisseurSection" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        ℹ️ Ce compte ne nécessite pas de fournisseur. Vous pouvez saisir les articles manuellement.
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Bénéficiaire (optionnel)</label>
                            <input type="text" name="fournisseur_libre" id="fournisseurLibre" placeholder="Nom du bénéficiaire"
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Référence</label>
                            <input type="text" name="reference_libre" placeholder="N° référence"
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        </div>
                    </div>
                </div>
                </div>

                <div id="articlesSection">
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
                </div> <!-- End of articlesSection -->

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" onclick="history.back()" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">Créer la Commande</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let articleCount = 0;
        let currentCompteArticles = [];
        let selectedCompteId = null;
        let fournisseurRequis = true;

        function toggleSource() {
            const source = document.getElementById('sourceSelect').value;
            const marcheContainer = document.getElementById('marcheContainer');
            const articlesSection = document.getElementById('articlesSection');
            const marcheSelect = document.getElementById('marcheSelect');

            if (source === 'Marché') {
                marcheContainer.style.display = 'block';
                articlesSection.style.display = 'none';
                document.getElementById('fournisseurMainWrapper').style.display = 'none';
                document.getElementById('fournisseurSelect').removeAttribute('required');
                marcheSelect.required = true;
            } else {
                marcheContainer.style.display = 'none';
                articlesSection.style.display = 'block';
                document.getElementById('fournisseurMainWrapper').style.display = 'block';
                if (fournisseurRequis && selectedCompteId) {
                    document.getElementById('fournisseurSelect').setAttribute('required', 'required');
                }
                marcheSelect.required = false;
            }
        }

        function showMarcheDetails() {
            const select = document.getElementById('marcheSelect');
            const detailsDiv = document.getElementById('marcheDetails');
            const option = select.options[select.selectedIndex];
            
            if (!option.value) {
                detailsDiv.style.display = 'none';
                return;
            }
            
            try {
                const marche = JSON.parse(option.getAttribute('data-details'));
                const fournisseur = marche.fournisseur ? marche.fournisseur.nom : 'Non défini';
                const montant = new Intl.NumberFormat().format(marche.montant) + ' FCFA';
                
                let articlesHtml = '<ul style="margin-top: 0.5rem; padding-left: 1.5rem; color: var(--slate-600);">';
                if (marche.articles && marche.articles.length > 0) {
                    marche.articles.forEach(art => {
                        articlesHtml += `<li>${art.designation} - ${art.quantite} x ${new Intl.NumberFormat().format(art.prix_unitaire)} FCFA</li>`;
                    });
                } else {
                    articlesHtml += '<li>Aucun article enregistré</li>';
                }
                articlesHtml += '</ul>';

                detailsDiv.innerHTML = `
                    <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--slate-800);">${marche.intitule}</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <div><span style="color: var(--text-muted);">Fournisseur:</span> <strong>${fournisseur}</strong></div>
                        <div><span style="color: var(--text-muted);">Montant total:</span> <strong>${montant}</strong></div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted);">Articles:</span>
                        ${articlesHtml}
                    </div>
                `;
                detailsDiv.style.display = 'block';
            } catch (e) {
                console.error('Erreur parsing marche details:', e);
                detailsDiv.style.display = 'none';
            }
        }

        async function loadFournisseurs() {
            const compteSelect = document.getElementById('compteSelect');
            const compteOption = compteSelect.options[compteSelect.selectedIndex];
            const fournisseurSelect = document.getElementById('fournisseurSelect');
            
            selectedCompteId = compteSelect.value;
            currentFournisseurArticles = [];
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            
            if (!selectedCompteId) {
                fournisseurSelect.innerHTML = '<option value="">Sélectionner d\'abord un compte...</option>';
                document.getElementById('fournisseurSection').style.display = 'grid';
                document.getElementById('sansFournisseurSection').style.display = 'none';
                return;
            }
            
            // Load articles for the selected compte
            currentCompteArticles = JSON.parse(compteOption.getAttribute('data-articles') || '[]');

            // Check if fournisseur is required for this compte
            fournisseurRequis = compteOption.getAttribute('data-fournisseur-requis') === '1';
            
            if (!fournisseurRequis) {
                // Mode without fournisseur
                document.getElementById('fournisseurSection').style.display = 'none';
                document.getElementById('sansFournisseurSection').style.display = 'block';
                document.getElementById('fournisseurRequired').textContent = '';
                fournisseurSelect.removeAttribute('required');
                
                // Add first article line for manual entry
                addArticleLine();
                return;
            }

            // Mode with fournisseur
            document.getElementById('fournisseurSection').style.display = 'grid';
            document.getElementById('sansFournisseurSection').style.display = 'none';
            document.getElementById('fournisseurRequired').textContent = '*';
            fournisseurSelect.setAttribute('required', 'required');

            try {
                let updateUrl = "{{ route('api.fournisseurs.compte', ':id') }}";
                const response = await fetch(updateUrl.replace(':id', selectedCompteId));
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
                document.getElementById('fournisseurNom').value = '';
                return;
            }

            document.getElementById('fournisseurNom').value = option.getAttribute('data-nom');
            
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
            currentCompteArticles.forEach(a => {
                articleOptions += `<option value="${a.id}" data-designation="${a.designation}">${a.designation}</option>`;
            });

            div.innerHTML = `
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Article *</label>
                    <select name="articles[${index}][article_id]" required class="article-select" onchange="selectArticle(this)"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        ${articleOptions}
                    </select>
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

        // Handle form submit to copy fournisseur_libre to fournisseur when no fournisseur required
        document.getElementById('commandeForm').addEventListener('submit', function() {
            if (!fournisseurRequis) {
                const fournisseurLibre = document.getElementById('fournisseurLibre').value;
                document.getElementById('fournisseurNom').value = fournisseurLibre;
            }
        });
    </script>
@endsection
