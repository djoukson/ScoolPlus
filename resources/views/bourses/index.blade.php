@extends('layouts.app')
@section('title', 'Types de bourses')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                🎓 Gestion des types de bourses
            </div>
            <a style="margin-bottom: 10px" href="{{ route('attributions.index') }}" class="btn btn-outline-info btn-sm">
                🎓  Attribution de bourses
            </a>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Creation des types de bourses
                    </li>
                </ol>
            </div>
        </div>

        {{-- 🌟 Formulaire moderne --}}
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body px-4 py-3">
                <form id="bourseForm" action="{{ route('bourses.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    {{-- 🧾 En-tête du formulaire --}}
                    <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                        <i class="fas fa-graduation-cap fs-4 text-primary me-2"></i>
                        <h5 class="fw-bold mb-0 text-dark">Création d’un type de bourse</h5>
                    </div>

                    {{-- 🔹 Informations principales --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">
                                <i class="fas fa-tag me-1 text-primary"></i> Nom de la bourse
                            </label>
                            <input type="text"
                                   name="nom"
                                   id="nom_bourse"
                                   class="form-control form-control-sm modern-input"
                                   placeholder="Ex : Bourse Orphelin"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">
                                <i class="fas fa-align-left me-1 text-primary"></i> Description
                            </label>
                            <input type="text" name="description" class="form-control form-control-sm modern-input" placeholder="Brève description (facultatif)">
                        </div>
                    </div>

                    {{-- 🔹 Section frais --}}
                    <div class="mb-2">
                        <label class="fw-semibold text-secondary small mb-2 d-flex align-items-center">
                            <i class="fas fa-list-check me-1 text-primary"></i> Frais concernés :
                        </label>

                        <div class="row g-2">
                            @foreach($frais as $f)
                                <div class="col-md-6">
                                    <div class="frais-item d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light hover-shadow-sm">
                                        <div class="form-check">
                                            <input class="form-check-input frais-checkbox" type="checkbox"
                                                   id="frais_{{ $f->id }}" name="frais[{{ $f->id }}][checked]"
                                                   value="{{ $f->id }}"
                                            >
                                            <label class="form-check-label fw-medium small text-dark" for="frais_{{ $f->id }}">
                                                {{ $f->libelle }}
                                            </label>
                                        </div>
                                        <input type="number"
                                               data-id="{{ $f->id }}"
                                               name="frais[{{ $f->id }}][pourcentage]"
                                               class="form-control form-control-sm pourcentage-input text-end"
                                               placeholder="%"
                                               min="1" max="100" style="width:80px;">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 🔹 Bouton --}}
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm fw-semibold">
                            <i class="fas fa-save me-2"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 🔹 Tableau des bourses --}}
        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
                <h5 class="fw-bold mb-3">📋 Liste des bourses</h5>
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Frais associés</th>
                        <th>Créé le</th>
                        <th width="120">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bourses as $bourse)
                        <tr>
                            <td>{{ $bourse->nom }}</td>
                            <td>{{ $bourse->description }}</td>
                            <td>
                                @foreach($bourse->frais as $f)
                                    <span class="badge bg-success mb-1">
                                    {{ $f->libelle }} ({{ $f->pivot->pourcentage }}%)
                                </span><br>
                                @endforeach
                            </td>
                            <td>{{ $bourse->created_at->format('d/m/Y') }}</td>
                            <td>
                                <form action="{{ route('bourses.destroy', $bourse->id) }}" method="POST" class="delete-form d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune bourse enregistrée</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <style>
        /* 🌈 Modernisation du formulaire */
        .modern-input {
            border-radius: 10px;
            border: 1px solid #d6d9dc;
            transition: all 0.2s ease-in-out;
        }
        .modern-input:focus {
            border-color: #4c6ef5;
            box-shadow: 0 0 0 0.15rem rgba(76, 110, 245, 0.25);
        }

        .frais-item {
            border: 1px solid #e2e5e8;
            transition: all 0.2s ease-in-out;
        }
        .frais-item:hover {
            background-color: #f8f9fa;
            border-color: #cfd4da;
            transform: scale(1.01);
        }

        .hover-shadow-sm {
            transition: box-shadow 0.2s ease;
        }
        .hover-shadow-sm:hover {
            box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: linear-gradient(90deg, #4c6ef5, #5a8dee);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #3b5bdb, #4c6ef5);
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            // ✅ Contrôle en direct de la saisie
            $(document).on('input', '.pourcentage-input', function () {
                let value = parseInt($(this).val());

                // Si la valeur dépasse 100
                if (value > 100) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Valeur invalide',
                        text: 'Le pourcentage ne peut pas dépasser 100%',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $(this).val(100);
                }

                // Si la valeur est négative ou nulle
                if (value < 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Valeur invalide',
                        text: 'Le pourcentage doit être au minimum 1%',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $(this).val(1);
                }
            });

            // 🔒 Validation avant envoi
            $('#bourseForm').on('submit', function (e) {
                let checked = false;
                let validPourcentage = true;

                let nom = $('#nom_bourse').val().trim();

                // Vérifier si le nom est vide
                if (nom === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nom obligatoire',
                        text: 'Veuillez saisir le nom de la bourse avant de continuer.'
                    });
                    return;
                }
                // Pour chaque checkbox de frais
                $('.frais-checkbox').each(function () {
                    const $checkbox = $(this);
                    if ($checkbox.is(':checked')) {
                        checked = true;

                        // Récupère l'input de pourcentage lié à ce même frais
                        const pourcentage = $(`.pourcentage-input[data-id="${$checkbox.val()}"]`).val();

                        if (!pourcentage || pourcentage <= 0) {
                            validPourcentage = false;
                        }
                    }
                });

                // Vérifie qu'au moins un frais est coché
                if (!checked) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Aucun frais sélectionné',
                        text: 'Veuillez cocher au moins un type de frais à appliquer à cette bourse.'
                    });
                    return;
                }

                // Vérifie que chaque frais coché a un pourcentage valide
                if (!validPourcentage) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pourcentage manquant',
                        text: 'Veuillez saisir un pourcentage pour chaque frais sélectionné.'
                    });
                }
            });


            // 🔔 Confirmation de suppression
            $('.delete-btn').on('click', function () {
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Supprimer cette bourse ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            // 🎉 Toasts de notification
            @if(session('success'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: "{{ session('success') }}", showConfirmButton: false, timer: 3000
            });
            @endif

            @if(session('error'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: "{{ session('error') }}", showConfirmButton: false, timer: 3000
            });
            @endif
        });
    </script>
@endpush
