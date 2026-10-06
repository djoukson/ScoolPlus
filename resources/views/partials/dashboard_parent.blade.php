{{-- resources/views/partials/dashboard_parent.blade.php --}}

@php
    $fmt            = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $has            = fn ($name) => \Illuminate\Support\Facades\Route::has($name);

    $enfantsData    = collect($enfantsData ?? []);
    $nbEnfants      = $enfantsData->count();
    $totalPrevu     = $totalPrevu ?? 0;
    $totalPaye      = $totalPaye ?? 0;
    $totalReste     = $totalReste ?? 0;
    $tauxGlobal     = $tauxGlobal ?? null;
    $messagesNonLus = $messagesNonLus ?? 0;
    $notificationsRecentes = $notificationsRecentes ?? collect();

    $parentUser = auth()->user();
    $parentName = $parentUser->name ?? $parentUser->username ?? 'Parent';

    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');

    // Actions rapides : n'affiche que les routes qui existent
    $actions = collect([
        ['route' => 'messages.index',      'icon' => 'fa-comment-dots', 'label' => 'Messagerie',    'tone' => 'primary', 'badge' => $messagesNonLus],
        ['route' => 'notifications.index', 'icon' => 'fa-bell',         'label' => 'Notifications', 'tone' => 'warning', 'badge' => 0],
        ['route' => 'settings.index',      'icon' => 'fa-user-cog',     'label' => 'Mon profil',    'tone' => 'info',    'badge' => 0],
    ])->filter(fn ($a) => $has($a['route']));
@endphp

<div class="pd-wrap">

    {{-- ============================================================
         BANDEAU D'ACCUEIL
    ============================================================ --}}
    <div class="pd-hero">
        <div class="pd-hero-text">
            <span class="pd-kicker">
                <i class="far fa-calendar-alt"></i>
                {{ $anneeActive->nom ?? 'Aucune année scolaire active' }}
            </span>

            <h2>{{ $greeting }}, {{ $parentName }}</h2>

            <p>
                Suivez la scolarité de {{ $nbEnfants > 1 ? 'vos enfants' : 'votre enfant' }}
                et échangez facilement avec l’école.
            </p>
        </div>

        @if($has('messages.index'))
            <a href="{{ route('messages.index') }}" class="pd-hero-btn">
                <i class="fas fa-comment-dots"></i>
                Messagerie
                @if($messagesNonLus > 0)
                    <span class="pd-pill">{{ $messagesNonLus > 99 ? '99+' : $messagesNonLus }}</span>
                @endif
            </a>
        @endif
    </div>


    {{-- ============================================================
         INDICATEURS FAMILLE
    ============================================================ --}}
    <div class="pd-kpis">

        <div class="pd-kpi">
            <span class="pd-kpi-icon tone-primary"><i class="fas fa-child"></i></span>
            <div>
                <div class="pd-kpi-value">{{ $nbEnfants }}</div>
                <div class="pd-kpi-label">{{ $nbEnfants > 1 ? 'Enfants' : 'Enfant' }} scolarisé{{ $nbEnfants > 1 ? 's' : '' }}</div>
            </div>
        </div>

        <div class="pd-kpi">
            <span class="pd-kpi-icon tone-info"><i class="fas fa-file-invoice-dollar"></i></span>
            <div>
                <div class="pd-kpi-value">{{ $fmt($totalPrevu) }} <small>FCFA</small></div>
                <div class="pd-kpi-label">Total à payer</div>
            </div>
        </div>

        <div class="pd-kpi">
            <span class="pd-kpi-icon tone-success"><i class="fas fa-check-circle"></i></span>
            <div>
                <div class="pd-kpi-value">{{ $fmt($totalPaye) }} <small>FCFA</small></div>
                <div class="pd-kpi-label">Déjà payé</div>
            </div>
        </div>

        <div class="pd-kpi">
            <span class="pd-kpi-icon {{ $totalReste > 0 ? 'tone-danger' : 'tone-success' }}">
                <i class="fas {{ $totalReste > 0 ? 'fa-hourglass-half' : 'fa-thumbs-up' }}"></i>
            </span>
            <div>
                <div class="pd-kpi-value">{{ $fmt($totalReste) }} <small>FCFA</small></div>
                <div class="pd-kpi-label">
                    Reste à payer
                    @if($tauxGlobal !== null)
                        <span class="pd-kpi-rate">· {{ $tauxGlobal }}% réglé</span>
                    @endif
                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
         CONTENU : ENFANTS + COLONNE LATÉRALE
    ============================================================ --}}
    <div class="pd-layout">

        {{-- ---------------- MES ENFANTS ---------------- --}}
        <div class="pd-main">

            <div class="pd-section-title">
                <h5><i class="fas fa-user-graduate"></i> Mes enfants</h5>
            </div>

            @if(!$anneeActive)
                <div class="pd-notice pd-notice-warn">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Aucune année scolaire active pour le moment. Les informations s’afficheront dès son ouverture.</span>
                </div>
            @endif

            @forelse($enfantsData as $e)

                @php
                    $statut = match (true) {
                        $e['taux'] === null => ['Frais non définis', 'muted'],
                        $e['taux'] >= 100   => ['Soldé', 'ok'],
                        $e['taux'] > 0      => ['Paiement partiel', 'mid'],
                        default             => ['Non payé', 'low'],
                    };
                    $initial = mb_strtoupper(mb_substr($e['prenom'] ?: $e['nom'], 0, 1));
                @endphp

                <div class="pd-child">

                    <div class="pd-child-head">
                        <div class="pd-child-avatar">{{ $initial }}</div>

                        <div class="pd-child-id">
                            <h6>{{ $e['nom'] }} {{ $e['prenom'] }}</h6>

                            <div class="pd-tags">
                                @if($e['classe'])
                                    <span class="pd-tag tag-primary"><i class="fas fa-chalkboard"></i> {{ $e['classe'] }}</span>
                                @endif
                                @if($e['niveau'])
                                    <span class="pd-tag tag-info"><i class="fas fa-layer-group"></i> {{ $e['niveau'] }}</span>
                                @endif
                            </div>
                        </div>

                        <span class="pd-status {{ $e['inscrit'] ? 'ok' : 'low' }}">
                            <i class="fas {{ $e['inscrit'] ? 'fa-check' : 'fa-times' }}"></i>
                            {{ $e['inscrit'] ? 'Inscrit' : 'Non inscrit' }}
                        </span>
                    </div>

                    @if($e['inscrit'])

                        <div class="pd-pay">
                            <div class="pd-pay-top">
                                <span class="pd-pay-label">Frais de scolarité</span>
                                <span class="pd-status pd-status-sm {{ $statut[1] }}">{{ $statut[0] }}</span>
                            </div>

                            <div class="pd-bar">
                                <div class="pd-bar-fill {{ $statut[1] }}" style="width: {{ $e['taux'] ?? 0 }}%"></div>
                            </div>

                            <div class="pd-pay-nums">
                                <div>
                                    <small>Prévu</small>
                                    <strong>{{ $fmt($e['prevu']) }}</strong>
                                </div>
                                <div>
                                    <small>Payé</small>
                                    <strong class="txt-ok">{{ $fmt($e['paye']) }}</strong>
                                </div>
                                <div>
                                    <small>Reste</small>
                                    <strong class="{{ $e['reste'] > 0 ? 'txt-low' : 'txt-ok' }}">{{ $fmt($e['reste']) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="pd-child-foot">
                            <span class="pd-meta">
                                <i class="fas fa-user-clock"></i>
                                {{ $e['absences'] }} absence{{ $e['absences'] > 1 ? 's' : '' }}
                            </span>

                            @if($has('messages.index'))
                                <a href="{{ route('messages.index') }}" class="pd-link">
                                    Écrire à l’école <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>

                    @else

                        <div class="pd-notice pd-notice-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            <span>Aucune inscription trouvée pour l’année en cours. Contactez l’administration pour plus d’informations.</span>
                        </div>

                    @endif

                </div>

            @empty

                <div class="pd-empty">
                    <div class="pd-empty-icon"><i class="fas fa-child"></i></div>
                    <h6>Aucun enfant rattaché à votre compte</h6>
                    <p>Contactez l’administration de l’école afin de lier vos enfants à votre profil parent.</p>
                    @if($has('messages.index'))
                        <a href="{{ route('messages.index') }}" class="pd-hero-btn pd-hero-btn-dark">
                            <i class="fas fa-comment-dots"></i> Contacter l’école
                        </a>
                    @endif
                </div>

            @endforelse

        </div>


        {{-- ---------------- COLONNE LATÉRALE ---------------- --}}
        <div class="pd-side">

            {{-- Actions rapides --}}
            @if($actions->isNotEmpty())
                <div class="pd-card">
                    <div class="pd-card-title"><i class="fas fa-bolt"></i> Accès rapides</div>

                    <div class="pd-actions">
                        @foreach($actions as $a)
                            <a href="{{ route($a['route']) }}" class="pd-action">
                                <span class="pd-action-icon tone-{{ $a['tone'] }}">
                                    <i class="fas {{ $a['icon'] }}"></i>
                                    @if($a['badge'] > 0)
                                        <span class="pd-action-badge">{{ $a['badge'] > 99 ? '99+' : $a['badge'] }}</span>
                                    @endif
                                </span>
                                <span>{{ $a['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Notifications récentes --}}
            <div class="pd-card">
                <div class="pd-card-title pd-card-title-between">
                    <span><i class="fas fa-bell"></i> Notifications</span>
                    @if($has('notifications.index'))
                        <a href="{{ route('notifications.index') }}" class="pd-link-sm">Tout voir</a>
                    @endif
                </div>

                @forelse($notificationsRecentes as $notif)
                    @php
                        $nIcon = 'fa-info-circle'; $nTone = 'primary';
                        if ($notif->type === 'Alerte frais')        { $nIcon = 'fa-exclamation-triangle'; $nTone = 'danger'; }
                        elseif ($notif->type === 'Alerte système')  { $nIcon = 'fa-cogs';                 $nTone = 'warning'; }
                    @endphp

                    <a href="{{ $notif->url ?? route('notifications.show', $notif->id) }}" class="pd-notif">
                        <span class="pd-notif-icon tone-{{ $nTone }}"><i class="fas {{ $nIcon }}"></i></span>
                        <span class="pd-notif-body">
                            <strong>{{ $notif->titre }}</strong>
                            <small>{{ \Illuminate\Support\Str::limit($notif->message, 65) }}</small>
                            <em>{{ $notif->created_at?->locale('fr')->diffForHumans() }}</em>
                        </span>
                    </a>
                @empty
                    <div class="pd-mini-empty">
                        <i class="far fa-bell-slash"></i>
                        <span>Vous êtes à jour</span>
                    </div>
                @endforelse
            </div>

            {{-- Calendrier & heure (ids utilisés par le script de dashboard.blade) --}}
            <div class="pd-card">
                <div class="pd-card-title pd-card-title-between">
                    <span><i class="fas fa-calendar-alt"></i> Calendrier</span>
                    <span id="liveClock" class="pd-clock"></span>
                </div>

                <div class="calendar-header text-center mb-2">
                    <span id="calendarMonth"></span>
                </div>

                <div id="miniCalendar" class="calendar-container"></div>
            </div>

        </div>

    </div>

</div>


<style>
.pd-wrap{
    --pd-primary:#4f46e5;
    --pd-primary-soft:#eef2ff;
    --pd-border:#eeecf3;
    --pd-text:#111827;
    --pd-muted:#6b7280;
    --pd-ok:#16a34a;
    --pd-mid:#d97706;
    --pd-low:#dc2626;
    color:var(--pd-text);
}
.pd-wrap *{box-sizing:border-box;}

/* ---------- Tons ---------- */
.tone-primary{background:#eef2ff;color:#4f46e5;}
.tone-info{background:#e0f2fe;color:#0284c7;}
.tone-success{background:#dcfce7;color:#16a34a;}
.tone-warning{background:#fef3c7;color:#d97706;}
.tone-danger{background:#fee2e2;color:#dc2626;}

/* ---------- Bandeau ---------- */
.pd-hero{
    display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;
    padding:26px 30px;margin-bottom:20px;border-radius:20px;color:#fff;
    background:linear-gradient(135deg,#6366f1 0%,#4f46e5 55%,#4338ca 100%);
    box-shadow:0 14px 34px rgba(79,70,229,.28);
    position:relative;overflow:hidden;
}
.pd-hero::after{
    content:"";position:absolute;right:-60px;top:-70px;width:240px;height:240px;border-radius:50%;
    background:rgba(255,255,255,.09);
}
.pd-hero-text{position:relative;z-index:1;}
.pd-kicker{
    display:inline-flex;align-items:center;gap:7px;
    background:rgba(255,255,255,.18);border-radius:999px;padding:4px 13px;
    font-size:12.5px;font-weight:600;margin-bottom:10px;
}
.pd-hero h2{font-size:26px;font-weight:700;letter-spacing:-.4px;margin:0 0 6px;}
.pd-hero p{margin:0;opacity:.88;font-size:14.5px;}
.pd-hero-btn{
    position:relative;z-index:1;
    display:inline-flex;align-items:center;gap:9px;
    background:#fff;color:var(--pd-primary);font-weight:700;font-size:14px;
    border-radius:12px;padding:11px 20px;text-decoration:none;
    box-shadow:0 8px 20px rgba(16,24,40,.16);transition:transform .15s ease;
}
.pd-hero-btn:hover{transform:translateY(-2px);color:var(--pd-primary);text-decoration:none;}
.pd-hero-btn-dark{background:var(--pd-primary);color:#fff;box-shadow:0 8px 20px rgba(79,70,229,.3);}
.pd-hero-btn-dark:hover{color:#fff;}
.pd-pill{
    background:#ef4444;color:#fff;font-size:11px;font-weight:700;
    border-radius:999px;padding:2px 8px;
}

/* ---------- KPI ---------- */
.pd-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px;}
.pd-kpi{
    display:flex;align-items:center;gap:14px;
    background:#fff;border:1px solid var(--pd-border);border-radius:16px;padding:16px 18px;
    box-shadow:0 1px 2px rgba(16,24,40,.04);transition:transform .18s ease,box-shadow .18s ease;
}
.pd-kpi:hover{transform:translateY(-3px);box-shadow:0 10px 24px rgba(16,24,40,.08);}
.pd-kpi-icon{
    width:46px;height:46px;flex-shrink:0;border-radius:14px;font-size:18px;
    display:flex;align-items:center;justify-content:center;
}
.pd-kpi-value{font-size:19px;font-weight:700;line-height:1.2;letter-spacing:-.3px;}
.pd-kpi-value small{font-size:11px;font-weight:600;color:var(--pd-muted);}
.pd-kpi-label{font-size:12.5px;color:var(--pd-muted);margin-top:2px;}
.pd-kpi-rate{color:var(--pd-ok);font-weight:600;}

/* ---------- Layout ---------- */
.pd-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;}
.pd-side{display:flex;flex-direction:column;gap:16px;}

.pd-section-title{margin-bottom:12px;}
.pd-section-title h5{font-size:16px;font-weight:700;margin:0;}
.pd-section-title i{color:var(--pd-primary);margin-right:8px;}

/* ---------- Carte enfant ---------- */
.pd-child{
    background:#fff;border:1px solid var(--pd-border);border-radius:18px;
    padding:20px;margin-bottom:14px;box-shadow:0 1px 2px rgba(16,24,40,.04);
    transition:box-shadow .18s ease;
}
.pd-child:hover{box-shadow:0 10px 26px rgba(16,24,40,.08);}
.pd-child-head{display:flex;align-items:center;gap:14px;margin-bottom:16px;}
.pd-child-avatar{
    width:52px;height:52px;flex-shrink:0;border-radius:16px;
    background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;
    font-size:20px;font-weight:700;
    display:flex;align-items:center;justify-content:center;
}
.pd-child-id{flex:1;min-width:0;}
.pd-child-id h6{font-size:16px;font-weight:700;margin:0 0 5px;}
.pd-tags{display:flex;gap:6px;flex-wrap:wrap;}
.pd-tag{
    font-size:11.5px;font-weight:600;border-radius:8px;padding:2px 9px;
    display:inline-flex;align-items:center;gap:6px;
}
.tag-primary{background:#eef2ff;color:#4f46e5;}
.tag-info{background:#e0f2fe;color:#0369a1;}

.pd-status{
    display:inline-flex;align-items:center;gap:6px;flex-shrink:0;
    font-size:12px;font-weight:700;border-radius:999px;padding:5px 12px;
}
.pd-status-sm{font-size:11px;padding:3px 10px;}
.pd-status.ok{background:#dcfce7;color:#15803d;}
.pd-status.mid{background:#fef3c7;color:#b45309;}
.pd-status.low{background:#fee2e2;color:#b91c1c;}
.pd-status.muted{background:#f3f4f6;color:#6b7280;}

/* ---------- Paiement ---------- */
.pd-pay{background:#f9fafb;border-radius:14px;padding:14px 16px;}
.pd-pay-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.pd-pay-label{font-size:12.5px;font-weight:700;color:#4b5563;text-transform:uppercase;letter-spacing:.4px;}
.pd-bar{height:9px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-bottom:12px;}
.pd-bar-fill{height:100%;border-radius:999px;transition:width .6s ease;}
.pd-bar-fill.ok{background:linear-gradient(90deg,#22c55e,#16a34a);}
.pd-bar-fill.mid{background:linear-gradient(90deg,#fbbf24,#f59e0b);}
.pd-bar-fill.low{background:#ef4444;}
.pd-bar-fill.muted{background:#d1d5db;}
.pd-pay-nums{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center;}
.pd-pay-nums small{display:block;font-size:11px;color:var(--pd-muted);margin-bottom:2px;}
.pd-pay-nums strong{font-size:14px;}
.txt-ok{color:var(--pd-ok);}
.txt-low{color:var(--pd-low);}

.pd-child-foot{
    display:flex;align-items:center;justify-content:space-between;
    margin-top:14px;padding-top:14px;border-top:1px dashed var(--pd-border);
}
.pd-meta{font-size:13px;color:var(--pd-muted);}
.pd-meta i{margin-right:6px;color:#9ca3af;}
.pd-link{font-size:13px;font-weight:700;color:var(--pd-primary);text-decoration:none;}
.pd-link i{font-size:11px;margin-left:4px;transition:transform .15s ease;}
.pd-link:hover{color:#4338ca;text-decoration:none;}
.pd-link:hover i{transform:translateX(3px);}

/* ---------- Notices / vide ---------- */
.pd-notice{
    display:flex;align-items:flex-start;gap:10px;border-radius:12px;
    padding:12px 14px;font-size:13.5px;margin-bottom:14px;
}
.pd-notice-info{background:#e0f2fe;color:#075985;}
.pd-notice-warn{background:#fef3c7;color:#92400e;}
.pd-notice.mb-0{margin-bottom:0;}

.pd-empty{
    text-align:center;background:#fff;border:1px dashed #d8dbe8;border-radius:18px;padding:50px 24px;
}
.pd-empty-icon{
    width:70px;height:70px;margin:0 auto 14px;border-radius:22px;
    background:#eef2ff;color:#6366f1;font-size:28px;
    display:flex;align-items:center;justify-content:center;
}
.pd-empty h6{font-weight:700;margin-bottom:6px;}
.pd-empty p{color:var(--pd-muted);font-size:14px;margin-bottom:18px;}

/* ---------- Cartes latérales ---------- */
.pd-card{
    background:#fff;border:1px solid var(--pd-border);border-radius:18px;padding:18px;
    box-shadow:0 1px 2px rgba(16,24,40,.04);
}
.pd-card-title{font-size:14px;font-weight:700;margin-bottom:14px;}
.pd-card-title i{color:var(--pd-primary);margin-right:8px;}
.pd-card-title-between{display:flex;align-items:center;justify-content:space-between;}
.pd-link-sm{font-size:12.5px;font-weight:600;color:var(--pd-primary);}

.pd-actions{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;}
.pd-action{
    display:flex;flex-direction:column;align-items:center;gap:8px;
    padding:14px 8px;border-radius:14px;background:#f9fafb;
    font-size:12.5px;font-weight:600;color:#374151;text-decoration:none;
    transition:all .18s ease;
}
.pd-action:hover{background:#fff;transform:translateY(-3px);box-shadow:0 8px 20px rgba(16,24,40,.09);color:var(--pd-primary);text-decoration:none;}
.pd-action-icon{
    position:relative;width:42px;height:42px;border-radius:13px;font-size:16px;
    display:flex;align-items:center;justify-content:center;
}
.pd-action-badge{
    position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;
    border-radius:999px;background:#ef4444;color:#fff;border:2px solid #fff;
    font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;
}

.pd-notif{
    display:flex;gap:12px;padding:9px 8px;border-radius:12px;text-decoration:none;color:inherit;
    transition:background .12s ease;
}
.pd-notif:hover{background:#f5f6ff;text-decoration:none;color:inherit;}
.pd-notif-icon{
    width:36px;height:36px;flex-shrink:0;border-radius:11px;font-size:13px;
    display:flex;align-items:center;justify-content:center;
}
.pd-notif-body{min-width:0;}
.pd-notif-body strong{display:block;font-size:13px;}
.pd-notif-body small{display:block;font-size:12px;color:var(--pd-muted);line-height:1.35;}
.pd-notif-body em{display:block;font-style:normal;font-size:11px;color:#9ca3af;margin-top:3px;}
.pd-mini-empty{text-align:center;color:var(--pd-muted);font-size:13px;padding:14px 0;}
.pd-mini-empty i{display:block;font-size:22px;margin-bottom:6px;color:#c4c8d4;}

/* ---------- Calendrier (surcharge légère du style de dashboard.blade) ---------- */
.pd-clock{font-weight:700;font-size:13px;color:var(--pd-primary);background:var(--pd-primary-soft);border-radius:999px;padding:3px 11px;}
.pd-card .calendar-header span{font-weight:700;color:var(--pd-text);}
.pd-card .calendar-container{gap:4px;}
.pd-card .calendar-date{border-radius:9px;font-size:12.5px;}
.pd-card .calendar-today{background:linear-gradient(135deg,#6366f1,#4f46e5);}

/* ---------- Responsive ---------- */
@media (max-width:1199.98px){
    .pd-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width:991.98px){
    .pd-layout{grid-template-columns:1fr;}
}
@media (max-width:575.98px){
    .pd-hero{padding:20px;}
    .pd-hero h2{font-size:21px;}
    .pd-hero-btn{width:100%;justify-content:center;}
    .pd-kpis{grid-template-columns:1fr;}
    .pd-child-head{flex-wrap:wrap;}
    .pd-child-foot{flex-direction:column;align-items:flex-start;gap:8px;}
}
</style>