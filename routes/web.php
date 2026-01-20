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
    Route::get('/comptes', [CompteController::class, 'index'])->name('comptes.index');
    Route::post('/comptes', [CompteController::class, 'store'])->name('comptes.store');
    Route::put('/comptes/{compte}', [CompteController::class, 'update'])->name('comptes.update');
    Route::delete('/comptes/{compte}', [CompteController::class, 'destroy'])->name('comptes.destroy');

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
});
