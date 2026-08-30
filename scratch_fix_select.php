<?php
$files = ['resources/views/commandes/create.blade.php', 'resources/views/commandes/edit.blade.php'];
foreach ($files as $f) {
    $c = file_get_contents($f);
    
    // Find the start of selectArticle
    $start = strpos($c, 'function selectArticle(select) {');
    if ($start !== false) {
        $end = strpos($c, 'function removeArticleLine', $start);
        if ($end !== false) {
            $newFunc = "function selectArticle(select) {\n" .
                "            const option = select.options[select.selectedIndex];\n" .
                "            const line = select.closest('.article-line');\n" .
                "            const prixInput = line.querySelector('.prix-input');\n" .
                "            \n" .
                "            if (option.value) {\n" .
                "                const prix = parseFloat(option.getAttribute('data-prix')) || 0;\n" .
                "                prixInput.value = prix;\n" .
                "                if (typeof fournisseurRequis !== 'undefined' && fournisseurRequis) {\n" .
                "                    prixInput.readOnly = true;\n" .
                "                    prixInput.style.backgroundColor = 'var(--slate-100)';\n" .
                "                } else {\n" .
                "                    prixInput.readOnly = false;\n" .
                "                    prixInput.style.backgroundColor = '';\n" .
                "                }\n" .
                "            } else {\n" .
                "                prixInput.value = 0;\n" .
                "                if (typeof fournisseurRequis !== 'undefined' && fournisseurRequis) {\n" .
                "                    prixInput.readOnly = true;\n" .
                "                    prixInput.style.backgroundColor = 'var(--slate-100)';\n" .
                "                } else {\n" .
                "                    prixInput.readOnly = false;\n" .
                "                    prixInput.style.backgroundColor = '';\n" .
                "                }\n" .
                "            }\n" .
                "            \n" .
                "            calculateTotal();\n" .
                "        }\n\n        ";
            
            $c = substr($c, 0, $start) . $newFunc . substr($c, $end);
            file_put_contents($f, $c);
            echo "Fixed selectArticle in $f\n";
        }
    }
}
