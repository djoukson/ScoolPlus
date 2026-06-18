@extends('layouts.app')
@section('title', 'Attribution des bourses')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                🎓 Gestion des bourses

                    {{ $anneeActive->nom ?? 'Non définie' }}

            </div>
            <a style="margin-bottom: 10px" href="{{ route('bourses.index') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-plus-square"></i> Nouveaux types de bourses
            </a>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Attribution des bourses
                    </li>
                </ol>
            </div>
        </div>
        <!-- 🔹 Formulaire d’attribution -->
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold text-secondary">
                    🧾 Nouvelle attribution
                </h5>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body bg-light p-4">
                    <form action="{{ route('attributions.store') }}" method="POST" id="formAttribution" class="row g-4 align-items-end">
                        @csrf

                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary small">
                                <i class="fas fa-user-graduate me-1 text-primary"></i> Élève concerné <span class="text-danger">*</span>
                            </label>
                            <select name="inscription_id" class="form-select form-select-lg border-0 shadow-sm select2" required>
                                <option value="">-- Sélectionnez un élève --</option>
                                @foreach($inscriptions as $ins)
                                    <option value="{{ $ins->id }}">
                                        {{ strtoupper($ins->eleve->nom) }} {{ ucfirst($ins->eleve->prenom) }} — {{ $ins->classe->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary small">
                                <i class="fas fa-award me-1 text-success"></i> Bourse à attribuer <span class="text-danger">*</span>
                            </label>
                            <select name="bourse_id" class="form-select form-select-lg border-0 shadow-sm select2" required>
                                <option value="">-- Sélectionnez une bourse --</option>
                                @foreach($bourses as $bourse)
                                    <option value="{{ $bourse->id }}">
                                        🎓 {{ ucfirst($bourse->nom) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary btn-lg fw-semibold shadow-sm hover-animate" type="submit">
                                <i class="fas fa-check-circle me-2"></i>Attribuer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <!-- 🔹 Liste des attributions -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold text-secondary">📋 Liste des attributions</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Bourse</th>
                            <th>Date d’attribution</th>
                            <th>État</th>
                            <th class="text-center" width="120">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($attributions as $attr)
                            <tr>
                                <td class="fw-semibold">{{ $attr->inscription->eleve->nom }} {{ $attr->inscription->eleve->prenom }}</td>
                                <td>{{ $attr->inscription->classe->nom }}</td>
                                <td>
                                    <strong>{{ $attr->bourse->nom }}</strong>
                                    @php
                                        $pourcentages = $attr->bourse->frais->pluck('pivot.pourcentage')->filter()->toArray();
                                    @endphp
                                    @if (count($pourcentages))
                                        <span class="badge bg-info text-dark">
                    {{ implode('%, ', $pourcentages) }}%
                </span>
                                    @else
                                        <span class="text-muted">(aucun % défini)</span>
                                    @endif
                                </td>

                                <td>{{ \Carbon\Carbon::parse($attr->date_attribution)->format('d/m/Y') }}</td>

                                <!-- 🔹 État -->
                                <td>
            <span class="badge {{ $attr->etat === 'active' ? 'bg-success' : 'bg-secondary' }}">
                {{ ucfirst($attr->etat) }}
            </span>
                                </td>

                                <td class="text-center d-flex justify-content-center gap-1">
                                    <!-- Toggle état -->
                                    <button type="button" class="btn btn-sm btn-outline-{{ $attr->etat === 'active' ? 'danger' : 'success' }} btn-toggle-etat"
                                            data-id="{{ $attr->id }}">
                                        {{ $attr->etat === 'active' ? 'Désactiver' : 'Activer' }}
                                    </button>

                                    <!-- Supprimer -->
                                    <form action="{{ route('attributions.destroy', $attr->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-light text-danger border-0 delete-btn" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Aucune attribution enregistrée</td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-gradient-primary {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gradient-primary:hover {
            background: linear-gradient(90deg, #0056d2, #00a4cc);
            transform: translateY(-1px);
        }
        .bg-gradient-info {
            background: linear-gradient(90deg, #17a2b8, #5bc0de);
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .select2-container--bootstrap4 .select2-selection {
            height: calc(2.9rem + 2px);
            border: 1px solid #ced4da; /* ✅ Bordure grise claire, comme Bootstrap */
            border-radius: 0.5rem;
            background-color: #fff;
            transition: all 0.2s ease-in-out;
        }

        /* 🎯 Ajout d’un effet focus propre */
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        /* ✅ Limite la hauteur et ajoute un scroll interne au menu Select2 */
        .select2-container .select2-results__options {
            max-height: 250px !important; /* hauteur max visible */
            overflow-y: auto !important;  /* scroll interne */
        }

        /* ✅ Optionnel : améliore la visibilité du fond */
        .select2-container--bootstrap4 .select2-results__option {
            padding: 8px 12px;
        }



        /* Style moderne et épuré */
        .form-select, .form-control {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
        }
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .hover-animate {
            transition: all 0.25s ease;
        }
        .hover-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
    </style>
@endsection



@push('scripts')
    <script>

        $('.btn-toggle-etat').on('click', function () {
            const attrId = $(this).data('id');
            const button = $(this);
            const action = button.text().trim();

            Swal.fire({
                title: `${action} cette bourse ?`,
                text: "Cette action peut être réversible.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Oui, ${action.toLowerCase()}`,
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirection vers la route toggle
                    window.location.href = `/attributions/toggle/${attrId}`;
                }
            });
        });


        // Validation simple avant envoi
        document.getElementById('formAttribution').addEventListener('submit', function (e) {
            const eleve = document.querySelector('[name="inscription_id"]').value;
            const bourse = document.querySelector('[name="bourse_id"]').value;

            if (!eleve || !bourse) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs manquants',
                    text: 'Veuillez sélectionner un élève et une bourse avant de continuer.',
                    confirmButtonColor: '#007bff'
                });
            }
        });

        $(function () {
            // Initialisation Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Sélectionner...',
                allowClear: true
            });

            // 🔔 SweetAlert2 - Suppression
            $('.delete-btn').on('click', function () {
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Supprimer cette attribution ?',
                    text: "Cette action est irréversible.",
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

            // 🔔 Notifications
            @if(session('success'))
            Swal.fire({
                toast: true, position: 'top-end',
                icon: 'success', title: "{{ session('success') }}",
                showConfirmButton: false, timer: 3000
            });
            @endif

            @if(session('error'))
            Swal.fire({
                toast: true, position: 'top-end',
                icon: 'error', title: "{{ session('error') }}",
                showConfirmButton: false, timer: 3000
            });
            @endif
        });
    </script>
@endpush
