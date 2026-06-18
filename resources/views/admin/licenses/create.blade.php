@extends('layouts.app')

@section('content')
    <div class="container  py-4">
        <h3>Créer une nouvelle licence</h3>
        <form action="{{ route('admin.licenses.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nom de l'école</label>
                <input type="text" name="school_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Type de licence</label>
                <select name="type" id="type" class="form-control">
                    <option value="test">Test (3 mois)</option>
                    <option value="custom">Durée personnalisée</option>
                    <option value="lifetime">Lifetime</option>
                </select>
            </div>

            <div class="mb-3" id="years_div" style="display:none;">
                <label>Durée (en années)</label>
                <input type="number" name="duration_years" class="form-control" min="1">
            </div>

            <button type="submit" class="btn btn-success">Créer la licence</button>
        </form>
    </div>

    <script>
        document.getElementById('type').addEventListener('change', function(){
            if(this.value === 'custom'){
                document.getElementById('years_div').style.display = 'block';
            }else{
                document.getElementById('years_div').style.display = 'none';
            }
        });
    </script>
@endsection
