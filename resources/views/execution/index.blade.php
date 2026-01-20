@extends('layouts.app')

@section('title', 'Exécution Budgétaire')

@section('content')
    <div class="actions-header"
        style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <p style="color: var(--text-muted); margin: 0;">Suivi des crédits, engagements et mandatements.</p>
        <a href="{{ route('execution.history') }}" class="btn-secondary" style="text-decoration: none;">📜 Historique des
            dépenses</a>
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
                        onclick="openModal({{ $ligne->id }}, '{{ $ligne->compte->libelle }}', {{ $ligne->solde_disponible }}, {{ $ligne->compte->besoins->toJson() }})"
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
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: flex-start; padding: 2rem 1rem; overflow-y: auto;">
        <div class="card" style="width: 100%; max-width: 650px; padding: 2rem; margin-top: 2rem; margin-bottom: 2rem; position: relative;">
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

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <label style="font-weight: 700; color: var(--slate-800);">Articles à engager</label>
                        <button type="button" onclick="addItem()" class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">+ Ajouter un article</button>
                    </div>
                    
                    <div id="itemsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- Rows will be added here -->
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem; background: var(--primary-50); padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--primary-100);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; color: var(--primary-800);">MONTANT TOTAL À ENGAGER</span>
                        <div id="totalDisplay" style="font-size: 1.5rem; font-weight: 800; color: var(--primary-700);">0 FCFA</div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Justification / Référence</label>
                    <textarea name="description" id="descriptionInput" rows="2" placeholder="Ex: Facture N°123..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" onclick="closeModal()"
                        style="padding: 0.75rem 1.5rem; background: white; border: 1px solid var(--border-color); border-radius: 0.5rem; cursor: pointer;">Annuler</button>
                    <button type="submit" class="btn-primary">Valider l'engagement</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentNeeds = [];
        let itemCount = 0;

        function openModal(id, name, max, needs) {
            currentNeeds = needs;
            const modal = document.getElementById('expenseModal');
            const container = document.getElementById('itemsContainer');
            
            // Reset
            container.innerHTML = '';
            itemCount = 0;
            
            document.getElementById('modalLineId').value = id;
            document.getElementById('modalLineName').textContent = 'Compte: ' + name;
            modal.style.display = 'flex';
            
            // Add first item automatically
            addItem();
        }

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const index = itemCount++;
            
            const div = document.createElement('div');
            div.className = 'item-row';
            div.style.display = 'grid';
            div.style.gridTemplateColumns = '2fr 1fr auto';
            div.style.gap = '0.75rem';
            div.style.alignItems = 'end';
            div.style.padding = '1rem';
            div.style.background = 'var(--slate-50)';
            div.style.borderRadius = '0.5rem';
            div.id = 'item-row-' + index;

            let options = '<option value="">Choisir article...</option>';
            currentNeeds.forEach(n => {
                options += `<option value="${n.id}" data-price="${n.prix_unitaire}">${n.libelle} (${new Intl.NumberFormat().format(n.prix_unitaire)} FCFA)</option>`;
            });

            div.innerHTML = `
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Article</label>
                    <select name="items[${index}][besoin_id]" required onchange="calculateTotal()" class="besoin-select"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem;">
                        ${options}
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Quantité</label>
                    <input type="number" name="items[${index}][quantite]" required min="1" value="1" oninput="calculateTotal()" class="qte-input"
                        style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem; text-align: center;">
                </div>
                <button type="button" onclick="removeItem(${index})" 
                    style="background: #fee2e2; color: #b91c1c; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    🗑️
                </button>
            `;

            container.appendChild(div);
            calculateTotal();
        }

        function removeItem(index) {
            const row = document.getElementById('item-row-' + index);
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotal();
            }
        }

        function calculateTotal() {
            let grandTotal = 0;
            const rows = document.querySelectorAll('.item-row');
            
            rows.forEach(row => {
                const select = row.querySelector('.besoin-select');
                const qte = row.querySelector('.qte-input').value || 0;
                const option = select.options[select.selectedIndex];
                
                if (option && option.value) {
                    const price = parseFloat(option.getAttribute('data-price')) || 0;
                    grandTotal += price * qte;
                }
            });
            
            document.getElementById('totalDisplay').textContent = new Intl.NumberFormat().format(grandTotal) + ' FCFA';
        }

        function closeModal() {
            document.getElementById('expenseModal').style.display = 'none';
        }
    </script>
@endsection