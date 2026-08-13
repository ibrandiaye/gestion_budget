<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\LigneBudget;
use App\Models\Compte;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetYearController extends Controller
{
    public function index()
    {
        $budgets = Budget::withCount('lignes')->orderByDesc('annee')->get();
        return view('budgets.index', compact('budgets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required|integer|min:2020|unique:budgets,annee',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        Budget::create([
            'annee' => $request->annee,
            'statut' => 'brouillon',
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
        ]);

        return back()->with('success', 'Exercice budgétaire créé.');
    }

    public function activate(Budget $budget)
    {
        Budget::query()->update(['statut' => 'cloture']);
        $budget->update(['statut' => 'actif']);
        return back()->with('success', "Le budget $budget->annee est maintenant ACTIF.");
    }

    public function show(Budget $budget)
    {
        $budget->load(['lignes.compte', 'lignes.service']);
        $totalAlloue = $budget->lignes->sum('montant_alloue');
        $totalEngage = $budget->lignes->sum('montant_engage');
        
        return view('budgets.show', compact('budget', 'totalAlloue', 'totalEngage'));
    }

    /**
     * Download Excel template for budget import
     */
    public function downloadTemplate()
    {
        $comptes = Compte::whereNotNull('compte_parent_id')->where('etat', 'actif')->orderBy('numero')->get();
        $services = Service::orderBy('nom')->get();

        // Generate CSV template
        $filename = 'modele_budget_' . date('Y') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($comptes, $services) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['numero_compte', 'libelle_compte', 'service', 'montant_alloue'], ';');
            
            // Example rows with all comptes x services
            foreach ($comptes as $compte) {
                foreach ($services as $service) {
                    fputcsv($file, [
                        $compte->numero,
                        $compte->libelle,
                        $service->nom,
                        0,
                    ], ';');
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import budget from Excel/CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'fichier' => 'required|file|mimes:csv,txt,xls,xlsx',
        ]);

        $budget = Budget::findOrFail($request->budget_id);
        $file = $request->file('fichier');
        $path = $file->getRealPath();

        $imported = 0;
        $errors = [];
        $lineNumber = 0;

        DB::beginTransaction();
        try {
            // Delete existing lines for this budget if reimporting
            LigneBudget::where('budget_id', $budget->id)->delete();

            $handle = fopen($path, 'r');
            
            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Read header
            $header = fgetcsv($handle, 0, ';');
            if (!$header) {
                throw new \Exception('Fichier vide ou format invalide.');
            }

            // Normalize header
            $header = array_map(function($h) {
                return strtolower(trim(str_replace(' ', '_', $h)));
            }, $header);

            $colNumero = array_search('numero_compte', $header);
            $colService = array_search('service', $header);
            $colMontant = array_search('montant_alloue', $header);

            if ($colNumero === false || $colMontant === false) {
                throw new \Exception('Colonnes requises manquantes: numero_compte, montant_alloue');
            }

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                
                if (count($row) < max($colNumero, $colMontant) + 1) continue;

                $numeroCompte = trim($row[$colNumero]);
                $serviceName = $colService !== false ? trim($row[$colService]) : null;
                $montant = floatval(str_replace([' ', ','], ['', '.'], $row[$colMontant]));

                if (empty($numeroCompte) || $montant <= 0) continue;

                // Find compte
                $compte = Compte::where('numero', $numeroCompte)->first();
                if (!$compte) {
                    $errors[] = "Ligne {$lineNumber}: Compte \"{$numeroCompte}\" introuvable.";
                    continue;
                }

                // Find service (optional)
                $serviceId = null;
                if ($serviceName) {
                    $service = Service::where('nom', 'LIKE', "%{$serviceName}%")->first();
                    if ($service) {
                        $serviceId = $service->id;
                    } else {
                        $errors[] = "Ligne {$lineNumber}: Service \"{$serviceName}\" introuvable.";
                        continue;
                    }
                }

                LigneBudget::create([
                    'budget_id' => $budget->id,
                    'compte_id' => $compte->id,
                    'service_id' => $serviceId,
                    'montant_alloue' => $montant,
                    'montant_engage' => 0,
                    'montant_paye' => 0,
                ]);

                $imported++;
            }

            fclose($handle);

            if (!empty($errors)) {
                DB::rollBack();
                return back()
                    ->withErrors(['fichier' => count($errors) . ' erreur(s) détectée(s). Le fichier n\'a pas été importé. Veuillez corriger ces erreurs d\'abord.'])
                    ->with('import_errors', $errors);
            }

            DB::commit();

            $message = "{$imported} ligne(s) budgétaire(s) importée(s) avec succès.";
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['fichier' => 'Erreur d\'importation: ' . $e->getMessage()]);
        }
    }
}
