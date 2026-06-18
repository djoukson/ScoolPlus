@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3">Gestion des Affectations</h3>

        <!-- Bouton d'ajout -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#affectationModal" onclick="openAddAffectation()">Nouvelle Affectation</button>

        <!-- Tableau -->
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Enseignant</th>
                <th>Classe</th>
                <th>Matière</th>
                <th>Année</th>
                <th>Heures attribuées</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($affectations as $aff)
                <tr>
                    <td>{{ $aff->enseignant->nom }} {{ $aff->enseignant->prenom }}</td>
                    <td>{{ $aff->class->nom }}</td>
                    <td>{{ $aff->matiere->nom }}</td>
                    <td>{{ $aff->annees_scolaire->nom }}</td>
                    <td>{{ $aff->heures_attribuees ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#affectationModal"
                                onclick="openEditAffectation({{ $aff }})">Modifier</button>

                        <form action="{{ route('affectations.destroy', $aff->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette affectation ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="affectationModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="affectationForm" method="POST">
                    @csrf
                    <input type="hidden" id="affectation_id" name="id">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvelle Affectation</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">

                            <div class="form-group">
                                <label>Enseignant</label>
                                <select name="enseignant_id" id="enseignant_id" class="form-control" required>
                                    @foreach($enseignants as $ens)
                                        <option value="{{ $ens->id }}">{{ $ens->nom }} {{ $ens->prenom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Classe</label>
                                <select name="classe_id" id="classe_id" class="form-control" required>
                                    @foreach($classes as $classe)
                                        <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Matière</label>
                                <select name="matiere_id" id="matiere_id" class="form-control" required>
                                    @foreach($matieres as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Année scolaire</label>
                                <select name="annee_id" id="annee_id" class="form-control" required>
                                    @foreach($annees as $annee)
                                        <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Heures attribuées</label>
                                <input type="number" name="heures_attribuees" id="heures_attribuees" class="form-control">
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAddAffectation() {
            document.getElementById('affectationForm').action = "{{ route('affectations.store') }}";
            document.querySelector('#affectationModal .modal-title').innerText = "Nouvelle Affectation";
            document.getElementById('affectationForm').reset();
        }

        function openEditAffectation(aff) {
            document.getElementById('affectationForm').action = "/affectations/" + aff.id;
            document.querySelector('#affectationModal .modal-title').innerText = "Modifier Affectation";

            document.getElementById('enseignant_id').value = aff.enseignant_id;
            document.getElementById('classe_id').value = aff.classe_id;
            document.getElementById('matiere_id').value = aff.matiere_id;
            document.getElementById('annee_id').value = aff.annee_id;
            document.getElementById('heures_attribuees').value = aff.heures_attribuees ?? '';

            let method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            document.getElementById('affectationForm').appendChild(method);
        }
    </script>
@endpush
