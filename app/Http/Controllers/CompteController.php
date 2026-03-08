<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\CompteParent;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        $activeBudget = \App\Models\Budget::where('statut', 'actif')->first();

        // Get parents with their accounts, including dotation for active budget
        $parents = CompteParent::with(['comptes' => function($q) use ($activeBudget) {
            $q->with(['dotations' => function($d) use ($activeBudget) {
                if ($activeBudget) {
                    $d->where('budget_id', $activeBudget->id);
                } else {
                    $d->whereNull('id'); // No results if no budget
                }
            }]);
        }])->orderBy('numero')->get();

        // Overlay the correct dotation for the view
        $parents->each(function($parent) {
            $parent->comptes->each(function($compte) {
                $compte->dotation_actuelle = $compte->dotations->first()->dotation_initiale ?? 0;
                $compte->prix_ref_actuel = $compte->dotations->first()->prix_unitaire_ref ?? 0;
                // We overwrite the standard property for the JSON serialization in the view to work easily
                $compte->dotation_initiale = $compte->dotation_actuelle;
                $compte->prix_unitaire_ref = $compte->prix_ref_actuel;
            });
        });

        // Get all parents for selection dropdown
        $allParents = CompteParent::all();

        return view('comptes.index', compact('parents', 'allParents', 'activeBudget'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:50|unique:comptes',
            'libelle' => 'required|string|max:255',
            'compte_parent_id' => 'required|exists:compte_parents,id',
            'is_marche' => 'nullable|boolean',
            'dotation_initiale' => 'nullable|numeric|min:0',
            'prix_unitaire_ref' => 'nullable|numeric|min:0',
            'etat' => 'required|in:actif,inactif',
        ]);

        Compte::create(array_merge($request->only([
            'numero', 'libelle', 'compte_parent_id', 'dotation_initiale', 'prix_unitaire_ref', 'etat'
        ]), [
            'is_marche' => $request->has('is_marche'),
            'fournisseur_requis' => $request->has('fournisseur_requis'),
        ]));

        // Handle Dotation for Active Budget
        if ($request->filled('dotation_initiale') || $request->filled('prix_unitaire_ref')) {
            $budget = \App\Models\Budget::where('statut', 'actif')->orderByDesc('annee')->first();
            if (!$budget) {
                // Try brouillon or create? If no budget, we can't save dotation properly linked.
                // Fallback: Just ignore or warn? 
                // Let's try to find ANY active/draft budget.
                $budget = \App\Models\Budget::orderByDesc('annee')->first();
            }

            if ($budget) {
                \App\Models\DotationCompte::updateOrCreate(
                    ['budget_id' => $budget->id, 'compte_id' => $compte->id],
                    [
                        'dotation_initiale' => $request->dotation_initiale ?? 0,
                        'prix_unitaire_ref' => $request->prix_unitaire_ref ?? 0
                    ]
                );
            }
        }

        return back()->with('success', 'Compte ajouté avec succès.');
    }

    public function update(Request $request, Compte $compte)
    {
        $request->validate([
            'numero' => 'required|string|max:50|unique:comptes,numero,' . $compte->id,
            'libelle' => 'required|string|max:255',
            'compte_parent_id' => 'required|exists:compte_parents,id',
            'dotation_initiale' => 'nullable|numeric|min:0',
            'prix_unitaire_ref' => 'nullable|numeric|min:0',
            'etat' => 'required|in:actif,inactif',
        ]);

        $compte->update(array_merge($request->only([
            'numero', 'libelle', 'compte_parent_id', 'dotation_initiale', 'prix_unitaire_ref', 'etat'
        ]), [
            'is_marche' => $request->has('is_marche') ? true : false,
            'fournisseur_requis' => $request->has('fournisseur_requis') ? true : false,
        ]));

        // Handle Dotation Update for Active Budget
        if ($request->has('dotation_initiale') || $request->has('prix_unitaire_ref')) {
             $budget = \App\Models\Budget::where('statut', 'actif')->orderByDesc('annee')->first();
             if (!$budget) {
                 $budget = \App\Models\Budget::orderByDesc('annee')->first();
             }

             if ($budget) {
                \App\Models\DotationCompte::updateOrCreate(
                    ['budget_id' => $budget->id, 'compte_id' => $compte->id],
                    [
                        'dotation_initiale' => $request->dotation_initiale ?? 0,
                        'prix_unitaire_ref' => $request->prix_unitaire_ref ?? 0
                    ]
                );
             }
        }

        return back()->with('success', 'Compte mis à jour avec succès.');
    }

    public function destroy(Compte $compte)
    {
        $compte->delete();
        return back()->with('success', 'Compte supprimé.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'numero',
            'libelle', 
            'compte_parent_numero', // Was type/parent
            'is_marche',
            'fournisseur_requis',
            'dotation_initiale',
            'prix_unitaire_ref',
            'etat'
        ];

        $example = [
            '6011',
            'Papier ramette A4',
            '10', // Parent numero
            '0',
            '1',
            '0',
            '0',
            'actif'
        ];

        $data = [$headers, $example];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_comptes.csv"',
        ]);
    }

    public function import(Request $request)
    {
         
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);
        
        $extension = $request->file('file')->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['csv', 'txt', 'xls', 'xlsx'])) {
            return back()->withErrors(['file' => 'Le fichier doit être de type : csv, txt, xls, xlsx.']);
        }
          
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'csv' || $extension === 'txt') {
                $handle = fopen($file->getPathname(), 'r');
                
                // Detect delimiter by reading first line
                $firstLine = fgets($handle);
                rewind($handle);
                
                $delimiter = ',';
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                }
                
                $headers = fgetcsv($handle, 0, $delimiter);
                
                $imported = 0;
                $errors = [];
                $lineNumber = 1;

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $lineNumber++;
                    // Expected columns: numero, libelle, parent_numero, is_marche, fournisseur_requis, dotation, prix, etat
                    // Adjust logic based on new headers structure
                    
                    if (count($row) < 8) {
                        $errors[] = "Ligne $lineNumber: Nombre de colonnes insuffisant";
                        continue;
                    } 
                    // Nettoyage des valeurs (ex: espaces dans les nombres, virgules de fin de ligne dans le CSV)
                    $dotationRaw = str_replace(' ', '', trim($row[5]));
                    $prixRaw = str_replace(' ', '', trim($row[6]));
                    
                    // Retirer les éventuelles virgules finales sur l'état (souvent ajoutées par Excel lors d'exports CSV imparfaits)
                    $etatClean = trim(explode(',', trim($row[7]))[0]);

                    // Convertir l'encodage si nécessaire (Excel exporte souvent en Windows-1252 ou ISO-8859-1)
                    $libelle = mb_convert_encoding(trim($row[1]), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $numero = mb_convert_encoding(trim($row[0]), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $parent_numero = mb_convert_encoding(trim($row[2]), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

                    $data = [
                        'numero' => $numero,
                        'libelle' => $libelle,
                        'parent_numero' => $parent_numero,
                        'is_marche' => $row[3] == '1',
                        'fournisseur_requis' => $row[4] == '1',
                        'dotation_initiale' => floatval($dotationRaw),
                        'prix_unitaire_ref' => floatval($prixRaw),
                        'etat' => strtolower($etatClean),
                    ];

                    // Validation & Parent Lookup
                    $parent = CompteParent::where('numero', $data['parent_numero'])->first();
                    if (!$parent) {
                        $errors[] = "Ligne $lineNumber: Parent avec numéro {$data['parent_numero']} introuvable";
                        continue;
                    }

                    if (Compte::where('numero', $data['numero'])->exists()) {
                         $errors[] = "Ligne $lineNumber: Le numéro {$data['numero']} existe déjà";
                         continue;
                    }

                    $compte = Compte::create([
                        'numero' => $data['numero'],
                        'libelle' => $data['libelle'],
                        'compte_parent_id' => $parent->id,
                        'is_marche' => $data['is_marche'],
                        'fournisseur_requis' => $data['fournisseur_requis'],
                        'dotation_initiale' => $data['dotation_initiale'],
                        'prix_unitaire_ref' => $data['prix_unitaire_ref'],
                        'etat' => $data['etat'],
                    ]);

                    // Handle Dotation for Active Budget (Import)
                    // We try to find active budget once outside loop or inside? Inside is safer if we want to be robust but slower.
                    // Given the loop, let's just find it once ideally, but here context is simple.
                    $budget = \App\Models\Budget::where('statut', 'actif')->orderByDesc('annee')->first();
                    if (!$budget) $budget = \App\Models\Budget::orderByDesc('annee')->first();

                    if ($budget && ($data['dotation_initiale'] > 0 || $data['prix_unitaire_ref'] > 0)) {
                        \App\Models\DotationCompte::updateOrCreate(
                            ['budget_id' => $budget->id, 'compte_id' => $compte->id],
                            [
                                'dotation_initiale' => $data['dotation_initiale'],
                                'prix_unitaire_ref' => $data['prix_unitaire_ref']
                            ]
                        );
                    }

                    $imported++;
                }

                fclose($handle);

                if (!empty($errors)) {
                    return back()->with('warning', "$imported compte(s) importé(s). Erreurs: " . implode('; ', $errors));
                }

                return back()->with('success', "$imported compte(s) importé(s) avec succès.");

            } else {
                return back()->withErrors(['file' => 'Format Excel non supporté pour le moment. Veuillez utiliser un fichier CSV.']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Erreur lors de l\'importation: ' . $e->getMessage()]);
        }
    }
}
