<?php
$files = [
    'resources/views/commandes/create.blade.php',
    'resources/views/commandes/edit.blade.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);

    // 1. Add data-prix and onchange to the select in addArticleLine()
    // For create.blade.php it doesn't have existingData, for edit.blade.php it does.
    // Let's replace the loop in addArticleLine to include data-prix
    if (strpos($file, 'edit.blade.php') !== false) {
        $loopTarget = "currentCompteArticles.forEach(a => {
                const selected = (existingData && existingData.article_id == a.id) ? 'selected' : '';
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\" \${selected}>\${a.designation}</option>`;
            });";
        $loopReplacement = "currentCompteArticles.forEach(a => {
                const selected = (existingData && existingData.article_id == a.id) ? 'selected' : '';
                const prix = a.pivot ? a.pivot.prix : 0;
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\" data-prix=\"\${prix}\" \${selected}>\${a.designation}</option>`;
            });";
        $content = str_replace($loopTarget, $loopReplacement, $content);

        // Also add onchange="selectArticle(this)" to the select
        $selectTarget = "select name=\"articles[\${index}][article_id]\" required class=\"article-select\"";
        $selectReplacement = "select name=\"articles[\${index}][article_id]\" required class=\"article-select\" onchange=\"selectArticle(this)\"";
        $content = str_replace($selectTarget, $selectReplacement, $content);
        
        // Let's update loadFournisseurs to update currentCompteArticles if isInit
        $loadInitTarget = "fournisseurSelect.innerHTML = options;
                
                if (isInit) {
                    populateExistingArticles();";
        $loadInitReplacement = "fournisseurSelect.innerHTML = options;
                
                if (isInit) {
                    const selectedOption = fournisseurSelect.options[fournisseurSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        const fArticles = JSON.parse(selectedOption.getAttribute('data-articles') || '[]');
                        if (fArticles && fArticles.length > 0) {
                            currentCompteArticles = fArticles;
                        }
                    }
                    populateExistingArticles();";
        $content = str_replace($loadInitTarget, $loadInitReplacement, $content);
    } else {
        // create.blade.php
        $loopTarget = "currentCompteArticles.forEach(a => {
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\">\${a.designation}</option>`;
            });";
        $loopReplacement = "currentCompteArticles.forEach(a => {
                const prix = a.pivot ? a.pivot.prix : 0;
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\" data-prix=\"\${prix}\">\${a.designation}</option>`;
            });";
        $content = str_replace($loopTarget, $loopReplacement, $content);

        // Also add onchange="selectArticle(this)" to the select
        $selectTarget = "select name=\"articles[\${index}][article_id]\" required class=\"article-select\"";
        $selectReplacement = "select name=\"articles[\${index}][article_id]\" required class=\"article-select\" onchange=\"selectArticle(this)\"";
        $content = str_replace($selectTarget, $selectReplacement, $content);
    }

    // 2. Modify loadArticlesFournisseur to set currentCompteArticles
    $loadArticlesFournisseurTarget = "document.getElementById('fournisseurNom').value = option.getAttribute('data-nom');
            
            // Add first article line
            addArticleLine();";
    $loadArticlesFournisseurReplacement = "document.getElementById('fournisseurNom').value = option.getAttribute('data-nom');
            
            const fArticles = JSON.parse(option.getAttribute('data-articles') || '[]');
            if (fArticles && fArticles.length > 0) {
                currentCompteArticles = fArticles;
            }

            // Add first article line
            addArticleLine();";
    $content = str_replace($loadArticlesFournisseurTarget, $loadArticlesFournisseurReplacement, $content);

    // 3. Rewrite selectArticle function
    $selectArticleTarget = "function selectArticle(select) {
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
        }";
    $selectArticleReplacement = "function selectArticle(select) {
            const option = select.options[select.selectedIndex];
            const line = select.closest('.article-line');
            const prixInput = line.querySelector('.prix-input');
            
            if (option.value) {
                const prix = parseFloat(option.getAttribute('data-prix')) || 0;
                prixInput.value = prix;
                if (fournisseurRequis) {
                    prixInput.readOnly = true;
                    prixInput.style.backgroundColor = 'var(--slate-100)';
                } else {
                    prixInput.readOnly = false;
                    prixInput.style.backgroundColor = '';
                }
            } else {
                prixInput.value = 0;
                if (fournisseurRequis) {
                    prixInput.readOnly = true;
                    prixInput.style.backgroundColor = 'var(--slate-100)';
                } else {
                    prixInput.readOnly = false;
                    prixInput.style.backgroundColor = '';
                }
            }
            
            calculateTotal();
        }";
    
    // In edit.blade.php, selectArticle might not exist because I wiped it during the rewrite. Let's check if it exists, if not, inject it.
    if (strpos($content, 'function selectArticle') !== false) {
        $content = str_replace($selectArticleTarget, $selectArticleReplacement, $content);
    } else {
        // Inject right before calculateTotal()
        $injectTarget = "function calculateTotal() {";
        $content = str_replace($injectTarget, $selectArticleReplacement . "\n\n        " . $injectTarget, $content);
    }

    file_put_contents($file, $content);
    echo "Updated $file\n";
}
