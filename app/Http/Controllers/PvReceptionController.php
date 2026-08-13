<?php

namespace App\Http\Controllers;

use App\Models\PvReception;
use App\Models\PvReceptionArticle;
use App\Models\Commande;
use App\Models\Signataire;
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
        $commande    = Commande::with('articles')->findOrFail($commande_id);
        $signataires = Signataire::where('actif', true)->orderBy('nom')->get();

        // Quantités déjà réceptionnées par article (cumul de tous les PVs précédents)
        $dejaRecus = PvReceptionArticle::whereHas('pvReception', fn($q) => $q->where('commande_id', $commande_id))
            ->selectRaw('commande_article_id, SUM(quantite_receptionnee) as total_recu')
            ->groupBy('commande_article_id')
            ->pluck('total_recu', 'commande_article_id');

        return view('pv_receptions.create', compact('commande', 'signataires', 'dejaRecus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id'                      => 'required|exists:commandes,id',
            'date_reception'                   => 'required|date',
            'tva'                              => 'required|numeric',
            'articles'                         => 'required|array',
            'articles.*.commande_article_id'   => 'required|exists:commande_articles,id',
            'articles.*.quantite_receptionnee' => 'required|integer|min:0',
            'signataire_ids'                   => 'nullable|array',
            'signataire_ids.*'                 => 'exists:signataires,id',
        ]);

        $commande = Commande::with('articles')->findOrFail($request->commande_id);

        // Cumul déjà réceptionné par article sur tous les PVs existants
        $dejaRecus = PvReceptionArticle::whereHas('pvReception', fn($q) => $q->where('commande_id', $commande->id))
            ->selectRaw('commande_article_id, SUM(quantite_receptionnee) as total_recu')
            ->groupBy('commande_article_id')
            ->pluck('total_recu', 'commande_article_id');

        // Validation cumulative
        $errors = [];
        foreach ($request->articles as $art) {
            $c_art    = $commande->articles->where('id', $art['commande_article_id'])->first();
            if (!$c_art) continue;

            $dejaRecu = (int) ($dejaRecus[$c_art->id] ?? 0);
            $restant  = $c_art->quantite - $dejaRecu;
            $saisie   = (int) $art['quantite_receptionnee'];

            if ($restant <= 0) {
                $errors[] = "\"" . $c_art->designation . "\" : déjà entièrement réceptionné ({$dejaRecu}/{$c_art->quantite}).";
            } elseif ($saisie > $restant) {
                $errors[] = "\"" . $c_art->designation . "\" : quantité saisie ({$saisie}) dépasse le restant à recevoir ({$restant}).";
            }
        }

        if (!empty($errors)) {
            return back()
                ->withErrors(['reception' => $errors])
                ->withInput();
        }

        // Calcul des totaux
        $montant_total = 0;
        foreach ($request->articles as $art) {
            $c_art = $commande->articles->where('id', $art['commande_article_id'])->first();
            if ($c_art) {
                $montant_total += $c_art->prix_unitaire * $art['quantite_receptionnee'];
            }
        }

        $montant_ttc = $montant_total + ($montant_total * $request->tva / 100);

        // Pièces justificatives
        $pieces = [];
        if ($request->pieces_justificatives_texte) {
            $pieces = array_map('trim', explode(',', $request->pieces_justificatives_texte));
        }

        $pv = PvReception::create([
            'commande_id'           => $commande->id,
            'date_reception'        => $request->date_reception,
            'montant_total'         => $montant_total,
            'tva'                   => $request->tva,
            'montant_ttc'           => $montant_ttc,
            'created_by'            => Auth::id(),
            'signataires'           => [],
            'pieces_justificatives' => $pieces,
        ]);

        if ($request->signataire_ids) {
            $pv->signatairesRelation()->attach($request->signataire_ids);
        }

        foreach ($request->articles as $art) {
            $pv->articles()->create([
                'commande_article_id'   => $art['commande_article_id'],
                'quantite_receptionnee' => $art['quantite_receptionnee'],
            ]);
        }

        return redirect()->route('pv-receptions.show', $pv)->with('success', 'PV de réception créé avec succès.');
    }

    public function show(PvReception $pv_reception)
    {
        $pv_reception->load([
            'commande.articles',
            'commande.compte',
            'commande.service',
            'articles.commandeArticle',
            'signatairesRelation',
            'createdBy',
        ]);

        return view('pv_receptions.show', compact('pv_reception'));
    }
}
