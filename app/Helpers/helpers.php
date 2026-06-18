<?php
use App\Models\UserLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

function logAction($action, $description = null)
{
    if (Auth::check()) {
        UserLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

function envoyerNotification($titre, $message, $type = 'général', $userId = null,$url=null)
{
    Notification::create([
        'user_id' => $userId,
        'type' => $type,
        'titre' => $titre,
        'message' => $message,
        'url' => $url,
    ]);
}
function calculateExpiration($type, $years = null)
{
    if ($type === 'lifetime') return null;
    if ($type === 'test') return now()->addMonths(3);
    return now()->addMonths($years * 12);
}

function generateLicenseKey($school, $years)
{
    $secret = config('app.key');
    $payload = $school .'|'. ($years ?? 'lifetime') .'|'. now()->timestamp;
    $hash = strtoupper(substr(hash_hmac('sha256', $payload, $secret), 0, 25));
    return implode('-', str_split($hash, 5));
}
function bulletinThemeActif()
{
    return \App\Models\BulletinTheme::where('active', true)->first();
}
