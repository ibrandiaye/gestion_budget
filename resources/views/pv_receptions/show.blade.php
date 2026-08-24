@extends('layouts.app')

@php
    $isPrintable = ($pv_reception->montant_ttc > 300000);
    $selectedIds = $pv_reception->signatairesRelation->pluck('id')->toArray();
    
    // Sort all signataires so that currently selected ones appear first in their saved order, followed by unselected ones
    $orderedSignataires = $allSignataires->sortBy(function($sig) use ($selectedIds) {
        $idx = array_search($sig->id, $selectedIds);
        return $idx !== false ? $idx : 9999;
    });
@endphp

@section('title')Réception — N° {{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}@endsection

@push('scripts')
<style>
@media print {
    body { background: white !important; font-family: 'Times New Roman', serif; }
    .sidebar, .top-bar, .no-print, .user-profile { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .app-container { display: block !important; }
    .scrollable-content { padding: 0 !important; }
    .print-page { padding: 15mm 12mm; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    table { page-break-inside: avoid; }
    .signature-grid { page-break-inside: avoid; }
    @page { size: A4; margin: 10mm; }
}
.print-page { font-family: 'Outfit', sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; background: white; border-radius: 0.5rem; box-shadow: var(--shadow-sm); }
.pv-header { text-align: center; border-bottom: 3px double #1e3a5f; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
.pv-header h1 { font-size: 1.4rem; font-weight: 800; color: #1e3a5f; margin: 0.5rem 0; letter-spacing: 0.05em; text-transform: uppercase; }
.pv-header .subtitle { font-size: 0.9rem; color: #555; }
.institution { font-size: 0.85rem; font-weight: 600; color: #333; letter-spacing: 0.02em; }
.pv-num { background: #1e3a5f; color: white; display: inline-block; padding: 0.3rem 1.2rem; border-radius: 4px; font-weight: 700; font-size: 1.1rem; margin-top: 0.5rem; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
.info-box { border: 1px solid #d1d5db; border-radius: 6px; padding: 1rem; background: #f9fafb; }
.info-box h4 { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; margin-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.4rem; }
.info-row { display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem; }
.info-row span:first-child { color: #6b7280; }
.info-row span:last-child { font-weight: 600; color: #111; }
.articles-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.88rem; }
.articles-table th { background: #1e3a5f; color: white; padding: 0.6rem 0.75rem; text-align: left; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; }
.articles-table th:not(:first-child) { text-align: right; }
.articles-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #e5e7eb; }
.articles-table td:not(:first-child) { text-align: right; }
.articles-table tr:hover { background: #f9fafb; }
.articles-table .partial { color: #f59e0b; font-weight: 700; }
.articles-table .complete { color: #10b981; font-weight: 700; }
.totals-box { background: #1e3a5f; color: white; border-radius: 8px; padding: 1rem 1.5rem; text-align: right; margin-bottom: 1.5rem; }
.totals-box .ht { font-size: 0.9rem; opacity: 0.8; margin-bottom: 0.25rem; }
.totals-box .tva-line { font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.5rem; }
.totals-box .ttc { font-size: 1.3rem; font-weight: 800; }
.pieces-box { border: 1px solid #d1d5db; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; background: #fffbeb; }
.pieces-box strong { display: block; margin-bottom: 0.25rem; color: #92400e; }
.signature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-top: 2rem; border-top: 1px solid #d1d5db; padding-top: 1.5rem; }
.signature-box { text-align: center; font-size: 0.8rem; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.75rem; background: #fafafa; }
.signature-box .title { font-weight: 700; text-transform: uppercase; color: #374151; margin-bottom: 0.25rem; font-size: 0.75rem; }
.signature-box .name { color: #475569; font-style: italic; margin-bottom: 2.5rem; font-size: 0.82rem; }
.signature-box .line { border-top: 1px solid #374151; padding-top: 0.25rem; color: #374151; font-size: 0.7rem; font-weight: 600; }

.sig-card-show {
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 0.4rem;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
    background: white;
    transition: all 0.15s;
}
.sig-card-show:hover { border-color: var(--primary-400, #60a5fa); background: #f8faff; }
.sig-card-show.selected { border-color: var(--primary-600, #2563eb); background: #eff6ff; }
.sig-card-show.drag-over { border-top: 3px solid var(--primary-500, #3b82f6); }
</style>
@endpush

@section('content')
<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="margin:0;">📦 Réception N° {{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <div style="margin-top:0.25rem; display:flex; gap:1rem; align-items:center;">
            <a href="{{ route('pv-receptions.index') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.9rem;">← Retour aux réceptions</a>
            <span style="color:var(--border-color);">|</span>
            <a href="{{ route('commandes.show', $pv_reception->commande_id) }}" style="color: var(--primary-600); text-decoration:none; font-size:0.9rem;">Voir la Commande ({{ $pv_reception->commande->numero }})</a>
        </div>
    </div>

    <div>
        @if($isPrintable)
            <button class="btn-primary" onclick="saveAndPrint()" style="display:flex; align-items:center; gap:0.5rem; padding:0.65rem 1.25rem; font-size:0.95rem;">
                <span>🖨️</span> Imprimer le PV de Réception
            </button>
        @else
            <span style="background:#f1f5f9; color:#64748b; padding:0.5rem 1rem; border-radius:99px; font-size:0.85rem; font-weight:600;">
                🚫 Impression non requise (≤ 300 000 FCFA)
            </span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="no-print" style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1.5rem;">
        {{ session('success') }}
    </div>
@endif

@if(!$isPrintable)
    <div class="no-print" style="padding:1rem 1.25rem; background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #64748b; border-radius:0.5rem; margin-bottom:1.5rem; color:#334155; font-size:0.9rem;">
        ℹ️ <strong>Information :</strong> Le montant réceptionné pour ce bon est de <strong>{{ number_format($pv_reception->montant_ttc, 0, ',', ' ') }} FCFA</strong> (inférieur ou égal à 300 000 FCFA). Conformément aux règles de gestion, <strong>l'impression d'un PV formel n'est pas requise</strong>.
    </div>
@else
    {{-- Panneau de configuration des signataires pour l'impression (uniquement si montant > 300 000) --}}
    <div class="card no-print" style="margin-bottom:1.5rem; background:#f0f7ff; border:1px solid #bfdbfe;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
            <div>
                <h4 style="margin:0; color:#1e40af; font-size:0.98rem; display:flex; align-items:center; gap:0.4rem;">
                    <span>✍️</span> Signataires du PV pour l'impression
                </h4>
                <p style="margin:0.2rem 0 0; color:#3b82f6; font-size:0.82rem;">
                    Cochez les personnes qui signent ce PV et glissez-déposez (☰) pour ajuster l'ordre des signatures.
                </p>
            </div>
            <button type="button" onclick="submitSignatairesForm()" class="btn-secondary" style="padding:0.4rem 0.8rem; font-size:0.85rem;">
                💾 Mettre à jour l'ordre
            </button>
        </div>

        <form id="signatairesForm" action="{{ route('pv-receptions.update-signataires', $pv_reception) }}" method="POST">
            @csrf
            <div id="signataire-list" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:0.6rem; max-height:280px; overflow-y:auto; padding:0.25rem;">
                @foreach($orderedSignataires as $sig)
                @php $checked = in_array($sig->id, $selectedIds); @endphp
                <label class="sig-card-show {{ $checked ? 'selected' : '' }}" id="sig-card-{{ $sig->id }}" draggable="true"
                    ondragstart="dragStart(event)" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)" ondragend="dragEnd(event)">
                    <div style="cursor:grab; color:#94a3b8; font-size:1.1rem; user-select:none;">☰</div>
                    <input type="checkbox"
                        name="signataire_ids[]"
                        value="{{ $sig->id }}"
                        style="width:16px; height:16px; accent-color:var(--primary-600); flex-shrink:0;"
                        onchange="this.closest('.sig-card-show').classList.toggle('selected', this.checked); updatePreview();"
                        {{ $checked ? 'checked' : '' }}>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; font-size:0.85rem; color:#1e293b;">{{ $sig->prenom }} {{ $sig->nom }}</div>
                        <div style="font-size:0.75rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $sig->fonction }}{{ $sig->matricule ? ' · '.$sig->matricule : '' }}
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </form>
    </div>
@endif

<div class="print-page">

    {{-- EN-TÊTE --}}
    <div class="pv-header">
        <div class="institution">HÔPITAL ARISTIDE LE DANTEC — DAKAR, SÉNÉGAL</div>
        <h1>Procès-Verbal de Réception</h1>
        <div class="pv-num">PV-{{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="subtitle" style="margin-top:0.5rem;">Établi le {{ \Carbon\Carbon::parse($pv_reception->date_reception)->locale('fr')->isoFormat('LL') }}</div>
    </div>

    {{-- INFORMATIONS GÉNÉRALES --}}
    <div class="info-grid">
        <div class="info-box">
            <h4>Informations Commande</h4>
            <div class="info-row">
                <span>N° Commande</span>
                <span>{{ $pv_reception->commande->numero }}</span>
            </div>
            <div class="info-row">
                <span>Fournisseur</span>
                <span>{{ $pv_reception->commande->fournisseur ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span>Compte</span>
                <span>{{ $pv_reception->commande->compte->numero ?? '—' }} — {{ $pv_reception->commande->compte->libelle ?? '' }}</span>
            </div>
            <div class="info-row">
                <span>Service</span>
                <span>{{ $pv_reception->commande->service->nom ?? 'Global' }}</span>
            </div>
        </div>
        <div class="info-box">
            <h4>Informations Réception</h4>
            <div class="info-row">
                <span>Date de réception</span>
                <span>{{ \Carbon\Carbon::parse($pv_reception->date_reception)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span>Enregistré par</span>
                <span>{{ $pv_reception->createdBy->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span>TVA appliquée</span>
                <span>{{ $pv_reception->tva }}%</span>
            </div>
            @if($pv_reception->pieces_justificatives && count($pv_reception->pieces_justificatives))
            <div class="info-row">
                <span>Pièces jointes</span>
                <span>{{ implode(', ', $pv_reception->pieces_justificatives) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- TABLEAU DES ARTICLES --}}
    <table class="articles-table">
        <thead>
            <tr>
                <th style="width:35%;">Désignation</th>
                <th>Conditionnement</th>
                <th>Qté Commandée</th>
                <th>Qté Reçue</th>
                <th>P.U (FCFA)</th>
                <th>Total (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pv_reception->articles as $pv_art)
            @php
                $ca = $pv_art->commandeArticle;
                $isPartial = $ca && $pv_art->quantite_receptionnee < $ca->quantite;
                $isOver = $ca && $pv_art->quantite_receptionnee > $ca->quantite;
                $montantLigne = $ca ? $pv_art->quantite_receptionnee * $ca->prix_unitaire : 0;
            @endphp
            <tr>
                <td>{{ $ca->designation ?? '—' }}</td>
                <td>{{ $ca->conditionnement ?? '—' }}</td>
                <td style="text-align:right;">{{ $ca->quantite ?? 0 }}</td>
                <td class="{{ $isPartial ? 'partial' : 'complete' }}" style="text-align:right;">
                    {{ $pv_art->quantite_receptionnee }}
                    @if($isPartial) <small>(partiel)</small> @endif
                    @if($isOver) <small style="color:#ef4444;">(⚠ dépassement)</small> @endif
                </td>
                <td style="text-align:right;">{{ number_format($ca->prix_unitaire ?? 0, 0, ',', ' ') }}</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($montantLigne, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAUX --}}
    <div class="totals-box">
        <div class="ht">Montant HT : {{ number_format($pv_reception->montant_total, 0, ',', ' ') }} FCFA</div>
        @if($pv_reception->tva > 0)
        <div class="tva-line">TVA ({{ $pv_reception->tva }}%) : {{ number_format($pv_reception->montant_total * $pv_reception->tva / 100, 0, ',', ' ') }} FCFA</div>
        @endif
        <div class="ttc">Montant TTC : {{ number_format($pv_reception->montant_ttc, 0, ',', ' ') }} FCFA</div>
    </div>

    {{-- SIGNATAIRES (uniquement si montant > 300 000) --}}
    @if($isPrintable)
        @php $sigs = $pv_reception->signatairesRelation; @endphp
        <div id="signatureGridPreview" class="signature-grid" style="grid-template-columns: repeat({{ max(min($sigs->count(), 4), 1) }}, 1fr);">
            @forelse($sigs as $sig)
            <div class="signature-box">
                <div class="title">{{ $sig->fonction }}</div>
                <div class="name">{{ $sig->prenom }} {{ $sig->nom }}{{ $sig->matricule ? ' ('.$sig->matricule.')' : '' }}</div>
                <div style="height:2.5rem;">&nbsp;</div>
                <div class="line">Signature &amp; Cachet</div>
            </div>
            @empty
            <div class="signature-box" style="grid-column: 1 / -1;">
                <div class="title">Signataires</div>
                <div class="name" style="color:var(--text-muted); margin-bottom:1.5rem;">(Sélectionnez les signataires ci-dessus avant impression)</div>
                <div class="line">Signature &amp; Cachet</div>
            </div>
            @endforelse
        </div>
    @endif

</div>

@if($isPrintable)
<script>
    let draggedItem = null;

    function dragStart(e) {
        draggedItem = e.currentTarget;
        e.dataTransfer.effectAllowed = "move";
        setTimeout(() => { draggedItem.style.opacity = "0.5"; }, 0);
    }

    function dragEnter(e) {
        e.preventDefault();
        const target = e.currentTarget;
        if (target !== draggedItem) {
            target.classList.add('drag-over');
        }
    }

    function dragLeave(e) {
        e.currentTarget.classList.remove('drag-over');
    }

    function dragOver(e) {
        e.preventDefault(); 
        e.dataTransfer.dropEffect = "move";
    }

    function drop(e) {
        e.preventDefault();
        const target = e.currentTarget;
        target.classList.remove('drag-over');
        if (target !== draggedItem) {
            const list = document.getElementById('signataire-list');
            const allItems = Array.from(list.children);
            const draggedIndex = allItems.indexOf(draggedItem);
            const targetIndex = allItems.indexOf(target);
            
            if (draggedIndex < targetIndex) {
                target.after(draggedItem);
            } else {
                target.before(draggedItem);
            }
        }
    }

    function dragEnd(e) {
        e.currentTarget.style.opacity = "1";
        document.querySelectorAll('.sig-card-show').forEach(c => c.classList.remove('drag-over'));
    }

    function submitSignatairesForm() {
        document.getElementById('signatairesForm').submit();
    }

    async function saveAndPrint() {
        const form = document.getElementById('signatairesForm');
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                window.print();
            } else {
                form.submit();
            }
        } catch (e) {
            window.print();
        }
    }
</script>
@endif
@endsection
