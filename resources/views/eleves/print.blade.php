

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier Élève - {{ $eleve->nom }} {{ $eleve->prenom }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { background-color: #f8f9fa; }
        .full-width { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .card { border-radius: 1rem; margin-bottom: 1rem; }
        .shadow-lg { box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
        .badge { font-size: 0.85rem; }
        .bg-gradient-primary { background: linear-gradient(135deg,#0d6efd,#0a58ca); }
        .bg-gradient-success { background: linear-gradient(135deg,#198754,#157347); }
        .bg-gradient-info { background: linear-gradient(135deg,#0dcaf0,#0bb4cc); }
        @media print {
            button { display: none !important; }
            body { background: #fff !important; }
        }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body class="full-width">

<div class="container-fluid py-3 full-width">

    <!-- Bouton Imprimer -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimer le dossier complet
        </button>
    </div>

    <!-- Profil élève -->
    <div class="card shadow-lg full-width" style="background: linear-gradient(135deg,#0c2325,#0661da);margin-bottom: 10px !important;">
        <div class="row g-0 align-items-center p-4">
            <div class="col-md-3 text-center">
                @php
                    $imagePath = $eleve->sexe == 'M' ? asset('dist/img/man.png') : asset('dist/img/woman.png');
                @endphp
                <img src="{{ $eleve->imglink ? asset($eleve->imglink) : $imagePath }}"
                     alt="Photo de {{ $eleve->nom }}"
                     class="img-fluid rounded-circle border border-3 border-light shadow-sm mb-3"
                     width="130" height="130" style="object-fit: cover;">
                <h5 class="fw-bold text-white text-uppercase">{{ $eleve->nom }} {{ ucfirst($eleve->prenom) }}</h5>
                <small class="text-white d-block mb-2"><i class="fas fa-id-card me-1"></i> Matricule : {{ $eleve->id }}</small>
                <span class="badge {{ $eleve->sexe == 'M' ? 'bg-primary' : 'bg-danger' }}">
                    {{ $eleve->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                </span>
            </div>
            <div class="col-md-9">
                <div class="row g-3">
                    <!-- Classe -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-white shadow-sm d-flex align-items-center gap-3">
                            <i class="fas fa-school fs-3 text-primary"></i>
                            <div>
                                <small class="text-muted">Classe</small>
                                <div class="fw-semibold fs-6">{{ $eleve->classeActuelle?->classe?->nom ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Date de naissance -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-white shadow-sm d-flex align-items-center gap-3">
                            <i class="fas fa-birthday-cake fs-3 text-primary"></i>
                            <div>
                                <small class="text-muted">Date de naissance</small>
                                <div class="fw-semibold fs-6">{{ $eleve->date_naissance ? $eleve->date_naissance->format('d/m/Y') : '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Adresse -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-white shadow-sm d-flex align-items-center gap-3">
                            <i class="fas fa-map-marker-alt fs-3 text-primary"></i>
                            <div>
                                <small class="text-muted">Adresse</small>
                                <div class="fw-semibold fs-6">{{ $eleve->adresse ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Nationalité -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-white shadow-sm d-flex align-items-center gap-3">
                            <i class="fas fa-flag fs-3 text-primary"></i>
                            <div>
                                <small class="text-muted">Nationalité</small>
                                <div class="fw-semibold fs-6">{{ $eleve->nationalite ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Observation -->
                    <div class="col-md-12">
                        <div class="p-3 bg-light rounded-3 shadow-sm">
                            <i class="fas fa-sticky-note text-primary me-2"></i>
                            <span class="fw-semibold">Observation</span>
                            <p class="mb-0 text-muted fst-italic">
                                {{ $eleve->observation ?: 'Aucune observation enregistrée.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parents -->
    <div class="card shadow-lg full-width" style="margin-bottom: 10px !important;">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-users me-2"></i> Informations des Parents
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="p-3 border rounded shadow-sm bg-white">
                        <h6 class="text-primary"><i class="fas fa-male me-2"></i>Père</h6>
                        <table class="table table-borderless mb-0 small">
                            <tr><th>Nom complet :</th><td>{{ $parent->pere_nom ?? '—' }}</td></tr>
                            <tr><th>Téléphone :</th><td>{{ $parent?->pere_tel ?? '—' }}</td></tr>
                            <tr><th>Profession :</th><td>{{ $parent?->pere_profession ?? '—' }}</td></tr>
                            <tr><th>Email :</th><td>{{ $parent?->pere_email ?? '—' }}</td></tr>
                            <tr><th>Adresse :</th><td>{{ $parent?->pere_adresse ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-3 border rounded shadow-sm bg-white">
                        <h6 class="text-danger"><i class="fas fa-female me-2"></i>Mère</h6>
                        <table class="table table-borderless mb-0 small">
                            <tr><th>Nom complet :</th><td>{{ $parent?->mere_nom ?? '—' }}</td></tr>
                            <tr><th>Téléphone :</th><td>{{ $parent?->mere_tel ?? '—' }}</td></tr>
                            <tr><th>Profession :</th><td>{{ $parent?->mere_profession ?? '—' }}</td></tr>
                            <tr><th>Email :</th><td>{{ $parent?->mere_email ?? '—' }}</td></tr>
                            <tr><th>Adresse :</th><td>{{ $parent?->mere_adresse ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bourses -->
    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-success text-white">
            <i class="fas fa-hand-holding-usd me-2"></i> Bourses
        </div>
        <div class="card-body">
            @if($eleve->attributions->count())
                @foreach($eleve->attributions as $attrib)
                    <div class="mb-2 p-3 rounded bg-light shadow-sm">
                        <strong>{{ $attrib->bourse->nom ?? '—' }}</strong>
                        - État: <span class="badge {{ $attrib->isActive() ? 'bg-success' : 'bg-secondary' }}">
                            {{ $attrib->isActive() ? 'Active' : 'Inactive' }}
                        </span>
                        - Date: {{ $attrib->date_attribution ? \Carbon\Carbon::parse($attrib->date_attribution)->format('d/m/Y') : '—' }}
                    </div>
                @endforeach
            @else
                <p class="text-muted">Aucune bourse attribuée</p>
            @endif
        </div>
    </div>

    <!-- Paiements -->
    <div class="card mb-4 shadow-lg ">
        <div class="card-header bg-info text-white">
            <i class="fas fa-money-bill-wave me-2"></i> Paiements
        </div>
        <div class="card-body">
            <h5 class="text-primary fw-bold mt-4">Paiements pour la classe actuelle</h5>
            @if($paiementsActuels->count())
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                    <tr>
                        <th>Frais</th>
                        <th>Montant payé</th>
                        <th>Date</th>
                        <th>Mode de paiement</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($paiementsActuels as $paiement)
                        <tr>
                            <td>{{ $paiement->frais?->libelle ?? '—' }}</td>
                            <td>{{ number_format($paiement->montant_paye, 2) }} FCFA</td>
                            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $paiement->mode_paiement ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Aucun paiement enregistré pour cette classe.</p>
            @endif

        </div>
    </div>

    <!-- Absences / Retards -->
    <div class="card shadow-sm " style="margin-bottom: 10px">
        <div class="card-header bg-gradient-primary text-white fw-bold">
            <i class="fas fa-calendar-times me-2"></i> Absences / Retards
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Nombre d'heures</th>
                        <th>Justifié</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($eleve->absences as $absence)
                        <tr class="{{ $absence->is_justified ? '' : 'table-warning' }}">
                            <td>{{ \Carbon\Carbon::parse($absence->date_absence)->format('d/m/Y') }}</td>
                            <td>{{ $absence->type ?? 'Absence' }}</td>
                            <td>{{ $absence->heures ?? '0' }}</td>
                            <td>
                                @if($absence->is_justified)
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Oui</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Non</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted fst-italic">
                                Aucune absence enregistrée
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Notes / Moyennes -->
    <div class="card mb-4 shadow-lg rounded-4 border-0">
        <div class="card-header bg-gradient-primary text-white fw-bold">
            <i class="fas fa-chart-line me-2"></i> Notes et Moyennes par Découpage
        </div>
        <div class="card-body p-3">
            @if($eleve->notes->count())
                @foreach($eleve->notes->groupBy('decoupage_id') as $decoupageId => $notesDecoupage)
                    @php
                        $decoupage = $notesDecoupage->first()->decoupage;
                        $typesEvaluation = $notesDecoupage->pluck('typeEvaluation.nom')->unique();
                    @endphp

                    <h6 class="fw-bold mt-4 text-primary">
                        {{ $decoupage->nom ?? "Découpage #$decoupageId" }}
                    </h6>

                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Matière</th>
                            @foreach($typesEvaluation as $type)
                                <th>{{ $type }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($notesDecoupage->groupBy('matiere_id') as $matiereId => $notesMatiere)
                            <tr>
                                <td>{{ $notesMatiere->first()->matiere->nom ?? '—' }}</td>
                                @foreach($typesEvaluation as $type)
                                    @php
                                        $note = $notesMatiere->firstWhere('typeEvaluation.nom', $type);
                                    @endphp
                                    <td>{{ $note->note ?? '—' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                @endforeach
            @else
                <p class="text-muted fst-italic">Aucune note enregistrée.</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
