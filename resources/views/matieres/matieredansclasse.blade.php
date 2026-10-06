@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Matières de la classe : {{ $classe->nom }}
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#affecterMatiereModal"
                   class="btn btn-outline-info">
                    ➕ Affectation
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('enseignantclasse.index') }}" >
                            ⬅ Retour aux classes
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        Matières de la classe : {{ $classe->nom }}
                    </li>
                </ol>
            </div>
        </div>


        {{-- ✅ Liste des matières --}}
        <div class="row g-4">
            @if($classe->niveau->nom == 'Primaire')
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-2">
                                <i class="fas fa-user-tie text-primary me-2"></i>
                                Enseignant de la classe
                            </h5>

                            @if($classe->enseignant)
                                <p class="card-text text-muted mb-1">
                                    Nom & Prénom :
                                    <strong>{{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}</strong>
                                </p>
                                <p class="card-text text-muted mb-0">
                                    Type :
                                    <strong>{{ $classe->enseignant->type ?? '—' }}</strong>
                                </p>

                                <!-- Bouton retirer enseignant -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-circle position-absolute top-0 end-0 m-2"
                                        data-toggle="modal"
                                        data-target="#removeEnseignantModal">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Modal confirmation suppression enseignant -->
                                <div class="modal fade" id="removeEnseignantModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 shadow-lg border-0">
                                            <div class="modal-header bg-danger text-white rounded-top-4">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                                        aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p class="mb-3">Voulez-vous vraiment <strong>retirer</strong> l’enseignant de cette classe ?</p>
                                                <h6 class="fw-bold text-dark">{{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}</h6>
                                                <p class="text-muted small mb-0">Cette action est irréversible.</p>
                                            </div>
                                            <div class="modal-footer justify-content-center border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill px-4"
                                                        data-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <form action="{{ route('classes.removeEnseignant', $classe->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger rounded-pill px-4 shadow-sm">
                                                        Retirer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Aucun enseignant n’est encore affecté à cette classe.
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @else
                @forelse($classe->affectations as $affectation)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 hover-card position-relative">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark mb-2">
                                    <i class="fas fa-book text-success me-2"></i>
                                    {{ $affectation->matiere->nom ?? 'Matière inconnue' }}
                                </h5>

                                <p class="card-text text-muted mb-1">
                                    Professeur :
                                    <strong>{{ $affectation->enseignant->nom.' '.$affectation->enseignant->prenom ?? 'N/A' }}</strong>
                                </p>

                                <p class="card-text text-muted mb-1">
                                    Coefficient :
                                    <strong>{{ $affectation->matiere->coefficient ?? 'N/A' }}</strong>
                                </p>

                                <p class="card-text text-muted mb-1">
                                    Heures attribuées :
                                    <strong>{{ $affectation->heures_attribuees ?? '—' }}</strong>
                                </p>
                            </div>

                            <!-- Bouton supprimer -->
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger rounded-circle position-absolute top-0 end-0 m-2"
                                    data-toggle="modal"
                                    data-target="#deleteModal{{ $affectation->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal de confirmation -->
                    <div class="modal fade" id="deleteModal{{ $affectation->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 shadow-lg border-0">
                                <div class="modal-header bg-danger text-white rounded-top-4">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                            aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="mb-3">Voulez-vous vraiment <strong>retirer</strong> la matière :</p>
                                    <h6 class="fw-bold text-dark">{{ $affectation->matiere->nom }}</h6>
                                    <p class="text-muted small mb-0">Cette action est irréversible.</p>
                                </div>
                                <div class="modal-footer justify-content-center border-0">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                            data-dismiss="modal">
                                        Annuler
                                    </button>
                                    <form action="{{ route('desaffectations.destroy', $affectation->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-danger rounded-pill px-4 shadow-sm">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-info-circle"></i>
                            Aucune matière n’est affectée à cette classe.
                        </div>
                    </div>
                @endforelse
            @endif
        </div>



    </div>

   <!-- ✅ Modal Affectation Matières (version modernisée) -->
<div class="modal fade" id="affecterMatiereModal" tabindex="-1" aria-labelledby="affecterMatiereModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg am-modal">

            <!-- Header -->
            <div class="modal-header am-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="affecterMatiereModalLabel">
                        Nouvelle affectation
                    </h5>
                    <p class="am-header-sub mb-0">
                        Classe <strong>{{ $classe->nom }}</strong> · {{ $classe->niveau->nom }}
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('classes.affecterMatieres', $classe->id) }}">
                @csrf
                <div class="modal-body p-4">

                    <p class="text-muted small mb-4">
                        Renseignez les informations ci-dessous pour affecter des matières à cette classe.
                    </p>

                    @if($classe->niveau->nom == 'Primaire')
                        <div class="am-field">
                            <label for="enseignant_id" class="form-label fw-semibold">Enseignant</label>
                            <select class="form-select am-select" name="enseignant_id" id="enseignant_id" required>
                                <option value="">Sélectionner un enseignant</option>
                                @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}">
                                        {{ $enseignant->nom }} {{ $enseignant->prenom }} — {{ $enseignant->type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <!-- Conteneur pour les lignes dynamiques -->
                        <div id="affectation-container" class="d-flex flex-column gap-3">

                            <div class="am-row affectation-row">
                                <div class="am-row-grid">
                                    <!-- Matière -->
                                    <div class="am-field">
                                        <label class="form-label fw-semibold">Matière</label>
                                        <select name="matieres[]" class="form-select am-select matiere-select" required>
                                            <option value="" disabled selected>Choisir une matière</option>
                                            @foreach($toutesMatieres as $matiere)
                                                <option value="{{ $matiere->id }}">{{ $matiere->nom }} ( Coef. {{ $matiere->coefficient }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Professeur -->
                                    <div class="am-field">
                                        <label class="form-label fw-semibold">Professeur</label>
                                        <select name="enseignant_id[]" class="form-select am-select enseignant-select" required>
                                            <option value="">Sélectionner</option>
                                            @foreach($professeurs as $professeur)
                                                <option value="{{ $professeur->id }}">{{ $professeur->nom }} {{ $professeur->prenom }} </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Heures -->
                                    <div class="am-field am-field-heures">
                                        <label class="form-label fw-semibold">Heures</label>
                                        <input type="number" name="heures_attribuees[]" class="form-control am-input" min="1" placeholder="20" required>
                                    </div>

                                    <!-- Actions -->
                                    <div class="am-field-actions">
                                        <button type="button" class="am-btn-icon am-btn-add" title="Ajouter une ligne">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="am-btn-icon am-btn-remove" title="Supprimer cette ligne">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 px-4 py-3 am-footer">
                    <button type="button" class="btn am-btn-cancel" data-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn am-btn-submit">
                        Valider l'affectation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ---------- Modal shell ---------- */
.am-modal {
    border-radius: 20px;
    overflow: hidden;
}

.am-header {
    background: #1e2757;
    padding: 1.5rem 1.75rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}
.am-header .modal-title {
    color: #fff;
    font-size: 1.25rem;
    letter-spacing: -0.01em;
}
.am-header-sub {
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.85rem;
}

/* ---------- Fields ---------- */
.am-field label {
    font-size: 0.8rem;
    color: #4b5166;
    margin-bottom: 0.35rem;
}

.am-select,
.am-input {
    border: 1.5px solid #e3e5ec;
    border-radius: 10px;
    padding: 0.6rem 0.85rem;
    font-size: 0.92rem;
    background-color: #f8f9fc;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}
.am-select:focus,
.am-input:focus {
    border-color: #4c5bd4;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(76, 91, 212, 0.15);
}

/* ---------- Dynamic rows ---------- */
.am-row {
    background: #f8f9fc;
    border: 1.5px solid #eceefa;
    border-radius: 14px;
    padding: 1rem 1.1rem;
}

.am-row-grid {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr auto;
    gap: 0.9rem;
    align-items: end;
}

.am-field-heures .am-input {
    text-align: center;
}

.am-field-actions {
    display: flex;
    gap: 0.4rem;
}

.am-btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1.5px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.am-btn-add {
    background: #eef0fd;
    color: #4c5bd4;
}
.am-btn-add:hover { background: #dfe3fb; }

.am-btn-remove {
    background: #fdeeee;
    color: #d64c4c;
}
.am-btn-remove:hover { background: #fbdede; }

/* First row has nothing to remove yet — hide its delete button */
.affectation-row:first-child .am-btn-remove {
    display: none;
}

@media (max-width: 767px) {
    .am-row-grid {
        grid-template-columns: 1fr;
    }
    .am-field-actions {
        justify-content: flex-end;
    }
}

/* ---------- Footer ---------- */
.am-footer {
    background: #fbfbfd;
    border-top: 1px solid #eceefa !important;
}

.am-btn-cancel {
    border-radius: 10px;
    padding: 0.55rem 1.4rem;
    color: #4b5166;
    border: 1.5px solid #e3e5ec;
    background: #fff;
}
.am-btn-cancel:hover { background: #f4f5f9; }

.am-btn-submit {
    border-radius: 10px;
    padding: 0.55rem 1.6rem;
    background: #1e2757;
    color: #fff;
    font-weight: 600;
    border: none;
}
.am-btn-submit:hover { background: #161d42; color: #fff; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('affectation-container');
    if (!container) return;

    container.addEventListener('click', function (e) {
        const addBtn = e.target.closest('.am-btn-add');
        const removeBtn = e.target.closest('.am-btn-remove');

        if (addBtn) {
            const row = addBtn.closest('.affectation-row');
            const clone = row.cloneNode(true);

            // Reset values on the clone
            clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            clone.querySelectorAll('input').forEach(i => i.value = '');
            clone.querySelector('.am-btn-remove').style.display = 'inline-flex';

            clone.style.opacity = '0';
            container.appendChild(clone);
            requestAnimationFrame(() => {
                clone.style.transition = 'opacity 0.2s ease';
                clone.style.opacity = '1';
            });
        }

        if (removeBtn) {
            const row = removeBtn.closest('.affectation-row');
            row.style.transition = 'opacity 0.15s ease';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 150);
        }
    });
});
</script>

    


@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        /* Custom Select2 */
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            border: 1px solid #ced4da;
            min-height: 55px;
            padding: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .select2-container--default .select2-selection__choice {
            background-color: #0d6efd;
            color: #fff;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .select2-container--default .select2-selection__choice__remove {
            margin-right: 6px;
            color: #ffc107;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#matieres').select2({
                placeholder: "Sélectionnez une ou plusieurs matières",
                allowClear: true,
                width: '100%',
            });
            $('#enseignant_id').select2({
                placeholder: "Choisissez un enseignant",
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endpush

