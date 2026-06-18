@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Notes – {{ $classe->nom }}
            </div>
            <button type="button" class="btn btn-outline-info shadow-sm" data-toggle="modal" data-target="#blocModal" style="margin-bottom: 10px">
                📋 Saisie en bloc
            </button>
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


        {{-- ================= Formulaire individuel ================= --}}
        <form action="{{ route('evaluations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="classe_id" value="{{ $classe->id }}">

            {{-- Card : Paramètres évaluation --}}
            <div class="card shadow-lg rounded-4 mb-4">
                <div class="card-header fw-bold text-primary">📌 Paramètres de l’évaluation</div>
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📘 Matière</label>
                        <select name="matiere_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($matieres as $matiere)
                                <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📑 Type</label>
                        <select name="type_evaluation_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($typesEvaluations as $type)
                                <option value="{{ $type->id }}">{{ $type->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📅 Découpage</label>
                        <select name="decoupage_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($decoupages as $decoupage)
                                <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📆 Date</label>
                        <input type="date" name="date_eval" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            {{-- Card : Notes des élèves --}}
            <div class="card shadow-lg rounded-4 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">👩‍🎓 Notes des élèves</span>
                    <button type="button" class="btn btn-sm btn-success rounded-pill" onclick="addRow()">➕ Ajouter une ligne</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="notesTable">
                            <thead class="table-light">
                            <tr>
                                <th>Élève</th>
                                <th>Note (/20)</th>
                                <th>⚙️</th>
                            </tr>
                            </thead>
                            <tbody id="notesBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-outline-success rounded-pill px-3">💾 Enregistrer toutes les notes</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ================= Liste des notes ================= --}}
        <div class="card shadow-lg rounded-4">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">📊 Notes enregistrées</h5>
                <div class="table-responsive">
                    <table id="notesListeTable" class="table table-hover align-middle">
                        <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Élève</th>
                            <th>Matière</th>
                            <th>Type</th>
                            <th>Découpage</th>
                            <th>Note</th>
                            <th>Date</th>
                            <th>⚙️ Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($evaluations as $eval)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</td>
                                <td>{{ $eval->matiere->nom }}</td>
                                <td>{{ $eval->typeEvaluation->nom }}</td>
                                <td>{{ $eval->decoupage->nom }}</td>
                                <td><span class="badge bg-info">{{ $eval->note }}/20</span></td>
                                <td>{{ $eval->date_eval }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $eval->id }}">✏️</button>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $eval->id }}">🗑️</button>
                                </td>
                            </tr>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="editModal{{ $eval->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable">
                                    <div class="modal-content rounded-4 shadow-lg">
                                        <div class="modal-header bg-warning text-white">
                                            <h5 class="modal-title">✏️ Modifier la note</h5>
                                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('evaluations.update', $eval->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <p><strong>Élève :</strong> {{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Note (/20)</label>
                                                    <input type="number" name="note" class="form-control" min="0" max="20" step="0.25" value="{{ $eval->note }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Date</label>
                                                    <input type="date" name="date_eval" class="form-control" value="{{ $eval->date_eval }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">❌ Annuler</button>
                                                <button type="submit" class="btn btn-warning rounded-pill">💾 Sauvegarder</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Delete --}}
                            <div class="modal fade" id="deleteModal{{ $eval->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow-lg">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">⚠️ Confirmation suppression</h5>
                                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Voulez-vous vraiment supprimer la note de <strong>{{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</strong> ?</p>
                                            <p class="text-danger fw-bold">Cette action est irréversible ❌</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Annuler</button>
                                            <form action="{{ route('evaluations.destroy', $eval->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger rounded-pill">🗑️ Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Aucune note enregistrée</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= Modal Saisie en bloc ================= --}}
        <div class="modal fade" id="blocModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">📋 Saisie en bloc des notes</h5>
                        <button type="button" class="btn-close" data-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('evaluations.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                        <div class="modal-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">📘 Matière</label>
                                    <select name="matiere_id" class="form-select" required>
                                        <option value="" disabled selected>-- Choisir --</option>
                                        @foreach($matieres as $matiere)
                                            <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">📑 Type</label>
                                    <select name="type_evaluation_id" class="form-select" required>
                                        <option value="" disabled selected>-- Choisir --</option>
                                        @foreach($typesEvaluations as $type)
                                            <option value="{{ $type->id }}">{{ $type->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">📅 Découpage</label>
                                    <select name="decoupage_id" class="form-select" required>
                                        <option value="" disabled selected>-- Choisir --</option>
                                        @foreach($decoupages as $decoupage)
                                            <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">📆 Date</label>
                                    <input type="date" name="date_eval" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Élève</th>
                                        <th>Note (/20)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($inscriptions as $index => $eleveInscription)
                                        <tr>
                                            <td>
                                                {{ $eleveInscription->eleve->nom }} {{ $eleveInscription->eleve->prenom }}
                                                <input type="hidden" name="notes[{{ $index }}][inscription_id]" value="{{ $eleveInscription->id }}">
                                            </td>
                                            <td>
                                                <input type="number" name="notes[{{ $index }}][note]" class="form-control" min="0" max="20" step="0.25" required>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">❌ Annuler</button>
                                <button type="submit" class="btn btn-success rounded-pill">💾 Enregistrer toutes les notes</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= JS ================= --}}
    <script>
        let rowIndex = 0;

        function addRow() {
            let tableBody = document.getElementById('notesBody');

            let newRow = document.createElement('tr');
            newRow.innerHTML = `
        <td>
            <select name="notes[${rowIndex}][inscription_id]" class="form-select" required>
                <option value="" disabled selected>-- Choisir --</option>
                @foreach($inscriptions as $inscription)
            <option value="{{ $inscription->id }}">{{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="notes[${rowIndex}][note]" class="form-control" min="0" max="20" step="0.25" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑️</button>
        </td>
        `;

            tableBody.appendChild(newRow);
            rowIndex++;
        }

        function removeRow(button) {
            button.closest('tr').remove();
        }

        // Ajouter une première ligne automatiquement au chargement
        document.addEventListener("DOMContentLoaded", () => {
            addRow();
        });
    </script>

@endsection

@push('scripts')
    <script>
        $('#notesListeTable').DataTable({
            destroy: true,
            responsive: true,
            autoWidth: false,
            pageLength: 50,
            deferRender: true,
            language: {
                url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
            }
        });
    </script>

@endpush
