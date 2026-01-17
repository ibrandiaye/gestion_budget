<?php

namespace App\Http\Controllers;

use App\Models\LigneBudget;
use App\Models\Service;
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
