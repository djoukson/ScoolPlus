<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return redirect()->back()->with('info', 'Notification lue.');
    }

   /* public function markAllRead()
    {
        $user = Auth::user();

        Notification::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->update(['is_read' => 1]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }*/
        
public function markAsRead($id)
{
    $user = Auth::user();

    Notification::where('id', $id)
        ->where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
        ->update(['is_read' => 1]);

    return response()->json(['ok' => true]);
}


    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->latest()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Compteurs temps réel (appelé toutes les 5 s par la navbar).
     * Renvoie aussi l'id du dernier élément non lu : c'est lui qui
     * permet de détecter un NOUVEAU message / une NOUVELLE notification.
     */
    public function counts()
    {
        $user = Auth::user();

        $notifBase = Notification::where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->where('is_read', 0);

        $messages      = 0;
        $lastMessageId = 0;

        try {
            $msgBase = DB::table('messages as m')
                ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'm.conversation_id')
                ->where('cp.user_id', $user->id)
                ->where('m.sender_id', '!=', $user->id)   // si erreur : essayez m.user_id
                ->where(function ($w) {
                    $w->whereNull('cp.last_read_at')
                      ->orWhereColumn('m.created_at', '>', 'cp.last_read_at');
                });

            $messages      = (clone $msgBase)->distinct()->count('m.conversation_id');
            $lastMessageId = (int) (clone $msgBase)->max('m.id');
        } catch (\Throwable $e) {
            Log::error('counts() messages : ' . $e->getMessage());
        }

        return response()
            ->json([
                'notifications'   => (clone $notifBase)->count(),
                'last_notif_id'   => (int) (clone $notifBase)->max('id'),
                'messages'        => $messages,
                'last_message_id' => $lastMessageId,
            ])
            ->header('Cache-Control', 'no-store');
    }
}