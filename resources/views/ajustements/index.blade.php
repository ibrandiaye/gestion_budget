@extends('layouts.app')

@section('title', 'Historique des Ajustements')

@section('content')
    <div class="actions-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="font-size: 1.1rem; color: var(--text-muted);">Historique des mouvements de crédits</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Consultez tous les virements effectués entre les différentes lignes budgétaires.</p>
        </div>
        <a href="{{ route('ajustements.create') }}" class="btn-primary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            + Nouvel Ajustement
        </a>
    </div>

    @if($ajustements->isEmpty())
        <div class="card" style="padding: 4rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <h4 style="color: var(--slate-800); margin-bottom: 0.5rem;">Aucun ajustement</h4>
            <p style="color: var(--text-muted);">L'historique des virements est vide pour le moment.</p>
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--slate-50); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Date</th>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Source</th>
                        <th style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;"></th>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Destination</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Montant</th>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ajustements as $ajustement)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s;">
                            <td style="padding: 1.25rem 1rem; color: var(--slate-600); font-size: 0.9rem; white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($ajustement->date)->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding: 1.25rem 1rem;">
                                <div style="font-weight: 600; color: var(--slate-800);">{{ $ajustement->sourceLigne->compte->libelle }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $ajustement->sourceLigne->service->nom }} ({{ $ajustement->sourceLigne->compte->numero }})</div>
                            </td>
                            <td style="padding: 1.25rem 1rem; text-align: center; color: var(--primary-500); font-size: 1.25rem;">
                                ➔
                            </td>
                            <td style="padding: 1.25rem 1rem;">
                                <div style="font-weight: 600; color: var(--slate-800);">{{ $ajustement->destLigne->compte->libelle }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $ajustement->destLigne->service->nom }} ({{ $ajustement->destLigne->compte->numero }})</div>
                            </td>
                            <td style="padding: 1.25rem 1rem; text-align: right; font-weight: 700; color: var(--primary-700); font-size: 1rem;">
                                {{ number_format($ajustement->montant, 0, ',', ' ') }} <span style="font-size: 0.75rem;">FCFA</span>
                            </td>
                            <td style="padding: 1.25rem 1rem; color: var(--slate-600); font-size: 0.9rem; max-width: 250px;">
                                <span title="{{ $ajustement->motif }}">{{ Str::limit($ajustement->motif, 50) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
