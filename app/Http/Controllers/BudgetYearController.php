<?php

namespace App\Http\Controllers;

use App\Models\Ajustement;
use App\Models\Budget;
use App\Models\Commande;
use App\Models\Compte;
use App\Models\DotationCompte;
use App\Models\Engagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetYearController extends Controller
{
    public function index()
    {
        $budgets = Budget::withCount('dotationsComptes')->orderByDesc('annee')->get();
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
        $comptes = Compte::with([
            'compteParent',
            'dotations' => fn($q) => $q->where('budget_id', $budget->id),
        ])
            ->whereNotNull('compte_parent_id')
            ->where('etat', 'actif')
            ->orderBy('numero')
            ->get();

        $comptesBudget = $comptes->map(function ($compte) use ($budget) {
            $dotationInitiale = (float) $compte->getDotationForBudget($budget->id);

            $virementPlus = (float) Ajustement::where('budget_id', $budget->id)
                ->where('compte_dest_id', $compte->id)
                ->sum('montant');
            $virementMoins = (float) Ajustement::where('budget_id', $budget->id)
                ->where('compte_source_id', $compte->id)
                ->sum('montant');
            $virement = $virementPlus - $virementMoins;

            $dotationTotale = $dotationInitiale + $virement;

            $totalCommandes = (float) Commande::where('compte_id', $compte->id)
                ->where('budget_id', $budget->id)
                ->where('statut', '!=', 'rejete')
                ->get()
                ->sum(function ($cmd) {
                    if ($cmd->statut === 'valide') {
                        return $cmd->articles()->where('valide', true)->sum('montant_ht');
                    }
                    return $cmd->articles()->sum('montant_ht');
                });

            $totalEngage = (float) Engagement::where('compte_id', $compte->id)
                ->where('budget_id', $budget->id)
                ->where('statut', '!=', 'rejete')
                ->sum('montant');

            return (object) [
                'compte' => $compte,
                'dotation_initiale' => $dotationInitiale,
                'virement' => $virement,
                'dotation_totale' => $dotationTotale,
                'total_commandes' => $totalCommandes,
                'total_engage' => $totalEngage,
                'disponible' => $dotationTotale - $totalEngage,
            ];
        });

        $totalAlloue = $comptesBudget->sum('dotation_totale');
        $totalCommandes = $comptesBudget->sum('total_commandes');
        $totalEngage = $comptesBudget->sum('total_engage');

        return view('budgets.show', compact('budget', 'comptesBudget', 'totalAlloue', 'totalCommandes', 'totalEngage'));
    }

    /**
     * Download Excel template for budget import (par compte)
     */
    public function downloadTemplate()
    {
        $comptes = Compte::whereNotNull('compte_parent_id')->where('etat', 'actif')->orderBy('numero')->get();

        $filename = 'modele_dotations_comptes_' . date('Y') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($comptes) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, ['numero_compte', 'libelle_compte', 'dotation_initiale', 'prix_unitaire_ref'], ';');

            foreach ($comptes as $compte) {
                fputcsv($file, [
                    $compte->numero,
                    $compte->libelle,
                    (float) ($compte->dotation ?? 0),
                    (float) ($compte->prix_unitaire_ref ?? 0),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import budget allocations per account from Excel/CSV file
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
            $handle = fopen($path, 'r');

            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            // Read header
            $header = fgetcsv($handle, 0, ';');
            if (!$header) {
                throw new \Exception('Fichier vide ou format invalide.');
            }

            // Normalize header
            $header = array_map(function ($h) {
                return strtolower(trim(str_replace(' ', '_', $h)));
            }, $header);

            $colNumero = array_search('numero_compte', $header);
            $colMontant = array_search('dotation_initiale', $header);
            if ($colMontant === false) {
                $colMontant = array_search('montant_alloue', $header);
            }
            $colPrixRef = array_search('prix_unitaire_ref', $header);

            if ($colNumero === false || $colMontant === false) {
                throw new \Exception('Colonnes requises manquantes: numero_compte, dotation_initiale (ou montant_alloue)');
            }

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;

                if (count($row) < max($colNumero, $colMontant) + 1) {
                    continue;
                }

                $numeroCompte = trim($row[$colNumero]);
                $montant = floatval(str_replace([' ', ','], ['', '.'], $row[$colMontant]));
                $prixRef = ($colPrixRef !== false && isset($row[$colPrixRef]))
                    ? floatval(str_replace([' ', ','], ['', '.'], $row[$colPrixRef]))
                    : null;

                if (empty($numeroCompte)) {
                    continue;
                }

                $compte = Compte::where('numero', $numeroCompte)->first();
                if (!$compte) {
                    $errors[] = "Ligne {$lineNumber}: Compte \"{$numeroCompte}\" introuvable.";
                    continue;
                }

                DotationCompte::updateOrCreate(
                    [
                        'budget_id' => $budget->id,
                        'compte_id' => $compte->id,
                    ],
                    [
                        'dotation_initiale' => $montant,
                        'prix_unitaire_ref' => $prixRef ?? $compte->prix_unitaire_ref ?? 0,
                    ]
                );

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

            $message = "{$imported} allocation(s) de compte importée(s) avec succès pour l'exercice {$budget->annee}.";
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['fichier' => 'Erreur d\'importation: ' . $e->getMessage()]);
        }
    }
}
