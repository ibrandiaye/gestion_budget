<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\CompteParent;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        // Get parents with their accounts
        $parents = CompteParent::with('comptes')->orderBy('numero')->get();
        // Get all parents for selection dropdown
        $allParents = CompteParent::all();

        return view('comptes.index', compact('parents', 'allParents'));
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
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);
          
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
                   
                    $data = [
                        'numero' => trim($row[0]),
                        'libelle' => trim($row[1]),
                        'parent_numero' => trim($row[2]),
                        'is_marche' => $row[3] == '1',
                        'fournisseur_requis' => $row[4] == '1',
                        'dotation_initiale' => floatval($row[5]),
                        'prix_unitaire_ref' => floatval($row[6]),
                        'etat' => trim($row[7]),
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

                    Compte::create([
                        'numero' => $data['numero'],
                        'libelle' => $data['libelle'],
                        'compte_parent_id' => $parent->id,
                        'is_marche' => $data['is_marche'],
                        'fournisseur_requis' => $data['fournisseur_requis'],
                        'dotation_initiale' => $data['dotation_initiale'],
                        'prix_unitaire_ref' => $data['prix_unitaire_ref'],
                        'etat' => $data['etat'],
                    ]);

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
