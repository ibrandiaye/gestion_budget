@extends('layouts.app')

@section('title', 'Arbitrage : ' . $compte->numero)

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ route('budget.arbitrage') }}" style="text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                ← Retour à la liste des comptes
            </a>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1rem;">
                <!-- Dotation Card -->
                <div class="card" style="padding: 1.5rem; border-left: 4px solid var(--primary);">
                    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Dotation du Compte</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--slate-900);">{{ number_format($compte->dotation_initiale, 0, ',', ' ') }} <small>FCFA</small></div>
                </div>

                <!-- Estimation Card -->
                <div class="card" style="padding: 1.5rem; border-left: 4px solid var(--warning);">
                    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Expression des Besoins</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--slate-900);">{{ number_format($totalEstimation, 0, ',', ' ') }} <small>FCFA</small></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Basé sur le prix réf: {{ number_format($compte->prix_unitaire_ref, 0, ',', ' ') }} FCFA</div>
                </div>

                <!-- Gap Card -->
                @php $gap = $compte->dotation_initiale - $totalEstimation; @endphp
                <div class="card" style="padding: 1.5rem; border-left: 4px solid {{ $gap >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Écart (Marge)</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: {{ $gap >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ number_format(abs($gap), 0, ',', ' ') }} <small>FCFA</small>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $gap >= 0 ? 'Surplus disponible' : 'Dépassement de dotation' }}</div>
                </div>
            </div>

            <h3 style="margin: 0; color: var(--slate-800);">
                Arbitrage pour : <span style="color: var(--primary-600);">{{ $compte->numero }} - {{ $compte->libelle }}</span>
            </h3>
        </div>
        <div style="background: var(--primary-50); color: var(--primary-700); padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600;">
            {{ $besoins->count() }} demande(s) en attente
        </div>
    </div>

   
   
    <form action="{{ route('budget.valider') }}" method="POST">
        @csrf
        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem; border: 1px solid var(--border-color);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--slate-50); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; width: 20%;">Service</th>
                        <th style="padding: 1rem; text-align: left; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Détails du Besoin</th>
                        <th style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; width: 10%;">Qté</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; width: 15%;">Prix Unitaire</th>
                        <th style="padding: 1rem; text-align: right; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; width: 15%;">Total</th>
                        <th style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($besoins as $besoin)
                        <tr style="border-bottom: 1px solid var(--border-color);" class="besoin-row" data-id="{{ $besoin->id }}">
                            <td style="padding: 1.25rem 1rem; font-weight: 600; color: var(--slate-700);">
                                {{ $besoin->service->nom }}
                            </td>
                            <td style="padding: 1.25rem 1rem;">
                                <div style="font-weight: 700; color: var(--slate-800); margin-bottom: 0.25rem;">{{ $besoin->libelle }}</div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0.5rem;">
                                    {{ $besoin->justification }}
                                </div>
                                @if($besoin->statut === 'valide')
                                    <span style="font-size: 0.7rem; background: var(--success-100); color: var(--success-700); padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">DÉJÀ VALIDÉ</span>
                                @elseif($besoin->statut === 'rejete')
                                    <span style="font-size: 0.7rem; background: var(--danger-100); color: var(--danger-700); padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">DÉJÀ REJETÉ</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <input type="number" name="besoins[{{ $besoin->id }}][quantite]" 
                                    value="{{ $besoin->quantite }}" min="0" 
                                    class="input-quantite"
                                    style="width: 70px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; text-align: center; font-family: inherit; font-weight: 600;">
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                    <input type="number" name="besoins[{{ $besoin->id }}][prix_unitaire]" 
                                        value="{{ $besoin->prix_unitaire ?? ($compte->prix_unitaire_ref ?? 0) }}" min="0" step="100"
                                        class="input-prix"
                                        style="width: 120px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; text-align: right; font-family: inherit; font-weight: 600;">
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">FCFA</span>
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: right; font-weight: 800; color: var(--primary-700); font-size: 1rem;">
                                <span class="total-ligne">0</span>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <select name="besoins[{{ $besoin->id }}][action]" 
                                    class="select-action"
                                    style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; font-family: inherit; cursor: pointer;">
                                    <option value="valider" {{ $besoin->statut !== 'rejete' ? 'selected' : '' }}>✅ Valider</option>
                                    <option value="rejetter" {{ $besoin->statut === 'rejete' ? 'selected' : '' }}>❌ Rejeter</option>
                                </select>
                                <input type="hidden" name="besoins[{{ $besoin->id }}][id]" value="{{ $besoin->id }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: var(--slate-50);">
                    <tr>
                        <td colspan="4" style="padding: 1.5rem 1rem; text-align: right; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; color: var(--slate-600);">
                            Total accordé pour ce compte :
                        </td>
                        <td style="padding: 1.5rem 1rem; text-align: right; font-weight: 900; font-size: 1.25rem; color: var(--primary-700);">
                            <span id="grand-total">0</span> <span style="font-size: 0.9rem;">FCFA</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('budget.arbitrage') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;">Annuler</a>
            <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; border-radius: 8px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);">
                Valider cet arbitrage
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const grandTotalDisplay = document.getElementById('grand-total');

            function formatNumber(num) {
                return new Intl.NumberFormat('fr-FR').format(num);
            }

            function calculate() {
                let grandTotal = 0;
                const rows = document.querySelectorAll('.besoin-row');
                const dotation = {{ $compte->dotation_initiale }};
                
                rows.forEach(row => {
                    const qte = parseFloat(row.querySelector('.input-quantite').value) || 0;
                    const prix = parseFloat(row.querySelector('.input-prix').value) || 0;
                    const action = row.querySelector('.select-action').value;
                    
                    let totalLigne = 0;
                    if (action === 'valider') {
                        totalLigne = qte * prix;
                        row.style.opacity = '1';
                        row.style.background = 'transparent';
                    } else {
                        row.style.opacity = '0.5';
                        row.style.background = 'var(--slate-50)';
                    }
                    
                    row.querySelector('.total-ligne').textContent = formatNumber(totalLigne);
                    grandTotal += totalLigne;
                });
                
                grandTotalDisplay.textContent = formatNumber(grandTotal);

                if (grandTotal > dotation && dotation > 0) {
                    const diff = grandTotal - dotation;
                    grandTotalDisplay.parentElement.style.color = 'var(--danger)';
                    grandTotalDisplay.parentElement.style.background = '#fee2e2';
                    grandTotalDisplay.parentElement.style.padding = '0.5rem';
                    grandTotalDisplay.parentElement.style.borderRadius = '4px';
                    
                    // Show error message if not exists
                    let errorMsg = document.getElementById('budget-error-msg');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.id = 'budget-error-msg';
                        errorMsg.style.color = 'var(--danger)';
                        errorMsg.style.fontWeight = 'bold';
                        errorMsg.style.marginTop = '1rem';
                        errorMsg.style.textAlign = 'right';
                        document.querySelector('tfoot td[colspan="4"]').appendChild(errorMsg);
                    }
                    errorMsg.innerHTML = `⚠️ Dépassement de : ${formatNumber(diff)} FCFA`;

                    // Disable Submit Button
                    const submitBtn = document.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                    submitBtn.title = "Impossible de valider : le budget est dépassé.";

                } else {
                    grandTotalDisplay.parentElement.style.color = 'var(--primary-700)';
                    grandTotalDisplay.parentElement.style.background = 'transparent';
                    
                    const errorMsg = document.getElementById('budget-error-msg');
                    if (errorMsg) errorMsg.remove();

                    // Enable Submit Button
                    const submitBtn = document.querySelector('button[type="submit"]');
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    submitBtn.title = "";
                }
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('input-quantite') || 
                    e.target.classList.contains('input-prix') || 
                    e.target.classList.contains('select-action')) {
                    calculate();
                }
            });

            calculate();
        });
    </script>
@endsection
