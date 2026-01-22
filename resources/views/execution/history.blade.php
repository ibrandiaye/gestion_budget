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
                        <th style="padding: 1rem;">Compte</th>
                        <th style="padding: 1rem;">Détails (Articles)</th>
                        <th style="padding: 1rem; text-align: center;">Nb Art.</th>
                        <th style="padding: 1rem; text-align: right; width: 140px;">Montant</th>
                        <th style="padding: 1rem; text-align: center; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($executions as $execution)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; font-family: monospace;">
                                {{ \Carbon\Carbon::parse($execution->date)->format('d/m/Y') }}</td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $execution->ligneBudget->compte->libelle }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $execution->ligneBudget->compte->numero }} 
                                    @if(Auth::user()->role === 'admin')
                                        | {{ $execution->ligneBudget->service->nom }}
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--slate-600); font-weight: 600;">
                                @if($execution->details->count() > 0)
                                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem;">
                                        @foreach($execution->details as $detail)
                                            <li>{{ $detail->besoin->libelle }} (x{{ $detail->quantite }})</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $execution->description }}
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center; font-weight: 600;">
                                {{ $execution->details->count() ?: 1 }}
                            </td>
                            <td style="padding: 1rem; text-align: right; font-family: monospace; font-weight: 700; color: var(--primary-700);">
                                {{ number_format($execution->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('execution.facture', $execution->id) }}" target="_blank" 
                                   title="Imprimer le bon de dépense"
                                   style="display: inline-flex; align-items: center; justify-content: center; padding: 0.4rem; background: var(--slate-100); color: var(--slate-700); border-radius: 6px; text-decoration: none; transition: all 0.2s;">
                                    🖨️
                                </a>
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