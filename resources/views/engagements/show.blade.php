@extends('layouts.app')

@section('title', 'Détail Engagement')

@section('content')
    <a href="{{ route('engagements.index') }}" style="display: inline-block; margin-bottom: 1.5rem; color: var(--text-muted); text-decoration: none;">
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
                <h2 style="margin-bottom: 0.5rem;">Engagement {{ $engagement->numero }}</h2>
                <p style="color: var(--text-muted);">Date: {{ $engagement->date->format('d/m/Y') }}</p>
            </div>
            <div style="text-align: right;">
                @php
                    $colors = [
                        'cree' => 'background: var(--warning); color: white;',
                        'valide' => 'background: var(--success); color: white;',
                        'rejete' => 'background: var(--danger); color: white;',
                    ];
                @endphp
                <span style="font-size: 0.9rem; padding: 0.4rem 0.8rem; border-radius: 4px; {{ $colors[$engagement->statut] ?? '' }}">
                    {{ ucfirst($engagement->statut) }}
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div style="padding: 1rem; background: var(--slate-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; color: var(--primary-700);">Informations</h4>
                <p><strong>Objet:</strong> {{ $engagement->objet }}</p>
                <p><strong>Nature:</strong> {{ $engagement->nature ?? '-' }}</p>
                <p><strong>Fournisseur:</strong> {{ $engagement->nom_fournisseur }}</p>
                @if($engagement->commande && $engagement->commande->fichier)
                    <p><strong>Fichier du Marché:</strong> <a href="{{ asset('storage/' . $engagement->commande->fichier) }}" target="_blank" style="color: var(--primary); text-decoration: underline;">📎 Voir le fichier</a></p>
                @endif
                <p><strong>Montant:</strong> <span style="font-size: 1.2rem; font-weight: 700;">{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</span></p>
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

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            @if($engagement->statut === 'cree')
                <form action="{{ route('engagements.rejeter', $engagement) }}" method="POST" id="refusForm" style="display: none;">
                    @csrf
                    <input type="hidden" name="motif" id="motifRefusInput">
                </form>
                <button type="button" onclick="refuserEngagement()" class="btn-secondary" style="background: var(--danger); color: white; border: none;">Rejeter</button>
                <form action="{{ route('engagements.valider', $engagement) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">Valider et créer le mandat</button>
                </form>
            @endif

            @if($engagement->statut === 'valide')
                <a href="{{ route('engagements.document', $engagement) }}" target="_blank" class="btn-primary">📄 Imprimer le Bon</a>
                @if($engagement->mandat)
                    <a href="{{ route('mandats.document', $engagement->mandat) }}" target="_blank" class="btn-primary" style="background: #e11d48; border-color: #e11d48;">📄 Imprimer Mandat</a>
                    <a href="{{ route('mandats.show', $engagement->mandat) }}" class="btn-secondary">Voir le mandat →</a>
                @endif
            @endif
        </div>
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
