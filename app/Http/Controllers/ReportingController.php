<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Compte;
use App\Models\CompteParent;
use App\Models\Commande;
use App\Models\Engagement;
use App\Models\Ajustement;
use App\Models\Besoin;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportingController extends Controller
{
    /**
     * Resolve the target budget (requested budget_id, active budget, or latest budget)
     */
    private function resolveBudget(Request $request)
    {
        $budgets = Budget::orderByDesc('annee')->get();
        $budget = $request->filled('budget_id')
            ? Budget::find($request->budget_id)
            : (Budget::where('statut', 'actif')->first() ?: $budgets->first());

        return [$budget, $budgets];
    }

    /**
     * Compute annual allocation, commandes, and engagements per active account for a given budget
     */
    private function buildStatsParCompte(?Budget $budget)
    {
        $comptes = Compte::where('etat', 'actif')
            ->whereNotNull('compte_parent_id')
            ->with(['compteParent'])
            ->get();

        return $comptes->map(function ($compte) use ($budget) {
            $dotation_initiale = 0;
            $virement = 0;
            $reamenagement = 0;
            $total_commandes = 0;
            $total_engage = 0;

            if ($budget) {
                $dotation_initiale = (float) ($compte->getDotationForBudget($budget->id) ?: $compte->lignesBudget()->where('budget_id', $budget->id)->sum('montant_alloue'));

                $virements_recus = (float) Ajustement::where('budget_id', $budget->id)
                    ->whereHas('destLigne', fn($q) => $q->where('compte_id', $compte->id))
                    ->sum('montant');
                $virements_emis = (float) Ajustement::where('budget_id', $budget->id)
                    ->whereHas('sourceLigne', fn($q) => $q->where('compte_id', $compte->id))
                    ->sum('montant');
                $virement = $virements_recus - $virements_emis;

                $reamenagement = 0;

                $total_commandes = (float) Commande::where('budget_id', $budget->id)
                    ->where('compte_id', $compte->id)
                    ->where('statut', '!=', 'rejete')
                    ->sum('montant_ttc');

                $total_engage = (float) Engagement::where(function ($q) use ($budget, $compte) {
                    $q->where(function ($q1) use ($budget, $compte) {
                        $q1->where('budget_id', $budget->id)
                           ->where('compte_id', $compte->id);
                    })->orWhereHas('commande', function ($q2) use ($budget, $compte) {
                        $q2->where('budget_id', $budget->id)
                           ->where('compte_id', $compte->id);
                    });
                })->where('statut', '!=', 'rejete')->sum('montant');
            }

            $dotation_totale = $dotation_initiale + $virement + $reamenagement;
            $disponible_commande = $dotation_totale - $total_commandes;
            $disponible_engage = $dotation_totale - $total_engage;
            $taux = $dotation_totale > 0 ? ($total_engage / $dotation_totale) * 100 : 0;

            return (object) [
                'compte' => $compte,
                'dotation_initiale' => $dotation_initiale,
                'virement' => $virement,
                'reamenagement' => $reamenagement,
                'dotation_totale' => $dotation_totale,
                'total_alloue' => $dotation_totale,
                'total_commandes' => $total_commandes,
                'disponible_commande' => $disponible_commande,
                'total_engage' => $total_engage,
                'disponible_engage' => $disponible_engage,
                'taux' => $taux,
            ];
        })->sortBy('compte.numero')->values();
    }

    public function global(Request $request)
    {
        [$budget, $budgets] = $this->resolveBudget($request);
        $statsComptes = $this->buildStatsParCompte($budget);

        $totalAlloue = $statsComptes->sum('dotation_totale');
        $totalCommandes = $statsComptes->sum('total_commandes');
        $totalEngage = $statsComptes->sum('total_engage');
        $totalDisponible = $totalAlloue - $totalEngage;
        $tauxExecution = $totalAlloue > 0 ? ($totalEngage / $totalAlloue) * 100 : 0;

        // Top 5 Comptes (par engagement puis dotation)
        $topComptes = $statsComptes
            ->filter(fn($s) => $s->total_engage > 0 || $s->dotation_totale > 0)
            ->sortByDesc(fn($s) => [$s->total_engage, $s->total_commandes, $s->dotation_totale])
            ->take(5)
            ->values();

        // Synthèse par Catégorie (CompteParent)
        $statsCategories = $statsComptes
            ->groupBy(fn($s) => $s->compte->compte_parent_id)
            ->map(function ($group) {
                $parent = $group->first()->compte->compteParent;
                $alloue = $group->sum('dotation_totale');
                $commande = $group->sum('total_commandes');
                $engage = $group->sum('total_engage');
                $disponible = $alloue - $engage;
                $taux = $alloue > 0 ? ($engage / $alloue) * 100 : 0;

                return (object) [
                    'parent' => $parent,
                    'total_alloue' => $alloue,
                    'total_commandes' => $commande,
                    'total_engage' => $engage,
                    'disponible' => $disponible,
                    'taux' => $taux,
                ];
            })
            ->sortBy(fn($c) => $c->parent->numero ?? '')
            ->values();

        return view('reporting.global', compact(
            'budget',
            'budgets',
            'totalAlloue',
            'totalCommandes',
            'totalEngage',
            'totalDisponible',
            'tauxExecution',
            'statsComptes',
            'statsCategories',
            'topComptes'
        ));
    }

    public function parCompte(Request $request)
    {
        [$budget, $budgets] = $this->resolveBudget($request);
        $statsComptes = $this->buildStatsParCompte($budget);

        return view('reporting.accounts', compact('statsComptes', 'budget', 'budgets'));
    }

    public function exportBesoinsByService()
    {
        $headers = ['Service', 'Compte Numero', 'Compte Libelle', 'Montant Demande', 'Statut', 'Date'];
        $data = Besoin::with(['service', 'compte'])
            ->get()
            ->map(function ($item) {
                return [
                    $item->service->nom ?? '-',
                    $item->compte->numero ?? '-',
                    $item->compte->libelle ?? '-',
                    $item->montant_demande,
                    $item->statut,
                    $item->created_at,
                ];
            });

        return $this->exportToCsv('besoins_par_service.csv', $headers, $data);
    }

    public function exportBesoinsByCompte()
    {
        $headers = ['Compte Numero', 'Compte Libelle', 'Total Demande', 'Nombre Demandes'];
        $data = Besoin::with('compte')
            ->select('compte_id', DB::raw('SUM(montant_demande) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('compte_id')
            ->get()
            ->map(function ($item) {
                return [
                    $item->compte->numero ?? '-',
                    $item->compte->libelle ?? '-',
                    $item->total,
                    $item->count
                ];
            });

        return $this->exportToCsv('besoins_par_compte.csv', $headers, $data);
    }

    public function exportBudgetByService(Request $request)
    {
        // Redirects/exports by account since budget allocations are now per account each year
        return $this->exportBudgetByCompte($request);
    }

    public function exportBudgetByCompte(Request $request)
    {
        $headers = [
            'Compte et libellé',
            'Dotation Initiale',
            'Virement',
            'Réaménagement',
            'Dotation Totale',
            'Commande Total',
            'Commande Disponible',
            'Engagement Total',
            'Engagement Disponible'
        ];

        [$budget] = $this->resolveBudget($request);
        $statsComptes = $this->buildStatsParCompte($budget);

        $data = $statsComptes->map(function ($stat) {
            return [
                $stat->compte->numero . ' - ' . $stat->compte->libelle,
                $stat->dotation_initiale,
                $stat->virement,
                $stat->reamenagement,
                $stat->dotation_totale,
                $stat->total_commandes,
                $stat->disponible_commande,
                $stat->total_engage,
                $stat->disponible_engage,
            ];
        });

        return $this->exportToCsv('budget_par_compte_' . ($budget ? $budget->annee : date('Y')) . '.csv', $headers, $data);
    }

    public function viewMSHP(Request $request)
    {
        [$budget, $budgets] = $this->resolveBudget($request);
        $data = $this->prepareReportData(false, $budget);
        return view('reporting.mshp', compact('data', 'budget', 'budgets'));
    }

    public function viewMFB(Request $request)
    {
        [$budget, $budgets] = $this->resolveBudget($request);
        $data = $this->prepareReportData(true, $budget);
        return view('reporting.mfb', compact('data', 'budget', 'budgets'));
    }

    public function exportMSHP(Request $request)
    {
        [$budget] = $this->resolveBudget($request);
        $data = $this->prepareReportData(false, $budget);
        $filename = 'rapport_mshp_' . ($budget ? $budget->annee . '_' : '') . date('Ymd_His') . '.csv';
        return $this->generateCSVReport($data, $filename, false, $budget);
    }

    public function exportMFB(Request $request)
    {
        [$budget] = $this->resolveBudget($request);
        $data = $this->prepareReportData(true, $budget);
        $filename = 'rapport_mfb_' . ($budget ? $budget->annee . '_' : '') . date('Ymd_His') . '.csv';
        return $this->generateCSVReport($data, $filename, true, $budget);
    }

    private function getCompteMetrics(Compte $compte, ?Budget $budget): array
    {
        if (!$budget) {
            return ['alloue' => 0, 'commande' => 0, 'engage' => 0];
        }

        $dotation_initiale = (float) ($compte->getDotationForBudget($budget->id) ?: $compte->lignesBudget()->where('budget_id', $budget->id)->sum('montant_alloue'));

        $virements_recus = (float) Ajustement::where('budget_id', $budget->id)
            ->whereHas('destLigne', fn($q) => $q->where('compte_id', $compte->id))
            ->sum('montant');
        $virements_emis = (float) Ajustement::where('budget_id', $budget->id)
            ->whereHas('sourceLigne', fn($q) => $q->where('compte_id', $compte->id))
            ->sum('montant');

        $alloue = $dotation_initiale + ($virements_recus - $virements_emis);

        $commande = (float) Commande::where('budget_id', $budget->id)
            ->where('compte_id', $compte->id)
            ->where('statut', '!=', 'rejete')
            ->sum('montant_ttc');

        $engage = (float) Engagement::where(function ($q) use ($budget, $compte) {
            $q->where(function ($q1) use ($budget, $compte) {
                $q1->where('budget_id', $budget->id)
                   ->where('compte_id', $compte->id);
            })->orWhereHas('commande', function ($q2) use ($budget, $compte) {
                $q2->where('budget_id', $budget->id)
                   ->where('compte_id', $compte->id);
            });
        })->where('statut', '!=', 'rejete')->sum('montant');

        return [
            'alloue' => $alloue,
            'commande' => $commande,
            'engage' => $engage,
        ];
    }

    private function prepareReportData($isMFB = false, ?Budget $budget = null)
    {
        if (!$budget) {
            $budget = Budget::where('statut', 'actif')->first() ?: Budget::orderByDesc('annee')->first();
        }

        $parents = CompteParent::orderByRaw("CASE WHEN type = 'Fonctionnement' THEN 1 ELSE 2 END")
            ->orderBy('numero')
            ->get();

        $data = [];

        if ($isMFB) {
            // MFB: Aggregated by 4-digit accounts
            foreach ($parents as $parent) {
                $allComptes = Compte::where('compte_parent_id', $parent->id)
                    ->where('etat', 'actif')
                    ->orderBy('numero')
                    ->get();

                $aggregated = [];

                foreach ($allComptes as $compte) {
                    $prefix = substr($compte->numero, 0, 4);
                    $metrics = $this->getCompteMetrics($compte, $budget);

                    if (!isset($aggregated[$prefix])) {
                        $baseCompte = Compte::where('compte_parent_id', $parent->id)
                            ->where('numero', $prefix)
                            ->first();

                        $aggregated[$prefix] = [
                            'numero' => $prefix,
                            'libelle' => $baseCompte ? $baseCompte->libelle : $compte->libelle,
                            'alloue' => 0,
                            'commande' => 0,
                            'engage' => 0,
                        ];
                    }

                    $aggregated[$prefix]['alloue'] += $metrics['alloue'];
                    $aggregated[$prefix]['commande'] += $metrics['commande'];
                    $aggregated[$prefix]['engage'] += $metrics['engage'];
                }

                $parentData = [
                    'parent' => $parent,
                    'comptes' => [],
                    'total_alloue' => 0,
                    'total_commande' => 0,
                    'total_engage' => 0,
                ];

                foreach ($aggregated as $item) {
                    $taux = $item['alloue'] > 0 ? ($item['engage'] / $item['alloue']) * 100 : 0;
                    $restant = $item['alloue'] - $item['engage'];

                    $parentData['comptes'][] = [
                        'numero' => $item['numero'],
                        'libelle' => $item['libelle'],
                        'alloue' => $item['alloue'],
                        'commande' => $item['commande'],
                        'engage' => $item['engage'],
                        'taux' => $taux,
                        'restant' => $restant,
                    ];

                    $parentData['total_alloue'] += $item['alloue'];
                    $parentData['total_commande'] += $item['commande'];
                    $parentData['total_engage'] += $item['engage'];
                }

                if (!empty($parentData['comptes'])) {
                    $data[] = $parentData;
                }
            }
        } else {
            // MSHP: All active accounts detailed
            foreach ($parents as $parent) {
                $comptes = Compte::where('compte_parent_id', $parent->id)
                    ->where('etat', 'actif')
                    ->orderBy('numero')
                    ->get();

                $parentData = [
                    'parent' => $parent,
                    'comptes' => [],
                    'total_alloue' => 0,
                    'total_commande' => 0,
                    'total_engage' => 0,
                ];

                foreach ($comptes as $compte) {
                    $metrics = $this->getCompteMetrics($compte, $budget);
                    $alloue = $metrics['alloue'];
                    $commande = $metrics['commande'];
                    $engage = $metrics['engage'];
                    $taux = $alloue > 0 ? ($engage / $alloue) * 100 : 0;
                    $restant = $alloue - $engage;

                    $parentData['comptes'][] = [
                        'numero' => $compte->numero,
                        'libelle' => $compte->libelle,
                        'alloue' => $alloue,
                        'commande' => $commande,
                        'engage' => $engage,
                        'taux' => $taux,
                        'restant' => $restant,
                    ];

                    $parentData['total_alloue'] += $alloue;
                    $parentData['total_commande'] += $commande;
                    $parentData['total_engage'] += $engage;
                }

                if (!empty($parentData['comptes'])) {
                    $data[] = $parentData;
                }
            }
        }

        return $data;
    }

    private function generateCSVReport($data, $filename, $isMFB = false, ?Budget $budget = null)
    {
        $callback = function () use ($data, $isMFB, $budget) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $anneeLabel = $budget ? ' - EXERCICE ' . $budget->annee : '';
            $this->fputcsv_semicolon($file, [
                ($isMFB ? 'RAPPORT MFB - EXECUTION BUDGETAIRE (AGREGE)' : 'RAPPORT MSHP - EXECUTION BUDGETAIRE PAR COMPTE') . $anneeLabel
            ]);
            $this->fputcsv_semicolon($file, []);

            $grandAlloue = 0;
            $grandEngage = 0;

            foreach ($data as $parentData) {
                $parent = $parentData['parent'];

                $this->fputcsv_semicolon($file, [$parent->numero . ' - ' . $parent->libelle . ' (' . $parent->type . ')']);
                $this->fputcsv_semicolon($file, ['Numéro', 'Libellé', 'Dotation Annuelle', 'Total Engagé', 'Taux (%)', 'Solde Disponible']);

                foreach ($parentData['comptes'] as $compte) {
                    $this->fputcsv_semicolon($file, [
                        $compte['numero'],
                        $compte['libelle'],
                        $compte['alloue'],
                        $compte['engage'],
                        round($compte['taux'], 2),
                        $compte['restant']
                    ]);
                }

                $taux_total = $parentData['total_alloue'] > 0 ? ($parentData['total_engage'] / $parentData['total_alloue']) * 100 : 0;
                $this->fputcsv_semicolon($file, [
                    'TOTAL ' . $parent->libelle,
                    '',
                    $parentData['total_alloue'],
                    $parentData['total_engage'],
                    round($taux_total, 2),
                    $parentData['total_alloue'] - $parentData['total_engage']
                ]);

                $this->fputcsv_semicolon($file, []);

                $grandAlloue += $parentData['total_alloue'];
                $grandEngage += $parentData['total_engage'];
            }

            $grandTaux = $grandAlloue > 0 ? ($grandEngage / $grandAlloue) * 100 : 0;
            $this->fputcsv_semicolon($file, [
                'TOTAL GENERAL EXERCICE',
                '',
                $grandAlloue,
                $grandEngage,
                round($grandTaux, 2),
                $grandAlloue - $grandEngage
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ]);
    }

    private function fputcsv_semicolon($handle, $fields)
    {
        fputcsv($handle, $fields, ';');
    }

    private function exportToCsv($filename, $headers, $data)
    {
        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');

            foreach ($data as $row) {
                fputcsv($file, is_array($row) ? $row : $row->toArray(), ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
