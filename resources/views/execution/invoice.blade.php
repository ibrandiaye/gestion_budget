<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bon de Dépense #{{ $execution->id }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; line-height: 1.6; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .hospital-brand h1 { margin: 0; font-size: 24px; color: #2563eb; }
        .hospital-brand p { margin: 5px 0 0; color: #666; font-size: 14px; }
        .document-type { text-align: right; }
        .document-type h2 { margin: 0; font-size: 28px; text-transform: uppercase; color: #1e293b; }
        .document-type p { margin: 5px 0 0; font-family: monospace; font-weight: bold; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 12px; color: #64748b; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
        
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-item p { margin: 4px 0; }
        .detail-item b { color: #1e293b; }

        .execution-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .execution-table th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #475569; }
        .execution-table td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        
        .amount-section { text-align: right; margin-top: 30px; }
        .total-amount { font-size: 24px; font-weight: 800; color: #2563eb; }
        .amount-words { font-style: italic; color: #666; font-size: 14px; margin-top: 10px; }

        .signatures { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; border-top: 1px solid #cbd5e1; padding-top: 10px; text-align: center; }
        .signature-title { font-weight: bold; font-size: 13px; text-transform: uppercase; margin-bottom: 40px; }

        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width: 800px; margin: 0 auto 20px auto; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">🖨️ Imprimer ce document</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="hospital-brand">
                <h1>Hôpital Aristide Le Dantec</h1>
                <img src="{{ asset('entete/hald.jpg') }}" alt="Logo" style="width: 200px; height: auto;"><br>
                <p>Direction Administrative et Financière</p>
                <p>Gestion Budgétaire Automatisée</p>
            </div>
            <div class="document-type">
                <h2>Bon de Dépense</h2>
                <p>REF: #EXE-{{ date('Y', strtotime($execution->date)) }}-{{ str_pad($execution->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        <div class="section">
            <div class="sections-title">Informations Générales</div>
            <div class="details-grid">
                <div class="detail-item">
                    <p><b>Service demandeur :</b> {{ $execution->ligneBudget->service->nom }}</p>
                    <p><b>Date d'émission :</b> {{ date('d/m/Y', strtotime($execution->date)) }}</p>
                    <p><b>Exercice Budgétaire :</b> {{ $execution->ligneBudget->budget->annee }}</p>
                </div>
                <div class="detail-item">
                    <p><b>Compte Imputé :</b> {{ $execution->ligneBudget->compte->numero }}</p>
                    <p><b>Libellé Compte :</b> {{ $execution->ligneBudget->compte->libelle }}</p>
                </div>
            </div>
        </div>

        <table class="execution-table">
            <thead>
                <tr>
                    <th>Article / Désignation</th>
                    <th style="text-align: center; width: 60px;">Qté</th>
                    <th style="text-align: right; width: 120px;">Prix Unit.</th>
                    <th style="text-align: right; width: 130px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($execution->details as $detail)
                <tr>
                    <td>
                        <div style="font-weight: 700; margin-bottom: 2px;">{{ $detail->besoin->libelle }}</div>
                        <span style="font-size: 11px; color: #666;">ID: #BES-{{ $detail->besoin_id }}</span>
                    </td>
                    <td style="text-align: center; font-weight: 600;">
                        {{ $detail->quantite }}
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        {{ number_format($detail->prix_unitaire, 0, ',', ' ') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 15px; color: #1e293b;">
                        {{ number_format($detail->montant, 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="amount-section">
            <p style="margin-bottom: 0; color: #64748b;">MONTANT TOTAL ARRÊTÉ À :</p>
            <div class="total-amount">{{ number_format($execution->montant, 0, ',', ' ') }} FCFA</div>
            <div class="amount-words">(Montant en vigueur selon les règles de gestion budgétaire de l'établissement)</div>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">L'ordonnateur</div>
                <div style="height: 60px;"></div>
                <p style="border-top: 1px dotted #ccc; width: 80%; margin: 0 auto; color: #999; font-size: 11px;">Signature et Cachet</p>
            </div>
            <div class="signature-box">
                <div class="signature-title">Le Chef de Service</div>
                <div style="height: 60px;"></div>
                <p style="border-top: 1px dotted #ccc; width: 80%; margin: 0 auto; color: #999; font-size: 11px;">Signature et Cachet</p>
            </div>
        </div>

        <div style="margin-top: 100px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; color: #94a3b8; font-size: 10px;">
            Document généré électroniquement par le système de gestion budgétaire - Version 2.0
        </div>
    </div>
</body>
</html>
