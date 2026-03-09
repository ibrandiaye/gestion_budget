<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\LigneBudget;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Execution;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get active budget
        $budget = Budget::where('statut', 'actif')->first() ?: Budget::latest()->first();

        // Initialize variables
        $data = [
            'annee' => $budget ? $budget->annee : 'N/A',
            'total_alloue' => 0,
            'total_engage' => 0,
            'total_disponible' => 0,
            'taux_execution' => 0,
            'top_services' => [],
            // Total budget based on dotation_initiale for the active budget
            'total_dotation' => $budget ? \DB::table('dotations_comptes')->where('budget_id', $budget->id)->sum('dotation_initiale') : 0,
        ];

        if ($budget) {
            // Check if user is a service manager
            if ($user->role === 'service' && $user->service_id) {
                // SERVICE MANAGER VIEW - Filter by their service
                $lignes = LigneBudget::where('budget_id', $budget->id)
                    ->where('service_id', $user->service_id)
                    ->get();

                $data['total_alloue'] = $lignes->sum('montant_alloue');
                $data['total_engage'] = $lignes->sum('montant_engage');
                $data['total_disponible'] = $data['total_alloue'] - $data['total_engage'];

                if ($data['total_alloue'] > 0) {
                    $data['taux_execution'] = round(($data['total_engage'] / $data['total_alloue']) * 100, 1);
                }

                // Besoins statistics for this service
                $besoins = \App\Models\Besoin::where('service_id', $user->service_id)
                    ->where('budget_id', $budget->id)
                    ->get();

                $data['besoins_stats'] = [
                    'total' => $besoins->count(),
                    'total_quantite' => $besoins->sum('quantite'),
                    'cree' => $besoins->where('statut', 'cree')->count(),
                    'valide' => $besoins->where('statut', 'valide')->count(),
                    'rejete' => $besoins->where('statut', 'rejete')->count(),
                ];

                // Budget lines by account for this service
                $data['lignes_budget'] = LigneBudget::where('budget_id', $budget->id)
                    ->where('service_id', $user->service_id)
                    ->with('compte')
                    ->get();

                // Recent executions for this service
                $data['recent_executions'] = Execution::whereHas('ligneBudget', function ($q) use ($budget, $user) {
                    $q->where('budget_id', $budget->id)
                        ->where('service_id', $user->service_id);
                })
                    ->with('ligneBudget.compte')
                    ->orderByDesc('date')
                    ->take(5)
                    ->get();

            } else {
                // ADMIN VIEW - Global statistics
                $lignes = LigneBudget::where('budget_id', $budget->id)->get();

                $data['total_alloue'] = $lignes->sum('montant_alloue');
                $data['total_engage'] = $lignes->sum('montant_engage');
               // $data['total_disponible'] = $data['total_alloue'] - $data['total_engage'];
               $data['total_disponible'] = $data['total_dotation'] - $data['total_engage'];

               /*  if ($data['total_alloue'] > 0) {
                    $data['taux_execution'] = round(($data['total_engage'] / $data['total_alloue']) * 100, 1);
                } 
                */

                if ($data['total_dotation'] > 0) {
                    $data['taux_execution'] = round(($data['total_engage'] / $data['total_dotation']) * 100, 1);
                }

                // Top Services by Engagement
                $data['top_services'] = LigneBudget::where('budget_id', $budget->id)
                    ->select('service_id', DB::raw('SUM(montant_engage) as total_engage'), DB::raw('SUM(montant_alloue) as total_alloue'))
                    ->with('service')
                    ->groupBy('service_id')
                    ->orderByDesc('total_engage')
                    ->take(5)
                    ->get();

                // CHART DATA PREPARATION

                // 1. Budget vs Engagé par Service
                $servicesStats = LigneBudget::where('budget_id', $budget->id)
                    ->select('service_id', DB::raw('SUM(montant_alloue) as total_alloue'), DB::raw('SUM(montant_engage) as total_engage'))
                    ->with('service')
                    ->groupBy('service_id')
                    ->get();

                $data['chart_services'] = [
                    'labels' => $servicesStats->pluck('service.nom'),
                    'alloue' => $servicesStats->pluck('total_alloue'),
                    'engage' => $servicesStats->pluck('total_engage'),
                ];

                // 2. Répartition par Compte (Top 8)
                $comptesStats = LigneBudget::where('budget_id', $budget->id)
                    ->select('compte_id', DB::raw('SUM(montant_alloue) as total_alloue'))
                    ->with('compte')
                    ->groupBy('compte_id')
                    ->orderByDesc('total_alloue')
                    ->take(8)
                    ->get();

                $data['chart_comptes'] = [
                    'labels' => $comptesStats->map(fn($item) => $item->compte->numero . ' ' . $item->compte->libelle),
                    'values' => $comptesStats->pluck('total_alloue'),
                ];

                // 3. Evolution des dépenses (Par mois)
                $executions = Execution::whereHas('ligneBudget', function ($q) use ($budget) {
                    $q->where('budget_id', $budget->id);
                })->orderBy('date')->get();

                $groupedExecutions = $executions->groupBy(function ($item) {
                    return Carbon::parse($item->date)->locale('fr')->isoFormat('MMMM');
                });

                $data['chart_evolution'] = [
                    'labels' => $groupedExecutions->keys(),
                    'values' => $groupedExecutions->map(fn($group) => $group->sum('montant'))->values(),
                ];
            }
        }

        return view('dashboard', compact('data'));
    }
}
