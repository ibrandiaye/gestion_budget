@extends('layouts.app')

@section('title', 'Historique des Dépenses')

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div class="actions-header">
                <a href="{{ route('execution.index') }}"
                    style="display: inline-block; margin-bottom: 0.5rem; color: var(--text-muted); text-decoration: none;">
                    ← Retour à l'exécution
                </a>
                <p style="color: var(--text-muted); margin: 0;">Liste détaillée de toutes les transactions budgétaires.</p>
            </div>
            <div>
                <!-- Future: Export Button -->
            </div>
        </div>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr
                        style="border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.85rem; color: var(--text-muted);">
                        <th style="padding: 1rem;">Date</th>
                        <th style="padding: 1rem;">Service</th>
                        <th style="padding: 1rem;">Compte</th>
                        <th style="padding: 1rem;">Description</th>
                        <th style="padding: 1rem; text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($executions as $execution)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; font-family: monospace;">
                                {{ \Carbon\Carbon::parse($execution->date)->format('d/m/Y') }}</td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $execution->ligneBudget->service->nom }}</div>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $execution->ligneBudget->compte->libelle }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $execution->ligneBudget->compte->numero }}</div>
                            </td>
                            <td style="padding: 1rem; color: var(--slate-600);">
                                {{ $execution->description }}
                            </td>
                            <td style="padding: 1rem; text-align: right; font-family: monospace; font-weight: 600;">
                                {{ number_format($execution->montant, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                Aucune dépense enregistrée pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $executions->links() }}
            </div>
        </div>
    </div>
@endsection