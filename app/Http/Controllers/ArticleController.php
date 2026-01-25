<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Compte;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        // Get all comptes with their articles
        $comptes = Compte::with('articles')->whereNull('parent_id')->orWhereHas('articles')->get();
        $allComptes = Compte::all();

        return view('articles.index', compact('comptes', 'allComptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'designation' => 'required|string|max:255',
        ]);

        Article::create([
            'compte_id' => $request->compte_id,
            'designation' => $request->designation,
            'actif' => $request->has('actif'),
        ]);

        return back()->with('success', 'Article ajouté avec succès.');
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'designation' => 'required|string|max:255',
        ]);

        $article->update([
            'compte_id' => $request->compte_id,
            'designation' => $request->designation,
            'actif' => $request->has('actif'),
        ]);

        return back()->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Article supprimé.');
    }
}
