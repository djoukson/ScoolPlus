@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                🧾 Gestion des Types d’évaluations
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#addTypeEvalModal"   class="btn btn-outline-info">
                    ➕  Nouveau types d'evaluation
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Types d’évaluations
                    </li>
                </ol>
            </div>
        </div>

        <!-- Tableau centré et réduit -->
        <div class="table-responsive shadow-sm rounded mx-auto" style="max-width: 700px;">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($typesEvaluations as $type)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $type->nom }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editTypeEvalModal{{ $type->id }}">✏️</button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteTypeEvalModal{{ $type->id }}">🗑️</button>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editTypeEvalModal{{ $type->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('types_evaluations.update', $type) }}">
                                @csrf
                                @method('PUT')

                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Modifier Type d’évaluation</h5>
                                    </div>

                                    <div class="modal-body">
                                        <label class="form-label">Type d’évaluation</label>
                                        <select name="nom" class="form-control" required>
                                            <option value="Devoir" {{ $type->nom == 'Devoir' ? 'selected' : '' }}>Devoir 1</option>
                                            <option value="Devoir2" {{ $type->nom == 'Devoir2' ? 'selected' : '' }}>Devoir 2</option>
                                            <option value="Devoir3" {{ $type->nom == 'Devoir3' ? 'selected' : '' }}>Devoir 3</option>
                                            <option value="Devoir4" {{ $type->nom == 'Devoir4' ? 'selected' : '' }}>Devoir 4</option>
                                            <option value="Composition" {{ $type->nom == 'Composition' ? 'selected' : '' }}>Composition</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


                    <!-- Modal Delete -->
                    <div class="modal fade" id="deleteTypeEvalModal{{ $type->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('types_evaluations.destroy', $type) }}">
                                @csrf @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Confirmation de suppression</h5>
                                    </div>
                                    <div class="modal-body">
                                        <p>Voulez-vous vraiment supprimer le type d’évaluation <strong>{{ $type->nom }}</strong> ? Cette action est irréversible.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                @empty
                    <tr><td colspan="3" class="text-center">Aucun type d’évaluation défini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="addTypeEvalModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('types_evaluations.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Nouveau Type d’évaluation</h5>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Type d’évaluation</label>
                        <select name="nom" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            <option value="Devoir">Devoir 1</option>
                            <option value="Devoir2">Devoir 2</option>
                            <option value="Devoir3">Devoir 3</option>
                            <option value="Devoir4">Devoir 4</option>
                            <option value="Composition">Composition</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
