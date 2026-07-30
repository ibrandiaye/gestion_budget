@extends('layouts.app')

@section('title', 'Nouveau PV de Réception')

@section('content')
    <style>
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 0.5rem;
            box-sizing: border-box;
            font-family: inherit;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
    </style>
    <div style="margin-bottom: 2rem;">
        <h2>Créer un PV de Réception</h2>
        <p style="color: var(--text-muted);">Pour la commande N° {{ $commande->numero }}</p>
    </div>

    <form action="{{ route('pv-receptions.store') }}" method="POST">
        @csrf
        <input type="hidden" name="commande_id" value="{{ $commande->id }}">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            
            <!-- Fenêtre 1 : Positionnement Quantité Commandé -->
            <div class="card">
                <h3>1. Articles Commandés</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Récapitulatif de la commande</p>
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: var(--slate-50);">
                            <th style="padding: 0.5rem; text-align: left;">Désignation</th>
                            <th style="padding: 0.5rem; text-align: right;">Qté Commandée</th>
                            <th style="padding: 0.5rem; text-align: right;">P.U</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->articles as $article)
                        <tr style="border-top: 1px solid var(--border-color);">
                            <td style="padding: 0.5rem;">{{ $article->designation }}</td>
                            <td style="padding: 0.5rem; text-align: right;">{{ $article->quantite }}</td>
                            <td style="padding: 0.5rem; text-align: right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Fenêtre 2 : Saisie des Quantités Réceptionnées -->
            <div class="card">
                <h3>2. Saisie de la Réception</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Indiquez les quantités reçues</p>
                
                <div style="margin-top: 1rem;">
                    <label>Date de Réception</label>
                    <input type="date" name="date_reception" required class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: var(--slate-50);">
                            <th style="padding: 0.5rem; text-align: left;">Désignation</th>
                            <th style="padding: 0.5rem; text-align: right;">Qté Reçue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->articles as $index => $article)
                        <tr style="border-top: 1px solid var(--border-color);">
                            <td style="padding: 0.5rem;">{{ $article->designation }}</td>
                            <td style="padding: 0.5rem; text-align: right;">
                                <input type="hidden" name="articles[{{ $index }}][commande_article_id]" value="{{ $article->id }}">
                                <input type="number" name="articles[{{ $index }}][quantite_receptionnee]" class="form-control" style="width: 80px; text-align: right;" value="{{ $article->quantite }}" min="0" max="{{ $article->quantite }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>TVA (%)</label>
                        <select name="tva" class="form-control" required>
                            <option value="0">0%</option>
                            <option value="18">18%</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <label>Membres signataires (Séparés par des virgules)</label>
                    <textarea name="signataires_texte" class="form-control" rows="2" placeholder="Ex: Jean Dupont, Marie Ndiaye"></textarea>
                </div>

                <div style="margin-top: 1.5rem;">
                    <label>Références des pièces justificatives</label>
                    <textarea name="pieces_justificatives_texte" class="form-control" rows="2" placeholder="Ex: BL N°1234, Facture N°5678"></textarea>
                </div>
                
                <div style="margin-top: 2rem; text-align: right;">
                    <button type="submit" class="btn-primary">Valider et Imprimer le PV</button>
                </div>
            </div>
            
        </div>
    </form>
@endsection
