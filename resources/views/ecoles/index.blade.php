@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2>Liste des écoles</h2>
        <a href="{{ route('ecoles.create') }}" class="btn btn-primary mb-3">➕ Nouvelle école</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Logo</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Directeur</th>
                <th>Année scolaire</th>
                <th>Site web</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($ecoles as $ecole)
                <tr>
                    <td>
                        @if($ecole->logo)
                            <img src="{{ asset('storage/'.$ecole->logo) }}" width="50">
                        @endif
                    </td>
                    <td>{{ $ecole->nom }}</td>
                    <td>{{ $ecole->adresse }}</td>
                    <td>{{ $ecole->telephone }}</td>
                    <td>{{ $ecole->email }}</td>
                    <td>{{ $ecole->directeur }}</td>
                    <td>{{ $ecole->annee_scolaire }}</td>
                    <td>{{ $ecole->site_web }}</td>
                    <td>
                        <a href="{{ route('ecoles.edit',$ecole) }}" class="btn btn-warning btn-sm">✏️</a>
                        <form action="{{ route('ecoles.destroy',$ecole) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
