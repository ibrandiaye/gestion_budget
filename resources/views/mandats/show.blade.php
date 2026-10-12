@extends('layouts.app')

@section('title', 'Détail Mandat')

@section('content')
    <style>
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            border: 1px solid transparent;
            font-family: inherit;
        }
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }
        .btn-action-modifier {
            background: #fef9c3;
            color: #92400e;
            border-color: #fde68a;
        }
        .btn-action-modifier:hover {
            background: #fef08a;
            border-color: #facc15;
            color: #854d0e;
        }
        .btn-action-supprimer {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .btn-action-supprimer:hover {
            background: #fecaca;
            border-color: #fca5a5;
            color: #991b1b;
        }
        .btn-action-imprimer {
            background: var(--primary-600, #0d9488);
            color: white;
            border-color: transparent;
        }
        .btn-action-imprimer:hover {
            background: var(--primary-700, #0f766e);
            color: white;
        }
        .btn-action-danger {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .btn-action-danger:hover {
            background: #fecaca;
            color: #991b1b;
        }
        .btn-action-success {
            background: var(--primary-600, #0d9488);
            color: white;
            border-color: transparent;
        }
        .btn-action-success:hover {
            background: var(--primary-700, #0f766e);
            color: white;
        }
        .btn-action-requisition {
            background: #f3e8ff;
            color: #7e22ce;
            border-color: #d8b4fe;
        }
        .btn-action-requisition:hover {
            background: #e9d5ff;
            color: #6b21a8;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0;">💳 Mandat N° {{ $mandat->numero }}</h2>
            <div style="margin-top: 0.3rem; display: flex; gap: 1rem; align-items: center; font-size: 0.9rem;">
                <a href="{{ route('mandats.index') }}" style="color: var(--text-muted); text-decoration: none;">
                    ← Retour à la liste
                </a>
                <span style="color: var(--border-color);">|</span>
                <span style="color: var(--text-muted);">Date : <strong>{{ $mandat->date->format('d/m/Y') }}</strong></span>
            </div>
        </div>

        <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('mandats.document', $mandat) }}" target="_blank" class="btn-action btn-action-imprimer">
                🖨️ Imprimer le Mandat
            </a>
            @if(Auth::user()->can('mandats.edit') || Auth::user()->role === 'admin')
                <a href="{{ route('mandats.edit', $mandat) }}" class="btn-action btn-action-modifier">
                    ✏️ Modifier
                </a>
            @endif
            @if(Auth::user()->can('mandats.delete') || Auth::user()->role === 'admin')
                <form action="{{ route('mandats.destroy', $mandat) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce mandat ? Cette action est irréversible.');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-action-supprimer">
                        🗑 Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
                <p style="margin: 0 0 0.25rem 0;">⚠️ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">Informations du Mandat</h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Statut et détails d'exécution budgétaire</span>
            </div>
            <div style="text-align: right;">
                @php
                    $colors = [
                        'cree' => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                        'valide_directeur' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                        'valide_comptable' => 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
                        'refuse_comptable' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                        'refuse_directeur' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                        'requisition' => 'background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;',
                        'paye' => 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
                    ];
                    $labels = [
                        'cree' => 'En attente Directeur',
                        'valide_directeur' => 'Attente Comptable',
                        'valide_comptable' => 'Payé',
                        'refuse_comptable' => 'Refusé par Comptable',
                        'refuse_directeur' => 'Refusé par Directeur',
                        'requisition' => 'Réquisition',
                        'paye' => 'Payé',
                    ];
                @endphp
                <span style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.9rem; border-radius: 99px; {{ $colors[$mandat->statut] ?? '' }}">
                    ● {{ $labels[$mandat->statut] ?? ucfirst($mandat->statut) }}
                </span>
                @if($mandat->requisition)
                    <div style="font-size: 0.75rem; color: #7e22ce; font-weight: 600; margin-top: 0.3rem;">⚠️ Payé par réquisition</div>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Objet de la dépense</h4>
                <p style="font-weight: 600; font-size: 1.1rem;">{{ $mandat->objet_depense }}</p>
                <p style="margin-top: 1rem;"><strong>Bénéficiaire:</strong> {{ $mandat->beneficiaire }}</p>
                @if($mandat->engagement?->commande?->fichier)
                    <p style="margin-top: 0.5rem;"><strong>Fichier du Marché:</strong> <a href="{{ asset('storage/' . $mandat->engagement->commande->fichier) }}" target="_blank" style="color: var(--primary); text-decoration: underline;">📎 Voir le fichier</a></p>
                @endif
                <p style="margin-top: 1rem; font-size: 1.5rem; font-weight: 800; color: var(--primary-700);">{{ number_format($mandat->montant, 0, ',', ' ') }} FCFA</p>
            </div>
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Imputation Budgétaire</h4>
                <p><strong>Compte principal:</strong> {{ $mandat->compte_principal ?? '-' }}</p>
                <p><strong>Compte d'imputation:</strong> {{ $mandat->compte_imputation ?? '-' }}</p>
                <p><strong>Libellé:</strong> {{ $mandat->libelle_imputation ?? '-' }}</p>
                <p style="margin-top: 1rem;"><strong>Engagement:</strong> 
                    @if($mandat->engagement)
                        <a href="{{ route('engagements.show', $mandat->engagement) }}">{{ $mandat->engagement->numero }}</a>
                    @else
                        -
                    @endif
                </p>
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
                        @if($mandat->statut === 'paye') ✓ 
                        @elseif($mandat->statut === 'refuse_comptable') ✗ 
                        @else 2 @endif
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

        @if($mandat->statut === 'refuse_comptable' || $mandat->statut === 'refuse_directeur')
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <strong>Motif du refus:</strong>
                <p>{{ $mandat->motif_refus }}</p>
            </div>
        @endif

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; flex-wrap: wrap; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            @if(Auth::user()->can('mandats.edit') || Auth::user()->role === 'admin')
                <a href="{{ route('mandats.edit', $mandat) }}" class="btn-action btn-action-modifier">
                    ✏️ Modifier
                </a>
            @endif

            @if(Auth::user()->can('mandats.delete') || Auth::user()->role === 'admin')
                <form action="{{ route('mandats.destroy', $mandat) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce mandat ? Cette action est irréversible.');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-action-supprimer">
                        🗑 Supprimer
                    </button>
                </form>
            @endif

            @if($mandat->canBeValidatedByDirecteur() && in_array(Auth::user()->role, ['admin', 'directeur']))
                <form action="{{ route('mandats.refuser-directeur', $mandat) }}" method="POST" id="refusDirecteurForm" style="display: none;">
                    @csrf
                    <input type="hidden" name="motif_refus" id="motifRefusDirecteurInput">
                </form>
                <button type="button" onclick="refuserMandat('Directeur')" class="btn-action btn-action-danger">❌ Refuser (Directeur)</button>
                <form action="{{ route('mandats.valider-directeur', $mandat) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action-success">✅ Valider (Directeur)</button>
                </form>
            @endif

            @if($mandat->canBeValidatedByComptable() && in_array(Auth::user()->role, ['admin', 'comptable']))
                <form action="{{ route('mandats.refuser-comptable', $mandat) }}" method="POST" id="refusForm" style="display: none;">
                    @csrf
                    <input type="hidden" name="motif_refus" id="motifRefusInput">
                </form>
                <button type="button" onclick="refuserMandat()" class="btn-action btn-action-danger">❌ Refuser (Comptable)</button>
                <form action="{{ route('mandats.valider-comptable', $mandat) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action-success">✅ Valider (Comptable)</button>
                </form>
            @endif

            @if($mandat->canBeRequisitioned() && in_array(Auth::user()->role, ['admin', 'directeur']))
                <form action="{{ route('mandats.requisition', $mandat) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir faire une réquisition ? Cela forcera le paiement malgré le refus du comptable.')" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action-requisition">⚠️ Réquisition</button>
                </form>
            @endif

            <a href="{{ route('mandats.document', $mandat) }}" target="_blank" class="btn-action btn-action-imprimer">🖨️ Imprimer le Mandat</a>
        </div>
    </div>

    <script>
        function refuserMandat(role = 'Comptable') {
            const motif = prompt('Veuillez saisir le motif du refus:');
            if (motif && motif.trim()) {
                if (role === 'Directeur') {
                    document.getElementById('motifRefusDirecteurInput').value = motif;
                    document.getElementById('refusDirecteurForm').submit();
                } else {
                    document.getElementById('motifRefusInput').value = motif;
                    document.getElementById('refusForm').submit();
                }
            }
        }
    </script>
@endsection
