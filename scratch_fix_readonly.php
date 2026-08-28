<?php
$files = [
    'resources/views/commandes/create.blade.php',
    'resources/views/commandes/edit.blade.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);

    // In addArticleLine, make the prix input conditionally readonly
    $targetInput = 'class="prix-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: right;">';
    
    $replacementInput = 'class="prix-input"
                        ${fournisseurRequis ? \'readonly style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: right; background: var(--slate-100);"\' : \'style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; text-align: right;"\'}>';

    $content = str_replace($targetInput, $replacementInput, $content);
    
    file_put_contents($file, $content);
    echo "Fixed input in $file\n";
}
