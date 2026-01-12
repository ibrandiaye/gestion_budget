@extends('layouts.app')

@section('title', 'Gestion des Budgets Annuels')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- List -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Exercices Budgétaires</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr
                        style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted); font-size: 0.85rem;">
                        <th style="padding: 0.75rem;">Année</th>
                        <th style="padding: 0.75rem;">Statut</th>
                        <th style="padding: 0.75rem;">Période</th>
                        <th style="padding: 0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                        <tr
                            style="border-bottom: 1px solid var(--border-color); {{ $budget->statut == 'actif' ? 'background: var(--primary-50);' : '' }}">
                            <td style="padding: 0.75rem; font-weight: 700; font-size: 1.1rem;">
                                {{ $budget->annee }}
                            </td>
                            <td style="padding: 0.75rem;">
                                @if($budget->statut == 'actif')
                                    <span
                                        style="background: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">ACTIF</span>
                                @elseif($budget->statut == 'brouillon')
                                    <span
                                        style="background: var(--warning); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">BROUILLON</span>
                                @else
                                    <span
                                        style="background: var(--slate-300); color: white; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">CLÔTURÉ</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; font-size: 0.85rem;">
                                {{ \Carbon\Carbon::parse($budget->date_debut)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($budget->date_fin)->format('d/m/Y') }}
                            </td>
                            <td style="padding: 0.75rem;">
                                @if($budget->statut != 'actif')
                                    <form action="{{ route('budgets.activate', $budget) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-primary"
                                            style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">Activer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Create -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Ouvrir un nouvel exercice</h3>
            <form action="{{ route('budgets.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Année Budgétaire</label>
                    <input type="number" name="annee" value="{{ date('Y') + 1 }}" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date Début</label>
                    <input type="date" name="date_debut" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date Fin</label>
                    <input type="date" name="date_fin" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Créer Budget</button>
            </form>
        </div>
    </div>
@endsection