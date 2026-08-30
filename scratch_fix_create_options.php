<?php
$f = 'resources/views/commandes/create.blade.php';
$c = file_get_contents($f);
$c = str_replace("\r\n", "\n", $c);

$target = "currentCompteArticles.forEach(a => {
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\">\${a.designation}</option>`;
            });";
$target = str_replace("\r\n", "\n", $target);

$replacement = "currentCompteArticles.forEach(a => {
                const prix = a.pivot ? a.pivot.prix : 0;
                articleOptions += `<option value=\"\${a.id}\" data-designation=\"\${a.designation}\" data-prix=\"\${prix}\">\${a.designation}</option>`;
            });";

if (strpos($c, $target) !== false) {
    $c = str_replace($target, $replacement, $c);
    file_put_contents($f, $c);
    echo "Fixed create.blade.php\n";
} else {
    echo "Target not found\n";
}
