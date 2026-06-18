@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Annees scolaires
            </div>
            {{-- Bouton Liste des classes --}}

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les Annees scolaires
                    </li>
                </ol>
            </div>
        </div>
        {{-- Formulaire d'ajout --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                Ajouter une année scolaire
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('annees.store') }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Nom de l’année</label>
                            <input type="text"
                                   name="nom"
                                   id="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   placeholder="2025 - 2026"
                                   value="{{ old('nom') }}"
                                   required>
                            @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date de début</label>
                            <input type="date"
                                   name="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut') }}"
                                   required>
                            @error('date_debut')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date de fin</label>
                            <input type="date"
                                   name="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin') }}"
                                   required>
                            @error('date_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-3">

            <div class="card-body">

                {{-- Année active --}}
                @if($anneeActive)
                    <div class="alert alert-info">
                        Année scolaire active :
                        <strong>{{ $anneeActive->nom }}</strong>
                    </div>
                @endif

                {{-- Tableau --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($annees as $annee)
                            <tr>
                                <td class="fw-semibold">{{ $annee->nom }}</td>
                                <td>{{ $annee->date_debut->format('d/m/Y') }}</td>
                                <td>{{ $annee->date_fin->format('d/m/Y') }}</td>

                                <td>
                                    @if($annee->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end">

                                    @if(!$annee->active)
                                        <button type="button"
                                                class="btn btn-sm btn-success"
                                                data-toggle="modal"
                                                data-target="#activateModal{{ $annee->id }}">
                                            Activer
                                        </button>
                                    @endif



                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                data-toggle="modal"
                                                data-target="#deleteModal{{ $annee->id }}">
                                            Supprimer
                                        </button>


                                </td>
                            </tr>

                            <div class="modal fade" id="activateModal{{ $annee->id }}" tabindex="-1" aria-labelledby="activateModalLabel{{ $annee->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title" id="activateModalLabel{{ $annee->id }}">Confirmer l’activation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>

                                        <div class="modal-body">
                                            ⚠️ Voulez-vous activer l’année scolaire <strong>{{ $annee->nom }}</strong> ?<br>
                                            L’année actuellement active sera automatiquement désactivée.
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <a href="{{ route('annees.change', $annee->id) }}" class="btn btn-success">Confirmer</a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="deleteModal{{ $annee->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $annee->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $annee->id }}">Confirmer la suppression</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>

                                        <div class="modal-body">
                                            ⚠️ Voulez-vous vraiment supprimer l’année scolaire <strong>{{ $annee->nom }}</strong> ?<br>
                                            Cette action est irréversible.
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>

                                            <form method="POST" action="{{ route('annees.destroy', $annee) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Confirmer</button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Aucune année scolaire enregistrée
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
        <script>
            document.querySelector('[name="date_debut"]').addEventListener('change', autoNom);
            document.querySelector('[name="date_fin"]').addEventListener('change', autoNom);

            function autoNom() {
            const d1 = document.querySelector('[name="date_debut"]').value;
            const d2 = document.querySelector('[name="date_fin"]').value;
            if (d1 && d2) {
            document.querySelector('[name="nom"]').value =
            d1.substring(0,4) + ' - ' + d2.substring(0,4);
        }
        }
    </script>

@endsection
