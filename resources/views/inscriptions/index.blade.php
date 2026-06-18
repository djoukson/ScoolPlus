@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3 class="mb-3">Gestion des Inscriptions</h3>

        <!-- Bouton d'ajout -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#inscriptionModal" onclick="openAddInscription()">Nouvelle inscription</button>

        <!-- Tableau -->
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Élève</th>
                <th>Classe</th>
                <th>Année scolaire</th>
                <th>Date d'inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inscriptions as $inscription)
                <tr>
                    <td>{{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}</td>
                    <td>{{ $inscription->classe->nom }}</td>
                    <td>{{ $inscription->annee->nom }}</td>
                    <td>{{ $inscription->date_inscription->format('d/m/Y') }}</td>
                    <td>{{ $inscription->statut ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#inscriptionModal"
                                onclick="openEditInscription({{ $inscription }})">Modifier</button>

                        <form action="{{ route('inscriptions.destroy', $inscription->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette inscription ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="inscriptionModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="inscriptionForm" method="POST">
                    @csrf
                    <input type="hidden" id="inscription_id" name="id">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvelle Inscription</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label>Élève</label>
                                <select name="eleve_id" id="eleve_id" class="form-control" required>
                                    @foreach($eleves as $eleve)
                                        <option value="{{ $eleve->id }}">{{ $eleve->nom }} {{ $eleve->prenom }}</option>
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
                                <label>Année scolaire</label>
                                <select name="annee_id" id="annee_id" class="form-control" required>
                                    @foreach($annees as $annee)
                                        <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Date d'inscription</label>
                                <input type="date" name="date_inscription" id="date_inscription" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Statut</label>
                                <select name="statut" id="statut" class="form-control">
                                    <option value="actif">Actif</option>
                                    <option value="suspendu">Suspendu</option>
                                    <option value="terminé">Terminé</option>
                                </select>
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
        function openAddInscription() {
            document.getElementById('inscriptionForm').action = "{{ route('inscriptions.store') }}";
            document.querySelector('#inscriptionModal .modal-title').innerText = "Nouvelle Inscription";
            document.getElementById('inscriptionForm').reset();
        }

        function openEditInscription(inscription) {
            document.getElementById('inscriptionForm').action = "/inscriptions/" + inscription.id;
            document.querySelector('#inscriptionModal .modal-title').innerText = "Modifier Inscription";

            document.getElementById('inscription_id').value = inscription.id;
            document.getElementById('eleve_id').value = inscription.eleve_id;
            document.getElementById('classe_id').value = inscription.classe_id;
            document.getElementById('annee_id').value = inscription.annee_id;
            document.getElementById('date_inscription').value = inscription.date_inscription.split('T')[0];
            document.getElementById('statut').value = inscription.statut;

            let method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            document.getElementById('inscriptionForm').appendChild(method);
        }
    </script>
@endpush
