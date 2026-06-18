@extends('layouts.app')

@section('title', 'Gestion des sauvegardes')

@section('content')
    <div class="container py-4">

        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Les sauvegardes
            </div>
            <form action="{{ route('backup.create') }}" method="POST">
                @csrf
                <button  style="margin-bottom: 10px" type="submit" class="btn btn-info mb-3">
                    <i class="fas fa-database me-2"></i> Créer une nouvelle sauvegarde
                </button>
            </form>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Creation des sauvegardes
                    </li>
                </ol>
            </div>
        </div>

        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>Nom du fichier</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($backups as $backup)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $backup->filename }}</td>
                    <td>{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('backup.download', $backup->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
