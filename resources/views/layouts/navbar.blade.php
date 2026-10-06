@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
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
        ->where('is_read', 0)
        ->latest()
        ->take(6)
        ->get();

    // 💬 Messages : nombre de conversations avec au moins un message non lu
    //    conversation_participants (conversation_id, user_id, last_read_at)
    //    messages (conversation_id, sender_id, created_at)
    //    ⚠️ Si la colonne expéditeur est user_id, remplacer m.sender_id par m.user_id
    try {
        $messagesNonLus = DB::table('conversation_participants as cp')
            ->where('cp.user_id', $user->id)
            ->whereExists(function ($q) use ($user) {
                $q->select(DB::raw(1))
                  ->from('messages as m')
                  ->whereColumn('m.conversation_id', 'cp.conversation_id')
                  ->where('m.sender_id', '!=', $user->id)
                  ->where(function ($w) {
                      $w->whereNull('cp.last_read_at')
                        ->orWhereColumn('m.created_at', '>', 'cp.last_read_at');
                  });
            })
            ->count();
    } catch (\Throwable $e) {
        $messagesNonLus = 0; // ne jamais casser la navbar
    }

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

    $isAdminLike   = in_array($user->role, ['admin', 'directeur']);
    $onMessages    = request()->routeIs('messages.*');
    $formatBadge   = fn ($n) => $n > 99 ? '99+' : $n;

    // 🔊 Bienvenue : une fois par connexion (la session est vidée à la déconnexion)
    $showWelcome = session('welcome_for') !== $user->id;
    if ($showWelcome) {
        session(['welcome_for' => $user->id]);
    }
@endphp

<nav class="main-header navbar navbar-expand navbar-white navbar-light fixed-top nav-modern">

    {{-- ============================================================
         GAUCHE
    ============================================================ --}}
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link nav-icon-btn" data-widget="pushmenu" href="#" title="Menu">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link nav-home">
                <i class="fas fa-home mr-2"></i> Accueil
            </a>
        </li>
    </ul>


    {{-- ============================================================
         DROITE
    ============================================================ --}}
    <ul class="navbar-nav ml-auto align-items-center nav-right">

        {{-- 📅 Année scolaire --}}
        @if($isAdminLike)
            <li class="nav-item dropdown">
                <a class="nav-link year-chip" data-toggle="dropdown" href="#" title="Année scolaire">
                    <i class="far fa-calendar-alt"></i>
                    <span>{{ $anneeActive ? $anneeActive->nom : 'Aucune' }}</span>
                    <i class="fas fa-chevron-down chip-caret"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-right" style="min-width:230px;">
                    <h6 class="dropdown-header-modern">Changer d'année scolaire</h6>

                    @foreach(App\Models\AnneesScolaire::all() as $annee)
                        @php $isCurrent = $anneeActive && $anneeActive->id == $annee->id; @endphp

                        <form action="{{ route('annee.change', $annee->id) }}" method="POST" class="m-0">
                           @csrf
                           @method('PATCH')
                           <button type="button"
                           onclick="handleYearChange(this.form, '{{ request()->route()?->getName() }}')"
                           class="dropdown-item d-flex align-items-center justify-content-between {{ $isCurrent ? 'active' : '' }}">
                            <span>{{ $annee->nom }}</span>
                            @if($isCurrent)
                                <i class="fas fa-check-circle"></i>
                            @endif
                           </button>
                        </form>
                    @endforeach
                </div>
            </li>
        @else
            <li class="nav-item d-flex align-items-center">
                <span class="year-chip year-chip-static">
                    <i class="far fa-calendar-alt"></i>
                    <span>{{ $anneeActive ? $anneeActive->nom : 'Aucune' }}</span>
                </span>
            </li>
        @endif

        <li class="nav-sep d-none d-md-block"></li>

        {{-- ⚙️ Paramètres (Admin / Directeur) --}}
        @if($isAdminLike)
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon-btn" data-toggle="dropdown" href="#" role="button" title="Paramètres">
                    <i class="fas fa-cog"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-right" style="min-width:260px;">
                    <h6 class="dropdown-header-modern">Paramètres</h6>

                    <a href="{{ route('ecole.index') }}" class="dropdown-item d-flex align-items-center">
                        <span class="item-icon bg-soft-info"><i class="fas fa-school"></i></span>
                        <span>Informations de l'école</span>
                    </a>

                    <a href="{{ route('backup.index') }}" class="dropdown-item d-flex align-items-center">
                        <span class="item-icon bg-soft-success"><i class="fas fa-database"></i></span>
                        <span>Gestion des sauvegardes</span>
                    </a>
                </div>
            </li>
        @endif

        {{-- 💬 Messages --}}
        <li class="nav-item">
            <a class="nav-link nav-icon-btn {{ $onMessages ? 'is-active' : '' }}"
               href="{{ route('messages.index') }}"
               title="Messagerie">
                <i class="fas fa-comment-dots"></i>

                <span id="badge-messages" class="nav-badge nav-badge-pulse"
                      style="display:{{ $messagesNonLus > 0 ? 'flex' : 'none' }}">{{ $formatBadge($messagesNonLus) }}</span>
            </a>
        </li>

        {{-- 🔔 Notifications --}}
        <li class="nav-item dropdown">
            <a class="nav-link nav-icon-btn" data-toggle="dropdown" href="#" role="button" title="Notifications">
                <i class="fas fa-bell"></i>

                <span id="badge-notifs" class="nav-badge nav-badge-pulse"
                      style="display:{{ $notificationsNonLues > 0 ? 'flex' : 'none' }}">{{ $formatBadge($notificationsNonLues) }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right notif-menu">

                <div class="notif-head">
                    <span class="notif-title">Notifications</span>
                    @if($notificationsNonLues > 0)
                        <span class="notif-count">{{ $notificationsNonLues }} non lue{{ $notificationsNonLues > 1 ? 's' : '' }}</span>
                    @endif
                </div>

                <div class="notif-list">
                    @forelse($notifications as $notif)
                        @php
                            $iconClass = 'fas fa-info-circle';
                            $iconBg    = 'bg-soft-primary';

                            if ($notif->type === 'Alerte frais') {
                                $iconClass = 'fas fa-exclamation-triangle';
                                $iconBg    = 'bg-soft-danger';
                            } elseif ($notif->type === 'Alerte système') {
                                $iconClass = 'fas fa-cogs';
                                $iconBg    = 'bg-soft-warning';
                            }
                        @endphp

                        <a href="{{ $notif->url ?? route('notifications.show', $notif->id) }}"
                           class="dropdown-item notif-item"
                           onclick="marquerNotificationLue({{ $notif->id }})">

                            <span class="item-icon {{ $iconBg }}"><i class="{{ $iconClass }}"></i></span>

                            <span class="notif-body">
                                <strong>{{ $notif->titre }}</strong>
                                <small class="notif-text">{{ \Illuminate\Support\Str::limit($notif->message, 70) }}</small>
                                <small class="notif-time">
                                    <i class="far fa-clock"></i>
                                    {{ $notif->created_at?->locale('fr')->diffForHumans() }}
                                </small>
                            </span>

                            <span class="notif-dot"></span>
                        </a>
                    @empty
                        <div class="notif-empty">
                            <div class="notif-empty-icon"><i class="far fa-bell-slash"></i></div>
                            <p>Vous êtes à jour</p>
                            <small>Aucune nouvelle notification</small>
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}" class="notif-footer">
                    Voir toutes les notifications <i class="fas fa-arrow-right ml-1"></i>
                </a>

            </div>
        </li>

        <li class="nav-sep d-none d-md-block"></li>

        {{-- 👤 Profil utilisateur --}}
        <li class="nav-item dropdown">
            <a class="nav-link nav-profile" data-toggle="dropdown" href="#">

                <span class="avatar-wrap">
                    <img src="{{ $profileImage }}" alt="Profil" width="38" height="38">
                    <span class="online-dot"></span>
                </span>

                <span class="d-none d-md-block profile-text">
                    <span class="profile-name">{{ $user->username ?? 'Utilisateur' }}</span>
                    <small class="profile-role">{{ ucfirst($user->role ?? 'Non défini') }}</small>
                </span>

                <i class="fas fa-chevron-down chip-caret d-none d-md-inline"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right profile-menu">

                <div class="profile-card">
                    <img src="{{ $profileImage }}" alt="Profil" width="52" height="52">
                    <div class="profile-card-info">
                        <strong>{{ $user->username ?? 'Utilisateur' }}</strong>
                        <span class="tag-role">{{ ucfirst($user->role ?? 'Non défini') }}</span>
                    </div>
                </div>

                <a href="{{ route('settings.index') }}" class="dropdown-item d-flex align-items-center">
                    <span class="item-icon bg-soft-info"><i class="fas fa-user"></i></span>
                    <span>Mon profil</span>
                </a>

                <a href="{{ route('messages.index') }}" class="dropdown-item d-flex align-items-center">
                    <span class="item-icon bg-soft-primary"><i class="fas fa-comment-dots"></i></span>
                    <span class="flex-grow-1">Mes messages</span>
                    @if($messagesNonLus > 0)
                        <span class="mini-badge">{{ $formatBadge($messagesNonLus) }}</span>
                    @endif
                </a>

                <div class="dropdown-divider"></div>

                <form id="logout-form" action="{{ route('logoutt') }}" method="POST" class="d-none">
                    @csrf
                </form>

                <a href="javascript:void(0)" onclick="confirmLogout()" class="dropdown-item d-flex align-items-center text-danger">
                    <span class="item-icon bg-soft-danger"><i class="fas fa-sign-out-alt"></i></span>
                    <span>Déconnexion</span>
                </a>
            </div>
        </li>

    </ul>
</nav>


{{-- ================================================================
     CSS
================================================================ --}}
<style>
.nav-modern{
    --nv-primary:#4f46e5;
    --nv-primary-soft:#eef2ff;
    --nv-border:#eeecf3;
    --nv-text:#111827;
    --nv-muted:#6b7280;

    background:rgba(255,255,255,.86) !important;
    -webkit-backdrop-filter:saturate(180%) blur(14px);
    backdrop-filter:saturate(180%) blur(14px);
    border-bottom:1px solid var(--nv-border) !important;
    box-shadow:0 1px 12px rgba(16,24,40,.05);
}

/* ---------- Boutons icône ---------- */
.nav-modern .nav-link{color:#4b5563;}
.nav-modern .nav-icon-btn{
    position:relative;width:40px;height:40px;padding:0 !important;margin:0 3px;
    border-radius:12px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:16px;transition:all .15s ease;
}
.nav-modern .nav-icon-btn:hover,
.nav-modern .show > .nav-icon-btn{background:#f3f4f6;color:var(--nv-primary);}
.nav-modern .nav-icon-btn.is-active{background:var(--nv-primary-soft);color:var(--nv-primary);}

.nav-modern .nav-home{
    font-weight:600;font-size:14px;border-radius:12px;padding:8px 14px;
    transition:all .15s ease;
}
.nav-modern .nav-home:hover{background:#f3f4f6;color:var(--nv-primary);}

.nav-sep{width:1px;height:26px;background:var(--nv-border);margin:0 10px;}

/* ---------- Badge ---------- */
.nav-badge{
    position:absolute;top:1px;right:1px;
    min-width:18px;height:18px;padding:0 5px;border-radius:999px;
    background:#ef4444;color:#fff;
    font-size:10.5px;font-weight:700;line-height:1;
    display:flex;align-items:center;justify-content:center;
    border:2px solid #fff;
}
.nav-badge-pulse::after{
    content:"";position:absolute;inset:-2px;border-radius:inherit;
    border:2px solid rgba(239,68,68,.5);
    animation:navPulse 2s ease-out infinite;
}
@keyframes navPulse{
    0%{transform:scale(1);opacity:.9;}
    100%{transform:scale(1.9);opacity:0;}
}

/* ---------- Année scolaire ---------- */
.year-chip{
    display:inline-flex !important;align-items:center;gap:8px;
    padding:7px 13px !important;border-radius:999px;
    background:var(--nv-primary-soft);color:var(--nv-primary) !important;
    font-weight:600;font-size:13px;transition:all .15s ease;
}
.year-chip:hover{background:#e0e7ff;}
.year-chip-static:hover{background:var(--nv-primary-soft);}
.chip-caret{font-size:9px;opacity:.6;}

/* ---------- Profil ---------- */
.nav-modern .nav-profile{
    display:flex;align-items:center;gap:10px;
    padding:4px 12px 4px 4px !important;border-radius:999px;transition:background .15s ease;
}
.nav-modern .nav-profile:hover,
.nav-modern .show > .nav-profile{background:#f3f4f6;}
.avatar-wrap{position:relative;display:inline-block;line-height:0;}
.avatar-wrap img{border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 1px 4px rgba(16,24,40,.18);}
.online-dot{
    position:absolute;right:0;bottom:0;width:11px;height:11px;border-radius:50%;
    background:#22c55e;border:2px solid #fff;
}
.profile-text{line-height:1.2;text-align:left;}
.profile-name{display:block;font-weight:700;font-size:13.5px;color:var(--nv-text);}
.profile-role{color:var(--nv-muted);font-size:11.5px;}

/* ---------- Menus déroulants ---------- */
.nav-modern .dropdown-menu{
    border:1px solid var(--nv-border);border-radius:16px;padding:8px;
    margin-top:10px;box-shadow:0 20px 48px rgba(16,24,40,.16);
    animation:navDrop .16s ease;
}
@keyframes navDrop{
    from{opacity:0;transform:translateY(-6px);}
    to{opacity:1;transform:translateY(0);}
}
.nav-modern .dropdown-item{
    border-radius:10px;padding:9px 12px;font-size:14px;color:#374151;
    transition:background .12s ease;
}
.nav-modern .dropdown-item:hover{background:#f5f6ff;color:var(--nv-primary);}
.nav-modern .dropdown-item.active,
.nav-modern .dropdown-item:active{background:var(--nv-primary-soft);color:var(--nv-primary);font-weight:600;}
.nav-modern .dropdown-item.text-danger:hover{background:#fef2f2;color:#dc2626 !important;}
.nav-modern .dropdown-divider{margin:6px 4px;border-color:var(--nv-border);}

.dropdown-header-modern{
    font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:var(--nv-muted);padding:6px 12px 8px;margin:0;
}

.item-icon{
    width:34px;height:34px;flex-shrink:0;margin-right:12px;border-radius:10px;
    display:inline-flex;align-items:center;justify-content:center;font-size:13px;
}
.bg-soft-primary{background:#eef2ff;color:#4f46e5;}
.bg-soft-info{background:#e0f2fe;color:#0284c7;}
.bg-soft-success{background:#dcfce7;color:#16a34a;}
.bg-soft-warning{background:#fef3c7;color:#d97706;}
.bg-soft-danger{background:#fee2e2;color:#dc2626;}

.mini-badge{
    background:#ef4444;color:#fff;font-size:10.5px;font-weight:700;
    border-radius:999px;padding:2px 8px;
}

/* ---------- Notifications ---------- */
.notif-menu{width:360px;padding:0 !important;overflow:hidden;}
.notif-head{
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 16px;border-bottom:1px solid var(--nv-border);
}
.notif-title{font-weight:700;font-size:15px;color:var(--nv-text);}
.notif-count{
    font-size:11.5px;font-weight:600;color:var(--nv-primary);
    background:var(--nv-primary-soft);border-radius:999px;padding:3px 10px;
}
.notif-list{max-height:340px;overflow-y:auto;padding:6px;}
.notif-item{display:flex !important;align-items:flex-start;white-space:normal;position:relative;padding-right:26px !important;}
.notif-body{min-width:0;display:block;}
.notif-body strong{display:block;font-size:13.5px;color:var(--nv-text);}
.notif-text{display:block;color:var(--nv-muted);line-height:1.35;margin-top:1px;}
.notif-time{display:block;color:#9ca3af;font-size:11px;margin-top:4px;}
.notif-dot{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    width:8px;height:8px;border-radius:50%;background:var(--nv-primary);
}
.notif-empty{text-align:center;padding:34px 20px;}
.notif-empty-icon{
    width:56px;height:56px;margin:0 auto 10px;border-radius:18px;
    background:#f3f4f6;color:#9ca3af;font-size:22px;
    display:flex;align-items:center;justify-content:center;
}
.notif-empty p{margin:0;font-weight:600;color:var(--nv-text);}
.notif-empty small{color:var(--nv-muted);}
.notif-footer{
    display:block;text-align:center;padding:12px;
    border-top:1px solid var(--nv-border);background:#fafbfc;
    font-size:13px;font-weight:600;color:var(--nv-primary);text-decoration:none;
}
.notif-footer:hover{background:var(--nv-primary-soft);text-decoration:none;color:var(--nv-primary);}

/* ---------- Carte profil ---------- */
.profile-menu{min-width:270px;}
.profile-card{
    display:flex;align-items:center;gap:12px;
    padding:12px;margin-bottom:6px;border-radius:12px;
    background:linear-gradient(135deg,#eef2ff,#f5f3ff);
}
.profile-card img{border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 2px 8px rgba(79,70,229,.2);}
.profile-card-info strong{display:block;font-size:14.5px;color:var(--nv-text);}
.tag-role{
    display:inline-block;margin-top:3px;font-size:11px;font-weight:600;
    background:#fff;color:var(--nv-primary);border-radius:8px;padding:2px 9px;
}

/* ---------- Responsive ---------- */
@media (max-width:575.98px){
    .notif-menu{width:calc(100vw - 20px);position:fixed !important;right:10px;left:10px !important;top:60px !important;transform:none !important;}
    .year-chip{padding:6px 10px !important;font-size:12px;}
    .nav-sep{margin:0 4px;}
}
</style>


{{-- ================================================================
     JS
================================================================ --}}
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

    function handleYearChange(form, currentRoute) {
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
                    form.submit();
                }
            });
        }
    }

    function marquerNotificationLue(id) {
        // keepalive : la requête part même si la page change juste après le clic
        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            keepalive: true,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
    }

    /* ===== Son de notification (généré par le navigateur, aucun fichier) ===== */
    const NotifSound = (() => {
        let ctx = null, pending = false;

        const getCtx = () => {
            if (!ctx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return null;
                ctx = new AC();
            }
            return ctx;
        };

        const tone = (c, freq, start, dur, vol) => {
            const o = c.createOscillator(), g = c.createGain();
            o.type = 'sine';
            o.frequency.value = freq;
            g.gain.setValueAtTime(0.0001, c.currentTime + start);
            g.gain.exponentialRampToValueAtTime(vol, c.currentTime + start + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, c.currentTime + start + dur);
            o.connect(g); g.connect(c.destination);
            o.start(c.currentTime + start);
            o.stop(c.currentTime + start + dur + 0.05);
        };

        async function play() {
            const c = getCtx();
            if (!c) return false;
            if (c.state === 'suspended') { try { await c.resume(); } catch (e) {} }
            if (c.state !== 'running') { pending = true; return false; } // bloqué : joué au 1er clic
            tone(c, 880, 0, 0.25, 0.18);     // La5
            tone(c, 1318, 0.12, 0.35, 0.15); // Mi6
            return true;
        }

        // Les navigateurs bloquent le son avant la 1re interaction : on le joue dès le 1er clic/touche
        const unlock = async () => {
            const c = getCtx();
            if (c && c.state === 'suspended') { try { await c.resume(); } catch (e) {} }
            if (pending) { pending = false; play(); }
            ['pointerdown', 'keydown', 'touchstart'].forEach(e => document.removeEventListener(e, unlock));
        };
        ['pointerdown', 'keydown', 'touchstart'].forEach(e =>
            document.addEventListener(e, unlock, { passive: true }));

        return { play };
    })();

    /* ===== Temps réel + message de bienvenue ===== */
    (function () {
        const SHOW_WELCOME = @json($showWelcome);
        const initMsg   = {{ (int) $messagesNonLus }};
        const initNotif = {{ (int) $notificationsNonLues }};

        const fmt = n => n > 99 ? '99+' : n;
        const setBadge = (id, n) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = fmt(n);
            el.style.display = n > 0 ? 'flex' : 'none';
        };
        const toast = (icon, title) => {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                toast: true, position: 'top-end', icon, title,
                showConfirmButton: false, timer: 4500, timerProgressBar: true
            });
        };
        const getId = k => parseInt(sessionStorage.getItem(k) || '-1', 10);
        const setId = (k, v) => sessionStorage.setItem(k, String(v));

        // 🔔 À chaque connexion : son + nombre total en attente
        if (SHOW_WELCOME) {
            const total = initMsg + initNotif;
            if (total > 0) {
                NotifSound.play();
                toast('info', `Vous avez ${total} notification${total > 1 ? 's' : ''} en attente`);
            }
            sessionStorage.removeItem('last_msg_id');
            sessionStorage.removeItem('last_notif_id');
        }

        // ⚡ Temps réel : vérification toutes les 5 secondes
        async function poll() {
            try {
                const r = await fetch(@json(route('notifications.counts')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!r.ok) { console.warn('counts HTTP', r.status); return; }
                const d = await r.json();

                const prevMsg   = getId('last_msg_id');
                const prevNotif = getId('last_notif_id');

                // -1 = première fois : on mémorise sans sonner
                const newMsg   = prevMsg   !== -1 && d.last_message_id > prevMsg;
                const newNotif = prevNotif !== -1 && d.last_notif_id   > prevNotif;

                if (newMsg) {
                    NotifSound.play();
                    toast('info', 'Nouveau message reçu');
                } else if (newNotif) {
                    NotifSound.play();
                    toast('info', 'Nouvelle notification');
                }

                setId('last_msg_id', d.last_message_id);
                setId('last_notif_id', d.last_notif_id);
                setBadge('badge-messages', d.messages);
                setBadge('badge-notifs', d.notifications);
            } catch (e) { console.warn('poll error', e); }
        }

        poll();
        setInterval(poll, 5000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    })();
</script>
