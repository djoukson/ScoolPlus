@extends('layouts.app')

@section('content')
    <div class="container  py-4">
        <h3>Gestion des Licences</h3>
        <a href="{{ route('admin.licenses.create') }}" class="btn btn-primary mb-3">Créer une licence</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>École</th>
                <th>Clé licence</th>
                <th>Durée (ans)</th>
                <th>Expiration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($licenses as $license)
                <tr>
                    <td>{{ $license->school_name }}</td>
                    <td>{{ $license->license_key }}</td>
                    <td>{{ $license->duration_years ?? 'Lifetime/Test' }}</td>
                    <td>{{ $license->expires_at ?? '-' }}</td>
                    <td>
                        @if($license->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($license->status === 'expired')
                            <span class="badge bg-danger">Expirée</span>
                        @elseif($license->status === 'revoked')
                            <span class="badge bg-warning text-dark">Révoquée</span>
                        @else
                            <span class="badge bg-secondary">{{ $license->status }}</span>
                        @endif
                    </td>

                    <td><a href="{{ route('license.download', $license->id) }}"
                           class="btn btn-primary">
                            📥 Télécharger Licence
                        </a>

                        <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('admin.licenses.destroy', $license) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette licence ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
