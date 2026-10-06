@extends('layouts.app')

@section('content')
    <div class="container py-4">

        @if (session('temporary_password'))
            <div class="alert alert-warning" role="alert">
                <strong>Mot de passe temporaire — copiez-le maintenant :</strong>
                <code class="user-select-all">{{ session('temporary_password') }}</code>
                <div class="small mt-1">Il ne sera affiché qu’une seule fois. Transmettez-le au titulaire du compte par un canal sûr et demandez-lui de le changer après connexion.</div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Utilisateurs
            </div>
            <button style="margin-bottom: 10px" class="btn btn-outline-danger shadow-sm" data-toggle="modal" data-target="#userModal" onclick="openAddModal()">
                <i class="fas fa-user-plus"></i> Nouvel Utilisateur
            </button>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajouter des utilisateurs
                    </li>
                </ol>
            </div>
        </div>


        <!-- ✅ Tableau -->
        <div class="card shadow-sm border-0 rounded-3" >
            <div class="card-body p-0" style="margin-top: 10px">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="ListeTable" >
                        <thead class="table-primary">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->matricule ?? '-' }}</td>
                                <td class="fw-semibold">{{ $user->name ?? '-' }}</td>
                                <td>{{ $user->username ?? '-' }}</td>
                                <td>{{ $user->email ?? '-' }}</td>
                                <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'professeur' ? 'info' : ($user->role == 'comptable' ? 'warning' : 'secondary')) }}">
                                    <i class="fas fa-user-tag"></i> {{ ucfirst($user->role) }}
                                </span>
                                </td>
                                <td class="text-center">
                                    @if($user->status)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Désactivé</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-light border shadow-sm btn-sm dropdown-toggle"
                                                type="button"
                                                id="dropdownMenuButton{{ $user->id }}"
                                                data-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-cog text-primary"></i> Actions
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                            aria-labelledby="dropdownMenuButton{{ $user->id }}">

                                            <!-- Voir les infos -->
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="#"
                                                   data-toggle="modal"
                                                   data-target="#viewUserModal"
                                                   onclick="openViewModal(
                        '{{ $user->name }}',
                        '{{ $user->email }}',
                        '{{ $user->username }}',
                        '{{ $user->phone }}',
                        '{{ ucfirst($user->role) }}',
                        '{{ $user->sexe ?? '-' }}',
                        '{{ $user->profileimg ? asset($user->profileimg) : asset('images/default-avatar.png') }}'
                    )">
                                                    <i class="fas fa-eye text-info me-2"></i> Voir le profil
                                                </a>
                                            </li>

                                            <!-- Modifier -->
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="#"
                                                   data-toggle="modal"
                                                   data-target="#userModal"
                                                   onclick="openEditModal(
                        {{ $user->id }},
                        '{{ $user->name }}',
                        '{{ $user->email }}',
                        '{{ $user->username }}',
                        '{{ $user->phone }}',
                        '{{ $user->role }}',
                        '{{ $user->sexe }}',
                        '{{ $user->profileimg ? asset($user->profileimg) : asset('images/default-avatar.png') }}'
                    )">
                                                    <i class="fas fa-edit text-warning me-2"></i> Modifier
                                                </a>
                                            </li>

                                            <!-- Réinitialiser mot de passe -->
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center"
                                                   href="#"
                                                   data-toggle="modal"
                                                   data-target="#resetPasswordModal"
                                                   onclick="setResetUser({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-sync-alt text-secondary me-2"></i> Réinitialiser le mot de passe
                                                </a>
                                            </li>

                                            <li><hr class="dropdown-divider"></li>
                                            <!-- Activer / Désactiver -->
                                            <li>
                                                <form action="{{ route('users.toggleStatus', $user->id) }}" method="POST"
                                                      onsubmit="return confirm('Confirmer cette action ?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item d-flex align-items-center
                {{ $user->status ? 'text-warning' : 'text-success' }}">

                                                        @if($user->status)
                                                            <i class="fas fa-user-slash me-2"></i> Désactiver
                                                        @else
                                                            <i class="fas fa-user-check me-2"></i> Activer
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>

                                            <!-- Supprimer -->
                                            <li>
                                                <a class="dropdown-item text-danger d-flex align-items-center"
                                                   href="#"
                                                   data-toggle="modal"
                                                   data-target="#deleteModal"
                                                   onclick="setDeleteAction({{ $user->id }})">
                                                    <i class="fas fa-trash-alt me-2"></i> Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>


                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="fas fa-user-slash fa-2x mb-2"></i><br>
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ✅ Modal Voir utilisateur -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-circle"></i> Informations de l'utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="text-center mb-3">
                        <img id="viewProfile" src="" alt="Photo de profil"
                             class="rounded-circle border border-3 border-white shadow"
                             width="110" height="110" style="object-fit: cover;">
                    </div>
                    <div class="row text-center text-md-start">
                        <div class="col-md-6 mb-2"><strong>Nom :</strong> <span id="viewName"></span></div>
                        <div class="col-md-6 mb-2"><strong>Nom d’utilisateur :</strong> <span id="viewUsername"></span></div>
                        <div class="col-md-6 mb-2"><strong>Email :</strong> <span id="viewEmail"></span></div>
                        <div class="col-md-6 mb-2"><strong>Téléphone :</strong> <span id="viewPhone"></span></div>
                        <div class="col-md-6 mb-2"><strong>Rôle :</strong> <span id="viewRole"></span></div>
                        <div class="col-md-6 mb-2"><strong>Sexe :</strong> <span id="viewSexe"></span></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ✅ Modal Reset Password -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-sync-alt"></i> Réinitialiser le mot de passe</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Voulez-vous vraiment réinitialiser le mot de passe de <strong id="resetUserName"></strong> ?</p>
                    <p class="text-muted mb-0">Un mot de passe temporaire aléatoire sera généré et affiché une seule fois après la réinitialisation.</p>
                </div>
                <div class="modal-footer border-0">
                    <form id="resetForm" method="POST" action="">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Modal Add/Edit -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="userModalLabel">
                        <i class="fas fa-user-plus"></i> Nouvel utilisateur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" method="POST" action="" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="formMethod" name="_method" value="POST">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="Nom" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Email">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="username" id="username" class="form-control form-control-lg" placeholder="Nom d’utilisateur">
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="phone" id="phone" class="form-control form-control-lg" placeholder="Téléphone">
                            </div>
                            <div class="col-md-6" id="passwordField">
                                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Mot de passe temporaire" minlength="8">
                            </div>
                            <div class="col-12" id="temporaryPasswordHelp" style="display:none">
                                <small class="text-muted">Un mot de passe temporaire aléatoire sera généré et affiché une seule fois après la création.</small>
                            </div>
                            <div class="col-md-6">
                                <select name="role" id="role" class="form-select form-select-lg" required>
                                    <option value="">-- Sélectionner un rôle --</option>
                                    <option value="admin">Admin</option>
                                    <option value="professeur">Professeur</option>
                                    <option value="comptable">Comptable</option>
                                    <option value="directeur">Directeur</option>
                                    <option value="secretaire">Secretaire</option>
                                </select>
                            </div>

                            <!-- Nouveau : Sexe -->
                            <div class="col-md-6">
                                <select name="sexe" id="sexe" class="form-select form-select-lg">
                                    <option value="">-- Sélectionner le sexe --</option>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Feminin">Feminin</option>
                                </select>
                            </div>

                            <!-- Nouveau : Photo de profil -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Photo de profil</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img id="profilePreview" src="{{ asset('images/default-avatar.png') }}" alt="Aperçu" class="rounded-circle border shadow-sm" width="60" height="60">
                                    <input type="file" name="profileimg" id="profileimg" class="form-control form-control-lg" accept="image/*" onchange="previewProfile(event)">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                    <button type="submit" class="btn btn-success" form="userForm"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Modal Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-0 fs-5">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
                </div>
                <div class="modal-footer border-0">
                    <form id="deleteForm" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openViewModal(name, email, username, phone, role, sexe, profileimg) {
            document.getElementById('viewName').textContent = name;
            document.getElementById('viewEmail').textContent = email;
            document.getElementById('viewUsername').textContent = username;
            document.getElementById('viewPhone').textContent = phone;
            document.getElementById('viewRole').textContent = role;
            document.getElementById('viewSexe').textContent = sexe;
            document.getElementById('viewProfile').src = profileimg;
        }

        function setResetUser(id, name) {
            document.getElementById('resetUserName').textContent = name;
            document.getElementById('resetForm').action = "/users/reset-password/" + id;
        }
        // Aperçu photo de profil
        function previewProfile(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profilePreview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function openAddModal(){
            document.getElementById('userModalLabel').innerHTML = '<i class="fas fa-user-plus"></i> Nouvel utilisateur';
            document.getElementById('userForm').action = "{{ route('users.store') }}";
            document.getElementById('formMethod').value = "POST";
            document.getElementById('passwordField').style.display = "none";
            document.getElementById('temporaryPasswordHelp').style.display = "block";

            document.getElementById('name').value = "";
            document.getElementById('email').value = "";
            document.getElementById('username').value = "";
            document.getElementById('phone').value = "";
            document.getElementById('password').value = "";
            document.getElementById('role').value = "";
            document.getElementById('sexe').value = "";
            document.getElementById('profileimg').value = "";
            document.getElementById('profilePreview').src = "{{ asset('images/default-avatar.png') }}";
        }

        function openEditModal(id, name, email, username, phone, role, sexe, profileimg){
            document.getElementById('userModalLabel').innerHTML = '<i class="fas fa-user-edit"></i> Modifier utilisateur';
            document.getElementById('userForm').action = "/users/" + id;
            document.getElementById('formMethod').value = "PUT";

            document.getElementById('name').value = name;
            document.getElementById('email').value = email;
            document.getElementById('username').value = username;
            document.getElementById('phone').value = phone;
            document.getElementById('role').value = role;
            document.getElementById('sexe').value = sexe;
            document.getElementById('password').value = "";
            document.getElementById('passwordField').style.display = "block";
            document.getElementById('temporaryPasswordHelp').style.display = "none";

            document.getElementById('profilePreview').src = profileimg ?? "{{ asset('images/default-avatar.png') }}";
        }

        function setDeleteAction(id){
            document.getElementById('deleteForm').action = "/users/" + id;
        }


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
