@extends('layouts.app')

@section('title')Modifier la Réception @endsection

@section('content')
<style>
.form-control {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 0.5rem;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 0.9rem;
}
</style>

<div style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="margin:0;">✏️ Modifier la Réception REC-{{ str_pad($pv_reception->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <p style="color:var(--text-muted); margin:0.3rem 0 0;">Pour la commande N° <strong>{{ $pv_reception->commande->numero }}</strong> — Fournisseur : <strong>{{ $pv_reception->commande->fournisseur ?? '—' }}</strong></p>
    </div>
    <a href="{{ route('pv-receptions.show', $pv_reception) }}" class="btn-secondary" style="text-decoration:none;">← Retour</a>
</div>

@if($errors->any())
<div style="background:#fee2e2; color:#b91c1c; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem; font-size:0.9rem;">
    @foreach($errors->all() as $e)<p style="margin:0 0 0.25rem;">⚠️ {{ $e }}</p>@endforeach
</div>
@endif

<form action="{{ route('pv-receptions.update', $pv_reception) }}" method="POST">
    @csrf
    @method('PUT')

    <div style="display:flex; flex-direction:column; gap:1.5rem; max-width:1100px;">

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="margin:0;">1. Articles & Quantités Réceptionnées</h3>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <label style="font-weight:500; font-size:0.9rem; white-space:nowrap;">Date de Réception *</label>
                    <input type="date" name="date_reception" required class="form-control"
                        value="{{ old('date_reception', $pv_reception->date_reception?->format('Y-m-d')) }}"
                        style="width:180px;">
                </div>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
                <thead>
                    <tr style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted);">
                        <th style="padding:0.6rem 0.75rem; text-align:left; border-bottom:2px solid var(--border-color);">Désignation</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color);">Cond.</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color);">P.U (FCFA)</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#eff6ff;">Qté Cmdée</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#fef9c3;">Déjà Livré (autres PVs)</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#dcfce7;">Restant</th>
                        <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#f0fdf4;">Qté reçue ce jour</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pv_reception->commande->articles as $index => $article)
                    @php
                        $dejaRecuAutres = (int)($dejaRecus[$article->id] ?? 0);
                        $restant        = $article->quantite - $dejaRecuAutres;
                        // Current value for this PV
                        $pvArt          = $pv_reception->articles->firstWhere('commande_article_id', $article->id);
                        $currentQte     = $pvArt ? $pvArt->quantite_receptionnee : 0;
                        $complet        = ($restant <= 0 && $currentQte <= 0);
                    @endphp
                    <tr style="border-bottom:1px solid var(--border-color); {{ $complet ? 'opacity:0.55; background:#f9fafb;' : '' }}">
                        <td style="padding:0.65rem 0.75rem; font-weight:500;">
                            {{ $article->designation }}
                            @if($complet)
                                <span style="background:#dcfce7; color:#166534; font-size:0.7rem; font-weight:700; padding:0.1rem 0.4rem; border-radius:99px; margin-left:0.4rem;">✓ Complet</span>
                            @endif
                        </td>
                        <td style="padding:0.65rem 0.75rem; text-align:right; color:var(--text-muted); font-size:0.82rem;">{{ $article->conditionnement ?? '—' }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:right; background:#f8faff; font-weight:700;">{{ $article->quantite }}</td>
                        <td style="padding:0.65rem 0.75rem; text-align:right; background:#fefce8; color:{{ $dejaRecuAutres > 0 ? '#92400e' : 'var(--text-muted)' }}; font-weight:{{ $dejaRecuAutres > 0 ? '700' : '400' }};">
                            {{ $dejaRecuAutres }}
                        </td>
                        <td style="padding:0.65rem 0.75rem; text-align:right; background:#f0fdf4; font-weight:700; color:#15803d;">
                            {{ $restant }}
                        </td>
                        <td style="padding:0.5rem 0.75rem; text-align:right; background:#f0fdf4;">
                            <input type="hidden" name="articles[{{ $index }}][commande_article_id]" value="{{ $article->id }}">
                            @if($complet)
                                <input type="number" name="articles[{{ $index }}][quantite_receptionnee]" value="0"
                                    style="width:110px; text-align:right; padding:0.45rem 0.5rem; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.9rem; background:#e5e7eb; color:#9ca3af;"
                                    disabled readonly>
                            @else
                                <input type="number"
                                    name="articles[{{ $index }}][quantite_receptionnee]"
                                    style="width:110px; text-align:right; padding:0.45rem 0.5rem; border:1px solid var(--border-color); border-radius:0.4rem; font-size:0.9rem; font-family:inherit;"
                                    value="{{ old("articles.{$index}.quantite_receptionnee", $currentQte) }}"
                                    min="0"
                                    max="{{ $restant + $currentQte }}"
                                    oninput="
                                        var max = {{ $restant + $currentQte }};
                                        if(parseInt(this.value) > max) {
                                            this.value = max;
                                            this.style.borderColor = '#ef4444';
                                            this.style.background = '#fee2e2';
                                        } else {
                                            this.style.borderColor = '';
                                            this.style.background = '';
                                        }
                                    ">
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:1.25rem; display:flex; align-items:center; gap:1rem; padding-top:0.75rem; border-top:1px solid var(--border-color);">
                <label style="font-weight:500; font-size:0.9rem; white-space:nowrap;">TVA (%)</label>
                <select name="tva" class="form-control" style="width:120px;">
                    <option value="0" {{ (old('tva', $pv_reception->tva) == '0') ? 'selected' : '' }}>0%</option>
                    <option value="18" {{ (old('tva', $pv_reception->tva) == '18') ? 'selected' : '' }}>18%</option>
                </select>
            </div>
        </div>

        {{-- Pièces justificatives --}}
        <div class="card">
            <h3 style="margin-bottom:1rem;">2. Pièces Justificatives</h3>
            <label style="display:block; font-size:0.9rem; margin-bottom:0.4rem;">Références (séparées par des virgules)</label>
            <textarea name="pieces_justificatives_texte" class="form-control" rows="2" placeholder="Ex: BL N°1234, Facture N°5678">{{ old('pieces_justificatives_texte', implode(', ', $pv_reception->pieces_justificatives ?? [])) }}</textarea>
        </div>

        {{-- Actions --}}
        <div style="display:flex; justify-content:flex-end; gap:1rem;">
            <a href="{{ route('pv-receptions.show', $pv_reception) }}" class="btn-secondary" style="padding:0.85rem 1.5rem; text-decoration:none;">Annuler</a>
            <button type="submit" class="btn-primary" style="padding:0.85rem 2rem; font-size:1rem;">
                💾 Enregistrer les modifications
            </button>
        </div>

    </div>
</form>
@endsection
