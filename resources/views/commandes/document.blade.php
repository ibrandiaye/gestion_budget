<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon de Commande {{ $commande->numero }}</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            background: #fffbcc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fffbcc;
            padding: 20px;
            border: 2px solid #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header-left {
            text-align: center;
        }
        .header-left h3 {
            margin: 0;
            font-size: 12pt;
        }
        .header-center {
            text-align: center;
        }
        .header-center h1 {
            margin: 0;
            font-size: 18pt;
        }
        .header-center h2 {
            margin: 5px 0 0;
            font-size: 14pt;
        }
        .header-right {
            text-align: right;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            padding: 10px;
        }
        .info-box p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #e0e0a0;
        }
        .total-row {
            font-weight: bold;
        }
        .signature-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
        .signature-box {
            text-align: center;
            padding: 10px;
        }
        .signature-box h4 {
            margin: 0 0 40px 0;
            font-size: 10pt;
        }
        @media print {
            body { background: #fffbcc; }
            .container { border: 2px solid #333; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h3>REPUBLIQUE DU SENEGAL</h3>
                <p style="font-size: 9pt;">Un peuple - Un but - Une foi</p>
                <p style="font-size: 9pt;">Ministère de la Santé et de l’hygiène publique</p>
                <p style="font-weight: bold;">HOPITAL ARISTIDE LE DANTEC</p>
            </div>
            <div class="header-center">
                <h1>BON DE</h1>
                <h2>COMMANDE</h2>
                <p style="margin-top: 10px;">N° {{ $commande->numero }}</p>
            </div>
            <div class="header-right">
                <p>Date: {{ $commande->date->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <p><strong>{{ $commande->caisse }}</strong></p>
                <p>Références: {{ $commande->reference ?? '-' }}</p>
                <p>Fournisseur: {{ $commande->fournisseur ?? '-' }}</p>
            </div>
            <div class="info-box" style="text-align: right;">
                <p>N° compte: <strong>{{ $commande->compte->numero }}</strong></p>
                <p>Intitulé du compte: <strong>{{ $commande->compte->libelle }}</strong></p>
                <p>Dotation: <strong>{{ number_format($dotation ?? 0, 0, ',', ' ') }}</strong></p>
                <p>Crédits engagés: <strong>{{ number_format($creditsEngages ?? 0, 0, ',', ' ') }}</strong></p>
                <p>Disponible: <strong>{{ number_format($disponible ?? 0, 0, ',', ' ') }}</strong></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Cdmt</th>
                    <th style="text-align: center;">Quantité</th>
                    <th style="text-align: right;">Prix unitaire</th>
                    <th style="text-align: right;">Montant HT</th>
                </tr>
            </thead>
            <tbody>
                @if($commande->statut !== 'valide')
                    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100pt; color: rgba(0,0,0,0.1); z-index: -1;">
                        BROUILLON
                    </div>
                @endif
                @foreach($articles as $article)
                <tr>
                    <td>{{ $article->designation }}</td>
                    <td>{{ $article->conditionnement ?? '-' }}</td>
                    <td style="text-align: center;">{{ $article->quantite }}</td>
                    <td style="text-align: right;">{{ number_format($article->prix_unitaire, 0, ',', ' ') }}</td>
                    <td style="text-align: right;">{{ number_format($article->montant_ht, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 300px; margin-left: auto;">
            <tr>
                <td>Montant TVA</td>
                <td style="text-align: right;">{{ number_format($commande->montant_tva, 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td>Montant TTC</td>
                <td style="text-align: right;">{{ number_format($articles->sum('montant_ht'), 0, ',', ' ') }}</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">
            Arrêté le présent Bon à la somme de: <strong>{{ $montantEnLettres ?? 'Zéro' }} francs CFA</strong>
        </p>

        <div class="signature-section">
            <div class="signature-box">
                <h4>Le Chef de service Administratif et financier</h4>
                <div style="height: 60px;"></div>
            </div>
            <div class="signature-box">
                <h4>Le Contrôleur de gestion</h4>
                <div style="height: 60px;"></div>
            </div>
            <div class="signature-box">
                <h4>Le Directeur</h4>
                <div style="height: 60px;"></div>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
