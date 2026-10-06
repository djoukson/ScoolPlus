<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des paiements - {{ $eleve->nom.' '.$eleve->prenom }}</title>
    <style>
        body {
            font-family: "Poppins", Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .receipt-container {
            background: #fff;
            max-width: 900px;
            margin: 0 auto;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px 50px;
        }

        /* ---------- HEADER ---------- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header .left {
            display: flex;
            align-items: center;
        }

        .header img {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            margin-right: 15px;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #4e73df;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
        }

        .header .right {
            text-align: right;
            font-size: 0.9rem;
        }

        /* ---------- TABLE ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }

        th {
            background: #f4f6fb;
            color: #555;
            font-weight: 600;
        }

        tbody tr:nth-child(even) { background-color: #f9f9f9; }

        /* ---------- SUMMARY ---------- */
        .summary {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .summary-box {
            flex: 1;
            background: #f4f6fb;
            padding: 15px;
        }

        .summary-box h4 {
            margin: 0 0 10px 0;
            color: #4e73df;
        }

        .summary-box p {
            margin: 3px 0;
        }

        /* ---------- FOOTER ---------- */
        .footer {
            border-top: 1px dashed #ccc;
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            font-size: 0.85rem;
            color: #777;
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">

    <!-- 🔹 En-tête -->
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
            <p><strong>Date d'impression :</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Année scolaire :</strong> {{ $anneenom }}</p>
        </div>
    </div>

    <!-- 🔹 Élève -->
    <table>
        <tr><th>Élève</th><td>{{ $eleve->nom.' '.$eleve->prenom }}</td></tr>
        <tr><th>Classe</th><td>{{ $eleve->classe->nom ?? ($eleve->inscriptions->last()->classe->nom ?? '—') }}</td></tr>
        <tr><th>Matricule</th><td>{{ $eleve->matricule ?? '—' }}</td></tr>
        <tr><th>Type d’inscription</th><td>{{ $typeInscription ?? 'Nouveau' }}</td></tr>
    </table>

    <!-- 🔹 Résumé -->
    <div class="summary">
        <div class="summary-box">
            <h4>Frais d'inscription</h4>
            @php
                $reducInscription = ($typeInscription ?? null) === 'Réinscrit'
                    ? 0
                    : ($bourse?->frais->firstWhere('libelle', "Frais d'Inscription")->pivot->pourcentage ?? 0);
            @endphp

            @if(($typeInscription ?? null) === 'Réinscrit')
                <p><strong>Exonération :</strong> frais d’inscription non dus pour un élève réinscrit.</p>
            @endif

            @if($reducInscription > 0)
                <p class="mb-1">
                    <strong class="text-warning">Réduction Bourse :</strong>
                    <span class="badge bg-warning text-dark">{{ number_format($reducInscription, 0) }} %</span>
                </p>
            @else
                <p class="mb-1"><strong>Réduction Bourse :</strong> {{ number_format($reducInscription, 0) }} %</p>
            @endif
            <p><strong>Total :</strong> {{ number_format($totalFrais, 0, ',', ' ') }} FCFA</p>
            <p><strong>Déjà payé :</strong> {{ number_format($totalPayé, 0, ',', ' ') }} FCFA</p>
            <p><strong>Restant :</strong> {{ number_format($restant, 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="summary-box">
            <h4>Scolarité</h4>
            @php
                $reducScolarite = $bourse?->frais->firstWhere('libelle', "Scolarité")->pivot->pourcentage ?? 0;
            @endphp

            @if($reducScolarite > 0)
                <p class="mb-1">
                    <strong class="text-warning">Réduction Bourse :</strong>
                    <span class="badge bg-warning text-dark">{{ number_format($reducScolarite, 0) }} %</span>
                </p>
            @else
                <p class="mb-1"><strong>Réduction Bourse :</strong> {{ number_format($reducScolarite, 0) }} %</p>
            @endif
            <p><strong>Total :</strong> {{ number_format($totalScolarite, 0, ',', ' ') }} FCFA</p>
            <p><strong>Déjà payé :</strong> {{ number_format($totalPayeScolarite, 0, ',', ' ') }} FCFA</p>
            <p><strong>Restant :</strong> {{ number_format($restantScolarite, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <!-- 🔹 Tableau -->
    <h3 style="margin-top: 30px; color: #4e73df;">Détails des paiements</h3>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Frais</th>
            <th>Classe</th>
            <th>Montant Payé</th>
            <th>Date</th>
            <th>Mode</th>
        </tr>
        </thead>
        <tbody>
        @foreach($eleve->paiements->where('annee_id', $annee->id) as $paiement)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $paiement->frais->libelle ?? '—' }}</td>
                <td>{{ $paiement->classe->nom ?? '—' }}</td>
                <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} FCFA</td>
                <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                <td>{{ ucfirst($paiement->mode_paiement) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- 🔹 Pied -->
    <div class="footer">
        Imprimé par : <strong>{{ auth()->user()->nom ?? auth()->user()->name }}</strong><br>
        Ce document a été généré automatiquement par le système de gestion de l’école.
    </div>
</div>

<script>
    window.onload = function() { window.print(); }
</script>

</body>
</html>
