<?php
$files = [
    'resources/views/commandes/create.blade.php',
    'resources/views/commandes/edit.blade.php'
];

foreach ($files as $filepath) {
    $content = file_get_contents($filepath);
    
    $startStr = "function loadArticlesFournisseur() {";
    $endStr = "function addArticleLine";
    
    $startIdx = strpos($content, $startStr);
    $endIdx = strpos($content, $endStr, $startIdx);
    
    if ($startIdx !== false && $endIdx !== false) {
        $newFunc = "function loadArticlesFournisseur() {\n" .
"            const fournisseurSelect = document.getElementById('fournisseurSelect');\n" .
"            const option = fournisseurSelect.options[fournisseurSelect.selectedIndex];\n" .
"            \n" .
"            document.getElementById('articlesContainer').innerHTML = '';\n" .
"            articleCount = 0;\n" .
"            \n" .
"            if (!option.value) {\n" .
"                document.getElementById('fournisseurNom').value = '';\n" .
"                return;\n" .
"            }\n" .
"\n" .
"            document.getElementById('fournisseurNom').value = option.getAttribute('data-nom');\n" .
"            \n" .
"            // Extract articles from the option and update currentCompteArticles\n" .
"            const articlesAttr = option.getAttribute('data-articles');\n" .
"            if (articlesAttr) {\n" .
"                const fArticles = JSON.parse(articlesAttr);\n" .
"                if (fArticles && fArticles.length > 0) {\n" .
"                    currentCompteArticles = fArticles;\n" .
"                } else {\n" .
"                    currentCompteArticles = [];\n" .
"                }\n" .
"            } else {\n" .
"                currentCompteArticles = [];\n" .
"            }\n" .
"            \n" .
"            // Add first article line\n" .
"            addArticleLine();\n" .
"        }\n\n        ";
        
        $content = substr($content, 0, $startIdx) . $newFunc . substr($content, $endIdx);
        file_put_contents($filepath, $content);
        echo "Fixed $filepath\n";
    }
}
