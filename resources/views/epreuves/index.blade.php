{{-- upload_epreuve.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Les epreuves
            </div>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajouter des epreuves
                    </li>
                </ol>
            </div>
        </div>

        <!-- Alertes -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Formulaire Upload -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('epreuves.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-select" required>
                                <option value="">-- Choisir une classe --</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}">{{ $classe->nom }} ({{ $classe->niveau->nom }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="type_evaluation_id" class="form-label">Type d'évaluation</label>
                            <select name="type_evaluation_id" id="type_evaluation_id" class="form-select" required>
                                <option value="">-- Choisir un type --</option>
                                @foreach($typesEvaluation as $type)
                                    <option value="{{ $type->id }}">{{ $type->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Découpage</label>
                            <select name="decoupage_id" class="form-select" required>
                                <option value="">-- Choisir une période --</option>
                                @foreach($decoupages as $decoupage)
                                    <option value="{{ $decoupage->id }}">
                                        {{ $decoupage->nom }} ({{ $decoupage->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="matiere_id" class="form-label fw-bold">Matière</label>

                            <select name="matiere_id" id="matiere_id" class="form-control matiere-select" required>
                                <option value="">-- Choisir une matière --</option>

                                @foreach($matieres as $matiere)
                                    <option value="{{ $matiere->id }}">
                                        {{ $matiere->nom }}  |  {{ $matiere->niveau->nom }}  |  Coef: {{ $matiere->coefficient }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="epreuve" class="form-label">Fichier de l'épreuve</label>
                            <input type="file" name="epreuve" class="form-control" required>
                            @error('epreuve')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-success">Envoyer l'épreuve</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des épreuves -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white" style="margin-bottom: 15px">
                <h5 class="mb-0">{{ $epreuves->count() }} épreuve(s)</h5>
            </div>
            <div class="card-body p-0">
                @if($epreuves->isEmpty())
                    <p class="text-center text-muted py-3">Aucune épreuve disponible.</p>
                @else
                    <table id="ListeTable" class="table table-hover mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th>Evaluation</th>
                            <th>Fichier</th>
                            <th>Ajouter par</th>
                            <th>Date d'envoi</th>
                            <th>Etat</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($epreuves as $index => $epreuve)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $epreuve->classe->nom }} |
                                    <span class="badge badge-warning">{{ $epreuve->classe->niveau->nom }} </span>
                                </td>
                                <td>{{ $epreuve->matiere->nom }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $epreuve->typeEvaluation?->nom ?? 'Type ?' }}
                                    </span>
                                    <span class="badge badge-info">
                                        {{ $epreuve->decoupage?->nom ?? 'Période ?' }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ asset($epreuve->chemin_fichier) }}" target="_blank" class="badge badge-success" title="{{ $epreuve->nom_fichier }}">
                                        <i class="fa fa-file"></i> Ouvrir
                                    </a>
                                </td>

                                <td>{{ $epreuve->users ? $epreuve->users->name : '—' }}</td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">
                                            {{ $epreuve->created_at->format('d M Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $epreuve->created_at->format('H:i') }}
                                            • {{ $epreuve->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @switch($epreuve->etat)
                                        @case('valide')
                                            <span class="badge bg-success">Valide</span>
                                            @break
                                        @case('attente')
                                            <span class="badge bg-warning text-dark">En attente</span>
                                            @break
                                        @case('a_remplacer')
                                            <span class="badge bg-danger">À remplacer</span>
                                            @break
                                        @case('compose')
                                            <span class="badge bg-info text-dark">Composé</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Inconnu</span>
                                    @endswitch
                                </td>

                                <td>
                                    @php
                                        $rolesAutorises = ['admin', 'secretaire', 'directeur'];
                                    @endphp

                                    @if(in_array(Auth::user()->role, $rolesAutorises))
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                {{ ucfirst($epreuve->etat) }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['valide', 'attente', 'a_remplacer', 'compose'] as $etat)
                                                    <li>
                                                        <form action="{{ route('epreuves.updateEtat', $epreuve->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="etat" value="{{ $etat }}">
                                                            <button class="dropdown-item">{{ ucfirst(str_replace('_', ' ', $etat)) }}</button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Bouton Options -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            ⚙️ Options
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center" data-toggle="modal" data-target="#editModal{{ $epreuve->id }}">
                                                    <i class="fas fa-pen me-2"></i> Modifier
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center text-danger" data-toggle="modal" data-target="#deleteModal{{ $epreuve->id }}">
                                                    <i class="fas fa-trash me-2"></i> Supprimer
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="modal fade" id="editModal{{ $epreuve->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $epreuve->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('epreuves.update', $epreuve->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="editModalLabel{{ $epreuve->id }}">Modifier l'épreuve</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">

                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Classe</label>
                                                            <select name="classe_id" class="form-control" required>
                                                                @foreach($classes as $classe)
                                                                    <option value="{{ $classe->id }}" {{ $classe->id == $epreuve->classe_id ? 'selected' : '' }}>
                                                                        {{ $classe->nom }} ({{ $classe->niveau->nom }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Matière</label>
                                                            <select name="matiere_id" class="form-control" required>
                                                                @foreach($matieres as $matiere)
                                                                    <option value="{{ $matiere->id }}" {{ $matiere->id == $epreuve->matiere_id ? 'selected' : '' }}>
                                                                        {{ $matiere->nom }} | {{ $matiere->niveau->nom }} | Coef: {{ $matiere->coefficient }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Type d'évaluation</label>
                                                            <select name="type_evaluation_id" class="form-control" required>
                                                                @foreach($typesEvaluation as $type)
                                                                    <option value="{{ $type->id }}" {{ $type->id == $epreuve->type_evaluation_id ? 'selected' : '' }}>
                                                                        {{ $type->nom }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Découpage</label>
                                                            <select name="decoupage_id" class="form-control" required>
                                                                @foreach($decoupages as $decoupage)
                                                                    <option value="{{ $decoupage->id }}" {{ $decoupage->id == $epreuve->decoupage_id ? 'selected' : '' }}>
                                                                        {{ $decoupage->nom }} ({{ $decoupage->type }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Fichier (laisser vide si inchangé)</label>
                                                            <input type="file" name="epreuve" class="form-control">
                                                        </div>
                                                    </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-success">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal -->
                                    <div class="modal fade" id="deleteModal{{ $epreuve->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $epreuve->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-danger">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $epreuve->id }}">⚠️ Confirmation de suppression</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer cette épreuve ? Cette action est <strong>irréversible</strong>.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('epreuves.destroy', $epreuve->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <style>
            .matiere-select {
                height: 45px;
                border-radius: 10px;
                padding-left: 15px;
                font-weight: 500;
                background-color: #fff;
            }

            .matiere-select option {
                padding: 8px 12px;
                font-size: 15px;
            }

            /* Améliore la lisibilité des séparateurs | */
            .matiere-select option {
                letter-spacing: 0.3px;
            }
        </style>

    </div>

    @push('scripts')
        <script>
            $('#ListeTable').DataTable({
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
@endsection
