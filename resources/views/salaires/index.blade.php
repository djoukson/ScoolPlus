@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Breadcrumb --}}
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                💰 Salaires & Rémunérations
            </div>

            {{-- Sélecteur mois + boutons PDF --}}
            <form action="{{ route('salaires.pdf') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="date_debut" value="{{ $dateDebut ?? '' }}" required>
                <input type="hidden" name="date_fin" value="{{ $dateFin ?? '' }}" required>
                <button type="submit" class="btn btn-dark mb-3">
                    <i class="fas fa-file-pdf"></i> Imprimer PDF Global
                </button>
            </form>



            {{-- Breadcrumb --}}
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Salaires & Rémunérations</li>
                </ol>
            </div>
        </div>

        {{-- Formulaire Calcul Salaires --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Calcul des salaires</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('salaires.calculer') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label>Date début</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button class="btn btn-success w-100">Calculer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Enseignants Primaire --}}
        @isset($enseignantsPrimaire)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">Enseignants - Primaire</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Niveau</th>
                            <th>Salaire</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($enseignantsPrimaire as $e)
                            <tr>
                                <td>{{ $e['nom'] }}</td>
                                <td>{{ $e['prenom'] }}</td>
                                <td>{{ $e['niveau'] }}</td>
                                <td>{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endisset

        {{-- Enseignants Collège/Lycée --}}
        @isset($enseignantsCollegeLycee)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-white">Enseignants - Collège & Lycée</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Niveau</th>
                            <th>Nombre d'heures</th>
                            <th>Heures manquee(s)</th>
                            <th>Taux horaire</th>
                            <th>Salaire</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($enseignantsCollegeLycee as $e)
                            <tr>
                                <td>{{ $e['nom'] }}</td>
                                <td>{{ $e['prenom'] }}</td>
                                <td>{{ $e['niveau'] }}</td>
                                <td>{{ $e['heures'] }}</td>
                                <td>{{ $e['heures_manquees'] }}</td>
                                <td>{{ number_format($e['taux'], 2, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endisset

        {{-- Personnel administratif --}}
        @isset($personnel)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">Personnel administratif</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Poste</th>
                            <th>Salaire</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($personnel as $p)
                            <tr>
                                <td>{{ $p['nom'] }}</td>
                                <td>{{ $p['prenom'] }}</td>
                                <td>{{ $p['poste'] }}</td>
                                <td>{{ number_format($p['salaire'], 2, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endisset
        @if(isset($grandTotalGlobal) && $grandTotalGlobal !== null)
            <div class="alert alert-success fw-bold fs-5 text-end mt-4">
                GRAND TOTAL SALAIRES :
                {{ number_format($grandTotalGlobal, 0, ',', ' ') }} FCFA
            </div>
        @endif
    </div>

    {{-- Script pour les PDF --}}
    <script>
        const basePersonnel = "{{ route('salaires.pdf', ['type'=>'personnel', 'mois'=>'__MOIS__']) }}";
        const baseEnseignant = "{{ route('salaires.pdf', ['type'=>'enseignant', 'mois'=>'__MOIS__']) }}";

        document.getElementById('pdfPersonnelBtn').addEventListener('click', function(e) {
            const mois = document.getElementById('moisPDF').value;
            if(!mois) return alert('Sélectionnez un mois');
            window.open(basePersonnel.replace('__MOIS__', mois), '_blank');
        });

        document.getElementById('pdfEnseignantBtn').addEventListener('click', function(e) {
            const mois = document.getElementById('moisPDF').value;
            if(!mois) return alert('Sélectionnez un mois');
            window.open(baseEnseignant.replace('__MOIS__', mois), '_blank');
        });
    </script>
@endsection
