@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Modifier la licence : {{ $license->school_name }}</h3>

        <form action="{{ route('admin.licenses.update', $license->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>École</label>
                <input type="text" class="form-control" value="{{ $license->school_name }}" disabled>
            </div>

            <div class="mb-3">
                <label>Clé licence</label>
                <input type="text" class="form-control" value="{{ $license->license_key }}" disabled>
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" {{ $license->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ $license->status === 'expired' ? 'selected' : '' }}>Expirée</option>
                    <option value="revoked" {{ $license->status === 'revoked' ? 'selected' : '' }}>Révoquée</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Mettre à jour</button>
            <a href="{{ route('admin.licenses.index') }}" class="btn btn-secondary">Retour</a>
        </form>
    </div>
@endsection
