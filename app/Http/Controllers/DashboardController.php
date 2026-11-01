<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Compte;
use App\Models\Commande;
use App\Models\Engagement;
use App\Models\Ajustement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $budgets = Budget::orderByDesc('annee')->get();
        $budget = $request->filled('budget_id')
            ? Budget::find($request->budget_id)
            : (Budget::where('statut', 'actif')->first() ?: $budgets->first());

        $data = [
            'annee' => $budget ? $budget->annee : 'N/A',
            'budget' => $budget,
            'budgets' => $budgets,
            'total_dotation' => 0,
            'total_alloue' => 0,
            'total_commandes' => 0,
            'total_engage' => 0,
            'total_disponible' => 0,
            'disponible_commande' => 0,
            'taux_execution' => 0,
            'top_comptes' => collect(),
            'chart_comptes_bar' => [
                'labels' => [],
                'alloue' => [],
                'commande' => [],
                'engage' => [],
            ],
            'chart_comptes' => [
                'labels' => [],
                'values' => [],
            ],
            'chart_evolution' => [
                'labels' => [],
                'values' => [],
            ],
        ];

        if ($budget) {
            $comptes = Compte::where('etat', 'actif')
                ->whereNotNull('compte_parent_id')
                ->with('compteParent')
                ->orderBy('numero')
                ->get();

            $statsComptes = $comptes->map(function ($compte) use ($budget) {
                $dotation_initiale = (float) ($compte->getDotationForBudget($budget->id) ?: $compte->lignesBudget()->where('budget_id', $budget->id)->sum('montant_alloue'));

                $virements_recus = (float) Ajustement::where('budget_id', $budget->id)
                    ->whereHas('destLigne', fn($q) => $q->where('compte_id', $compte->id))
                    ->sum('montant');
                $virements_emis = (float) Ajustement::where('budget_id', $budget->id)
                    ->whereHas('sourceLigne', fn($q) => $q->where('compte_id', $compte->id))
                    ->sum('montant');
                $virement = $virements_recus - $virements_emis;

                $dotation_totale = $dotation_initiale + $virement;

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

                $disponible = $dotation_totale - $total_engage;
                $disponible_commande = $dotation_totale - $total_commandes;
                $taux = $dotation_totale > 0 ? round(($total_engage / $dotation_totale) * 100, 1) : 0;

                return (object) [
                    'compte' => $compte,
                    'dotation_initiale' => $dotation_initiale,
                    'virement' => $virement,
                    'dotation_totale' => $dotation_totale,
                    'total_commandes' => $total_commandes,
                    'total_engage' => $total_engage,
                    'disponible' => $disponible,
                    'disponible_commande' => $disponible_commande,
                    'taux' => $taux,
                ];
            });

            $data['total_dotation'] = $statsComptes->sum('dotation_totale');
            $data['total_alloue'] = $data['total_dotation'];
            $data['total_commandes'] = $statsComptes->sum('total_commandes');
            $data['total_engage'] = $statsComptes->sum('total_engage');
            $data['total_disponible'] = $data['total_dotation'] - $data['total_engage'];
            $data['disponible_commande'] = $data['total_dotation'] - $data['total_commandes'];

            if ($data['total_dotation'] > 0) {
                $data['taux_execution'] = round(($data['total_engage'] / $data['total_dotation']) * 100, 1);
            }

            // Top comptes (prioritizing accounts with engagements or allocations)
            $sortedForTable = $statsComptes
                ->filter(fn($s) => $s->dotation_totale > 0 || $s->total_engage > 0 || $s->total_commandes > 0)
                ->sortByDesc(fn($s) => [$s->total_engage, $s->total_commandes, $s->dotation_totale])
                ->values();

            $data['top_comptes'] = $sortedForTable->take(8);

            // 1. Bar Chart: Dotation vs Engagé par Compte (Top 8 actifs)
            $barComptes = $sortedForTable->take(8);
            $data['chart_comptes_bar'] = [
                'labels' => $barComptes->map(fn($item) => $item->compte->numero . ' - ' . \Illuminate\Support\Str::limit($item->compte->libelle, 18))->values(),
                'alloue' => $barComptes->pluck('dotation_totale')->values(),
                'commande' => $barComptes->pluck('total_commandes')->values(),
                'engage' => $barComptes->pluck('total_engage')->values(),
            ];

            // 2. Doughnut Chart: Répartition des Dotations par Compte (Top 8)
            $topDotations = $statsComptes
                ->filter(fn($s) => $s->dotation_totale > 0)
                ->sortByDesc('dotation_totale')
                ->take(8)
                ->values();

            $data['chart_comptes'] = [
                'labels' => $topDotations->map(fn($item) => $item->compte->numero . ' - ' . \Illuminate\Support\Str::limit($item->compte->libelle, 20))->values(),
                'values' => $topDotations->pluck('dotation_totale')->values(),
            ];

            // 3. Line Chart: Évolution mensuelle des engagements sur l'exercice
            $engagements = Engagement::where(function ($q) use ($budget) {
                $q->where('budget_id', $budget->id)
                  ->orWhereHas('commande', fn($q2) => $q2->where('budget_id', $budget->id));
            })
                ->where('statut', '!=', 'rejete')
                ->orderBy('date')
                ->get();

            $groupedEngagements = $engagements->groupBy(function ($item) {
                return Carbon::parse($item->date)->locale('fr')->isoFormat('MMMM YYYY');
            });

            $data['chart_evolution'] = [
                'labels' => $groupedEngagements->keys()->values(),
                'values' => $groupedEngagements->map(fn($group) => (float) $group->sum('montant'))->values(),
            ];
        }

        return view('dashboard', compact('data'));
    }
}
