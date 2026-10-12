@extends('layouts.app')

@section('title', 'Détail Commande')

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
        .btn-action-imprimer-brouillon {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }
        .btn-action-imprimer-brouillon:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }
        .btn-action-reception {
            background: #eff6ff;
            color: #1e40af;
            border-color: #bfdbfe;
        }
        .btn-action-reception:hover {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e3a8a;
        }
        .btn-action-secondary {
            background: white;
            color: #334155;
            border-color: var(--border-color, #e2e8f0);
        }
        .btn-action-secondary:hover {
            background: var(--slate-50, #f8fafc);
            color: #0f172a;
        }
        .btn-action-warning {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }
        .btn-action-warning:hover {
            background: #fde68a;
            color: #78350f;
        }
        .btn-action-primary {
            background: var(--primary-600, #0d9488);
            color: white;
            border-color: transparent;
        }
        .btn-action-primary:hover {
            background: var(--primary-700, #0f766e);
            color: white;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0;">📦 Commande N° {{ $commande->numero }}</h2>
            <div style="margin-top: 0.3rem; display: flex; gap: 1rem; align-items: center; font-size: 0.9rem;">
                <a href="{{ route('commandes.index') }}" style="color: var(--text-muted); text-decoration: none;">
                    ← Retour à la liste
                </a>
                <span style="color: var(--border-color);">|</span>
                <span style="color: var(--text-muted);">Date : <strong>{{ $commande->date->format('d/m/Y') }}</strong></span>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if($commande->statut === 'valide')
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-action btn-action-imprimer">
                    🖨️ Imprimer le Bon
                </a>
                <a href="{{ route('pv-receptions.create', ['commande_id' => $commande->id]) }}" class="btn-action btn-action-reception">
                    📦 Saisir Réception
                </a>
                @if($commande->engagement)
                    <a href="{{ route('engagements.show', $commande->engagement) }}" class="btn-action btn-action-secondary">
                        📄 Voir l'engagement →
                    </a>
                @endif
            @else
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-action btn-action-imprimer-brouillon">
                    🖨️ Imprimer (Brouillon)
                </a>
            @endif

            @if(in_array($commande->statut, ['brouillon', 'rejete']))
                @if(Auth::user()->can('commandes.edit') || Auth::user()->role === 'admin')
                    <a href="{{ route('commandes.edit', $commande) }}" class="btn-action btn-action-modifier">
                        ✏️ Modifier
                    </a>
                @endif

                @if(Auth::user()->can('commandes.delete') || Auth::user()->role === 'admin')
                    <form action="{{ route('commandes.destroy', $commande) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-action-supprimer">
                            🗑️ Supprimer
                        </button>
                    </form>
                @endif

                @if(Auth::user()->can('commandes.soumettre') || Auth::user()->role === 'admin')
                    <form action="{{ route('commandes.soumettre', $commande) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-action btn-action-primary">
                            📤 Soumettre pour validation
                        </button>
                    </form>
                @endif
            @endif

            @if($commande->statut === 'soumis')
                <button type="button" onclick="refuserCommande()" class="btn-action btn-action-warning">
                    ↩️ Renvoyer en brouillon
                </button>
                <form action="{{ route('commandes.finaliser', $commande) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action-primary">
                        ✅ Finaliser la validation
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
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">Détail de la commande</h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Statut et progression du traitement</span>
            </div>
            <div>
                @php
                    $colors = [
                        'brouillon' => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                        'soumis' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                        'valide' => 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
                        'rejete' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                    ];
                @endphp
                <span style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.9rem; border-radius: 99px; {{ $colors[$commande->statut] ?? '' }}">
                    ● {{ ucfirst($commande->statut) }}
                </span>
            </div>
        </div>

        @php
            $hasEngagement = $commande->engagement !== null;
            $hasReception = $commande->pvReceptions->count() > 0;
            $hasMandat = $hasEngagement && $commande->engagement->mandat !== null;
            
            $steps = [
                ['label' => 'Création', 'icon' => '📝', 'status' => 'completed', 'date' => $commande->created_at?->format('d/m/Y')],
                ['label' => 'Validation', 'icon' => '✅', 'status' => $commande->statut === 'valide' ? 'completed' : ($commande->statut === 'soumis' ? 'active' : 'pending'), 'date' => $commande->valide_at?->format('d/m/Y')],
                ['label' => 'Engagement', 'icon' => '📄', 'status' => $hasEngagement ? 'completed' : ($commande->statut === 'valide' ? 'active' : 'pending'), 'date' => $commande->engagement?->date?->format('d/m/Y')],
                ['label' => 'Réception', 'icon' => '📦', 'status' => $hasReception ? 'completed' : ($hasEngagement || $commande->statut === 'valide' ? 'active' : 'pending'), 'date' => $commande->pvReceptions->last()?->date_reception?->format('d/m/Y')],
                ['label' => 'Mandatement', 'icon' => '💳', 'status' => $hasMandat ? 'completed' : ($hasReception ? 'active' : 'pending'), 'date' => $commande->engagement?->mandat?->date?->format('d/m/Y')],
            ];

            // Calculate progress line
            $completedCount = count(array_filter($steps, fn($s) => $s['status'] === 'completed'));
            $progressWidth = (max(0, $completedCount - 1) / (count($steps) - 1)) * 100;
        @endphp

        <style>
            .timeline-wrapper { position: relative; margin: 2rem 1rem 3rem 1rem; }
            .timeline-bg-line { position: absolute; top: 1.25rem; left: 2%; right: 2%; height: 4px; background: #e2e8f0; z-index: 1; border-radius: 4px; }
            .timeline-progress-line { position: absolute; top: 1.25rem; left: 2%; height: 4px; background: var(--primary-500, #3b82f6); z-index: 2; border-radius: 4px; transition: width 0.5s ease; }
            .timeline-steps { display: flex; justify-content: space-between; position: relative; z-index: 3; }
            .t-step { display: flex; flex-direction: column; align-items: center; width: 100px; text-align: center; }
            .t-icon { width: 2.5rem; height: 2.5rem; border-radius: 50%; background: white; border: 3px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 0.5rem; color: #94a3b8; transition: all 0.3s; }
            .t-step.completed .t-icon { border-color: var(--primary-500, #3b82f6); background: var(--primary-50, #eff6ff); color: var(--primary-600, #2563eb); }
            .t-step.active .t-icon { border-color: var(--primary-400, #60a5fa); box-shadow: 0 0 0 4px var(--primary-100, #dbeafe); color: var(--primary-600, #2563eb); }
            .t-label { font-size: 0.8rem; font-weight: 600; color: #64748b; }
            .t-step.completed .t-label { color: #1e293b; }
            .t-step.active .t-label { color: var(--primary-700, #1d4ed8); }
            .t-date { font-size: 0.7rem; color: #94a3b8; margin-top: 0.2rem; min-height: 1rem; }
        </style>

        <div class="timeline-wrapper">
            <div class="timeline-bg-line"></div>
            <div class="timeline-progress-line" style="width: {{ $progressWidth }}%;"></div>
            <div class="timeline-steps">
                @foreach($steps as $step)
                <div class="t-step {{ $step['status'] }}">
                    <div class="t-icon">{{ $step['icon'] }}</div>
                    <div class="t-label">{{ $step['label'] }}</div>
                    <div class="t-date">{{ $step['date'] ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Compte</span>
                <span style="font-weight: 600;">{{ $commande->compte->numero }} - {{ $commande->compte->libelle }}</span>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Service</span>
                <span style="font-weight: 600;">{{ $commande->service->nom ?? '-' }}</span>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Fournisseur</span>
                <span style="font-weight: 600;">{{ $commande->fournisseur ?? '-' }}</span>
                @if($commande->fichier)
                    <a href="{{ asset('storage/' . $commande->fichier) }}" target="_blank" style="display: inline-block; margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary); text-decoration: underline;">
                        📎 Voir le Fichier du Marché
                    </a>
                @endif
            </div>
        </div>

        <h4 style="margin-bottom: 1rem;">Articles de la commande</h4>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="background: var(--slate-100);">
                    <th style="padding: 0.75rem; text-align: left;">Désignation</th>
                    <th style="padding: 0.75rem; text-align: center;">Conditionnement</th>
                    <th style="padding: 0.75rem; text-align: center;">Qté</th>
                    <th style="padding: 0.75rem; text-align: right;">P.U.</th>
                    <th style="padding: 0.75rem; text-align: right;">Montant HT</th>
                    @if($commande->statut === 'soumis')
                        <th style="padding: 0.75rem; text-align: center;">Validation</th>
                    @elseif($commande->statut === 'valide')
                        <th style="padding: 0.75rem; text-align: center;">État</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($commande->articles as $article)
                <tr style="border-top: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem;">{{ $article->designation }}</td>
                    <td style="padding: 0.75rem; text-align: center;">{{ $article->conditionnement ?? '-' }}</td>
                    <td style="padding: 0.75rem; text-align: center;">{{ $article->quantite }}</td>
                    <td style="padding: 0.75rem; text-align: right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($article->montant_ht, 0, ',', ' ') }}</td>
                    @if($commande->statut === 'soumis')
                        <td style="padding: 0.75rem; text-align: center;">
                            @if($article->valide)
                                <span style="color: var(--success); font-weight: 600;">✓ Validé</span>
                            @else
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <form action="{{ route('commandes.valider-article', [$commande, $article]) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="background: var(--success); color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Valider</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    @elseif($commande->statut === 'valide')
                        <td style="padding: 0.75rem; text-align: center;">
                            @if($article->valide)
                                <span style="color: var(--success);">✓ Validé</span>
                            @else
                                <span style="color: var(--danger);">✗ Rejeté</span>
                            @endif
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: var(--slate-50);">
                    <td colspan="4" style="padding: 0.75rem; text-align: right; font-weight: 700;">TOTAL TTC:</td>
                    <td style="padding: 0.75rem; text-align: right; font-weight: 700; font-size: 1.1rem;">{{ number_format($commande->montant_ttc, 0, ',', ' ') }} FCFA</td>
                    @if(in_array($commande->statut, ['soumis', 'valide']))
                        <td></td>
                    @endif
                </tr>
            </tfoot>
        </table>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; flex-wrap: wrap; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            @if(in_array($commande->statut, ['brouillon', 'rejete']))
                @if(Auth::user()->can('commandes.edit') || Auth::user()->role === 'admin')
                    <a href="{{ route('commandes.edit', $commande) }}" class="btn-action btn-action-modifier">
                        ✏️ Modifier
                    </a>
                @endif

                @if(Auth::user()->can('commandes.delete') || Auth::user()->role === 'admin')
                    <form action="{{ route('commandes.destroy', $commande) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-action-supprimer">
                            🗑️ Supprimer
                        </button>
                    </form>
                @endif

                @if(Auth::user()->can('commandes.soumettre') || Auth::user()->role === 'admin')
                    <form action="{{ route('commandes.soumettre', $commande) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-action btn-action-primary">
                            📤 Soumettre pour validation
                        </button>
                    </form>
                @endif
            @endif

            @if($commande->statut === 'soumis')
                <button type="button" onclick="refuserCommande()" class="btn-action btn-action-warning">
                    ↩️ Renvoyer en brouillon
                </button>
                <form action="{{ route('commandes.finaliser', $commande) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-action-primary">
                        ✅ Finaliser la validation
                    </button>
                </form>
            @endif

            @if($commande->statut === 'valide')
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-action btn-action-imprimer">
                    🖨️ Imprimer le Bon
                </a>
             
                <a href="{{ route('pv-receptions.create', ['commande_id' => $commande->id]) }}" class="btn-action btn-action-reception">
                    📦 Saisir Réception
                </a>
                @if($commande->engagement)
                    <a href="{{ route('engagements.show', $commande->engagement) }}" class="btn-action btn-action-secondary">
                        📄 Voir l'engagement →
                    </a>
                @endif
            @else
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-action btn-action-imprimer-brouillon">
                    🖨️ Imprimer (Brouillon)
                </a>
            @endif
        </div>
        
        @if($commande->statut === 'brouillon' && $commande->motif_rejet)
        <div style="margin-top: 1.5rem; padding: 1rem; background: #fee2e2; border: 1px solid #fecaca; border-radius: 0.5rem;">
            <h4 style="color: #b91c1c; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                ⚠️ Motif du renvoi en brouillon
            </h4>
            <p style="color: #991b1b; margin: 0;">{{ $commande->motif_rejet }}</p>
        </div>
        @endif
    </div>

    @if($commande->statut === 'soumis')
        <!-- Hidden Refus Form -->
        <form action="{{ route('commandes.refuser', $commande) }}" method="POST" id="refusForm" style="display: none;">
            @csrf
            <input type="hidden" name="motif" id="motifRefusInput">
        </form>
        
        <script>
            function refuserCommande() {
                const motif = prompt('Veuillez saisir le motif du renvoi en brouillon:');
                if (motif && motif.trim()) {
                    document.getElementById('motifRefusInput').value = motif;
                    document.getElementById('refusForm').submit();
                }
            }
        </script>
    @endif
@endsection
