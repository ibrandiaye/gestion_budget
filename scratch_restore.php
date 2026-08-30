<?php
$filepath = 'resources/views/commandes/edit.blade.php';
$content = file_get_contents($filepath);

$target = "function addArticleLine(existingData = null) {";
$insertion = "function populateExistingArticles() {
            document.getElementById('articlesContainer').innerHTML = '';
            articleCount = 0;
            const existingArticles = existingArticlesData;
            if (existingArticles && existingArticles.length > 0) {
                existingArticles.forEach(art => {
                    addArticleLine(art);
                });
            } else {
                addArticleLine();
            }
        }

        ";

if (strpos($content, 'function populateExistingArticles()') === false) {
    $content = str_replace($target, $insertion . $target, $content);
    file_put_contents($filepath, $content);
    echo "Restored populateExistingArticles\n";
} else {
    echo "Already exists\n";
}
