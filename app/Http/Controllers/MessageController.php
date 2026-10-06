<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Boîte de réception
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $conversations = $user->conversations()
            ->with([
                'participants',
                'eleve',
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->orderByDesc('updated_at')
            ->get();

        return view('messages.index', compact('conversations'));
    }


    /**
     * Recherche des utilisateurs pour le champ "À"
     */
    public function searchUsers(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        $search = trim($request->input('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $currentUserId = auth()->id();

        $users = User::query()
            ->where('id', '!=', $currentUserId)
            ->where('name', 'LIKE', "%{$search}%")
            ->select([
                'id',
                'name',
                'role',
            ])
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();

        return response()->json(
            $users->map(function ($user) {

                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'role'      => $user->role,
                ];
            })
        );
    }


    /**
     * Afficher une conversation
     */
    public function show($id)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();



        $conversation = Conversation::with([
            'participants',
            'eleve',
            'messages' => function ($query) {
                $query->with([
                    'sender',
                    'replyTo.sender'
                ])
                    ->orderBy('created_at', 'asc');
            }

        ])->findOrFail($id);
        // Sécurité :
        // l'utilisateur doit obligatoirement participer
        // à cette conversation.
        if (!$conversation->participants()
            ->where('users.id', $user->id)
            ->exists()) {

            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette conversation.');
        }

        // Marquer comme lu
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update([
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);

        return view('messages.show', compact('conversation'));
    }


    /**
     * Créer une nouvelle conversation et envoyer le premier message
     */
   public function store(Request $request)
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $validated = $request->validate([

        'recipient_type' => [
            'required',
            'in:individual,selected,parents,teachers,administration,all',
        ],

        'user_ids' => [
            'nullable',
            'array',
        ],

        'user_ids.*' => [
            'integer',
            'exists:users,id',
        ],

        'eleve_id' => [
            'nullable',
            'integer',
            'exists:eleves,id',
        ],

        'subject' => [
            'nullable',
            'string',
            'max:255',
        ],

        'body' => [
            'required',
            'string',
            'max:10000',
        ],

    ]);

    $currentUser = auth()->user();

    $broadcastTypes = [
        'parents',
        'teachers',
        'administration',
        'all',
    ];

    if (
        in_array($validated['recipient_type'], $broadcastTypes, true)
        && !in_array($currentUser->role, ['admin', 'directeur', 'secretaire'], true)
    ) {
        abort(403, 'Seuls les responsables de l’établissement peuvent envoyer une annonce groupée.');
    }

    /*
    |--------------------------------------------------------------------------
    | 1. DÉTERMINER LES DESTINATAIRES
    |--------------------------------------------------------------------------
    */

    $recipientType = $validated['recipient_type'];

    switch ($recipientType) {

        /*
        |--------------------------------------------------------------------------
        | UNE PERSONNE
        |--------------------------------------------------------------------------
        */

        case 'individual':

            $recipientIds = $validated['user_ids'] ?? [];

            if (count($recipientIds) !== 1) {

                return back()
                    ->withInput()
                    ->with(
                        'danger',
                        'Veuillez sélectionner une seule personne.'
                    );
            }

            break;


        /*
        |--------------------------------------------------------------------------
        | PLUSIEURS PERSONNES
        |--------------------------------------------------------------------------
        */

        case 'selected':

            $recipientIds = $validated['user_ids'] ?? [];

            if (empty($recipientIds)) {

                return back()
                    ->withInput()
                    ->with(
                        'danger',
                        'Veuillez sélectionner au moins un destinataire.'
                    );
            }

            break;


        /*
        |--------------------------------------------------------------------------
        | TOUS LES PARENTS
        |--------------------------------------------------------------------------
        */

        case 'parents':

            $recipientIds = User::where('role', 'parent')
                ->where('id', '!=', $currentUser->id)
                ->pluck('id')
                ->toArray();

            break;


        /*
        |--------------------------------------------------------------------------
        | TOUS LES PROFESSEURS
        |--------------------------------------------------------------------------
        */

        case 'teachers':

            $recipientIds = User::where('role', 'professeur')
                ->where('id', '!=', $currentUser->id)
                ->pluck('id')
                ->toArray();

            break;


        /*
        |--------------------------------------------------------------------------
        | PERSONNEL ADMINISTRATIF
        |--------------------------------------------------------------------------
        */

        case 'administration':

            $administrativeRoles = [
                'admin',
                'directeur',
                'secretaire',
                'comptable',
            ];

            $recipientIds = User::whereIn(
                'role',
                $administrativeRoles
            )
            ->where('id', '!=', $currentUser->id)
            ->pluck('id')
            ->toArray();

            break;


        /*
        |--------------------------------------------------------------------------
        | TOUTE L'ÉCOLE
        |--------------------------------------------------------------------------
        */

        case 'all':

            $recipientIds = User::where(
                'id',
                '!=',
                $currentUser->id
            )
            ->pluck('id')
            ->toArray();

            break;


        default:

            return back()
                ->withInput()
                ->with(
                    'danger',
                    'Type de destinataire invalide.'
                );
    }


    /*
    |--------------------------------------------------------------------------
    | 2. NETTOYER LES DESTINATAIRES
    |--------------------------------------------------------------------------
    */

    $recipientIds = array_values(
        array_unique(
            array_map(
                'intval',
                $recipientIds
            )
        )
    );


    /*
    |--------------------------------------------------------------------------
    | 3. NE PAS S'ENVOYER LE MESSAGE À SOI-MÊME
    |--------------------------------------------------------------------------
    */

    $recipientIds = array_values(
        array_filter(
            $recipientIds,
            function ($id) use ($currentUser) {
                return $id !== (int) $currentUser->id;
            }
        )
    );


    if (empty($recipientIds)) {

        return back()
            ->withInput()
            ->with(
                'danger',
                'Aucun destinataire valide.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 4. TYPE DE CONVERSATION
    |--------------------------------------------------------------------------
    */

    if (in_array($recipientType, [
        'parents',
        'teachers',
        'administration',
        'all',
    ])) {

        $conversationType = 'announcement';

    } elseif (count($recipientIds) === 1) {

        $conversationType = 'direct';

    } else {

        $conversationType = 'group';
    }


    /*
    |--------------------------------------------------------------------------
    | 5. CRÉATION DE LA CONVERSATION + MESSAGE
    |--------------------------------------------------------------------------
    */

    try {

        $conversation = DB::transaction(function () use (
            $validated,
            $currentUser,
            $recipientIds,
            $conversationType
        ) {

            /*
            |--------------------------------------------------------------------------
            | Création conversation
            |--------------------------------------------------------------------------
            */

            $conversation = Conversation::create([

                'eleve_id' => $validated['eleve_id'] ?? null,

                'created_by' => $currentUser->id,

                'type' => $conversationType,

                'subject' => $validated['subject'] ?? null,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Ajouter l'expéditeur
            |--------------------------------------------------------------------------
            */

            $participantIds = array_merge(
                [$currentUser->id],
                $recipientIds
            );


            $participantIds = array_values(
                array_unique($participantIds)
            );


            /*
            |--------------------------------------------------------------------------
            | Ajouter tous les participants
            |--------------------------------------------------------------------------
            */

            $conversation->participants()->attach(
                $participantIds
            );


            /*
            |--------------------------------------------------------------------------
            | Créer le message
            |--------------------------------------------------------------------------
            */

            Message::create([

                'conversation_id' => $conversation->id,

                'sender_id' => $currentUser->id,

                'body' => $validated['body'],

                'message_type' => 'text',

            ]);


            /*
            |--------------------------------------------------------------------------
            | Mettre à jour la conversation
            |--------------------------------------------------------------------------
            */

            $conversation->touch();


            return $conversation;
        });


        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        logAction(
            'Messagerie',
            "Message envoyé à {$conversation->participants->count()} participant(s)"
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECTION
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'messages.show',
                $conversation->id
            )
            ->with(
                'success',
                'Message envoyé avec succès ✅'
            );


    } catch (\Exception $e) {

        return back()
            ->withInput()
            ->with(
                'danger',
                'Échec lors de l\'envoi du message : '
                . $e->getMessage()
            );
    }
}

    /**
     * Envoyer un message dans une conversation existante
     */
    public function send(Request $request, $conversationId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'max:10000',
            ],

            'reply_to_id' => [
                'nullable',
                'integer',
                'exists:messages,id',
            ],
        ]);

        $user = auth()->user();

        $conversation = Conversation::findOrFail($conversationId);

        // Vérification de sécurité
        if (!$conversation->participants()
            ->where('users.id', $user->id)
            ->exists()) {

            abort(403, 'Vous n\'êtes pas autorisé à envoyer un message ici.');
        }

        // Si réponse à un message,
        // il doit appartenir à cette conversation.
        if (!empty($validated['reply_to_id'])) {

            $replyExists = Message::where('id', $validated['reply_to_id'])
                ->where('conversation_id', $conversation->id)
                ->exists();

            if (!$replyExists) {
                return back()
                    ->withInput()
                    ->with('danger', 'Le message auquel vous répondez est invalide.');
            }
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'reply_to_id'     => $validated['reply_to_id'] ?? null,
            'body'            => $validated['body'],
            'message_type'    => 'text',
        ]);

        $conversation->touch();

        return redirect()
            ->route('messages.show', $conversation->id)
            ->with('success', 'Message envoyé ✅');
    }


    /**
     * Marquer une conversation comme lue
     */
    public function markAsRead($conversationId)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        $updated = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->update([
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'message' => 'Accès refusé'
            ], 403);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
