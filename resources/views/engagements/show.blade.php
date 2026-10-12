@extends('layouts.app')

@section('title', 'Détail Engagement')

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
        .btn-action-danger {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .btn-action-danger:hover {
            background: #fecaca;
            border-color: #fca5a5;
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
        .btn-action-imprimer {
            background: var(--primary-600, #0d9488);
            color: white;
            border-color: transparent;
        }
        .btn-action-imprimer:hover {
            background: var(--primary-700, #0f766e);
            color: white;
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
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0;">📄 Bon d'Engagement N° {{ $engagement->numero }}</h2>
            <div style="margin-top: 0.3rem; display: flex; gap: 1rem; align-items: center; font-size: 0.9rem;">
                <a href="{{ route('engagements.index') }}" style="color: var(--text-muted); text-decoration: none;">
                    ← Retour à la liste
                </a>
                <span style="color: var(--border-color);">|</span>
                <span style="color: var(--text-muted);">Date : <strong>{{ $engagement->date->format('d/m/Y') }}</strong></span>
            </div>
        </div>

        <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
            @if($engagement->statut === 'cree')
                @if(Auth::user()->can('engagements.rejeter') || Auth::user()->role === 'admin')
                    <button type="button" onclick="refuserEngagement()" class="btn-action btn-action-danger">
                        ❌ Rejeter
                    </button>
                @endif
                @if(Auth::user()->can('engagements.valider') || Auth::user()->role === 'admin')
                    <form action="{{ route('engagements.valider', $engagement) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-action btn-action-success">
                            ✅ Valider et créer le mandat
                        </button>
                    </form>
                @endif
            @endif

            @if($engagement->statut === 'valide')
                <a href="{{ route('engagements.document', $engagement) }}" target="_blank" class="btn-action btn-action-imprimer">
                    🖨️ Imprimer le Bon
                </a>
                @if($engagement->mandat)
                    <a href="{{ route('mandats.document', $engagement->mandat) }}" target="_blank" class="btn-action" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                        🖨️ Imprimer Mandat
                    </a>
                    <a href="{{ route('mandats.show', $engagement->mandat) }}" class="btn-action btn-action-secondary">
                        Voir le mandat →
                    </a>
                @endif
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
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">Informations de l'Engagement</h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Détails budgétaires et imputation</span>
            </div>
            <div style="text-align: right;">
                @php
                    $colors = [
                        'cree' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                        'valide' => 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
                        'rejete' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                    ];
                @endphp
                <span style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.9rem; border-radius: 99px; {{ $colors[$engagement->statut] ?? '' }}">
                    ● {{ ucfirst($engagement->statut) }}
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Informations</h4>
                <p><strong>Objet:</strong> {{ $engagement->objet }}</p>
                <p><strong>Nature:</strong> {{ $engagement->nature ?? '-' }}</p>
                <p><strong>Fournisseur:</strong> {{ $engagement->nom_fournisseur ?? 'Non spécifié' }}</p>
                @if($engagement->commande && $engagement->commande->fichier)
                    <p><strong>Fichier du Marché:</strong> <a href="{{ asset('storage/' . $engagement->commande->fichier) }}" target="_blank" style="color: var(--primary); text-decoration: underline;">📎 Voir le fichier</a></p>
                @endif
                <p><strong>Montant:</strong> <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary-700);">{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</span></p>
            </div>
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Imputation</h4>
                <p><strong>Compte:</strong> {{ $engagement->compte->numero ?? ($engagement->commande->compte->numero ?? '-') }} - {{ $engagement->compte->libelle ?? ($engagement->commande->compte->libelle ?? '-') }}</p>
                <p><strong>Service:</strong> {{ $engagement->service->nom ?? ($engagement->commande->service->nom ?? '-') }}</p>
                @if($engagement->commande)
                <p><strong>Commande:</strong> <a href="{{ route('commandes.show', $engagement->commande) }}">{{ $engagement->commande->numero }}</a></p>
                @endif
            </div>
        </div>

        @if($engagement->commande)
        <h4 style="margin-bottom: 1rem;">Articles validés</h4>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="background: var(--slate-100);">
                    <th style="padding: 0.75rem; text-align: left;">Désignation</th>
                    <th style="padding: 0.75rem; text-align: center;">Qté</th>
                    <th style="padding: 0.75rem; text-align: right;">P.U.</th>
                    <th style="padding: 0.75rem; text-align: right;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($engagement->commande->articlesValides as $article)
                <tr style="border-top: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem;">{{ $article->designation }}</td>
                    <td style="padding: 0.75rem; text-align: center;">{{ $article->quantite }}</td>
                    <td style="padding: 0.75rem; text-align: right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                    <td style="padding: 0.75rem; text-align: right; font-weight: 600;">{{ number_format($article->montant_ht, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <p style="color: var(--text-muted); text-align: center; margin: 0;">Cet engagement a été créé directement (sans commande associée).</p>
        </div>
        @endif

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; flex-wrap: wrap; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            @if($engagement->statut === 'cree')
                @if(Auth::user()->can('engagements.rejeter') || Auth::user()->role === 'admin')
                    <form action="{{ route('engagements.rejeter', $engagement) }}" method="POST" id="refusForm" style="display: none;">
                        @csrf
                        <input type="hidden" name="motif" id="motifRefusInput">
                    </form>
                    <button type="button" onclick="refuserEngagement()" class="btn-action btn-action-danger">
                        ❌ Rejeter
                    </button>
                @endif
                @if(Auth::user()->can('engagements.valider') || Auth::user()->role === 'admin')
                    <form action="{{ route('engagements.valider', $engagement) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-action btn-action-success">
                            ✅ Valider et créer le mandat
                        </button>
                    </form>
                @endif
            @endif

            @if($engagement->statut === 'valide')
                <a href="{{ route('engagements.document', $engagement) }}" target="_blank" class="btn-action btn-action-imprimer">
                    🖨️ Imprimer le Bon
                </a>
                @if($engagement->mandat)
                    <a href="{{ route('mandats.document', $engagement->mandat) }}" target="_blank" class="btn-action" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                        🖨️ Imprimer Mandat
                    </a>
                    <a href="{{ route('mandats.show', $engagement->mandat) }}" class="btn-action btn-action-secondary">
                        Voir le mandat →
                    </a>
                @endif
            @endif
        </div>
    </div>

    @if($engagement->statut === 'rejete' && $engagement->motif_rejet)
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
            <strong>Motif du rejet:</strong>
            <p>{{ $engagement->motif_rejet }}</p>
        </div>
    @endif

    <script>
        function refuserEngagement() {
            const motif = prompt('Veuillez saisir le motif du rejet:');
            if (motif && motif.trim()) {
                document.getElementById('motifRefusInput').value = motif;
                document.getElementById('refusForm').submit();
            }
        }
    </script>
@endsection
