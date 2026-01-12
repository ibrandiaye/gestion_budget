<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetYearController extends Controller
{
    public function index()
    {
        $budgets = Budget::orderByDesc('annee')->get();
        return view('budgets.index', compact('budgets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required|integer|min:2020|unique:budgets,annee',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        // If making active, deactivate others (optional logic, but good practice)
        // For now, new budgets are 'brouillon' by default

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
        // Deactivate all others
        Budget::query()->update(['statut' => 'cloture']);

        // Activate this one
        $budget->update(['statut' => 'actif']);

        return back()->with('success', "Le budget $budget->annee est maintenant ACTIF.");
    }
}
