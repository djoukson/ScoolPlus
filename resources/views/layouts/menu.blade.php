@php
    use App\Models\AnneesScolaire;

    $anneeActive = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    $user = auth()->user();
@endphp

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" >

    {{-- Dashboard toujours visible --}}
    <li class="nav-item">
        <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt me-1"></i>
            <p> Dashboard</p>
        </a>
    </li>

    @if($user->role === 'admin' || $user->role === 'directeur')
        <!-- Menu complet pour admin/directeur -->
        <li class="nav-item">
            <a href="{{ url('annees-scolaires') }}"
               class="nav-link {{ request()->is('annees-scolaires*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt me-1"></i>
                <p>Années scolaires</p>
            </a>
        </li>

        <!-- Gestion des élèves -->
        <li class="nav-item has-treeview {{ request()->is('eleves*') || request()->is('elevesparannee*') || request()->is('listeclasses*') || request()->is('scolarite*') || request()->is('notes*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('eleves*') || request()->is('elevesparannee*') || request()->is('listeclasses*') || request()->is('scolarite*') || request()->is('notes*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate me-1"></i>
                <p>
                    Élèves
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('eleves.index') }}" class="nav-link {{ request()->routeIs('eleves.index') ? 'active' : '' }}">
                        <i class="fas fa-user-plus me-1"></i>
                        <p> Nouvel élève</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('elevesparannee.index') }}" class="nav-link {{ request()->routeIs('elevesparannee.index') ? 'active' : '' }}">
                        <i class="fas fa-users me-1"></i>
                        Les élèves de
                        <span class="badge badge-pill badge-primary ml-2">
                            {{ $anneeActive ? $anneeActive->nom : 'Aucune année' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('listeclasses.index') }}" class="nav-link {{ request()->routeIs('listeclasses.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-1"></i>
                        élèves / Classe
                        <span class="badge badge-pill badge-primary ml-2">
                            {{ $anneeActive ? $anneeActive->nom : 'Aucune année' }}
                        </span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Gestion des enseignants -->
        <li class="nav-item has-treeview {{ request()->is('enseignants*') || request()->is('enseignantclasse*') || request()->is('enseignants-classes*') || request()->is('monemplois*')|| request()->is('personnel*')|| request()->is('presences-enseignants*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('enseignants*') || request()->is('enseignantclasse*') || request()->is('enseignants-classes*') || request()->is('monemplois*')|| request()->is('personnel*')|| request()->is('presences-enseignants*') ? 'active' : '' }}">
                <i class="fas fa-users me-1"></i>
                <p>
                    Personnel
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('enseignants.index') }}"
                       class="nav-link {{ request()->routeIs('enseignants.index') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>Enseignants</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('presences-enseignants.index') }}"
                       class="nav-link {{ request()->routeIs('presences-enseignants.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <p>Absences / Présences</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('personnel.index') }}"
                       class="nav-link {{ request()->routeIs('personnel.index') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Personnel administratif</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('enseignantclasse.index') }}"
                       class="nav-link {{ request()->routeIs('enseignantclasse.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-1"></i>

                        <p>Enseignants / Classe</p>
                    </a>
                </li>

            </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('classes*') || request()->is('emplois*')  ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('classes*') || request()->is('emplois*')  ? 'active' : '' }}">
                <i class="fas fa-school me-1"></i>
                <p>
                    Classes
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.index') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle me-1"></i>
                        <p> Nouvelle classe</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('emplois.index')}}" class="nav-link {{ request()->routeIs('emplois.index') ? 'active' : '' }}">
                        <i class="fas fa-clock me-1"></i>
                        <p> Emploi du temps</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Gestion des matières -->
        <li class="nav-item has-treeview {{ request()->routeIs('matieres.*') ||request()->routeIs('taux_horaires.*') || request()->routeIs('matieres-par-classe*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('matieres.*') || request()->routeIs('taux_horaires.*') || request()->routeIs('matieres-par-classe*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <p>
                    Matières
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">
                {{-- Nouvelle matière --}}
                <li class="nav-item">
                    <a href="{{ route('taux_horaires.index') }}"
                       class="nav-link {{ request()->routeIs('taux_horaires.index') ? 'active' : '' }}">
                        <i class="fas fa-clock me-1"></i> <p> Taux horaires</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('matieres.index') }}"
                       class="nav-link {{ request()->routeIs('matieres.index') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <p> Nouvelle matière</p>
                    </a>
                </li>

                {{-- Matières par classe --}}
                <li class="nav-item">
                    <a href="{{ route('matieres.matiereparclasse') }}"
                       class="nav-link {{ request()->routeIs('matieres.matiereparclasse') || request()->routeIs('matieres.dansclasse') ? 'active' : '' }}">
                        <i class="fas fa-layer-group"></i>
                        <p>Matières par classe</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('bourses*') || request()->is('attributions*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('bourses*') || request()->is('attributions*') ? 'active' : '' }}">


                <p>     <i class="fas fa-graduation-cap"></i>
               Bourses
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">

                <li class="nav-item">
                    <a href="{{ route('bourses.index') }}"
                       class="nav-link {{ request()->routeIs('bourses.index') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <p>Types de bourses</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('attributions.index') }}"
                       class="nav-link {{ request()->routeIs('attributions.index') ? 'active' : '' }}">
                        <i class="fas fa-bullseye"></i>
                        <p>Attributions</p>
                    </a>
                </li>

            </ul>

        </li>

        <li class="nav-item has-treeview {{ request()->routeIs('salaires.*') || request()->routeIs('depenses.*') || request()->routeIs('etats.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('salaires.*') || request()->routeIs('depenses.*')|| request()->routeIs('etats.*')  ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                <p>
                    Finances & Comptabilité
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('salaires.index') }}"
                       class="nav-link {{ request()->routeIs('salaires.index') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Rémunérations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('depenses.index') }}"
                       class="nav-link {{ request()->routeIs('depenses.index') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <p>Dépenses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('etats.index') }}"
                       class="nav-link {{ request()->routeIs('etats.index') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <p>États & Rapports</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Gestion de la scolarité -->
        <li class="nav-item has-treeview {{ request()->is('frais*') || request()->is('paiements*') ||request()->is('etatscolaritess*') || request()->is('montants-frais*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('frais*') || request()->is('paiements*') ||request()->is('etatscolaritess*') || request()->is('montants-frais*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave me-1"></i>
                <p> Scolarité <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('frais.index') }}" class="nav-link {{ request()->routeIs('frais.index') ? 'active' : '' }}">
                        <i class="fas fa-list-alt me-1"></i>
                        <p>Type de Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('montants_frais.index') }}" class="nav-link {{ request()->routeIs('montants_frais.index') ? 'active' : '' }}">
                        <i class="fas fa-coins me-1"></i>
                        <p>Montants des Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('paiements.index') }}" class="nav-link {{ request()->routeIs('paiements.index') ? 'active' : '' }}">
                        <i class="fas fa-credit-card me-1"></i>
                        <p>Paiement de Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('etatscolaritess') }}"
                       class="nav-link {{ request()->routeIs('etatscolaritess') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice me-1"></i>
                        <p>Etat des Paiements</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item has-treeview {{ request()->is('services*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('services*') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell me-1"></i>
                <p>
                    Services
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                {{-- Nouveau service (Admin/Directeur) --}}
                <li class="nav-item">
                    <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
                        <i class="fas fa-plus me-1"></i>
                        <p>Liste / Nouveau service</p>
                    </a>
                </li>

                {{-- Souscription à un service --}}
                <li class="nav-item">
                    <a href="{{ route('services.souscriptions') }}" class="nav-link {{ request()->routeIs('services.souscriptions') ? 'active' : '' }}">
                        <i class="fas fa-pen me-1"></i>
                        <p>Souscription service</p>
                    </a>
                </li>

                {{-- État des souscriptions --}}
                <li class="nav-item">
                    <a href="{{ route('services.paiementsouscriptions') }}" class="nav-link {{ request()->routeIs('services.paiementsouscriptions') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar me-1"></i>
                        <p>Paiement</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="{{ route('epreuves.index') }}"
               class="nav-link {{ request()->routeIs('epreuves.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>Epreuves</p>
            </a>
        </li>

        <li class="nav-item has-treeview {{ request()->is('decoupages*') || request()->is('note*') || request()->is('evaluations*') || request()->is('types-evaluations*') || request()->routeIs('bulletins.index') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('decoupages*') || request()->is('note*') || request()->is('evaluations*') || request()->is('types-evaluations*') || request()->routeIs('bulletins.index') ? 'active' : '' }}">
                <i class="fas fa-pencil-alt me-1"></i>
                <p> Notes/Evaluations <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('decoupages.index') }}" class="nav-link {{ request()->routeIs('decoupages.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <p>Découpage</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('types_evaluations.index') }}" class="nav-link {{ request()->routeIs('types_evaluations.index') ? 'active' : '' }}">
                        <i class="fas fa-file-alt me-1"></i>
                        <p>Types d’évaluations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('evaluations.index') }}"
                       class="nav-link {{ request()->routeIs('evaluations.index') || request()->routeIs('evaluations.createByClasse') || request()->routeIs('bulletins.index') ? 'active' : '' }}">
                        <i class="fas fa-bullseye me-1"></i>
                        <p>Évaluations</p>
                    </a>
                </li>
            </ul>
        </li>


        <!-- Utilisateurs -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ route('users') }}">
                <i class="fas fa-user me-1"></i>
                <p> Utilisateurs</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('userlogs') }} " class="nav-link {{ request()->routeIs('userlogs') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>User Logs</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                <i class="fas fa-database"></i><p> Sauvegardes</p>
            </a>
        </li>
    @elseif($user->role === 'secretaire')
        <!-- Gestion des élèves -->
        <li class="nav-item has-treeview {{ request()->is('eleves*') || request()->is('elevesparannee*') || request()->is('listeclasses*') || request()->is('scolarite*') || request()->is('notes*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('eleves*') || request()->is('elevesparannee*') || request()->is('listeclasses*') || request()->is('scolarite*') || request()->is('notes*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate me-1"></i>
                <p>
                    Élèves
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('eleves.index') }}" class="nav-link {{ request()->routeIs('eleves.index') ? 'active' : '' }}">
                        <i class="fas fa-user-plus me-1"></i>
                        <p> Nouvel élève</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('elevesparannee.index') }}" class="nav-link {{ request()->routeIs('elevesparannee.index') ? 'active' : '' }}">
                        <i class="fas fa-users me-1"></i>
                        Les élèves de
                        <span class="badge badge-pill badge-primary ml-2">
                            {{ $anneeActive ? $anneeActive->nom : 'Aucune année' }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('listeclasses.index') }}" class="nav-link {{ request()->routeIs('listeclasses.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-1"></i>
                        élèves / Classe
                        <span class="badge badge-pill badge-primary ml-2">
                            {{ $anneeActive ? $anneeActive->nom : 'Aucune année' }}
                        </span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Gestion des enseignants -->
        <li class="nav-item has-treeview {{ request()->is('enseignants*') || request()->is('enseignantclasse*') || request()->is('enseignants-classes*') || request()->is('monemplois*')|| request()->is('personnel*')|| request()->is('presences-enseignants*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('enseignants*') || request()->is('enseignantclasse*') || request()->is('enseignants-classes*') || request()->is('monemplois*')|| request()->is('personnel*')|| request()->is('presences-enseignants*') ? 'active' : '' }}">
                <i class="fas fa-users me-1"></i>
                <p>
                    Personnel
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('enseignants.index') }}"
                       class="nav-link {{ request()->routeIs('enseignants.index') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>Enseignants</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('presences-enseignants.index') }}"
                       class="nav-link {{ request()->routeIs('presences-enseignants.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <p>Absences / Présences</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('personnel.index') }}"
                       class="nav-link {{ request()->routeIs('personnel.index') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Personnel administratif</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('enseignantclasse.index') }}"
                       class="nav-link {{ request()->routeIs('enseignantclasse.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-1"></i>

                        <p>Enseignants / Classe</p>
                    </a>
                </li>

            </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('classes*') || request()->is('emplois*')  ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('classes*') || request()->is('emplois*')  ? 'active' : '' }}">
                <i class="fas fa-school me-1"></i>
                <p>
                    Classes
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.index') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle me-1"></i>
                        <p> Nouvelle classe</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('emplois.index')}}" class="nav-link {{ request()->routeIs('emplois.index') ? 'active' : '' }}">
                        <i class="fas fa-clock me-1"></i>
                        <p> Emploi du temps</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Gestion des matières -->
        <li class="nav-item has-treeview {{ request()->routeIs('matieres.*') ||request()->routeIs('taux_horaires.*') || request()->routeIs('matieres-par-classe*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('matieres.*') || request()->routeIs('taux_horaires.*') || request()->routeIs('matieres-par-classe*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <p>
                    Matières
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">
                {{-- Nouvelle matière --}}
                <li class="nav-item">
                    <a href="{{ route('taux_horaires.index') }}"
                       class="nav-link {{ request()->routeIs('taux_horaires.index') ? 'active' : '' }}">
                        <i class="fas fa-clock me-1"></i> <p> Taux horaires</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('matieres.index') }}"
                       class="nav-link {{ request()->routeIs('matieres.index') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <p> Nouvelle matière</p>
                    </a>
                </li>

                {{-- Matières par classe --}}
                <li class="nav-item">
                    <a href="{{ route('matieres.matiereparclasse') }}"
                       class="nav-link {{ request()->routeIs('matieres.matiereparclasse') || request()->routeIs('matieres.dansclasse') ? 'active' : '' }}">
                        <i class="fas fa-layer-group"></i>
                        <p>Matières par classe</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('bourses*') || request()->is('attributions*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('bourses*') || request()->is('attributions*') ? 'active' : '' }}">


                <p>     <i class="fas fa-graduation-cap"></i>
                    Bourses
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">

                <li class="nav-item">
                    <a href="{{ route('bourses.index') }}"
                       class="nav-link {{ request()->routeIs('bourses.index') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <p>Types de bourses</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('attributions.index') }}"
                       class="nav-link {{ request()->routeIs('attributions.index') ? 'active' : '' }}">
                        <i class="fas fa-bullseye"></i>
                        <p>Attributions</p>
                    </a>
                </li>

            </ul>

        </li>
        <li class="nav-item">
            <a href="{{ route('epreuves.index') }}"
               class="nav-link {{ request()->routeIs('epreuves.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>Epreuves</p>
            </a>
        </li>
        <!-- Gestion des notes -->
        <li class="nav-item has-treeview {{ request()->is('decoupages*') || request()->is('note*') || request()->is('evaluations*') || request()->is('types-evaluations*') || request()->routeIs('bulletins.index') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('decoupages*') || request()->is('note*') || request()->is('evaluations*') || request()->is('types-evaluations*') || request()->routeIs('bulletins.index') ? 'active' : '' }}">
                <i class="fas fa-pencil-alt me-1"></i>
                <p> Notes/Evaluations <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('decoupages.index') }}" class="nav-link {{ request()->routeIs('decoupages.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <p>Découpage</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('types_evaluations.index') }}" class="nav-link {{ request()->routeIs('types_evaluations.index') ? 'active' : '' }}">
                        <i class="fas fa-file-alt me-1"></i>
                        <p>Types d’évaluations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('evaluations.index') }}"
                       class="nav-link {{ request()->routeIs('evaluations.index') || request()->routeIs('evaluations.createByClasse') || request()->routeIs('bulletins.index') ? 'active' : '' }}">
                        <i class="fas fa-bullseye me-1"></i>
                        <p>Évaluations</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('userlogs') }}"
               class="nav-link {{ request()->routeIs('userlogs.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>User Logs</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                <i class="fas fa-database"></i><p> Sauvegardes</p>
            </a>
        </li>
    @elseif($user->role === 'professeur')
        <!-- Menu pour professeur -->
        <li class="nav-item">
            <a href="{{ route('epreuves.index') }}"
               class="nav-link {{ request()->routeIs('epreuves.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>Epreuves</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('evaluations.index') }}"
               class="nav-link {{ request()->routeIs('evaluations.index') || request()->routeIs('evaluations.createByClasse') ? 'active' : '' }}">
                <i class="fas fa-bullseye me-1"></i>
                <p>Évaluations</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('monemplois.index') }}"
               class="nav-link {{ request()->routeIs('monemplois.index') ? 'active' : '' }}">
                <i class="fas fa-clock me-1"></i>
                <p>Mon emploi du temps</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{route('matieres.mesmatieres')}}"
               class="nav-link {{ request()->routeIs('matieres.mesmatieres') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <p>Mes matières</p>
            </a>
        </li>
    @elseif($user->role === 'comptable')
        <!-- Menu pour comptable -->
        <li class="nav-item has-treeview {{ request()->is('bourses*') || request()->is('attributions*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('bourses*') || request()->is('attributions*') ? 'active' : '' }}">


                <p>     <i class="fas fa-graduation-cap"></i>
                    Bourses
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">

                <li class="nav-item">
                    <a href="{{ route('bourses.index') }}"
                       class="nav-link {{ request()->routeIs('bourses.index') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <p>Types de bourses</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('attributions.index') }}"
                       class="nav-link {{ request()->routeIs('attributions.index') ? 'active' : '' }}">
                        <i class="fas fa-bullseye"></i>
                        <p>Attributions</p>
                    </a>
                </li>

            </ul>

        </li>
        <li class="nav-item has-treeview {{ request()->routeIs('salaires.*') || request()->routeIs('depenses.*') || request()->routeIs('etats.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('salaires.*') || request()->routeIs('depenses.*')|| request()->routeIs('etats.*')  ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                <p>
                    Finances & Comptabilité
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>

            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('salaires.index') }}"
                       class="nav-link {{ request()->routeIs('salaires.index') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Rémunérations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('depenses.index') }}"
                       class="nav-link {{ request()->routeIs('depenses.index') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <p>Dépenses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('etats.index') }}"
                       class="nav-link {{ request()->routeIs('etats.index') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <p>États & Rapports</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('frais*') || request()->is('paiements*') ||request()->is('etatscolaritess*') || request()->is('montants-frais*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('frais*') || request()->is('paiements*') ||request()->is('etatscolaritess*') || request()->is('montants-frais*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave me-1"></i>
                <p> Scolarité <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                <li class="nav-item">
                    <a href="{{ route('frais.index') }}" class="nav-link {{ request()->routeIs('frais.index') ? 'active' : '' }}">
                        <i class="fas fa-list-alt me-1"></i>
                        <p>Type de Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('montants_frais.index') }}" class="nav-link {{ request()->routeIs('montants_frais.index') ? 'active' : '' }}">
                        <i class="fas fa-coins me-1"></i>
                        <p>Montants des Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('paiements.index') }}" class="nav-link {{ request()->routeIs('paiements.index') ? 'active' : '' }}">
                        <i class="fas fa-credit-card me-1"></i>
                        <p>Paiement de Frais</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('etatscolaritess') }}"
                       class="nav-link {{ request()->routeIs('etatscolaritess') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice me-1"></i>
                        <p>Etat des Paiements</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item has-treeview {{ request()->is('services*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('services*') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell me-1"></i>
                <p>
                    Services
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview custom-submenu">
                {{-- Nouveau service (Admin/Directeur) --}}
                <li class="nav-item">
                    <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
                        <i class="fas fa-plus me-1"></i>
                        <p>Liste / Nouveau service</p>
                    </a>
                </li>

                {{-- Souscription à un service --}}
                <li class="nav-item">
                    <a href="{{ route('services.souscriptions') }}" class="nav-link {{ request()->routeIs('services.souscriptions') ? 'active' : '' }}">
                        <i class="fas fa-pen me-1"></i>
                        <p>Souscription service</p>
                    </a>
                </li>

                {{-- État des souscriptions --}}
                <li class="nav-item">
                    <a href="{{ route('services.paiementsouscriptions') }}" class="nav-link {{ request()->routeIs('services.paiementsouscriptions') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar me-1"></i>
                        <p>Paiement</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('userlogs', $user->id) }}"
               class="nav-link {{ request()->routeIs('userlogs.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <p>User Logs</p>
            </a>
        </li>
    @endif

    <!-- Paramètres toujours visible pour tous les rôles sauf peut-être élèves -->
    <li class="nav-item">
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="fas fa-gear"></i>
            <p> Paramètres</p>
        </a>
    </li>
    <!-- Paramètres toujours visible pour tous les rôles sauf peut-être élèves -->


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
        background: linear-gradient(90deg,
        rgba(0, 123, 255, 0.75),
        rgba(0, 170, 255, 0.55)
        );
        color: #fff !important;
        font-weight: 600;
        transform: translateX(5px);
        box-shadow:
            inset 4px 0 0 #00aaff,
            0 6px 18px rgba(0, 123, 255, 0.45);
    }


    /* Effet au survol */
    .nav-sidebar .nav-item > .nav-link:hover {
        background: linear-gradient(90deg,
        rgba(0, 123, 255, 0.35),
        rgba(0, 170, 255, 0.15)
        );
        color: #fff;
        transform: translateX(4px);
        box-shadow: inset 3px 0 0 #00aaff;
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


    /* 📐 Augmenter la largeur de la sidebar */
    .main-sidebar {
        width: 290px; /* valeur par défaut ≈ 250px */
    }

    /* Ajuster le contenu principal */
    @media (min-width: 768px) {
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav)
        .content-wrapper,
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav)
        .main-footer,
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav)
        .main-header {
            margin-left: 290px;
        }
    }


</style>

