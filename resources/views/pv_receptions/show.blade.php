@extends('layouts.app')

@section('title')PV de Réception — PV-{{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}@endsection

@push('scripts')
<style>
@media print {
    body { background: white !important; font-family: 'Times New Roman', serif; }
    .sidebar, .top-bar, .no-print, .user-profile { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .app-container { display: block !important; }
    .scrollable-content { padding: 0 !important; }
    .print-page { padding: 20mm 15mm; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    table { page-break-inside: avoid; }
    .signature-grid { page-break-inside: avoid; }
    @page { size: A4; margin: 10mm; }
}
.print-page { font-family: 'Outfit', sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; }
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
.signature-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 2rem; border-top: 1px solid #d1d5db; padding-top: 1.5rem; }
.signature-box { text-align: center; font-size: 0.8rem; }
.signature-box .title { font-weight: 700; text-transform: uppercase; color: #374151; margin-bottom: 0.25rem; font-size: 0.75rem; }
.signature-box .name { color: #6b7280; font-style: italic; margin-bottom: 3rem; }
.signature-box .line { border-top: 1px solid #374151; padding-top: 0.25rem; color: #374151; font-size: 0.7rem; }
</style>
@endpush

@section('content')
<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin:0;">PV-{{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <a href="{{ route('pv-receptions.index') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.9rem;">← Retour à la liste</a>
    </div>
    <button class="btn-primary" onclick="window.print()">🖨️ Imprimer / PDF</button>
</div>

@if(session('success'))
    <div class="no-print" style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1.5rem;">
        {{ session('success') }}
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
            <h4>Informations PV</h4>
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

    {{-- SIGNATAIRES (from relation) --}}
    @php $sigs = $pv_reception->signatairesRelation; @endphp
    @if($sigs->isNotEmpty())
    <div class="signature-grid" style="grid-template-columns: repeat({{ min($sigs->count(), 4) }}, 1fr);">
        @foreach($sigs as $sig)
        <div class="signature-box">
            <div class="title">{{ $sig->fonction }}</div>
            <div class="name">{{ $sig->prenom }} {{ $sig->nom }}{{ $sig->matricule ? ' ('.$sig->matricule.')' : '' }}</div>
            <div style="height:3rem;">&nbsp;</div>
            <div class="line">Signature &amp; Cachet</div>
        </div>
        @endforeach
    </div>
    @else
    {{-- Fallback 3 cases vides --}}
    <div class="signature-grid">
        <div class="signature-box">
            <div class="title">Le Gestionnaire</div>
            <div style="height:3rem;">&nbsp;</div>
            <div class="line">Signature &amp; Cachet</div>
        </div>
        <div class="signature-box">
            <div class="title">Le Fournisseur</div>
            <div style="height:3rem;">&nbsp;</div>
            <div class="line">Signature &amp; Cachet</div>
        </div>
        <div class="signature-box">
            <div class="title">Le Directeur</div>
            <div style="height:3rem;">&nbsp;</div>
            <div class="line">Signature &amp; Cachet</div>
        </div>
    </div>
    @endif

</div>
@endsection
