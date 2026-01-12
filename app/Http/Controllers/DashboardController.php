<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\LigneBudget;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get active budget
        $budget = Budget::where('statut', 'actif')->first();

        // Initialize variables
        $data = [
            'annee' => $budget ? $budget->annee : 'N/A',
            'total_alloue' => 0,
            'total_engage' => 0,
            'total_disponible' => 0,
            'taux_execution' => 0,
            'top_services' => [],
        ];

        if ($budget) {
            $lignes = LigneBudget::where('budget_id', $budget->id)->get();

            $data['total_alloue'] = $lignes->sum('montant_alloue');
            $data['total_engage'] = $lignes->sum('montant_engage');
            $data['total_disponible'] = $data['total_alloue'] - $data['total_engage'];

            if ($data['total_alloue'] > 0) {
                $data['taux_execution'] = round(($data['total_engage'] / $data['total_alloue']) * 100, 1);
            }

            // Top Services by Engagement
            $data['top_services'] = LigneBudget::where('budget_id', $budget->id)
                ->select('service_id', DB::raw('SUM(montant_engage) as total_engage'), DB::raw('SUM(montant_alloue) as total_alloue'))
                ->with('service')
                ->groupBy('service_id')
                ->orderByDesc('total_engage')
                ->take(5)
                ->get();
        }

        return view('dashboard', compact('data'));
    }
}
