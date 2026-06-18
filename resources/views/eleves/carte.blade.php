@extends('layouts.app')

@section('title', 'Carte scolaire')
@section('page-title', 'Carte scolaire de ' . $eleve->nom)

@section('content')
    @php
        use SimpleSoftwareIO\QrCode\Facades\QrCode;

        $ecole = \App\Models\Ecole::first();
        $imagePath = $eleve->sexe == 'M' ? asset('dist/img/man.png') : asset('dist/img/woman.png');
        $qrData = route('eleves.profil', $eleve->id);
    @endphp

    <div class="d-flex justify-content-center py-5 flex-column align-items-center">
        <div class="carte-pvc p-0 shadow-sm" id="cartePrintable">
            <!-- Entête -->
            <div class="d-flex align-items-center justify-content-between header-carte">
                <div class="logo-ecole">
                    @if($ecole && $ecole->logo)
                        <img src="{{ asset('storage/' . $ecole->logo) }}" alt="Logo école">
                    @endif
                </div>
                <div class="texte-header text-center">
                    <p class="fw-bold">REPUBLIQUE TOGOLAISE</p>
                    <p>Travail - Liberté - Patrie</p>
                    <p class="fw-bold">{{ $ecole->nom ?? 'Nom de l’École' }}</p>
                    <p>Année : {{ $ecole->annee->nom  }}</p>
                </div>
                <div class="armoiries">
                    <img src="{{ asset('dist/img/Armoiries_du_Togo.png') }}" alt="Armoiries du Togo">
                </div>
            </div>

            <!-- Séparation -->
            <div class="separation"></div>

            <!-- Corps -->
            <div class="d-flex corps-carte">
                <div class="photo-eleve">
                    <img src="{{ $eleve->imglink ? asset($eleve->imglink) : $imagePath }}" alt="Photo de {{ $eleve->nom }}">
                    <p class="matricule"><strong>Matricule :{{ $eleve->id }}</strong> </p>
                </div>

                <div class="infos-eleve">
                    <p><strong>Nom :</strong> {{ strtoupper($eleve->nom) }}</p>
                    <p><strong>Prénom :</strong> {{ ucfirst($eleve->prenom) }}</p>
                    <p><strong>Sexe :</strong> {{ $eleve->sexe == 'M' ? 'M' : 'F' }} | <strong>Classe :&nbsp</strong> {{ ($eleve->classeActuelle?->classe?->nom ?? '—') }}</p>
                    <p><strong>Naissance :</strong> {{ $eleve->date_naissance ? $eleve->date_naissance->format('d/m/Y') : '—' }}</p>
                    <p><strong>Adresse :</strong> {{ $eleve->adresse ?? '—' }}</p>
                    <p><strong>Tuteur :</strong> {{ $eleve->tuteur_nom ?? '—' }}</p>
                    <p><strong>Tél. tuteur :</strong> {{ $eleve->tuteur_tel ?? '—' }}</p>
                </div>

                <div class="qr-code">
                    {!! QrCode::size(50)->generate($qrData) !!}
                </div>
            </div>
            <div style="font-size: smaller">
                <strong>Directeur :</strong>
                {{
                    ($eleve->classeActuelle?->classe?->niveau->nom === 'Primaire')
                    ? $ecole->directeur_primaire
                    : $ecole->directeur
                }}
            </div>
            <!-- Footer -->
            <div class="footer-carte text-center">
                <p>Cette carte est délivrée dans le cadre scolaire et doit être retournée à l’école en cas de perte.</p>
                <p><strong>Tél :</strong> {{ $ecole->telephone ?? '—' }} | <strong>Email :</strong> {{ $ecole->email ?? '—' }}</p>
            </div>
        </div>

        <!-- Bouton Impression en dehors de la carte -->
        <div class="text-center mt-2">
            <button class="btn btn-primary btn-sm" onclick="imprimerCarte()">Imprimer la carte</button>
        </div>
    </div>

    <style>
        .carte-pvc {
            width: 440px;
            min-height: 310px;
            background: #fff;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            overflow: hidden;
            border: 1px solid #000;
            display: flex;
            flex-direction: column;
        }
        .header-carte {
            padding: 2px 5px;
            background: #0062ff;
            color: #fff;
            align-items: center;
        }
        .header-carte p { margin: 0; line-height: 1.1; } /* encore plus serré */
        .logo-ecole img { width: 45px; height: 45px; border-radius: 3px; border: 1px solid #fff; object-fit: contain; }
        .armoiries img { width: 40px; height: 45px; }
        .texte-header { text-align: center; font-size: 0.70rem; }
        .texte-header p.fw-bold { font-size: 0.75rem; }
        .separation { height: 1.5px; background: #003399; }
        .corps-carte { display: flex; padding: 3px; gap: 5px; font-size: 0.70rem; position: relative; flex-shrink: 0; }
        .photo-eleve { text-align: center; flex-shrink: 0; }
        .photo-eleve img { width: 80px; height: 100px; object-fit: cover; border: 1px solid #000; border-radius: 3px; }
        .photo-eleve .matricule { font-size: 0.60rem; margin-top: 2px; }
        .infos-eleve { flex-grow: 1; }
        .infos-eleve p { margin: 1px; }
        .qr-code { position: absolute; bottom: 5px; right: 5px; }
        .footer-carte { font-size: 0.6rem; border-top: 2px dashed #000; background: #f8f9fa; padding: 1px; text-align: center; flex-shrink:0; }

        @media print {
            body, html { margin: 0; padding: 0; }
            .btn { display: none; }
            .carte-pvc { box-shadow: none !important; border: 1px solid #000 !important; width: 440px; min-height: 310px; display:flex; flex-direction:column; page-break-inside: avoid; }
            .footer-carte { display:block; }
        }
    </style>

    <script>
        function imprimerCarte() {
            var carte = document.getElementById('cartePrintable').outerHTML;
            var myWindow = window.open('', '', 'width=460,height=340');
            myWindow.document.write('<html><head><title>Imprimer Carte</title>');
            myWindow.document.write('<style>');
            myWindow.document.write(`
        body, html { margin:0; padding:0; }
        .carte-pvc { align:center;width:450; min-height:320; border-radius:6px; font-family:Arial,sans-serif; overflow:hidden; border:2px solid #000; display:flex; flex-direction:column; }
        .header-carte { padding:3px 6px; background:#0062ff; color:#fff; display:flex; align-items:center; justify-content:space-between; }
        .header-carte p { margin:0; line-height:1.05; }
        .logo-ecole img { width:45; height:45; border-radius:3px; border:1px solid #fff; object-fit:contain; }
        .armoiries img { width:40; height:40; }
        .texte-header { text-align:center; font-size:0.65rem; }
        .texte-header p.fw-bold { font-size:0.75rem; margin:0; }
        .separation { height:1.5px; background:#003399; }
        .corps-carte { display:flex; padding:3px; gap:5px; font-size:0.65rem; position:relative; flex-shrink:0; }
        .photo-eleve { text-align:center; flex-shrink:0; }
        .photo-eleve img { width:80; height:100; object-fit:cover; border:1px solid #000; border-radius:3px; }
        .photo-eleve .matricule { font-size:0.60rem; margin-top:3px; }
        .infos-eleve { flex-grow:2; }
        .infos-eleve p {
    margin: 3px 0;       /* espace vertical entre les lignes */
    line-height: 1.25;   /* hauteur de ligne plus confortable */
}
        .qr-code { position:absolute; bottom:5px; right:5px; }
        .footer-carte { font-size:0.7rem; border-top:1px dashed #000; background:#f8f9fa; padding:1px; text-align:center; flex-shrink:0; display:block; }
        @page { size:105mm 75mm; margin:0; }
    `);
            myWindow.document.write('</style>');
            myWindow.document.write('</head><body>');
            myWindow.document.write(carte);
            myWindow.document.write('</body></html>');
            myWindow.document.close();
            myWindow.focus();
            myWindow.print();
        }
    </script>
@endsection
