@extends('layouts.app')

@section('title', 'Bulletins – ' . $classe->nom)

@section('content')
    <div class="container-fluid py-5 px-4">

        {{-- 🔹 En-tête --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fas fa-clipboard-list text-primary me-2"></i> Bulletins de la classe {{ $classe->nom }}
                </h2>
                <p class="text-muted mb-0">Générez ou consultez les bulletins selon les élèves ou les découpages.</p>
            </div>
            <a href="{{ route('evaluations.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4 py-2">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <div class="row g-4">

            {{-- 👥 Tous les bulletins d’un découpage --}}
            <div class="col-12 col-lg-4">
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
            <div class="col-12 col-lg-4">
                <div class="card card-hover border-0 rounded-4 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="fas fa-user-graduate me-2"></i> Bulletin d’un élève
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formOne" class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted">Élève</label>
                                <select id="eleve1" class="form-select rounded-3 shadow-sm">
                                    <option value="">Sélectionner un élève</option>
                                    @foreach($classe->inscriptions as $inscription)
                                        <option value="{{ $inscription->id }}">
                                            {{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted">Découpage</label>
                                <select id="decoupage1" class="form-select rounded-3 shadow-sm">
                                    <option value="">Sélectionner un découpage</option>
                                    @foreach($decoupages as $decoupage)
                                        <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="button" id="btnOne" class="btn btn-primary btn-lg rounded-3" disabled>
                                    <i class="fas fa-eye me-1"></i> Voir le bulletin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 📚 Tous les bulletins d’un élève --}}
            {{-- 📚 Liste de proclamation par découpage --}}
            <div class="col-12 col-lg-4">
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
    </style>
@endpush

@push('scripts')
    <script>
        // Activer le bouton 1
        $('#eleve1, #decoupage1').on('change', function() {
            $('#btnOne').prop('disabled', !($('#eleve1').val() && $('#decoupage1').val()));
        });

        // Redirection 1
        $('#btnOne').on('click', function() {
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
            const classeId = "{{ $classe->id }}";
            const decoupageId = $('#decoupage3').val();
            window.location.href = "{{ url('bulletins') }}/" + classeId + "/decoupage/" + decoupageId;
        });


        const decoupageProclamation = document.getElementById('decoupageProclamation');
        const btnProclamation = document.getElementById('btnProclamation');

        decoupageProclamation.addEventListener('change', function() {
            btnProclamation.disabled = !this.value;
        });

        btnProclamation.addEventListener('click', function() {
            const decoupageId = decoupageProclamation.value;
            const classeId = "{{ $classe->id }}";
            if(decoupageId) {
                window.location.href = "{{ url('bulletins/proclamation') }}/" + classeId + "/" + decoupageId;
            }
        });
    </script>
@endpush
