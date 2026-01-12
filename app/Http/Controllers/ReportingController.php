<?php

namespace App\Http\Controllers;

use App\Models\LigneBudget;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function global()
    {
        // Stats Globaux
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
}
