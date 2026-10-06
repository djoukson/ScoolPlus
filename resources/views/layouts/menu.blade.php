@php
    /* ------------------------------------------------------------------
     | Contexte
     ------------------------------------------------------------------ */
    $anneeActive = session('annee_id')
        ? \App\Models\AnneesScolaire::find(session('annee_id'))
        : \App\Models\AnneesScolaire::where('active', 1)->first();

    $user     = auth()->user();
    $role     = $user->role;
    $anneeNom = $anneeActive->nom ?? 'Aucune année';

    // Conversations non lues (messagerie)
    $unread = 0;
    if (auth()->check()) {
        $unread = \App\Models\ConversationParticipant::where('user_id', auth()->id())
            ->whereNotNull('last_read_at')
            ->whereHas('conversation.messages', function ($q) {
                $q->where('sender_id', '!=', auth()->id())
                  ->whereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->count();
    }

    /* ------------------------------------------------------------------
     | Groupes de rôles
     ------------------------------------------------------------------ */
    $ALL      = ['admin', 'directeur', 'secretaire', 'comptable', 'professeur', 'parent'];
    $ADM      = ['admin', 'directeur'];
    $ADM_SEC  = ['admin', 'directeur', 'secretaire'];
    $ADM_COMPTA = ['admin', 'directeur', 'comptable'];

    /* ------------------------------------------------------------------
     | Menu : section => [items]
     | - href     : closure (évaluée uniquement si l'item est visible)
     | - is       : patterns d'URL      (request()->is)
     | - routes   : patterns de routes  (request()->routeIs)
     | - children : sous-menu
     ------------------------------------------------------------------ */
    $menu = [

        'Principal' => [
            ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'roles' => $ALL,
             'href' => fn () => url('/'), 'is' => ['/']],

            ['label' => 'Années scolaires', 'icon' => 'fa-calendar-alt', 'roles' => $ADM,
             'href' => fn () => url('annees-scolaires'), 'is' => ['annees-scolaires*']],
        ],

        'Académique' => [
            ['label' => 'Élèves', 'icon' => 'fa-user-graduate', 'roles' => $ADM_SEC,
             'is' => ['eleves*', 'elevesparannee*', 'listeclasses*', 'scolarite*', 'notes*'],
             'children' => [
                 ['label' => 'Nouvel élève', 'href' => fn () => route('eleves.index'), 'routes' => ['eleves.index']],
                 ['label' => 'Élèves de l’année', 'tag' => $anneeNom, 'href' => fn () => route('elevesparannee.index'), 'routes' => ['elevesparannee.index']],
                 ['label' => 'Élèves par classe', 'tag' => $anneeNom, 'href' => fn () => route('listeclasses.index'), 'routes' => ['listeclasses.index']],
             ]],

            ['label' => 'Classes', 'icon' => 'fa-school', 'roles' => $ADM_SEC,
             'is' => ['classes*', 'emplois*', 'enseignantclasse*'],
             'children' => [
                 ['label' => 'Nouvelle classe', 'href' => fn () => route('classes.index'), 'routes' => ['classes.index']],
                 ['label' => 'Classes / Enseignants', 'href' => fn () => route('enseignantclasse.index'), 'routes' => ['enseignantclasse.index']],
                 ['label' => 'Emploi du temps', 'href' => fn () => route('emplois.index'), 'routes' => ['emplois.index']],
             ]],

            ['label' => 'Matières', 'icon' => 'fa-book-open', 'roles' => $ADM_SEC,
             'routes' => ['taux_horaires.*', 'matieres.*', 'matieres-par-classe*'],
             'children' => [
                 ['label' => 'Nouvelle matière', 'href' => fn () => route('matieres.index'), 'routes' => ['matieres.index']],
             ['label' => 'Taux horaires', 'roles' => $ADM, 'href' => fn () => route('taux_horaires.index'), 'routes' => ['taux_horaires.index']],
             ]],

            ['label' => 'Notes & évaluations', 'icon' => 'fa-pencil-alt', 'roles' => $ADM_SEC,
             'is' => ['decoupages*', 'note*', 'evaluations*', 'types-evaluations*'], 'routes' => ['bulletins.index'],
             'children' => [
                 ['label' => 'Découpage', 'href' => fn () => route('decoupages.index'), 'routes' => ['decoupages.index']],
                 ['label' => 'Types d’évaluations', 'href' => fn () => route('types_evaluations.index'), 'routes' => ['types_evaluations.index']],
                 ['label' => 'Évaluations', 'href' => fn () => route('evaluations.index'),
                  'routes' => ['evaluations.index', 'evaluations.createByClasse', 'bulletins.index']],
             ]],

            ['label' => 'Épreuves', 'icon' => 'fa-file-signature', 'roles' => [...$ADM_SEC, 'professeur'],
             'href' => fn () => route('epreuves.index'), 'routes' => ['epreuves.*']],

            // Professeur
            ['label' => 'Évaluations', 'icon' => 'fa-bullseye', 'roles' => ['professeur'],
             'href' => fn () => route('evaluations.index'), 'routes' => ['evaluations.index', 'evaluations.createByClasse']],

            ['label' => 'Mon emploi du temps', 'icon' => 'fa-clock', 'roles' => ['professeur'],
             'href' => fn () => route('monemplois.index'), 'routes' => ['monemplois.index']],

            ['label' => 'Mes matières', 'icon' => 'fa-chalkboard-teacher', 'roles' => ['professeur'],
             'href' => fn () => route('matieres.mesmatieres'), 'routes' => ['matieres.mesmatieres']],
        ],

        // ⚠️ Noms de routes à adapter : 'needs' masque l'item tant que la route n'existe pas
        'Espace parent' => [
            ['label' => 'Mes enfants', 'icon' => 'fa-child', 'roles' => ['parent'], 'needs' => 'parent.enfants',
             'href' => fn () => route('parent.enfants'), 'routes' => ['parent.enfants*']],

            ['label' => 'Notes & bulletins', 'icon' => 'fa-file-alt', 'roles' => ['parent'], 'needs' => 'parent.bulletins',
             'href' => fn () => route('parent.bulletins'), 'routes' => ['parent.bulletins*']],

            ['label' => 'Emploi du temps', 'icon' => 'fa-clock', 'roles' => ['parent'], 'needs' => 'parent.emplois',
             'href' => fn () => route('parent.emplois'), 'routes' => ['parent.emplois*']],

            ['label' => 'Paiements & scolarité', 'icon' => 'fa-credit-card', 'roles' => ['parent'], 'needs' => 'parent.paiements',
             'href' => fn () => route('parent.paiements'), 'routes' => ['parent.paiements*']],

            ['label' => 'Services', 'icon' => 'fa-concierge-bell', 'roles' => ['parent'], 'needs' => 'parent.services',
             'href' => fn () => route('parent.services'), 'routes' => ['parent.services*']],
        ],

        'Ressources humaines' => [
            ['label' => 'Personnel', 'icon' => 'fa-users', 'roles' => $ADM_SEC,
             'is' => ['enseignants*', 'enseignants-classes*', 'monemplois*', 'personnel*', 'presences-enseignants*'],
             'children' => [
                 ['label' => 'Enseignants', 'href' => fn () => route('enseignants.index'), 'routes' => ['enseignants.index']],
                 ['label' => 'Absences / Présences', 'href' => fn () => route('presences-enseignants.index'), 'routes' => ['presences-enseignants.*']],
                 ['label' => 'Personnel administratif', 'href' => fn () => route('personnel.index'), 'routes' => ['personnel.index']],
             ]],
        ],

        'Finances' => [
            ['label' => 'Bourses', 'icon' => 'fa-graduation-cap', 'roles' => [...$ADM_SEC, 'comptable'],
             'is' => ['bourses*', 'attributions*'],
             'children' => [
                 ['label' => 'Types de bourses', 'href' => fn () => route('bourses.index'), 'routes' => ['bourses.index']],
                 ['label' => 'Attributions', 'href' => fn () => route('attributions.index'), 'routes' => ['attributions.index']],
             ]],

            ['label' => 'Comptabilité', 'icon' => 'fa-coins', 'roles' => $ADM_COMPTA,
             'routes' => ['salaires.*', 'depenses.*', 'etats.*'],
             'children' => [
                 ['label' => 'Rémunérations', 'href' => fn () => route('salaires.index'), 'routes' => ['salaires.index']],
                 ['label' => 'Dépenses', 'href' => fn () => route('depenses.index'), 'routes' => ['depenses.index']],
                 ['label' => 'États & rapports', 'href' => fn () => route('etats.index'), 'routes' => ['etats.index']],
             ]],

            ['label' => 'Scolarité', 'icon' => 'fa-money-bill-wave', 'roles' => $ADM_COMPTA,
             'is' => ['frais*', 'paiements*', 'etatscolaritess*', 'montants-frais*'],
             'children' => [
                 ['label' => 'Types de frais', 'href' => fn () => route('frais.index'), 'routes' => ['frais.index']],
                 ['label' => 'Montants des frais', 'href' => fn () => route('montants_frais.index'), 'routes' => ['montants_frais.index']],
                 ['label' => 'Paiement de frais', 'href' => fn () => route('paiements.index'), 'routes' => ['paiements.index']],
                 ['label' => 'État des paiements', 'href' => fn () => route('etatscolaritess'), 'routes' => ['etatscolaritess']],
             ]],

            ['label' => 'Services', 'icon' => 'fa-concierge-bell', 'roles' => $ADM_COMPTA,
             'is' => ['services*'],
             'children' => [
                 ['label' => 'Liste / Nouveau service', 'href' => fn () => route('services.index'), 'routes' => ['services.index']],
                 ['label' => 'Souscriptions', 'href' => fn () => route('services.souscriptions'), 'routes' => ['services.souscriptions']],
                 ['label' => 'Paiements', 'href' => fn () => route('services.paiementsouscriptions'), 'routes' => ['services.paiementsouscriptions']],
             ]],
        ],

        'Administration' => [
            ['label' => 'Utilisateurs', 'icon' => 'fa-user', 'roles' => $ADM,
             'href' => fn () => route('users'), 'is' => ['users*']],

            ['label' => 'Comptes parents', 'icon' => 'fa-user-shield', 'roles' => $ADM,
             'href' => fn () => route('parents.index'), 'routes' => ['parents.*']],

            ['label' => 'Journal d’activité', 'icon' => 'fa-clipboard-list', 'roles' => $ADM,
             'href' => fn () => route('userlogs'),
             'routes' => ['userlogs', 'userlogs.*']],

            ['label' => 'Sauvegardes', 'icon' => 'fa-database', 'roles' => $ADM,
             'href' => fn () => route('backup.index'), 'routes' => ['backup.*']],
        ],

        'Outils' => [
            ['label' => 'Messagerie', 'icon' => 'fa-comments', 'roles' => $ALL,
             'href' => fn () => route('messages.index'), 'routes' => ['messages.*'], 'count' => $unread],

            ['label' => 'Paramètres', 'icon' => 'fa-gear', 'roles' => $ALL,
             'href' => fn () => route('settings.index'), 'routes' => ['settings.*']],
        ],
    ];

    /* ------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------ */
    $isActive = fn (array $i) => request()->routeIs(...($i['routes'] ?? []))
                              || request()->is(...($i['is'] ?? []));

    $visible = collect($menu)
        ->map(fn ($items) => collect($items)
            ->filter(fn ($i) => in_array($role, $i['roles'])
                && (empty($i['needs']) || \Illuminate\Support\Facades\Route::has($i['needs'])))
            ->values())
        ->filter(fn ($items) => $items->isNotEmpty());
@endphp

<ul class="nav nav-pills nav-sidebar flex-column sidebar-pro" data-widget="treeview" role="menu" data-accordion="false">

    {{-- Année scolaire en cours --}}
    <li class="sp-year" title="Année scolaire active">
        <span class="sp-year__icon"><i class="fas fa-calendar-check"></i></span>
        <span class="sp-year__text">
            <small>Année scolaire</small>
            <strong>{{ $anneeNom }}</strong>
        </span>
    </li>

    @foreach($visible as $section => $items)
        <li class="nav-header sp-section">{{ $section }}</li>

        @foreach($items as $item)
            @php
                $children = $item['children'] ?? null;
                $open = $children
                    ? ($isActive($item) || collect($children)->contains(fn ($c) => $isActive($c)))
                    : $isActive($item);
            @endphp

            @if($children)
                <li class="nav-item has-treeview {{ $open ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                        <i class="nav-icon fas {{ $item['icon'] }}"></i>
                        <p>
                            {{ $item['label'] }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-submenu">
                        @foreach(collect($children)->filter(fn ($child) => empty($child['roles']) || in_array($role, $child['roles'], true)) as $child)
                            <li class="nav-item">
                                <a href="{{ $child['href']() }}" class="nav-link {{ $isActive($child) ? 'active' : '' }}">
                                    <span class="sp-dot"></span>
                                    <p>
                                        {{ $child['label'] }}
                                        @if(!empty($child['tag']))
                                            <span class="badge badge-year">{{ $child['tag'] }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ $item['href']() }}" class="nav-link {{ $open ? 'active' : '' }}">
                        <i class="nav-icon fas {{ $item['icon'] }}"></i>
                        <p>
                            {{ $item['label'] }}
                            @if(($item['count'] ?? 0) > 0)
                                <span class="right badge badge-counter">{{ $item['count'] }}</span>
                            @endif
                        </p>
                    </a>
                </li>
            @endif
        @endforeach
    @endforeach
</ul>

<style>
    /* ==================================================================
       SIDEBAR PRO — jetons de design
       ================================================================== */
    .main-sidebar {
        --sp-bg:        #0f1520;
        --sp-bg-soft:   #151c2b;
        --sp-text:      #a9b4c6;
        --sp-text-dim:  #6b778c;
        --sp-hover:     rgba(255, 255, 255, .05);
        --sp-accent:    #5b9dff;
        --sp-accent-bg: rgba(91, 157, 255, .13);
        --sp-danger:    #ff6b6b;
        --sp-line:      rgba(255, 255, 255, .07);
        --sp-radius:    9px;
        --sp-width:     270px;

        background: var(--sp-bg) !important;
        border-right: 1px solid var(--sp-line);
        box-shadow: none;
    }

    body:not(.sidebar-collapse) .main-sidebar,
    body.sidebar-collapse .main-sidebar:hover { width: var(--sp-width); }

    @media (min-width: 992px) {
        body:not(.sidebar-collapse):not(.layout-top-nav) .content-wrapper,
        body:not(.sidebar-collapse):not(.layout-top-nav) .main-footer,
        body:not(.sidebar-collapse):not(.layout-top-nav) .main-header { margin-left: 280px; }
    }

    /* ==================================================================
       Structure
       ================================================================== */
    .sidebar-pro {
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        font-size: 13.5px;
        padding: 6px 4px 24px;
    }

    .sidebar-pro .nav-item > .nav-link p { margin: 0; }

    /* Chip année scolaire */
    .sp-year {
        display: flex; align-items: center; gap: 11px;
        margin: 8px 10px 6px; padding: 10px 12px;
        background: var(--sp-bg-soft);
        border: 1px solid var(--sp-line);
        border-radius: 11px;
        color: #fff;
    }
    .sp-year__icon {
        width: 32px; height: 32px; flex: none;
        display: grid; place-items: center;
        background: var(--sp-accent-bg); color: var(--sp-accent);
        border-radius: 8px; font-size: 14px;
    }
    .sp-year__text { display: flex; flex-direction: column; line-height: 1.25; min-width: 0; }
    .sp-year__text small  { color: var(--sp-text-dim); font-size: 11.5px; }
    .sp-year__text strong { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Titres de section */
    .sidebar-pro .nav-header.sp-section {
        padding: 18px 20px 6px;
        font-size: 11.5px; font-weight: 600;
        color: var(--sp-text-dim);
        letter-spacing: .2px;
    }

    /* ==================================================================
       Liens principaux
       ================================================================== */
    .main-sidebar .sidebar-pro > .nav-item > .nav-link {
        position: relative;
        display: flex; align-items: center;
        margin: 2px 10px; padding: 9px 12px;
        color: var(--sp-text);
        background: transparent;
        border-radius: var(--sp-radius);
        transition: background .18s ease, color .18s ease;
    }

    .main-sidebar .sidebar-pro > .nav-item > .nav-link > .nav-icon {
        width: 22px; margin-right: 10px;
        font-size: 14px; text-align: center;
        color: var(--sp-text-dim);
        transition: color .18s ease;
    }

    .main-sidebar .sidebar-pro > .nav-item > .nav-link > p {
        display: flex; align-items: center; flex: 1; min-width: 0;
        font-weight: 500;
    }

    .main-sidebar .sidebar-pro > .nav-item > .nav-link:hover {
        background: var(--sp-hover);
        color: #fff;
    }
    .main-sidebar .sidebar-pro > .nav-item > .nav-link:hover > .nav-icon { color: #fff; }

    /* Actif : fond teinté + barre d'accent */
    .main-sidebar .sidebar-pro > .nav-item > .nav-link.active {
        background: var(--sp-accent-bg);
        color: #fff;
        font-weight: 600;
        box-shadow: none;
    }
    .main-sidebar .sidebar-pro > .nav-item > .nav-link.active > .nav-icon { color: var(--sp-accent); }
    .main-sidebar .sidebar-pro > .nav-item > .nav-link.active::before {
        content: '';
        position: absolute; left: -10px; top: 8px; bottom: 8px;
        width: 3px; border-radius: 0 3px 3px 0;
        background: var(--sp-accent);
    }

    /* Chevron */
    .sidebar-pro .nav-link > p > .right {
        position: static; margin-left: auto;
        font-size: 11px; color: var(--sp-text-dim);
        transition: transform .2s ease;
    }
    .sidebar-pro .menu-open > .nav-link > p > .right { transform: rotate(-90deg); }

    /* Groupe ouvert (sans activer visuellement le parent) */
    .main-sidebar .sidebar-pro .menu-open > .nav-link:not(.active) { background: transparent; color: #fff; }

    /* ==================================================================
       Sous-menus
       ================================================================== */
    .sidebar-pro .nav-treeview.custom-submenu {
        position: relative;
        margin: 2px 10px 6px 27px;
        padding: 2px 0;
        background: transparent;
        border-left: 1px solid var(--sp-line);
        border-radius: 0;
    }

    .main-sidebar .sidebar-pro .nav-treeview > .nav-item > .nav-link {
        display: flex; align-items: center;
        margin: 1px 0 1px 8px; padding: 7px 12px;
        color: var(--sp-text);
        background: transparent;
        border-radius: 8px;
        font-size: 13px;
        transition: background .18s ease, color .18s ease;
    }
    .main-sidebar .sidebar-pro .nav-treeview > .nav-item > .nav-link > p {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .main-sidebar .sidebar-pro .nav-treeview > .nav-item > .nav-link:hover {
        background: var(--sp-hover); color: #fff;
    }
    .main-sidebar .sidebar-pro .nav-treeview > .nav-item > .nav-link.active {
        background: var(--sp-accent-bg); color: #fff; font-weight: 600;
    }

    .sp-dot {
        width: 5px; height: 5px; flex: none; margin-right: 11px;
        border-radius: 50%;
        background: var(--sp-text-dim);
        transition: background .18s ease, transform .18s ease;
    }
    .nav-treeview > .nav-item > .nav-link:hover .sp-dot { background: #fff; }
    .nav-treeview > .nav-item > .nav-link.active .sp-dot {
        background: var(--sp-accent); transform: scale(1.4);
    }

    /* ==================================================================
       Badges
       ================================================================== */
    .sidebar-pro .badge-year {
        padding: 2px 7px;
        font-size: 10.5px; font-weight: 600;
        color: var(--sp-accent);
        background: var(--sp-accent-bg);
        border-radius: 20px;
    }
    .sidebar-pro .badge-counter {
        position: static; margin-left: auto;
        min-width: 20px; padding: 3px 7px;
        font-size: 11px; font-weight: 700; line-height: 1.1;
        color: #fff; background: var(--sp-danger);
        border-radius: 20px;
    }

    /* ==================================================================
       Scrollbar & accessibilité
       ================================================================== */
    .main-sidebar .sidebar { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.12) transparent; }
    .main-sidebar .sidebar::-webkit-scrollbar { width: 6px; }
    .main-sidebar .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 10px; }

    .sidebar-pro .nav-link:focus-visible {
        outline: 2px solid var(--sp-accent);
        outline-offset: 1px;
    }

    /* Mode réduit : on masque le chip année */
    body.sidebar-collapse .main-sidebar:not(:hover) .sp-year__text,
    body.sidebar-collapse .main-sidebar:not(:hover) .sp-section { display: none; }
    body.sidebar-collapse .main-sidebar:not(:hover) .sp-year { justify-content: center; padding: 8px; margin: 8px 6px; }

    @media (prefers-reduced-motion: reduce) {
        .sidebar-pro *, .sidebar-pro *::before { transition: none !important; }
    }
</style>
