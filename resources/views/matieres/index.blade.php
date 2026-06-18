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
        <div class="card shadow-lg border-1 rounded-0">
            <div class="card-body">
                <table id="notesListeTable" class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-primary">
                    <tr>
                        <th> Nom</th>
                        <th>Sigle</th>
                        <th> Niveau</th>
                        <th> Coefficient</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($matieres as $matiere)
                        <tr>
                            <td class="fw-semibold">{{ $matiere->nom }}</td>
                            <td class="fw-semibold">{{ $matiere->sigle? : '-' }}</td>
                            @php
                                $colors = [
                                    'Primaire' => 'bg-success',
                                    'College' => 'bg-info',
                                    'Lycee' => 'bg-warning',
                                    // ajoute d'autres niveaux si besoin
                                ];

                                $niveauNom = $matiere->niveau->nom ?? 'Inconnu';
                                $badgeColor = $colors[$niveauNom] ?? 'bg-secondary'; // couleur par défaut
                            @endphp

                            <td>
                                <span class="badge {{ $badgeColor }}">
                                    {{ ucfirst($niveauNom) }}
                                </span>
                            </td>

                            <td>{{ $matiere->coefficient ?? '-' }}</td>
                            <td class="text-end">
                                {{-- Modifier --}}
                                <button class="btn btn-sm btn-outline-warning me-1"
                                        data-toggle="modal"
                                        data-target="#matiereModal"
                                        onclick="openEditMatiere({{ $matiere }})">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Supprimer -> ouverture du modal --}}
                                <button class="btn btn-sm btn-outline-danger"
                                        data-toggle="modal"
                                        data-target="#deleteConfirmModal"
                                        onclick="setDeleteAction('{{ route('matieres.destroy', $matiere->id) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i> Aucune matière enregistrée.
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
@endpush
