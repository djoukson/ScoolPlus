@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- Breadcrumb --}}
    <div class="pro-breadcrumb mb-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap">

            <div>
                <div class="breadcrumb-title">
                    Comptes parents
                </div>

                <div class="breadcrumb-wrapper">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="/">Dashboard</a>
                        </li>

                        <li class="breadcrumb-item active">
                            Comptes parents
                        </li>
                    </ol>
                </div>
            </div>

        </div>

    </div>


    {{-- Messages --}}
    @if(session('temporary_password'))
        <div class="alert alert-warning" role="alert">
            <strong>Mot de passe temporaire — copiez-le maintenant :</strong>
            <code class="user-select-all">{{ session('temporary_password') }}</code>
            <div class="small mt-1">Il ne sera affiché qu’une seule fois. Transmettez-le au parent par un canal sûr et demandez-lui de le changer après connexion.</div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('warning') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-times-circle"></i>
            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    {{-- Erreurs validation --}}
    @if($errors->any())
        <div class="alert alert-danger shadow-sm">

            <strong>
                <i class="fas fa-exclamation-circle"></i>
                Une erreur est survenue
            </strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif


    {{-- Tableau --}}
    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h5 class="mb-1 font-weight-bold">
                        <i class="fas fa-users text-primary"></i>
                        Gestion des comptes parents
                    </h5>

                    <small class="text-muted">
                        Créez et gérez les accès des parents aux informations de leurs enfants.
                    </small>
                </div>

                <div class="mt-2 mt-md-0">

                    <span class="badge badge-primary p-2">
                        {{ $parents->count() }}
                        dossier(s)
                    </span>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0"
                       id="ParentsTable">

                    <thead class="thead-light">

                    <tr>
                        <th>Élève</th>

                        <th>Père</th>

                        <th>Email père</th>

                        <th>Mère</th>

                        <th>Email mère</th>

                        <th class="text-center">
                            Compte
                        </th>

                        <th class="text-center">
                            Actions
                        </th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($parents as $parent)

                        @php

                            /*
                             * Recherche d'un éventuel compte parent
                             * à partir des deux emails disponibles.
                             */

                            $emails = array_filter([
                                $parent->pere_email,
                                $parent->mere_email
                            ]);

                            $parentUser = !empty($emails)
                                ? \App\Models\User::whereIn('email', $emails)
                                    ->where('role', 'parent')
                                    ->first()
                                : null;

                        @endphp


                        <tr>

                            {{-- Élève --}}
                            <td>

                                <div class="font-weight-bold">

                                    {{ $parent->eleve->nom ?? '' }}

                                    {{ $parent->eleve->prenom ?? '' }}

                                </div>

                                @if(isset($parent->eleve->matricule))

                                    <small class="text-muted">
                                        {{ $parent->eleve->matricule }}
                                    </small>

                                @endif

                            </td>


                            {{-- Père --}}
                            <td>

                                @if($parent->pere_nom)

                                    <i class="fas fa-male text-primary"></i>

                                    {{ $parent->pere_nom }}

                                    @if($parent->pere_tel)

                                        <br>

                                        <small class="text-muted">
                                            {{ $parent->pere_tel }}
                                        </small>

                                    @endif

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Email père --}}
                            <td>

                                @if($parent->pere_email)

                                    <span class="text-primary">
                                        {{ $parent->pere_email }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Mère --}}
                            <td>

                                @if($parent->mere_nom)

                                    <i class="fas fa-female text-danger"></i>

                                    {{ $parent->mere_nom }}

                                    @if($parent->mere_tel)

                                        <br>

                                        <small class="text-muted">
                                            {{ $parent->mere_tel }}
                                        </small>

                                    @endif

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Email mère --}}
                            <td>

                                @if($parent->mere_email)

                                    <span class="text-primary">
                                        {{ $parent->mere_email }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- État du compte --}}
                            <td class="text-center">

                                @if($parentUser)

                                    @if($parentUser->status)

                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            Actif
                                        </span>

                                    @else

                                        <span class="badge badge-danger">
                                            <i class="fas fa-ban"></i>
                                            Désactivé
                                        </span>

                                    @endif

                                    <br>

                                    <small class="text-muted">

                                        {{ $parentUser->email }}

                                    </small>

                                @else

                                    <span class="badge badge-secondary">
                                        <i class="fas fa-user-slash"></i>
                                        Aucun compte
                                    </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="text-center">

                                <div class="dropdown">

                                    <button
                                        class="btn btn-light border shadow-sm btn-sm dropdown-toggle"
                                        type="button"
                                        data-toggle="dropdown">

                                        <i class="fas fa-cog text-primary"></i>
                                        Actions

                                    </button>


                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">


                                        {{-- Créer compte --}}
                                        @if(!$parentUser)

                                            @php

                                                $hasFatherEmail = !empty($parent->pere_email);
                                                $hasMotherEmail = !empty($parent->mere_email);

                                            @endphp


                                            @if($hasFatherEmail || $hasMotherEmail)

                                                <a href="#"
                                                   class="dropdown-item"
                                                   data-toggle="modal"
                                                   data-target="#createParentModal"

                                                   onclick="openCreateParentModal(

                                                       {{ $parent->eleve_id }},

                                                       '{{ addslashes(($parent->eleve->nom ?? '') . ' ' . ($parent->eleve->prenom ?? '')) }}',

                                                       '{{ addslashes($parent->pere_nom ?? '') }}',
                                                       '{{ addslashes($parent->pere_email ?? '') }}',

                                                       '{{ addslashes($parent->mere_nom ?? '') }}',
                                                       '{{ addslashes($parent->mere_email ?? '') }}'

                                                   )">

                                                    <i class="fas fa-user-plus text-success mr-2"></i>

                                                    Créer le compte parent

                                                </a>

                                            @else

                                                <span class="dropdown-item text-muted">

                                                    <i class="fas fa-envelope-slash mr-2"></i>

                                                    Aucun email disponible

                                                </span>

                                            @endif


                                        @else


                                            {{-- Voir compte --}}
                                            <a href="#"
                                               class="dropdown-item"
                                               data-toggle="modal"
                                               data-target="#parentInfoModal"

                                               onclick="openParentInfoModal(

                                                   '{{ addslashes($parentUser->name) }}',
                                                   '{{ addslashes($parentUser->email) }}',
                                                   '{{ addslashes($parentUser->matricule ?? '') }}',
                                                   {{ $parentUser->status ? 'true' : 'false' }}

                                               )">

                                                <i class="fas fa-eye text-info mr-2"></i>

                                                Voir le compte

                                            </a>


                                            {{-- Ajouter un autre enfant --}}
                                            <a href="#"
                                               class="dropdown-item"
                                               data-toggle="modal"
                                               data-target="#linkChildModal"

                                               onclick="openLinkChildModal(

                                                   {{ $parentUser->id }},

                                                   '{{ addslashes($parentUser->name) }}',
                                                   '{{ addslashes($parentUser->email) }}'

                                               )">

                                                <i class="fas fa-child text-primary mr-2"></i>

                                                Ajouter un enfant

                                            </a>


                                            <div class="dropdown-divider"></div>


                                            {{-- Activer / désactiver --}}
                                            <form action="{{ route('users.toggleStatus', $parentUser->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Confirmer cette action ?')">

                                                @csrf

                                                <button type="submit"
                                                        class="dropdown-item
                                                        {{ $parentUser->status ? 'text-warning' : 'text-success' }}">

                                                    @if($parentUser->status)

                                                        <i class="fas fa-user-slash mr-2"></i>
                                                        Désactiver

                                                    @else

                                                        <i class="fas fa-user-check mr-2"></i>
                                                        Activer

                                                    @endif

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted py-5">

                                <i class="fas fa-users-slash fa-3x mb-3"></i>

                                <br>

                                Aucun dossier parent trouvé.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- MODAL : CRÉER COMPTE PARENT --}}
{{-- ========================================================= --}}

<div class="modal fade"
     id="createParentModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow-lg border-0 rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="fas fa-user-plus"></i>

                    Créer un compte parent

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form method="POST"
                  action="{{ route('parents.store') }}">

                @csrf

                <input type="hidden"
                       name="eleve_id"
                       id="parentEleveId">


                <div class="modal-body">

                    <div class="alert alert-light border">

                        <small class="text-muted">
                            Élève
                        </small>

                        <div class="font-weight-bold"
                             id="parentEleveName">

                        </div>

                    </div>


                    <label class="font-weight-bold">

                        Email utilisé pour la connexion

                    </label>


                    <div id="parentEmailOptions">


                        {{-- Père --}}
                        <div class="custom-control custom-radio mb-3"
                             id="fatherEmailOption">

                            <input type="radio"
                                   class="custom-control-input"
                                   name="email_parent"
                                   id="fatherEmail"
                                   value="">

                            <label class="custom-control-label"
                                   for="fatherEmail">

                                <i class="fas fa-male text-primary"></i>

                                <strong id="fatherName"></strong>

                                <br>

                                <small class="text-muted"
                                       id="fatherEmailText">

                                </small>

                            </label>

                        </div>


                        {{-- Mère --}}
                        <div class="custom-control custom-radio mb-3"
                             id="motherEmailOption">

                            <input type="radio"
                                   class="custom-control-input"
                                   name="email_parent"
                                   id="motherEmail"
                                   value="">

                            <label class="custom-control-label"
                                   for="motherEmail">

                                <i class="fas fa-female text-danger"></i>

                                <strong id="motherName"></strong>

                                <br>

                                <small class="text-muted"
                                       id="motherEmailText">

                                </small>

                            </label>

                        </div>


                    </div>


                    <div class="alert alert-warning mt-3 mb-0">

                        <i class="fas fa-key"></i>

                        <small>
                            Un mot de passe temporaire aléatoire sera créé et affiché une seule fois après la création. Le parent devra le modifier lors de sa première connexion.
                        </small>

                    </div>

                </div>


                <div class="modal-footer border-0">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">

                        Annuler

                    </button>

                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-user-plus"></i>

                        Créer le compte

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- MODAL : INFORMATIONS COMPTE --}}
{{-- ========================================================= --}}

<div class="modal fade"
     id="parentInfoModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-info text-white">

                <h5 class="modal-title">

                    <i class="fas fa-user-circle"></i>

                    Compte parent

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <div class="modal-body">

                <div class="text-center mb-4">

                    <div class="rounded-circle bg-light d-inline-flex
                                align-items-center justify-content-center"
                         style="width:80px;height:80px;">

                        <i class="fas fa-user fa-2x text-primary"></i>

                    </div>

                </div>


                <div class="row">

                    <div class="col-5 font-weight-bold">
                        Nom :
                    </div>

                    <div class="col-7"
                         id="infoParentName">
                    </div>


                    <div class="col-5 font-weight-bold mt-3">
                        Email :
                    </div>

                    <div class="col-7 mt-3"
                         id="infoParentEmail">
                    </div>


                    <div class="col-5 font-weight-bold mt-3">
                        Matricule :
                    </div>

                    <div class="col-7 mt-3"
                         id="infoParentMatricule">
                    </div>


                    <div class="col-5 font-weight-bold mt-3">
                        Statut :
                    </div>

                    <div class="col-7 mt-3"
                         id="infoParentStatus">
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- MODAL : AJOUTER UN ENFANT --}}
{{-- ========================================================= --}}

<div class="modal fade"
     id="linkChildModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="fas fa-child"></i>

                    Ajouter un enfant

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form method="POST"
                  action="{{ route('parents.linkChild') }}">

                @csrf

                <input type="hidden"
                       name="user_id"
                       id="linkParentUserId">


                <div class="modal-body">

                    <div class="alert alert-info">

                        <strong id="linkParentName"></strong>

                        <br>

                        <small id="linkParentEmail"></small>

                    </div>


                    <div class="form-group">

                        <label class="font-weight-bold">

                            Élève à ajouter

                        </label>

                        <select name="eleve_id"
                                class="form-control"
                                required>

                            <option value="">
                                -- Sélectionner un élève --
                            </option>

                            @foreach(
                                \App\Models\Eleve::orderBy('nom')->orderBy('prenom')->get()
                                as $eleve
                            )

                                <option value="{{ $eleve->id }}">

                                    {{ $eleve->nom }}
                                    {{ $eleve->prenom }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                <div class="modal-footer border-0">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">

                        Annuler

                    </button>

                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-link"></i>

                        Lier l'enfant

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



@endsection


@push('scripts')

<script>

    /*
     * Ouvrir modal création compte parent
     */
    function openCreateParentModal(
        eleveId,
        eleveName,
        fatherName,
        fatherEmail,
        motherName,
        motherEmail
    ) {

        document.getElementById('parentEleveId').value = eleveId;

        document.getElementById('parentEleveName').textContent = eleveName;


        /*
         * Père
         */
        if (fatherEmail) {

            document.getElementById('fatherEmailOption').style.display = 'block';

            document.getElementById('fatherEmail').value = fatherEmail;

            document.getElementById('fatherName').textContent =
                fatherName || 'Père';

            document.getElementById('fatherEmailText').textContent =
                fatherEmail;

        } else {

            document.getElementById('fatherEmailOption').style.display = 'none';

            document.getElementById('fatherEmail').checked = false;

        }


        /*
         * Mère
         */
        if (motherEmail) {

            document.getElementById('motherEmailOption').style.display = 'block';

            document.getElementById('motherEmail').value = motherEmail;

            document.getElementById('motherName').textContent =
                motherName || 'Mère';

            document.getElementById('motherEmailText').textContent =
                motherEmail;

        } else {

            document.getElementById('motherEmailOption').style.display = 'none';

            document.getElementById('motherEmail').checked = false;

        }


        /*
         * Sélection automatique
         */
        document.querySelectorAll(
            'input[name="email_parent"]'
        ).forEach(function(input) {

            input.checked = false;

        });

        if (fatherEmail) {

            document.getElementById('fatherEmail').checked = true;

        } else if (motherEmail) {

            document.getElementById('motherEmail').checked = true;

        }

    }


    /*
     * Informations du compte
     */
    function openParentInfoModal(
        name,
        email,
        matricule,
        status
    ) {

        document.getElementById('infoParentName').textContent =
            name;

        document.getElementById('infoParentEmail').textContent =
            email;

        document.getElementById('infoParentMatricule').textContent =
            matricule || '-';


        if (status) {

            document.getElementById('infoParentStatus').innerHTML =
                '<span class="badge badge-success">' +
                '<i class="fas fa-check-circle"></i> Actif' +
                '</span>';

        } else {

            document.getElementById('infoParentStatus').innerHTML =
                '<span class="badge badge-danger">' +
                '<i class="fas fa-ban"></i> Désactivé' +
                '</span>';

        }

    }


    /*
     * Ajouter un enfant
     */
    function openLinkChildModal(
        userId,
        name,
        email
    ) {

        document.getElementById('linkParentUserId').value =
            userId;

        document.getElementById('linkParentName').textContent =
            name;

        document.getElementById('linkParentEmail').textContent =
            email;

    }


    /*
     * DataTable
     */
    $('#ParentsTable').DataTable({

        destroy: true,

        responsive: true,

        autoWidth: false,

        pageLength: 50,

        order: [[0, 'asc']],

        deferRender: true,

        language: {
            url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
        }

    });

</script>

@endpush
