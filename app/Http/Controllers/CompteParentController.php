<?php

namespace App\Http\Controllers;

use App\Models\CompteParent;
use Illuminate\Http\Request;

class CompteParentController extends Controller
{
    public function index()
    {
        $parents = CompteParent::orderBy('numero')->get();
        return view('compte_parents.index', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|unique:compte_parents,numero|max:10',
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:Fonctionnement,Investissement',
        ]);

        CompteParent::create($request->all());

        return back()->with('success', 'Catégorie de compte ajoutée.');
    }

    public function update(Request $request, CompteParent $compteParent)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:compte_parents,numero,' . $compteParent->id,
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:Fonctionnement,Investissement',
        ]);

        $compteParent->update($request->all());

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(CompteParent $compteParent)
    {
        if ($compteParent->comptes()->count() > 0) {
            return back()->withErrors(['msg' => 'Impossible de supprimer cette catégorie car elle contient des comptes.']);
        }

        $compteParent->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
