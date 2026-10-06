@extends('layouts.app')

@section('content')

@php
    $authUser = auth()->user();
    $isParent = $authUser->role === 'parent';
@endphp

<div class="container-fluid px-3">
<div class="msg-app">

    {{-- ============================================================
         ZONE FIXE (ne défile jamais)
    ============================================================ --}}
    <div class="msg-top">

        {{-- Bandeau --}}
        <div class="msg-banner">
            <div class="msg-banner-left">
                <div class="msg-banner-icon"><i class="fas fa-comments"></i></div>
                <div>
                    <h4 class="msg-banner-title">Messagerie</h4>
                    <div class="msg-banner-sub">Communiquez avec les utilisateurs de SchoolPlus</div>
                </div>
            </div>

            <div class="msg-banner-right">
                <span class="msg-unread-pill" id="unreadPill" style="display:none;">
                    <i class="fas fa-circle"></i> <strong id="unreadTotal">0</strong> non lue(s)
                </span>
                <button type="button" class="msg-btn-new" data-toggle="modal" data-target="#newMessageModal">
                    <i class="fas fa-pen"></i> <span>Nouveau message</span>
                </button>
            </div>
        </div>

        {{-- Recherche + onglets --}}
        <div class="msg-toolbar">
            <div class="msg-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchConversation" class="form-control"
                       placeholder="Rechercher une conversation, un élève, un participant...">
                <button type="button" id="clearSearch" class="msg-search-clear d-none" aria-label="Effacer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="msg-tabs">
                <button type="button" class="msg-filter active" data-filter="all">
                    Toutes <span class="msg-count" data-count="all">{{ $conversations->count() }}</span>
                </button>
                <button type="button" class="msg-filter" data-filter="direct">
                    <i class="fas fa-user"></i> Directes <span class="msg-count" data-count="direct">0</span>
                </button>
                <button type="button" class="msg-filter" data-filter="group">
                    <i class="fas fa-users"></i> Groupes <span class="msg-count" data-count="group">0</span>
                </button>
                <button type="button" class="msg-filter" data-filter="announcement">
                    <i class="fas fa-bullhorn"></i> Annonces <span class="msg-count" data-count="announcement">0</span>
                </button>
                <button type="button" class="msg-filter" data-filter="unread">
                    <i class="fas fa-envelope"></i> Non lues <span class="msg-count" data-count="unread">0</span>
                </button>
            </div>
        </div>

    </div>


    {{-- ============================================================
         LISTE (seule zone qui défile)
    ============================================================ --}}
    <div class="msg-panel">
        <div class="msg-scroll">

            <div id="conversationList">

                @foreach($conversations as $conversation)

                    @php
                        $currentUserId = auth()->id();

                        $autresParticipants = $conversation->participants->where('id', '!=', $currentUserId);
                        $autreParticipant   = $autresParticipants->first();
                        $nombreParticipants = $autresParticipants->count();
                        $dernierMessage     = $conversation->messages->first();

                        $participantPivot = $conversation->participants->firstWhere('id', $currentUserId)?->pivot;

                        $nonLu = false;
                        if ($dernierMessage && $dernierMessage->sender_id != $currentUserId) {
                            $nonLu = !$participantPivot?->last_read_at
                                || $dernierMessage->created_at > $participantPivot->last_read_at;
                        }

                        $conversationTitle    = 'Conversation';
                        $conversationSubtitle = '';
                        $conversationIcon     = 'fa-comments';
                        $conversationType     = $conversation->type;

                        switch ($conversationType) {
                            case 'direct':
                                $conversationTitle    = $autreParticipant?->name ?? 'Utilisateur';
                                $conversationSubtitle = $roles[$autreParticipant?->role] ?? ucfirst($autreParticipant?->role ?? '');
                                $conversationIcon     = 'fa-user';
                                break;

                            case 'group':
                                $conversationTitle    = $conversation->subject ?: 'Groupe de discussion';
                                $conversationSubtitle = $nombreParticipants . ' participant' . ($nombreParticipants > 1 ? 's' : '');
                                $conversationIcon     = 'fa-users';
                                break;

                            case 'announcement':
                                $conversationIcon  = 'fa-bullhorn';
                                $conversationTitle = $conversation->subject ?: 'Annonce';
                                $rolesParticipants = $autresParticipants->pluck('role')->unique();

                                if ($rolesParticipants->count() === 1) {
                                    $conversationTitle = match ($rolesParticipants->first()) {
                                        'parent'     => 'Tous les parents',
                                        'professeur' => 'Tous les professeurs',
                                        'admin', 'directeur', 'secretaire', 'comptable' => 'Personnel administratif',
                                        default      => $conversation->subject ?: 'Annonce',
                                    };
                                } else {
                                    $conversationTitle = $conversation->subject ?: 'Toute l’école';
                                }

                                $conversationSubtitle = $nombreParticipants . ' destinataire' . ($nombreParticipants > 1 ? 's' : '');
                                break;
                        }

                        $participantsPreview   = $autresParticipants->take(3)->pluck('name')->implode(' • ');
                        $remainingParticipants = max(0, $nombreParticipants - 3);
                    @endphp

                    <a href="{{ route('messages.show', $conversation->id) }}"
                       class="conversation-item {{ $nonLu ? 'is-unread' : '' }}"
                       data-type="{{ $conversationType }}"
                       data-unread="{{ $nonLu ? 1 : 0 }}"
                       data-search="{{ strtolower(
                            $conversationTitle . ' ' .
                            $conversationSubtitle . ' ' .
                            ($conversation->eleve?->nom ?? '') . ' ' .
                            ($conversation->eleve?->prenom ?? '') . ' ' .
                            ($conversation->subject ?? '') . ' ' .
                            $participantsPreview
                       ) }}">

                        <div class="conversation-avatar-wrap">
                            @if($conversationType === 'announcement')
                                <div class="conversation-avatar avatar-announcement"><i class="fas {{ $conversationIcon }}"></i></div>
                            @elseif($conversationType === 'group')
                                <div class="conversation-avatar avatar-group"><i class="fas {{ $conversationIcon }}"></i></div>
                            @elseif($autreParticipant?->profileimg)
                                <img src="{{ asset($autreParticipant->profileimg) }}" class="conversation-avatar" alt="Photo de {{ $autreParticipant->name }}">
                            @else
                                <div class="conversation-avatar avatar-direct">{{ strtoupper(substr($autreParticipant?->name ?? '?', 0, 1)) }}</div>
                            @endif
                        </div>

                        <div class="conversation-body">
                            <div class="conversation-line">
                                <span class="conversation-title text-truncate">{{ $conversationTitle }}</span>
                                @if($dernierMessage)
                                    <span class="conversation-date">{{ $dernierMessage->created_at->diffForHumans(null, true) }}</span>
                                @endif
                            </div>

                            <div class="conversation-tags">
                                @if($conversationSubtitle)
                                    <span class="tag tag-{{ $conversationType }}">
                                        <i class="fas {{ $conversationIcon }}"></i> {{ $conversationSubtitle }}
                                    </span>
                                @endif
                                @if($conversation->eleve)
                                    <span class="tag tag-eleve">
                                        <i class="fas fa-user-graduate"></i> {{ $conversation->eleve->nom }} {{ $conversation->eleve->prenom }}
                                    </span>
                                @endif
                            </div>

                            @if(in_array($conversationType, ['group', 'announcement']) && $participantsPreview)
                                <div class="conversation-participants text-truncate">
                                    {{ $participantsPreview }}
                                    @if($remainingParticipants > 0)<span class="more">+{{ $remainingParticipants }}</span>@endif
                                </div>
                            @endif

                            <div class="conversation-preview">
                                <span class="text-truncate">
                                    @if($dernierMessage)
                                        @if($dernierMessage->sender_id == $currentUserId)
                                            <span class="sender">Vous :</span>
                                        @elseif($conversationType !== 'direct')
                                            <span class="sender">{{ $dernierMessage->sender?->name }} :</span>
                                        @endif
                                        {{ \Illuminate\Support\Str::limit($dernierMessage->body, 120) }}
                                    @else
                                        <em class="text-muted">Aucun message</em>
                                    @endif
                                </span>
                                @if($nonLu)<span class="badge-unread">Nouveau</span>@endif
                            </div>
                        </div>

                        <i class="fas fa-chevron-right conversation-chevron"></i>
                    </a>

                @endforeach

            </div>

            {{-- Aucune conversation du tout --}}
            @if($conversations->isEmpty())
                <div class="msg-empty">
                    <div class="msg-empty-icon"><i class="far fa-comments"></i></div>
                    <h5>Aucune conversation</h5>
                    <p>Démarrez une discussion avec un membre de l’école.</p>
                    <button type="button" class="msg-btn-solid" data-toggle="modal" data-target="#newMessageModal">
                        <i class="fas fa-pen mr-2"></i> Démarrer une conversation
                    </button>
                </div>
            @endif

            {{-- Aucun résultat de recherche/filtre --}}
            <div id="noConversationFound" class="msg-empty d-none">
                <div class="msg-empty-icon"><i class="fas fa-search"></i></div>
                <h5>Aucun résultat</h5>
                <p>Essayez un autre mot-clé ou changez de filtre.</p>
            </div>

        </div>
    </div>

</div>
</div>


{{-- ================================================================
     MODAL : NOUVEAU MESSAGE
================================================================ --}}
<div class="modal fade msg-modal" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('messages.store') }}">
                @csrf

                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <h5 class="modal-title">Nouveau message</h5>
                            <div class="modal-sub">Choisissez vos destinataires puis rédigez votre message</div>
                        </div>
                    </div>
                    <button type="button" class="msg-close" data-dismiss="modal" aria-label="Fermer">&times;</button>
                </div>

                <div class="modal-body">

                    @if($isParent)

                        <input type="radio" name="recipient_type" value="individual" checked hidden>

                        <div class="msg-alert">
                            <i class="fas fa-info-circle"></i>
                            <span>
                                Vous pouvez écrire à un directeur, secrétaire, comptable, professeur
                                ou à un autre parent — <strong>une personne à la fois</strong>.
                            </span>
                        </div>

                    @else

                        @php
                            $types = [
                                'individual'     => ['fa-user',               'Une personne'],
                                'selected'       => ['fa-user-friends',       'Plusieurs'],
                                'parents'        => ['fa-users',              'Parents'],
                                'teachers'       => ['fa-chalkboard-teacher', 'Professeurs'],
                                'administration' => ['fa-building',           'Administration'],
                                'all'            => ['fa-school',             'Toute l’école'],
                            ];
                        @endphp

                        <div class="form-group mb-3">
                            <label class="msg-label mb-2">Type de destinataires</label>
                            <div class="recipient-grid">
                                @foreach($types as $value => $meta)
                                    <label class="recipient-option {{ $loop->first ? 'active' : '' }}">
                                        <input type="radio" name="recipient_type" value="{{ $value }}" {{ $loop->first ? 'checked' : '' }} required>
                                        <span class="recipient-icon"><i class="fas {{ $meta[0] }}"></i></span>
                                        <span class="recipient-text">{{ $meta[1] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    @endif

                    {{-- Destinataires --}}
                    <div id="userSelectionBlock" class="form-group">
                        <label class="msg-label" for="userSearch">{{ $isParent ? 'Destinataire' : 'Destinataires' }}</label>

                        <div class="msg-search msg-search-sm">
                            <i class="fas fa-search"></i>
                            <input type="text" id="userSearch" class="form-control" autocomplete="off"
                                   placeholder="{{ $isParent
                                        ? 'Rechercher un professeur, un directeur, un parent...'
                                        : 'Rechercher par nom...' }}">
                        </div>

                        <div id="userResults" class="user-results" style="display:none;"></div>
                        <div id="selectedUsers" class="selected-users"></div>
                    </div>

                    <div id="recipientInfo" class="msg-alert" style="display:none;">
                        <i class="fas fa-info-circle"></i>
                        <span id="recipientInfoText"></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="msg-label" for="subject">Sujet</label>
                            <input type="text" name="subject" id="subject" class="form-control" maxlength="255" placeholder="Objet du message">
                        </div>

                        <div class="form-group col-md-5">
                            <label class="msg-label" for="eleve_id">Élève concerné <span class="optional">facultatif</span></label>
                            <select name="eleve_id" id="eleve_id" class="form-control">
                                <option value="">Aucun élève</option>
                                @foreach($isParent
                                    ? $authUser->enfants
                                    : \App\Models\Eleve::orderBy('nom')->orderBy('prenom')->get() as $eleve)
                                    <option value="{{ $eleve->id }}">{{ $eleve->nom }} {{ $eleve->prenom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="msg-label" for="body">Message</label>
                        <textarea name="body" id="body" class="form-control" rows="5" maxlength="10000" required
                                  placeholder="Écrivez votre message..."></textarea>
                        <div class="counter"><span id="bodyCounter">0</span> / 10 000</div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="msg-btn-ghost" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="msg-btn-solid" id="sendMessageButton">
                        <i class="fas fa-paper-plane mr-2"></i> Envoyer
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- ================================================================
     CSS
================================================================ --}}
<style>
/* ⚙️ Ajustez ces 2 valeurs si besoin (hauteur navbar / footer fixe) */
.msg-app{
    --nav-h:57px;
    --footer-h:46px;
    --mp:#2b7bb9;
    --mp-dark:#0b1f33;
    --mp-soft:#eaf3fb;
    --danger:#e74c3c;
    --border:#e6edf3;
    --text:#1d2f3f;
    --muted:#6b7f90;

    display:flex;flex-direction:column;gap:12px;
    height:calc(100vh - var(--nav-h) - var(--footer-h) - 24px);
    color:var(--text);
}

/* ---------- Zone fixe ---------- */
.msg-top{flex:0 0 auto;display:flex;flex-direction:column;gap:12px;}

.msg-banner{
    display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;
    padding:14px 20px;border-radius:16px;color:#fff;
    background:linear-gradient(135deg,#0b1f33,#102c44);
    box-shadow:0 10px 26px rgba(11,31,51,.25);
    margin-top: 25px;
}
.msg-banner-left{display:flex;align-items:center;gap:14px;}
.msg-banner-icon{
    width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.12);font-size:19px;color:#6dd5fa;
}
.msg-banner-title{margin:0;font-weight:800;font-size:1.25rem;letter-spacing:.3px;}
.msg-banner-sub{font-size:.8rem;color:rgba(255,255,255,.65);}
.msg-banner-right{display:flex;align-items:center;gap:12px;}
.msg-unread-pill{
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
    border-radius:999px;padding:5px 13px;font-size:.8rem;
}
.msg-unread-pill i{font-size:.45rem;color:#ff6b6b;vertical-align:middle;margin-right:4px;}
.msg-btn-new{
    border:0;border-radius:12px;padding:10px 18px;font-weight:700;font-size:.88rem;cursor:pointer;
    background:#fff;color:var(--mp-dark);display:inline-flex;align-items:center;gap:8px;
    transition:transform .18s, box-shadow .18s;
}
.msg-btn-new:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(0,0,0,.25);}

.msg-toolbar{
    display:flex;align-items:center;gap:14px;flex-wrap:wrap;
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:10px 12px;
    box-shadow:0 1px 3px rgba(16,24,40,.05);
}
.msg-search{position:relative;flex:1 1 280px;}
.msg-search > i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9fb0be;font-size:13px;pointer-events:none;}
.msg-search .form-control{
    height:42px;padding-left:38px;padding-right:36px;border:1px solid var(--border);border-radius:11px;
    background:#f6f9fc;font-size:14px;box-shadow:none;transition:all .18s;
}
.msg-search .form-control:focus{background:#fff;border-color:var(--mp);box-shadow:0 0 0 4px rgba(43,123,185,.12);}
.msg-search-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:none;color:#9fb0be;padding:4px 8px;}
.msg-search-clear:hover{color:var(--danger);}

.msg-tabs{display:flex;gap:4px;padding:4px;background:#f1f5f9;border-radius:12px;overflow-x:auto;max-width:100%;}
.msg-filter{
    border:0;background:transparent;color:var(--muted);font-size:.82rem;font-weight:600;white-space:nowrap;
    border-radius:9px;padding:7px 12px;display:inline-flex;align-items:center;gap:6px;transition:all .18s;
}
.msg-filter i{font-size:.72rem;}
.msg-filter:hover{color:var(--text);}
.msg-filter.active{background:#fff;color:var(--mp);box-shadow:0 2px 6px rgba(16,24,40,.1);}
.msg-count{background:#e2e8ee;color:#58697a;border-radius:999px;padding:0 7px;font-size:.7rem;font-weight:700;}
.msg-filter.active .msg-count{background:var(--mp);color:#fff;}

/* ---------- Zone qui défile ---------- */
.msg-panel{
    flex:1 1 auto;min-height:0;display:flex;flex-direction:column;
    background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;
    box-shadow:0 1px 3px rgba(16,24,40,.05);
}
.msg-scroll{flex:1 1 auto;min-height:0;overflow-y:auto;overscroll-behavior:contain;}
.msg-scroll::-webkit-scrollbar{width:8px;}
.msg-scroll::-webkit-scrollbar-thumb{background:#cdd8e2;border-radius:8px;}
.msg-scroll::-webkit-scrollbar-thumb:hover{background:#aebdca;}

/* ---------- Conversation ---------- */
.conversation-item{
    display:flex;align-items:flex-start;gap:14px;padding:15px 20px;position:relative;
    border-bottom:1px solid #f0f4f8;text-decoration:none;color:inherit;transition:background .15s;
}
.conversation-item:last-child{border-bottom:0;}
.conversation-item:hover{background:#f7fafd;text-decoration:none;color:inherit;}
.conversation-item.is-unread{background:#f3f9ff;}
.conversation-item.is-unread::before{content:"";position:absolute;left:0;top:10px;bottom:10px;width:4px;border-radius:0 4px 4px 0;background:var(--mp);}

.conversation-avatar-wrap{flex-shrink:0;}
.conversation-avatar{
    width:46px;height:46px;border-radius:50%;object-fit:cover;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:17px;color:#fff;transition:transform .18s;
}
.avatar-direct{background:linear-gradient(135deg,#1c3f5e,#295f7d);}
.avatar-group{background:linear-gradient(135deg,#38bdf8,#0284c7);}
.avatar-announcement{background:linear-gradient(135deg,#fbbf24,#f59e0b);}
.conversation-item:hover .conversation-avatar{transform:scale(1.05);}

.conversation-body{flex:1 1 auto;min-width:0;}
.conversation-line{display:flex;justify-content:space-between;align-items:center;gap:10px;}
.conversation-title{font-weight:600;font-size:.95rem;}
.is-unread .conversation-title{font-weight:800;}
.conversation-date{font-size:.75rem;color:var(--muted);white-space:nowrap;}
.is-unread .conversation-date{color:var(--mp);font-weight:700;}

.conversation-tags{display:flex;gap:6px;flex-wrap:wrap;margin:5px 0 3px;}
.tag{font-size:.68rem;font-weight:700;border-radius:7px;padding:2px 8px;display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;color:#52667a;}
.tag-direct{background:var(--mp-soft);color:#1f6aa5;}
.tag-group{background:#e0f2fe;color:#0369a1;}
.tag-announcement{background:#fef3c7;color:#b45309;}
.tag-eleve{background:#dcfce7;color:#15803d;}

.conversation-participants{font-size:.76rem;color:var(--muted);margin-bottom:2px;}
.conversation-participants .more{color:var(--mp);font-weight:700;}
.conversation-preview{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--muted);}
.conversation-preview > span:first-child{min-width:0;}
.conversation-preview .sender{color:#9fb0be;}
.is-unread .conversation-preview{color:#2c3f50;font-weight:500;}
.badge-unread{background:var(--danger);color:#fff;font-size:.65rem;font-weight:800;padding:2px 8px;border-radius:999px;text-transform:uppercase;letter-spacing:.3px;flex-shrink:0;}
.conversation-chevron{align-self:center;color:#cbd5df;font-size:.75rem;flex-shrink:0;opacity:0;transform:translateX(-4px);transition:all .18s;}
.conversation-item:hover .conversation-chevron{opacity:1;transform:translateX(0);}

/* ---------- États vides ---------- */
.msg-empty{text-align:center;padding:60px 20px;}
.msg-empty-icon{width:70px;height:70px;margin:0 auto 16px;border-radius:50%;background:#f1f5f9;color:#9fb0be;font-size:26px;display:flex;align-items:center;justify-content:center;}
.msg-empty h5{font-weight:700;margin-bottom:6px;}
.msg-empty p{color:var(--muted);font-size:.9rem;margin-bottom:18px;}

/* ---------- Boutons ---------- */
.msg-btn-solid{
    border:0;color:#fff;font-weight:700;font-size:.9rem;padding:10px 22px;border-radius:12px;cursor:pointer;
    background:linear-gradient(135deg,#1c3f5e,#2b7bb9);box-shadow:0 6px 16px rgba(43,123,185,.3);transition:all .18s;
}
.msg-btn-solid:hover{transform:translateY(-1px);color:#fff;}
.msg-btn-solid:disabled{opacity:.7;transform:none;cursor:not-allowed;}
.msg-btn-ghost{border:0;background:#eef2f6;color:#44586a;font-weight:600;font-size:.9rem;padding:10px 20px;border-radius:12px;cursor:pointer;}
.msg-btn-ghost:hover{background:#e0e7ee;}

/* ---------- Modal ---------- */
.msg-modal .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(11,31,51,.35);}
.msg-modal .modal-header{
    display:flex;justify-content:space-between;align-items:center;border:0;padding:18px 24px;color:#fff;
    background:linear-gradient(135deg,#0b1f33,#102c44);
}
.msg-modal .modal-icon{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.12);color:#6dd5fa;display:flex;align-items:center;justify-content:center;margin-right:12px;}
.msg-modal .modal-title{font-weight:700;font-size:1.05rem;}
.msg-modal .modal-sub{font-size:.76rem;color:rgba(255,255,255,.65);}
.msg-close{background:rgba(255,255,255,.12);border:0;color:#fff;width:34px;height:34px;border-radius:50%;font-size:1.3rem;line-height:1;cursor:pointer;}
.msg-close:hover{background:rgba(255,255,255,.25);}
.msg-modal .modal-body{padding:22px 24px;max-height:68vh;overflow-y:auto;}
.msg-modal .modal-footer{border-top:1px solid var(--border);padding:14px 24px;}
.msg-modal .form-control{border:1px solid var(--border);border-radius:11px;background:#f6f9fc;font-size:.9rem;box-shadow:none;}
.msg-modal .form-control:focus{background:#fff;border-color:var(--mp);box-shadow:0 0 0 4px rgba(43,123,185,.12);}
.msg-modal .msg-search .form-control{padding-left:38px;}
.msg-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#5b7184;display:block;margin-bottom:5px;}
.optional{font-weight:500;text-transform:none;letter-spacing:0;color:#9fb0be;font-size:.72rem;}
.counter{text-align:right;font-size:.72rem;color:#9fb0be;margin-top:5px;}

.recipient-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px;}
.recipient-option{
    position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
    padding:12px 6px;margin:0;background:#f6f9fc;border:1.5px solid var(--border);border-radius:12px;
    cursor:pointer;user-select:none;transition:all .18s;
}
.recipient-option input{position:absolute;opacity:0;pointer-events:none;}
.recipient-icon{width:32px;height:32px;border-radius:10px;background:#e3edf6;color:var(--mp);display:flex;align-items:center;justify-content:center;font-size:.85rem;transition:all .18s;}
.recipient-text{font-size:.74rem;font-weight:700;color:#52667a;text-align:center;line-height:1.1;}
.recipient-option:hover{border-color:#b4d0e6;background:#f0f7fd;}
.recipient-option.active{border-color:var(--mp);background:var(--mp-soft);box-shadow:0 3px 10px rgba(43,123,185,.15);}
.recipient-option.active .recipient-icon{background:var(--mp);color:#fff;}
.recipient-option.active .recipient-text{color:#1f5f94;}
@media (max-width:900px){ .recipient-grid{grid-template-columns:repeat(3,1fr);} }

.user-results{border:1px solid var(--border);border-radius:12px;margin-top:8px;max-height:230px;overflow-y:auto;background:#fff;box-shadow:0 8px 20px rgba(16,24,40,.08);}
.user-result{width:100%;border:0;background:none;text-align:left;padding:10px 14px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #f3f6f9;transition:background .15s;}
.user-result:last-child{border-bottom:0;}
button.user-result:hover{background:var(--mp-soft);}
.user-result .avatar{width:36px;height:36px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#1c3f5e,#295f7d);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;}
.user-result strong{font-size:.85rem;display:block;}
.user-result small{font-size:.72rem;color:var(--muted);}

.selected-users{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
.selected-count{width:100%;font-size:.75rem;color:var(--muted);font-weight:700;}
.chip{display:inline-flex;align-items:center;gap:8px;background:var(--mp-soft);color:#1f5f94;border-radius:999px;padding:6px 12px;font-size:.78rem;font-weight:700;}
.chip small{color:#5b95c2;font-weight:500;}
.chip .remove-user{border:0;background:none;color:#8db5d3;padding:0;line-height:1;}
.chip .remove-user:hover{color:var(--danger);}

.msg-alert{background:#e8f4fd;color:#0b5c8f;border-radius:12px;padding:12px 14px;font-size:.84rem;display:flex;gap:10px;align-items:center;margin-bottom:1rem;}

/* ---------- Responsive ---------- */
@media (max-width:767px){
    .msg-app{gap:8px;}
    .msg-banner{padding:10px 14px;}
    .msg-banner-sub,.msg-btn-new span{display:none;}
    .msg-banner-icon{width:40px;height:40px;}
    .msg-toolbar{padding:8px;gap:8px;}
    .conversation-item{padding:13px 12px;gap:11px;}
    .conversation-avatar{width:42px;height:42px;}
    .conversation-chevron{display:none;}
}
</style>


{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
@push('scripts')
<script>
$(function () {

    const CURRENT_USER_ID = {{ (int) auth()->id() }};
    const IS_PARENT       = @json($isParent);

    let selectedUsers = {};
    let activeFilter  = 'all';

    function escapeHtml(value) { return $('<div>').text(value ?? '').html(); }
    function currentType() { return IS_PARENT ? 'individual' : $('input[name="recipient_type"]:checked').val(); }

    /* ---------------- Compteurs des onglets ---------------- */
    function updateCounts() {
        const $items = $('#conversationList .conversation-item');
        const unread = $items.filter(function () { return $(this).data('unread') == 1; }).length;

        $('[data-count="all"]').text($items.length);
        ['direct', 'group', 'announcement'].forEach(function (t) {
            $('[data-count="' + t + '"]').text($items.filter('[data-type="' + t + '"]').length);
        });
        $('[data-count="unread"]').text(unread);

        $('#unreadTotal').text(unread);
        $('#unreadPill').toggle(unread > 0);
    }

    /* ---------------- Recherche + filtres ---------------- */
    function applyFilters() {
        const query = $('#searchConversation').val().trim().toLowerCase();
        let visible = 0;

        $('#conversationList .conversation-item').each(function () {
            const $item     = $(this);
            const matchText = !query || ($item.data('search') || '').toString().includes(query);
            const matchType =
                activeFilter === 'all'    ? true :
                activeFilter === 'unread' ? $item.data('unread') == 1 :
                $item.data('type') === activeFilter;

            const show = matchText && matchType;
            $item.toggle(show);
            if (show) visible++;
        });

        const total = $('#conversationList .conversation-item').length;
        $('#noConversationFound').toggleClass('d-none', visible > 0 || total === 0);
        $('#clearSearch').toggleClass('d-none', query.length === 0);
    }

    $('#searchConversation').on('input', applyFilters);

    $('#clearSearch').on('click', function () {
        $('#searchConversation').val('').focus();
        applyFilters();
    });

    $('.msg-filter').on('click', function () {
        $('.msg-filter').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('filter');
        applyFilters();
        $('.msg-scroll').scrollTop(0);
    });

    updateCounts();

    /* ---------------- Type de destinataires ---------------- */
    $('.recipient-option input').on('change', function () {
        $('.recipient-option').removeClass('active');
        $(this).closest('.recipient-option').addClass('active');

        const type = $(this).val();

        $('#userSearch').val('');
        $('#userResults').hide().empty();

        if (type === 'individual' || type === 'selected') {
            $('#userSelectionBlock').show();
            $('#recipientInfo').hide();
        } else {
            $('#userSelectionBlock').hide();
            $('#recipientInfo').show();

            const messages = {
                parents:        'Le message sera envoyé à tous les parents actifs (vous êtes exclu automatiquement).',
                teachers:       'Le message sera envoyé à tous les professeurs actifs (vous êtes exclu automatiquement).',
                administration: 'Le message sera envoyé à tout le personnel administratif (vous êtes exclu automatiquement).',
                all:            'Le message sera envoyé à tous les utilisateurs de l’établissement (vous êtes exclu automatiquement).'
            };
            $('#recipientInfoText').text(messages[type] || '');
        }

        if (type === 'individual' && Object.keys(selectedUsers).length > 1) selectedUsers = {};
        updateSelectedUsers();
    });

    /* ---------------- Recherche utilisateurs ---------------- */
    let searchTimer = null;

    $('#userSearch').on('input', function () {
        const query = $(this).val().trim();
        clearTimeout(searchTimer);

        if (query.length < 2) { $('#userResults').hide().empty(); return; }

        searchTimer = setTimeout(function () {
            $.ajax({
                url:  "{{ route('messages.searchUsers') }}",
                type: "GET",
                data: { q: query },
                success: function (users) {
                    let html = '';
                    const available = users.filter(u => Number(u.id) !== CURRENT_USER_ID && !selectedUsers[u.id]);

                    if (!available.length) {
                        html = '<div class="user-result text-muted">Aucun utilisateur trouvé.</div>';
                    } else {
                        available.forEach(function (user) {
                            const initial = (user.name || '?').charAt(0).toUpperCase();
                            html += `
                                <button type="button" class="user-result"
                                        data-id="${user.id}"
                                        data-name="${escapeHtml(user.name)}"
                                        data-role="${escapeHtml(user.role)}">
                                    <span class="avatar">${escapeHtml(initial)}</span>
                                    <span>
                                        <strong>${escapeHtml(user.name)}</strong>
                                        <small>${escapeHtml(user.role)}</small>
                                    </span>
                                </button>`;
                        });
                    }
                    $('#userResults').html(html).show();
                },
                error: function (xhr) { console.error('Erreur recherche utilisateurs :', xhr.responseText); }
            });
        }, 300);
    });

    /* ---------------- Sélection / suppression ---------------- */
    $(document).on('click', 'button.user-result', function () {
        const id = Number($(this).data('id'));
        if (!id || id === CURRENT_USER_ID) return;

        if (IS_PARENT || currentType() === 'individual') selectedUsers = {};

        selectedUsers[id] = { id: id, name: $(this).data('name'), role: $(this).data('role') };

        $('#userSearch').val('');
        $('#userResults').hide().empty();
        updateSelectedUsers();
    });

    $(document).on('click', '.remove-user', function () {
        delete selectedUsers[$(this).data('id')];
        updateSelectedUsers();
    });

    function updateSelectedUsers() {
        const container = $('#selectedUsers').empty();
        const count     = Object.keys(selectedUsers).length;

        if (count > 1) {
            container.append(`<div class="selected-count"><i class="fas fa-users mr-1"></i> ${count} destinataires sélectionnés</div>`);
        }

        Object.values(selectedUsers).forEach(function (user) {
            container.append(`
                <span class="chip">
                    <i class="fas fa-user"></i>
                    ${escapeHtml(user.name)}
                    <small>(${escapeHtml(user.role)})</small>
                    <button type="button" class="remove-user" data-id="${user.id}"><i class="fas fa-times"></i></button>
                    <input type="hidden" name="user_ids[]" value="${user.id}">
                </span>`);
        });
    }

    /* ---------------- Compteur de caractères ---------------- */
    $('#body').on('input', function () {
        $('#bodyCounter').text($(this).val().length.toLocaleString('fr-FR'));
    });

    /* ---------------- Validation à l'envoi ---------------- */
    $('form[action="{{ route('messages.store') }}"]').on('submit', function (e) {
        const type = currentType();

        if ((type === 'individual' || type === 'selected') && Object.keys(selectedUsers).length === 0) {
            e.preventDefault();
            $('#userSearch').focus();
            alert('Veuillez sélectionner au moins un destinataire.');
            return false;
        }

        $('#sendMessageButton').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-2"></i> Envoi en cours...');
    });

    /* ---------------- Reset à la fermeture ---------------- */
    $('#newMessageModal').on('hidden.bs.modal', function () {
        selectedUsers = {};
        updateSelectedUsers();
        $('#userResults').hide().empty();
        $('#userSearch').val('');
    });

});
</script>
@endpush

@endsection
