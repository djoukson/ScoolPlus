<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
          

        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // 3️⃣ Si tout est bon, exécuter le reste
        $users = User::orderBy('id', 'desc')->get();

        logAction('Consultation', 'Consultation de la liste complète des utilisateurs');

        return view('users.index', compact('users'));
    }



    public function store(Request $request)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

       
        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255',
                'email'      => 'nullable|email|unique:users,email',
                'role'       => 'required|in:admin,professeur,comptable,directeur,secretaire,parent',
                'phone'      => 'nullable|string|max:30',
                'username'   => 'nullable|string|max:50|unique:users,username',
                'sexe'       => 'nullable|in:Masculin,Feminin',
                'profileimg' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ]);
            $temporaryPassword = bin2hex(random_bytes(16));
            $validated['status'] = 0;
            $validated['must_change_password'] = true;
            $validated['matricule'] = User::generateMatricule('USSP');
            $validated['password'] = Hash::make($temporaryPassword);
 
            $directory = storage_path('app/public/profile_images');
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            if ($request->hasFile('profileimg')) {
                $file = $request->file('profileimg');
                $filename = 'user_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile_images', $filename, 'public');
                $validated['profileimg'] = 'storage/profile_images/' . $filename;
            }


    $user = User::create($validated);


            logAction('Création', "Ajout d’un nouvel utilisateur : {$user->name} avec le rôle {$user->role}");

            $response = redirect()->route('users')->with('success', 'Utilisateur ajouté avec succès ✅');
            if ($temporaryPassword !== null) {
                $response->with('temporary_password', $temporaryPassword);
            }

            return $response;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Échec lors de l’ajout : ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(User $user)
    {
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $users = User::orderBy('id', 'desc')->get();

        logAction('Consultation', "Consultation des informations de l’utilisateur : {$user->name}");

        return view('users.index', compact('users', 'user'));
    }

    public function update(Request $request, $id)
    {
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name'       => 'nullable|string|max:255',
                'email'      => 'nullable|email|unique:users,email,' . $user->id,
                'password'   => 'nullable|string|min:8',
                'role'       => 'required|in:admin,professeur,comptable,directeur,secretaire,parent',
                'phone'      => 'nullable|string|max:30',
                'username'   => 'nullable|string|max:50|unique:users,username,' . $user->id,
                'sexe'       => 'nullable|in:Masculin,Feminin',
                'profileimg' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ]);

            $temporaryPassword = null;
            if (!empty($validated['password'])) {
                $temporaryPassword = $validated['password'];
                $validated['password'] = Hash::make($validated['password']);
                $validated['must_change_password'] = true;
                $validated['remember_token'] = Str::random(60);
                $validated['session_version'] = ((int) $user->session_version) + 1;
            } else {
                unset($validated['password']);
            }

            $directory = storage_path('app/public/profile_images');
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            if ($request->hasFile('profileimg')) {
                if (!empty($user->profileimg)) {
                    $oldPath = str_replace('storage/', 'public/', $user->profileimg);
                    if (Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }

                $file = $request->file('profileimg');
                $filename = 'user_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile_images', $filename, 'public');
                $validated['profileimg'] = 'storage/profile_images/' . $filename;
            }

            $user->update($validated);

            logAction('Modification', "Mise à jour de l’utilisateur : {$user->name} ({$user->role})");

            $response = redirect()->route('users')->with('success', 'Utilisateur mis à jour avec succès ✅');
            if ($temporaryPassword !== null) {
                $response->with('temporary_password', $temporaryPassword);
            }

            return $response;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Échec lors de la mise à jour : ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(User $user)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $nom = $user->name;
        $user->delete();

        logAction('Suppression', "Suppression de l’utilisateur : $nom");

        return redirect()->route('users')->with('success', 'Utilisateur supprimé ✅');
    }

    public function login()
    {
      

        logAction('Consultation', 'Accès à la page de connexion');
        return view('users.login');
    }

    public function verifylogins(Request $request)
{
    $request->validate([
        'email'    => 'required|string',
        'password' => 'required|string',
    ]);

    $ipAttemptKey = 'login-ip-failures:' . hash('sha256', $request->ip());

    $loginInput = $request->email;

    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $user = User::where('email', $loginInput)->first();
    } elseif (preg_match('/^(SP|ENS)/i', $loginInput)) {
        $user = User::where('matricule', $loginInput)->first();
    } else {
        $user = User::where('username', $loginInput)->first();
    }

    $attemptKey = 'login-failures:' . ($user
        ? 'user:' . $user->id
        : 'identifier:' . hash('sha256', mb_strtolower(trim($loginInput))));

    // Se souvenir de moi
    $remember = $request->boolean('remember');

    if ($user && Hash::check($request->password, $user->password)) {
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($ipAttemptKey);

        if (!$user->status) {
            logAction(
                'Connexion refusée',
                "Utilisateur désactivé : {$user->name} ({$user->role})"
            );

            return back()->with(
                'danger',
                'Votre compte est désactivé. Veuillez contacter l’administration ❌'
            );
        }

        // Connexion avec ou sans "Se souvenir de moi"
        Auth::login($user, $remember);

        $request->session()->regenerate();
        $request->session()->put('auth_session_version', (int) $user->session_version);

        logAction(
            'Connexion',
            "Connexion réussie de {$user->name} ({$user->role})"
        );

        $request->session()->put(
            'user_identifier',
            $user->email ?? $user->username ?? $user->matricule
        );

        $request->session()->put(
            'user_role',
            $user->role ?? 'undefined'
        );

        if ($user->must_change_password) {
            return redirect()
                ->route('settings.index', ['tab' => 'securite'])
                ->with(
                    'warning',
                    'Veuillez changer votre mot de passe temporaire avant de continuer.'
                );
        }

        return redirect()
            ->intended('/')
            ->with(
                'success',
                'Connexion réussie. Bienvenue ' . $user->name . ' 👋'
            );
    }

    logAction(
        'Échec de connexion',
        "Tentative échouée avec identifiant : $loginInput"
    );

    if (RateLimiter::tooManyAttempts($ipAttemptKey, 10)) {
        throw new ThrottleRequestsException(
            'Trop de tentatives de connexion.',
            null,
            ['Retry-After' => (string) RateLimiter::availableIn($ipAttemptKey)]
        );
    }

    RateLimiter::hit($ipAttemptKey, 60);

    RateLimiter::hit($attemptKey, 15 * 60);
    $failedAttempts = RateLimiter::attempts($attemptKey);
    $remainingAttempts = max(0, 5 - $failedAttempts);

    if ($user && $user->status && $remainingAttempts === 0) {
        $user->status = 0;
        $user->save();

        logAction(
            'Compte désactivé',
            "Désactivation automatique après cinq échecs de connexion : {$user->name} ({$user->role})"
        );

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('danger', 'Compte désactivé après 5 tentatives échouées. Veuillez contacter l’administration.');
    }

    return back()
        ->withInput($request->only('email', 'remember'))
        ->with(
            'danger',
            "Les informations de connexion sont incorrectes. Il vous reste {$remainingAttempts} tentative(s)."
        );
}

    public function logoutt(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            logAction('Déconnexion', "Déconnexion de {$user->name}");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function resetPassword($id)
    {
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        try {
            $user = User::findOrFail($id);
            $temporaryPassword = bin2hex(random_bytes(16));
            $user->password = Hash::make($temporaryPassword);
            $user->must_change_password = true;
            $user->remember_token = Str::random(60);
            $user->session_version = ((int) $user->session_version) + 1;
            $user->save();

            logAction('Réinitialisation mot de passe', "Mot de passe réinitialisé pour {$user->name}");

            return redirect()->route('users')
                ->with('success', 'Mot de passe réinitialisé pour ' . $user->name)
                ->with('temporary_password', $temporaryPassword);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    public function userlogs(Request $request, $iduser = null)
    {
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $query = UserLog::with('user')->latest();

        // 🔹 Filtrage par utilisateur
        if (!empty($iduser)) {
            $query->where('user_id', $iduser);
        }

        // 🔹 Filtrage par intervalle de dates (avec inclusion des bornes)
        if ($request->filled('start') && $request->filled('end')) {
            $start = date('Y-m-d 00:00:00', strtotime($request->start));
            $end = date('Y-m-d 23:59:59', strtotime($request->end));

            $query->whereBetween('created_at', [$start, $end]);
        }

        // 🔹 Résultats paginés
        $logs = $query->paginate(500);

        // 🔹 Récupération des dates uniques disponibles (pour la liste déroulante)
        $dates = \App\Models\UserLog::selectRaw('DATE(created_at) as date')
            ->when($iduser, fn($q) => $q->where('user_id', $iduser))
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        // 🔹 Enregistrement de l’action dans les logs du système
        logAction(
            'Consultation',
            $iduser
                ? "Consultation des journaux d’activité de l’utilisateur #$iduser"
                : "Consultation des journaux d’activité de tous les utilisateurs"
        );

        return view('users.userlogs', compact('logs', 'iduser', 'dates'));
    }




    public function print(Request $request, $iduser = null)
    {
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $query = UserLog::query();

        // Filtrer par utilisateur si fourni (comme dans userlogs)
        if (!empty($iduser)) {
            $query->where('user_id', $iduser);
        }

        $start = $request->input('start');
        $end   = $request->input('end');

        if ($start && $end) {
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate   = Carbon::parse($end)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // 🔹 Fournir $dates à la vue (mêmes règles que dans userlogs)
        $datesQuery = UserLog::selectRaw('DATE(created_at) as date')
            ->when($iduser, fn($q) => $q->where('user_id', $iduser))
            ->distinct()
            ->orderBy('date', 'desc');

        $dates = $datesQuery->pluck('date');

        return view('users.printuserlog', [
            'logs'   => $logs,
            'start'  => $start,
            'end'    => $end,
            'dates'  => $dates,
            'iduser' => $iduser,
        ]);
    }

    public function toggleStatus($id)
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'directeur'], true),
            403,
            'Accès réservé à l’administration.'
        );

        $user = User::findOrFail($id);

        // Sécurité : empêcher la désactivation de soi-même (optionnel)
        if (auth()->id() === $user->id) {
            return back()->with('danger', 'Vous ne pouvez pas désactiver votre propre compte ❌');
        }

        $user->status = !$user->status;
        $temporaryPassword = null;
        if ($user->status && $user->role === 'professeur') {
            $temporaryPassword = bin2hex(random_bytes(16));
            $user->password = Hash::make($temporaryPassword);
            $user->must_change_password = true;
            $user->remember_token = Str::random(60);
            $user->session_version = ((int) $user->session_version) + 1;
        }
        $user->save();

        if ($user->role === 'professeur' && $user->matricule) {
            \App\Models\Enseignant::where('matricule', $user->matricule)
                ->update(['statut' => $user->status]);
        }

        logAction(
            'Changement de statut',
            "Utilisateur {$user->name} " . ($user->status ? 'activé' : 'désactivé')
        );

        $response = back()->with(
            'success',
            $user->status
                ? 'Utilisateur activé avec succès ✅'
                : 'Utilisateur désactivé avec succès 🚫'
        );
        if ($temporaryPassword !== null) {
            $response->with('temporary_password', $temporaryPassword);
        }

        return $response;
    }


}
