@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Présences des enseignants
            </div>
            <button class="btn btn-primary" style="margin-bottom: 10px" data-toggle="modal" data-target="#addModal">
                + Nouvelle présence
            </button>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajouter des présences
                    </li>
                </ol>
            </div>
        </div>

        {{-- TABLE --}}
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Enseignant</th>
                <th>Nombre d'Heures</th>
                <th>Motif</th>
                <th width="140">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($presences as $p)
                <tr>
                    <td>{{ $p->date }}</td>
                    <td>{{ $p->enseignant->nom }} {{ $p->enseignant->prenom }}</td>
                    <td class="text-center">{{ $p->nombre_heures }}</td>
                    <td>{{ $p->motif }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#editModal{{ $p->id }}">
                            ✏
                        </button>

                        <form action="{{ route('presences-enseignants.destroy',$p) }}"
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer cette présence ?')">
                                🗑
                            </button>
                        </form>
                    </td>
                </tr>

                {{-- MODAL EDIT --}}
                <div class="modal fade" id="editModal{{ $p->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('presences-enseignants.update',$p) }}">
                            @csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier présence</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Enseignant</label>
                                        <select name="enseignant_id" class="form-control" required>
                                            @foreach($enseignants as $e)
                                                <option value="{{ $e->id }}"
                                                    @selected($e->id == $p->enseignant_id)>
                                                    {{ $e->nom }} {{ $e->prenom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control"
                                               value="{{ $p->date }}" required>
                                    </div>

                                    <div class="row">
                                        <div class="col">
                                            <label>Heures prévues</label>
                                            <input type="number" name="nombre_heures"
                                                   class="form-control"
                                                   value="{{ $p->nombre_heures }}">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <label>Motif</label>
                                        <input type="text" name="motif"
                                               class="form-control"
                                               value="{{ $p->motif }}">
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                                        Annuler
                                    </button>
                                    <button class="btn btn-success">
                                        Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- MODAL ADD --}}
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('presences-enseignants.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle présence</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Enseignant</label>
                            <select name="enseignant_id" class="form-control" required>
                                <option value="" selected disabled>
                                    ---- Sélectionner le Prof ----
                                </option>

                                @foreach($enseignants as $e)
                                    <option value="{{ $e->id }}">
                                        {{ $e->nom }} {{ $e->prenom }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        <div class="mb-2">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label>Nombre d'Heures</label>
                                <input type="number" name="nombre_heures"
                                       class="form-control" min="1" value="1">
                            </div>
                        </div>

                        <div class="mt-2">
                            <label>Motif</label>
                            <input type="text" name="motif" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button class="btn btn-primary">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
