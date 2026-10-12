<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon d'Engagement {{ $engagement->numero }}</title>
    <style>
        @page { margin: 1cm; size: landscape; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            background: #fce4ec;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fce4ec;
            padding: 15px;
            border: 2px solid #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .box {
            border: 1px solid #333;
            padding: 8px;
        }
        .box h4 {
            margin: 0 0 5px 0;
            font-size: 9pt;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }
        .box p {
            margin: 3px 0;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
        }
        th {
            background: #f8bbd9;
        }
        .signature-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        .signature-box {
            text-align: center;
            font-size: 8pt;
        }
        @media print {
            body { background: #fce4ec; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="text-align: center;">
                <strong>REPUBLIQUE DU SENEGAL</strong><br>
                <span style="font-size: 8pt;">Un peuple - Un but - Une foi</span><br>
                <img src="{{ asset('entete/drapeau.png') }}" alt="Logo" style="width: 50px; height: auto;"><br>
                <span style="font-size: 8pt;">HOPITAL ARISTIDE LE DANTEC</span><br>
                <img src="{{ asset('entete/hald.jpg') }}" alt="Logo" style="width: 200px; height: auto;">
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0;">BON D'ENGAGEMENT</h2>
                <p style="margin: 5px 0;">N° {{ $engagement->numero }}</p>
            </div>
            <div style="text-align: right;">
                <p>GESTION: {{ date('Y') }}</p>
                <p>Dakar, le {{ $engagement->date ? \Carbon\Carbon::parse($engagement->date)->format('d/m/Y') : date('d/m/Y') }}</p>
            </div>
        </div>

        <div class="grid-2" style="margin-bottom: 10px;">
            <div class="box">
                <h4>DÉPENSE</h4>
                <p><strong>Type:</strong> {{ $engagement->nature ?? 'Fonctionnement' }}</p>
                <p><strong>Nature:</strong> {{ $engagement->objet }}</p>
                <p><strong>Objet:</strong> {{ $engagement->objet }}</p>
                <p><strong>Montant:</strong> {{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="box">
                @php
                    $compte = $engagement->compte ?? $engagement->commande?->compte;
                    $service = $engagement->service ?? $engagement->commande?->service;
                @endphp
                <h4>IMPUTATION</h4>
                <p><strong>Budget:</strong> {{ $engagement->nature ?? 'Fonctionnement' }}</p>
                <p><strong>Numéro compte principal:</strong> {{ substr($compte->numero ?? '', 0, 2) }}</p>
                <p><strong>Libellé:</strong> {{ $compte->libelle ?? '-' }}</p>
            </div>
        </div>

        <div class="grid-2" style="margin-bottom: 10px;">
            <div class="box">
                <h4>DÉSIGNATION DU FOURNISSEUR</h4>
                <p style="font-size: 11pt; font-weight: bold;">{{ $engagement->nom_fournisseur ?? 'Non spécifié' }}</p>
                <p><strong>SONAFEL:</strong> {{ $engagement->sonafel ?? '-' }}</p>
                <p><strong>SERVICE BÉNÉFICIAIRE:</strong> {{ $service->nom ?? '-' }}</p>
            </div>
            <div class="box">
                <h4>SITUATION DES ENGAGEMENTS</h4>
                <table>
                    <tr><td>Crédits ouverts</td><td style="text-align: right;">{{ number_format($engagement->credits_ouverts, 0, ',', ' ') }}</td></tr>
                    <tr><td>Modifications de crédits</td><td style="text-align: right;">{{ number_format($engagement->modifications_credits, 0, ',', ' ') }}</td></tr>
                    <tr><td>Engagements antérieurs</td><td style="text-align: right;">{{ number_format($engagement->engagements_anterieurs, 0, ',', ' ') }}</td></tr>
                    <tr><td>Disponible avant</td><td style="text-align: right;">{{ number_format($engagement->disponible_avant, 0, ',', ' ') }}</td></tr>
                    <tr><td>Dépenses du jour</td><td style="text-align: right;">{{ number_format($engagement->montant, 0, ',', ' ') }}</td></tr>
                    <tr style="font-weight: bold;"><td>Nouveau disponible</td><td style="text-align: right;">{{ number_format($engagement->nouveau_disponible, 0, ',', ' ') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <strong>Chef du Service Administratif et Financier</strong>
                <div style="height: 50px;"></div>
            </div>
            <div class="signature-box">
                <strong>Le contrôleur de gestion</strong>
                <div style="height: 50px;"></div>
            </div>
            <div class="signature-box">
                <strong>Le Directeur</strong>
                <div style="height: 50px;"></div>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
