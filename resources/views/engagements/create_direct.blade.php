@extends('layouts.app')

@section('title', 'Nouvel Engagement Direct')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>📝 Nouvel Engagement Direct</h2>
        <a href="{{ route('engagements.index') }}" style="color: var(--text-muted); text-decoration: none;">← Retour</a>
    </div>

    @if ($errors->any())
        <div style="padding: 1rem; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            @foreach ($errors->all() as $error)
                <p style="margin: 0;">⚠️ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="max-width: 800px;">
        <form action="{{ route('engagements.store-direct') }}" method="POST">
            @csrf

            @if(Auth::user()->role === 'admin')
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Service (Admin) *</label>
                <select name="service_id" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <option value="">Sélectionner un service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Compte d'imputation (Ligne à engagement direct) *</label>
                <select name="compte_id" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <option value="">Sélectionner un compte à engagement direct...</option>
                    @foreach($comptes as $compte)
                        @php
                            $dot = $budget ? ($compte->getDotationForBudget($budget->id) ?: $compte->lignesBudget()->where('budget_id', $budget->id)->sum('montant_alloue')) : 0;
                            
                            $totalEngage = \App\Models\Engagement::where(function($query) use ($budget, $compte) {
                                $query->where(function($q) use ($budget, $compte) {
                                    $q->where('budget_id', $budget->id)
                                      ->where('compte_id', $compte->id);
                                })->orWhereHas('commande', function($q) use ($budget, $compte) {
                                    $q->where('budget_id', $budget->id)
                                      ->where('compte_id', $compte->id);
                                });
                            })->where('statut', '!=', 'rejete')->sum('montant');
                            
                            $disp = $dot - $totalEngage;
                        @endphp
                        @if($dot > 0)
                            <option value="{{ $compte->id }}" {{ old('compte_id') == $compte->id ? 'selected' : '' }}>
                                {{ $compte->numero }} - {{ $compte->libelle }} (Dispo: {{ number_format($disp, 0, ',', ' ') }} FCFA)
                            </option>
                        @endif
                    @endforeach
                </select>
                @if($comptes->isEmpty())
                    <small style="color: #dc2626; display: block; margin-top: 0.35rem;">
                        ⚠️ Aucun compte à engagement direct configuré.
                    </small>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Date *</label>
                    <input type="date" name="date" required value="{{ old('date', date('Y-m-d')) }}" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Montant (FCFA) *</label>
                    <input type="number" name="montant" required min="1" value="{{ old('montant') }}" placeholder="Montant en FCFA" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Objet de la dépense *</label>
                <input type="text" name="objet" required value="{{ old('objet') }}" placeholder="Ex: Achat fournitures de bureau" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Fournisseur / Bénéficiaire <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">(facultatif)</span></label>
                <input type="text" name="nom_fournisseur" value="{{ old('nom_fournisseur') }}" placeholder="Nom complet (facultatif)" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">Créer l'engagement</button>
                <a href="{{ route('engagements.index') }}" style="padding: 0.75rem 2rem; border: 1px solid var(--border-color); border-radius: 0.5rem; text-decoration: none; color: var(--text-color);">Annuler</a>
            </div>
        </form>
    </div>
@endsection
