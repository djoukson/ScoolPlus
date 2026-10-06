<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    /**
     * Afficher le formulaire de nouveau mot de passe.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Réinitialiser le mot de passe.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'email.required' => 'L’adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password) {

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                    'session_version' => ((int) $user->session_version) + 1,
                ])->save();

                event(new PasswordReset($user));

                logAction(
                    'Mot de passe réinitialisé',
                    "Mot de passe réinitialisé pour {$user->name} ({$user->role})"
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.'
                );
        }

        return back()
            ->withInput($request->only('email'))
            ->with(
                'danger',
                'Le lien de réinitialisation est invalide ou a expiré.'
            );
    }
}
