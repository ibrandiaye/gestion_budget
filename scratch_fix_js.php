<?php
$file = 'resources/views/commandes/edit.blade.php';
$content = file_get_contents($file);

$pos = strpos($content, '<script>');
if ($pos !== false) {
    $html = substr($content, 0, $pos);
    
    $js = <<<'HTML'
    <script>
        let articleCount = 0;
        let currentCompteArticles = [];
        let selectedCompteId = null;
        let fournisseurRequis = true;

        window.onload = async function() {
            // Setup source
            toggleSource();
            
            // Allow UI to settle
            setTimeout(async () => {
                if (document.getElementById('sourceSelect').value === 'Marché') {
                    showMarcheDetails();
                } else {
                    await loadFournisseurs(true);
                }
            }, 100);
        };

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

        async function loadFournisseurs(isInit = false) {
            const compteSelect = document.getElementById('compteSelect');
            if (!compteSelect.value) return;

            const fournisseurSelect = document.getElementById('fournisseurSelect');
            
            selectedCompteId = compteSelect.value;
            const compteOption = compteSelect.options[compteSelect.selectedIndex];
            
            if (!isInit) {
                document.getElementById('articlesContainer').innerHTML = '';
                articleCount = 0;
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
                
                if (isInit) {
                    populateExistingArticles();
                } else {
                    addArticleLine();
                }
                return;
            }

            // Mode with fournisseur
            document.getElementById('fournisseurSection').style.display = 'grid';
            document.getElementById('sansFournisseurSection').style.display = 'none';
            document.getElementById('fournisseurRequired').textContent = '*';
            fournisseurSelect.setAttribute('required', 'required');

            try {
                // Laravel route wrapper
                let updateUrl = window.location.origin + "/api/fournisseurs/compte/" + selectedCompteId;
                const response = await fetch(updateUrl);
                const fournisseurs = await response.json();
                
                let options = '<option value="">Choisir un fournisseur...</option>';
                const existingFournisseurName = document.getElementById('fournisseurNom').value;

                fournisseurs.forEach(f => {
                    const selected = (isInit && f.nom === existingFournisseurName) ? 'selected' : '';
                    options += `<option value="${f.id}" data-nom="${f.nom}" data-articles='${JSON.stringify(f.articles)}' ${selected}>${f.nom}</option>`;
                });
                
                fournisseurSelect.innerHTML = options;
                
                if (isInit) {
                    populateExistingArticles();
                } else {
                    addArticleLine();
                }
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

        function populateExistingArticles() {
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            
            // Get data from blade (We must output json in a hidden script or inject it. I'll just use a blade directive here, wait, this is inside HEREDOC, so I can't use blade directly if I escape it, but I can write the exact blade syntax).
            // Actually, I can use a global variable that blade sets before the script.
        }
    </script>
HTML;

    // To cleanly inject the existing articles from blade, I'll place it right before the main script
    $bladeJson = "\n    <script>\n        const existingArticlesData = @json(\$commande->articles);\n    </script>\n";

    $js = str_replace("function populateExistingArticles() {", "function populateExistingArticles() {\n            const existingArticles = existingArticlesData;\n            if (existingArticles && existingArticles.length > 0) {\n                existingArticles.forEach(art => {\n                    addArticleLine(art);\n                });\n            } else {\n                addArticleLine();\n            }", $js);

    $js .= <<<'HTML'
    <script>
        function addArticleLine(existingData = null) {
            const container = document.getElementById('articlesContainer');
            const index = articleCount++;

            const div = document.createElement('div');
            div.className = 'article-line';
            div.id = 'article-line-' + index;
            div.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 0.5rem; align-items: end; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;';

            let articleOptions = '<option value="">Choisir article...</option>';
            currentCompteArticles.forEach(a => {
                const selected = (existingData && existingData.article_id == a.id) ? 'selected' : '';
                articleOptions += `<option value="${a.id}" data-designation="${a.designation}" ${selected}>${a.designation}</option>`;
            });

            div.innerHTML = `
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Article *</label>
                    <select name="articles[${index}][article_id]" required class="article-select"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        ${articleOptions}
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Conditionnement</label>
                    <input type="text" name="articles[${index}][conditionnement]" value="${existingData ? (existingData.conditionnement || '') : ''}" placeholder="ex: 38 unités"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Quantité *</label>
                    <input type="number" name="articles[${index}][quantite]" required min="1" value="${existingData ? existingData.quantite : 1}" onchange="calculateTotal()" class="qte-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: center;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Prix Unitaire *</label>
                    <input type="number" name="articles[${index}][prix_unitaire]" required min="0" value="${existingData ? existingData.prix_unitaire : 0}" onchange="calculateTotal()" class="prix-input"
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

        document.getElementById('commandeForm').addEventListener('submit', function() {
            if (!fournisseurRequis) {
                const fournisseurLibre = document.getElementById('fournisseurLibre').value;
                document.getElementById('fournisseurNom').value = fournisseurLibre;
            }
        });
    </script>
@endsection
HTML;

    file_put_contents($file, $html . $bladeJson . $js);
    echo "Done.\n";
} else {
    echo "Script tag not found.\n";
}
