<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- PWA / MANIFEST --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#007bff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SchoolPlus">

    <title>SP-Connexion</title>
    <link rel="shortcut icon" href="{{ asset('dist/img/logo.png') }}" type="image/x-icon">

    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    {{-- Thème appliqué avant le rendu pour éviter tout flash --}}
    <script>
        (function() {
            var t = null;
            try { t = localStorage.getItem('sp-theme'); } catch (e) {}
            if (!t && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>

    <style>
        /* =====================================================
           SchoolPlus Login v2 : tout est préfixé .sp-
        ====================================================== */
        @property --sp-angle {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        :root {
            --sp-ink: #0f1b3d;
            --sp-muted: #5b6785;
            --sp-line: #dde3f0;
            --sp-surface: #fff;
            --sp-bg: #fff;
            --sp-glow: #eaf0ff;
            --sp-soft: #f4f7ff;
            --sp-brand: #2f5bea;
            --sp-brand-dark: #1f43c4;
            --sp-danger: #b42318;
            --sp-danger-bg: #fef3f2;
            --sp-danger-line: #fecdca;
            --sp-ok: #067647;
            --sp-ok-bg: #ecfdf3;
            --sp-ok-line: #abefc6;
            --sp-ease: cubic-bezier(.22, .8, .3, 1);
        }

        [data-theme="dark"] {
            --sp-ink: #e8edfb;
            --sp-muted: #98a4c4;
            --sp-line: #2a3556;
            --sp-surface: #121a33;
            --sp-bg: #0b1226;
            --sp-glow: #16224a;
            --sp-soft: #1a2547;
            --sp-brand: #6b8cff;
            --sp-brand-dark: #4d6fee;
            --sp-danger: #fda29b;
            --sp-danger-bg: #3a1618;
            --sp-danger-line: #6b2a2d;
            --sp-ok: #75e0a7;
            --sp-ok-bg: #0f2e22;
            --sp-ok-line: #1f5a40;
        }

        html,
        body.sp-login {
            margin: 0;
            min-height: 100%;
        }

        body.sp-login {
            font-family: 'Segoe UI', system-ui, -apple-system, Roboto, Tahoma, sans-serif;
            color: var(--sp-ink);
            background: var(--sp-bg);
            overflow-x: hidden;
        }

        .sp-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        }

        /* ---------- Panneau gauche ---------- */
        .sp-aside {
            --mx: 0;
            --my: 0;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(2rem, 5vw, 4rem);
            color: #fff;
            background: #10205f;
        }

        /* Fond : photo + dégradé maillé lent (transform uniquement) */
        .sp-aside::before {
            content: "";
            position: absolute;
            inset: -20%;
            z-index: -2;
            background:
                radial-gradient(40% 40% at 20% 25%, rgba(94, 224, 210, .55), transparent 70%),
                radial-gradient(45% 45% at 80% 20%, rgba(123, 149, 255, .6), transparent 70%),
                radial-gradient(50% 50% at 60% 85%, rgba(47, 91, 234, .7), transparent 70%),
                linear-gradient(155deg, rgba(14, 28, 82, .95), rgba(31, 67, 196, .88)),
                url("{{ asset('dist/img/4.jpg') }}") center / cover no-repeat;
            animation: sp-mesh 26s ease-in-out infinite alternate;
        }

        .sp-aside::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            opacity: .07;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        .sp-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sp-brand img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            object-fit: cover;
        }

        .sp-pitch {
            max-width: 32rem;
        }

        .sp-pitch h2 {
            margin: 0 0 1rem;
            font-size: clamp(1.9rem, 3.2vw, 2.9rem);
            line-height: 1.12;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .sp-w {
            display: inline-block;
            animation: sp-rise .7s var(--sp-ease) both;
            animation-delay: calc(.15s + var(--i) * .07s);
        }

        /* Mot qui alterne : 3 mots empilés, cycle de 9 s */
        .sp-rotor {
            display: block;
            position: relative;
            height: 1.15em;
            overflow: hidden;
        }

        .sp-rotor span {
            position: absolute;
            left: 0;
            top: 0;
            white-space: nowrap;
            opacity: 0;
            transform: translateY(60%);
            animation: sp-word 9s var(--sp-ease) infinite;
            animation-delay: calc(var(--k) * 3s);
        }

        .sp-pitch p {
            margin: 0;
            color: rgba(255, 255, 255, .8);
            font-size: 1.05rem;
            line-height: 1.6;
            animation: sp-rise .8s .7s var(--sp-ease) both;
        }

        /* Mini-cartes : parallax (externe) + flottement (interne) */
        .sp-floats {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .sp-fc {
            position: absolute;
            transform: translate3d(calc(var(--mx) * var(--d, 10) * 1px), calc(var(--my) * var(--d, 10) * 1px), 0);
            transition: transform .4s ease-out;
        }

        .sp-fc-in {
            width: 190px;
            padding: .85rem 1rem;
            border-radius: 14px;
            color: #fff;
            font-size: .8rem;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            box-shadow: 0 14px 30px rgba(6, 14, 50, .3);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            animation: sp-rise .9s var(--sp-ease) both, sp-float 7s ease-in-out infinite alternate;
            animation-delay: var(--t, 1s), var(--t, 1s);
        }

        .sp-fc--a { top: 14%; right: 7%; --d: 14; --t: 1s; }
        .sp-fc--b { top: 46%; right: 26%; --d: -10; --t: 1.3s; }
        .sp-fc--c { bottom: 15%; right: 6%; --d: 8; --t: 1.6s; }

        .sp-fc-in b {
            display: block;
            margin-bottom: .5rem;
            font-size: .82rem;
            font-weight: 600;
        }

        .sp-bar {
            height: 6px;
            border-radius: 6px;
            background: rgba(255, 255, 255, .2);
            overflow: hidden;
        }

        .sp-bar i {
            display: block;
            height: 100%;
            width: 82%;
            border-radius: 6px;
            background: #5ee0d2;
            transform-origin: left;
            animation: sp-grow 1.6s 1.6s var(--sp-ease) both;
        }

        .sp-dots {
            display: flex;
            gap: 6px;
        }

        .sp-dots i {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #5ee0d2;
            animation: sp-pop .5s var(--sp-ease) both;
            animation-delay: calc(1.8s + var(--i) * .12s);
        }

        .sp-dots i.off { background: rgba(255, 255, 255, .3); }

        .sp-pay {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .sp-pay svg {
            width: 26px;
            height: 26px;
            flex: none;
        }

        .sp-pay circle { fill: #5ee0d2; }

        .sp-pay path {
            fill: none;
            stroke: #10205f;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 20;
            animation: sp-draw .7s 2.2s ease forwards;
            stroke-dashoffset: 20;
        }

        .sp-foot {
            font-size: .8rem;
            color: rgba(255, 255, 255, .6);
        }

        /* ---------- Zone formulaire ---------- */
        .sp-main {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: radial-gradient(900px 500px at 100% 0%, var(--sp-glow) 0%, transparent 60%), var(--sp-bg);
        }

        .sp-theme {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid var(--sp-line);
            border-radius: 12px;
            background: var(--sp-surface);
            color: var(--sp-muted);
            cursor: pointer;
            transition: color .2s, transform .3s var(--sp-ease);
        }

        .sp-theme:hover { color: var(--sp-brand); transform: rotate(18deg); }
        .sp-theme .fa-sun { display: none; }
        [data-theme="dark"] .sp-theme .fa-sun { display: inline; }
        [data-theme="dark"] .sp-theme .fa-moon { display: none; }

        /* Carte avec liseré lumineux au focus */
        .sp-card {
            position: relative;
            width: 100%;
            max-width: 26rem;
            padding: 2rem 1.75rem;
            border-radius: 20px;
            background: var(--sp-surface);
            border: 1px solid var(--sp-line);
            box-shadow: 0 20px 50px rgba(15, 27, 61, .08);
            animation: sp-rise .7s var(--sp-ease) both;
        }

        .sp-card::before {
            content: "";
            position: absolute;
            inset: -1px;
            z-index: -1;
            border-radius: 21px;
            padding: 0;
            background: conic-gradient(from var(--sp-angle), transparent 0 55%, var(--sp-brand) 75%, #5ee0d2 88%, transparent 100%);
            opacity: 0;
            transition: opacity .5s;
        }

        .sp-card:focus-within::before {
            opacity: .9;
            animation: sp-spin 5s linear infinite;
        }

        .sp-card.sp-shake { animation: sp-rise .7s var(--sp-ease) both, sp-shake .5s .7s ease; }

        .sp-stagger > * {
            animation: sp-rise .6s var(--sp-ease) both;
            animation-delay: calc(.15s + var(--i, 0) * .07s);
        }

        .sp-card-logo {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            object-fit: cover;
            border: 1px solid var(--sp-line);
            box-shadow: 0 6px 18px rgba(47, 91, 234, .15);
            margin-bottom: 1.1rem;
        }

        .sp-title { margin: 0 0 .4rem; font-size: 1.65rem; font-weight: 700; letter-spacing: -.02em; }
        .sp-sub { margin: 0 0 1.6rem; color: var(--sp-muted); font-size: .96rem; }

        /* Alertes */
        .sp-alert {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: .8rem 2.4rem .8rem .95rem;
            margin-bottom: 1.2rem;
            border-radius: 12px;
            border: 1px solid;
            font-size: .92rem;
            line-height: 1.45;
            animation: sp-slide .5s var(--sp-ease) both;
        }

        .sp-alert--danger { color: var(--sp-danger); background: var(--sp-danger-bg); border-color: var(--sp-danger-line); }
        .sp-alert--success { color: var(--sp-ok); background: var(--sp-ok-bg); border-color: var(--sp-ok-line); }
        .sp-alert ul { margin: 0; padding-left: 1.1rem; }
        .sp-alert .close { position: absolute; top: .35rem; right: .6rem; color: inherit; opacity: .6; text-shadow: none; }
        .sp-alert .close:hover { opacity: 1; }

        /* Champs à label flottant */
        .sp-field { position: relative; margin-bottom: 1.1rem; }

        .sp-input {
            width: 100%;
            height: 54px;
            padding: 1.25rem 1rem .35rem 2.75rem;
            font: inherit;
            font-size: .98rem;
            color: var(--sp-ink);
            background: var(--sp-surface);
            border: 1px solid var(--sp-line);
            border-radius: 12px;
            transition: border-color .25s, box-shadow .25s;
        }

        .sp-input:hover { border-color: #b9c4e2; }

        .sp-input:focus {
            outline: none;
            border-color: var(--sp-brand);
            box-shadow: 0 0 0 4px rgba(47, 91, 234, .15);
        }

        .sp-input--pw { padding-right: 3rem; }

        .sp-label {
            position: absolute;
            left: 2.75rem;
            top: 50%;
            margin: 0;
            color: var(--sp-muted);
            font-size: .95rem;
            pointer-events: none;
            transform-origin: left top;
            transform: translateY(-50%);
            transition: transform .25s var(--sp-ease), color .25s;
        }

        .sp-input:focus ~ .sp-label,
        .sp-input:not(:placeholder-shown) ~ .sp-label {
            transform: translateY(-112%) scale(.78);
        }

        .sp-input:focus ~ .sp-label { color: var(--sp-brand); }

        .sp-ico {
            position: absolute;
            left: 1rem;
            top: 50%;
            margin-top: -.5em;
            color: #8b96b2;
            font-size: .95rem;
            pointer-events: none;
            transition: color .25s, transform .3s var(--sp-ease);
        }

        .sp-input:focus ~ .sp-ico { color: var(--sp-brand); transform: scale(1.12); }

        /* Soulignement qui s'étire */
        .sp-field::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 0;
            height: 2px;
            border-radius: 2px;
            background: var(--sp-brand);
            transform: scaleX(0);
            transition: transform .4s var(--sp-ease);
        }

        .sp-field:focus-within::after { transform: scaleX(1); }

        .sp-eye {
            position: absolute;
            right: .35rem;
            top: 50%;
            margin-top: -20px;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #8b96b2;
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .sp-eye:hover { background: var(--sp-soft); color: var(--sp-brand); }

        .sp-eye i {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            transition: opacity .25s, transform .3s var(--sp-ease);
        }

        .sp-eye .fa-eye-slash { opacity: 0; transform: scale(.6) rotate(-30deg); }
        .sp-eye.is-on .fa-eye { opacity: 0; transform: scale(.6) rotate(30deg); }
        .sp-eye.is-on .fa-eye-slash { opacity: 1; transform: none; }

        .sp-caps {
            display: block;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin: 0;
            font-size: .8rem;
            color: #b54708;
            transition: max-height .3s var(--sp-ease), opacity .3s, margin .3s;
        }

        .sp-caps.is-on { max-height: 2rem; opacity: 1; margin: -.45rem 0 .9rem; }
        [data-theme="dark"] .sp-caps { color: #fdb022; }

        .sp-eye:focus-visible, .sp-link:focus-visible, .sp-btn:focus-visible, .sp-theme:focus-visible {
            outline: 3px solid rgba(47, 91, 234, .5);
            outline-offset: 2px;
        }

        /* Ligne options */
        .sp-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.4rem; flex-wrap: wrap; }

        .sp-check { position: relative; display: inline-flex; align-items: center; margin: 0; cursor: pointer; font-size: .9rem; color: var(--sp-muted); user-select: none; }
        .sp-check input { position: absolute; opacity: 0; width: 1px; height: 1px; }

        .sp-check span::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: .55rem;
            vertical-align: -4px;
            border: 1.5px solid #b7c2dd;
            border-radius: 5px;
            background: var(--sp-surface) center / 12px no-repeat;
            transition: background-color .2s, border-color .2s, transform .2s var(--sp-ease);
        }

        .sp-check:active span::before { transform: scale(.88); }

        .sp-check input:checked + span::before {
            border-color: var(--sp-brand);
            background-color: var(--sp-brand);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2.5 6.3l2.4 2.4 4.6-5' fill='none' stroke='%23fff' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        .sp-check input:focus-visible + span::before { outline: 3px solid rgba(47, 91, 234, .5); outline-offset: 2px; }

        .sp-link { color: var(--sp-brand); font-size: .9rem; font-weight: 600; text-decoration: none; border-radius: 4px; }
        .sp-link:hover { color: var(--sp-brand-dark); text-decoration: underline; }

        /* Bouton */
        .sp-btn {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 12px;
            font: inherit;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--sp-brand), var(--sp-brand-dark));
            box-shadow: 0 8px 20px rgba(47, 91, 234, .28);
            transition: transform .25s var(--sp-ease), box-shadow .25s, filter .25s;
        }

        .sp-btn::after {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .3), transparent);
            transform: skewX(-20deg);
            transition: left .7s var(--sp-ease);
        }

        .sp-btn:hover { transform: translateY(-2px); filter: brightness(1.07); box-shadow: 0 12px 26px rgba(47, 91, 234, .35); }
        .sp-btn:hover::after { left: 120%; }
        .sp-btn:active { transform: translateY(1px) scale(.985); box-shadow: 0 4px 10px rgba(47, 91, 234, .3); }

        .sp-btn .sp-spin { display: none; width: 18px; height: 18px; border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 50%; animation: sp-rot .7s linear infinite; }
        .sp-btn.is-loading { pointer-events: none; filter: brightness(.95); }
        .sp-btn.is-loading .sp-spin { display: inline-block; }
        .sp-btn.is-loading .sp-ico-btn { display: none; }

        .sp-help { margin: 1.5rem 0 0; text-align: center; color: var(--sp-muted); font-size: .88rem; }

        /* ---------- Modal ---------- */
        #forgotPasswordModal .modal-content { border: 0; border-radius: 18px; background: var(--sp-surface); box-shadow: 0 24px 60px rgba(15, 27, 61, .3); color: var(--sp-ink); }
        #forgotPasswordModal .modal-header { border-bottom: 0; padding: 1.5rem 1.5rem .25rem; align-items: flex-start; }
        #forgotPasswordModal .modal-title { font-size: 1.2rem; font-weight: 700; }
        #forgotPasswordModal .modal-title i { color: var(--sp-brand); margin-right: .4rem; }
        #forgotPasswordModal .modal-body { padding: .5rem 1.5rem 1.75rem; }
        #forgotPasswordModal .modal-body p { color: var(--sp-muted); font-size: .93rem; margin-bottom: 1.25rem; }
        #forgotPasswordModal .close { color: var(--sp-ink); opacity: .55; text-shadow: none; }
        #forgotPasswordModal.fade .modal-dialog { transform: scale(.92) translateY(24px); transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        #forgotPasswordModal.show .modal-dialog { transform: none; }

        /* ---------- Keyframes ---------- */
        @keyframes sp-mesh { to { transform: translate3d(3%, -3%, 0) scale(1.08) rotate(3deg); } }
        @keyframes sp-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes sp-slide { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: none; } }
        @keyframes sp-float { to { translate: 0 -10px; } }
        @keyframes sp-grow { from { transform: scaleX(0); } }
        @keyframes sp-pop { from { transform: scale(0); } }
        @keyframes sp-draw { to { stroke-dashoffset: 0; } }
        @keyframes sp-spin { to { --sp-angle: 360deg; } }
        @keyframes sp-rot { to { transform: rotate(360deg); } }
        @keyframes sp-shake { 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        @keyframes sp-word {
            0% { opacity: 0; transform: translateY(60%); }
            6%, 30% { opacity: 1; transform: none; }
            36%, 100% { opacity: 0; transform: translateY(-60%); }
        }

        /* ---------- Reduced motion ---------- */
        @media (prefers-reduced-motion: reduce) {
            .sp-login *, .sp-login *::before, .sp-login *::after { animation: none; transition: none; }
            .sp-rotor span:first-child { opacity: 1; transform: none; }
            .sp-pay path { stroke-dashoffset: 0; }
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1199.98px) { .sp-fc--b { display: none; } }

        @media (max-width: 991.98px) {
            .sp-shell { grid-template-columns: minmax(0, .8fr) minmax(0, 1fr); }
            .sp-floats { display: none; }
        }

        @media (max-width: 767.98px) {
            .sp-shell { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto 1fr; }
            .sp-aside { padding: 1.25rem 1.25rem 1.5rem; gap: .75rem; }
            .sp-pitch h2 { font-size: 1.25rem; margin: 0; }
            .sp-pitch p, .sp-foot { display: none; }
            .sp-main { align-items: flex-start; padding-top: 1.5rem; }
            .sp-card { padding: 1.5rem 1.25rem; }
            .sp-card-logo { display: none; }
            .sp-theme { top: .75rem; }
        }
    </style>
</head>

<body class="sp-login">

    <div class="sp-shell">

        {{-- ================= PRÉSENTATION ================= --}}
        <aside class="sp-aside" id="spAside" aria-label="Présentation de SchoolPlus">

            <div class="sp-brand">
                <img src="{{ asset('dist/img/logo.png') }}" alt="">
                <span>SchoolPlus</span>
            </div>

            <div class="sp-pitch">
                <h2>
                    <span class="sp-w" style="--i:0">Une</span>
                    <span class="sp-w" style="--i:1">gestion</span>
                    <span class="sp-w" style="--i:2">scolaire</span>
                    <span class="sp-rotor sp-w" style="--i:3" aria-label="plus simple">
                        <span style="--k:0" aria-hidden="true">plus simple.</span>
                        <span style="--k:1" aria-hidden="true">plus rapide.</span>
                        <span style="--k:2" aria-hidden="true">plus intelligente.</span>
                    </span>
                </h2>
                <p>Élèves, enseignants, parents et administration réunis sur une seule plateforme.</p>
            </div>

            <div class="sp-foot">&copy; {{ date('Y') }} SchoolPlus</div>

            {{-- Mini-cartes illustratives (décoratives) --}}
            <div class="sp-floats" aria-hidden="true">
                <div class="sp-fc sp-fc--a">
                    <div class="sp-fc-in">
                        <b>Bulletin du trimestre</b>
                        <div class="sp-bar"><i></i></div>
                    </div>
                </div>
                <div class="sp-fc sp-fc--b">
                    <div class="sp-fc-in">
                        <b>Présences</b>
                        <div class="sp-dots">
                            <i style="--i:0"></i><i style="--i:1"></i><i style="--i:2"></i><i class="off" style="--i:3"></i><i style="--i:4"></i>
                        </div>
                    </div>
                </div>
                <div class="sp-fc sp-fc--c">
                    <div class="sp-fc-in sp-pay">
                        <svg viewBox="0 0 26 26"><circle cx="13" cy="13" r="13" /><path d="M7.5 13.5l4 4 7-8" /></svg>
                        <b style="margin:0">Paiement confirmé</b>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ================= CONNEXION ================= --}}
        <main class="sp-main">

            <button type="button" class="sp-theme" id="themeToggle" aria-label="Changer de thème">
                <i class="fas fa-moon" aria-hidden="true"></i>
                <i class="fas fa-sun" aria-hidden="true"></i>
            </button>

            <div class="sp-card sp-stagger {{ session('danger') || $errors->any() ? 'sp-shake' : '' }}">

                <img class="sp-card-logo" src="{{ asset('dist/img/logo.png') }}" alt="Logo SchoolPlus" style="--i:0">

                <div style="--i:1">
                    <h1 class="sp-title">Bienvenue sur SchoolPlus</h1>
                    <p class="sp-sub">Connectez-vous pour accéder à votre espace.</p>
                </div>

                {{-- Messages de session / validation (existants) --}}
                <div style="--i:2">
                    @if (session('danger'))
                        <div class="sp-alert sp-alert--danger" role="alert">
                            <i class="fas fa-exclamation-triangle mt-1" aria-hidden="true"></i>
                            <div>{{ session('danger') }}</div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="sp-alert sp-alert--success" role="status">
                            <i class="fas fa-check-circle mt-1" aria-hidden="true"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="sp-alert sp-alert--danger" role="alert">
                            <i class="fas fa-exclamation-circle mt-1" aria-hidden="true"></i>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- FORMULAIRE (action, méthode, name inchangés) --}}
                <form method="POST" action="{{ route('verifylogins') }}" id="loginForm" style="--i:3">
                    @csrf

                    <div class="sp-field">
                        <input type="text" name="email" id="email" class="sp-input" placeholder=" "
                            value="{{ old(
                                'email',
                                Auth::viaRemember() ? Auth::user()->email ?? (Auth::user()->username ?? Auth::user()->matricule) : '',
                            ) }}"
                            autocomplete="username" required autofocus>
                        <label for="email" class="sp-label">Email / Username / Matricule</label>
                        <i class="fas fa-user sp-ico" aria-hidden="true"></i>
                    </div>

                    <div class="sp-field">
                        <input type="password" name="password" id="password" class="sp-input sp-input--pw"
                            placeholder=" " autocomplete="current-password" required>
                        <label for="password" class="sp-label">Mot de passe</label>
                        <i class="fas fa-lock sp-ico" aria-hidden="true"></i>
                        <button type="button" class="sp-eye" id="togglePassword"
                            aria-label="Afficher le mot de passe" aria-controls="password">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            <i class="fas fa-eye-slash" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span class="sp-caps" id="capsHint" role="status">
                        <i class="fas fa-arrow-up" aria-hidden="true"></i> Verr. Maj est activé
                    </span>

                    <div class="sp-row">
                        <label class="sp-check" for="remember">
                            <input type="checkbox" name="remember" id="remember"
                                {{ old('remember') || Auth::viaRemember() ? 'checked' : '' }}>
                            <span>Se souvenir de moi</span>
                        </label>

                        <a href="#" class="sp-link" data-toggle="modal" data-target="#forgotPasswordModal">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="sp-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt sp-ico-btn" aria-hidden="true"></i>
                        <span class="sp-spin" aria-hidden="true"></span>
                        <span id="loginBtnText">Se connecter</span>
                    </button>
                </form>

                <p class="sp-help" style="--i:4">Pas encore de compte ? Parlez à l'administration.</p>
            </div>
        </main>
    </div>

    {{-- ================= MODAL MOT DE PASSE OUBLIÉ ================= --}}
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog"
        aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-unlock-alt" aria-hidden="true"></i>
                        Réinitialisation du mot de passe
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p>
                        Entrez votre email, username ou matricule.
                        Un lien de réinitialisation sera envoyé
                        à l'adresse email associée à votre compte.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="sp-field">
                            <input type="text" name="email" id="resetEmail" class="sp-input" placeholder=" "
                                value="{{ old('email') }}" autocomplete="username" required>
                            <label for="resetEmail" class="sp-label">Email / Username / Matricule</label>
                            <i class="fas fa-user-lock sp-ico" aria-hidden="true"></i>
                        </div>

                        <button type="submit" class="sp-btn">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            Envoyer le lien
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT : jQuery AVANT Bootstrap 4 --}}
    <script src="{{ asset('dist/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            // Modal (comportement existant)
            $('[data-toggle="modal"]').on('click', function(e) {
                e.preventDefault();
                $('#forgotPasswordModal').modal('show');
            });

            $('#forgotPasswordModal').on('hidden.bs.modal', function() {
                $('#resetEmail').val('');
            });

            // Afficher / masquer le mot de passe
            $('#togglePassword').on('click', function() {
                var show = $('#password').attr('type') === 'password';
                $('#password').attr('type', show ? 'text' : 'password');
                $(this).toggleClass('is-on', show)
                    .attr('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            });

            // Avertissement Verr. Maj
            $('#password').on('keydown keyup', function(e) {
                var on = e.originalEvent.getModifierState && e.originalEvent.getModifierState('CapsLock');
                $('#capsHint').toggleClass('is-on', !!on);
            }).on('blur', function() {
                $('#capsHint').removeClass('is-on');
            });

            // État de chargement : la soumission n'est jamais bloquée
            $('#loginForm').on('submit', function() {
                $('#loginBtn').addClass('is-loading').attr('aria-busy', 'true');
                $('#loginBtnText').text('Connexion…');
            });

            // Retour arrière (cache bfcache) : on réactive le bouton
            $(window).on('pageshow', function() {
                $('#loginBtn').removeClass('is-loading').removeAttr('aria-busy');
                $('#loginBtnText').text('Se connecter');
            });

            // Thème clair / sombre (mémorisé)
            $('#themeToggle').on('click', function() {
                var light = document.documentElement.getAttribute('data-theme') !== 'light';
                if (dark) document.documentElement.setAttribute('data-theme', 'light');
                else document.documentElement.removeAttribute('data-theme');
                try { localStorage.setItem('sp-theme', light ? 'light' : 'dark'); } catch (e) {}
            });

            // Parallaxe douce (souris uniquement, pas de tactile, pas de reduced-motion)
            if (window.matchMedia('(hover: <!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- PWA / MANIFEST --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f1520">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SchoolPlus">

    <title>SP-Connexion</title>
    <link rel="shortcut icon" href="{{ asset('dist/img/logo.png') }}" type="image/x-icon">

    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        /* =====================================================
           SchoolPlus Login v3 : thème unique, aligné sur la
           sidebar de l'application. Tout est préfixé .sp-
        ====================================================== */
        @property --sp-angle {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        :root {
            /* Mêmes jetons que la sidebar */
            --sp-bg: #0f1520;
            --sp-bg-soft: #151c2b;
            --sp-text: #a9b4c6;
            --sp-dim: #6b778c;
            --sp-accent: #5b9dff;
            --sp-accent-bg: rgba(91, 157, 255, .13);
            --sp-danger: #ff6b6b;
            --sp-line: rgba(255, 255, 255, .08);
            --sp-ease: cubic-bezier(.22, .8, .3, 1);
        }

        html,
        body.sp-login {
            margin: 0;
            min-height: 100%;
        }

        body.sp-login {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #fff;
            background: var(--sp-bg);
            overflow-x: hidden;
        }

        .sp-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
        }

        /* ---------- Panneau fonctionnalités (gauche) ---------- */
        .sp-aside {
            --x: 30%;
            --y: 20%;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2rem;
            padding: clamp(2rem, 4.5vw, 4rem);
            background:
                radial-gradient(520px circle at var(--x) var(--y), rgba(91, 157, 255, .16), transparent 65%),
                var(--sp-bg);
        }

        /* Halos lents (transform uniquement) */
        .sp-aside::before,
        .sp-aside::after {
            content: "";
            position: absolute;
            z-index: -1;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .28;
            pointer-events: none;
            animation: sp-drift 20s ease-in-out infinite alternate;
        }

        .sp-aside::before { background: #2f6bff; top: -140px; right: -100px; }
        .sp-aside::after { background: #22d3c5; bottom: -180px; left: -120px; animation-delay: -10s; opacity: .16; }

        .sp-grid {
            position: absolute;
            inset: 0;
            z-index: -1;
            opacity: .05;
            pointer-events: none;
            background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px);
            background-size: 48px 48px;
            -webkit-mask-image: radial-gradient(circle at 40% 30%, #000, transparent 75%);
            mask-image: radial-gradient(circle at 40% 30%, #000, transparent 75%);
        }

        .sp-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: 1.2rem;
            font-weight: 700;
            animation: sp-rise .7s var(--sp-ease) both;
        }

        .sp-brand img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            object-fit: cover;
        }

        .sp-pitch h2 {
            margin: 0 0 .7rem;
            max-width: 34rem;
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            line-height: 1.12;
            font-weight: 700;
            letter-spacing: -.025em;
        }

        .sp-w {
            display: inline-block;
            animation: sp-rise .7s var(--sp-ease) both;
            animation-delay: calc(.1s + var(--i) * .06s);
        }

        .sp-pitch p {
            margin: 0;
            max-width: 32rem;
            color: var(--sp-text);
            line-height: 1.6;
            animation: sp-rise .7s .45s var(--sp-ease) both;
        }

        /* Fonctionnalités */
        .sp-feats {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .8rem;
        }

        .sp-feat {
            position: relative;
            padding: .95rem 1rem;
            border-radius: 14px;
            background: rgba(21, 28, 43, .7);
            border: 1px solid var(--sp-line);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            animation: sp-rise .6s var(--sp-ease) both;
            animation-delay: calc(.5s + var(--i) * .06s);
            transition: transform .3s var(--sp-ease), border-color .3s;
        }

        .sp-feat:hover { transform: translateY(-3px); border-color: rgba(91, 157, 255, .45); }

        /* Un seul « faisceau » qui parcourt les tuiles à tour de rôle */
        .sp-feat::after {
            content: "";
            position: absolute;
            inset: -1px;
            border-radius: 14px;
            border: 1px solid var(--sp-accent);
            box-shadow: 0 0 22px rgba(91, 157, 255, .28);
            opacity: 0;
            pointer-events: none;
            animation: sp-spot 13.5s ease-in-out infinite;
            animation-delay: calc(2s + var(--i) * 1.5s);
        }

        .sp-feat i {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            margin-bottom: .7rem;
            border-radius: 9px;
            background: var(--sp-accent-bg);
            color: var(--sp-accent);
            font-size: .9rem;
        }

        .sp-feat b { display: block; margin-bottom: .2rem; font-size: .92rem; font-weight: 600; }
        .sp-feat span { display: block; color: var(--sp-dim); font-size: .78rem; line-height: 1.4; }

        .sp-roles {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .45rem;
            color: var(--sp-dim);
            font-size: .82rem;
            animation: sp-rise .7s 1.2s var(--sp-ease) both;
        }

        .sp-roles em {
            font-style: normal;
            padding: .2rem .65rem;
            border-radius: 20px;
            color: var(--sp-text);
            background: var(--sp-accent-bg);
            border: 1px solid rgba(91, 157, 255, .2);
        }

        /* ---------- Zone formulaire ---------- */
        .sp-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: #0b111b;
            border-left: 1px solid var(--sp-line);
        }

        .sp-card {
            position: relative;
            width: 100%;
            max-width: 26rem;
            padding: 2rem 1.75rem;
            border-radius: 20px;
            background: var(--sp-bg-soft);
            border: 1px solid var(--sp-line);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
            animation: sp-rise .7s var(--sp-ease) both;
        }

        /* Liseré lumineux au focus */
        .sp-card::before {
            content: "";
            position: absolute;
            inset: -1px;
            z-index: -1;
            border-radius: 21px;
            background: conic-gradient(from var(--sp-angle), transparent 0 55%, var(--sp-accent) 75%, #22d3c5 88%, transparent 100%);
            opacity: 0;
            transition: opacity .5s;
        }

        .sp-card:focus-within::before { opacity: .85; animation: sp-spin 5s linear infinite; }
        .sp-card.sp-shake { animation: sp-rise .7s var(--sp-ease) both, sp-shake .5s .7s ease; }

        .sp-stagger > * {
            animation: sp-rise .6s var(--sp-ease) both;
            animation-delay: calc(.15s + var(--i, 0) * .07s);
        }

        .sp-card-logo {
            width: 50px;
            height: 50px;
            margin-bottom: 1.1rem;
            border-radius: 14px;
            object-fit: cover;
            background: #fff;
            padding: 2px;
        }

        .sp-title { margin: 0 0 .4rem; font-size: 1.6rem; font-weight: 700; letter-spacing: -.02em; }
        .sp-sub { margin: 0 0 1.6rem; color: var(--sp-text); font-size: .95rem; }

        /* Alertes */
        .sp-alert {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: .8rem 2.4rem .8rem .95rem;
            margin-bottom: 1.2rem;
            border-radius: 12px;
            border: 1px solid;
            font-size: .9rem;
            line-height: 1.45;
            animation: sp-slide .5s var(--sp-ease) both;
        }

        .sp-alert--danger { color: #ffb4b4; background: rgba(255, 107, 107, .1); border-color: rgba(255, 107, 107, .35); }
        .sp-alert--success { color: #86efac; background: rgba(34, 197, 94, .1); border-color: rgba(34, 197, 94, .35); }
        .sp-alert ul { margin: 0; padding-left: 1.1rem; }
        .sp-alert .close { position: absolute; top: .35rem; right: .6rem; color: inherit; opacity: .7; text-shadow: none; }
        .sp-alert .close:hover { opacity: 1; }

        /* Champs à label flottant */
        .sp-field { position: relative; margin-bottom: 1.1rem; }

        .sp-input {
            width: 100%;
            height: 54px;
            padding: 1.25rem 1rem .35rem 2.75rem;
            font: inherit;
            font-size: .98rem;
            color: #fff;
            background: var(--sp-bg);
            border: 1px solid var(--sp-line);
            border-radius: 12px;
            transition: border-color .25s, box-shadow .25s;
        }

        .sp-input:hover { border-color: rgba(255, 255, 255, .18); }
        .sp-input:focus { outline: none; border-color: var(--sp-accent); box-shadow: 0 0 0 4px rgba(91, 157, 255, .18); }
        .sp-input--pw { padding-right: 3rem; }

        /* Empêche le fond jaune/blanc de l'autofill navigateur */
        .sp-input:-webkit-autofill {
            -webkit-text-fill-color: #fff;
            box-shadow: 0 0 0 40px var(--sp-bg) inset;
            caret-color: #fff;
        }

        .sp-label {
            position: absolute;
            left: 2.75rem;
            top: 50%;
            margin: 0;
            color: var(--sp-dim);
            font-size: .95rem;
            pointer-events: none;
            transform-origin: left top;
            transform: translateY(-50%);
            transition: transform .25s var(--sp-ease), color .25s;
        }

        .sp-input:focus ~ .sp-label,
        .sp-input:not(:placeholder-shown) ~ .sp-label { transform: translateY(-112%) scale(.78); }
        .sp-input:focus ~ .sp-label { color: var(--sp-accent); }

        .sp-ico {
            position: absolute;
            left: 1rem;
            top: 50%;
            margin-top: -.5em;
            color: var(--sp-dim);
            font-size: .95rem;
            pointer-events: none;
            transition: color .25s, transform .3s var(--sp-ease);
        }

        .sp-input:focus ~ .sp-ico { color: var(--sp-accent); transform: scale(1.12); }

        .sp-field::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 0;
            height: 2px;
            border-radius: 2px;
            background: var(--sp-accent);
            transform: scaleX(0);
            transition: transform .4s var(--sp-ease);
        }

        .sp-field:focus-within::after { transform: scaleX(1); }

        .sp-eye {
            position: absolute;
            right: .35rem;
            top: 50%;
            margin-top: -20px;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: var(--sp-dim);
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .sp-eye:hover { background: var(--sp-accent-bg); color: var(--sp-accent); }

        .sp-eye i {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            transition: opacity .25s, transform .3s var(--sp-ease);
        }

        .sp-eye .fa-eye-slash { opacity: 0; transform: scale(.6) rotate(-30deg); }
        .sp-eye.is-on .fa-eye { opacity: 0; transform: scale(.6) rotate(30deg); }
        .sp-eye.is-on .fa-eye-slash { opacity: 1; transform: none; }

        .sp-caps {
            display: block;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin: 0;
            font-size: .8rem;
            color: #fdb022;
            transition: max-height .3s var(--sp-ease), opacity .3s, margin .3s;
        }

        .sp-caps.is-on { max-height: 2rem; opacity: 1; margin: -.45rem 0 .9rem; }

        .sp-eye:focus-visible, .sp-link:focus-visible, .sp-btn:focus-visible {
            outline: 2px solid var(--sp-accent);
            outline-offset: 2px;
        }

        /* Options */
        .sp-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.4rem; flex-wrap: wrap; }
        .sp-check { position: relative; display: inline-flex; align-items: center; margin: 0; cursor: pointer; font-size: .9rem; color: var(--sp-text); user-select: none; }
        .sp-check input { position: absolute; opacity: 0; width: 1px; height: 1px; }

        .sp-check span::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: .55rem;
            vertical-align: -4px;
            border: 1.5px solid var(--sp-dim);
            border-radius: 5px;
            background: var(--sp-bg) center / 12px no-repeat;
            transition: background-color .2s, border-color .2s, transform .2s var(--sp-ease);
        }

        .sp-check:active span::before { transform: scale(.88); }

        .sp-check input:checked + span::before {
            border-color: var(--sp-accent);
            background-color: var(--sp-accent);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2.5 6.3l2.4 2.4 4.6-5' fill='none' stroke='%230f1520' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        .sp-check input:focus-visible + span::before { outline: 2px solid var(--sp-accent); outline-offset: 2px; }

        .sp-link { color: var(--sp-accent); font-size: .9rem; font-weight: 600; text-decoration: none; border-radius: 4px; }
        .sp-link:hover { color: #8dbbff; text-decoration: underline; }

        /* Bouton */
        .sp-btn {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 12px;
            font: inherit;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, #4f8cff, #2f63e0);
            box-shadow: 0 8px 24px rgba(47, 99, 224, .4);
            transition: transform .25s var(--sp-ease), box-shadow .25s, filter .25s;
        }

        .sp-btn::after {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .3), transparent);
            transform: skewX(-20deg);
            transition: left .7s var(--sp-ease);
        }

        .sp-btn:hover { transform: translateY(-2px); filter: brightness(1.08); box-shadow: 0 12px 30px rgba(47, 99, 224, .5); }
        .sp-btn:hover::after { left: 120%; }
        .sp-btn:active { transform: translateY(1px) scale(.985); }

        .sp-btn .sp-spin { display: none; width: 18px; height: 18px; border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 50%; animation: sp-rot .7s linear infinite; }
        .sp-btn.is-loading { pointer-events: none; filter: brightness(.95); }
        .sp-btn.is-loading .sp-spin { display: inline-block; }
        .sp-btn.is-loading .sp-ico-btn { display: none; }

        .sp-help { margin: 1.5rem 0 0; text-align: center; color: var(--sp-dim); font-size: .86rem; }

        /* ---------- Modal ---------- */
        #forgotPasswordModal .modal-content { border: 1px solid var(--sp-line); border-radius: 18px; background: var(--sp-bg-soft); box-shadow: 0 24px 60px rgba(0, 0, 0, .6); color: #fff; }
        #forgotPasswordModal .modal-header { border-bottom: 0; padding: 1.5rem 1.5rem .25rem; align-items: flex-start; }
        #forgotPasswordModal .modal-title { font-size: 1.15rem; font-weight: 700; }
        #forgotPasswordModal .modal-title i { color: var(--sp-accent); margin-right: .4rem; }
        #forgotPasswordModal .modal-body { padding: .5rem 1.5rem 1.75rem; }
        #forgotPasswordModal .modal-body p { color: var(--sp-text); font-size: .92rem; margin-bottom: 1.25rem; }
        #forgotPasswordModal .close { color: #fff; opacity: .6; text-shadow: none; }
        #forgotPasswordModal .close:hover { opacity: 1; }
        #forgotPasswordModal.fade .modal-dialog { transform: scale(.92) translateY(24px); transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        #forgotPasswordModal.show .modal-dialog { transform: none; }

        /* ---------- Keyframes ---------- */
        @keyframes sp-drift { to { transform: translate3d(50px, 40px, 0) scale(1.15); } }
        @keyframes sp-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes sp-slide { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: none; } }
        @keyframes sp-spot { 0%, 14%, 100% { opacity: 0; } 4%, 10% { opacity: 1; } }
        @keyframes sp-spin { to { --sp-angle: 360deg; } }
        @keyframes sp-rot { to { transform: rotate(360deg); } }
        @keyframes sp-shake { 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }

        @media (prefers-reduced-motion: reduce) {
            .sp-login *, .sp-login *::before, .sp-login *::after { animation: none; transition: none; }
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1199.98px) {
            .sp-feats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sp-feat:nth-child(n+7) { display: none; }
        }

        @media (max-width: 991.98px) {
            .sp-shell { grid-template-columns: minmax(0, .9fr) minmax(0, 1fr); }
            .sp-feat span, .sp-roles { display: none; }
            .sp-feat:nth-child(n+5) { display: none; }
            .sp-feats { grid-template-columns: minmax(0, 1fr); }
            .sp-feat { display: flex; align-items: center; gap: .7rem; padding: .7rem .85rem; }
            .sp-feat i { margin: 0; }
        }

        @media (max-width: 767.98px) {
            .sp-shell { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto 1fr; }
            .sp-aside { padding: 1.25rem; gap: .6rem; }
            .sp-pitch h2 { font-size: 1.2rem; margin: 0; }
            .sp-pitch p, .sp-feats { display: none; }
            .sp-main { align-items: flex-start; padding-top: 1.5rem; border-left: 0; }
            .sp-card { padding: 1.5rem 1.25rem; }
            .sp-card-logo { display: none; }
        }
    </style>
</head>

<body class="sp-login">

    <div class="sp-shell">

        {{-- ================= PRÉSENTATION ================= --}}
        <aside class="sp-aside" id="spAside" aria-label="Fonctionnalités de SchoolPlus">
            <span class="sp-grid" aria-hidden="true"></span>

            <div class="sp-brand">
                <img src="{{ asset('dist/img/logo.png') }}" alt="">
                <span>SchoolPlus</span>
            </div>

            <div class="sp-pitch">
                <h2>
                    <span class="sp-w" style="--i:0">Toute</span>
                    <span class="sp-w" style="--i:1">votre</span>
                    <span class="sp-w" style="--i:2">école,</span>
                    <span class="sp-w" style="--i:3">une</span>
                    <span class="sp-w" style="--i:4">seule</span>
                    <span class="sp-w" style="--i:5">plateforme.</span>
                </h2>
                <p>De l'inscription des élèves aux bulletins, des paiements à la messagerie, SchoolPlus réunit la gestion complète de votre établissement.</p>
            </div>

            <ul class="sp-feats">
                <li class="sp-feat" style="--i:0">
                    <i class="fas fa-user-graduate" aria-hidden="true"></i>
                    <div><b>Élèves</b><span>Inscriptions, dossiers, élèves par classe et par année</span></div>
                </li>
                <li class="sp-feat" style="--i:1">
                    <i class="fas fa-school" aria-hidden="true"></i>
                    <div><b>Classes</b><span>Classes, enseignants affectés, emplois du temps</span></div>
                </li>
                <li class="sp-feat" style="--i:2">
                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                    <div><b>Notes &amp; bulletins</b><span>Découpage, évaluations, épreuves et bulletins</span></div>
                </li>
                <li class="sp-feat" style="--i:3">
                    <i class="fas fa-money-bill-wave" aria-hidden="true"></i>
                    <div><b>Scolarité</b><span>Frais, paiements et état des règlements</span></div>
                </li>
                <li class="sp-feat" style="--i:4">
                    <i class="fas fa-coins" aria-hidden="true"></i>
                    <div><b>Comptabilité &amp; bourses</b><span>Rémunérations, dépenses, bourses, rapports</span></div>
                </li>
                <li class="sp-feat" style="--i:5">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    <div><b>Personnel</b><span>Enseignants, présences et équipe administrative</span></div>
                </li>
                <li class="sp-feat" style="--i:6">
                    <i class="fas fa-child" aria-hidden="true"></i>
                    <div><b>Espace parent</b><span>Enfants, bulletins, paiements et services</span></div>
                </li>
                <li class="sp-feat" style="--i:7">
                    <i class="fas fa-comments" aria-hidden="true"></i>
                    <div><b>Messagerie</b><span>Échanges entre tous les profils</span></div>
                </li>
                <li class="sp-feat" style="--i:8">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <div><b>Sécurité</b><span>Journal d'activité et sauvegardes</span></div>
                </li>
            </ul>

            <div class="sp-roles">
                <span>Un espace pour chaque profil :</span>
                <em>Direction</em><em>Secrétariat</em><em>Comptabilité</em><em>Enseignants</em><em>Parents</em>
            </div>
        </aside>

        {{-- ================= CONNEXION ================= --}}
        <main class="sp-main">

            <div class="sp-card sp-stagger {{ session('danger') || $errors->any() ? 'sp-shake' : '' }}">

                <img class="sp-card-logo" src="{{ asset('dist/img/logo.png') }}" alt="Logo SchoolPlus" style="--i:0">

                <div style="--i:1">
                    <h1 class="sp-title">Bienvenue sur SchoolPlus</h1>
                    <p class="sp-sub">Connectez-vous pour accéder à votre espace.</p>
                </div>

                {{-- Messages de session / validation (existants) --}}
                <div style="--i:2">
                    @if (session('danger'))
                        <div class="sp-alert sp-alert--danger" role="alert">
                            <i class="fas fa-exclamation-triangle mt-1" aria-hidden="true"></i>
                            <div>{{ session('danger') }}</div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="sp-alert sp-alert--success" role="status">
                            <i class="fas fa-check-circle mt-1" aria-hidden="true"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="sp-alert sp-alert--danger" role="alert">
                            <i class="fas fa-exclamation-circle mt-1" aria-hidden="true"></i>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- FORMULAIRE (action, méthode, name inchangés) --}}
                <form method="POST" action="{{ route('verifylogins') }}" id="loginForm" style="--i:3">
                    @csrf

                    <div class="sp-field">
                        <input type="text" name="email" id="email" class="sp-input" placeholder=" "
                            value="{{ old(
                                'email',
                                Auth::viaRemember() ? Auth::user()->email ?? (Auth::user()->username ?? Auth::user()->matricule) : '',
                            ) }}"
                            autocomplete="username" required autofocus>
                        <label for="email" class="sp-label">Email / Username / Matricule</label>
                        <i class="fas fa-user sp-ico" aria-hidden="true"></i>
                    </div>

                    <div class="sp-field">
                        <input type="password" name="password" id="password" class="sp-input sp-input--pw"
                            placeholder=" " autocomplete="current-password" required>
                        <label for="password" class="sp-label">Mot de passe</label>
                        <i class="fas fa-lock sp-ico" aria-hidden="true"></i>
                        <button type="button" class="sp-eye" id="togglePassword"
                            aria-label="Afficher le mot de passe" aria-controls="password">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            <i class="fas fa-eye-slash" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span class="sp-caps" id="capsHint" role="status">
                        <i class="fas fa-arrow-up" aria-hidden="true"></i> Verr. Maj est activé
                    </span>

                    <div class="sp-row">
                        <label class="sp-check" for="remember">
                            <input type="checkbox" name="remember" id="remember"
                                {{ old('remember') || Auth::viaRemember() ? 'checked' : '' }}>
                            <span>Se souvenir de moi</span>
                        </label>

                        <a href="#" class="sp-link" data-toggle="modal" data-target="#forgotPasswordModal">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="sp-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt sp-ico-btn" aria-hidden="true"></i>
                        <span class="sp-spin" aria-hidden="true"></span>
                        <span id="loginBtnText">Se connecter</span>
                    </button>
                </form>

                <p class="sp-help" style="--i:4">Pas encore de compte ? Parlez à l'administration.</p>
            </div>
        </main>
    </div>

    {{-- ================= MODAL MOT DE PASSE OUBLIÉ ================= --}}
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog"
        aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-unlock-alt" aria-hidden="true"></i>
                        Réinitialisation du mot de passe
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p>
                        Entrez votre email, username ou matricule.
                        Un lien de réinitialisation sera envoyé
                        à l'adresse email associée à votre compte.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="sp-field">
                            <input type="text" name="email" id="resetEmail" class="sp-input" placeholder=" "
                                value="{{ old('email') }}" autocomplete="username" required>
                            <label for="resetEmail" class="sp-label">Email / Username / Matricule</label>
                            <i class="fas fa-user-lock sp-ico" aria-hidden="true"></i>
                        </div>

                        <button type="submit" class="sp-btn">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            Envoyer le lien
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT : jQuery AVANT Bootstrap 4 --}}
    <script src="{{ asset('dist/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            // Modal (comportement existant)
            $('[data-toggle="modal"]').on('click', function(e) {
                e.preventDefault();
                $('#forgotPasswordModal').modal('show');
            });

            $('#forgotPasswordModal').on('hidden.bs.modal', function() {
                $('#resetEmail').val('');
            });

            // Afficher / masquer le mot de passe
            $('#togglePassword').on('click', function() {
                var show = $('#password').attr('type') === 'password';
                $('#password').attr('type', show ? 'text' : 'password');
                $(this).toggleClass('is-on', show)
                    .attr('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            });

            // Avertissement Verr. Maj
            $('#password').on('keydown keyup', function(e) {
                var on = e.originalEvent.getModifierState && e.originalEvent.getModifierState('CapsLock');
                $('#capsHint').toggleClass('is-on', !!on);
            }).on('blur', function() {
                $('#capsHint').removeClass('is-on');
            });

            // État de chargement : la soumission n'est jamais bloquée
            $('#loginForm').on('submit', function() {
                $('#loginBtn').addClass('is-loading').attr('aria-busy', 'true');
                $('#loginBtnText').text('Connexion…');
            });

            // Retour arrière (bfcache) : on réactive le bouton
            $(window).on('pageshow', function() {
                $('#loginBtn').removeClass('is-loading').removeAttr('aria-busy');
                $('#loginBtnText').text('Se connecter');
            });

            // Halo qui suit la souris (souris uniquement, hors reduced-motion)
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches &&
                !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var aside = document.getElementById('spAside');
                aside.addEventListener('mousemove', function(e) {
                    var r = aside.getBoundingClientRect();
                    aside.style.setProperty('--x', (e.clientX - r.left) + 'px');
                    aside.style.setProperty('--y', (e.clientY - r.top) + 'px');
                });
            }
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('SchoolPlus Service Worker enregistré :', registration.scope);
                    })
                    .catch(function(error) {
                        console.error('Erreur Service Worker SchoolPlus :', error);
                    });
            });
        }
    </script>
</body>

</html>hover) and (pointer: fine)').matches &&
                !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var aside = document.getElementById('spAside');
                aside.addEventListener('mousemove', function(e) {
                    var r = aside.getBoundingClientRect();
                    aside.style.setProperty('--mx', ((e.clientX - r.left) / r.width - .5).toFixed(3));
                    aside.style.setProperty('--my', ((e.clientY - r.top) / r.height - .5).toFixed(3));
                });
            }
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('SchoolPlus Service Worker enregistré :', registration.scope);
                    })
                    .catch(function(error) {
                        console.error('Erreur Service Worker SchoolPlus :', error);
                    });
            });
        }
    </script>
</body>

</html>