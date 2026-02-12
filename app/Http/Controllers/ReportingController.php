<?php

namespace App\Http\Controllers;

use App\Models\LigneBudget;
use App\Models\Service;
use App\Models\Compte;
use App\Models\CompteParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Besoin;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportingController extends Controller
{
    public function global()
    {
        $user = auth()->user();

        // Check if user is a service manager
        if ($user->role === 'service' && $user->service_id) {
            // SERVICE MANAGER VIEW - Filter by their service
            $lignes = LigneBudget::where('service_id', $user->service_id)->get();

            $totalAlloue = $lignes->sum('montant_alloue');
            $totalEngage = $lignes->sum('montant_engage');
            $tauxExecution = $totalAlloue > 0 ? ($totalEngage / $totalAlloue) * 100 : 0;

            // Budget lines by account for this service
            $statsComptes = LigneBudget::where('service_id', $user->service_id)
                ->select('compte_id', DB::raw('SUM(montant_alloue) as total_alloue'), DB::raw('SUM(montant_engage) as total_engage'))
                ->with('compte')
                ->groupBy('compte_id')
                ->get()
                ->map(function ($stat) {
                    $stat->taux = $stat->total_alloue > 0 ? ($stat->total_engage / $stat->total_alloue) * 100 : 0;
                    return $stat;
                });

            // Top budget lines for this service
            $topLignes = LigneBudget::where('service_id', $user->service_id)
                ->with(['compte'])
                ->orderByDesc('montant_engage')
                ->take(5)
                ->get();

            return view('reporting.global', compact('totalAlloue', 'totalEngage', 'tauxExecution', 'statsComptes', 'topLignes'));

        } else {
            // ADMIN VIEW - Global statistics
            $totalAlloue = LigneBudget::sum('montant_alloue');
            $totalEngage = LigneBudget::sum('montant_engage');
            $tauxExecution = $totalAlloue > 0 ? ($totalEngage / $totalAlloue) * 100 : 0;

            // Par Service
            $statsServices = LigneBudget::select(
                'service_id',
                DB::raw('SUM(montant_alloue) as total_alloue'),
                DB::raw('SUM(montant_engage) as total_engage')
            )
                ->groupBy('service_id')
                ->with('service')
                ->get()
                ->map(function ($stat) {
                    $stat->taux = $stat->total_alloue > 0 ? ($stat->total_engage / $stat->total_alloue) * 100 : 0;
                    return $stat;
                });

            // Top Dépenses (Lignes avec le plus fort engagement)
            $topLignes = LigneBudget::with(['compte', 'service'])
                ->orderByDesc('montant_engage')
                ->take(5)
                ->get();

            return view('reporting.global', compact('totalAlloue', 'totalEngage', 'tauxExecution', 'statsServices', 'topLignes'));
        }
    }

    public function parCompte()
    {
        $budget = \App\Models\Budget::where('statut', 'actif')->first();

        // Aggregate by Compte (across all services)
        $query = LigneBudget::select(
            'compte_id',
            DB::raw('SUM(montant_alloue) as total_alloue'),
            DB::raw('SUM(montant_engage) as total_engage')
        )
            ->groupBy('compte_id')
            ->with(['compte']);

        if ($budget) {
            $query->where('budget_id', $budget->id);
        }

        $statsComptes = $query->get()->sortBy('compte.numero');

        return view('reporting.accounts', compact('statsComptes'));
    }
    public function exportBesoinsByService()
    {
        $headers = ['Service', 'Compte Numero', 'Compte Libelle', 'Montant Demande', 'Statut', 'Date'];
        $data = Besoin::with(['service', 'compte'])
            ->get()
            ->map(function ($item) {
                return [
                    $item->service->nom,
                    $item->compte->numero,
                    $item->compte->libelle,
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
                    $item->compte->numero,
                    $item->compte->libelle,
                    $item->total,
                    $item->count
                ];
            });

        return $this->exportToCsv('besoins_par_compte.csv', $headers, $data);
    }

    public function exportBudgetByService()
    {
        $headers = ['Service', 'Total Alloue', 'Total Engage', 'Taux Execution'];
        $data = LigneBudget::with('service')
            ->select('service_id', DB::raw('SUM(montant_alloue) as alloue'), DB::raw('SUM(montant_engage) as engage'))
            ->groupBy('service_id')
            ->get()
            ->map(function ($item) {
                return [
                    $item->service->nom,
                    $item->alloue,
                    $item->engage,
                    $item->alloue > 0 ? round(($item->engage / $item->alloue) * 100, 2) . '%' : '0%'
                ];
            });

        return $this->exportToCsv('budget_par_service.csv', $headers, $data);
    }

    public function exportBudgetByCompte()
    {
        $headers = ['Compte Numero', 'Compte Libelle', 'Total Alloue', 'Total Engage'];
        $data = LigneBudget::with('compte')
            ->select('compte_id', DB::raw('SUM(montant_alloue) as alloue'), DB::raw('SUM(montant_engage) as engage'))
            ->groupBy('compte_id')
            ->get()
            ->map(function ($item) {
                return [
                    $item->compte->numero,
                    $item->compte->libelle,
                    $item->alloue,
                    $item->engage
                ];
            });

        return $this->exportToCsv('budget_par_compte.csv', $headers, $data);
    }

    public function exportMSHP()
    {
        $budget = \App\Models\Budget::where('statut', 'actif')->first();
        
        // Get all CompteParents ordered by type (Fonctionnement first)
        $parents = CompteParent::orderByRaw("CASE WHEN type = 'Fonctionnement' THEN 1 ELSE 2 END")
            ->orderBy('numero')
            ->get();
        
        $data = [];
        
        foreach ($parents as $parent) {
            // Get all comptes for this parent with their budget data
            $comptes = Compte::where('compte_parent_id', $parent->id)
                ->orderBy('numero')
                ->get();
            
            $parentData = [
                'parent' => $parent,
                'comptes' => [],
                'total_alloue' => 0,
                'total_engage' => 0,
            ];
            
            foreach ($comptes as $compte) {
                // Sum across all services for this compte
                $stats = LigneBudget::where('compte_id', $compte->id)
                    ->when($budget, fn($q) => $q->where('budget_id', $budget->id))
                    ->selectRaw('SUM(montant_alloue) as total_alloue, SUM(montant_engage) as total_engage')
                    ->first();
                
                $alloue = $stats->total_alloue ?? 0;
                $engage = $stats->total_engage ?? 0;
                $taux = $alloue > 0 ? ($engage / $alloue) * 100 : 0;
                $restant = $alloue - $engage;
                
                $parentData['comptes'][] = [
                    'numero' => $compte->numero,
                    'libelle' => $compte->libelle,
                    'alloue' => $alloue,
                    'engage' => $engage,
                    'taux' => $taux,
                    'restant' => $restant,
                ];
                
                $parentData['total_alloue'] += $alloue;
                $parentData['total_engage'] += $engage;
            }
            
            if (!empty($parentData['comptes'])) {
                $data[] = $parentData;
            }
        }
        
        return $this->generateCSVReport($data, 'rapport_mshp.csv', false);
    }
    
    public function exportMFB()
    {
        $budget = \App\Models\Budget::where('statut', 'actif')->first();
        
        // Get all CompteParents ordered by type (Fonctionnement first)
        $parents = CompteParent::orderByRaw("CASE WHEN type = 'Fonctionnement' THEN 1 ELSE 2 END")
            ->orderBy('numero')
            ->get();
        
        $data = [];
        
        foreach ($parents as $parent) {
            // Get all comptes for this parent
            $allComptes = Compte::where('compte_parent_id', $parent->id)
                ->orderBy('numero')
                ->get();
            
            // Aggregate by 4-digit prefix
            $aggregated = [];
            
            foreach ($allComptes as $compte) {
                // Get 4-digit prefix
                $prefix = substr($compte->numero, 0, 4);
                
                // Only include if numero is exactly 4 digits OR aggregate longer ones
                $stats = LigneBudget::where('compte_id', $compte->id)
                    ->when($budget, fn($q) => $q->where('budget_id', $budget->id))
                    ->selectRaw('SUM(montant_alloue) as total_alloue, SUM(montant_engage) as total_engage')
                    ->first();
                
                $alloue = $stats->total_alloue ?? 0;
                $engage = $stats->total_engage ?? 0;
                
                if (!isset($aggregated[$prefix])) {
                    // Find the compte with exactly this 4-digit number for the label
                    $baseCompte = Compte::where('compte_parent_id', $parent->id)
                        ->where('numero', $prefix)
                        ->first();
                    
                    $aggregated[$prefix] = [
                        'numero' => $prefix,
                        'libelle' => $baseCompte ? $baseCompte->libelle : 'Compte ' . $prefix,
                        'alloue' => 0,
                        'engage' => 0,
                    ];
                }
                
                $aggregated[$prefix]['alloue'] += $alloue;
                $aggregated[$prefix]['engage'] += $engage;
            }
            
            // Calculate taux and restant for aggregated data
            $parentData = [
                'parent' => $parent,
                'comptes' => [],
                'total_alloue' => 0,
                'total_engage' => 0,
            ];
            
            foreach ($aggregated as $item) {
                $taux = $item['alloue'] > 0 ? ($item['engage'] / $item['alloue']) * 100 : 0;
                $restant = $item['alloue'] - $item['engage'];
                
                $parentData['comptes'][] = [
                    'numero' => $item['numero'],
                    'libelle' => $item['libelle'],
                    'alloue' => $item['alloue'],
                    'engage' => $item['engage'],
                    'taux' => $taux,
                    'restant' => $restant,
                ];
                
                $parentData['total_alloue'] += $item['alloue'];
                $parentData['total_engage'] += $item['engage'];
            }
            
            if (!empty($parentData['comptes'])) {
                $data[] = $parentData;
            }
        }
        
        return $this->generateCSVReport($data, 'rapport_mfb.csv', true);
    }
    
    private function generateCSVReport($data, $filename, $isMFB = false)
    {
        $callback = function () use ($data, $isMFB) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Title row
            $this->fputcsv_semicolon($file, [$isMFB ? 'RAPPORT MFB - EXECUTION BUDGETAIRE (AGREGE)' : 'RAPPORT MSHP - EXECUTION BUDGETAIRE']);
            $this->fputcsv_semicolon($file, []); // Empty row
            
            foreach ($data as $parentData) {
                $parent = $parentData['parent'];
                
                // Parent header
                $this->fputcsv_semicolon($file, [$parent->numero . ' - ' . $parent->libelle . ' (' . $parent->type . ')']);
                
                // Column headers
                $this->fputcsv_semicolon($file, ['Numero', 'Libelle', 'Total Alloue', 'Total Execute', 'Taux (%)', 'Restant']);
                
                // Compte rows
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
                
                // Total row
                $taux_total = $parentData['total_alloue'] > 0 ? ($parentData['total_engage'] / $parentData['total_alloue']) * 100 : 0;
                $this->fputcsv_semicolon($file, [
                    'TOTAL ' . $parent->libelle,
                    '',
                    $parentData['total_alloue'],
                    $parentData['total_engage'],
                    round($taux_total, 2),
                    $parentData['total_alloue'] - $parentData['total_engage']
                ]);
                
                $this->fputcsv_semicolon($file, []); // Empty row between parents
            }
            
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
        // Use semicolon as delimiter for better Excel compatibility (French locale)
        fputcsv($handle, $fields, ';');
    }

    private function exportToCsv($filename, $headers, $data)
    {
        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, is_array($row) ? $row : $row->toArray());
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
