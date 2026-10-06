@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Notes – {{ $classe->nom }}
            </div>
            <button type="button" class="btn btn-outline-info shadow-sm" data-toggle="modal" data-target="#blocModal" style="margin-bottom: 10px">
                📋 Saisie en bloc
            </button>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('evaluations.index') }}">Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les Notes
                    </li>
                </ol>
            </div>
        </div>


        {{-- ================= Formulaire individuel ================= --}}
        <form action="{{ route('evaluations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="classe_id" value="{{ $classe->id }}">

            {{-- Card : Paramètres évaluation --}}
            <div class="card shadow-lg rounded-4 mb-4">
                <div class="card-header fw-bold text-primary">📌 Paramètres de l’évaluation</div>
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📘 Matière</label>
                        <select name="matiere_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($matieres as $matiere)
                                <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📑 Type</label>
                        <select name="type_evaluation_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($typesEvaluations as $type)
                                <option value="{{ $type->id }}">{{ $type->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📅 Découpage</label>
                        <select name="decoupage_id" class="form-select" required>
                            <option value="" disabled selected>-- Choisir --</option>
                            @foreach($decoupages as $decoupage)
                                <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">📆 Date</label>
                        <input type="date" name="date_eval" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            {{-- Card : Notes des élèves --}}
            <div class="card shadow-lg rounded-4 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">👩‍🎓 Notes des élèves</span>
                    <button type="button" class="btn btn-sm btn-success rounded-pill" onclick="addRow()">➕ Ajouter une ligne</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="notesTable">
                            <thead class="table-light">
                            <tr>
                                <th>Élève</th>
                                <th>Note (/20)</th>
                                <th>⚙️</th>
                            </tr>
                            </thead>
                            <tbody id="notesBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-outline-success rounded-pill px-3">💾 Enregistrer toutes les notes</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ================= Liste des notes ================= --}}
        <div class="card shadow-lg rounded-4">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">📊 Notes enregistrées</h5>
                <div class="table-responsive">
                    <table id="notesListeTable" class="table table-hover align-middle">
                        <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Élève</th>
                            <th>Matière</th>
                            <th>Type</th>
                            <th>Découpage</th>
                            <th>Note</th>
                            <th>Date</th>
                            <th>⚙️ Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- Pas de @empty / colspan : DataTables affiche lui-même le message "tableau vide" --}}
                        @foreach($evaluations as $eval)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</td>
                                <td>{{ $eval->matiere->nom }}</td>
                                <td>{{ $eval->typeEvaluation->nom }}</td>
                                <td>{{ $eval->decoupage->nom }}</td>
                                <td><span class="badge bg-info">{{ $eval->note }}/20</span></td>
                                <td>{{ $eval->date_eval }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $eval->id }}">✏️</button>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $eval->id }}">🗑️</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= Modals Edit / Delete (hors du tableau) ================= --}}
        @foreach($evaluations as $eval)
            {{-- Modal Edit --}}
            <div class="modal fade" id="editModal{{ $eval->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content rounded-4 shadow-lg">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">✏️ Modifier la note</h5>
                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('evaluations.update', $eval->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <p><strong>Élève :</strong> {{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</p>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Note (/20)</label>
                                    <input type="number" name="note" class="form-control" min="0" max="20" step="0.25" value="{{ $eval->note }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Date</label>
                                    <input type="date" name="date_eval" class="form-control" value="{{ $eval->date_eval }}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">❌ Annuler</button>
                                <button type="submit" class="btn btn-warning rounded-pill">💾 Sauvegarder</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Delete --}}
            <div class="modal fade" id="deleteModal{{ $eval->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 shadow-lg">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">⚠️ Confirmation suppression</h5>
                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Voulez-vous vraiment supprimer la note de <strong>{{ $eval->inscription->eleve->nom }} {{ $eval->inscription->eleve->prenom }}</strong> ?</p>
                            <p class="text-danger fw-bold">Cette action est irréversible ❌</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Annuler</button>
                            <form action="{{ route('evaluations.destroy', $eval->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger rounded-pill">🗑️ Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- ================= Modal Saisie en bloc ================= --}}
       {{-- ================= Modal Saisie en bloc (refonte) ================= --}}
<div class="modal fade" id="blocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bm-content">

            <form action="{{ route('evaluations.store') }}" method="POST" id="blocForm" class="bm-form" autocomplete="off">
                @csrf
                <input type="hidden" name="classe_id" value="{{ $classe->id }}">

                {{-- Header --}}
                <div class="bm-header">
                    <div>
                        <div class="bm-title">Saisie en bloc des notes</div>
                        <div class="bm-subtitle">{{ $classe->nom }} · {{ count($inscriptions) }} élèves</div>
                    </div>
                    <button type="button" class="bm-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Fermer">&times;</button>
                </div>

                {{-- Paramètres --}}
                <div class="bm-params">
                    <div class="bm-field">
                        <label><i class="fas fa-book"></i> Matière</label>
                        <select name="matiere_id" required>
                            <option value="" disabled selected>Choisir…</option>
                            @foreach($matieres as $matiere)
                                <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bm-field">
                        <label><i class="fas fa-file-alt"></i> Type</label>
                        <select name="type_evaluation_id" required>
                            <option value="" disabled selected>Choisir…</option>
                            @foreach($typesEvaluations as $type)
                                <option value="{{ $type->id }}">{{ $type->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bm-field">
                        <label><i class="fas fa-calendar-alt"></i> Découpage</label>
                        <select name="decoupage_id" required>
                            <option value="" disabled selected>Choisir…</option>
                            @foreach($decoupages as $decoupage)
                                <option value="{{ $decoupage->id }}">{{ $decoupage->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bm-field">
                        <label><i class="fas fa-calendar-day"></i> Date</label>
                        <input type="date" name="date_eval" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Barre d'outils --}}
                <div class="bm-toolbar">
                    <div class="bm-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="bmSearch" placeholder="Rechercher un élève…">
                    </div>
                    <div class="bm-hint d-none d-md-block">
                        <kbd>Entrée</kbd> ou <kbd>↓</kbd> élève suivant · <kbd>↑</kbd> précédent
                    </div>
                    <button type="button" class="bm-link" id="bmReset"><i class="fas fa-eraser"></i> Tout vider</button>
                </div>

                {{-- Liste élèves --}}
                <div class="bm-list" id="bmList">
                    @foreach($inscriptions as $index => $ins)
                        @php
                            $nomComplet = $ins->eleve->nom . ' ' . $ins->eleve->prenom;
                            $initiales = mb_strtoupper(mb_substr($ins->eleve->nom, 0, 1) . mb_substr($ins->eleve->prenom, 0, 1));
                        @endphp
                        <div class="bm-row" data-name="{{ mb_strtolower($nomComplet) }}">
                            <span class="bm-num">{{ $loop->iteration }}</span>
                            <span class="bm-avatar">{{ $initiales }}</span>
                            <span class="bm-name">{{ $nomComplet }}</span>
                            <input type="hidden" name="notes[{{ $index }}][inscription_id]" value="{{ $ins->id }}">
                            <div class="bm-input-wrap">
                                <input type="number" inputmode="decimal" name="notes[{{ $index }}][note]"
                                       class="bm-note" min="0" max="20" step="0.25" placeholder="–">
                                <span>/20</span>
                            </div>
                        </div>
                    @endforeach
                    <div class="bm-none" id="bmNone">Aucun élève trouvé</div>
                </div>

                {{-- Footer --}}
                <div class="bm-footer">
                    <div class="bm-stats">
                        <div class="bm-progress"><div id="bmBar"></div></div>
                        <span><strong id="bmCount">0</strong>/{{ count($inscriptions) }} saisies</span>
                        <span class="bm-sep"></span>
                        <span>Moyenne : <strong id="bmAvg">–</strong></span>
                        <span class="bm-error" id="bmErr" style="display:none"></span>
                    </div>
                    <div class="bm-actions">
                        <button type="button" class="bm-btn bm-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="bm-btn bm-btn-primary" id="bmSubmit" disabled>
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #blocModal .bm-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,31,51,.35); }
    #blocModal .bm-form { display: flex; flex-direction: column; max-height: 90vh; }

    /* Header */
    #blocModal .bm-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px;
        background: linear-gradient(135deg, #0b1f33, #102c44); color: #fff; }
    #blocModal .bm-title { font-size: 1.15rem; font-weight: 700; letter-spacing: .3px; }
    #blocModal .bm-subtitle { font-size: .8rem; color: rgba(255,255,255,.65); margin-top: 2px; }
    #blocModal .bm-close { background: rgba(255,255,255,.12); border: 0; color: #fff; width: 34px; height: 34px;
        border-radius: 50%; font-size: 1.3rem; line-height: 1; cursor: pointer; transition: background .2s; }
    #blocModal .bm-close:hover { background: rgba(255,255,255,.25); }

    /* Paramètres */
    #blocModal .bm-params { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; padding: 16px 24px; background: #f5f8fb; border-bottom: 1px solid #e6edf3; }
    #blocModal .bm-field label { display: block; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #5b7184; margin-bottom: 4px; }
    #blocModal .bm-field label i { margin-right: 4px; color: #2b7bb9; }
    #blocModal .bm-field select, #blocModal .bm-field input { width: 100%; height: 38px; padding: 0 10px; border: 1px solid #d5dfe8;
        border-radius: 10px; background: #fff; font-size: .9rem; transition: border-color .2s, box-shadow .2s; }
    #blocModal .bm-field select:focus, #blocModal .bm-field input:focus { outline: 0; border-color: #2b7bb9; box-shadow: 0 0 0 3px rgba(43,123,185,.15); }

    /* Toolbar */
    #blocModal .bm-toolbar { display: flex; align-items: center; gap: 16px; padding: 12px 24px; border-bottom: 1px solid #eef2f6; }
    #blocModal .bm-search { position: relative; flex: 0 0 260px; }
    #blocModal .bm-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #93a4b3; font-size: .8rem; }
    #blocModal .bm-search input { width: 100%; height: 36px; padding: 0 12px 0 32px; border: 1px solid #d5dfe8; border-radius: 20px; font-size: .88rem; }
    #blocModal .bm-search input:focus { outline: 0; border-color: #2b7bb9; box-shadow: 0 0 0 3px rgba(43,123,185,.15); }
    #blocModal .bm-hint { flex: 1; text-align: center; font-size: .75rem; color: #8798a8; }
    #blocModal kbd { background: #eef2f6; color: #44586a; border-radius: 5px; padding: 1px 6px; font-size: .72rem; box-shadow: none; }
    #blocModal .bm-link { background: none; border: 0; color: #c0392b; font-size: .82rem; font-weight: 600; cursor: pointer; margin-left: auto; }
    #blocModal .bm-link:hover { text-decoration: underline; }

    /* Liste */
    #blocModal .bm-list { flex: 1; overflow-y: auto; min-height: 200px; padding: 6px 24px; }
    #blocModal .bm-row { display: flex; align-items: center; gap: 12px; padding: 7px 10px; border-radius: 10px; transition: background .15s; }
    #blocModal .bm-row:hover { background: #f5f8fb; }
    #blocModal .bm-row:focus-within { background: #eaf4fc; }
    #blocModal .bm-row.is-hidden { display: none; }
    #blocModal .bm-num { width: 24px; text-align: right; font-size: .75rem; color: #a3b1be; }
    #blocModal .bm-avatar { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #1c3f5e, #295f7d); color: #fff; font-size: .72rem; font-weight: 700; flex-shrink: 0; }
    #blocModal .bm-name { flex: 1; font-weight: 600; font-size: .92rem; color: #1d2f3f; }
    #blocModal .bm-input-wrap { display: flex; align-items: center; gap: 6px; font-size: .78rem; color: #8798a8; }
    #blocModal .bm-note { width: 84px; height: 38px; text-align: center; font-weight: 700; font-size: 1rem; border: 1.5px solid #d5dfe8; border-radius: 10px; transition: all .15s; }
    #blocModal .bm-note:focus { outline: 0; border-color: #2b7bb9; box-shadow: 0 0 0 3px rgba(43,123,185,.18); }
    #blocModal .bm-note.is-filled { border-color: #27ae60; background: #f1fbf5; color: #1e8449; }
    #blocModal .bm-note.is-invalid { border-color: #e74c3c; background: #fdf0ee; color: #c0392b; }
    #blocModal .bm-note::-webkit-outer-spin-button, #blocModal .bm-note::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #blocModal .bm-note { -moz-appearance: textfield; }
    #blocModal .bm-none { display: none; text-align: center; padding: 30px 0; color: #93a4b3; }

    /* Footer */
    #blocModal .bm-footer { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 14px 24px; border-top: 1px solid #e6edf3; background: #fff; }
    #blocModal .bm-stats { display: flex; align-items: center; gap: 12px; font-size: .85rem; color: #44586a; flex-wrap: wrap; }
    #blocModal .bm-progress { width: 120px; height: 8px; background: #e6edf3; border-radius: 8px; overflow: hidden; }
    #blocModal .bm-progress div { height: 100%; width: 0; background: linear-gradient(90deg, #2b7bb9, #27ae60); transition: width .25s; }
    #blocModal .bm-sep { width: 1px; height: 16px; background: #d5dfe8; }
    #blocModal .bm-error { color: #c0392b; font-weight: 600; }
    #blocModal .bm-actions { display: flex; gap: 10px; }
    #blocModal .bm-btn { border: 0; border-radius: 22px; padding: 9px 22px; font-weight: 600; font-size: .9rem; cursor: pointer; transition: all .2s; }
    #blocModal .bm-btn-light { background: #eef2f6; color: #44586a; }
    #blocModal .bm-btn-light:hover { background: #e0e7ee; }
    #blocModal .bm-btn-primary { background: linear-gradient(135deg, #1c8c4e, #27ae60); color: #fff; box-shadow: 0 6px 16px rgba(39,174,96,.3); }
    #blocModal .bm-btn-primary:hover:not(:disabled) { transform: translateY(-1px); }
    #blocModal .bm-btn-primary:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

    @media (max-width: 767px) {
        #blocModal .bm-params { grid-template-columns: 1fr 1fr; padding: 12px 16px; }
        #blocModal .bm-toolbar, #blocModal .bm-list, #blocModal .bm-footer, #blocModal .bm-header { padding-left: 16px; padding-right: 16px; }
        #blocModal .bm-search { flex: 1; }
        #blocModal .bm-footer { flex-direction: column; align-items: stretch; }
        #blocModal .bm-actions { justify-content: flex-end; }
        #blocModal .bm-num { display: none; }
    }
</style>


    </div>
@endsection

@push('scripts')
 <script>
(function () {
    const modal   = document.getElementById('blocModal');
    const form    = document.getElementById('blocForm');
    const rows    = Array.from(modal.querySelectorAll('.bm-row'));
    const inputs  = rows.map(r => r.querySelector('.bm-note'));
    const total   = rows.length;
    const els = {
        count: document.getElementById('bmCount'),
        avg: document.getElementById('bmAvg'),
        bar: document.getElementById('bmBar'),
        err: document.getElementById('bmErr'),
        submit: document.getElementById('bmSubmit'),
        search: document.getElementById('bmSearch'),
        none: document.getElementById('bmNone')
    };

    function refresh() {
        let filled = 0, sum = 0, invalid = 0;
        inputs.forEach(inp => {
            const raw = inp.value.trim();
            inp.classList.remove('is-filled', 'is-invalid');
            if (raw === '') return;
            const v = parseFloat(raw);
            if (isNaN(v) || v < 0 || v > 20) { inp.classList.add('is-invalid'); invalid++; return; }
            inp.classList.add('is-filled');
            filled++; sum += v;
        });
        els.count.textContent = filled;
        els.avg.textContent = filled ? (sum / filled).toFixed(2) + '/20' : '–';
        els.bar.style.width = (total ? (filled / total) * 100 : 0) + '%';
        els.err.style.display = invalid ? '' : 'none';
        els.err.textContent = invalid ? invalid + ' note(s) invalide(s)' : '';
        els.submit.disabled = filled === 0 || invalid > 0;
    }

    // Navigation clavier : Entrée / ↓ = suivant, ↑ = précédent
    function visibleInputs() { return inputs.filter(i => !i.closest('.bm-row').classList.contains('is-hidden')); }
    function move(current, dir) {
        const list = visibleInputs();
        const next = list[list.indexOf(current) + dir];
        if (next) { next.focus(); next.select(); }
        else if (dir > 0) els.submit.focus();
    }

    inputs.forEach(inp => {
        inp.addEventListener('input', refresh);
        inp.addEventListener('focus', () => inp.select());
        inp.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === 'ArrowDown') { e.preventDefault(); move(inp, 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); move(inp, -1); }
        });
    });

    // Recherche instantanée
    els.search.addEventListener('input', () => {
        const q = els.search.value.trim().toLowerCase();
        let shown = 0;
        rows.forEach(r => {
            const match = r.dataset.name.includes(q);
            r.classList.toggle('is-hidden', !match);
            if (match) shown++;
        });
        els.none.style.display = shown ? 'none' : 'block';
    });

    // Tout vider
    document.getElementById('bmReset').addEventListener('click', () => {
        inputs.forEach(i => i.value = '');
        refresh();
        if (inputs[0]) inputs[0].focus();
    });

    // À l'envoi : seules les lignes renseignées sont transmises
    form.addEventListener('submit', () => {
        rows.forEach((row, i) => {
            if (inputs[i].value.trim() === '') {
                row.querySelectorAll('input').forEach(el => el.disabled = true);
            }
        });
    });

    // Focus automatique sur la 1re note à l'ouverture (Bootstrap 4 ou 5)
    function focusFirst() { setTimeout(() => inputs[0] && inputs[0].focus(), 150); }
    if (window.jQuery) { window.jQuery(modal).on('shown.bs.modal', focusFirst); }
    modal.addEventListener('shown.bs.modal', focusFirst);

    refresh();
})();
    {{-- ================= JS saisie individuelle ================= --}}
   
        let rowIndex = 0;

        function addRow() {
            let tableBody = document.getElementById('notesBody');

            let newRow = document.createElement('tr');
            newRow.innerHTML = `
        <td>
            <select name="notes[${rowIndex}][inscription_id]" class="form-select" required>
                <option value="" disabled selected>-- Choisir --</option>
                @foreach($inscriptions as $inscription)
                    <option value="{{ $inscription->id }}">{{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="notes[${rowIndex}][note]" class="form-control" min="0" max="20" step="0.25" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑️</button>
        </td>
        `;

            tableBody.appendChild(newRow);
            rowIndex++;
        }

        function removeRow(button) {
            button.closest('tr').remove();
        }

        // Ajouter une première ligne automatiquement au chargement
        document.addEventListener("DOMContentLoaded", () => {
            addRow();
        });
    </script>

    {{-- ================= DataTables ================= --}}
    <script>
        $(document).ready(function () {
            $('#notesListeTable').DataTable({
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 50,
                deferRender: true,
                language: {
                    url: "{{ asset('assets/datatables/i18n/fr-FR.json') }}",
                    emptyTable: "Aucune note enregistrée"
                }
            });
        });
    </script>
@endpush