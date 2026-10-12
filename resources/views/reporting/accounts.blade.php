@extends('layouts.app')

@section('title', 'Budget par Compte')

@section('content')
<style>
    .table-budget {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        border: 1.5px solid #1e293b;
        background: white;
    }
    .table-budget th, .table-budget td {
        border: 1px solid #334155;
    }
    .table-budget th {
        color: #0f172a;
    }
    .btn-action-rep {
        text-decoration: none;
        padding: 0.45rem 0.85rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
        font-family: inherit;
        border: 1px solid transparent;
    }
    .btn-action-rep:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    @media print {
        .sidebar, .navbar, .no-print, form, input, button, select {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        body {
            background: white !important;
            font-size: 8pt !important;
        }
        .table-budget {
            font-size: 7.5pt !important;
        }
        @page {
            size: landscape;
            margin: 1cm;
        }
    }
</style>

<div class="card" style="padding: 1.5rem;">
    <!-- En-tête et actions -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <!-- Titre en ROUGE exactement comme sur le modèle -->
            <h2 style="color: #dc2626; font-size: 1.65rem; font-weight: 800; margin: 0 0 0.35rem 0; letter-spacing: -0.02em;">
                Budget par compte
            </h2>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.88rem;">
                Consolidation et suivi de l'exécution budgétaire par compte
                @if($budget)
                    — Exercice <strong>{{ $budget->annee }}</strong> ({{ ucfirst($budget->statut) }})
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if(isset($budgets) && $budgets->count() > 1)
                <form action="{{ route('reporting.accounts') }}" method="GET" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">Exercice :</label>
                    <select name="budget_id" onchange="this.form.submit()" style="padding: 0.45rem 0.65rem; border-radius: 0.4rem; border: 1px solid var(--border-color); font-size: 0.82rem; background: white; font-weight: 600; cursor: pointer;">
                        @foreach($budgets as $b)
                            <option value="{{ $b->id }}" {{ ($budget && $budget->id == $b->id) ? 'selected' : '' }}>
                                {{ $b->annee }} ({{ ucfirst($b->statut) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('export.budget.compte', ['budget_id' => $budget?->id]) }}" class="btn-action-rep" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;" title="Télécharger sous Excel / CSV">
                📥 Exporter CSV
            </a>

            <button onclick="window.print()" class="btn-action-rep" style="background: #f1f5f9; color: #334155; border-color: #cbd5e1; cursor: pointer;" title="Imprimer le tableau">
                🖨️ Imprimer
            </button>

            <a href="{{ route('reporting.mshp') }}" class="btn-action-rep" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                📊 Rapport MSHP
            </a>

            <a href="{{ route('reporting.mfb') }}" class="btn-action-rep" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                📊 Rapport MFB
            </a>

            <a href="{{ route('reporting.global') }}" class="btn-action-rep" style="background: white; color: var(--text-color); border-color: var(--border-color);">
                Vue Globale
            </a>
        </div>
    </div>

    <!-- Barre de recherche rapide -->
    <div class="no-print" style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="position: relative; width: 340px; max-width: 100%;">
            <input type="text" id="filtreCompte" placeholder="🔍 Filtrer par numéro ou libellé..." 
                style="width: 100%; padding: 0.5rem 0.85rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 0.85rem; box-sizing: border-box;"
                onkeyup="filtrerTableau()">
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Montants exprimés en <strong>FCFA</strong>
        </div>
    </div>

    <!-- Tableau Budget par compte -->
    <div style="overflow-x: auto;">
        <table class="table-budget">
            <thead>
                <!-- Ligne d'en-tête principale (Groupes) -->
                <tr style="background: #f8fafc;">
                    <th rowspan="2" style="padding: 0.75rem 0.65rem; text-align: left; vertical-align: middle; font-weight: 800; min-width: 260px; background: #f8fafc;">
                        Compte et libelle
                    </th>
                    <th colspan="4" style="padding: 0.6rem 0.5rem; text-align: center; font-weight: 800; background: #f1f5f9; letter-spacing: 0.04em;">
                        DOTATION
                    </th>
                    <th colspan="2" style="padding: 0.6rem 0.5rem; text-align: center; font-weight: 800; background: #e2e8f0; letter-spacing: 0.04em;">
                        COMMANDE
                    </th>
                    <th colspan="2" style="padding: 0.6rem 0.5rem; text-align: center; font-weight: 800; background: #f1f5f9; letter-spacing: 0.04em;">
                        ENGAGEMENT
                    </th>
                </tr>
                <!-- Ligne de sous-colonnes -->
                <tr style="background: #f8fafc; font-size: 0.8rem;">
                    <!-- DOTATION -->
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 100px;">Initiale</th>
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 95px;">Virement</th>
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 110px;">Réaménagement</th>
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 800; width: 105px; background: #f1f5f9;">Total</th>
                    <!-- COMMANDE -->
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 100px;">Total</th>
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 105px;">Disponible</th>
                    <!-- ENGAGEMENT -->
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 100px;">Total</th>
                    <th style="padding: 0.5rem 0.4rem; text-align: right; font-weight: 700; width: 105px;">Disponible</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($statsComptes as $stat)
                    <tr class="compte-row" style="background: white; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <!-- Compte et libelle -->
                        <td style="padding: 0.65rem 0.75rem; text-align: left;">
                            <span style="font-family: monospace; font-weight: 700; color: var(--primary-700, #0369a1); font-size: 0.9rem;">{{ $stat->compte->numero }}</span>
                            <span style="color: #64748b; margin: 0 0.2rem;">-</span>
                            <span style="font-weight: 500; color: #1e293b;">{{ $stat->compte->libelle }}</span>
                        </td>

                        <!-- DOTATION : Initiale -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                            {{ number_format($stat->dotation_initiale, 0, ',', ' ') }}
                        </td>

                        <!-- DOTATION : Virement -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap; color: {{ $stat->virement > 0 ? '#166534' : ($stat->virement < 0 ? '#dc2626' : '#64748b') }};">
                            {{ $stat->virement > 0 ? '+' : '' }}{{ number_format($stat->virement, 0, ',', ' ') }}
                        </td>

                        <!-- DOTATION : Réaménagement -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap; color: #64748b;">
                            {{ number_format($stat->reamenagement, 0, ',', ' ') }}
                        </td>

                        <!-- DOTATION : Total -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; font-weight: 800; background: #f8fafc; white-space: nowrap; color: #0f172a;">
                            {{ number_format($stat->dotation_totale, 0, ',', ' ') }}
                        </td>

                        <!-- COMMANDE : Total -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap; color: #334155;">
                            {{ number_format($stat->total_commandes, 0, ',', ' ') }}
                        </td>

                        <!-- COMMANDE : Disponible -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; font-weight: 700; white-space: nowrap; color: {{ $stat->disponible_commande < 0 ? '#dc2626' : '#166534' }};">
                            {{ number_format($stat->disponible_commande, 0, ',', ' ') }}
                        </td>

                        <!-- ENGAGEMENT : Total -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap; color: #334155;">
                            {{ number_format($stat->total_engage, 0, ',', ' ') }}
                        </td>

                        <!-- ENGAGEMENT : Disponible -->
                        <td style="padding: 0.65rem 0.5rem; text-align: right; font-family: monospace; font-weight: 700; white-space: nowrap; color: {{ $stat->disponible_engage < 0 ? '#dc2626' : '#166534' }};">
                            {{ number_format($stat->disponible_engage, 0, ',', ' ') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            Aucun compte actif trouvé pour cet exercice budgétaire.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <!-- Ligne de total général -->
                <tr style="background: #e2e8f0; font-weight: 800; border-top: 2px solid #0f172a;">
                    <td style="padding: 0.75rem 0.65rem; text-align: left; font-size: 0.85rem; color: #0f172a;">
                        TOTAL GÉNÉRAL
                    </td>
                    <!-- Total Initiale -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                        {{ number_format($statsComptes->sum('dotation_initiale'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Virement -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                        {{ number_format($statsComptes->sum('virement'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Réaménagement -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                        {{ number_format($statsComptes->sum('reamenagement'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Dotation Totale -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; font-weight: 900; background: #cbd5e1; white-space: nowrap; color: #0f172a;">
                        {{ number_format($statsComptes->sum('dotation_totale'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Commande -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                        {{ number_format($statsComptes->sum('total_commandes'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Disponible Commande -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; font-weight: 800; white-space: nowrap; color: {{ $statsComptes->sum('disponible_commande') < 0 ? '#dc2626' : '#166534' }};">
                        {{ number_format($statsComptes->sum('disponible_commande'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Engagement -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; white-space: nowrap;">
                        {{ number_format($statsComptes->sum('total_engage'), 0, ',', ' ') }}
                    </td>
                    <!-- Total Disponible Engagement -->
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: monospace; font-weight: 800; white-space: nowrap; color: {{ $statsComptes->sum('disponible_engage') < 0 ? '#dc2626' : '#166534' }};">
                        {{ number_format($statsComptes->sum('disponible_engage'), 0, ',', ' ') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    function filtrerTableau() {
        const input = document.getElementById('filtreCompte').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.compte-row');
        rows.forEach(row => {
            const text = row.querySelector('td').innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>
@endsection