@extends('layouts.app')

@section('title', 'Gestion des absences')

@section('content')
    <div class="container-fluid py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des absences
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajouter des absences
                    </li>
                </ol>
            </div>
        </div>
        {{-- 🔍 FILTRES --}}
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <select name="classe_id" class="form-control">
                    <option value="">— Classe —</option>
                    @foreach($classes as $classe)
                        <option value="{{ $classe->id }}" @selected(request('classe_id')==$classe->id)>
                            {{ $classe->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="decoupage_id" class="form-control">
                    <option value="">— Découpage —</option>
                    @foreach($decoupages as $d)
                        <option value="{{ $d->id }}" @selected(request('decoupage_id')==$d->id)>
                            {{ $d->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>

        {{-- ➕ Bouton ajouter absence --}}
        <div class="mb-3 text-end">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addAbsenceModal">
                ➕ Ajouter une absence
            </button>
        </div>

        {{-- ================= MODAL AJOUT GLOBAL ================= --}}
        <div class="modal fade" id="addAbsenceModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <form method="POST" action="{{ route('absences.store') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une absence</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            {{-- Sélection élève --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label>Classe</label>
                                    <select name="classe_id" id="classeSelect" class="form-control" required>
                                        <option value="">— Sélectionner une classe —</option>
                                        @foreach($classes as $classe)
                                            <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label>Élève</label>
                                    <select name="inscription_id" id="eleveSelect" class="form-control" required>
                                        <option value="">— Sélectionner un élève —</option>
                                        {{-- Les options seront remplies via AJAX --}}
                                    </select>
                                </div>


                                <div class="col-md-4">
                                    <label>Découpage</label>
                                    <select name="decoupage_id" class="form-control" required>
                                        @foreach($decoupages as $d)
                                            <option value="{{ $d->id }}">{{ $d->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tableau dynamique --}}
                            <table class="table table-bordered absenceTable">
                                <thead class="table-light">
                                <tr>
                                    <th>Matière</th>
                                    <th>Heures</th>
                                    <th>Justifié</th>
                                    <th width="50"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <select name="absences[0][matiere_id]" class="form-control" required>
                                            @foreach($matieres as $m)
                                                <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="absences[0][heures]" class="form-control" min="0.5" step="0.5" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="absences[0][is_justified]" value="1">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm removeRow">✕</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-outline-primary addRow">➕ Ajouter une ligne</button>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- ================= FIN MODAL AJOUT GLOBAL ================= --}}

        @if($absences->isEmpty())
            <div class="alert alert-info text-center">
                Aucune absence enregistrée pour l'année scolaire <strong>{{ $anneeActive->nom }}</strong>.
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Total Heures</th>
                            <th width="150">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($absences as $eleveId => $data)
                            @php $inscription = $data['inscription']; @endphp
                            <tr>
                                <td>{{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}</td>
                                <td>{{ $inscription->classe->nom }}</td>
                                <td class="text-center">{{ $data['total_heures'] }} h</td>
                                <td class="text-center">
                                    {{-- Bouton pour voir les détails dans un modal ou page --}}
                                    <a href="{{ route('absences.show', $inscription->id) }}" class="btn btn-sm btn-info">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="mt-3">
                {{-- pagination si besoin --}}
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (e) {
            // ➕ Ajouter ligne dans modal ajout
            if (e.target.classList.contains('addRow')) {
                let table = e.target.closest('.modal-body').querySelector('.absenceTable tbody');
                let index = table.children.length;

                table.insertAdjacentHTML('beforeend', `
        <tr>
            <td>
                <select name="absences[${index}][matiere_id]" class="form-control" required>
                    @foreach($matieres as $m)
                <option value="{{ $m->id }}">{{ $m->nom }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="absences[${index}][heures]" class="form-control" min="0.5" step="0.5" required>
            </td>
            <td class="text-center">
                <input type="checkbox" name="absences[${index}][is_justified]" value="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">✕</button>
            </td>
        </tr>
        `);
            }

            // ❌ Supprimer ligne
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });

            document.addEventListener('DOMContentLoaded', function () {
            const classeSelect = document.getElementById('classeSelect');
            const eleveSelect = document.getElementById('eleveSelect');

            classeSelect.addEventListener('change', function () {
            const classeId = this.value;
            eleveSelect.innerHTML = '<option value="">Chargement...</option>';

            if (!classeId) {
            eleveSelect.innerHTML = '<option value="">— Sélectionner un élève —</option>';
            return;
        }

            fetch(`/api/eleves?classe_id=${classeId}`)
            .then(res => res.json())
            .then(data => {
            eleveSelect.innerHTML = '<option value="">— Sélectionner un élève —</option>';
            data.forEach(eleve => {
            const option = document.createElement('option');
            option.value = eleve.id;
            option.text = `${eleve.nom} ${eleve.prenom}`;
            eleveSelect.appendChild(option);
        });
        })
            .catch(() => {
            eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
        });
        });


    </script>

@endpush
