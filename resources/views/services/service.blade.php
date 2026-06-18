@extends('layouts.app')

@section('title', 'Gestion des Services')
@section('page-title', 'Services')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Liste des services
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#serviceModal" onclick="openAddModal()"   class="btn btn-outline-info">
                    ➕  Nouveau service
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les services
                    </li>
                </ol>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white fw-bold">
                📌 Services disponibles
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Libellé</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Année scolaire</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($services as $s)
                        <tr>
                            <td class="fw-bold text-secondary">#{{ $s->id }}</td>
                            <td>{{ $s->libelle }}</td>
                            <td>{{ $s->description }}</td>
                            <td>{{ number_format($s->montant, 2) }} FCFA</td>
                            <td>{{ $s->annee->nom ?? '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1 rounded-pill"
                                        onclick="openEditModal({{ $s->id }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete({{ $s->id }})" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                <form id="delete-form-{{ $s->id }}" action="{{ route('services.destroy', $s) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <span class="text-muted">🚫 Aucun service défini.</span>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Service -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="serviceModalLabel"><i class="fas fa-plus"></i> Nouveau service</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="serviceForm" method="POST" action="{{ route('services.store') }}">
                        @csrf
                        <input type="hidden" id="serviceId" name="id" value="">
                        <div class="mb-3">
                            <label for="libelle" class="form-label">Libellé</label>
                            <input type="text" class="form-control" name="libelle" id="libelle" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant</label>
                            <input type="number" class="form-control" name="montant" id="montant" required>
                        </div>
                        <div class="mb-3">
                            <label for="annee_id" class="form-label">Année scolaire</label>
                            <select class="form-select" name="annee_id" id="annee_id" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" form="serviceForm" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openAddModal() {
            document.getElementById('serviceModalLabel').innerHTML = '<i class="fas fa-plus"></i> Nouveau service';
            const form = document.getElementById('serviceForm');
            form.action = "{{ route('services.store') }}";
            form.querySelector('input[name="_method"]')?.remove();
            document.getElementById('libelle').value = '';
            document.getElementById('description').value = '';
            document.getElementById('montant').value = '';
            document.getElementById('annee_id').value = '';
            new bootstrap.Modal(document.getElementById('serviceModal')).show();
        }

        function openEditModal(id) {
            fetch('/services/' + id + '/edit')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('serviceModalLabel').innerHTML = '<i class="fas fa-edit"></i> Modifier le service #' + id;
                    const form = document.getElementById('serviceForm');
                    form.action = "/services/" + id;
                    if (!form.querySelector('input[name="_method"]')) {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_method';
                        input.value = 'PUT';
                        form.appendChild(input);
                    }
                    document.getElementById('libelle').value = data.libelle;
                    document.getElementById('description').value = data.description;
                    document.getElementById('montant').value = data.montant;
                    document.getElementById('annee_id').value = data.annee_id;
                    new bootstrap.Modal(document.getElementById('serviceModal')).show();
                })
                .catch(err => { console.error(err); alert('Impossible de charger le service.'); });
        }

        function confirmDelete(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Supprimer ?',
                    text: "Voulez-vous vraiment supprimer ce service ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => { if (result.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
            } else {
                if (confirm('Supprimer ce service ?')) document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endpush
