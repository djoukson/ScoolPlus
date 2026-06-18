{{-- resources/views/salaires/pdf_personnel.blade.php --}}
    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Personnel</title>
</head>
<body>
<h1>Salaires Personnel - {{ $mois }}</h1>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Poste</th>
        <th>Salaire</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $p)
        <tr>
            <td>{{ $p->nom }}</td>
            <td>{{ $p->prenom }}</td>
            <td>{{ $p->poste }}</td>
            <td>{{ number_format($p->salaire, 2, ',', ' ') }} FCFA</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
