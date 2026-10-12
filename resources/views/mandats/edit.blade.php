@extends('layouts.app')

@section('title', 'Modifier le Mandat')

@section('content')
    <style>
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.5rem;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 0.95rem;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: var(--slate-700);
            font-size: 0.9rem;
        }
        .btn-secondary {
            padding: 0.6rem 1.25rem;
            background: white;
            color: #334155;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-secondary:hover {
            background: var(--slate-50);
        }
    </style>

    <div style="margin-bottom: 2rem;">
        <h2>✏️ Modifier le Mandat : {{ $mandat->numero }}</h2>
        <a href="{{ route('mandats.show', $mandat) }}" style="color: var(--text-muted); text-decoration: none;">&larr; Retour au mandat</a>
    </div>

    <div class="card" style="max-width: 950px;">
        @if ($errors->any())
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mandats.update', $mandat) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label>Numéro du Mandat</label>
                    <input type="text" value="{{ $mandat->numero }}" readonly class="form-control" style="background: var(--slate-100); font-weight: 600; color: var(--slate-600);">
                </div>
                <div>
                    <label>Date du Mandat *</label>
                    <input type="date" name="date" required class="form-control" value="{{ old('date', $mandat->date ? $mandat->date->format('Y-m-d') : date('Y-m-d')) }}">
                </div>

                <div style="grid-column: span 2;">
                    <label>Objet de la dépense *</label>
                    <input type="text" name="objet_depense" required class="form-control" value="{{ old('objet_depense', $mandat->objet_depense) }}" placeholder="Objet complet de la dépense">
                </div>

                <div>
                    <label>Bénéficiaire *</label>
                    <input type="text" name="beneficiaire" required class="form-control" value="{{ old('beneficiaire', $mandat->beneficiaire) }}" placeholder="Nom complet du bénéficiaire / fournisseur">
                </div>
                <div>
                    <label>Montant (FCFA) *</label>
                    <input type="number" step="0.01" name="montant" required class="form-control" value="{{ old('montant', $mandat->montant) }}" style="font-weight: 700; color: var(--primary-700);">
                </div>

                <div style="grid-column: span 2;">
                    <label>Montant en lettres</label>
                    <input type="text" name="montant_lettres" class="form-control" value="{{ old('montant_lettres', $mandat->montant_lettres) }}" placeholder="Ex: Deux cent cinquante mille francs CFA">
                </div>

                <div style="grid-column: span 2;">
                    <label>Pièces Justificatives</label>
                    <textarea name="pieces_justificatives" rows="2" class="form-control" placeholder="Ex: Facture N°123, Bon de commande, BL...">{{ old('pieces_justificatives', $mandat->pieces_justificatives) }}</textarea>
                </div>
            </div>

            <h3 style="margin: 2rem 0 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-size: 1.1rem; color: var(--primary-700);">
                Détails du Règlement & Compte Bancaire
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label>Mode de règlement</label>
                    <select name="mode_reglement" class="form-control">
                        <option value="">Sélectionnez...</option>
                        <option value="Virement bancaire" {{ (old('mode_reglement', $mandat->mode_reglement) == 'Virement bancaire') ? 'selected' : '' }}>Virement bancaire</option>
                        <option value="Chèque" {{ (old('mode_reglement', $mandat->mode_reglement) == 'Chèque') ? 'selected' : '' }}>Chèque</option>
                        <option value="Espèces" {{ (old('mode_reglement', $mandat->mode_reglement) == 'Espèces') ? 'selected' : '' }}>Espèces</option>
                        <option value="Autre" {{ (old('mode_reglement', $mandat->mode_reglement) == 'Autre') ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label>Établissement bancaire</label>
                    <input type="text" name="etablissement" class="form-control" value="{{ old('etablissement', $mandat->etablissement) }}" placeholder="Ex: CBAO, BOA, Ecobank...">
                </div>

                <div>
                    <label>Numéro de compte</label>
                    <input type="text" name="numero_compte" class="form-control" value="{{ old('numero_compte', $mandat->numero_compte) }}">
                </div>
                <div>
                    <label>RIB / Clé</label>
                    <input type="text" name="rib" class="form-control" value="{{ old('rib', $mandat->rib) }}">
                </div>
            </div>

            <h3 style="margin: 2rem 0 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-size: 1.1rem; color: var(--primary-700);">
                Imputation Budgétaire
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label>Compte principal</label>
                    <input type="text" name="compte_principal" class="form-control" value="{{ old('compte_principal', $mandat->compte_principal) }}">
                </div>
                <div>
                    <label>Compte d'imputation</label>
                    <input type="text" name="compte_imputation" class="form-control" value="{{ old('compte_imputation', $mandat->compte_imputation) }}">
                </div>
                <div style="grid-column: span 2;">
                    <label>Libellé d'imputation</label>
                    <input type="text" name="libelle_imputation" class="form-control" value="{{ old('libelle_imputation', $mandat->libelle_imputation) }}">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('mandats.show', $mandat) }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary" style="padding: 0.65rem 1.75rem;">
                    💾 Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
@endsection
