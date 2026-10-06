@extends('layouts.app')

@section('content')
@php
    $u = auth()->user();
    $isFem = in_array($u->sexe, ['Féminin', 'Feminin']);
    $avatar = $u->profileimg
        ? asset('/' . $u->profileimg)
        : ($u->sexe === 'Masculin' ? asset('dist/img/man.png') : ($isFem ? asset('dist/img/woman.png') : asset('dist/img/general.png')));

    // Onglet à ouvrir (après une erreur de validation ou un enregistrement)
    $tab = old('_tab', session('tab', 'profil'));
    if (session('force_password_change') || $u->must_change_password || $errors->has('current_password') || $errors->has('password')) { $tab = 'securite'; }
    $sexeProfil = old('sexe', $u->sexe);
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="sp container-fluid py-4" id="sp-root">

    <header class="sp-head">
        <h1>Paramètres</h1>
        <p>Gérez votre profil, votre sécurité et vos informations professionnelles.</p>
    </header>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="sp-alert sp-alert-ok" role="status"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="sp-alert sp-alert-err" role="alert"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif
    @if(session('force_password_change') || $u->must_change_password)
        <div class="sp-alert sp-alert-err" role="alert"><i class="fas fa-lock"></i> Ce mot de passe temporaire doit être changé avant d’utiliser l’application.</div>
    @endif
    @if($errors->any())
        <div class="sp-alert sp-alert-err" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Certaines informations sont à corriger :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <div class="sp-layout">

        {{-- ===== Colonne identité + navigation ===== --}}
        <aside class="sp-side">
            <div class="sp-id">
                <div class="sp-id-cover"></div>
                <div class="sp-id-body">
                    <div class="sp-avatar-wrap">
                        <img id="sp-avatar" src="{{ $avatar }}" alt="Photo de profil" class="sp-avatar">
                        <label for="profile_image" class="sp-cam" title="Changer la photo">
                            <i class="fas fa-camera"></i><span class="sr-only visually-hidden">Changer la photo</span>
                        </label>
                        <input type="file" id="profile_image" name="profileimg" form="profile-form" class="d-none" accept="image/*">
                    </div>
                    <h2 class="sp-id-name">{{ $u->name }}</h2>
                    <div class="sp-id-user">{{ '@' . $u->username }}</div>
                    <div class="sp-id-mail">{{ $u->email }}</div>
                    <div id="sp-photo-hint" class="sp-photo-hint" hidden>
                        <i class="fas fa-info-circle"></i> Nouvelle photo prête. Cliquez sur « Enregistrer ».
                    </div>
                </div>
            </div>

            <nav class="sp-nav" role="tablist" aria-label="Sections des paramètres">
                <button type="button" role="tab" class="sp-tab" data-tab="profil">
                    <i class="fas fa-user"></i><span>Profil</span>
                </button>
                <button type="button" role="tab" class="sp-tab" data-tab="securite">
                    <i class="fas fa-shield-alt"></i><span>Sécurité</span>
                </button>
                @if($enseignant)
                    <button type="button" role="tab" class="sp-tab" data-tab="enseignant">
                        <i class="fas fa-chalkboard-teacher"></i><span>Enseignant</span>
                    </button>
                @endif
            </nav>
        </aside>

        {{-- ===== Contenu ===== --}}
        <main class="sp-main">

            {{-- PROFIL --}}
            <section class="sp-panel" data-panel="profil" role="tabpanel">
                <div class="sp-panel-head">
                    <h3>Informations personnelles</h3>
                    <p>Ces informations sont visibles par l'administration de l'école.</p>
                </div>

                <form id="profile-form" method="POST" action="{{ route('settings.updateProfile') }}" enctype="multipart/form-data" data-dirty-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="profil">

                    <div class="sp-grid">
                        <div class="sp-field sp-span-2">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $u->name) }}" required autocomplete="name">
                        </div>

                        <div class="sp-field">
                            <label for="username">Nom d'utilisateur</label>
                            <div class="sp-affix"><span>@</span>
                                <input type="text" id="username" name="username" value="{{ old('username', $u->username) }}" required autocomplete="username">
                            </div>
                        </div>

                        <div class="sp-field">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $u->phone) }}" placeholder="+233 ..." required autocomplete="tel">
                        </div>

                        <div class="sp-field">
                            <label for="sexe">Sexe</label>
                            <select id="sexe" name="sexe" required>
                                <option value="">Sélectionner</option>
                                <option value="Masculin" @selected($sexeProfil === 'Masculin')>Masculin</option>
                                <option value="Feminin" @selected(in_array($sexeProfil, ['Féminin', 'Feminin']))>Féminin</option>
                            </select>
                        </div>

                        <div class="sp-field">
                            <label for="email">Adresse email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $u->email) }}" required autocomplete="email">
                        </div>
                    </div>

                    <div class="sp-foot">
                        <span class="sp-dirty" hidden><i class="fas fa-circle"></i> Modifications non enregistrées</span>
                        <button type="submit" class="sp-btn sp-btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </section>

            {{-- SÉCURITÉ --}}
            <section class="sp-panel" data-panel="securite" role="tabpanel" hidden>
                <div class="sp-panel-head">
                    <h3>Mot de passe</h3>
                    <p>Choisissez un mot de passe unique, difficile à deviner.</p>
                </div>

                <form method="POST" action="{{ route('settings.updatePassword') }}" data-dirty-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="securite">

                    <div class="sp-grid">
                        <div class="sp-field sp-span-2">
                            <label for="current_password">Mot de passe actuel</label>
                            <div class="sp-pass">
                                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                                <button type="button" class="sp-eye" data-eye aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="sp-field sp-span-2">
                            <label for="password">Nouveau mot de passe</label>
                            <div class="sp-pass">
                                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                                <button type="button" class="sp-eye" data-eye aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="sp-meter" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                            <small id="sp-strength" class="sp-help">8 caractères minimum, avec majuscules, chiffres et symboles.</small>
                        </div>

                        <div class="sp-field sp-span-2">
                            <label for="password_confirmation">Confirmer le mot de passe</label>
                            <div class="sp-pass">
                                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                                <button type="button" class="sp-eye" data-eye aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                            </div>
                            <small id="sp-match" class="sp-help"></small>
                        </div>
                    </div>

                    <div class="sp-foot">
                        <span></span>
                        <button type="submit" class="sp-btn sp-btn-danger">
                            <i class="fas fa-lock"></i> Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
            </section>

            {{-- ENSEIGNANT --}}
            @if($enseignant)
                <section class="sp-panel" data-panel="enseignant" role="tabpanel" hidden>
                    <div class="sp-panel-head">
                        <h3>Informations enseignant</h3>
                        <p>Votre fiche professionnelle au sein de l'établissement.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.updateTeacherInfo') }}" data-dirty-form>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_tab" value="enseignant">

                        <div class="sp-grid">
                            <div class="sp-field">
                                <label for="t_nom">Nom</label>
                                <input type="text" id="t_nom" name="nom" value="{{ old('nom', $enseignant->nom) }}">
                            </div>
                            <div class="sp-field">
                                <label for="t_prenom">Prénom</label>
                                <input type="text" id="t_prenom" name="prenom" value="{{ old('prenom', $enseignant->prenom) }}">
                            </div>
                            <div class="sp-field">
                                <label for="t_tel">Téléphone</label>
                                <input type="tel" id="t_tel" name="tel" value="{{ old('tel', $enseignant->tel) }}">
                            </div>
                            <div class="sp-field">
                                <label for="t_adresse">Adresse</label>
                                <input type="text" id="t_adresse" name="adresse" value="{{ old('adresse', $enseignant->adresse) }}">
                            </div>
                            <div class="sp-field sp-span-2">
                                <label for="t_specialite">Spécialité</label>
                                <input type="text" id="t_specialite" name="specialite" value="{{ old('specialite', $enseignant->specialite) }}">
                            </div>
                            <div class="sp-field">
                                <label for="t_sexe">Sexe</label>
                                <select id="t_sexe" name="sexe" required>
                                    <option value="">Sélectionner</option>
                                    <option value="Masculin" @selected(old('sexe', $enseignant->sexe ?? '') == 'Masculin')>Masculin</option>
                                    <option value="Féminin" @selected(old('sexe', $enseignant->sexe ?? '') == 'Féminin')>Féminin</option>
                                </select>
                            </div>
                            <div class="sp-field">
                                <label for="t_gs">Groupe sanguin</label>
                                <select id="t_gs" name="groupesanguin">
                                    <option value="">Non renseigné</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs)
                                        <option value="{{ $gs }}" @selected(old('groupesanguin', $enseignant->groupesanguin) == $gs)>{{ $gs }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="sp-foot">
                            <span class="sp-dirty" hidden><i class="fas fa-circle"></i> Modifications non enregistrées</span>
                            <button type="submit" class="sp-btn sp-btn-success">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </section>
            @endif
        </main>
    </div>
</div>

<style>
    .sp {
        --ink: #151a26; --muted: #667085; --line: #e4e8f0; --bg: #f4f6fb; --card: #fff;
        --brand: #007bff; --brand-2: #00aaff; --danger: #dc3545; --ok: #16a34a;
        --radius: 14px;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
        color: var(--ink);
        max-width: 1180px;
    }
    .sp *, .sp *::before, .sp *::after { box-sizing: border-box; }
    .sp [hidden] { display: none !important; }

    .sp-head h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -.02em; margin: 0 0 4px; }
    .sp-head p { color: var(--muted); margin: 0 0 22px; font-size: .95rem; }

    /* Alertes */
    .sp-alert { display: flex; gap: 12px; align-items: flex-start; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px; font-size: .92rem; border: 1px solid; }
    .sp-alert ul { margin: 4px 0 0; padding-left: 18px; }
    .sp-alert-ok  { background: #ecfdf3; border-color: #abefc6; color: #067647; }
    .sp-alert-err { background: #fef3f2; border-color: #fecdca; color: #b42318; }
    .sp-alert i { margin-top: 3px; }

    /* Layout */
    .sp-layout { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 24px; align-items: start; }
    .sp-side { position: sticky; top: 16px; display: grid; gap: 16px; }

    /* Carte identité (reprend le ton de la sidebar) */
    .sp-id { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
    .sp-id-cover { height: 84px; background: linear-gradient(135deg, #16161a 0%, #23232b 55%, #0a4a8f 100%); }
    .sp-id-body { padding: 0 20px 22px; text-align: center; }
    .sp-avatar-wrap { position: relative; width: 104px; height: 104px; margin: -52px auto 12px; }
    .sp-avatar { width: 104px; height: 104px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; background: #fff; box-shadow: 0 0 0 2px var(--brand-2); }
    .sp-cam { position: absolute; right: 0; bottom: 2px; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: var(--brand); color: #fff; border: 3px solid #fff; cursor: pointer; transition: background .2s; }
    .sp-cam:hover { background: #0062cc; }
    .sp-cam:focus-within, .sp-cam:focus-visible { outline: 3px solid rgba(0,123,255,.4); }
    .sp-id-name { font-size: 1.08rem; font-weight: 700; margin: 0; }
    .sp-id-user { color: var(--brand); font-weight: 600; font-size: .86rem; margin-top: 2px; }
    .sp-id-mail { color: var(--muted); font-size: .84rem; margin-top: 4px; word-break: break-all; }
    .sp-photo-hint { margin-top: 12px; font-size: .8rem; color: #175cd3; background: #eff8ff; border-radius: 8px; padding: 8px 10px; }

    /* Navigation */
    .sp-nav { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 8px; display: grid; gap: 2px; }
    .sp-tab { display: flex; align-items: center; gap: 12px; width: 100%; padding: 11px 14px; border: 0; border-radius: 10px; background: transparent; color: #475467; font: 600 .92rem inherit; font-family: inherit; text-align: left; cursor: pointer; transition: background .15s, color .15s; }
    .sp-tab i { width: 18px; text-align: center; color: #98a2b3; }
    .sp-tab:hover { background: #f2f4f7; color: var(--ink); }
    .sp-tab[aria-selected="true"] { background: linear-gradient(90deg, var(--brand), var(--brand-2)); color: #fff; box-shadow: 0 4px 12px rgba(0,123,255,.28); }
    .sp-tab[aria-selected="true"] i { color: #fff; }
    .sp-tab:focus-visible { outline: 3px solid rgba(0,123,255,.4); outline-offset: 1px; }

    /* Panneaux */
    .sp-panel { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); }
    .sp-panel-head { padding: 24px 28px 18px; border-bottom: 1px solid var(--line); }
    .sp-panel-head h3 { font-size: 1.12rem; font-weight: 700; margin: 0 0 4px; letter-spacing: -.01em; }
    .sp-panel-head p { margin: 0; color: var(--muted); font-size: .9rem; }
    .sp-panel form > .sp-grid { padding: 24px 28px; }

    .sp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 22px; }
    .sp-span-2 { grid-column: span 2; }
    .sp-field { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
    .sp .sp-field label { font-size: .84rem; font-weight: 600; color: #344054; margin: 0; }

    .sp input[type=text], .sp input[type=email], .sp input[type=tel], .sp input[type=password], .sp select {
        width: 100%; height: 46px; padding: 0 14px; border: 1px solid #d0d5dd; border-radius: 10px;
        background: #fff; color: var(--ink); font: 500 .95rem inherit; font-family: inherit; transition: border-color .15s, box-shadow .15s;
    }
    .sp input::placeholder { color: #98a2b3; }
    .sp input:hover, .sp select:hover { border-color: #b2bac6; }
    .sp input:focus, .sp select:focus { outline: 0; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(0,123,255,.14); }

    .sp-affix { display: flex; align-items: stretch; }
    .sp-affix span { display: grid; place-items: center; padding: 0 14px; background: #f2f4f7; border: 1px solid #d0d5dd; border-right: 0; border-radius: 10px 0 0 10px; color: var(--muted); font-weight: 600; }
    .sp-affix input { border-radius: 0 10px 10px 0; }

    .sp-pass { position: relative; }
    .sp-pass input { padding-right: 46px; }
    .sp-eye { position: absolute; top: 0; right: 0; width: 46px; height: 46px; border: 0; background: transparent; color: #98a2b3; cursor: pointer; border-radius: 0 10px 10px 0; }
    .sp-eye:hover { color: var(--ink); }
    .sp-eye:focus-visible { outline: 3px solid rgba(0,123,255,.4); }

    .sp-meter { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 2px; }
    .sp-meter i { height: 5px; border-radius: 4px; background: #e4e7ec; transition: background .25s; }
    .sp-help { color: var(--muted); font-size: .8rem; }
    .sp-help.is-ok { color: var(--ok); }
    .sp-help.is-bad { color: var(--danger); }

    /* Pied de formulaire */
    .sp-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 28px; border-top: 1px solid var(--line); background: #fafbfc; border-radius: 0 0 var(--radius) var(--radius); }
    .sp-dirty { color: #b54708; font-size: .84rem; font-weight: 600; }
    .sp-dirty i { font-size: .5rem; vertical-align: middle; margin-right: 6px; }

    .sp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 44px; padding: 0 22px; border: 0; border-radius: 10px; color: #fff; font: 600 .92rem inherit; font-family: inherit; cursor: pointer; transition: filter .15s, box-shadow .15s; }
    .sp-btn:hover { filter: brightness(1.07); box-shadow: 0 6px 16px rgba(16,24,40,.16); }
    .sp-btn:focus-visible { outline: 3px solid rgba(0,123,255,.4); outline-offset: 2px; }
    .sp-btn[disabled] { opacity: .7; cursor: progress; }
    .sp-btn-primary { background: linear-gradient(90deg, var(--brand), var(--brand-2)); }
    .sp-btn-danger  { background: var(--danger); }
    .sp-btn-success { background: var(--ok); }

    /* Mobile */
    @media (max-width: 991.98px) {
        .sp-layout { grid-template-columns: 1fr; }
        .sp-side { position: static; }
        .sp-nav { display: flex; overflow-x: auto; }
        .sp-tab { width: auto; white-space: nowrap; }
    }
    @media (max-width: 575.98px) {
        .sp-grid { grid-template-columns: 1fr; }
        .sp-span-2 { grid-column: auto; }
        .sp-panel-head, .sp-panel form > .sp-grid, .sp-foot { padding-left: 18px; padding-right: 18px; }
        .sp-foot { flex-direction: column-reverse; align-items: stretch; }
        .sp-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .sp * { transition: none !important; } }
</style>

<script>
(function () {
    const root = document.getElementById('sp-root');
    const tabs = root.querySelectorAll('.sp-tab');
    const panels = root.querySelectorAll('.sp-panel');

    // Onglets
    function show(name) {
        if (!root.querySelector('[data-panel="' + name + '"]')) name = 'profil';
        tabs.forEach(t => t.setAttribute('aria-selected', t.dataset.tab === name));
        panels.forEach(p => p.hidden = p.dataset.panel !== name);
        history.replaceState(null, '', '#' + name);
    }
    tabs.forEach(t => t.addEventListener('click', () => show(t.dataset.tab)));
    show(location.hash.replace('#', '') || @json($tab));

    // Aperçu de la photo
    document.getElementById('profile_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Cette image dépasse 2 Mo. Choisissez une image plus légère.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
            document.getElementById('sp-avatar').src = ev.target.result;
            document.getElementById('sp-photo-hint').hidden = false;
        };
        reader.readAsDataURL(file);
    });

    // Afficher / masquer les mots de passe
    root.querySelectorAll('[data-eye]').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    }));

    // Force du mot de passe + correspondance
    const pwd = document.getElementById('password');
    const conf = document.getElementById('password_confirmation');
    const bars = root.querySelectorAll('.sp-meter i');
    const strength = document.getElementById('sp-strength');
    const match = document.getElementById('sp-match');
    const colors = ['#e4e7ec', '#f04438', '#f79009', '#2e90fa', '#12b76a'];
    const labels = ['', 'Faible', 'Moyen', 'Bon', 'Excellent'];

    function score(v) {
        let s = 0;
        if (v.length >= 8) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v) || v.length >= 8) s++;
        return s;
    }
    function checkMatch() {
        if (!conf.value) { match.textContent = ''; match.className = 'sp-help'; return; }
        const ok = conf.value === pwd.value;
        match.textContent = ok ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
        match.className = 'sp-help ' + (ok ? 'is-ok' : 'is-bad');
    }
    pwd.addEventListener('input', () => {
        const s = pwd.value ? score(pwd.value) : 0;
        bars.forEach((b, i) => b.style.background = i < s ? colors[s] : colors[0]);
        strength.textContent = s ? 'Sécurité : ' + labels[s] : '8 caractères minimum, avec majuscules, chiffres et symboles.';
        checkMatch();
    });
    conf.addEventListener('input', checkMatch);

    // Modifications non enregistrées + état de chargement à l'envoi
    root.querySelectorAll('form[data-dirty-form]').forEach(form => {
        const hint = form.querySelector('.sp-dirty');
        const snap = () => JSON.stringify([...new FormData(form).entries()].map(([k, v]) => [k, v instanceof File ? v.name + v.size : v]));
        let initial = snap();
        const refresh = () => { if (hint) hint.hidden = snap() === initial; };
        form.addEventListener('input', refresh);
        form.addEventListener('change', refresh);
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
        });
    });
})();
</script>
@endsection
