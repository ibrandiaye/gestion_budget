<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeArticle;
use App\Models\Compte;
use App\Models\Budget;
use App\Models\Engagement;
use App\Models\LigneBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index()
    {
        $query = Commande::with(['compte', 'service', 'articles'])
            ->orderByDesc('created_at');

        if (Auth::user()->role !== 'admin') {
            $query->where('service_id', Auth::user()->service_id);
        }

        $commandes = $query->paginate(20);

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $comptes = Compte::with(['articles' => fn($q) => $q->where('actif', true)])
            ->where('etat', 'actif')
            ->whereNotNull('parent_id')
            ->get();
        
        $budget = Budget::whereIn('statut', ['brouillon', 'actif'])->latest('annee')->first();

        return view('commandes.create', compact('comptes', 'budget'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'date' => 'required|date',
            'fournisseur' => 'nullable|string|max:191',
            'reference' => 'nullable|string|max:191',
            'caisse' => 'nullable|string|max:100',
            'articles' => 'required|array|min:1',
            'articles.*.designation' => 'required|string|max:191',
            'articles.*.conditionnement' => 'nullable|string|max:100',
            'articles.*.quantite' => 'required|integer|min:1',
            'articles.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $budget = Budget::whereIn('statut', ['brouillon', 'actif'])->latest('annee')->first();

        if (!$budget) {
            return back()->withErrors(['budget' => 'Aucun exercice budgétaire actif.']);
        }

        // Calculate total amount
        $montantTotal = 0;
        foreach ($request->articles as $art) {
            $montantTotal += $art['quantite'] * $art['prix_unitaire'];
        }

        // Check budget availability
        $ligneBudget = LigneBudget::where('budget_id', $budget->id)
            ->where('compte_id', $request->compte_id)
            ->where('service_id', Auth::user()->service_id)
            ->first();

        if (!$ligneBudget) {
            return back()->withErrors(['budget' => 'Aucune ligne budgétaire trouvée pour ce compte et service.']);
        }

        $disponible = $ligneBudget->montant_alloue - $ligneBudget->montant_engage;

        if ($montantTotal > $disponible) {
            return back()->withErrors([
                'budget' => "Montant insuffisant. Disponible: " . number_format($disponible, 0, ',', ' ') . " FCFA. Demandé: " . number_format($montantTotal, 0, ',', ' ') . " FCFA."
            ]);
        }

        DB::transaction(function () use ($request, $budget, $montantTotal) {
            $commande = Commande::create([
                'numero' => Commande::generateNumero(),
                'date' => $request->date,
                'compte_id' => $request->compte_id,
                'service_id' => Auth::user()->service_id,
                'budget_id' => $budget->id,
                'fournisseur' => $request->fournisseur,
                'reference' => $request->reference,
                'caisse' => $request->caisse ?? 'Caisse avance',
                'montant_tva' => 0,
                'montant_ttc' => $montantTotal,
                'statut' => 'brouillon',
            ]);

            foreach ($request->articles as $art) {
                CommandeArticle::create([
                    'commande_id' => $commande->id,
                    'article_id' => $art['article_id'] ?? null,
                    'designation' => $art['designation'],
                    'conditionnement' => $art['conditionnement'] ?? null,
                    'quantite' => $art['quantite'],
                    'prix_unitaire' => $art['prix_unitaire'],
                    'montant_ht' => $art['quantite'] * $art['prix_unitaire'],
                    'valide' => false,
                ]);
            }
        });

        return redirect()->route('commandes.index')->with('success', 'Bon de commande créé avec succès.');
    }


    public function show(Commande $commande)
    {
        $commande->load(['compte', 'service', 'articles', 'engagement']);
        return view('commandes.show', compact('commande'));
    }

    public function soumettre(Commande $commande)
    {
        if ($commande->statut !== 'brouillon') {
            return back()->withErrors(['statut' => 'Cette commande ne peut pas être soumise.']);
        }

        $commande->update(['statut' => 'soumis']);

        return back()->with('success', 'Bon de commande soumis pour validation.');
    }

    public function validerArticle(Request $request, Commande $commande, CommandeArticle $article)
    {
        if ($commande->statut !== 'soumis') {
            return back()->withErrors(['statut' => 'La commande doit être soumise pour valider les articles.']);
        }

        $article->update(['valide' => true]);

        return back()->with('success', 'Article validé.');
    }

    public function rejeterArticle(Request $request, Commande $commande, CommandeArticle $article)
    {
        if ($commande->statut !== 'soumis') {
            return back()->withErrors(['statut' => 'La commande doit être soumise pour rejeter les articles.']);
        }

        $article->update(['valide' => false]);

        return back()->with('success', 'Article rejeté.');
    }

    public function finaliserValidation(Commande $commande)
    {
        if ($commande->statut !== 'soumis') {
            return back()->withErrors(['statut' => 'La commande doit être soumise.']);
        }

        $articlesValides = $commande->articlesValides()->count();

        if ($articlesValides === 0) {
            $commande->update([
                'statut' => 'rejete',
                'valide_par' => Auth::id(),
                'valide_at' => now(),
            ]);
            return back()->with('success', 'Commande rejetée (aucun article validé).');
        }

        $commande->update([
            'statut' => 'valide',
            'valide_par' => Auth::id(),
            'valide_at' => now(),
        ]);

        // Create engagement with validated articles
        $montantValide = $commande->articlesValides()->sum('montant_ht');

        Engagement::create([
            'numero' => Engagement::generateNumero(),
            'commande_id' => $commande->id,
            'date' => now()->toDateString(),
            'objet' => $commande->articles->first()->designation ?? 'Commande ' . $commande->numero,
            'nature' => $commande->compte->type ?? 'Fonctionnement',
            'montant' => $montantValide,
            'nom_fournisseur' => $commande->fournisseur ?? 'Non spécifié',
            'statut' => 'cree',
        ]);

        return redirect()->route('engagements.index')->with('success', 'Commande validée et engagement créé.');
    }

    public function document(Commande $commande)
    {
        $commande->load(['compte', 'service', 'articles']);
        
        // Get budget line for this compte and service
        $ligneBudget = LigneBudget::where('budget_id', $commande->budget_id)
            ->where('compte_id', $commande->compte_id)
            ->where('service_id', $commande->service_id)
            ->first();
        
        $dotation = $ligneBudget->montant_alloue ?? 0;
        $creditsEngages = $ligneBudget->montant_engage ?? 0;
        $disponible = $dotation - $creditsEngages;
        
        // Montant total des articles validés
        $montantTotal = $commande->articles->where('valide', true)->sum('montant_ht');
        $montantEnLettres = \App\Helpers\NumberToWords::convert($montantTotal);
        
        return view('commandes.document', compact('commande', 'dotation', 'creditsEngages', 'disponible', 'montantEnLettres'));
    }
}
