@extends('layouts.app')

@section('title', 'Détail Mandat')

@section('content')
    <a href="{{ route('mandats.index') }}" style="display: inline-block; margin-bottom: 1.5rem; color: var(--text-muted); text-decoration: none;">
        ← Retour à la liste
    </a>

    @if ($errors->any())
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <div>
                <h2 style="margin-bottom: 0.5rem;">Mandat {{ $mandat->numero }}</h2>
                <p style="color: var(--text-muted);">Date: {{ $mandat->date->format('d/m/Y') }}</p>
            </div>
            <div style="text-align: right;">
                @php
                    $colors = [
                        'cree' => 'background: var(--slate-300); color: var(--slate-700);',
                        'valide_directeur' => 'background: var(--warning); color: white;',
                        'valide_comptable' => 'background: var(--success); color: white;',
                        'refuse_comptable' => 'background: var(--danger); color: white;',
                        'requisition' => 'background: #9c27b0; color: white;',
                        'paye' => 'background: var(--success); color: white;',
                    ];
                    $labels = [
                        'cree' => 'En attente Directeur',
                        'valide_directeur' => 'Attente Comptable',
                        'valide_comptable' => 'Payé',
                        'refuse_comptable' => 'Refusé par Comptable',
                        'requisition' => 'Réquisition',
                        'paye' => 'Payé',
                    ];
                @endphp
                <span style="font-size: 0.9rem; padding: 0.4rem 0.8rem; border-radius: 4px; {{ $colors[$mandat->statut] ?? '' }}">
                    {{ $labels[$mandat->statut] ?? ucfirst($mandat->statut) }}
                </span>
                @if($mandat->requisition)
                    <p style="font-size: 0.75rem; color: #9c27b0; margin-top: 0.5rem;">⚠️ Payé par réquisition</p>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Objet de la dépense</h4>
                <p style="font-weight: 600; font-size: 1.1rem;">{{ $mandat->objet_depense }}</p>
                <p style="margin-top: 1rem;"><strong>Bénéficiaire:</strong> {{ $mandat->beneficiaire }}</p>
                <p style="margin-top: 1rem; font-size: 1.5rem; font-weight: 800; color: var(--primary-700);">{{ number_format($mandat->montant, 0, ',', ' ') }} FCFA</p>
            </div>
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Imputation Budgétaire</h4>
                <p><strong>Compte principal:</strong> {{ $mandat->compte_principal ?? '-' }}</p>
                <p><strong>Compte d'imputation:</strong> {{ $mandat->compte_imputation ?? '-' }}</p>
                <p><strong>Libellé:</strong> {{ $mandat->libelle_imputation ?? '-' }}</p>
                <p style="margin-top: 1rem;"><strong>Engagement:</strong> <a href="{{ route('engagements.show', $mandat->engagement) }}">{{ $mandat->engagement->numero }}</a></p>
            </div>
        </div>

        <!-- Validation workflow display -->
        <div style="padding: 1rem; background: linear-gradient(90deg, var(--primary-50), var(--slate-50)); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <h4 style="margin-bottom: 1rem;">Workflow de validation</h4>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: center; flex: 1;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $mandat->valide_directeur_at ? 'var(--success)' : 'var(--slate-300)' }}; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.5rem;">
                        {{ $mandat->valide_directeur_at ? '✓' : '1' }}
                    </div>
                    <p style="font-weight: 600; margin-top: 0.5rem;">Directeur</p>
                    @if($mandat->valide_directeur_at)
                        <p style="font-size: 0.75rem; color: var(--text-muted);">{{ $mandat->valide_directeur_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <div style="flex: 0.5; height: 4px; background: {{ $mandat->valide_directeur_at ? 'var(--success)' : 'var(--slate-300)' }};"></div>
                <div style="text-align: center; flex: 1;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $mandat->statut === 'paye' ? 'var(--success)' : ($mandat->statut === 'refuse_comptable' ? 'var(--danger)' : 'var(--slate-300)') }}; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.5rem;">
                        @if($mandat->statut === 'paye') ✓ @elseif($mandat->statut === 'refuse_comptable') ✗ @else 2 @endif
                    </div>
                    <p style="font-weight: 600; margin-top: 0.5rem;">Comptable</p>
                    @if($mandat->valide_comptable_at)
                        <p style="font-size: 0.75rem; color: var(--text-muted);">{{ $mandat->valide_comptable_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <div style="flex: 0.5; height: 4px; background: {{ $mandat->statut === 'paye' ? 'var(--success)' : 'var(--slate-300)' }};"></div>
                <div style="text-align: center; flex: 1;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $mandat->statut === 'paye' ? 'var(--success)' : 'var(--slate-300)' }}; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.5rem;">
                        {{ $mandat->statut === 'paye' ? '✓' : '💰' }}
                    </div>
                    <p style="font-weight: 600; margin-top: 0.5rem;">Payé</p>
                </div>
            </div>
        </div>

        @if($mandat->statut === 'refuse_comptable')
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <strong>Motif du refus:</strong>
                <p>{{ $mandat->motif_refus }}</p>
            </div>
        @endif

        <div style="display: flex; justify-content: flex-end; gap: 1rem; flex-wrap: wrap;">
            @if($mandat->canBeValidatedByDirecteur() && in_array(Auth::user()->role, ['admin', 'directeur']))
                <form action="{{ route('mandats.valider-directeur', $mandat) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">✅ Valider (Directeur)</button>
                </form>
            @endif

            @if($mandat->canBeValidatedByComptable() && in_array(Auth::user()->role, ['admin', 'comptable']))
                <form action="{{ route('mandats.refuser-comptable', $mandat) }}" method="POST" id="refusForm" style="display: none;">
                    @csrf
                    <input type="hidden" name="motif_refus" id="motifRefusInput">
                </form>
                <button type="button" onclick="refuserMandat()" class="btn-secondary" style="background: var(--danger); color: white; border: none;">❌ Refuser (Comptable)</button>
                <form action="{{ route('mandats.valider-comptable', $mandat) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">✅ Valider (Comptable)</button>
                </form>
            @endif

            @if($mandat->canBeRequisitioned() && in_array(Auth::user()->role, ['admin', 'directeur']))
                <form action="{{ route('mandats.requisition', $mandat) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir faire une réquisition ? Cela forcera le paiement malgré le refus du comptable.')">
                    @csrf
                    <button type="submit" class="btn-primary" style="background: #9c27b0;">⚠️ Réquisition</button>
                </form>
            @endif

            @if($mandat->statut === 'paye')
                <a href="{{ route('mandats.document', $mandat) }}" target="_blank" class="btn-primary">📄 Imprimer le Mandat</a>
            @endif
        </div>
    </div>

    <script>
        function refuserMandat() {
            const motif = prompt('Veuillez saisir le motif du refus:');
            if (motif && motif.trim()) {
                document.getElementById('motifRefusInput').value = motif;
                document.getElementById('refusForm').submit();
            }
        }
    </script>
@endsection
