@extends('layouts.app')

@section('content')
    <div class="container py-4">

        @if(session('temporary_password'))
            <div class="alert alert-warning" role="alert">
                <strong>Mot de passe temporaire — copiez-le maintenant :</strong>
                <code class="user-select-all">{{ session('temporary_password') }}</code>
                <div class="small mt-1">Il ne sera affiché qu’une seule fois. Communiquez-le à l’enseignant après son activation et demandez-lui de le changer dès sa première connexion.</div>
            </div>
        @endif

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des enseignants/professeurs
            </div>
            {{-- Boutons Ajouter --}}
            <div class="mb-3 d-flex justify-content-between">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        ➕ Ajouter / Voir enseignants
                    </button>

                    <ul class="dropdown-menu">
                        {{-- Ajouter un enseignant --}}
                        <li>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addEnseignantModal">
                                ➕ Ajouter un enseignant
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        {{-- Liste enseignants par niveau --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('enseignants.liste.niveau', ['niveau' => 'primaire']) }}">
                                🏫 Enseignants Primaire
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="{{ route('enseignants.liste.niveau', ['niveau' => 'college']) }}">
                                🏫 Enseignants Collège
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="{{ route('enseignants.liste.niveau', ['niveau' => 'lycee']) }}">
                                🏫 Enseignants Lycée
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Enseignants/Professeurs
                    </li>
                </ol>
            </div>
        </div>




        <div class="modal fade" id="addEnseignantModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">➕ Ajouter un enseignant</h5>
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('enseignants.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" name="nom" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prénom *</label>
                                    <input type="text" name="prenom" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">🚻 Sexe</label>
                                    <select name="sexe" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">🏠 Adresse</label>
                                    <input type="text" name="adresse" class="form-control"
                                           placeholder="Quartier, ville">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">🩸 Groupe sanguin</label>
                                    <select name="groupesanguin" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="niveau_id" class="form-label">Niveau</label>
                                    <select name="niveau_id" id="niveau_add" class="form-select" required>
                                        <option value="">-- Sélectionner un niveau --</option>
                                        @foreach(App\Models\Niveau::all() as $niveau)
                                            <option value="{{ $niveau->id }}" {{ old('niveau_id') == $niveau->id ? 'selected' : '' }}>
                                                {{ $niveau->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Salaire mensuel (uniquement pour le primaire) --}}
                                <div class="col-md-12" id="salaire_add" style="display:none;">
                                    <label class="form-label">💵 Salaire mensuel (Primaire)</label>
                                    <input type="number" name="salaire_mensuel" class="form-control">
                                </div>


                                <div class="col-md-6">
                                    <label for="type" class="form-label">Type d’enseignant </label>
                                    <select name="type" class="form-select" >
                                        <option value="">-- Sélectionner --</option>
                                        <option value="titulaire" {{ old('type') == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                        <option value="vacataire" {{ old('type') == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                                        <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Spécialité</label>
                                    <input type="text" name="specialite" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="tuteur_tel" class="form-label">📞 Téléphone du Tuteur</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+228</span>
                                        <input type="tel" name="tel" id="tuteur_tel" class="form-control"
                                               placeholder="90 12 34 56"
                                               pattern="[0-9]{8}" maxlength="8">
                                    </div>
                                    <small class="text-muted">Format : 8 chiffres (ex: 90123456)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
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

        {{-- Tableau des enseignants --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Liste des enseignants ({{ $enseignants->total() }})</h5>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="notesListeTable" class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>Niveau</th> <!-- Nouvelle colonne -->
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($enseignants as $ens)
                            <tr>
                                <td style="text-align: center">{{ $ens->id }}</td>
                                <td>{{ strtoupper($ens->nom) }} {{ ucfirst($ens->prenom) }}</td>
                                <td>{{ $ens->niveau ? $ens->niveau->nom : '-' }}</td> <!-- Affichage du niveau -->
                                <td>{{ $ens->tel ?? '-' }}</td>
                                <td>{{ $ens->email ?? '-' }}</td>
                                <td class="text-center">
                                    @if($ens->statut)
                                        <span class="text-success" title="Actif">✅</span>
                                    @else
                                        <span class="text-danger" title="Inactif">🚫</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            ⚙️ Options
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('enseignants.show', $ens->id) }}" class="dropdown-item">
                                                    👁️ Voir détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editEnseignant{{ $ens->id }}">
                                                    ✏️ Modifier
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#toggleEnseignant{{ $ens->id }}">
                                                    {{ $ens->statut ? '🚫 Désactiver' : '✅ Activer' }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#deleteEnseignant{{ $ens->id }}">
                                                    🗑️ Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                            </tr>

                            {{-- Modal Modifier --}}
                            <div class="modal fade" id="editEnseignant{{ $ens->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">✏️ Modifier l’enseignant</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="{{ route('enseignants.update', $ens->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nom *</label>
                                                        <input type="text" name="nom" value="{{ $ens->nom }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prénom *</label>
                                                        <input type="text" name="prenom" value="{{ $ens->prenom }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">🚻 Sexe</label>
                                                        <select name="sexe" class="form-select">
                                                            <option value="">-- Sélectionner --</option>
                                                            <option value="M" {{ $ens->sexe === 'M' ? 'selected' : '' }}>
                                                                Masculin
                                                            </option>
                                                            <option value="F" {{ $ens->sexe === 'F' ? 'selected' : '' }}>
                                                                Féminin
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">🏠 Adresse</label>
                                                        <input type="text"
                                                               name="adresse"
                                                               class="form-control"
                                                               value="{{ old('adresse', $ens->adresse) }}"
                                                               placeholder="Quartier, ville">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">🩸 Groupe sanguin</label>
                                                        <select name="groupesanguin" class="form-select">
                                                            <option value="">-- Sélectionner --</option>

                                                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $groupe)
                                                                <option value="{{ $groupe }}"
                                                                    {{ old('groupesanguin', $ens->groupesanguin) === $groupe ? 'selected' : '' }}>
                                                                    {{ $groupe }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>


                                                    <div class="mb-3">
                                                        <label for="niveau_id" class="form-label">Niveau</label>
                                                        <select name="niveau_id"
                                                                class="form-select niveau-edit"
                                                                data-salaire="salaire_edit_{{ $ens->id }}">
                                                            <option value="">-- Sélectionner un niveau --</option>
                                                            @foreach(App\Models\Niveau::all() as $niveau)
                                                                <option value="{{ $niveau->id }}"
                                                                    {{ $ens->niveau_id == $niveau->id ? 'selected' : '' }}>
                                                                    {{ $niveau->nom }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 salaire-edit"
                                                         id="salaire_edit_{{ $ens->id }}"
                                                         style="display:none;">
                                                        <label class="form-label">💵 Salaire mensuel (Primaire)</label>
                                                        <input type="number"
                                                               name="salaire_mensuel"
                                                               value="{{ $ens->salaire_mensuel }}"
                                                               class="form-control">
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label for="type" class="form-label">Type d’enseignant *</label>
                                                        <select name="type" class="form-select">
                                                            <option value="">-- Sélectionner --</option>
                                                            <option value="titulaire" {{ $ens->type == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                                            <option value="vacataire" {{ $ens->type == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                                                            <option value="autre" {{ $ens->type == 'autre' ? 'selected' : '' }}>Autre</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Spécialité</label>
                                                        <input type="text" name="specialite" value="{{ $ens->specialite }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="tuteur_tel" class="form-label">📞 Téléphone du Tuteur</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+228</span>
                                                            <input type="tel" name="tel" id="tuteur_tel" class="form-control"
                                                                   placeholder="90 12 34 56"
                                                                   pattern="[0-9]{8}" maxlength="8">
                                                        </div>
                                                        <small class="text-muted">Format : 8 chiffres (ex: 90123456)</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" value="{{ $ens->email }}" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-end">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-info">💾 Mettre à jour</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Supprimer --}}
                            <div class="modal fade" id="deleteEnseignant{{ $ens->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">🗑️ Supprimer l’enseignant</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment supprimer <strong>{{ $ens->nom }} {{ $ens->prenom }}</strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <form action="{{ route('enseignants.destroy', $ens->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Activer/Désactiver --}}
                            <div class="modal fade" id="toggleEnseignant{{ $ens->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header {{ $ens->statut ? 'bg-warning' : 'bg-success' }} text-white">
                                            <h5 class="modal-title">{{ $ens->statut ? 'Désactiver' : 'Activer' }} l’enseignant</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment <strong>{{ $ens->statut ? 'désactiver' : 'activer' }}</strong> <strong>{{ $ens->nom }} {{ $ens->prenom }}</strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <form action="{{ route('enseignants.toggle', $ens->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $ens->statut ? 'btn-warning' : 'btn-success' }}">
                                                    Oui, {{ $ens->statut ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucun enseignant trouvé</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $enseignants->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function toggleSalaire(select, salaireBlock) {
                if (!select || !salaireBlock) return;

                const text = select.options[select.selectedIndex]?.text.toLowerCase() || '';

                if (text.includes('primaire')) {
                    salaireBlock.style.display = 'block';
                } else {
                    salaireBlock.style.display = 'none';
                    const input = salaireBlock.querySelector('input');
                    if (input) input.value = '';
                }
            }

            /* ========= AJOUT ========= */
            const niveauAdd = document.getElementById('niveau_add');
            const salaireAdd = document.getElementById('salaire_add');

            if (niveauAdd && salaireAdd) {
                niveauAdd.addEventListener('change', () => toggleSalaire(niveauAdd, salaireAdd));
                toggleSalaire(niveauAdd, salaireAdd);
            }

            /* ========= EDIT (MULTI MODALS) ========= */
            document.querySelectorAll('.niveau-edit').forEach(select => {
                const salaireId = select.dataset.salaire;
                const salaireBlock = document.getElementById(salaireId);

                select.addEventListener('change', () => toggleSalaire(select, salaireBlock));
                toggleSalaire(select, salaireBlock);
            });

            /* ========= DATATABLE ========= */
            $('#notesListeTable').DataTable({
                destroy: true,
                responsive: true,
                pageLength: 50,
                order: [[0, "desc"]],
                language: {
                    url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
                }
            });

        });
    </script>
@endpush
