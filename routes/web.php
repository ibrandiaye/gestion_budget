<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BesoinController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\BudgetYearController;
use App\Http\Controllers\AjustementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\MandatController;
use App\Http\Controllers\FournisseurController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users (Admin Only - ideally protected by middleware)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Services (Admin Only)
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Comptes (Admin Only)
    Route::get('/comptes/template/download', [CompteController::class, 'downloadTemplate'])->name('comptes.template');
    Route::post('/comptes/import', [CompteController::class, 'import'])->name('comptes.import');
    Route::get('/comptes', [CompteController::class, 'index'])->name('comptes.index');
    Route::post('/comptes', [CompteController::class, 'store'])->name('comptes.store');
    Route::put('/comptes/{compte}', [CompteController::class, 'update'])->name('comptes.update');
    Route::delete('/comptes/{compte}', [CompteController::class, 'destroy'])->name('comptes.destroy');

    // Compte Parents (Admin Only)
    Route::resource('compte-parents', \App\Http\Controllers\CompteParentController::class)->except(['create', 'edit', 'show']);

    // Articles (Admin Only)
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

    // Fournisseurs
    Route::get('/fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
    Route::post('/fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
    Route::put('/fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
    Route::delete('/fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
    Route::post('/fournisseurs/{fournisseur}/articles', [FournisseurController::class, 'ajouterArticle'])->name('fournisseurs.ajouter-article');
    Route::delete('/fournisseurs/{fournisseur}/articles/{article}', [FournisseurController::class, 'supprimerArticle'])->name('fournisseurs.supprimer-article');
    // API endpoints for dynamic loading
    Route::get('/api/fournisseurs/compte/{compte}', [FournisseurController::class, 'pourCompte'])->name('api.fournisseurs.compte');
    Route::get('/api/fournisseurs/{fournisseur}/articles/{compte}', [FournisseurController::class, 'articlesPourCompte'])->name('api.fournisseurs.articles');


    // Annual Budgets (Admin Only)
    Route::get('/budgets', [BudgetYearController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetYearController::class, 'store'])->name('budgets.store');
    Route::post('/budgets/{budget}/activate', [BudgetYearController::class, 'activate'])->name('budgets.activate');

    // Adjustments (Virements)
    Route::get('/ajustements', [AjustementController::class, 'index'])->name('ajustements.index');
    Route::get('/ajustements/creer', [AjustementController::class, 'create'])->name('ajustements.create');
    Route::post('/ajustements', [AjustementController::class, 'store'])->name('ajustements.store');

    // Besoins
    Route::get('/besoins', [BesoinController::class, 'index'])->name('besoins.index');
    Route::get('/besoins/creer', [BesoinController::class, 'create'])->name('besoins.create');
    Route::post('/besoins', [BesoinController::class, 'store'])->name('besoins.store');

    // Budget Arbitrage (Admin only in real app, simplified here)
    Route::get('/budget/arbitrage', [BudgetController::class, 'arbitrage'])->name('budget.arbitrage');
    Route::post('/budget/arbitrage', [BudgetController::class, 'valider'])->name('budget.valider');

    // Execution
    Route::get('/execution/historique', [ExecutionController::class, 'history'])->name('execution.history');
    Route::get('/execution/facture/{execution}', [ExecutionController::class, 'invoice'])->name('execution.facture');
    Route::get('/execution', [ExecutionController::class, 'index'])->name('execution.index');
    Route::post('/execution', [ExecutionController::class, 'store'])->name('execution.store');

    // Reporting
    // Reporting
    Route::get('/reporting', [ReportingController::class, 'global'])->name('reporting.global');
    Route::get('/reporting/compte', [ReportingController::class, 'parCompte'])->name('reporting.accounts');
    // Exports
    Route::get('/export/besoins/service', [ReportingController::class, 'exportBesoinsByService'])->name('export.besoins.service');
    Route::get('/export/besoins/compte', [ReportingController::class, 'exportBesoinsByCompte'])->name('export.besoins.compte');
    Route::get('/export/budget/service', [ReportingController::class, 'exportBudgetByService'])->name('export.budget.service');
    Route::get('/export/budget/compte', [ReportingController::class, 'exportBudgetByCompte'])->name('export.budget.compte');
    Route::get('/export/mshp', [ReportingController::class, 'exportMSHP'])->name('export.mshp');
    Route::get('/export/mfb', [ReportingController::class, 'exportMFB'])->name('export.mfb');

    // Commandes
    Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes.index');
    Route::get('/commandes/creer', [CommandeController::class, 'create'])->name('commandes.create');
    Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');
    Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
    Route::post('/commandes/{commande}/soumettre', [CommandeController::class, 'soumettre'])->name('commandes.soumettre');
    Route::post('/commandes/{commande}/articles/{article}/valider', [CommandeController::class, 'validerArticle'])->name('commandes.valider-article');
    Route::post('/commandes/{commande}/articles/{article}/rejeter', [CommandeController::class, 'rejeterArticle'])->name('commandes.rejeter-article');
    Route::post('/commandes/{commande}/finaliser', [CommandeController::class, 'finaliserValidation'])->name('commandes.finaliser');
    Route::post('/commandes/{commande}/refuser', [CommandeController::class, 'refuser'])->name('commandes.refuser');
    Route::get('/commandes/{commande}/document', [CommandeController::class, 'document'])->name('commandes.document');

    // Engagements
    Route::get('/engagements', [EngagementController::class, 'index'])->name('engagements.index');
    Route::get('/engagements/{engagement}', [EngagementController::class, 'show'])->name('engagements.show');
    Route::post('/engagements/{engagement}/valider', [EngagementController::class, 'valider'])->name('engagements.valider');
    Route::post('/engagements/{engagement}/rejeter', [EngagementController::class, 'rejeter'])->name('engagements.rejeter');
    Route::get('/engagements/{engagement}/document', [EngagementController::class, 'document'])->name('engagements.document');

    // Mandats
    Route::get('/mandats', [MandatController::class, 'index'])->name('mandats.index');
    Route::get('/mandats/{mandat}', [MandatController::class, 'show'])->name('mandats.show');
    Route::post('/mandats/{mandat}/valider-directeur', [MandatController::class, 'validerDirecteur'])->name('mandats.valider-directeur');
    Route::post('/mandats/{mandat}/refuser-directeur', [MandatController::class, 'refuserDirecteur'])->name('mandats.refuser-directeur');
    Route::post('/mandats/{mandat}/valider-comptable', [MandatController::class, 'validerComptable'])->name('mandats.valider-comptable');
    Route::post('/mandats/{mandat}/refuser-comptable', [MandatController::class, 'refuserComptable'])->name('mandats.refuser-comptable');
    Route::post('/mandats/{mandat}/requisition', [MandatController::class, 'requisition'])->name('mandats.requisition');
    Route::get('/mandats/{mandat}/document', [MandatController::class, 'document'])->name('mandats.document');
});
