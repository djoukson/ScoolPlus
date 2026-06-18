@php
    use App\Models\AnneesScolaire;

    $user = auth()->user();
    $anneeActive = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    $menuItems = [
        // Dashboard
        [
            'label' => 'Dashboard',
            'icon'  => '📊',
            'route' => 'dashboard',
            'roles' => ['admin','directeur','secretaire','professeur','comptable']
        ],

        // Élèves
        [
            'label' => 'Élèves',
            'icon'  => '👨‍🎓',
            'roles' => ['admin','directeur','secretaire'],
            'submenus' => [
                ['label' => 'Nouvel élève', 'route' => 'eleves.index'],
                ['label' => 'Élèves par année', 'route' => 'elevesparannee.index', 'badge' => $anneeActive ? $anneeActive->nom : 'Aucune année'],
                ['label' => 'Élèves / Classe', 'route' => 'listeclasses.index', 'badge' => $anneeActive ? $anneeActive->nom : 'Aucune année'],
            ]
        ],

        // Enseignants
        [
            'label' => 'Enseignants',
            'icon'  => '👨‍🏫',
            'roles' => ['admin','directeur','secretaire','professeur'],
            'submenus' => [
                ['label' => 'Ajouter un enseignant', 'route' => 'enseignants.index'],
                ['label' => 'Enseignants / Classe', 'route' => 'enseignantclasse.index'],
                ['label' => 'Mon emploi du temps', 'route' => 'monemplois.index'],
            ]
        ],

        // Classes
        [
            'label' => 'Classes',
            'icon'  => '📘',
            'roles' => ['admin','directeur','secretaire'],
            'submenus' => [
                ['label' => 'Nouvelle classe', 'route' => 'classes.index'],
                ['label' => 'Emploi du temps', 'route' => 'emplois.index'],
            ]
        ],

        // Matières
        [
            'label' => 'Matières',
            'icon'  => '📚',
            'roles' => ['admin','directeur','secretaire','professeur'],
            'submenus' => [
                ['label' => 'Nouvelle matière', 'route' => 'matieres.index'],
                ['label' => 'Matières par classe', 'route' => 'matieres.matiereparclasse'],
                ['label' => 'Mes matières', 'route' => 'matieres.mesmatieres', 'roles' => ['professeur']],
            ]
        ],

        // Bourses
        [
            'label' => 'Bourses',
            'icon'  => '🎓',
            'roles' => ['admin','directeur','comptable'],
            'submenus' => [
                ['label' => 'Types de bourses', 'route' => 'bourses.index'],
                ['label' => 'Attributions', 'route' => 'attributions.index'],
            ]
        ],

        // Scolarité / Frais
        [
            'label' => 'Scolarité',
            'icon'  => '💰',
            'roles' => ['admin','directeur','comptable','secretaire'],
            'submenus' => [
                ['label' => 'Type de Frais', 'route' => 'frais.index'],
                ['label' => 'Montants des Frais', 'route' => 'montants_frais.index'],
                ['label' => 'Paiement de Frais', 'route' => 'paiements.index'],
                ['label' => 'État des Paiements', 'route' => 'etatscolaritess'],
            ]
        ],

        // Services
        [
            'label' => 'Services',
            'icon'  => '🛎',
            'roles' => ['admin','directeur','comptable','secretaire'],
            'submenus' => [
                ['label' => 'Liste / Nouveau service', 'route' => 'services.index'],
                ['label' => 'Souscription service', 'route' => 'services.souscriptions'],
                ['label' => 'État des souscriptions', 'route' => 'services.paiementsouscriptions'],
            ]
        ],

        // Notes
        [
            'label' => 'Notes',
            'icon'  => '📝',
            'roles' => ['admin','directeur','secretaire','professeur'],
            'submenus' => [
                ['label' => 'Découpage', 'route' => 'decoupages.index'],
                ['label' => 'Types d’évaluations', 'route' => 'types_evaluations.index'],
                ['label' => 'Évaluations', 'route' => 'evaluations.index'],
            ]
        ],

        // Utilisateurs (admin/directeur uniquement)
        [
            'label' => 'Utilisateurs',
            'icon'  => '👤',
            'route' => 'users',
            'roles' => ['admin','directeur']
        ],

        // User Logs
        [
            'label' => 'User Logs',
            'icon'  => '<i class="fas fa-clipboard-list"></i>',
            'route' => 'userlogs',
            'roles' => ['admin','directeur','secretaire','comptable']
        ],

        // Sauvegardes
        [
            'label' => 'Sauvegardes',
            'icon'  => '<i class="fas fa-database"></i>',
            'route' => 'backup.index',
            'roles' => ['admin','directeur']
        ],

        // Paramètres (toujours visible)
        [
            'label' => 'Paramètres',
            'icon'  => '⚙️',
            'route' => 'settings.index',
            'roles' => ['admin','directeur','secretaire','professeur','comptable']
        ],
    ];
@endphp

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    @foreach($menuItems as $item)
        @php
            $show = in_array($user->role, $item['roles']);
        @endphp
        @if($show)
            <li class="nav-item {{ isset($item['submenus']) ? 'has-treeview' : '' }} {{ isset($item['route']) && request()->is($item['route'].'*') ? 'menu-open' : '' }}">
                <a href="{{ isset($item['route']) && $item['route'] ? route($item['route'], $user->id ?? null) : '#' }}" class="nav-link {{ isset($item['route']) && request()->routeIs($item['route']) ? 'active' : '' }}">
                    {!! $item['icon'] !!}
                    <p>
                        {{ $item['label'] }}
                        @if(isset($item['submenus'])) <i class="right fas fa-angle-left"></i> @endif
                    </p>
                    @if(isset($item['badge']))
                        <span class="badge badge-pill badge-primary ml-2">{{ $item['badge'] }}</span>
                    @endif
                </a>


                @if(isset($item['submenus']))
                    <ul class="nav nav-treeview custom-submenu">
                        @foreach($item['submenus'] as $submenu)
                            @php
                                $showSub = !isset($submenu['roles']) || in_array($user->role, $submenu['roles']);
                            @endphp
                            @if($showSub)
                                <li class="nav-item">
                                    <a href="{{ route($submenu['route']) }}" class="nav-link {{ request()->routeIs($submenu['route']) ? 'active' : '' }}">
                                        {!! $submenu['icon'] ?? '' !!}
                                        <p>{{ $submenu['label'] }}</p>
                                        @if(isset($submenu['badge']))
                                            <span class="badge badge-pill badge-primary ml-2">{{ $submenu['badge'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </li>
        @endif
    @endforeach
</ul>

<style>
    /* 🌌 --- MODERN SIDEBAR DESIGN --- */

    /* Structure générale du menu */
    .nav-sidebar {
        background: linear-gradient(180deg, #1f1f24 0%, #2a2a31 100%);
        color: #d1d5db;
        font-family: 'Inter', 'Poppins', sans-serif;
        font-size: 14px;
        letter-spacing: 0.3px;
        padding-top: 10px;
    }

    /* Liens principaux */
    .nav-sidebar .nav-item > .nav-link {
        color: #d1d5db;
        background: transparent;
        border-radius: 10px;
        margin: 4px 8px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    /* Icônes dans le menu */
    .nav-sidebar .nav-item > .nav-link i,
    .nav-sidebar .nav-item > .nav-link p {
        font-size: 14px;
    }

    /* État actif */
    .nav-sidebar .nav-item > .nav-link.active {
        background: linear-gradient(90deg, #007bff, #00aaff);
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        font-weight: 600;
    }

    /* Effet au survol */
    .nav-sidebar .nav-item > .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.08);
        transform: translateX(3px);
        color: #fff;
    }

    /* Sous-menus stylés */
    .nav-sidebar .nav-treeview.custom-submenu {
        background: rgba(255, 255, 255, 0.05);
        margin-left: 12px;
        border-left: 2px solid #00aaff;
        border-radius: 8px;
        padding: 5px 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    /* Liens internes des sous-menus */
    .nav-sidebar .nav-treeview .nav-link {
        background: transparent;
        color: #cbd5e1;
        padding: 8px 20px;
        margin: 2px 10px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    /* Hover sur sous-menus */
    .nav-sidebar .nav-treeview .nav-link:hover {
        background-color: rgba(0, 123, 255, 0.15);
        color: #fff;
        transform: translateX(5px);
    }

    /* Sous-menu actif */
    .nav-sidebar .nav-treeview .nav-link.active {
        background-color: rgba(0, 123, 255, 0.25);
        color: #fff;
        font-weight: 600;
    }

    /* Indicateur de menu ouvert */
    .nav-sidebar .menu-open > .nav-link {
        background: rgba(0, 123, 255, 0.15);
        color: #fff;
        font-weight: 600;
    }

    /* Petits badges d’année scolaire */
    .badge-primary {
        background: linear-gradient(90deg, #00aaff, #007bff);
        color: #fff;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Amélioration des transitions */
    .nav-item, .nav-link, .nav-treeview {
        transition: all 0.25s ease-in-out;
    }

    /* Optionnel : ajout d’un effet d’ombre sur tout le menu */
    .main-sidebar {
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.25);
    }

    /* Optionnel : style scrollbar du menu */
    .nav-sidebar::-webkit-scrollbar {
        width: 6px;
    }
    .nav-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }


</style>
