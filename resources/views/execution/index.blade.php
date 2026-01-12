@extends('layouts.app')

@section('title', 'Exécution Budgétaire')

@section('content')
    <div class="actions-header" style="margin-bottom: 2rem;">
        <p style="color: var(--text-muted);">Suivi des crédits, engagements et mandatements.</p>
    </div>

    <!-- Grid of Budget Lines -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
        @forelse($lignes as $ligne)
            <div class="card" style="position: relative;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div>
                        <h4 style="margin: 0; font-size: 1rem; color: var(--slate-900);">{{ $ligne->compte->libelle }}</h4>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                            {{ $ligne->service->nom }} | {{ $ligne->compte->numero }}
                        </div>
                    </div>
                    <div
                        style="background: var(--primary-50); color: var(--primary-700); padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600;">
                        {{ number_format($ligne->solde_disponible, 0, ',', ' ') }} Disp.
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="margin-bottom: 1rem;">
                    <div
                        style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                        <span>Consommé: {{ number_format($ligne->montant_engage, 0, ',', ' ') }}</span>
                        <span>Total: {{ number_format($ligne->montant_alloue, 0, ',', ' ') }}</span>
                    </div>
                    <div style="height: 6px; background: var(--slate-100); border-radius: 3px; overflow: hidden;">
                        @php
                            $percent = $ligne->montant_alloue > 0 ? ($ligne->montant_engage / $ligne->montant_alloue) * 100 : 0;
                            $color = $percent > 90 ? 'var(--danger)' : ($percent > 70 ? 'var(--warning)' : 'var(--primary-500)');
                        @endphp
                        <div style="width: {{ $percent }}%; height: 100%; background: {{ $color }};"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; text-align: right;">
                    <button
                        onclick="openModal('{{ $ligne->id }}', '{{ $ligne->compte->libelle }}', {{ $ligne->solde_disponible }})"
                        class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;" {{ $ligne->solde_disponible <= 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                        Nouvelle Dépense
                    </button>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
                Aucune ligne budgétaire disponible. Attendez l'arbitrage.
            </div>
        @endforelse
    </div>

    <!-- Simple Modal for Expense -->
    <div id="expenseModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Engager une dépense</h3>
            <p id="modalLineName" style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.9rem;"></p>

            <form action="{{ route('execution.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ligne_budget_id" id="modalLineId">

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Montant (Max: <span
                            id="modalMaxAmount"></span>)</label>
                    <input type="number" name="montant" id="amountInput" required min="1"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Description / Motif</label>
                    <textarea name="description" required rows="3"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" onclick="closeModal()"
                        style="padding: 0.75rem 1.5rem; background: white; border: 1px solid var(--border-color); border-radius: 0.5rem; cursor: pointer;">Annuler</button>
                    <button type="submit" class="btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, name, max) {
            document.getElementById('expenseModal').style.display = 'flex';
            document.getElementById('modalLineId').value = id;
            document.getElementById('modalLineName').textContent = 'Sur la ligne: ' + name;
            document.getElementById('modalMaxAmount').textContent = new Intl.NumberFormat().format(max);
            document.getElementById('amountInput').max = max;
        }

        function closeModal() {
            document.getElementById('expenseModal').style.display = 'none';
        }
    </script>
@endsection