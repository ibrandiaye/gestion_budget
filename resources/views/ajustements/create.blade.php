@extends('layouts.app')

@section('title', 'Virements de Crédit')

@section('content')
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h3 style="margin-bottom: 1.5rem;">Nouveau Virement</h3>

        @if(session('errors'))
            <div
                style="background: var(--danger-light); color: var(--danger); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                {{ session('errors')->first() }}
            </div>
        @endif

        <form action="{{ route('ajustements.store') }}" method="POST">
            @csrf

            <!-- Source Line -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Ligne Budget Source (À
                    débiter)</label>
                <select name="source_ligne_id" id="sourceLine" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                    onchange="updateMaxAmount()">
                    <option value="">Sélectionner une ligne source...</option>
                    @foreach($lignes as $ligne)
                        <option value="{{ $ligne->id }}" data-solde="{{ $ligne->solde_disponible }}">
                            {{ $ligne->service->nom }} - {{ $ligne->compte->libelle }} (Disp:
                            {{ number_format($ligne->solde_disponible, 0, ',', ' ') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dest Line -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Ligne Budget Destination (À
                    créditer)</label>
                <select name="dest_ligne_id" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <option value="">Sélectionner une ligne destination...</option>
                    @foreach($lignes as $ligne)
                        <option value="{{ $ligne->id }}">
                            {{ $ligne->service->nom }} - {{ $ligne->compte->libelle }} (Actuel:
                            {{ number_format($ligne->montant_alloue, 0, ',', ' ') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Amount -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Montant à transférer</label>
                <input type="number" name="montant" id="amountInput" required min="1"
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                <p id="maxAmountHint" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;"></p>
            </div>

            <!-- Motif -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Motif du virement</label>
                <textarea name="motif" required rows="2"
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%;">Effectuer le Virement</button>
        </form>
    </div>

    <script>
        function updateMaxAmount() {
            const select = document.getElementById('sourceLine');
            const selectedOption = select.options[select.selectedIndex];
            const max = selectedOption.getAttribute('data-solde');

            const input = document.getElementById('amountInput');

            if (max) {
                input.max = max;
                document.getElementById('maxAmountHint').textContent = 'Maximum disponible: ' + new Intl.NumberFormat().format(max) + ' FCFA';
            } else {
                input.removeAttribute('max');
                document.getElementById('maxAmountHint').textContent = '';
            }
        }
    </script>
@endsection