<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        // Get parent accounts with children for display
        $comptes = Compte::with('children')->whereNull('parent_id')->get();
        // Get all accounts for parent selection dropdown
        $allComptes = Compte::all();

        return view('comptes.index', compact('comptes', 'allComptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:50|unique:comptes',
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:Fonctionnement,Investissement',
            'is_marche' => 'nullable|boolean',
            'dotation_initiale' => 'nullable|numeric|min:0',
            'prix_unitaire_ref' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:comptes,id',
            'etat' => 'required|in:actif,inactif',
        ]);

        Compte::create(array_merge($request->all(), [
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
            'type' => 'required|in:Fonctionnement,Investissement',
           // 'is_marche' => 'nullable|boolean',
            'dotation_initiale' => 'nullable|numeric|min:0',
            'prix_unitaire_ref' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:comptes,id',
            'etat' => 'required|in:actif,inactif',
        ]);

        $compte->update(array_merge($request->all(), [
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
            'type',
            'is_marche',
            'fournisseur_requis',
            'dotation_initiale',
            'prix_unitaire_ref',
            'parent_numero',
            'etat'
        ];

        $example = [
            '6011',
            'Papier ramette A4',
            'Fonctionnement',
            '0',
            '1',
            '0',
            '0',
            '',
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
                    //dd($row);
                    if (count($row) < 9) {
                        $errors[] = "Ligne $lineNumber: Nombre de colonnes insuffisant (" . count($row) . " colonnes trouvées)";
                        continue;
                    } 
                   

                    $data = [
                        'numero' => trim($row[0]),
                        'libelle' => trim($row[1]),
                        'type' => trim($row[2]),
                        'is_marche' => $row[3] == '1',
                        'fournisseur_requis' => $row[4] == '1',
                        'dotation_initiale' => floatval($row[5]),
                        'prix_unitaire_ref' => floatval($row[6]),
                        'parent_numero' => trim($row[7]),
                        'etat' => trim($row[8]),
                    ];

                    // Validation
                    if (empty($data['numero']) || empty($data['libelle']) || empty($data['type']) || empty($data['etat'])) {
                        $errors[] = "Ligne $lineNumber: Champs requis manquants";
                        continue;
                    }

                    if (!in_array($data['type'], ['Fonctionnement', 'Investissement'])) {
                        $errors[] = "Ligne $lineNumber: Type invalide (doit être Fonctionnement ou Investissement)";
                        continue;
                    }

                    if (!in_array($data['etat'], ['actif', 'inactif'])) {
                        $errors[] = "Ligne $lineNumber: État invalide (doit être actif ou inactif)";
                        continue;
                    }

                    // Check if numero already exists
                    if (Compte::where('numero', $data['numero'])->exists()) {
                        $errors[] = "Ligne $lineNumber: Le numéro {$data['numero']} existe déjà";
                        continue;
                    }

                    // Find parent if specified
                    $parent_id = null;
                    if (!empty($data['parent_numero'])) {
                        $parent = Compte::where('numero', $data['parent_numero'])->first();
                        if (!$parent) {
                            $errors[] = "Ligne $lineNumber: Compte parent {$data['parent_numero']} introuvable";
                            continue;
                        }
                        $parent_id = $parent->id;
                    }

                    Compte::create([
                        'numero' => $data['numero'],
                        'libelle' => $data['libelle'],
                        'type' => $data['type'],
                        'is_marche' => $data['is_marche'],
                        'fournisseur_requis' => $data['fournisseur_requis'],
                        'dotation_initiale' => $data['dotation_initiale'],
                        'prix_unitaire_ref' => $data['prix_unitaire_ref'],
                        'parent_id' => $parent_id,
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
