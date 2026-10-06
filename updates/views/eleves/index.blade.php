@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des eleves
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="{{ route('listeclasses.index') }}" class="btn btn-secondary">
                    📚 Liste des classes
                </a>
                <!-- Bouton qui ouvre le modal Ajout Élève -->
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addEleveModal">
                    ➕ Ajouter un élève
                </button>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Eleves
                    </li>
                </ol>
            </div>
        </div>




            {{-- Modal Ajout Élève --}}
            <div class="modal fade" id="addEleveModal" tabindex="-1" aria-labelledby="addEleveModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-3">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addEleveModalLabel">➕ Ajouter un élève</h5>
                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                    aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('eleves.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nom" class="form-label">Nom *</label>
                                        <input type="text" name="nom" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="prenom" class="form-label">Prénom *</label>
                                        <input type="text" name="prenom" class="form-control" required>
                                    </div>

                                    @php
                                        $today = date('Y-m-d');
                                        $twoYearsAgo = date('Y-m-d', strtotime('-2 year'));
                                    @endphp

                                    <div class="col-md-4">
                                        <label for="date_naissance" class="form-label">Date Naissance</label>
                                        <input type="date" name="date_naissance" class="form-control"
                                               max="{{ $twoYearsAgo }}" min="1950-01-01">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="lieudenaissance" class="form-label">Lieu de Naissance</label>
                                        <input type="text" name="lieudenaissance" class="form-control"
                                               value="">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="sexe" class="form-label">Sexe</label>
                                        <select name="sexe" class="form-select">
                                            <option value="">-- Choisir --</option>
                                            <option value="M">Masculin</option>
                                            <option value="F">Féminin</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="classe_id" class="form-label">Classe</label>
                                        <select name="classe_id" class="form-select">
                                            <option value="" selected disabled>-- Sélectionner --</option>
                                            @foreach($classes as $classe)
                                                <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <input type="text" name="adresse" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nationalite" class="form-label"> Nationalité</label>
                                        <input type="text"
                                               name="nationalite"
                                               id="nationalite"
                                               class="form-control"
                                               placeholder="Ex : Togolaise">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="observation" class="form-label"> Observation</label>
                                        <textarea name="observation"
                                                  id="observation"
                                                  class="form-control"
                                                  rows="3"
                                                  placeholder="Remarques particulières concernant l'élève..."></textarea>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="tuteur_nom" class="form-label">Nom & Prénom du Tuteur</label>
                                        <input type="text" name="tuteur_nom" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tuteur_tel" class="form-label"> Téléphone du Tuteur</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+228</span>
                                            <input type="tel" name="tuteur_tel" id="tuteur_tel" class="form-control"
                                                   placeholder="90 12 34 56"
                                                   pattern="[0-9]{8}" maxlength="8">
                                        </div>
                                        <small class="text-muted">Format : 8 chiffres (ex: 90123456)</small>
                                    </div>
                                    {{-- ---------- Mensurations Élève ----------
                                    <div class="col-md-4">
                                        <label for="taille_eleve" class="form-label">📏 Taille de l'élève (cm)</label>
                                        <input type="text"
                                               name="taille_eleve"
                                               id="taille_eleve"
                                               class="form-control"
                                               placeholder="Ex: 135 cm">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="pointure_chaussure" class="form-label">👟 Pointure</label>
                                        <input type="text"
                                               name="pointure_chaussure"
                                               id="pointure_chaussure"
                                               class="form-control"
                                               placeholder="Ex: 32">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="taille_habit" class="form-label">👕 Taille d'habit</label>
                                        <input type="text"
                                               name="taille_habit"
                                               id="taille_habit"
                                               class="form-control"
                                               placeholder="Ex: S, M, L ou 10 ans">
                                    </div>
                                    --}}



                                </div>
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-success">✅ Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        {{-- Recherche --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('eleves.index') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text bg-light">🔍</span>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="form-control" placeholder="Rechercher un élève (nom, prénom, matricule)...">
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-primary w-100" type="submit">Rechercher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tableau des élèves --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Liste des élèves ({{ $eleves->total() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>
                                <a href="{{ route('eleves.index', ['sort' => 'matricule', 'direction' => request('direction') === 'asc' && request('sort') === 'matricule' ? 'desc' : 'asc']) }}">
                                    Matricule
                                    <i class="fas fa-sort{{ request('sort') === 'matricule' ? '-' . (request('direction') === 'asc' ? 'up' : 'down') : '' }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('eleves.index', ['sort' => 'nom', 'direction' => request('direction') === 'asc' && request('sort') === 'nom' ? 'desc' : 'asc']) }}">
                                    Nom
                                    <i class="fas fa-sort{{ request('sort') === 'nom' ? '-' . (request('direction') === 'asc' ? 'up' : 'down') : '' }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('eleves.index', ['sort' => 'prenom', 'direction' => request('direction') === 'asc' && request('sort') === 'prenom' ? 'desc' : 'asc']) }}">
                                    Prénom
                                    <i class="fas fa-sort{{ request('sort') === 'prenom' ? '-' . (request('direction') === 'asc' ? 'up' : 'down') : '' }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('eleves.index', ['sort' => 'date_naissance', 'direction' => request('direction') === 'asc' && request('sort') === 'date_naissance' ? 'desc' : 'asc']) }}">
                                    Date Naissance
                                    <i class="fas fa-sort{{ request('sort') === 'date_naissance' ? '-' . (request('direction') === 'asc' ? 'up' : 'down') : '' }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('eleves.index', ['sort' => 'sexe', 'direction' => request('direction') === 'asc' && request('sort') === 'sexe' ? 'desc' : 'asc']) }}">
                                    Sexe
                                    <i class="fas fa-sort{{ request('sort') === 'sexe' ? '-' . (request('direction') === 'asc' ? 'up' : 'down') : '' }}"></i>
                                </a>
                            </th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                        </thead>


                        <tbody>
                        @forelse($eleves as $eleve)
                            <tr>
                                <td>{{ $eleve->id }}</td>
                                <td class="fw-bold">{{ strtoupper($eleve->nom) }}</td>
                                <td>{{ ucfirst($eleve->prenom) }}</td>
                                <td>{{ $eleve->date_naissance ? $eleve->date_naissance->format('d/m/Y') : '-' }}</td>
                                <td>
                            <span class="badge {{ $eleve->sexe == 'M' ? 'bg-primary' : 'bg-pink' }}">
                                {{ $eleve->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                            </span>
                                </td>
                                <td>{{ $eleve->tuteur_tel }}</td>
                                <td>
                                    <!-- Bouton Modifier -->
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#editEleve{{ $eleve->id }}">
                                        ✏️
                                    </button>

                                    {{-- Supprimer --}}
                                    <!-- Bouton qui ouvre le modal -->
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirmDeleteEleve{{ $eleve->id }}">
                                        🗑️
                                    </button>

                                    {{-- Activer/Désactiver --}}
                                    <!-- Bouton Activer/Désactiver -->
                                    @if ($eleve->statut)
                                        <button type="button" class="btn btn-warning btn-sm"
                                                data-toggle="modal" data-target="#toggleEleve{{ $eleve->id }}">
                                            🚫
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm"
                                                data-toggle="modal" data-target="#toggleEleve{{ $eleve->id }}">
                                            ✅
                                        </button>
                                    @endif

                                </td>
                            </tr>
                            <!-- Modal Confirmation Suppression Élève -->
                            <div class="modal fade" id="confirmDeleteEleve{{ $eleve->id }}" tabindex="-1" aria-labelledby="confirmDeleteEleveLabel{{ $eleve->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow border-0 rounded-3">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="confirmDeleteEleveLabel{{ $eleve->id }}">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Confirmation
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment supprimer l'élève <strong>{{ $eleve->nom }} {{ $eleve->prenom }}</strong> ?
                                            <br>
                                            <small class="text-muted">Cette action est irréversible.</small>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>

                                            <form action="{{ route('eleves.destroy', $eleve->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    Oui, Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Activer/Désactiver -->
                            <div class="modal fade" id="toggleEleve{{ $eleve->id }}" tabindex="-1" aria-labelledby="toggleEleveLabel{{ $eleve->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">

                                        <!-- Header -->
                                        <div class="modal-header {{ $eleve->statut ? 'bg-warning' : 'bg-success' }} text-white">
                                            <h5 class="modal-title" id="toggleEleveLabel{{ $eleve->id }}">
                                                <i class="bi {{ $eleve->statut ? 'bi-person-dash-fill' : 'bi-person-check-fill' }}"></i>
                                                {{ $eleve->statut ? 'Désactiver un élève' : 'Activer un élève' }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                                        </div>

                                        <!-- Body -->
                                        <div class="modal-body">
                                            Voulez-vous vraiment
                                            <strong class="{{ $eleve->statut ? 'text-warning' : 'text-success' }}">
                                                {{ $eleve->statut ? 'désactiver' : 'activer' }}
                                            </strong>
                                            l’élève <strong>{{ $eleve->nom }} {{ $eleve->prenom }}</strong> ?
                                        </div>

                                        <!-- Footer -->
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>

                                            <form action="{{ route('eleves.toggle', $eleve->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $eleve->statut ? 'btn-warning' : 'btn-success' }}">
                                                    Oui, {{ $eleve->statut ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Modifier Élève -->
                            <div class="modal fade" id="editEleve{{ $eleve->id }}" tabindex="-1"
                                 aria-labelledby="editEleveLabel{{ $eleve->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="editEleveLabel{{ $eleve->id }}">
                                                ✏️ Modifier l’élève
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                    data-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="{{ route('eleves.update', $eleve->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label for="nom" class="form-label">Nom *</label>
                                                        <input type="text" name="nom" class="form-control"
                                                               value="{{ $eleve->nom }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="prenom" class="form-label">Prénom *</label>
                                                        <input type="text" name="prenom" class="form-control"
                                                               value="{{ $eleve->prenom }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="date_naissance" class="form-label">Date Naissance</label>
                                                        <input type="date" name="date_naissance" class="form-control"
                                                               value="{{ $eleve->date_naissance?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="lieudenaissance" class="form-label">Lieu de Naissance</label>
                                                        <input type="text" name="lieudenaissance" class="form-control"
                                                               value="{{ $eleve->lieudenaissance }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="sexe" class="form-label">Sexe</label>
                                                        <select name="sexe" class="form-select">
                                                            <option value="M" {{ $eleve->sexe == 'M' ? 'selected' : '' }}>Masculin</option>
                                                            <option value="F" {{ $eleve->sexe == 'F' ? 'selected' : '' }}>Féminin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="classe_id" class="form-label">Classe</label>
                                                        <select name="classe_id" class="form-select">
                                                            @foreach($classes as $classe)
                                                                <option value="" selected disabled>-- Sélectionner --</option>
                                                                <option value="{{ $classe->id }}"
                                                                    {{ $eleve->classeActuelle?->classe?->id == $classe->id ? 'selected' : '' }}>
                                                                    {{ $classe->nom }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="adresse" class="form-label">Adresse</label>
                                                        <input type="text" name="adresse" class="form-control"
                                                               value="{{ $eleve->adresse }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="nationalite" class="form-label"> Nationalité</label>
                                                        <input type="text"
                                                               name="nationalite"
                                                               id="nationalite"
                                                               class="form-control"
                                                               value="{{ $eleve->nationalite }}">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label for="observation" class="form-label"> Observation</label>
                                                        <textarea name="observation"
                                                                  id="observation"
                                                                  class="form-control"
                                                                  rows="3"
                                                                  placeholder="Remarques particulières concernant l'élève...">{{ $eleve->observation }}</textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="tuteur_nom" class="form-label">Nom du Tuteur</label>
                                                        <input type="text" name="tuteur_nom" class="form-control"
                                                               value="{{ $eleve->tuteur_nom }}">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label for="tuteur_tel" class="form-label">Téléphone du Tuteur</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+228</span>
                                                            <input type="tel" name="tuteur_tel" class="form-control"
                                                                   value="{{ $eleve->tuteur_tel }}"
                                                                   pattern="[0-9]{8}" maxlength="8">
                                                        </div>
                                                    </div>
                                                    {{-- ✅ Mensurations élève
                                                    <div class="col-md-4">
                                                        <label for="taille_eleve" class="form-label">Taille</label>
                                                        <input type="text" name="taille_eleve" class="form-control"
                                                               value="{{ $eleve->taille_eleve }}">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="pointure_chaussure" class="form-label">Pointure</label>
                                                        <input type="text" name="pointure_chaussure" class="form-control"
                                                               value="{{ $eleve->pointure_chaussure }}">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="taille_habit" class="form-label">Taille d’habit</label>
                                                        <input type="text" name="taille_habit" class="form-control"
                                                               value="{{ $eleve->taille_habit }}">
                                                    </div>
--}}
                                                </div>
                                                <div class="mt-3 text-end">
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-info">💾 Mettre à jour</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Aucun élève trouvé</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $eleves->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
@endsection
