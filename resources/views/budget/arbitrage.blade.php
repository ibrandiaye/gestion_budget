@extends('layouts.app')

@section('title', 'Arbitrage Budgétaire')

@section('content')
    <div class="actions-header" style="margin-bottom: 2rem;">
        <p style="color: var(--text-muted);">Validez, modifiez ou rejetez les demandes des services pour construire le
            budget final.</p>
    </div>

    <form action="{{ route('budget.valider') }}" method="POST">
        @csrf

        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--slate-50); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th
                            style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            Service</th>
                        <th
                            style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            Compte</th>
                        <th
                            style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            Demandé</th>
                        <th
                            style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            Accordé</th>
                        <th
                            style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            Décision</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($besoins as $besoin)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; font-weight: 500;">
                                {{ $besoin->service->nom }}
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $besoin->compte->libelle }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $besoin->compte->numero }}</div>
                                <div
                                    style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; font-style: italic;">
                                    "{{ Str::limit($besoin->justification, 30) }}"
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: right; font-weight: 600;">
                                {{ number_format($besoin->montant_demande, 0, ',', ' ') }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <input type="number" name="besoins[{{ $besoin->id }}][montant_accorde]"
                                    value="{{ $besoin->montant_demande }}" min="0"
                                    style="width: 120px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.25rem; text-align: right;">
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <select name="besoins[{{ $besoin->id }}][action]"
                                    style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.25rem;">
                                    <option value="valider" selected>✅ Valider</option>
                                    <option value="rejetter">❌ Rejeter</option>
                                </select>
                                <input type="hidden" name="besoins[{{ $besoin->id }}][id]" value="{{ $besoin->id }}">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                Toutes les demandes ont été traitées ou aucune demande en attente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($besoins->count() > 0)
            <div style="text-align: right;">
                <button type="submit" class="btn-primary">Enregistrer l'arbitrage</button>
            </div>
        @endif
    </form>
@endsection