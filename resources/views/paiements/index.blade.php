@extends('layouts.app')

@section('title', 'Gestion des paiements')
@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Paiements
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="{{ route('paiements.create') }}"   class="btn btn-outline-info">
                    ➕  Nouveau paiement
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Gestion des Paiements
                    </li>
                </ol>
            </div>
        </div>

        {{-- 🔹 Formulaire de recherche --}}
        <form method="GET" class="mb-3 d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par élève ou classe"
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary">Rechercher</button>
        </form>

        {{-- 🔹 Tableau --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>
                            <a href="?sort=nom&direction={{ request('direction') === 'asc' ? 'desc' : 'asc' }}" class="text-white">
                                Élève
                                @if(request('sort') === 'nom')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="?sort=classe&direction={{ request('direction') === 'asc' ? 'desc' : 'asc' }}" class="text-white">
                                Classe
                                @if(request('sort') === 'classe')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>Année</th>
                        <th>Bourse</th>
                        <th>Total payé</th>
                        <th>Restant</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['eleve']->nom }} {{ $item['eleve']->prenom }}</td>
                            <td>{{ $item['classe']->nom }}</td>
                            <td>{{ $item['annee']->nom }}</td>

                            {{-- 🔹 Bourse --}}
                            <td>
                                @if($item['bourse'])
                                    <span class="badge bg-info text-dark">
                                        {{ $item['bourse']->nom }} ({{ implode('%, ', $item['bourse']->frais->pluck('pivot.pourcentage')->toArray()) }}%)
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- 🔹 Total payé --}}
                            <td>
                                <span class="badge bg-success">
                                    {{ number_format($item['total_paye'], 0, ',', ' ') }} FCFA
                                </span>
                            </td>

                            {{-- 🔹 Reste --}}
                            <td>
                                @if($item['reste_total'] < 0)
                                    <span class="badge bg-danger">
                                        {{ number_format(abs($item['reste_total']), 0, ',', ' ') }} FCFA (Trop payé)
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        {{ number_format($item['reste_total'], 0, ',', ' ') }} FCFA
                                    </span>
                                @endif
                            </td>

                            {{-- 🔹 Actions --}}
                            <td class="text-center">
                                <a href="{{ route('paiements.listeeleve', $item['eleve']->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Aucun paiement enregistré.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination si nécessaire --}}
            <div class="d-flex justify-content-center mt-3">
                {{-- $data->links() --}}
            </div>
        </div>
    </div>

    {{-- 🔹 Style pour impression --}}
    <style>
        @media print {
            .btn { display: none; }
            .card { box-shadow: none; border: 1px solid #000; }
        }
    </style>
@endsection
