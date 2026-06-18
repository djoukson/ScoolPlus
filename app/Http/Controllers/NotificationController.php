<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return redirect()->back()->with('info', 'Notification lue.');
    }

    public function markAllRead()
    {
        $user = Auth::user();

        Notification::where(function($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->update(['is_read' => 1]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }


    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::where(function($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->latest()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

}
