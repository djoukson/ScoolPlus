@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Breadcrumb --}}
        <div class="pro-breadcrumb mb-4">
            <div class="breadcrumb-title">💰 Gestion des Taux Horaires</div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Taux Horaires</li>
                </ol>
            </div>
        </div>

        {{-- Bouton Ajouter --}}
        <div class="mb-3 text-end">
            <button class="btn btn-success" data-toggle="modal" data-target="#addTauxModal">
                ➕ Ajouter un taux horaire
            </button>
        </div>

        {{-- Tableau --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Liste des Taux Horaires
            </div>
            <div class="card-body p-2">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-primary">
                    <tr>
                        <th>Niveau</th>
                        <th>Taux Horaire (FCFA)</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tauxHoraires as $th)
                        <tr>
                            <td>{{ $th->niveau->nom }}</td>
                            <td>{{ number_format($th->taux, 2, ',', ' ') }}</td>
                            <td>
                                <div class="btn-group">
                                    <button style="margin-right: 15px" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editTauxModal{{ $th->id }}">✏️ Modifier</button>
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteTauxModal{{ $th->id }}">🗑️ Supprimer</button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="editTauxModal{{ $th->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow-lg border-0 rounded-3">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">✏️ Modifier Taux Horaire</h5>
                                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('taux_horaires.update', $th->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="niveau_id" class="form-label">Niveau</label>
                                                <select name="niveau_id" id="niveau_id" class="form-select" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    @foreach(App\Models\Niveau::where('nom', '!=', 'Primaire')->get() as $niveau)
                                                        <option value="{{ $niveau->id }}">
                                                            {{ $niveau->nom }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="taux" class="form-label">Taux Horaire (FCFA)</label>
                                                <input type="number" step="0.01" name="taux" class="form-control" value="{{ $th->taux }}" required>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-info">💾 Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Delete --}}
                        <div class="modal fade" id="deleteTauxModal{{ $th->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow-lg border-0 rounded-3">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">🗑️ Supprimer Taux Horaire</h5>
                                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Voulez-vous vraiment supprimer le taux horaire du niveau <strong>{{ $th->niveau->nom }}</strong> ?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <form action="{{ route('taux_horaires.destroy', $th->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Aucun taux horaire défini</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="addTauxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-3">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">➕ Ajouter un Taux Horaire</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('taux_horaires.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="niveau_id" class="form-label">Niveau</label>
                            <select name="niveau_id" id="niveau_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach(App\Models\Niveau::where('nom', '!=', 'Primaire')->get() as $niveau)
                                    <option value="{{ $niveau->id }}" {{ isset($tauxHoraire) && $tauxHoraire->niveau_id == $niveau->id ? 'selected' : '' }}>
                                        {{ $niveau->nom }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taux" class="form-label">Taux Horaire (FCFA)</label>
                            <input type="number" step="0.01" name="taux" class="form-control" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">✅ Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
