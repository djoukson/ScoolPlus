@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Notification;
    use App\Models\AnneesScolaire;

    if (!Auth::check()) {
        echo '<script>window.location.href="' . route('login') . '";</script>';
        exit;
    }

    $user = Auth::user();

    // 🔔 Notifications (non lues et récentes)
    $notificationsNonLues = Notification::where(function($q) use ($user) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $user->id);
        })
        ->where('is_read', 0)
        ->count();

   $notifications = Notification::where(function($q) use ($user) {
        $q->whereNull('user_id')
          ->orWhere('user_id', $user->id);
    })
    ->where('is_read', 0) // ✅ afficher seulement celles non lues
    ->latest()
    ->take(6)
    ->get();


    // 👤 Image de profil
    if ($user->profileimg && file_exists(public_path($user->profileimg))) {
        $profileImage = asset($user->profileimg);
    } else {
        $profileImage = $user->sexe === 'Feminin'
            ? asset('dist/img/woman.png')
            : ($user->sexe === 'Masculin'
                ? asset('dist/img/man.png')
                : asset('dist/img/general.png'));
    }

    // 📆 Année scolaire active
    $anneeActive = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();
@endphp

<nav class="main-header navbar navbar-expand navbar-white navbar-light shadow-sm fixed-top">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">Accueil</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- 📅 Sélection Année scolaire -->
        @if(in_array($user->role, ['admin', 'directeur']))
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                    <i class="far fa-calendar-alt mr-1 text-primary"></i>
                    <span><strong>{{ $anneeActive ? $anneeActive->nom : 'Aucune' }}</strong></span>
                    <i class="fas fa-caret-down ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right p-2" style="min-width:220px;">
                    <h6 class="dropdown-header">Changer d'année scolaire</h6>
                    @foreach(App\Models\AnneesScolaire::all() as $annee)
                        <a href="javascript:void(0);"
                           onclick="handleYearChange('{{ route('annee.change', $annee->id) }}', '{{ request()->route()->getName() }}')"
                           class="dropdown-item {{ ($anneeActive && $anneeActive->id == $annee->id) ? 'active font-weight-bold' : '' }}">
                            {{ $annee->nom }}
                        </a>
                    @endforeach
                </div>
            </li>
        @else
            <li class="nav-item d-flex align-items-center mx-2">
                <i class="far fa-calendar-alt mr-1 text-primary"></i>
                <span><strong>{{ $anneeActive ? $anneeActive->nom : 'Aucune' }}</strong></span>
            </li>
        @endif

        <!-- ⚙️ Paramètres (Admin / Directeur) -->
        @if(in_array($user->role, ['admin', 'directeur']))
            <li class="nav-item dropdown mx-2">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button">
                    <i class="fas fa-cog text-primary"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow-lg p-2 rounded">
                    <a href="{{ route('ecole.index') }}" class="dropdown-item d-flex align-items-center">
                        <i class="fas fa-school text-info me-2"></i> Informations de l'école
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('backup.index') }}" class="dropdown-item d-flex align-items-center">
                        <i class="fas fa-database text-success me-2"></i> Gestion des sauvegardes
                    </a>
                </div>
            </li>
        @endif

        <!-- 🔔 Notifications -->
        <li class="nav-item dropdown mx-2">
            <a class="nav-link position-relative" data-toggle="dropdown" href="#" role="button">
                <i class="fas fa-bell fa-lg"></i>
                @if($notificationsNonLues > 0)
                    <span class="badge badge-danger navbar-badge"
                          style="font-size: 0.7rem; position:absolute; top:5px; right:5px;">
                        {{ $notificationsNonLues }}
                    </span>
                @else
                    <span style="position:absolute; top:8px; right:8px; width:8px; height:8px;
                         background-color:limegreen; border-radius:50%; display:inline-block;"></span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow-lg p-2"
                 style="min-width: 300px; max-height: 420px; overflow-y: auto;">
                <h6 class="dropdown-header text-center text-primary">
                    <i class="fas fa-bell me-1"></i> Notifications
                </h6>
                <div class="dropdown-divider"></div>

                @forelse($notifications as $notif)
                    <a href="{{ $notif->url ?? route('notifications.show', $notif->id) }}"
                       class="dropdown-item d-flex align-items-start"
                       onclick="marquerNotificationLue({{ $notif->id }})">
                        <div>
                            @php
                                $icon = 'fas fa-info-circle text-primary';
                                if ($notif->type === 'Alerte frais') $icon = 'fas fa-exclamation-triangle text-danger';
                                elseif ($notif->type === 'Alerte système') $icon = 'fas fa-cogs text-warning';
                            @endphp
                            <i class="{{ $icon }} me-2"></i>
                        </div>
                        <div>
                            <strong>{{ $notif->titre }}</strong><br>
                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($notif->message, 60) }}</small>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <p class="text-center text-muted mb-0">Aucune notification</p>
                @endforelse

                <div class="text-center mt-2">
                    <a href="{{ route('notifications.index') }}" class="small text-primary">Voir toutes les notifications</a>
                </div>
            </div>
        </li>

        <!-- 👤 Profil utilisateur -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                <img src="{{ $profileImage }}" alt="Profil"
                     class="rounded-circle shadow-sm border"
                     width="38" height="38"
                     style="object-fit: cover;">

                <div class="d-none d-md-block text-left ms-2">
                    <span class="d-block fw-bold">
                        {{ $user->username ?? 'Utilisateur' }}
                        <span class="text-success">●</span>
                    </span>
                    <small class="text-muted">{{ ucfirst($user->role ?? 'Non défini') }}</small>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2 text-info"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <form id="logout-form" action="{{ route('logoutt') }}" method="POST" class="d-none">
                    @csrf
                </form>
                <a href="javascript:void(0)" onclick="confirmLogout()" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- ✅ JS: Confirmation et restrictions -->
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Déconnexion',
            text: "Voulez-vous vraiment vous déconnecter ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, déconnectez-moi',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    function handleYearChange(url, currentRoute) {
        const allowedRoutes = ['dashboard', 'home', '/'];
        if (!allowedRoutes.includes(currentRoute)) {
            Swal.fire({
                title: 'Action non autorisée',
                text: "Veuillez revenir au tableau de bord pour changer d'année scolaire.",
                icon: 'info',
                confirmButtonText: 'Aller au tableau de bord',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('dashboard') }}";
                }
            });
        } else {
            Swal.fire({
                title: 'Changer d’année ?',
                text: "Voulez-vous passer à cette année scolaire ?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, changer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    }

        function marquerNotificationLue(id) {
        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });
    }
</script>
