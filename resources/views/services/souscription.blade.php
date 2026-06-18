@extends('layouts.app')

@section('title', 'Souscriptions')
@section('page-title', 'Souscriptions aux services')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Les souscriptions
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#souscriptionModal" onclick="openAddModal()" class="btn btn-outline-info">
                    ➕  Nouvelle souscription
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les souscriptions
                    </li>
                </ol>
            </div>
        </div>


        <div class="card shadow-sm border-0 rounded-3">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Service</th>
                        <th>Montant</th>
                        <th>Année scolaire</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($souscriptions as $sub)
                        @php
                            // On récupère l'inscription de l'élève pour l'année scolaire active
                            $inscription = $sub->eleve->inscriptions->first();
                            $classe = $inscription->classe ?? null;
                            $anneeService = $sub->service->annee ?? null;
                        @endphp
                        <tr>
                            <td class="fw-bold text-secondary">#{{ $sub->id }}</td>
                            <td>{{ $sub->eleve->nom }} {{ $sub->eleve->prenom }}</td>
                            <td>{{ $classe->nom ?? '-' }}</td>
                            <td>{{ $sub->service->libelle }}</td>
                            <td>{{ number_format($sub->service->montant, 2) }} FCFA</td>
                            <td>{{ $anneeService->nom ?? '-' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete('{{ route('services.destroySouscription', $sub->id) }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                            </td>
                        </tr>
                        <!-- Modal Confirmation Suppression -->
                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Êtes-vous sûr de vouloir supprimer cette souscription ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <form id="deleteForm" method="POST" action="">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <span class="text-muted">🚫 Aucune souscription pour le moment.</span>
                            </td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add Souscription -->
    <div class="modal fade" id="souscriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Nouvelle souscription</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="souscriptionForm" method="POST" action="{{ route('services.storeSouscription') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select class="form-select" name="classe_id" id="classe_id" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="eleve_id" class="form-label">Élève</label>
                            <select class="form-select" name="eleve_id" id="eleve_id" required>
                                <option value="">-- Sélectionnez une classe --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service</label>
                            <select class="form-select" name="service_id" id="service_id" required>
                                <option value="">-- Sélectionnez --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->libelle }} - {{ number_format($service->montant, 2) }} FCFA</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" form="souscriptionForm" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('eleve_id').value = '';
            document.getElementById('service_id').value = '';
            new bootstrap.Modal(document.getElementById('souscriptionModal')).show();
        }

            document.getElementById('classe_id').addEventListener('change', function() {
            const classeId = this.value;
            const eleveSelect = document.getElementById('eleve_id');

            eleveSelect.innerHTML = '<option value="">Chargement...</option>';

            if(classeId) {
            fetch(`/services/classe/${classeId}/eleves`)
            .then(res => res.json())
            .then(data => {
            let options = '<option value="">-- Sélectionnez --</option>';
            data.forEach(eleve => {
            options += `<option value="${eleve.id}">${eleve.nom} ${eleve.prenom}</option>`;
        });
            eleveSelect.innerHTML = options;
        })
            .catch(err => {
            eleveSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            console.error(err);
        });
        } else {
            eleveSelect.innerHTML = '<option value="">-- Sélectionnez une classe --</option>';
        }
        });

        function confirmDelete(url) {
            const form = document.getElementById('deleteForm');
            form.action = url; // on définit l'action du formulaire
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        }
    </script>

@endsection
