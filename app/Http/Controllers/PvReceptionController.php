<?php

namespace App\Http\Controllers;

use App\Models\PvReception;
use App\Models\PvReceptionArticle;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PvReceptionController extends Controller
{
    public function index()
    {
        $pvs = PvReception::with('commande')->latest()->get();
        return view('pv_receptions.index', compact('pvs'));
    }

    public function create(Request $request)
    {
        $commande_id = $request->get('commande_id');
        $commande = Commande::with('articles')->findOrFail($commande_id);
        
        return view('pv_receptions.create', compact('commande'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'date_reception' => 'required|date',
            'tva' => 'required|numeric',
            'articles' => 'required|array',
            'articles.*.commande_article_id' => 'required|exists:commande_articles,id',
            'articles.*.quantite_receptionnee' => 'required|integer',
        ]);

        $commande = Commande::with('articles')->findOrFail($request->commande_id);
        
        // Calculate totals
        $montant_total = 0;
        foreach ($request->articles as $art) {
            $c_art = $commande->articles->where('id', $art['commande_article_id'])->first();
            if ($c_art) {
                $montant_total += $c_art->prix_unitaire * $art['quantite_receptionnee'];
            }
        }

        $montant_ttc = $montant_total + ($montant_total * $request->tva / 100);

        // Handle comma separated texts
        $signataires = [];
        if ($request->signataires_texte) {
            $signataires = array_map('trim', explode(',', $request->signataires_texte));
        }

        $pieces = [];
        if ($request->pieces_justificatives_texte) {
            $pieces = array_map('trim', explode(',', $request->pieces_justificatives_texte));
        }

        $pv = PvReception::create([
            'commande_id' => $commande->id,
            'date_reception' => $request->date_reception,
            'montant_total' => $montant_total,
            'tva' => $request->tva,
            'montant_ttc' => $montant_ttc,
            'created_by' => Auth::id(),
            'signataires' => $signataires,
            'pieces_justificatives' => $pieces,
        ]);

        foreach ($request->articles as $art) {
            $pv->articles()->create([
                'commande_article_id' => $art['commande_article_id'],
                'quantite_receptionnee' => $art['quantite_receptionnee']
            ]);
        }

        return redirect()->route('pv-receptions.show', $pv)->with('success', 'PV de réception créé avec succès.');
    }

    public function show(PvReception $pv_reception)
    {
        $pv_reception->load(['commande.articles', 'articles', 'createdBy']);
        return view('pv_receptions.show', compact('pv_reception'));
    }
}
