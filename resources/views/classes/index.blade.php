@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Classes {{ $anneeActive ? $anneeActive->nom : ' ' }}
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal"
                   data-target="#classeModal"
                   onclick="openAddClasse()" class="btn btn-outline-info">
                    ➕ Nouvelle classe
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Gestion des Classes
                    </li>
                </ol>
            </div>
        </div>

        {{-- ✅ Grid Classes --}}
        <div class="row g-3">
            @foreach($classes as $classe)
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-card">
                        <div class="card-body">
                            <h5 class="fw-bold text-truncate mb-2">
                                <i class="fas fa-chalkboard"></i> {{ $classe->nom }}
                            </h5>
                            <p class="mb-1"><i class="fas fa-layer-group text-primary"></i> <strong>Niveau:</strong> {{ ucfirst($classe->niveau->nom) }}</p>
                            <p class="mb-1"><i class="fas fa-users text-success"></i> <strong>Effectif:</strong> {{ $classe->inscriptions_count }} élève(s)</p>
                            <p class="mb-0"><i class="fas fa-calendar-alt text-info"></i> <strong>Année:</strong> {{ $classe->annee?->nom ?? '-' }}</p>

                            <p>
                                Type de Découpage :
                                @php
                                    $badgeClass = match($classe->type_decoupage) {
                                        'trimestre' => 'bg-primary',
                                        'semestre'  => 'bg-warning',
                                        default     => 'bg-secondary',
                                    };
                                @endphp

                                <span class="badge {{ $badgeClass }}">
        {{ $classe->type_decoupage ?? 'Non défini' }}
    </span>
                            </p>


                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between py-2 border-top">
                            <button class="btn btn-sm btn-outline-warning rounded-pill"
                                    data-toggle="modal"
                                    data-target="#classeModal"
                                    onclick="openEditClasse({{ $classe }})">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-outline-danger rounded-pill"
                                    data-toggle="modal"
                                    data-target="#confirmDelete{{ $classe->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <a href="{{ route('elevesshow', $classe->id) }}" class="btn btn-sm btn-outline-info rounded-pill">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ✅ Modal Suppression --}}
                <div class="modal fade" id="confirmDelete{{ $classe->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-3 shadow-sm border-0">
                            <div class="modal-header bg-danger text-white rounded-top-3">
                                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle"></i> Confirmer</h5>
                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <p>Voulez-vous vraiment supprimer la classe <strong>{{ $classe->nom }}</strong> ?</p>
                                <small class="text-muted">Cette action est irréversible.</small>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">Annuler</button>
                                <form action="{{ route('classes.destroy', $classe->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- ✅ Modal Ajout/Edition Classe --}}
        <div class="modal fade" id="classeModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="classeForm" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                    @csrf

                    <input type="hidden" id="classe_id" name="id">

                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title fw-bold">Nouvelle Classe</h5>
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="form-group mb-3">
                            <label class="fw-semibold">Nom de la classe</label>
                            <input type="text" name="nom" id="nom" class="form-control shadow-sm" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-semibold">Niveau</label>
                            <select name="niveau_id" id="niveau" class="form-select shadow-sm" required>
                                <option value="">--Choisir le niveau--</option>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">
                                        {{ $niveau->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Type de Découpage</label>
                            <select name="type_decoupage" id="type_decoupage" class="form-select shadow-sm mb-2">
                                <option value="">-- Choisir un type --</option>
                                <option value="Trimestre">Trimestre</option>
                                <option value="Semestre">Semestre</option>
                            </select>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success rounded-pill px-4">Enregistrer</button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Modern hover card effect */
        .hover-card {
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.12);
        }
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@push('scripts')
    <script>
        function openAddClasse() {
            document.getElementById('classeForm').action = "{{ route('classes.store') }}";
            document.querySelector('#classeModal .modal-title').innerText = "Nouvelle Classe";
            document.getElementById('classeForm').reset();
        }

        function openEditClasse(classe) {

            const form = document.getElementById('classeForm');
            form.action = "/classesupdate/" + classe.id;

            document.querySelector('#classeModal .modal-title').innerText = "Modifier Classe";

            // Remplissage champs
            document.getElementById('classe_id').value = classe.id;
            document.getElementById('nom').value = classe.nom;

            // ✅ Sélection du niveau dynamique (niveau_id)
            document.getElementById('niveau').value = classe.niveau_id ?? '';

            // ✅ Ajout du _method PUT si absent
            if (!form.querySelector('input[name="_method"]')) {
                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';
                form.appendChild(method);
            }
        }


    </script>
@endpush
