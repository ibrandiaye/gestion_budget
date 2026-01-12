<?php

namespace App\Http\Controllers;

use App\Models\Execution;
use App\Models\LigneBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExecutionController extends Controller
{
    public function index()
    {
        // View Budget Lines to execute against
        // Filter by user service if not admin
        $query = LigneBudget::with(['compte', 'service', 'budget']);

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('service_id', Auth::user()->service_id);
        }

        $lignes = $query->get();

        return view('execution.index', compact('lignes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ligne_budget_id' => 'required|exists:lignes_budget,id',
            'montant' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        $ligne = LigneBudget::findOrFail($request->ligne_budget_id);

        // Check Availability (Engagement limit)
        $disponible = $ligne->montant_alloue - $ligne->montant_engage;

        if ($request->montant > $disponible) {
            return back()->withErrors(['montant' => "Montant indisponible. Solde actuel: " . number_format($disponible, 0, ',', ' ') . " FCFA"]);
        }

        // Create Execution (Engagement)
        Execution::create([
            'ligne_budget_id' => $ligne->id,
            'montant' => $request->montant,
            'date' => $request->date,
            'type' => 'engagement',
            'description' => $request->description,
        ]);

        // Update Ligne stats
        $ligne->increment('montant_engage', $request->montant);

        return back()->with('success', 'Dépense engagée avec succès.');
    }
}
