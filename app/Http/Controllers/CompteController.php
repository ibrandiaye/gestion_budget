<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        // Get parent accounts with children for display
        $comptes = Compte::with('children')->whereNull('parent_id')->get();
        // Get all accounts for parent selection dropdown
        $allComptes = Compte::all();

        return view('comptes.index', compact('comptes', 'allComptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:50|unique:comptes',
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:Fonctionnement,Investissement',
            'parent_id' => 'nullable|exists:comptes,id',
        ]);

        Compte::create($request->all());

        return back()->with('success', 'Compte ajouté avec succès.');
    }

    public function update(Request $request, Compte $compte)
    {
        $request->validate([
            'numero' => 'required|string|max:50|unique:comptes,numero,' . $compte->id,
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:Fonctionnement,Investissement',
            'parent_id' => 'nullable|exists:comptes,id',
        ]);

        $compte->update($request->all());

        return back()->with('success', 'Compte mis à jour avec succès.');
    }

    public function destroy(Compte $compte)
    {
        $compte->delete();
        return back()->with('success', 'Compte supprimé.');
    }
}
