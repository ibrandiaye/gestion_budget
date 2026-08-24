@extends('layouts.app')

@section('title', 'Détail Commande')

@section('content')
    <a href="{{ route('commandes.index') }}" style="display: inline-block; margin-bottom: 1.5rem; color: var(--text-muted); text-decoration: none;">
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
                <h2 style="margin-bottom: 0.5rem;">Commande {{ $commande->numero }}</h2>
                <p style="color: var(--text-muted);">Date: {{ $commande->date->format('d/m/Y') }}</p>
            </div>
            <div style="text-align: right;">
                @php
                    $colors = [
                        'brouillon' => 'background: var(--slate-200); color: var(--slate-700);',
                        'soumis' => 'background: var(--warning); color: white;',
                        'valide' => 'background: var(--success); color: white;',
                        'rejete' => 'background: var(--danger); color: white;',
                    ];
                @endphp
                <span style="font-size: 0.9rem; padding: 0.4rem 0.8rem; border-radius: 4px; {{ $colors[$commande->statut] ?? '' }}">
                    {{ ucfirst($commande->statut) }}
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

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            @if($commande->statut === 'brouillon')
                <form action="{{ route('commandes.soumettre', $commande) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">Soumettre pour validation</button>
                </form>
            @endif

            @if($commande->statut === 'soumis')
                <button type="button" onclick="refuserCommande()" class="btn-secondary" style="background: var(--warning); color: white; border: none;">Renvoyer en brouillon</button>
                <form action="{{ route('commandes.finaliser', $commande) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">Finaliser la validation</button>
                </form>
            @endif

            @if($commande->statut === 'valide')
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-primary">📄 Imprimer le Bon</a>
             
                <a href="{{ route('pv-receptions.create', ['commande_id' => $commande->id]) }}" class="btn-secondary" style="background: var(--warning); color: white; border: none;">📦 Saisir Réception</a>
                   @if($commande->engagement)
                    <a href="{{ route('engagements.show', $commande->engagement) }}" class="btn-secondary">Voir l'engagement →</a>
                @endif
            @else
                <a href="{{ route('commandes.document', $commande) }}" target="_blank" class="btn-secondary">📄 Imprimer (Brouillon)</a>
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
