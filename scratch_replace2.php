<?php
$file = 'resources/views/commandes/edit.blade.php';
$content = file_get_contents($file);

// Let's modify addArticleLine to accept default values
$oldAddArticleLine = "function addArticleLine() {";
$newAddArticleLine = "function addArticleLine(existingData = null) {";
$content = str_replace($oldAddArticleLine, $newAddArticleLine, $content);

// Inside addArticleLine, replace the `<select>` generation
$oldSelect = 'let articleOptions = \'<option value=\"\">Choisir article...</option>\';
            currentCompteArticles.forEach(a => {
                articleOptions += `<option value=\"${a.id}\" data-designation=\"${a.designation}\">${a.designation}</option>`;
            });';
$newSelect = 'let articleOptions = \'<option value=\"\">Choisir article...</option>\';
            currentCompteArticles.forEach(a => {
                const selected = (existingData && existingData.article_id == a.id) ? "selected" : "";
                articleOptions += `<option value="${a.id}" data-designation="${a.designation}" ${selected}>${a.designation}</option>`;
            });';
$content = str_replace($oldSelect, $newSelect, $content);

// And populate the other inputs inside addArticleLine
$content = str_replace('name="articles[${index}][conditionnement]" placeholder="ex: 38 unités"', 'name="articles[${index}][conditionnement]" value="${existingData ? existingData.conditionnement || \'\' : \'\'}" placeholder="ex: 38 unités"', $content);
$content = str_replace('name="articles[${index}][quantite]" required min="1" value="1"', 'name="articles[${index}][quantite]" required min="1" value="${existingData ? existingData.quantite : 1}"', $content);
$content = str_replace('name="articles[${index}][prix_unitaire]" required min="0" value="0"', 'name="articles[${index}][prix_unitaire]" required min="0" value="${existingData ? existingData.prix_unitaire : 0}"', $content);


$initScript = <<<JS
        window.onload = async function() {
            // Trigger toggleSource to show correct section
            toggleSource();
            
            // Wait a moment for UI to settle
            setTimeout(async () => {
                if (document.getElementById('sourceSelect').value === 'Marché') {
                    showMarcheDetails();
                } else {
                    // It's from Compte
                    await loadFournisseurs(true);
                }
            }, 100);
        };

        // We override loadFournisseurs to accept an initialization flag
        const originalLoadFournisseurs = loadFournisseurs;
        loadFournisseurs = async function(isInit = false) {
            const compteSelect = document.getElementById('compteSelect');
            if (!compteSelect.value) return;
            
            const fournisseurSelect = document.getElementById('fournisseurSelect');
            
            selectedCompteId = compteSelect.value;
            const compteOption = compteSelect.options[compteSelect.selectedIndex];
            currentCompteArticles = JSON.parse(compteOption.getAttribute('data-articles') || '[]');
            fournisseurRequis = compteOption.getAttribute('data-fournisseur-requis') === '1';
            
            if (!fournisseurRequis) {
                document.getElementById('fournisseurSection').style.display = 'none';
                document.getElementById('sansFournisseurSection').style.display = 'block';
                document.getElementById('fournisseurRequired').textContent = '';
                fournisseurSelect.removeAttribute('required');
                
                if (isInit) {
                    populateExistingArticles();
                } else {
                    document.getElementById('articlesContainer').innerHTML = '';
                    articleCount = 0;
                    addArticleLine();
                }
                return;
            }

            document.getElementById('fournisseurSection').style.display = 'grid';
            document.getElementById('sansFournisseurSection').style.display = 'none';
            document.getElementById('fournisseurRequired').textContent = '*';
            fournisseurSelect.setAttribute('required', 'required');

            try {
                let updateUrl = "{{ route('api.fournisseurs.compte', ':id') }}";
                const response = await fetch(updateUrl.replace(':id', selectedCompteId));
                const fournisseurs = await response.json();
                
                let options = '<option value="">Choisir un fournisseur...</option>';
                const existingFournisseurName = "{{ \$commande->fournisseur }}";
                
                fournisseurs.forEach(f => {
                    const selected = (isInit && f.nom === existingFournisseurName) ? 'selected' : '';
                    options += `<option value="\${f.id}" data-nom="\${f.nom}" data-articles='\${JSON.stringify(f.articles)}' \${selected}>\${f.nom}</option>`;
                });
                
                fournisseurSelect.innerHTML = options;
                
                if (isInit) {
                    populateExistingArticles();
                } else {
                    document.getElementById('articlesContainer').innerHTML = '';
                    articleCount = 0;
                    addArticleLine();
                }
            } catch (e) {
                console.error('Erreur chargement fournisseurs:', e);
            }
        };

        function populateExistingArticles() {
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            const existingArticles = @json(\$commande->articles);
            
            if (existingArticles.length > 0) {
                existingArticles.forEach(art => {
                    addArticleLine(art);
                });
            } else {
                addArticleLine();
            }
        }
JS;

$content = str_replace('// Handle form submit to copy fournisseur_libre to fournisseur when no fournisseur required', $initScript . "\n\n        // Handle form submit to copy fournisseur_libre to fournisseur when no fournisseur required", $content);

file_put_contents($file, $content);
echo "Done";
