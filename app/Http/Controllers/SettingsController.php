<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Vérifie si l'utilisateur a un matricule correspondant à un enseignant
        $enseignant = Enseignant::where('matricule', $user->matricule)->first();

        // 🔒 Log de la consultation du profil
        logAction(
            'Consultation',
            "Consultation du profil utilisateur : {$user->name} (ID #{$user->id}, matricule : {$user->matricule}, rôle : {$user->role })."
        );

        return view('settings.index', compact('user', 'enseignant'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // ✅ Validation des données
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:15',
            'username'   => 'required|string|max:50|unique:users,username,' . $user->id,
            'sexe'       => 'required|string|in:Masculin,Feminin',
            'profileimg' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // ✅ Gestion du répertoire de stockage
        $directory = storage_path('app/public/profile_images');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        // ✅ Gestion de la photo de profil
        if ($request->hasFile('profileimg')) {
            // Supprimer l'ancienne photo si elle existe
            if (!empty($user->profileimg)) {
                $oldPath = str_replace('storage/', 'public/', $user->profileimg);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            // Enregistrer la nouvelle photo
            $file = $request->file('profileimg');
            $filename = 'user_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_images', $filename, 'public');
            $validated['profileimg'] = 'storage/profile_images/' . $filename;
        }

        $ancienProfil = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'username' => $user->username,
            'sexe' => $user->sexe,
        ];

        // ✅ Mise à jour de l'utilisateur
        $user->update($validated);

        // 🔒 Log de la modification de profil
        logAction(
            'Modification',
            "Profil mis à jour pour l'utilisateur {$user->name} (ID #{$user->id}). " .
            "Anciennes infos : " . json_encode($ancienProfil) . " | Nouvelles infos : " . json_encode($validated)
        );

        return back()->with('success', '✅ Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            // 🔒 Log tentative échouée
            logAction(
                'Échec sécurité',
                "Tentative de changement de mot de passe échouée pour {$user->name} (ID #{$user->id}) — ancien mot de passe incorrect."
            );

            return back()->withErrors(['current_password' => '❌ Ancien mot de passe incorrect.']);
        }

        $newSessionVersion = ((int) $user->session_version) + 1;
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
            'session_version' => $newSessionVersion,
        ]);
        $request->session()->put('auth_session_version', $newSessionVersion);

        // 🔒 Log de la mise à jour du mot de passe
        logAction(
            'Modification',
            "Mot de passe mis à jour pour l'utilisateur {$user->name} (ID #{$user->id})."
        );

        return back()->with('success', '🔒 Mot de passe mis à jour avec succès.');
    }

    public function updateTeacherInfo(Request $request)
    {
        $user = auth()->user();
        $enseignant = Enseignant::where('matricule', $user->matricule)->firstOrFail();

        // ✅ Validation des champs modifiables
        $validated = $request->validate([
            'nom'            => 'required|string|max:255',
            'prenom'         => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'tel'            => 'nullable|string|max:30',
            'specialite'     => 'nullable|string|max:100',
            'adresse'        => 'nullable|string|max:255',
            'groupesanguin'  => 'nullable|string|max:10',
            'sexe'           => 'nullable|string|max:10',
        ]);

        $ancien = $enseignant->only(['nom', 'prenom', 'email', 'tel', 'specialite', 'adresse', 'groupesanguin', 'sexe']);

        // ✅ Mise à jour
        $enseignant->update($validated);

        // 🔒 Log de la mise à jour des infos enseignant
        logAction(
            'Modification',
            "Informations de l'enseignant mises à jour pour {$enseignant->nom} {$enseignant->prenom} (Matricule : {$enseignant->matricule}). " .
            "Anciennes données : " . json_encode($ancien) . " | Nouvelles données : " . json_encode($validated)
        );

        return redirect()
            ->route('settings.index')
            ->with('success', 'Les informations de l’enseignant ont été mises à jour avec succès ✅');
    }
}
