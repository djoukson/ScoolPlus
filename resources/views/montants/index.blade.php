@extends('layouts.app')

@section('title', 'Gestion des Montants de Frais')
@section('page-title', 'Montants des frais par classe et année')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Differents montants de frais
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#"  data-toggle="modal" data-target="#montantModal" onclick="openAddModal()" class="btn btn-outline-info">
                    ➕ Nouveau montant
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les montants des frais
                    </li>
                </ol>
            </div>
        </div>


        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white fw-bold">
                📌 Montants définis
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Année scolaire</th>
                        <th>Classe</th>
                        <th>Frais</th>
                        <th>Montant</th>
                        <th>Ajouté le</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($montants as $m)
                        <tr>
                            <td class="fw-bold text-secondary">#{{ $m->id }}</td>
                            <td>{{ $m->annee->nom ?? '-' }}</td>
                            <td>{{ $m->classe->nom ?? '-' }}</td>
                            <td>{{ $m->frais->libelle ?? '-' }}</td>
                            <td class="fw-bold text-success">{{ number_format($m->montant, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge bg-light text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning me-1 rounded-pill"
                                        onclick="openEditModal({{ $m->id }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete({{ $m->id }})" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                <form id="delete-form-{{ $m->id }}" action="{{ route('montants_frais.destroy', $m) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <span class="text-muted">🚫 Aucun montant défini.</span>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="montantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="montantModalLabel"><i class="fas fa-plus"></i> Nouveau montant</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="montantForm" method="POST" action="{{ route('montants_frais.store') }}">
                        @csrf
                        <input type="hidden" id="montantId" name="id" value="">

                        <div class="mb-3">
                            <label for="annee_id" class="form-label">Année scolaire</label>
                            <input type="text" class="form-control" value="{{ $anneeActive->nom ?? 'Non définie' }}" disabled>

                            {{-- Champ caché pour garder la valeur lors de l’envoi du formulaire --}}
                            <input type="hidden" name="annee_id" value="{{ $anneeActive->id ?? '' }}">
                        </div>


                        <div class="mb-3">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-select" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="frais_id" class="form-label">Type de frais</label>
                            <select name="frais_id" id="frais_id" class="form-select" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($frais as $f)
                                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (FCFA)</label>
                            <input type="number" class="form-control" name="montant" id="montant" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" form="montantForm" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAddModal() {
            document.getElementById('montantModalLabel').innerHTML = '<i class="fas fa-plus"></i> Nouveau montant';
            const form = document.getElementById('montantForm');
            form.action = "{{ route('montants_frais.store') }}";
            form.querySelector('input[name="_method"]')?.remove();

            // Reset champs
            document.getElementById('classe_id').value = '';
            document.getElementById('frais_id').value = '';
            document.getElementById('montant').value = '';

            // Sélectionner l'année active automatiquement
            const anneeSelect = document.getElementById('annee_id');
            @foreach($annees as $a)
                @if($a->active == 1)
                anneeSelect.value = "{{ $a->id }}";
            @endif
            @endforeach

            var modal = new bootstrap.Modal(document.getElementById('montantModal'));
            modal.show();
        }

        function openEditModal(id) {
            fetch('/montants-frais/' + id + '/edit')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('montantModalLabel').innerHTML = '<i class="fas fa-edit"></i> Modifier le montant #' + id;
                    const form = document.getElementById('montantForm');
                    form.action = "/montants-frais/" + id;
                    if (!form.querySelector('input[name="_method"]')) {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_method';
                        input.value = 'PUT';
                        form.appendChild(input);
                    }
                    document.getElementById('annee_id').value = data.annee_id;
                    document.getElementById('classe_id').value = data.classe_id;
                    document.getElementById('frais_id').value = data.frais_id;
                    document.getElementById('montant').value = data.montant;

                    var modal = new bootstrap.Modal(document.getElementById('montantModal'));
                    modal.show();
                })
                .catch(err => {
                    console.error(err);
                    alert('Impossible de charger le montant.');
                });
        }

        function confirmDelete(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Supprimer ?',
                    text: "Voulez-vous vraiment supprimer ce montant ?",
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
                if (confirm('Supprimer ce montant ?')) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        }
    </script>
@endpush
