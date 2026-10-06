@extends('layouts.app')

@section('content')

@php
    $currentUserId     = (int) auth()->id();
    $roleLabels        = isset($roles) ? $roles : [];
    $otherParticipants = $conversation->participants->where('id', '!=', $currentUserId);
    $otherCount        = $otherParticipants->count();
    $firstOther        = $otherParticipants->first();
    $type              = $conversation->type;

    /* ---------- Identité de la conversation ---------- */
    if ($type === 'announcement') {
        $isMulti     = true;
        $chatTitle   = $conversation->subject ?: 'Annonce';
        $chatIcon    = 'fa-bullhorn';
        $avatarClass = 'avatar-announcement';
        $chatRole    = null;
    } elseif ($type === 'group' || $otherCount > 1) {
        $isMulti     = true;
        $chatTitle   = $conversation->subject ?: 'Conversation de groupe';
        $chatIcon    = 'fa-users';
        $avatarClass = 'avatar-group';
        $chatRole    = null;
    } else {
        $isMulti     = false;
        $chatTitle   = $firstOther?->name ?? 'Utilisateur';
        $chatIcon    = 'fa-user';
        $avatarClass = 'avatar-direct';
        $chatRole    = $roleLabels[$firstOther?->role] ?? ucfirst($firstOther?->role ?? '');
    }

    /* ---------- Libellé de date (Aujourd'hui / Hier / date) ---------- */
    $dayLabel = function ($date) {
        $d = \Carbon\Carbon::parse($date)->locale('fr');

        if ($d->isToday())     return 'Aujourd’hui';
        if ($d->isYesterday()) return 'Hier';

        return ucfirst($d->translatedFormat($d->isSameYear(now()) ? 'l j F' : 'j F Y'));
    };

    $messages   = $conversation->messages->values();
    $prevSender = null;
    $prevDay    = null;
@endphp

<div class="container-fluid py-3 chat-page">

    <div class="chat-shell">

        {{-- ============================================================
             EN-TÊTE (toujours visible)
        ============================================================ --}}
        <div class="chat-header">

            <a href="{{ route('messages.index') }}" class="chat-icon-btn" title="Retour">
                <i class="fas fa-arrow-left"></i>
            </a>

            <div class="chat-avatar {{ $avatarClass }}">
                @if(!$isMulti && $firstOther?->profileimg)
                    <img src="{{ asset($firstOther->profileimg) }}" alt="{{ $chatTitle }}">
                @elseif(!$isMulti)
                    {{ strtoupper(substr($chatTitle, 0, 1)) }}
                @else
                    <i class="fas {{ $chatIcon }}"></i>
                @endif
            </div>

            <div class="chat-header-info">
                <h5 class="chat-header-title text-truncate">{{ $chatTitle }}</h5>

                <div class="chat-header-meta">
                    @if($chatRole)
                        <span class="tag tag-direct">{{ $chatRole }}</span>
                    @endif

                    @if($type === 'announcement')
                        <span class="tag tag-announcement">
                            <i class="fas fa-bullhorn"></i> Annonce
                        </span>
                    @endif

                    @if($isMulti)
                        <span class="tag tag-group">
                            <i class="fas fa-users"></i>
                            {{ $otherCount }} {{ $type === 'announcement' ? 'destinataire' : 'participant' }}{{ $otherCount > 1 ? 's' : '' }}
                        </span>
                    @endif

                    @if($conversation->eleve)
                        <span class="tag tag-eleve">
                            <i class="fas fa-user-graduate"></i>
                            {{ $conversation->eleve->nom }} {{ $conversation->eleve->prenom }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Liste des participants (groupes / annonces) --}}
            @if($isMulti)
                <div class="dropdown">
                    <button type="button" class="chat-icon-btn" data-toggle="dropdown" title="Participants">
                        <i class="fas fa-user-friends"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right participants-menu">
                        <div class="participants-title">
                            Participants ({{ $conversation->participants->count() }})
                        </div>

                        @foreach($conversation->participants as $participant)
                            <div class="participant-row">
                                <span class="participant-avatar">
                                    {{ strtoupper(substr($participant->name ?? '?', 0, 1)) }}
                                </span>
                                <span class="participant-info">
                                    <strong>
                                        {{ $participant->name }}
                                        @if((int) $participant->id === $currentUserId)
                                            <em>(vous)</em>
                                        @endif
                                    </strong>
                                    <small>{{ $roleLabels[$participant->role] ?? ucfirst($participant->role ?? '') }}</small>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>


        {{-- ============================================================
             MESSAGES (seule zone qui défile)
        ============================================================ --}}
        <div class="chat-body">

            <div class="chat-messages" id="messagesContainer">

                @forelse($messages as $i => $message)

                    @php
                        $isMine      = (int) $message->sender_id === $currentUserId;
                        $day         = $message->created_at->toDateString();
                        $newDay      = $day !== $prevDay;
                        $next        = $messages[$i + 1] ?? null;
                        $firstOfRun  = $newDay || $prevSender !== $message->sender_id;
                        $lastOfRun   = !$next
                            || $next->sender_id !== $message->sender_id
                            || $next->created_at->toDateString() !== $day;
                        $senderName  = $message->sender?->name ?? 'Utilisateur';
                        $senderRole  = $roleLabels[$message->sender?->role] ?? ucfirst($message->sender?->role ?? '');
                        $showSender  = !$isMine && $isMulti;

                        $prevSender  = $message->sender_id;
                        $prevDay     = $day;
                    @endphp

                    @if($newDay)
                        <div class="day-sep"><span>{{ $dayLabel($message->created_at) }}</span></div>
                    @endif

                    <div id="msg-{{ $message->id }}"
                         class="msg-row {{ $isMine ? 'mine' : 'theirs' }} {{ $firstOfRun ? 'run-start' : '' }}">

                        {{-- Avatar (groupes uniquement, sur le dernier message de la série) --}}
                        @if($showSender)
                            <div class="msg-avatar-col">
                                @if($lastOfRun)
                                    <span class="msg-avatar">{{ strtoupper(substr($senderName, 0, 1)) }}</span>
                                @endif
                            </div>
                        @endif

                        @if($isMine)
                            <button type="button" class="reply-btn" title="Répondre"
                                    data-id="{{ $message->id }}"
                                    data-name="Vous"
                                    data-text="{{ \Illuminate\Support\Str::limit($message->body, 90) }}">
                                <i class="fas fa-reply"></i>
                            </button>
                        @endif

                        <div class="msg-stack">

                            @if($showSender && $firstOfRun)
                                <div class="msg-sender">
                                    {{ $senderName }}
                                    @if($senderRole)<span>{{ $senderRole }}</span>@endif
                                </div>
                            @endif

                            <div class="bubble {{ $lastOfRun ? 'is-last' : '' }}">

                                @if($message->replyTo)
                                    <a href="#msg-{{ $message->replyTo->id }}"
                                       class="reply-quote"
                                       data-target="msg-{{ $message->replyTo->id }}">
                                        <strong>{{ $message->replyTo->sender?->name ?? 'Utilisateur' }}</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($message->replyTo->body, 100) }}</span>
                                    </a>
                                @endif

                                <div class="bubble-text">{{ $message->body }}</div>

                                <div class="bubble-meta">{{ $message->created_at->format('H:i') }}</div>

                            </div>

                        </div>

                        @if(!$isMine)
                            <button type="button" class="reply-btn" title="Répondre"
                                    data-id="{{ $message->id }}"
                                    data-name="{{ $senderName }}"
                                    data-text="{{ \Illuminate\Support\Str::limit($message->body, 90) }}">
                                <i class="fas fa-reply"></i>
                            </button>
                        @endif

                    </div>

                @empty

                    <div class="chat-empty">
                        <div class="chat-empty-icon"><i class="far fa-comments"></i></div>
                        <h6>Aucun message</h6>
                        <p>Écrivez le premier message de cette conversation.</p>
                    </div>

                @endforelse

            </div>

            <button type="button" id="scrollBottom" class="scroll-bottom d-none" title="Aller au dernier message">
                <i class="fas fa-chevron-down"></i>
            </button>

        </div>


        {{-- ============================================================
             ZONE DE SAISIE (toujours visible)
        ============================================================ --}}
        <div class="chat-composer">

            <form method="POST"
                  action="{{ route('messages.send', $conversation->id) }}"
                  id="composerForm">

                @csrf

                <input type="hidden" name="reply_to_id" id="replyToId" value="">

                {{-- Aperçu de la réponse en cours --}}
                <div class="reply-bar d-none" id="replyBar">
                    <div class="reply-bar-body">
                        <i class="fas fa-reply"></i>
                        <div class="reply-bar-text">
                            <strong id="replyName"></strong>
                            <span id="replyText"></span>
                        </div>
                    </div>
                    <button type="button" id="cancelReply" class="reply-bar-close" title="Annuler la réponse">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="composer-box">

                    <textarea name="body"
                              id="chatBody"
                              rows="1"
                              maxlength="10000"
                              required
                              placeholder="Écrire un message...">{{ old('body') }}</textarea>

                    <button type="submit" id="chatSend" class="send-btn" disabled>
                        <i class="fas fa-paper-plane"></i>
                        <span class="send-label">Envoyer</span>
                    </button>

                </div>

                <div class="composer-hint">
                    <span class="d-none d-md-inline">
                        <kbd>Entrée</kbd> pour envoyer · <kbd>Maj</kbd> + <kbd>Entrée</kbd> pour un saut de ligne
                    </span>
                    <span id="charCounter"></span>
                </div>

                @if($errors->has('body'))
                    <div class="composer-error">{{ $errors->first('body') }}</div>
                @endif

            </form>

        </div>

    </div>

</div>


{{-- ================================================================
     CSS
================================================================ --}}
<style>
.chat-page{
    --msg-primary:#4f46e5;
    --msg-primary-soft:#eef2ff;
    --msg-info-soft:#e0f2fe;
    --msg-warn-soft:#fef3c7;
    --msg-danger:#ef4444;
    --msg-border:#eeecf3;
    --msg-text:#111827;
    --msg-muted:#6b7280;

    /* Hauteur à retirer de l'écran : navbar + marges.
       Ajuste cette valeur si la carte déborde ou laisse du vide en bas. */
    --chat-offset:110px;

    color:var(--msg-text);
}

/* ---------- Carte plein écran : header / messages / composer ---------- */
.chat-shell{
    display:flex;flex-direction:column;
    height:calc(100vh - var(--chat-offset));
    height:calc(100dvh - var(--chat-offset));
    min-height:460px;
    background:#fff;border:1px solid var(--msg-border);border-radius:18px;
    overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.06);
}

/* ---------- En-tête ---------- */
.chat-header{
    flex-shrink:0;display:flex;align-items:center;gap:12px;
    padding:12px 16px;background:#fff;border-bottom:1px solid var(--msg-border);
    z-index:5;
}
.chat-icon-btn{
    width:40px;height:40px;flex-shrink:0;border:none;border-radius:12px;
    background:#f3f4f6;color:#4b5563;
    display:inline-flex;align-items:center;justify-content:center;
    transition:all .15s ease;text-decoration:none;
}
.chat-icon-btn:hover{background:var(--msg-primary-soft);color:var(--msg-primary);text-decoration:none;}

.chat-avatar{
    width:44px;height:44px;flex-shrink:0;border-radius:14px;overflow:hidden;
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-weight:700;font-size:17px;
}
.chat-avatar img{width:100%;height:100%;object-fit:cover;}
.avatar-direct{background:linear-gradient(135deg,#6366f1,#4f46e5);}
.avatar-group{background:linear-gradient(135deg,#38bdf8,#0284c7);}
.avatar-announcement{background:linear-gradient(135deg,#fbbf24,#f59e0b);}

.chat-header-info{flex:1 1 auto;min-width:0;}
.chat-header-title{font-size:16px;font-weight:700;letter-spacing:-.2px;margin:0 0 3px;}
.chat-header-meta{display:flex;gap:6px;flex-wrap:wrap;}

.tag{
    font-size:11px;font-weight:600;border-radius:8px;padding:2px 8px;
    display:inline-flex;align-items:center;gap:5px;background:#f3f4f6;color:#4b5563;
}
.tag-direct{background:var(--msg-primary-soft);color:var(--msg-primary);}
.tag-group{background:var(--msg-info-soft);color:#0369a1;}
.tag-announcement{background:var(--msg-warn-soft);color:#b45309;}
.tag-eleve{background:#dcfce7;color:#15803d;}

/* Participants */
.participants-menu{
    min-width:280px;max-height:340px;overflow-y:auto;padding:8px;
    border:1px solid var(--msg-border);border-radius:14px;
    box-shadow:0 16px 40px rgba(16,24,40,.14);
}
.participants-title{
    font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:var(--msg-muted);padding:6px 8px 8px;
}
.participant-row{display:flex;align-items:center;gap:10px;padding:7px 8px;border-radius:10px;}
.participant-row:hover{background:#f9fafb;}
.participant-avatar{
    width:34px;height:34px;border-radius:10px;flex-shrink:0;
    background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-weight:700;font-size:13px;
    display:flex;align-items:center;justify-content:center;
}
.participant-info strong{display:block;font-size:13.5px;}
.participant-info strong em{font-weight:500;color:var(--msg-muted);font-style:normal;}
.participant-info small{font-size:11.5px;color:var(--msg-muted);}

/* ---------- Zone des messages ---------- */
.chat-body{position:relative;flex:1 1 auto;min-height:0;}
.chat-messages{
    position:relative;height:100%;overflow-y:auto;
    padding:18px 20px 12px;background:#f6f7fb;
    scroll-behavior:auto;
}
.chat-messages::-webkit-scrollbar{width:8px;}
.chat-messages::-webkit-scrollbar-thumb{background:#d5d8e4;border-radius:8px;}

/* Séparateur de jour */
.day-sep{display:flex;align-items:center;gap:12px;margin:18px 0 10px;}
.day-sep::before,.day-sep::after{content:"";flex:1;height:1px;background:#e3e6f0;}
.day-sep span{
    font-size:11.5px;font-weight:600;color:var(--msg-muted);
    background:#fff;border:1px solid var(--msg-border);border-radius:999px;padding:3px 12px;
}

/* Ligne de message */
.msg-row{display:flex;align-items:flex-end;gap:8px;margin-bottom:2px;}
.msg-row.run-start{margin-top:12px;}
.msg-row.mine{justify-content:flex-end;}
.msg-row.theirs{justify-content:flex-start;}

.msg-avatar-col{width:30px;flex-shrink:0;}
.msg-avatar{
    width:30px;height:30px;border-radius:10px;
    background:linear-gradient(135deg,#94a3b8,#64748b);color:#fff;
    font-size:12px;font-weight:700;
    display:flex;align-items:center;justify-content:center;
}

.msg-stack{max-width:72%;min-width:0;display:flex;flex-direction:column;}
.mine .msg-stack{align-items:flex-end;}

.msg-sender{font-size:12px;font-weight:700;color:#374151;margin:0 0 3px 4px;}
.msg-sender span{font-weight:500;color:#9ca3af;margin-left:6px;}

/* Bulle */
.bubble{
    padding:9px 13px 6px;border-radius:18px;
    font-size:14.5px;line-height:1.5;word-break:break-word;
    box-shadow:0 1px 2px rgba(16,24,40,.06);
}
.mine .bubble{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;}
.theirs .bubble{background:#fff;color:var(--msg-text);border:1px solid var(--msg-border);}
.mine .bubble.is-last{border-bottom-right-radius:5px;}
.theirs .bubble.is-last{border-bottom-left-radius:5px;}

.bubble-text{white-space:pre-wrap;}
.bubble-meta{font-size:10.5px;text-align:right;margin-top:3px;opacity:.65;}

/* Citation (réponse) */
.reply-quote{
    display:block;margin:0 0 7px;padding:6px 10px;border-radius:10px;
    border-left:3px solid var(--msg-primary);background:var(--msg-primary-soft);
    color:var(--msg-text);text-decoration:none;font-size:12.5px;line-height:1.4;
}
.reply-quote strong{display:block;font-size:12px;color:var(--msg-primary);}
.reply-quote span{display:block;color:#4b5563;}
.reply-quote:hover{text-decoration:none;filter:brightness(.97);}
.mine .reply-quote{background:rgba(255,255,255,.16);border-left-color:rgba(255,255,255,.75);color:#fff;}
.mine .reply-quote strong,.mine .reply-quote span{color:rgba(255,255,255,.92);}

/* Bouton répondre (au survol) */
.reply-btn{
    width:28px;height:28px;flex-shrink:0;border:none;border-radius:50%;
    background:#fff;color:var(--msg-muted);font-size:11px;
    box-shadow:0 1px 4px rgba(16,24,40,.12);
    opacity:0;transition:opacity .15s ease,transform .15s ease;margin-bottom:4px;
}
.reply-btn:hover{color:var(--msg-primary);transform:scale(1.08);}
.msg-row:hover .reply-btn,.reply-btn:focus{opacity:1;}
@media (hover:none){ .reply-btn{opacity:.55;} }

/* Flash quand on saute vers un message cité */
@keyframes msgFlash{
    0%{box-shadow:0 0 0 0 rgba(79,70,229,.55);}
    100%{box-shadow:0 0 0 12px rgba(79,70,229,0);}
}
.msg-flash .bubble{animation:msgFlash 1.3s ease-out;}

/* Bouton "aller en bas" */
.scroll-bottom{
    position:absolute;right:20px;bottom:16px;z-index:6;
    width:40px;height:40px;border:none;border-radius:50%;
    background:#fff;color:var(--msg-primary);
    box-shadow:0 6px 18px rgba(16,24,40,.18);
    transition:transform .15s ease;
}
.scroll-bottom:hover{transform:translateY(-2px);}

/* État vide */
.chat-empty{text-align:center;padding:70px 20px;color:var(--msg-muted);}
.chat-empty-icon{
    width:68px;height:68px;margin:0 auto 14px;border-radius:20px;
    background:#eceef6;color:#9ca3af;font-size:26px;
    display:flex;align-items:center;justify-content:center;
}
.chat-empty h6{font-weight:700;color:var(--msg-text);margin-bottom:4px;}
.chat-empty p{font-size:13.5px;margin:0;}

/* ---------- Composer ---------- */
.chat-composer{
    flex-shrink:0;background:#fff;border-top:1px solid var(--msg-border);
    padding:12px 16px 8px;z-index:5;
}

.reply-bar{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    margin-bottom:9px;padding:8px 12px;border-radius:12px;
    background:var(--msg-primary-soft);border-left:3px solid var(--msg-primary);
}
.reply-bar.d-none{display:none !important;}
.reply-bar-body{display:flex;align-items:center;gap:10px;min-width:0;color:var(--msg-primary);}
.reply-bar-text{min-width:0;font-size:12.5px;line-height:1.35;}
.reply-bar-text strong{display:block;color:var(--msg-primary);}
.reply-bar-text span{display:block;color:#4b5563;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.reply-bar-close{border:none;background:transparent;color:#9ca3af;padding:4px 6px;}
.reply-bar-close:hover{color:var(--msg-danger);}

.composer-box{
    display:flex;align-items:flex-end;gap:10px;
    background:#f4f5fa;border:1px solid var(--msg-border);border-radius:24px;
    padding:6px 6px 6px 18px;transition:all .18s ease;
}
.composer-box:focus-within{
    background:#fff;border-color:var(--msg-primary);
    box-shadow:0 0 0 4px rgba(79,70,229,.10);
}
.composer-box textarea{
    flex:1 1 auto;min-width:0;border:none;outline:none;background:transparent;
    resize:none;font-size:14.5px;line-height:1.5;
    padding:8px 0;max-height:140px;overflow-y:auto;color:var(--msg-text);
}

/* Bouton envoyer : pilule avec texte sur desktop, rond sur mobile */
.send-btn{
    flex-shrink:0;height:42px;min-width:42px;padding:0 18px;
    border:none;border-radius:999px;
    background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;
    font-weight:600;font-size:14px;
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 6px 16px rgba(79,70,229,.28);
    transition:all .18s ease;
}
.send-btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 20px rgba(79,70,229,.36);}
.send-btn:active:not(:disabled){transform:translateY(0);}
.send-btn:disabled{
    background:#d3d6e6;box-shadow:none;cursor:not-allowed;color:#fff;
}
@media (max-width:575.98px){
    .send-btn{padding:0;width:42px;}
    .send-label{display:none;}
}

.composer-hint{
    display:flex;justify-content:space-between;align-items:center;
    min-height:20px;padding:5px 6px 0;font-size:11.5px;color:#9ca3af;
}
.composer-hint kbd{
    background:#f3f4f6;color:#6b7280;border-radius:5px;padding:1px 6px;
    font-size:10.5px;box-shadow:none;border:1px solid #e5e7eb;
}
#charCounter.warn{color:#d97706;font-weight:600;}
.composer-error{color:var(--msg-danger);font-size:12.5px;padding:4px 6px 0;}

/* ---------- Responsive ---------- */
@media (max-width:767px){
    .chat-page{--chat-offset:96px;padding-left:8px !important;padding-right:8px !important;}
    .chat-shell{border-radius:14px;}
    .chat-header{padding:10px 12px;gap:10px;}
    .chat-messages{padding:14px 12px 10px;}
    .msg-stack{max-width:86%;}
    .chat-composer{padding:10px 12px 8px;}
}
</style>


{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
@push('scripts')
<script>
$(function () {

    const $msgs  = $('#messagesContainer');
    const $body  = $('#chatBody');
    const $send  = $('#chatSend');
    const $form  = $('#composerForm');
    const $down  = $('#scrollBottom');

    const MAX_LEN = 10000;
    const IS_TOUCH = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
    let sending = false;

    /* ---------------- Scroll ---------------- */
    function scrollToBottom(animated) {
        const el = $msgs[0];
        if (!el) return;

        if (animated) {
            $msgs.stop().animate({ scrollTop: el.scrollHeight }, 250);
        } else {
            $msgs.scrollTop(el.scrollHeight);
        }
    }

    function toggleScrollButton() {
        const el = $msgs[0];
        if (!el) return;

        const distance = el.scrollHeight - el.scrollTop - el.clientHeight;
        $down.toggleClass('d-none', distance < 240);
    }

    $msgs.on('scroll', toggleScrollButton);
    $down.on('click', function () { scrollToBottom(true); });

    // Position initiale : dernier message
    scrollToBottom(false);
    toggleScrollButton();

    /* ---------------- Zone de saisie : auto-hauteur + état du bouton ---------------- */
    function autosize() {
        $body.css('height', 'auto');
        $body.css('height', Math.min($body[0].scrollHeight, 140) + 'px');
    }

    function refreshComposer() {
        const value = $body.val();
        const len   = value.length;

        $send.prop('disabled', sending || value.trim().length === 0);

        if (len > 8000) {
            $('#charCounter')
                .text(len.toLocaleString('fr-FR') + ' / ' + MAX_LEN.toLocaleString('fr-FR'))
                .toggleClass('warn', len > 9500);
        } else {
            $('#charCounter').text('').removeClass('warn');
        }
    }

    $body.on('input', function () {
        autosize();
        refreshComposer();
    });

    // Entrée = envoyer, Maj+Entrée = saut de ligne (sauf sur mobile)
    $body.on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.originalEvent.isComposing && !IS_TOUCH) {
            e.preventDefault();

            if (!$send.prop('disabled')) {
                $form.trigger('submit');
            }
        }
    });

    autosize();
    refreshComposer();

    if (!IS_TOUCH) {
        $body.trigger('focus');
    }

    /* ---------------- Envoi ---------------- */
    $form.on('submit', function (e) {
        if (sending || $body.val().trim().length === 0) {
            e.preventDefault();
            return false;
        }

        sending = true;

        $send
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i><span class="send-label">Envoi...</span>');
    });

    /* ---------------- Répondre à un message ---------------- */
    function setReply(id, name, text) {
        $('#replyToId').val(id);
        $('#replyName').text(name);
        $('#replyText').text(text);
        $('#replyBar').removeClass('d-none');
        $body.trigger('focus');
    }

    function clearReply() {
        $('#replyToId').val('');
        $('#replyBar').addClass('d-none');
    }

    $(document).on('click', '.reply-btn', function () {
        setReply(
            $(this).data('id'),
            $(this).data('name'),
            $(this).data('text')
        );
    });

    $('#cancelReply').on('click', clearReply);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') clearReply();
    });

    /* ---------------- Aller au message cité ---------------- */
    $(document).on('click', '.reply-quote', function (e) {
        e.preventDefault();

        const $target = $('#' + $(this).data('target'));
        if (!$target.length) return;

        $msgs.stop().animate({
            scrollTop: $msgs.scrollTop() + $target.position().top - 90
        }, 300);

        $target.addClass('msg-flash');
        setTimeout(function () { $target.removeClass('msg-flash'); }, 1400);
    });

});
</script>
@endpush

@endsection