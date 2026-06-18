@extends('layouts.app')

@section('title', 'Notes de la classe ' . $classe->nom)

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Notes des élèves – {{ $classe->nom }}
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('evaluations.index') }}">Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les Notes
                    </li>
                </ol>
            </div>
        </div>


        {{-- ✅ Choix de l’élève + découpage --}}
        <div class="card shadow-sm rounded mb-4">
            <div class="card-body">
                <form id="filterForm">
                    <div class="row align-items-center g-3">

                        {{-- Sélection élève --}}
                        <div class="col-md-5">
                            <label for="eleveSelect" class="form-label fw-semibold">Sélectionnez un élève :</label>
                            <select id="eleveSelect" class="form-select">
                                <option value="">-- Tous les élèves --</option>
                                @foreach($classe->inscriptions as $inscription)
                                    <option value="{{ $inscription->id }}">
                                        {{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sélection découpage --}}
                        <div class="col-md-5">
                            <label for="decoupageSelect" class="form-label fw-semibold">Sélectionnez le découpage :</label>
                            <select id="decoupageSelect" class="form-select">
                                <option value="">-- Choisir --</option>
                                @foreach($decoupages as $decoupage)
                                    <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- ✅ Tableau des notes --}}
        <div class="card shadow-sm rounded">
            <div class="card-body">
                <table id="notesTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Élève</th>
                        <th>Découpage</th>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Note</th>
                        <th>Date</th>
                        <th>Observation</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($evaluations as $index => $eval)
                        <tr data-eleve="{{ $eval->inscription_id }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</td>
                            <td>{{ $eval->decoupage->nom ?? '-' }}</td>
                            <td>{{ $eval->matiere->nom }}</td>
                            <td>{{ $eval->typeEvaluation->nom ?? '-' }}</td>
                            <td><span class="badge bg-success fs-6">{{ $eval->note }}/20</span></td>
                            <td>{{ \Carbon\Carbon::parse($eval->date_eval)->format('d/m/Y') }}</td>
                            <td>{{ $eval->observation ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        let table = $('#notesTable').DataTable({
            destroy: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[2, 'asc']], // tri par découpage
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            }
        });

        function toggleBulletinButton() {
            let eleveId = $('#eleveSelect').val();
            let decoupageId = $('#decoupageSelect').val();
            if (eleveId && decoupageId) {
                $('#btnBulletin').prop('disabled', false)
                    .data('eleve-id', eleveId)
                    .data('decoupage-id', decoupageId);
            } else {
                $('#btnBulletin').prop('disabled', true);
            }
        }

        // ✅ Filtrage par élève
        $('#eleveSelect').on('change', function () {
            let eleveId = $(this).val();
            if (eleveId) {
                table.rows().every(function () {
                    let show = $(this.node()).data('eleve') == eleveId;
                    $(this.node()).toggle(show);
                });
            } else {
                table.rows().every(function () {
                    $(this.node()).show();
                });
            }
            toggleBulletinButton();
        });

        // ✅ Changement découpage
        $('#decoupageSelect').on('change', function () {
            toggleBulletinButton();
        });

        // ✅ Action voir bulletin
        $('#btnBulletin').on('click', function () {
            let eleveId = $(this).data('eleve-id');
            let decoupageId = $(this).data('decoupage-id');
            if (eleveId && decoupageId) {
                window.location.href = "{{ url('bulletins/apercu') }}/" + eleveId + "/" + decoupageId;
            }
        });
    </script>
@endpush
