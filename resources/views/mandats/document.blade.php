<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mandat de Paiement {{ $mandat->numero }}</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            background: #c8e6c9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #c8e6c9;
            padding: 20px;
            border: 2px solid #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .box {
            border: 1px solid #333;
            padding: 10px;
        }
        .box h4 {
            margin: 0 0 8px 0;
            font-size: 10pt;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            background: #a5d6a7;
            margin: -10px -10px 8px -10px;
            padding: 5px 10px;
        }
        .box p {
            margin: 5px 0;
            font-size: 9pt;
        }
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .signature-box {
            border: 1px solid #333;
            padding: 10px;
            min-height: 80px;
        }
        .signature-box h4 {
            margin: 0 0 5px 0;
            font-size: 9pt;
            background: #a5d6a7;
            margin: -10px -10px 10px -10px;
            padding: 5px 10px;
        }
        @media print {
            body { background: #c8e6c9; }
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
                <h2 style="margin: 0;">MANDAT DE PAIEMENT</h2>
                <p style="margin: 5px 0;">N° {{ $mandat->numero }}</p>
            </div>
            <div style="text-align: right;">
                <p>GESTION: {{ date('Y') }}</p>
                <p>{{ $mandat->date->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="grid-2">
            <div class="box">
                <h4>OBJET DE LA DÉPENSE</h4>
                <p style="font-weight: bold; font-size: 11pt;">{{ $mandat->objet_depense }}</p>
                <p style="margin-top: 15px;"><strong>Montant (en chiffres):</strong> {{ number_format($mandat->montant, 0, ',', ' ') }}</p>
                <p><strong>en (lettres):</strong><br>{{ $mandat->montant_lettres ?? 'Non spécifié' }}</p>
            </div>
            <div>
                <div class="box" style="margin-bottom: 10px;">
                    <h4>PIÈCES JUSTIFICATIVES</h4>
                    <p>{{ $mandat->pieces_justificatives ?? 'Facture, Bon de commande, Bon de livraison' }}</p>
                </div>
                <div class="box">
                    <h4>RÈGLEMENT</h4>
                    <p><strong>Mode:</strong> {{ $mandat->mode_reglement ?? '-' }}</p>
                    <p><strong>Établissement:</strong> {{ $mandat->etablissement ?? '-' }}</p>
                    <p><strong>Numéro compte:</strong> {{ $mandat->numero_compte ?? '-' }}</p>
                    <p><strong>RIB:</strong> {{ $mandat->rib ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="box">
                <h4>BÉNÉFICIAIRE</h4>
                <p style="font-weight: bold; font-size: 12pt;">{{ $mandat->beneficiaire }}</p>
                <p style="margin-top: 15px;"><strong>ENGAGEMENT</strong></p>
                <p>NUMÉRO: {{ $mandat->engagement->numero ?? '-' }}</p>
                <p>DATE: {{ $mandat->engagement?->date ? \Carbon\Carbon::parse($mandat->engagement->date)->format('d/m/Y') : '-' }}</p>
                <p>MONTANT: {{ number_format($mandat->montant, 0, ',', ' ') }}</p>
                <p>SERVICE: {{ $mandat->engagement?->service?->nom ?? ($mandat->engagement?->commande?->service?->nom ?? 'HALD') }}</p>
            </div>
            <div class="box">
                <h4>IMPUTATION BUDGÉTAIRE</h4>
                <p><strong>COMPTE PRINCIPAL:</strong> {{ $mandat->compte_principal ?? '-' }}</p>
                <p><strong>COMPTE D'IMPUTATION:</strong> {{ $mandat->compte_imputation ?? '-' }}</p>
                <p><strong>LIBELLÉ:</strong> {{ $mandat->libelle_imputation ?? '-' }}</p>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <h4>Cadre réservé à l'ordonnateur</h4>
                <p style="text-align: center; margin-top: 30px;"><strong>Le Directeur</strong></p>
                <div style="height: 40px;"></div>
            </div>
            <div class="signature-box">
                <h4>Cadre réservé au Comptable</h4>
                <div style="height: 60px;"></div>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
