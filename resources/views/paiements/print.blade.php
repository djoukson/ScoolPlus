<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement - {{ $paiement->eleve->nom.' '.$paiement->eleve->prenom }}</title>
    <style>
        body {
            font-family: "Poppins", Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            color: #333;
            padding: 10px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
        }

        .page {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            padding: 25px 35px;
            margin-bottom: 5px;
        }

        .separator {
            border-top: 1px dashed #888;
            margin: 10px 0;
            text-align: center;
            position: relative;
        }

        .separator span {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            color: #777;
            padding: 0 8px;
            font-size: 0.8rem;
            font-style: italic;
        }

        /* ---------- HEADER ---------- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header .left {
            display: flex;
            align-items: center;
        }

        .header img {
            width: 65px;
            height: 65px;
            border-radius: 8px;
            margin-right: 12px;
        }

        .header h1 {
            font-size: 1.2rem;
            margin: 0;
            color: #4e73df;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            font-size: 0.8rem;
            color: #666;
        }

        .header .right {
            text-align: right;
            font-size: 0.85rem;
        }

        /* ---------- DETAILS ---------- */
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .details th, .details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .details th {
            background: #f4f6fb;
            width: 35%;
            color: #555;
            font-weight: 600;
        }

        .amount {
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
            color: #28a745;
            margin-top: 5px;
        }

        /* ---------- SIGNATURE ---------- */
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 0.85rem;
        }

        .signature div {
            width: 45%;
            text-align: center;
        }

        .signature .sign-line {
            border-top: 1px solid #aaa;
            margin-top: 40px;
        }

        /* ---------- FOOTER ---------- */
        .footer {
            border-top: 1px dashed #ccc;
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 0.8rem;
            color: #777;
        }

        /* ---------- Impression ---------- */
        @media print {
            body {
                margin: 0;
                background: #fff;
            }
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .page {
                box-shadow: none;
                border: none;
                margin-bottom: 0;
                page-break-inside: avoid;
            }
            .separator {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>

@php
    $utilisateur = auth()->user();
@endphp

<div class="container">
    <!-- 🔹 Première copie : élève -->
    <div class="page">
        <div class="header">
            <div class="left">
                @if(!empty($ecole->logo))
                    <img src="{{ asset('storage/' . $ecole->logo) }}" alt="Logo de l'école">
                @endif
                <div>
                    <h1>{{ strtoupper($ecole->nom ?? 'École CASE') }}</h1>
                    <p>{{ $ecole->adresse ?? 'Adresse non spécifiée' }}</p>
                    <p>Tél : {{ $ecole->telephone ?? '-' }} | Email : {{ $ecole->email ?? '-' }}</p>
                </div>
            </div>
            <div class="right">
                <p><strong>Reçu N°:</strong> {{ str_pad($paiement->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</p>
                <p><strong>Année:</strong> {{ $anneenom }}</p>
            </div>
        </div>

        <table class="details">
            <tr><th>Élève</th><td>{{ $paiement->eleve->nom.' '.$paiement->eleve->prenom }}</td></tr>
            <tr><th>Classe</th><td>{{ $paiement->classe->nom }}</td></tr>
            <tr><th>Frais</th><td>{{ $paiement->frais->libelle }}</td></tr>
            <tr><th>Mode de paiement</th><td>{{ ucfirst($paiement->mode_paiement) }}</td></tr>
        </table>

        <div class="amount">Montant payé : {{ number_format($paiement->montant_paye, 0, ',', ' ') }} FCFA</div>

        <div class="signature">
            <div>
                <strong>Signature élève / parent</strong>
                <div class="sign-line"></div>
            </div>
            <div>
                <strong>Caissier : {{ $utilisateur->name ?? 'Utilisateur inconnu' }}</strong>
                <div class="sign-line"></div>
            </div>
        </div>

        <div class="footer">
            Copie élève — Merci pour votre paiement.<br>
            Reçu généré automatiquement par le système de gestion de l'école.
        </div>
    </div>

    <!-- 🔸 Séparateur -->
    <div class="separator">
        <span>✂️ Découper ici</span>
    </div>

    <!-- 🔹 Deuxième copie : école -->
    <div class="page">
        <div class="header">
            <div class="left">
                @if(!empty($ecole->logo))
                    <img src="{{ asset('storage/' . $ecole->logo) }}" alt="Logo de l'école">
                @endif
                <div>
                    <h1>{{ strtoupper($ecole->nom ?? 'École CASE') }}</h1>
                    <p>{{ $ecole->adresse ?? 'Adresse non spécifiée' }}</p>
                    <p>Tél : {{ $ecole->telephone ?? '-' }} | Email : {{ $ecole->email ?? '-' }}</p>
                </div>
            </div>
            <div class="right">
                <p><strong>Reçu N°:</strong> {{ str_pad($paiement->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</p>
                <p><strong>Année:</strong> {{ $anneenom }}</p>
            </div>
        </div>

        <table class="details">
            <tr><th>Élève</th><td>{{ $paiement->eleve->nom.' '.$paiement->eleve->prenom }}</td></tr>
            <tr><th>Classe</th><td>{{ $paiement->classe->nom }}</td></tr>
            <tr><th>Frais</th><td>{{ $paiement->frais->libelle }}</td></tr>
            <tr><th>Mode de paiement</th><td>{{ ucfirst($paiement->mode_paiement) }}</td></tr>
        </table>

        <div class="amount">Montant payé : {{ number_format($paiement->montant_paye, 0, ',', ' ') }} FCFA</div>

        <div class="signature">
            <div>
                <strong>Signature élève / parent</strong>
                <div class="sign-line"></div>
            </div>
            <div>
                <strong>Caissier : {{ $utilisateur->name ?? 'Utilisateur inconnu' }}</strong>
                <div class="sign-line"></div>
            </div>
        </div>

        <div class="footer">
            Copie école — Conservez cette souche pour archivage administratif.
        </div>
    </div>
</div>

<script>
    window.onload = function() { window.print(); }
</script>

</body>
</html>
