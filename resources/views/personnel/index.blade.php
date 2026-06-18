@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion du personnel administratif
            </div>

            {{-- Boutons Ajouter --}}
            <div class="mb-3 d-flex justify-content-between">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        ➕ Ajouter / Voir personnel
                    </button>

                    <ul class="dropdown-menu">
                        {{-- Ajouter un membre administratif --}}
                        <li>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addPersonnelModal">
                                ➕ Ajouter un membre
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('personnel.pdf') }}" target="_blank">
                                📝 Télécharger la liste en PDF
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
                        Personnel administratif
                    </li>
                </ol>
            </div>
        </div>

        {{-- Modal Ajouter --}}
        <div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">➕ Ajouter un membre administratif</h5>
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('personnel.store') }}">
                            @csrf
                            <input type="hidden" name="personnel_type" value="administratif">
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
                                <div class="col-md-6">
                                    <label class="form-label">Poste / Fonction *</label>
                                    <input type="text" name="poste" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Salaire *</label>
                                    <input type="text" name="salaire" class="form-control" >
                                </div>

                                <div class="col-md-6">
                                    <label for="tel" class="form-label">📞 Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+228</span>
                                        <input type="tel" name="tel" id="tel" class="form-control"
                                               placeholder="90 12 34 56" pattern="[0-9]{8}" maxlength="8">
                                    </div>
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

        {{-- Tableau du personnel administratif --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Liste du personnel administratif ({{ $personnel->total() }})</h5>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="personnelTable" class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>Poste / Fonction</th>
                            <th>Salaire</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($personnel as $p)
                            <tr>
                                <td style="text-align: center">{{ $p->id }}</td>
                                <td>{{ strtoupper($p->nom) }} {{ ucfirst($p->prenom) }}</td>
                                <td>{{ $p->poste ?? '-' }}</td>
                                <td>{{ $p->salaire ?? '-' }}</td>
                                <td>{{ $p->tel ?? '-' }}</td>
                                <td>{{ $p->email ?? '-' }}</td>
                                <td class="text-center">
                                    @if($p->statut)
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
                                                <a href="{{ route('personnel.show', $p->id) }}" class="dropdown-item">
                                                    👁️ Voir détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editPersonnel{{ $p->id }}">
                                                    ✏️ Modifier
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#togglePersonnel{{ $p->id }}">
                                                    {{ $p->statut ? '🚫 Désactiver' : '✅ Activer' }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#deletePersonnel{{ $p->id }}">
                                                    🗑️ Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Modifier --}}
                            <div class="modal fade" id="editPersonnel{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">✏️ Modifier le membre administratif</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="{{ route('personnel.update', $p->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="personnel_type" value="administratif">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nom *</label>
                                                        <input type="text" name="nom" value="{{ $p->nom }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prénom *</label>
                                                        <input type="text" name="prenom" value="{{ $p->prenom }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">🚻 Sexe</label>
                                                        <select name="sexe" class="form-select">
                                                            <option value="">-- Sélectionner --</option>
                                                            <option value="M" {{ $p->sexe === 'M' ? 'selected' : '' }}>
                                                                Masculin
                                                            </option>
                                                            <option value="F" {{ $p->sexe === 'F' ? 'selected' : '' }}>
                                                                Féminin
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">🏠 Adresse</label>
                                                        <input type="text"
                                                               name="adresse"
                                                               class="form-control"
                                                               value="{{ old('adresse', $p->adresse) }}"
                                                               placeholder="Quartier, ville">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">🩸 Groupe sanguin</label>
                                                        <select name="groupesanguin" class="form-select">
                                                            <option value="">-- Sélectionner --</option>

                                                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $groupe)
                                                                <option value="{{ $groupe }}"
                                                                    {{ old('groupesanguin', $p->groupesanguin) === $groupe ? 'selected' : '' }}>
                                                                    {{ $groupe }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Poste / Fonction *</label>
                                                        <input type="text" name="poste" value="{{ $p->poste }}" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Poste / Fonction *</label>
                                                        <input type="text" name="salaire" value="{{ $p->salaire }}" class="form-control" >
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="tel" class="form-label">📞 Téléphone</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+228</span>
                                                            <input type="tel" name="tel" class="form-control"
                                                                   value="{{ $p->tel }}" pattern="[0-9]{8}" maxlength="8">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" value="{{ $p->email }}" class="form-control">
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
                            <div class="modal fade" id="deletePersonnel{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">🗑️ Supprimer le membre</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment supprimer <strong>{{ $p->nom }} {{ $p->prenom }}</strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <form action="{{ route('personnel.destroy', $p->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Activer/Désactiver --}}
                            <div class="modal fade" id="togglePersonnel{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                        <div class="modal-header {{ $p->statut ? 'bg-warning' : 'bg-success' }} text-white">
                                            <h5 class="modal-title">{{ $p->statut ? 'Désactiver' : 'Activer' }} le membre</h5>
                                            <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment <strong>{{ $p->statut ? 'désactiver' : 'activer' }}</strong> <strong>{{ $p->nom }} {{ $p->prenom }}</strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <form action="{{ route('personnel.toggle', $p->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $p->statut ? 'btn-warning' : 'btn-success' }}">
                                                    Oui, {{ $p->statut ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucun membre administratif trouvé</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $personnel->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $('#personnelTable').DataTable({
            destroy: true,
            responsive: true,
            autoWidth: false,
            pageLength: 50,
            order: [[0, "desc"]],
            deferRender: true,
            language: {
                url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
            }
        });
    </script>
@endpush
