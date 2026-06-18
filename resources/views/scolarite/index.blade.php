@extends('layouts.app')

@section('title', 'Gestion des Frais')
@section('page-title', 'Frais scolaires')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Differents frais
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#"  data-toggle="modal" data-target="#fraisModal" onclick="openAddModal()" class="btn btn-outline-info">
                    ➕ Nouveau frais
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les frais
                    </li>
                </ol>
            </div>
        </div>


        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white fw-bold">
                📌 Liste des frais
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Libellé</th>
                        <th>Type</th>
                        <th>Ajouté le</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($frais as $f)
                        <tr>
                            <td class="fw-bold text-secondary">#{{ $f->id }}</td>
                            <td>{{ $f->libelle }}</td>
                            <td><span class="badge bg-info text-dark">{{ ucfirst($f->type) }}</span></td>
                            <td>
                                <span class="badge bg-light text-muted">{{ $f->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1 rounded-pill"
                                        onclick="openEditModal({{ $f->id }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete({{ $f->id }})" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                <form id="delete-form-{{ $f->id }}" action="{{ route('frais.destroy', $f) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <span class="text-muted">🚫 Aucun frais défini.</span>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="fraisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="fraisModalLabel"><i class="fas fa-plus"></i> Nouveau frais</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="fraisForm" method="POST" action="{{ route('frais.store') }}">
                        @csrf
                        <input type="hidden" id="fraisId" name="id" value="">
                        <div class="mb-3">
                            <label for="libelle" class="form-label">Libellé</label>
                            <input type="text" class="form-control" name="libelle" id="libelle" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de frais</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">-- Sélectionnez --</option>
                                <option value="scolarite">Scolarité</option>
                                <option value="inscription">Inscription</option>
                                <option value="transport">Transport</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" form="fraisForm" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAddModal() {
            document.getElementById('fraisModalLabel').innerHTML = '<i class="fas fa-plus"></i> Nouveau frais';
            const form = document.getElementById('fraisForm');
            form.action = "{{ route('frais.store') }}";
            form.querySelector('input[name="_method"]')?.remove();
            document.getElementById('libelle').value = '';
            document.getElementById('type').value = '';
            var modal = new bootstrap.Modal(document.getElementById('fraisModal'));
            modal.show();
        }

        function openEditModal(id) {
            fetch('/frais/' + id + '/edit')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('fraisModalLabel').innerHTML = '<i class="fas fa-edit"></i> Modifier le frais #' + id;
                    const form = document.getElementById('fraisForm');
                    form.action = "/frais/" + id;
                    if (!form.querySelector('input[name="_method"]')) {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_method';
                        input.value = 'PUT';
                        form.appendChild(input);
                    }
                    document.getElementById('libelle').value = data.libelle;
                    document.getElementById('type').value = data.type;
                    var modal = new bootstrap.Modal(document.getElementById('fraisModal'));
                    modal.show();
                })
                .catch(err => {
                    console.error(err);
                    alert('Impossible de charger le frais.');
                });
        }

        function confirmDelete(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Supprimer ?',
                    text: "Voulez-vous vraiment supprimer ce frais ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            } else {
                if (confirm('Supprimer ce frais ?')) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        }
    </script>
@endpush
