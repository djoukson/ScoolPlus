@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Breadcrumb --}}
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Dépenses
            </div>
            <button class="btn btn-outline-info mb-3" data-toggle="modal" data-target="#addModal" style="margin-bottom: 10px">Ajouter une dépense</button>
            {{-- Filtre par date --}}
            <form method="GET" action="{{ route('depenses.print') }}" target="_blank" class="row g-1 mb-2">
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" required value="{{ request('date_start') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" required value="{{ request('date_end') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">🖨️ Imprimer</button>
                </div>
            </form>



            {{-- Breadcrumb --}}
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Les dépenses</li>
                </ol>
            </div>
        </div>

            {{-- Bouton ajouter --}}

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">Dépenses</div>
            <div class="card-body table-responsive">
          <table class="table table-bordered table-hover table-striped">
                    <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Montant (FCFA)</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($depenses as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->titre }}</td>
                    <td>{{ $d->description }}</td>
                    <td>{{ number_format($d->montant, 0, ',', ' ') }}</td>
                    <td>{{ $d->date_depense->format('d/m/Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $d->id }}">Éditer</button>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteDepense{{ $d->id }}">
                            Supprimer
                        </button>

                    </td>
                </tr>
                <!-- Modal Suppression Dépense -->
                <div class="modal fade" id="deleteDepense{{ $d->id }}" tabindex="-1" aria-labelledby="deleteDepenseLabel{{ $d->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="deleteDepenseLabel{{ $d->id }}">🗑️ Confirmer la suppression</h5>
                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                Voulez-vous vraiment supprimer la dépense : <strong>{{ $d->titre }}</strong> ?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <form action="{{ route('depenses.destroy', $d) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal édition --}}
                <div class="modal fade" id="editModal{{ $d->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('depenses.update', $d) }}">
                            @csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier dépense</h5>
                                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Titre</label>
                                        <input type="text" name="titre" class="form-control" value="{{ $d->titre }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control">{{ $d->description }}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label>Montant</label>
                                        <input type="number" name="montant" class="form-control" value="{{ $d->montant }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Date</label>
                                        <input type="date" name="date_depense" class="form-control" value="{{ $d->date_depense->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                    <button class="btn btn-success">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            @endforeach
            </tbody>
        </table>
            </div>
            </div>
            </div>
        {{-- Modal ajout --}}
        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('depenses.store') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une dépense</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label>Titre</label>
                                <input type="text" name="titre" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                            <div class="mb-2">
                                <label>Montant</label>
                                <input type="number" name="montant" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Date</label>
                                <input type="date" name="date_depense" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button class="btn btn-success">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    @push('scripts')
        <script>
            function printFiltered() {
                let printContents = document.querySelector('.card-body table').outerHTML;
                let originalContents = document.body.innerHTML;

                document.body.innerHTML = `
            <h3 class="text-center">Dépenses imprimées</h3>
            ${printContents}
        `;
                window.print();
                document.body.innerHTML = originalContents;
                window.location.reload(); // recharge pour réinitialiser les modals et scripts
            }
        </script>
    @endpush

@endsection
