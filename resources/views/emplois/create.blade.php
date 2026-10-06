@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <form action="{{ route('emplois.generate', $classe->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-warning" style="margin-bottom: 15px">
                    Générer automatiquement l'emploi du temps
                </button>
            </form>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{route('emplois.index')}}">🔙 Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Creation
                    </li>
                </ol>
            </div>
        </div>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Emploi du temps - {{ $classe->nom }}</h5>
            </div>

            <form id="emploiForm" action="{{ route('emplois.storeMultiple', $classe->id) }}" method="POST">
                @csrf
                <input type="hidden" name="classe_id" value="{{ $classe->id }}">

                <div class="card-body bg-light">
                    {{-- 🔘 Boutons d’action avant le tableau --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                          <button type="button"
        class="btn btn-outline-danger"
        data-toggle="modal"
        data-target="#resetAllModal">
    <i class="fas fa-undo-alt me-1"></i> Réinitialiser tout
</button>
                            <a href="{{ route('emplois.print', $classe->id) }}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-print"></i> Imprimer
                            </a>

                        </div>
                    </div>

                    {{-- 📅 Tableau principal --}}
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle shadow-sm rounded-3 bg-white">
                            <thead class="table-primary">
                            <tr>
                                <th class="bg-light">Jours</th>
                                @foreach ($heures as $heure)
                                    <th>
                                        {{ $heure->libelle }}
                                        <br>
                                        <small class="text-muted">
                                            {{ substr($heure->heure_debut,0,5) }} - {{ substr($heure->heure_fin,0,5) }}
                                        </small>
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $jour)
                                <tr>
                                    <td class="fw-bold text-start bg-light ps-3">{{ $jour }}</td>

                                    @foreach ($heures as $heure)
                                        @php
                                            $emploiKey = $jour . '_' . $heure->id;
                                            $emploi = $emploisExistants[$emploiKey][0] ?? null;
                                            $isAffecte = !empty($emploi);
                                        @endphp

                                        <td class="{{ $isAffecte ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                            @if($heure->libelle === 'Pause')
                                                <span class="text-secondary fw-semibold">Pause</span>
                                            @else
                                                <select name="emplois[{{ $jour }}][{{ $heure->id }}]"
                                                        class="form-select form-select-sm {{ $isAffecte ? 'select-affected' : 'select-free' }}">
                                                    <option value="">--</option>

                                                    @foreach ($affectations as $aff)
                                                        @php
                                                            $enseignantId = $aff->enseignant->id;
                                                            $key = $jour . '_' . $heure->id;
                                                            $enseignantOccupe = isset($enseignantsOccupes[$key]) && in_array($enseignantId, $enseignantsOccupes[$key]);
                                                            $heuresDejaPlanifiees = \App\Models\EmploiDuTemps::where('affectation_id', $aff->id)->count();
                                                            $heuresRestantes = $aff->heures_attribuees - $heuresDejaPlanifiees;
                                                        @endphp

                                                        <option value="{{ $aff->id }}"
                                                            {{ $emploi && $emploi->affectation_id == $aff->id ? 'selected' : '' }}
                                                            {{ $enseignantOccupe && (!$emploi || $emploi->affectation_id != $aff->id) ? 'disabled' : '' }}>
                                                            {{ $aff->matiere->nom }} — {{ $aff->enseignant->nom }}
                                                            @if($enseignantOccupe && (!$emploi || $emploi->affectation_id != $aff->id))
                                                                🟥 (Occupé)
                                                            @else
                                                                ({{ $heuresRestantes }}h / {{ $aff->heures_attribuees }}h)
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ Bouton enregistrer final (en bas du tableau) --}}
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Enregistrer l’emploi du temps
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{{-- ================= MODAL RÉINITIALISATION ================= --}}
<div class="modal fade" id="resetAllModal" tabindex="-1" role="dialog"
     aria-labelledby="resetAllModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resetAllModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Réinitialiser l'emploi du temps
                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal"
                        aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center py-4">

                <div style="font-size: 50px;" class="text-danger mb-3">
                    <i class="fas fa-trash-alt"></i>
                </div>

                <h5 class="mb-3">
                    Voulez-vous vraiment réinitialiser tout l'emploi du temps ?
                </h5>

                <p class="text-muted mb-0">
                    Tous les cours actuellement planifiés pour la classe
                    <strong>{{ $classe->nom }}</strong> seront supprimés.
                </p>

                <p class="text-danger mt-2 mb-0">
                    <strong>Cette action est irréversible.</strong>
                </p>

            </div>

            <div class="modal-footer justify-content-center">

                <button type="button"
                        class="btn btn-secondary px-4"
                        data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>

                <form action="{{ route('emplois.reset', $classe->id) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-danger px-4">
                        <i class="fas fa-trash-alt me-1"></i>
                        Oui, réinitialiser
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>
    {{-- === Styles & Script === --}}
    <style>
        .bg-gradient-primary {
            background: linear-gradient(90deg, #007bff, #00aaff);
        }
        .table-bordered td, .table-bordered th {
            vertical-align: middle;
            text-align: center;
        }
        .table thead th {
            white-space: nowrap;
        }
        .form-select-sm {
            min-width: 170px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            font-size: 0.9rem;
        }
        .form-select-sm:focus {
            border-color: #00aaff;
            box-shadow: 0 0 6px rgba(0, 170, 255, 0.4);
        }
        .bg-success-subtle {
            background-color: #e8fbe8 !important;
        }
        .bg-danger-subtle {
            background-color: #fbe8e8 !important;
        }
        .select-affected {
            background-color: #e8fbe8;
            border: 1px solid #a2d5a2;
        }
        .select-free {
            background-color: #fdf7f7;
            border: 1px solid #f0c0c0;
        }
        option[disabled] {
            color: #999;
            background-color: #f8f9fa;
            font-style: italic;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .card, .card * {
                visibility: visible;
            }
            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            button, .btn, .form-select {
                display: none !important;
            }
        }
    </style>


@endsection
