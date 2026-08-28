<?php
$file = 'resources/views/commandes/edit.blade.php';
$content = file_get_contents($file);

$content = str_replace('Nouvelle Commande', 'Modifier Commande', $content);
$content = str_replace('📋 Nouveau Bon de Commande', '✏️ Modifier Bon de Commande {{ $commande->numero }}', $content);
$content = str_replace('action="{{ route(\'commandes.store\') }}"', 'action="{{ route(\'commandes.update\', $commande) }}"', $content);
$content = str_replace('@csrf', "@csrf\n                @method('PUT')", $content);
$content = str_replace('value="{{ date(\'Y-m-d\') }}"', 'value="{{ $commande->date->format(\'Y-m-d\') }}"', $content);

$content = str_replace('<option value="{{ $compte->id }}"', '<option value="{{ $compte->id }}" {{ $commande->compte_id == $compte->id ? \'selected\' : \'\' }}', $content);
$content = str_replace('value="Facture Pro-forma">', 'value="Facture Pro-forma" {{ $commande->source == \'Facture Pro-forma\' ? \'selected\' : \'\' }}>', $content);
$content = str_replace('value="Marché">', 'value="Marché" {{ $commande->source == \'Marché\' ? \'selected\' : \'\' }}>', $content);
$content = str_replace('value="Autre">', 'value="Autre" {{ $commande->source == \'Autre\' ? \'selected\' : \'\' }}>', $content);
$content = str_replace('<option value="{{ $marche->id }}"', '<option value="{{ $marche->id }}" {{ $commande->marche_id == $marche->id ? \'selected\' : \'\' }}', $content);

$content = str_replace('name="reference" placeholder', 'name="reference" value="{{ $commande->reference }}" placeholder', $content);
$content = str_replace('name="reference_libre" placeholder', 'name="reference_libre" value="{{ $commande->reference }}" placeholder', $content);
$content = str_replace('name="fournisseur_libre" id="fournisseurLibre" placeholder', 'name="fournisseur_libre" id="fournisseurLibre" value="{{ $commande->fournisseur }}" placeholder', $content);
$content = str_replace('id="fournisseurNom"', 'id="fournisseurNom" value="{{ $commande->fournisseur }}"', $content);
$content = str_replace('Créer la Commande', 'Mettre à jour la Commande', $content);

file_put_contents($file, $content);
echo "Done";
