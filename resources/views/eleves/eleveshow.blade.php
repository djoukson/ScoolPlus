{{--eleveshow--}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Classe : {{ $classe->nom }}
            </div>
            {{-- Bouton Liste des classes --}}
            <div style="margin-bottom: 10px">
                <a href="{{ route('eleves.index') }}" class="btn btn-success me-2" >
                    ➕ Ajouter un élève
                </a>
                <button class="btn btn-primary me-2" data-toggle="modal" data-target="#affecterElevesModal">
                    👥 Affecter élèves
                </button>
                <button class="btn btn-primary me-2" data-toggle="modal" data-target="#importerElevesModal">
                    📥 Importer / Affecter élèves
                </button>
                <button class="btn btn-danger me-2"
                        data-toggle="modal"
                        data-target="#viderClasseModal">
                    🗑️ Vider la classe
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle"
                            data-toggle="dropdown" aria-expanded="false"
                        {{ $eleves->isEmpty() ? 'disabled' : '' }}>
                        🖨️ Imprimer
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item d-flex align-items-center"
                               href="{{ route('classes.imprimer', $classe->id) }}" target="_blank"
                               style="color: #0d6efd;">
                                <i class="fas fa-list-ul me-2"></i> Liste de classe
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center"
                               href="{{ route('classes.imprimer.complete', $classe->id) }}" target="_blank"
                               style="color: #198754;">
                                <i class="fas fa-list-alt me-2"></i> Liste complète
                            </a>
                        </li>
                    </ul>
                </div>

                <style>
                    .dropdown-item:hover {
                        background-color: #f8f9fa; /* couleur de fond au hover */
                        color: #212529 !important; /* couleur du texte au hover */
                    }
                </style>

            </div>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('listeclasses.index') }}">Classes</a>

                    </li>
                    <li class="breadcrumb-item active">
                        Gestion de la Classes
                    </li>
                </ol>
            </div>
        </div>



        <!-- Barre de recherche -->
        <form method="GET" action="{{ route('elevesshow', $classe->id) }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="🔍 Rechercher un élève (nom, prénom, matricule)...">
                <button class="btn btn-outline-primary" type="submit">Rechercher</button>
            </div>
        </form>


        <!-- Tableau élèves -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Liste des élèves ({{ $eleves->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Date de naissance</th>
                        <th>Genre</th>
                        <th>Tuteur</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($eleves as $index => $eleve)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $eleve->id }}</td>
                            <td>{{ $eleve->nom }} {{ $eleve->prenom }}</td>
                            <td>{{ $eleve->date_naissance }}</td>
                            <td>{{ ucfirst($eleve->sexe) }}</td>
                            <td>{{ $eleve->tuteur_nom .' - '. $eleve->tuteur_tel }}</td>
                            <td>{{ $eleve->adresse }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            id="actionsEleve{{ $eleve->id }}" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-cogs me-1"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="actionsEleve{{ $eleve->id }}">
                                        <li class="dropdown-header">Type d’inscription</li>
                                        @foreach(['Nouveau', 'Réinscrit'] as $typeInscription)
                                            <li>
                                                <form method="POST" action="{{ route('inscriptions.type.update', $eleve->inscription_id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                                                    <input type="hidden" name="type_inscription" value="{{ $typeInscription }}">
                                                    <button type="submit" class="dropdown-item {{ $eleve->type_inscription === $typeInscription ? 'active' : '' }}">
                                                        <i class="fas {{ $typeInscription === 'Nouveau' ? 'fa-user-plus' : 'fa-user-check' }} me-2"></i>
                                                        {{ $typeInscription }}
                                                        @if($eleve->type_inscription === $typeInscription)
                                                            <i class="fas fa-check ml-2"></i>
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                        <li><div class="dropdown-divider"></div></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-toggle="modal" data-target="#confirmRetraitModal{{ $eleve->id }}">
                                                <i class="fas fa-user-minus me-2"></i> Retirer de la classe
                                            </button>
                                        </li>
                                    </ul>
                                </div>
{{--                                <a href="{{ route('eleves.show', $eleve->id) }}" class="btn btn-sm btn-info">👀</a>--}}

                            </td>
                        </tr>

                        <!-- Modal Confirmation -->
                        <div class="modal fade" id="confirmRetraitModal{{ $eleve->id }}" tabindex="-1" aria-labelledby="confirmRetraitLabel{{ $eleve->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow-lg">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="confirmRetraitLabel{{ $eleve->id }}">
                                            <i class="bi bi-exclamation-triangle me-2"></i> Confirmation
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        Voulez-vous vraiment <strong>retirer {{ $eleve->nom }} {{ $eleve->prenom }}</strong> de la classe <strong>{{ $classe->nom }}</strong> ?<br>
                                        <small class="text-danger">
                                            Cette action supprimera également tous ses paiements de scolarité et ses attributions de bourse si l’élève en possède.
                                        </small>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">❌ Annuler</button>

                                        <form action="{{ route('classes.retirerEleve', [$classe->id, $eleve->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                ✅ Confirmer le retrait
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun élève trouvé dans cette classe.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addEleveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- ✅ modal-lg pour plus d'espace -->
            <form method="POST" action="{{ route('storeeleveinscription') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">➕ Ajouter un élève</h5>
                        <button type="button" class="btn-close" data-dismiss="modal"></button>
                    </div>
                    <input type="hidden" name="classe_id" value="" required>
                    <input type="hidden" name="annee_id" value="" required>
                    <div class="modal-body">

                        <div class="row g-3">
                            <!-- Nom et Prénom -->
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control"
                                       value="" required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control"
                                       value="" required>
                            </div>

                            <!-- Date de naissance et Sexe -->
                            <div class="col-md-6">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control"
                                       value="" required>
                            </div>
                            <div class="col-md-6">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select name="sexe" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="M" >Masculin</option>
                                    <option value="F" >Féminin</option>
                                </select>
                            </div>

                            <!-- Adresse et Classe -->
                            <div class="col-md-12">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control"
                                       value="" required>
                            </div>

                            <!-- Tuteur nom et Tuteur tel -->
                            <div class="col-md-6">
                                <label for="tuteur_nom" class="form-label">Nom du tuteur</label>
                                <input type="text" name="tuteur_nom" class="form-control"
                                       value="" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tuteur_tel" class="form-label">Téléphone du tuteur</label>
                                <input type="text" name="tuteur_tel" class="form-control"
                                       value="" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            💾 Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="affecterElevesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('classes.storeEleves', $classe->id) }}">
                @csrf
                <div class="modal-content border-0 shadow-lg rounded-4">
                    {{-- Header --}}
                    <div class="modal-header bg-gradient text-white" style="background: linear-gradient(90deg, #0d6efd, #0dcaf0);">
                        <h5 class="modal-title">
                            <i class="bi bi-person-check-fill me-2"></i>
                            Affecter des élèves à <span class="fw-bold">{{ $classe->nom }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body">
                        {{-- Actions rapides --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="input-group w-50">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchEleveInput" placeholder="Rechercher un élève...">
                            </div>
                            <button type="button" id="toggleAllBtn" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-check2-square"></i> Tout cocher
                            </button>
                        </div>

                        <div class="row g-3" id="eleveList">
                            @php
                                $anneeId = session('annee_id') ?? \App\Models\AnneesScolaire::where('active', 1)->value('id');
                                $elevesSansClasse = \App\Models\Eleve::whereDoesntHave('inscriptions', function($q) use ($anneeId) {
                                    $q->where('annee_id', $anneeId);
                                })->get();
                            @endphp

                            @forelse($elevesSansClasse as $eleve)
                                <div class="col-md-4 eleve-item">
                                    <div class="card shadow-sm border-0 h-100 hover-card">
                                        <div class="card-body d-flex align-items-center">
                                            {{-- Checkbox --}}
                                            <div class="form-check me-3">
                                                <input class="form-check-input eleve-checkbox" type="checkbox"
                                                       name="eleves[]" value="{{ $eleve->id }}" id="eleve{{ $eleve->id }}">
                                            </div>

                                            {{-- Avatar circle --}}
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px; font-size:18px;">
                                                {{ strtoupper(substr($eleve->prenom,0,1)) }}
                                            </div>

                                            {{-- Infos élève --}}
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $eleve->nom }} {{ $eleve->prenom }}</h6>
                                                <small class="text-muted">
                                                    <span class="badge bg-secondary">#{{ $eleve->matricule }}</span>
                                                    @if($eleve->sexe == 'M')
                                                        <i class="bi bi-gender-male text-primary"></i>
                                                    @elseif($eleve->sexe == 'F')
                                                        <i class="bi bi-gender-female text-danger"></i>
                                                    @endif
                                                </small>
                                            </div>

                                            {{-- Tuteur --}}
                                            <div class="text-muted small text-end d-none d-md-block">
                                                👤 {{ $eleve->tuteur_nom ?? '—' }} <br>
                                                📞 {{ $eleve->tuteur_tel ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">✅ Tous les élèves sont déjà affectés pour cette année scolaire.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle"></i> Enregistrer la sélection
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{{-- ========================================================= --}}
{{-- MODAL IMPORT / AFFECTATION DES ÉLÈVES --}}
{{-- ========================================================= --}}

<div class="modal fade" id="importerElevesModal"
     tabindex="-1"
     aria-labelledby="importerElevesModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4">

            {{-- HEADER --}}
            <div class="modal-header text-white"
                 style="background: linear-gradient(90deg, #0d6efd, #0dcaf0);">

                <div>
                    <h5 class="modal-title mb-1" id="importerElevesModalLabel">
                        📥 Importer les élèves
                    </h5>

                    <small>
                        Affectation vers :
                        <strong>{{ $classe->nom }}</strong>
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-dismiss="modal">
                </button>

            </div>


            {{-- BODY --}}
            <div class="modal-body">

                {{-- ================================================= --}}
                {{-- CHOIX DE LA MÉTHODE --}}
                {{-- ================================================= --}}

                <div class="row mb-4">

                    <div class="col-md-10">

                        <div class="import-method-card active"
                             id="methodClassCard"
                             onclick="showImportMethod('class')">

                            <div class="method-icon">
                                📚
                            </div>

                            <div>
                                <h6>Importer depuis une classe</h6>

                                <small class="text-muted">
                                    Réutiliser les élèves d'une classe d'une
                                    autre année scolaire.
                                </small>
                            </div>

                        </div>

                    </div>


                   <!-- <div class="col-md-6">

                        <div class="import-method-card"
                             id="methodTextCard"
                             onclick="showImportMethod('text')">

                            <div class="method-icon">
                                📝
                            </div>

                            <div>
                                <h6>Coller une liste</h6>

                                <small class="text-muted">
                                    Coller rapidement les matricules des élèves.
                                </small>
                            </div>

                        </div>

                    </div>-->

                </div>


                {{-- ================================================= --}}
                {{-- MÉTHODE 1 : CLASSE SOURCE --}}
                {{-- ================================================= --}}

                <div id="importClassSection">

                    <div class="alert alert-info border-0">

                        <strong>💡 Import rapide</strong>

                        <br>

                        Sélectionne une ancienne classe.
                        SchoolPlus retrouvera automatiquement les élèves
                        inscrits dans cette classe.

                    </div>


                    <form id="importClassForm"
                          method="POST"
                          action="{{ route('classes.importerEleves', $classe->id) }}">

                        @csrf


                        <div class="row align-items-end">

                            <div class="col-md-8">

                                <label class="form-label fw-bold">
                                    Classe source
                                </label>

                                <select name="classe_source_id"
        id="classe_source_id"
        class="form-control"
        required>

    <option value="">
        -- Sélectionner une classe de l'année précédente --
    </option>

    @foreach($classess as $classeSource)

        <option value="{{ $classeSource->id }}">
            {{ $classeSource->nom }}
            ({{ $classeSource->effectif }} élèves)
        </option>

    @endforeach

</select>

                            </div>


                            <div class="col-md-4">

                                <button type="submit"
                                        class="btn btn-primary w-100">

                                    📥 Importer toute la classe

                                </button>

                            </div>

                        </div>

                    </form>


                    <hr class="my-4">


                    <div class="text-muted small">

                        ⚠️ Les élèves déjà inscrits dans
                        <strong>{{ $classe->nom }}</strong>
                        pour l'année scolaire active seront automatiquement
                        ignorés.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
{{-- ========================================================= --}}
{{-- MODAL VIDER LA CLASSE --}}
{{-- ========================================================= --}}

<div class="modal fade"
     id="viderClasseModal"
     tabindex="-1"
     aria-labelledby="viderClasseModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            {{-- HEADER --}}
            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title" id="viderClasseModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Vider la classe
                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal"
                        aria-label="Fermer">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            {{-- BODY --}}
            <div class="modal-body">

                <div class="alert alert-warning">

                    <strong>⚠️ Attention !</strong>

                    <p class="mb-0 mt-2">
                        Vous êtes sur le point de retirer tous les élèves
                        de la classe :
                    </p>

                    <h5 class="mt-2 text-danger">
                        {{ $classe->nom }}
                    </h5>

                </div>

                <p>
                    Cette opération va supprimer les
                    <strong>inscriptions des élèves dans cette classe</strong>
                    pour l'année scolaire concernée.
                </p>

                <p class="text-muted mb-0">
                    Les élèves eux-mêmes ne seront pas supprimés de SchoolPlus.
                    Ils pourront être réaffectés ultérieurement à une autre classe.
                </p>

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">

                    ❌ Annuler

                </button>

                <form action="{{ route('classes.viderEleves', $classe->id) }}"
                      method="POST">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-danger">

                        🗑️ Oui, vider la classe

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
<style>

    /* ================================ */
    /* CARTES CHOIX IMPORT */
    /* ================================ */

    .import-method-card {

        display: flex;
        align-items: center;

        gap: 15px;

        padding: 18px;

        border: 2px solid #e9ecef;

        border-radius: 12px;

        cursor: pointer;

        transition: all .2s ease;

        background: #fff;

    }


    .import-method-card:hover {

        border-color: #0d6efd;

        background: #f8fbff;

        transform: translateY(-2px);

    }


    .import-method-card.active {

        border-color: #0d6efd;

        background: #f0f7ff;

        box-shadow: 0 4px 12px rgba(13,110,253,.12);

    }


    .method-icon {

        width: 50px;

        height: 50px;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #e7f1ff;

        font-size: 23px;

    }


    .import-method-card h6 {

        margin: 0 0 4px 0;

        font-weight: 700;

    }


    /* ================================ */
    /* TEXTAREA */
    /* ================================ */

    .import-textarea {

        font-family: monospace;

        font-size: 15px;

        line-height: 1.7;

        border-radius: 10px;

        padding: 15px;

        background: #f8f9fa;

        resize: vertical;

    }


    .import-textarea:focus {

        background: #fff;

        border-color: #0d6efd;

        box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);

    }


    /* ================================ */
    /* RESULTAT IMPORT */
    /* ================================ */

    .import-result-table {

        max-height: 300px;

        overflow-y: auto;

    }

</style>
    {{-- Script recherche + toggle --}}
    <script>
        // Recherche en live
        document.getElementById("searchEleveInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#eleveList .eleve-item").forEach(function (item) {
                let text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? "" : "none";
            });
        });

        // Tout cocher/décocher
        document.getElementById("toggleAllBtn").addEventListener("click", function () {
            let checkboxes = document.querySelectorAll(".eleve-checkbox");
            let allChecked = Array.from(checkboxes).every(ch => ch.checked);
            checkboxes.forEach(ch => ch.checked = !allChecked);
            this.innerHTML = allChecked
                ? '<i class="bi bi-check2-square"></i> Tout cocher'
                : '<i class="bi bi-x-square"></i> Tout décocher';
        });
    </script>

    <style>
        .hover-card:hover {
            transform: translateY(-3px);
            transition: 0.2s ease-in-out;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
    </style>





    <style>
        .table-hover tbody tr:hover {
            background-color: #f1f7ff;
            transition: background-color 0.2s ease-in-out;
        }

        .table .dropdown-menu {
            z-index: 1050;
            background-color: #fff;
            opacity: 1;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>

    @if(request('modal') == 'eleve')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var modal = new bootstrap.Modal(document.getElementById('addEleveModal'));
                modal.show();
            });
        </script>
    @endif
    <script>

function showImportMethod(method)
{
    const classSection = document.getElementById('importClassSection');
    const textSection  = document.getElementById('importTextSection');

    const classCard = document.getElementById('methodClassCard');
    const textCard  = document.getElementById('methodTextCard');


    if (method === 'class') {

        classSection.style.display = 'block';
        textSection.style.display = 'none';

        classCard.classList.add('active');
        textCard.classList.remove('active');

    } else {

        classSection.style.display = 'none';
        textSection.style.display = 'block';

        classCard.classList.remove('active');
        textCard.classList.add('active');

    }
}

</script>
@endsection
