@extends('layouts.app')

@section('title', 'Liste des Classes')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Classes {{ $anneeActive ? $anneeActive->nom : ' ' }}
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="{{route('classes.index')}}" class="btn btn-outline-info">
                    ➕ Nouvelle classe
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Gestion des Classes
                    </li>
                </ol>
            </div>
        </div>


        <div class="card shadow-lg border-0 overflow-hidden">

            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="classesTable" class="table table-striped table-hover align-middle" style="width:100%">
                        <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom de la classe</th>
                            <th>Niveau</th>
                            <th>Effectif</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($classes as $index => $classe)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $classe->nom }}</td>
                                <td>
                                    @php
                                        $niveauColors = [
                                            'primaire'   => 'bg-info',
                                            'college'    => 'bg-success',
                                            'lycee'      => 'bg-warning',
                                        ];

                                        $badgeColor = $niveauColors[strtolower($classe->niveau->nom)] ?? 'bg-secondary';
                                    @endphp

                                    <span class="badge {{ $badgeColor }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($classe->niveau->nom) }}
                                    </span>

                                </td>
                                <td>
                                    👥 <strong>{{ $classe->inscriptions_count }}</strong>
                                </td>
                                <td>{{ $classe->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('elevesshow', $classe->id) }}"
                                           class="btn btn-outline-primary btn-sm" title="Voir les élèves">
                                            <i class="fas fa-eye"></i>
                                        </a>
{{--                                        <a href="{{ route('elevesshow', ['id' => $classe->id, 'modal' => 'eleve']) }}"--}}
{{--                                           class="btn btn-outline-success btn-sm" title="Ajouter un élève">--}}
{{--                                            <i class="fas fa-user-plus"></i>--}}
{{--                                        </a>--}}
                                        <form action="{{ route('classes.destroy', $classe->id) }}" method="POST" onsubmit="return confirm('Supprimer cette classe ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nom de la classe</th>
                            <th>Niveau</th>
                            <th>Effectif</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Styles modernes -->
    <style>
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #36b9cc);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            padding: 5px 10px !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 12px;
            border: 1px solid #ced4da;
            padding: 6px 10px;
        }
        .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: rgba(0,0,0,.03);
        }
    </style>

    @push('scripts')
        <script>
            $('#classesTable').DataTable({
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 50,
                order: [[0, "desc"]], // tri sur la première colonne (id ou matricule)
                deferRender: true,
                language: {
                    url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}"
                }
            });

        </script>
    @endpush
@endsection
