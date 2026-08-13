<?php

namespace App\Http\Controllers;

use App\Models\Signataire;
use Illuminate\Http\Request;

class SignataireController extends Controller
{
    public function index()
    {
        $signataires = Signataire::orderBy('nom')->get();
        return view('signataires.index', compact('signataires'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'matricule' => 'nullable|string|max:50|unique:signataires,matricule',
            'fonction'  => 'required|string|max:150',
        ]);

        Signataire::create($request->only('prenom', 'nom', 'matricule', 'fonction'));

        return back()->with('success', 'Signataire ajouté avec succès.');
    }

    public function update(Request $request, Signataire $signataire)
    {
        $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'matricule' => 'nullable|string|max:50|unique:signataires,matricule,' . $signataire->id,
            'fonction'  => 'required|string|max:150',
        ]);

        $signataire->update($request->only('prenom', 'nom', 'matricule', 'fonction'));

        return back()->with('success', 'Signataire modifié avec succès.');
    }

    public function toggleActif(Signataire $signataire)
    {
        $signataire->update(['actif' => !$signataire->actif]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Signataire $signataire)
    {
        $signataire->delete();
        return back()->with('success', 'Signataire supprimé.');
    }
}
