@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Matières
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal"
                   data-target="#matiereModal"
                   onclick="openAddMatiere()"
                   class="btn btn-outline-info">
                    ➕ Nouvelle matière
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajout de nouvelles matières
                    </li>
                </ol>
            </div>
        </div>


{{-- ✅ Tableau moderne --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-book-open text-primary me-2"></i> Matières
                </h5>
                <small class="text-muted">{{ $matieres->count() }} matière(s) enregistrée(s)</small>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="notesListeTable" class="table table-hover align-middle mb-0">
                <thead>
                <tr class="text-uppercase text-muted small">
                    <th class="ps-4 py-3 border-0" style="letter-spacing: .04em;">Nom</th>
                    <th class="py-3 border-0" style="letter-spacing: .04em;">Sigle</th>
                    <th class="py-3 border-0" style="letter-spacing: .04em;">Niveau</th>
                    <th class="py-3 border-0" style="letter-spacing: .04em;">Coefficient</th>
                    <th class="py-3 border-0 text-end pe-4" style="letter-spacing: .04em;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($matieres as $matiere)
                    @php
                        $colors = [
                            'Primaire' => ['badge' => 'text-bg-success', 'avatar' => '#198754'],
                            'College'  => ['badge' => 'text-bg-info',    'avatar' => '#0dcaf0'],
                            'Lycee'    => ['badge' => 'text-bg-warning', 'avatar' => '#ffc107'],
                            // ajoute d'autres niveaux si besoin
                        ];

                        $niveauNom  = $matiere->niveau->nom ?? 'Inconnu';
                        $niveauInfo = $colors[$niveauNom] ?? ['badge' => 'text-bg-secondary', 'avatar' => '#6c757d'];
                        $initiale   = strtoupper(mb_substr($matiere->nom ?? '?', 0, 1));
                    @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                     style="width:38px; height:38px; background-color: {{ $niveauInfo['avatar'] }}; font-size: .9rem;">
                                    {{ $initiale }}
                                </div>
                                <span class="fw-semibold text-dark">{{ $matiere->nom }}</span>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($matiere->sigle)
                                <span class="badge rounded-pill text-bg-light border text-dark fw-normal px-3 py-2">
                                    {{ $matiere->sigle }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge rounded-pill {{ $niveauInfo['badge'] }} px-3 py-2 fw-normal">
                                {{ ucfirst($niveauNom) }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($matiere->coefficient)
                                <span class="fw-semibold">{{ $matiere->coefficient }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 py-3">
                            <div class="d-inline-flex gap-1">
                                {{-- Modifier --}}
                                <button type="button"
                                        class="btn btn-sm btn-light border rounded-3 text-warning"
                                        data-toggle="modal"
                                        data-target="#matiereModal"
                                        data-bs-toggle="tooltip"
                                        title="Modifier"
                                        onclick="openEditMatiere({{ $matiere }})">
                                    <i class="fas fa-pen"></i>
                                </button>

                                {{-- Supprimer -> ouverture du modal --}}
                                <button type="button"
                                        class="btn btn-sm btn-light border rounded-3 text-danger"
                                        data-toggle="modal"
                                        data-target="#deleteConfirmModal"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer"
                                        onclick="setDeleteAction('{{ route('matieres.destroy', $matiere->id) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                <i class="fas fa-book fa-2x opacity-50"></i>
                                <span>Aucune matière enregistrée.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



    {{-- ✅ Modal Ajouter/Modifier --}}
    <div class="modal fade" id="matiereModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="matiereForm" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="matiereModalTitle">Ajouter une matière</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex : Mathématiques" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Niveau</label>
                        <select name="niveau_id" id="niveau_id" class="form-select" required>
                            <option value="">--- Choisir ---</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}"
                                    {{ isset($matiere) && $matiere->niveau_id == $niveau->id ? 'selected' : '' }}>
                                    {{ ucfirst($niveau->nom) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Coefficient</label>
                        <input type="number" name="coefficient" id="coefficient" class="form-control" placeholder="Ex : 2" required>
                    </div>
                </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ Modal Confirmation Suppression --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="confirmDeleteForm" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="mb-0">Voulez-vous vraiment supprimer cette matière ?</p>
                    <small class="text-muted">Cette action est irréversible.</small>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check me-1"></i> Supprimer
                    </button>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ Script --}}
    <script>
        function openAddMatiere() {
            document.getElementById('matiereForm').action = "{{ route('matieres.store') }}";
            document.getElementById('formMethod').value = "POST";
            document.getElementById('matiereModalTitle').innerText = "Ajouter une matière";
            document.getElementById('nom').value = "";
            document.getElementById('niveau').value = "";
            document.getElementById('coefficient').value = "";
        }

        function openEditMatiere(matiere) {
            document.getElementById('matiereForm').action = "/matieres/" + matiere.id;
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('matiereModalTitle').innerText = "Modifier la matière";
            document.getElementById('nom').value = matiere.nom;
            // document.getElementById('niveau').value = matiere.niveau.nom;
            document.getElementById('coefficient').value = matiere.coefficient ?? "";
        }

        function setDeleteAction(actionUrl) {
            document.getElementById('confirmDeleteForm').action = actionUrl;
        }



    </script>
@endsection

@push('styles')
    <style>
        /* Bouton gradient moderne */
        .btn-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            border: none;
            color: #fff;
        }
        .btn-gradient-primary:hover {
            opacity: 0.9;
            color: #fff;
        }

        /* Modal stylé */
        .modal-content {
            border-radius: 1rem;
        }

        /* Ombre tableau */
        table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: .5px;
        }
    </style>
@endpush
@push('scripts')
    <script>
        $('#notesListeTable').DataTable({
            destroy: true,
            responsive: true,
            autoWidth: false,
            pageLength: 50,
            order: [[0, "desc"]], // tri sur la première colonne (id ou matricule)
            deferRender: true,
            language: {
                url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
            }
        });

    </script>
    {{-- Active les tooltips Bootstrap sur les boutons d'action --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>
@endpush
