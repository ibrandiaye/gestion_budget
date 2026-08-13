<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\Article;
use App\Models\Compte;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::with('articles')->orderBy('nom')->get();
        $articles = Article::where('actif', true)->with('compte')->get();
        $comptes = Compte::whereNotNull('compte_parent_id')->where('etat', 'actif')->get();

        return view('fournisseurs.index', compact('fournisseurs', 'articles', 'comptes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:191',
            'telephone' => 'nullable|string|max:50',
            'telephone2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:191',
            'ninea' => 'nullable|string|max:50',
            'registre_commerce' => 'nullable|string|max:255',
            'banque' => 'nullable|string|max:191',
            'numero_compte' => 'nullable|string|max:50',
        ]);

        Fournisseur::create([
            'nom' => $request->nom,
            'telephone' => $request->telephone,
            'telephone2' => $request->telephone2,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'ninea' => $request->ninea,
            'registre_commerce' => $request->registre_commerce,
            'banque' => $request->banque,
            'numero_compte' => $request->numero_compte,
            'actif' => true,
        ]);

        return back()->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
       // dd($request->actif);
        $request->validate([
            'nom' => 'required|string|max:191',
            'telephone' => 'nullable|string|max:50',
            'telephone2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:191',
            'ninea' => 'nullable|string|max:50',
            'registre_commerce' => 'nullable|string|max:255',
            'banque' => 'nullable|string|max:191',
            'numero_compte' => 'nullable|string|max:50',
           // 'actif' => 'nullable|boolean',
        ]);
        $etat = false;
        if($request->has('actif')) {
            $etat = true;
        }
        

        $fournisseur->update([
            'nom' => $request->nom,
            'telephone' => $request->telephone,
            'telephone2' => $request->telephone2,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'ninea' => $request->ninea,
            'registre_commerce' => $request->registre_commerce,
            'banque' => $request->banque,
            'numero_compte' => $request->numero_compte,
            'actif' => $etat,
        ]);

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return back()->with('success', 'Fournisseur supprimé.');
    }

    /**
     * Ajouter/modifier le prix d'un article pour ce fournisseur
     */
    public function ajouterArticle(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'prix' => 'required|numeric|min:0',
        ]);

        $fournisseur->articles()->syncWithoutDetaching([
            $request->article_id => ['prix' => $request->prix]
        ]);

        return back()->with('success', 'Article ajouté au fournisseur.');
    }

    /**
     * Supprimer un article du fournisseur
     */
    public function supprimerArticle(Fournisseur $fournisseur, Article $article)
    {
        $fournisseur->articles()->detach($article->id);
        return back()->with('success', 'Article retiré du fournisseur.');
    }

    /**
     * API: Obtenir les fournisseurs pour un compte donné
     */
    public function pourCompte(Compte $compte)
    {
        $articleIds = $compte->articles()->pluck('id');
        
        if ($articleIds->isEmpty()) {
            $fournisseurs = Fournisseur::actif()->with('articles')->get();
        } else {
            $fournisseurs = Fournisseur::actif()
                ->whereHas('articles', function ($q) use ($articleIds) {
                    $q->whereIn('articles.id', $articleIds);
                })
                ->with(['articles' => function ($q) use ($articleIds) {
                    $q->whereIn('articles.id', $articleIds);
                }])
                ->get();
        }

        return response()->json($fournisseurs);
    }

    /**
     * API: Obtenir les articles d'un fournisseur pour un compte
     */
    public function articlesPourCompte(Fournisseur $fournisseur, Compte $compte)
    {
        $articles = $fournisseur->articles()
            ->where('compte_id', $compte->id)
            ->get();

        return response()->json($articles);
    }
}
