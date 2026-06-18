@extends('layouts.app')

@section('content')
    <div class="container py-4">


        <h3 class="fw-bold text-primary mb-4">
            <i class="fas fa-cogs"></i> Paramètres utilisateur
        </h3>

        <div class="row g-4">
            {{-- 👤 Mise à jour du profil utilisateur --}}
            <div class="col-md-6 mx-auto">
                <div class="card shadow-lg border-0 h-100 setting-card">
                    <div class="card-body text-center">
                        <h5 class="fw-bold text-primary mb-4">
                            <i class="fas fa-user-cog"></i> Mise à jour du profil
                        </h5>

                        <!-- Photo de profil -->
                        <div class="mb-4 position-relative d-inline-block">
                            <div class="position-relative">
                                <img src="{{ auth()->user()->profileimg
                                                ? asset('/' . auth()->user()->profileimg)
                                                : (auth()->user()->sexe === 'Masculin'
                                                    ? asset('dist/img/man.png')
                                                    : (auth()->user()->sexe === 'Féminin'
                                                        ? asset('dist/img/woman.png')
                                                        : asset('dist/img/general.png')))
                                            }}"
                                     alt="Photo de profil"
                                     class="rounded-circle shadow-lg border border-3 border-white"
                                     style="width: 120px; height: 120px; object-fit: cover;">


                                <!-- Bouton de modification -->
                                <label for="profile_image"
                                       class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow"
                                       style="cursor:pointer; width:35px; height:35px;">
                                    <i class="fas fa-camera"></i>
                                </label>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.updateProfile') }}" enctype="multipart/form-data" class="text-start mt-3">
                            @csrf
                            @method('PUT')

                            <input type="file" id="profile_image" name="profileimg" class="d-none" accept="image/*">

                            <!-- Nom complet -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" id="name" name="name" class="form-control"
                                       value="{{ old('name', auth()->user()->name) }}" required>
                            </div>

                            <!-- Nom d'utilisateur -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" class="form-control"
                                       value="{{ old('username', auth()->user()->username) }}" required>
                            </div>

                            <!-- Téléphone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                       value="{{ old('phone', auth()->user()->phone) }}" placeholder="+233..." required>
                            </div>

                            <!-- Sexe -->
                            <div class="mb-3">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select id="sexe" name="sexe" class="form-select" required>
                                    <option value="">-- Sélectionnez --</option>
                                    <option value="Masculin" {{ old('sexe', auth()->user()->sexe) === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                    <option value="Feminin" {{ old('sexe', auth()->user()->sexe) === 'Feminin' ? 'selected' : '' }}>Feminin</option>
                                </select>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="{{ old('email', auth()->user()->email) }}" required>
                            </div>

                            <button class="btn btn-primary w-100 mt-2">
                                💾 Sauvegarder les modifications
                            </button>
                        </form>
                    </div>
                </div>
            </div>



            {{-- 🔒 Changement de mot de passe --}}
            <div class="col-md-6">
                <div class="card shadow-lg border-0 h-100 setting-card">
                    <div class="card-body">
                        <h5 class="fw-bold text-danger mb-3">
                            <i class="fas fa-lock"></i> Changer le mot de passe
                        </h5>

                        <form method="POST" action="{{ route('settings.updatePassword') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>

                            <button class="btn btn-danger w-100">🔒 Mettre à jour</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🧑‍🏫 Informations Enseignant --}}
        @if($enseignant)
            <div class="col-md-12 mt-5">
                <div class="card shadow-lg border-0 setting-card">
                    <div class="card-body">
                        <h5 class="fw-bold text-success mb-3">
                            <i class="fas fa-chalkboard-teacher"></i> Informations Enseignant
                        </h5>

                        <form method="POST" action="{{ route('settings.updateTeacherInfo') }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom" class="form-control"
                                           value="{{ old('nom', $enseignant->nom) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom" class="form-control"
                                           value="{{ old('prenom', $enseignant->prenom) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" name="tel" class="form-control"
                                           value="{{ old('tel', $enseignant->tel) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" name="adresse" class="form-control"
                                           value="{{ old('adresse', $enseignant->adresse) }}">
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label">Spécialité</label>
                                    <input type="text" name="specialite" class="form-control"
                                           value="{{ old('specialite', $enseignant->specialite) }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="sexe" class="form-label">Sexe</label>
                                    <select name="sexe" id="sexe" class="form-select" required>
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="Masculin" {{ old('sexe', $enseignant->sexe ?? '') == 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                        <option value="Féminin" {{ old('sexe', $enseignant->sexe ?? '') == 'Féminin' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Groupe sanguin</label>
                                    <select name="groupesanguin" class="form-select">
                                        <option value="">— Sélectionner —</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs)
                                            <option value="{{ $gs }}" {{ old('groupesanguin', $enseignant->groupesanguin) == $gs ? 'selected' : '' }}>
                                                {{ $gs }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-success w-100">
                                    <i class="fas fa-save"></i> Mettre à jour les informations
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- 🌟 Style moderne --}}
    <style>
        .setting-card {
            transition: all 0.3s ease;
            border-radius: 14px;
        }

        .setting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }

        label {
            font-weight: 600;
            color: #444;
        }
        .btn {
            border-radius: 10px !important;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>
    <script>
        document.getElementById('profile_image').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.querySelector('img[alt="Photo de profil"]').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
