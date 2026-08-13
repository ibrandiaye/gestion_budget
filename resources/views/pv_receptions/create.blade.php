@extends('layouts.app')

@section('title')Nouveau PV de Réception @endsection

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
.sig-card {
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.sig-card:hover { border-color: var(--primary-400, #60a5fa); background: var(--primary-50, #eff6ff); }
.sig-card input[type=checkbox]:checked + .sig-info { color: var(--primary-700); }
.sig-card.selected { border-color: var(--primary-600, #2563eb); background: var(--primary-50, #eff6ff); }
</style>

<div style="margin-bottom:1.5rem;">
    <h2 style="margin:0;">Créer un PV de Réception</h2>
    <p style="color:var(--text-muted); margin:0.3rem 0 0;">Pour la commande N° <strong>{{ $commande->numero }}</strong> — Fournisseur : <strong>{{ $commande->fournisseur ?? '—' }}</strong></p>
</div>

@if($errors->any())
<div style="background:#fee2e2; color:#b91c1c; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem; font-size:0.9rem;">
    @foreach($errors->all() as $e)<p style="margin:0 0 0.25rem;">⚠️ {{ $e }}</p>@endforeach
</div>
@endif

<form action="{{ route('pv-receptions.store') }}" method="POST">
    @csrf
    <input type="hidden" name="commande_id" value="{{ $commande->id }}">

    <div style="display:grid; grid-template-columns:3fr 1fr; gap:2rem; align-items:start;">

        {{-- Colonne gauche : Articles commandés + Quantités reçues --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem;">

            {{-- Tableau unifié : Articles commandés + Quantités reçues sur la même ligne --}}
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="margin:0;">1. Articles &amp; Réception</h3>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <label style="font-weight:500; font-size:0.9rem; white-space:nowrap;">Date de Réception *</label>
                        <input type="date" name="date_reception" required class="form-control" value="{{ date('Y-m-d') }}" style="width:180px;">
                    </div>
                </div>

                <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
                    <thead>
                        <tr style="font-size:0.78rem; text-transform:uppercase; color:var(--text-muted);">
                            <th style="padding:0.6rem 0.75rem; text-align:left; border-bottom:2px solid var(--border-color);">Désignation</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color);">Cond.</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color);">P.U (FCFA)</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#eff6ff;">Qté Cmdée</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#fef9c3;">Déjà reçu</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#dcfce7;">Restant</th>
                            <th style="padding:0.6rem 0.75rem; text-align:right; border-bottom:2px solid var(--border-color); background:#f0fdf4;">Qté ce PV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->articles as $index => $article)
                        @php
                            $dejaRecu = (int)($dejaRecus[$article->id] ?? 0);
                            $restant  = $article->quantite - $dejaRecu;
                            $complet  = $restant <= 0;
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
                            <td style="padding:0.65rem 0.75rem; text-align:right; background:#fefce8; color:{{ $dejaRecu > 0 ? '#92400e' : 'var(--text-muted)' }}; font-weight:{{ $dejaRecu > 0 ? '700' : '400' }};">
                                {{ $dejaRecu }}
                            </td>
                            <td style="padding:0.65rem 0.75rem; text-align:right; background:#f0fdf4; font-weight:700; color:{{ $complet ? '#16a34a' : '#15803d' }};">
                                {{ $restant }}
                            </td>
                            <td style="padding:0.5rem 0.75rem; text-align:right; background:#f0fdf4;">
                                <input type="hidden" name="articles[{{ $index }}][commande_article_id]" value="{{ $article->id }}">
                                @if($complet)
                                    <input type="number" name="articles[{{ $index }}][quantite_receptionnee]" value="0"
                                        style="width:90px; text-align:right; padding:0.45rem 0.5rem; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.9rem; background:#e5e7eb; color:#9ca3af;"
                                        disabled readonly>
                                @else
                                    <input type="number"
                                        name="articles[{{ $index }}][quantite_receptionnee]"
                                        style="width:90px; text-align:right; padding:0.45rem 0.5rem; border:1px solid var(--border-color); border-radius:0.4rem; font-size:0.9rem; font-family:inherit;"
                                        value="{{ old("articles.{$index}.quantite_receptionnee", $restant) }}"
                                        min="0"
                                        max="{{ $restant }}"
                                        oninput="
                                            var max = {{ $restant }};
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
                        <option value="0" {{ old('tva')=='0'?'selected':'' }}>0%</option>
                        <option value="18" {{ old('tva')=='18'?'selected':'' }}>18%</option>
                    </select>
                </div>
            </div>

            {{-- Pièces justificatives --}}
            <div class="card">
                <h3 style="margin-bottom:1rem;">3. Pièces Justificatives</h3>
                <label style="display:block; font-size:0.9rem; margin-bottom:0.4rem;">Références (séparées par des virgules)</label>
                <textarea name="pieces_justificatives_texte" class="form-control" rows="2" placeholder="Ex: BL N°1234, Facture N°5678">{{ old('pieces_justificatives_texte') }}</textarea>
            </div>
        </div>

        {{-- Colonne droite : Signataires --}}
        <div class="card" style="height:fit-content; position:sticky; top:1rem;">
            <h3 style="margin-bottom:0.5rem;">4. Signataires</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.25rem;">Cochez les membres qui signent ce PV.</p>

            @if($signataires->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <div style="font-size:2rem; margin-bottom:0.5rem;">👥</div>
                    <p>Aucun signataire enregistré.</p>
                    <a href="{{ route('signataires.index') }}" class="btn-primary" style="text-decoration:none; font-size:0.85rem;">Gérer les signataires</a>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:0.5rem; max-height:400px; overflow-y:auto; padding-right:0.25rem;">
                    @foreach($signataires as $sig)
                    <label class="sig-card" id="card-{{ $sig->id }}">
                        <input type="checkbox"
                            name="signataire_ids[]"
                            value="{{ $sig->id }}"
                            style="width:18px; height:18px; accent-color:var(--primary-600); flex-shrink:0;"
                            onchange="this.closest('.sig-card').classList.toggle('selected', this.checked)"
                            {{ in_array($sig->id, old('signataire_ids', [])) ? 'checked' : '' }}>
                        <div class="sig-info" style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:0.9rem;">{{ $sig->prenom }} {{ $sig->nom }}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted);">{{ $sig->fonction }}{{ $sig->matricule ? ' · '.$sig->matricule : '' }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p style="font-size:0.78rem; color:var(--text-muted); margin-top:0.75rem; text-align:right;">
                    <a href="{{ route('signataires.index') }}" target="_blank" style="color:var(--primary-600);">+ Gérer la liste</a>
                </p>
            @endif

            <div style="margin-top:2rem;">
                <button type="submit" class="btn-primary" style="width:100%; padding:0.85rem; font-size:1rem;">
                    ✅ Valider et créer le PV
                </button>
            </div>
        </div>

    </div>
</form>
@endsection
