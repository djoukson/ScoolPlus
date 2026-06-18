@extends('layouts.app')

@section('title', 'Bulletins – ' . $classe->nom)

@section('content')
    <div class="container-fluid py-5 px-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Bulletins de la classe {{ $classe->nom }}
            </div>
            {{-- 🔄 Sélecteur de thème --}}
            <form action="{{ route('bulletins.theme.activer') }}" method="POST" class="d-flex align-items-center mb-2">
                @csrf
                <label for="themeSelect" class="me-2 fw-semibold" style="font-size:14px;">🎨 Thème :</label>

                <div style="position: relative; display: inline-block;">
                    <select disabled name="theme_id" id="themeSelect" class="form-select form-select-sm"
                            onchange="this.form.submit()"
                            style="appearance: none; padding-right:30px; min-width:160px; font-weight:500; border-radius:8px; border:1px solid #ccc; background:#fff; transition:0.3s;">
                        @foreach($themes as $theme)
                            <option value="{{ $theme->id }}" {{ $theme->active ? 'selected' : '' }}>
                                {{ $theme->nom }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Icône dropdown -->
                    <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none;">
            <i class="fas fa-chevron-down" style="color:#666;"></i>
        </span>
                </div>
            </form>


            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('evaluations.index') }}">Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les Bulletins et liste de proclamation
                    </li>
                </ol>
            </div>
        </div>

        <div class="row g-4">

            {{-- 👥 Tous les bulletins d’un découpage --}}
{{--            <div class="col-12 col-lg-3">--}}
{{--                <div class="card card-hover border-0 rounded-4 shadow-sm h-100">--}}
{{--                    <div class="card-header bg-white border-0 py-3">--}}
{{--                        <h6 class="fw-bold mb-0 text-info">--}}
{{--                            <i class="fas fa-calculator me-2"></i> Recalculer les moeyennes--}}
{{--                        </h6>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        <form id="formCalc" class="row g-3">--}}
{{--                            <div class="col-12">--}}
{{--                                <label class="form-label small fw-semibold text-muted">Découpage</label>--}}
{{--                                <select id="selectcalc" class="form-select rounded-3 shadow-sm">--}}
{{--                                    <option value="">Sélectionner un découpage</option>--}}
{{--                                    @foreach($decoupages as $decoupage)--}}
{{--                                        <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                            <div class="col-12 d-grid">--}}
{{--                                <button type="button" id="btncalc" class="btn btn-warning btn-lg rounded-3" disabled>--}}
{{--                                    <i class="fas fa-users-viewfinder me-1"></i> Calculer--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}


            <div class="col-12 col-lg-6">
                <div class="card card-hover border-0 rounded-4 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-info">
                            <i class="fas fa-users me-2"></i> Bulletins par découpage
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formThree" class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted">Découpage</label>
                                <select id="decoupage3" class="form-select rounded-3 shadow-sm">
                                    <option value="">Sélectionner un découpage</option>
                                    @foreach($decoupages as $decoupage)
                                        <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="button" id="btnThree" class="btn btn-info btn-lg rounded-3" disabled>
                                    <i class="fas fa-users-viewfinder me-1"></i> Voir tous les bulletins
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- 🧑‍🎓 Bulletin d’un élève pour un découpage --}}
{{--            <div class="col-12 col-lg-4">--}}
{{--                <div class="card card-hover border-0 rounded-4 shadow-sm h-100">--}}
{{--                    <div class="card-header bg-white border-0 py-3">--}}
{{--                        <h6 class="fw-bold mb-0 text-primary">--}}
{{--                            <i class="fas fa-user-graduate me-2"></i> Bulletin d’un élève--}}
{{--                        </h6>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        <form id="formOne" class="row g-3">--}}
{{--                            <div class="col-12">--}}
{{--                                <label class="form-label small fw-semibold text-muted">Élève</label>--}}
{{--                                <select id="eleve1" class="form-select rounded-3 shadow-sm">--}}
{{--                                    <option value="">Sélectionner un élève</option>--}}
{{--                                    @foreach($classe->inscriptions as $inscription)--}}
{{--                                        <option value="{{ $inscription->id }}">--}}
{{--                                            {{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}--}}
{{--                                        </option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                            <div class="col-12">--}}
{{--                                <label class="form-label small fw-semibold text-muted">Découpage</label>--}}
{{--                                <select id="decoupage1" class="form-select rounded-3 shadow-sm">--}}
{{--                                    <option value="">Sélectionner un découpage</option>--}}
{{--                                    @foreach($decoupages as $decoupage)--}}
{{--                                        <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                            <div class="col-12 d-grid">--}}
{{--                                <button type="button" id="btnOne" class="btn btn-primary btn-lg rounded-3" disabled>--}}
{{--                                    <i class="fas fa-eye me-1"></i> Voir le bulletin--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

            {{-- 📚 Tous les bulletins d’un élève --}}
            {{-- 📚 Liste de proclamation par découpage --}}
            <div class="col-12 col-lg-6">
                <div class="card card-hover border-0 rounded-4 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-success">
                            <i class="fas fa-layer-group me-2"></i> Liste de proclamation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Découpage</label>
                            <select id="decoupageProclamation" class="form-select rounded-3 shadow-sm">
                                <option value="">Sélectionner un découpage</option>
                                @foreach($decoupages as $decoupage)
                                    <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="button" id="btnProclamation" class="btn btn-success btn-lg rounded-3" disabled>
                                <i class="fas fa-eye me-1"></i> Voir la liste des élèves
                            </button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

@endsection

@push('styles')
    <style>
        body {
            background-color: #f8fafc;
            font-family: "Inter", "Segoe UI", sans-serif;
        }
        .card-hover {
            transition: all 0.3s ease-in-out;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .form-select {
            border: 1px solid #e5e7eb;
            transition: 0.2s;
        }
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99,102,241,0.2);
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

             /* Popup globale */
         .swal2-popup.swal2-modal {
             border-radius: 1.25rem !important;
             background: linear-gradient(135deg, #ffffff, #f3f6ff) !important;
             box-shadow: 0 25px 50px rgba(0,0,0,.2) !important;
         }

        /* Quand le loader est actif */
        .swal2-loading .swal2-popup {
            padding-top: 2.5rem !important;
        }

        /* Titre */
        .swal2-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        /* Texte */
        .swal2-html-container,
        .swal2-content {
            font-size: 0.95rem !important;
            color: #64748b !important;
        }

        /* Spinner */
        .swal2-loading .swal2-loader {
            width: 3.2rem !important;
            height: 3.2rem !important;
            border-width: 4px !important;
            border-color: #6366f1 transparent #6366f1 transparent !important;
        }

        /* Backdrop (fond flouté premium) */
        .swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.55) !important;
            backdrop-filter: blur(8px);
        }
    </style>



@endpush

@push('scripts')
    <script>
        function showLoader(title = 'Chargement', text = 'Veuillez patienter...') {
            Swal.fire({
                title: title,
                text: text,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    popup: 'shadow'
                }
            });
        }



        // Activer le bouton 1
        $('#eleve1, #decoupage1').on('change', function() {
            $('#btnOne').prop('disabled', !($('#eleve1').val() && $('#decoupage1').val()));
        });

        // Redirection 1
        $('#btnOne').on('click', function() {
            showLoader('Bulletin en cours', 'Génération du bulletin...');

            window.location.href = "{{ url('bulletins/apercu') }}/" + $('#eleve1').val() + "/" + $('#decoupage1').val();
        });

        // Activer bouton 2
        $('#eleve2').on('change', function() {
            $('#btnTwo').prop('disabled', !$(this).val());
        });

        // Redirection 2
        $('#btnTwo').on('click', function() {
            window.location.href = "{{ url('bulletins/eleve') }}/" + $('#eleve2').val();
        });

        // Activer bouton 3
        $('#decoupage3').on('change', function() {
            $('#btnThree').prop('disabled', !$(this).val());
        });

        // Redirection 3
        $('#btnThree').on('click', function() {
            showLoader('Chargement', 'Préparation des bulletins...');

            const classeId = "{{ $classe->id }}";
            const decoupageId = $('#decoupage3').val();
            window.location.href = "{{ url('bulletins') }}/" + classeId + "/decoupage/" + decoupageId;
        });

        $('#btncalc').on('click', function() {
           // showLoader('Chargement', 'Préparation des bulletins...');

            const classeId = "{{ $classe->id }}";
            const decoupageId = $('#selectcalc').val();

            console.log(decoupageId);
            //exit;
           window.location.href = "{{ url('bulletinscalculs') }}/" + classeId + "/decoupage/" + decoupageId;
        });


        const selectcalc = document.getElementById('selectcalc');
        const btncalc = document.getElementById('btncalc');

        selectcalc.addEventListener('change', function() {
            btncalc.disabled = !this.value;
        });

        const decoupageProclamation = document.getElementById('decoupageProclamation');
        const btnProclamation = document.getElementById('btnProclamation');

        decoupageProclamation.addEventListener('change', function() {
            btnProclamation.disabled = !this.value;
        });

        btnProclamation.addEventListener('click', function () {
            if (decoupageProclamation.value) {
                showLoader('Liste de proclamation', 'Chargement des résultats...');
                const classeId = "{{ $classe->id }}";
                window.location.href = "{{ url('bulletins/proclamation') }}/" + classeId + "/" + decoupageProclamation.value;
            }
        });

    </script>
@endpush
