<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Afficher le formulaire de mot de passe oublié.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoyer le lien de réinitialisation.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'max:255',
            ],
        ], [
            'email.required' => 'Veuillez saisir votre email, username ou matricule.',
        ]);

        $input = trim($request->email);

        /*
        |--------------------------------------------------------------------------
        | Recherche de l'utilisateur
        |--------------------------------------------------------------------------
        */

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {

            $user = User::where('email', $input)->first();

        } elseif (preg_match('/^(SP|ENS)/i', $input)) {

            $user = User::where('matricule', $input)->first();

        } else {

            $user = User::where('username', $input)->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Compte introuvable
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            logAction(
                'Mot de passe oublié',
                "Compte introuvable pour l'identifiant : {$input}"
            );

            return back()
                ->withInput()
                ->with('danger', 'Aucun compte ne correspond à cet identifiant.');
        }

        /*
        |--------------------------------------------------------------------------
        | L'utilisateur doit avoir un email
        |--------------------------------------------------------------------------
        */

        if (empty($user->email)) {

            return back()
                ->withInput()
                ->with(
                    'danger',
                    'Ce compte ne possède aucune adresse email. Veuillez contacter l’administration.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Vérifier que le compte est actif
        |--------------------------------------------------------------------------
        */

        if (!$user->status) {

            return back()
                ->withInput()
                ->with(
                    'danger',
                    'Ce compte est désactivé. Veuillez contacter l’administration.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Envoi du lien
        |--------------------------------------------------------------------------
        */

        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        if ($status === Password::RESET_LINK_SENT) {

            logAction(
                'Demande de réinitialisation',
                "Lien de réinitialisation envoyé à {$user->email}"
            );

            return back()->with(
                'success',
                'Un lien de réinitialisation a été envoyé à votre adresse email.'
            );
        }

        logAction(
            'Échec réinitialisation',
            "Impossible d'envoyer le lien à {$user->email}"
        );

        return back()->with(
            'danger',
            'Impossible d’envoyer le lien de réinitialisation. Veuillez réessayer.'
        );
    }
}