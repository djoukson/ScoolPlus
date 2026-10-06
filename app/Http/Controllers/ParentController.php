<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Eleve;
use App\Models\ParentEleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /**
     * Liste des élèves avec leurs informations parentales
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }

       $parents = ParentEleve::with('eleve')
    ->where(function ($query) {
        $query->whereNotNull('pere_email')
              ->where('pere_email', '!=', '')
              ->orWhere(function ($q) {
                  $q->whereNotNull('mere_email')
                    ->where('mere_email', '!=', '');
              });
    })
    ->orderBy('id', 'desc')
    ->get();

        return view('parents.index', compact('parents'));
    }


    /**
     * Créer un compte parent à partir d'un élève
     */
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }

        $validated = $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'email_parent' => 'required|email',
        ]);

        try {

            $parentInfo = ParentEleve::where(
                'eleve_id',
                $validated['eleve_id']
            )->firstOrFail();

            /*
             * Vérifier quel email a été choisi
             */
            $email = null;
            $nomParent = null;
            $relation = 'parents';
            $telephone = null;

            if (
                !empty($parentInfo->pere_email) &&
                strtolower($parentInfo->pere_email) === strtolower($validated['email_parent'])
            ) {
                $email = $parentInfo->pere_email;
                $nomParent = $parentInfo->pere_nom;
                $telephone = $parentInfo->pere_tel;
            }

            elseif (
                !empty($parentInfo->mere_email) &&
                strtolower($parentInfo->mere_email) === strtolower($validated['email_parent'])
            ) {
                $email = $parentInfo->mere_email;
                $nomParent = $parentInfo->mere_nom;
                $telephone = $parentInfo->mere_tel;
            }

            else {
                return back()
                    ->with('error', 'L’adresse email sélectionnée ne correspond pas aux informations du parent.')
                    ->withInput();
            }


            /*
             * Vérifier si cet email existe déjà
             */
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {

                /*
                 * Si le compte existe déjà comme parent,
                 * on peut simplement vérifier le lien avec l'enfant.
                 */
                if ($existingUser->role === 'parent') {

                    $alreadyLinked = DB::table('parent_eleve_user')
                        ->where('user_id', $existingUser->id)
                        ->where('eleve_id', $validated['eleve_id'])
                        ->exists();

                    if ($alreadyLinked) {
                        return back()->with(
                            'warning',
                            'Ce parent possède déjà un compte lié à cet élève.'
                        );
                    }

                    DB::table('parent_eleve_user')->insert([
                        'user_id' => $existingUser->id,
                        'eleve_id' => $validated['eleve_id'],
                        'relation' => 'parents',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    logAction(
                        'Association parent',
                        "L’enfant #{$validated['eleve_id']} a été lié au compte parent {$existingUser->name}"
                    );

                    return back()->with(
                        'success',
                        'L’enfant a été ajouté au compte parent avec succès ✅'
                    );
                }

                /*
                 * L'email appartient déjà à un autre type de compte.
                 */
                return back()
                    ->with(
                        'error',
                        'Cette adresse email est déjà utilisée par un autre compte utilisateur.'
                    )
                    ->withInput();
            }


            /*
             * Générer un matricule parent
             */
            $matricule = User::generateMatricule('PAR');

            $temporaryPassword = bin2hex(random_bytes(16));
            $user = User::create([
                'name' => $nomParent ?: 'Parent',
                'email' => $email,
                'phone' => $telephone,
                'matricule' => $matricule,
                'role' => 'parent',
                'password' => Hash::make($temporaryPassword),
                'status' => true,
                'must_change_password' => true,
            ]);


            /*
             * Liaison parent <-> élève
             */
            DB::table('parent_eleve_user')->insert([
                'user_id' => $user->id,
                'eleve_id' => $validated['eleve_id'],
                'relation' => $relation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            logAction(
                'Création compte parent',
                "Création du compte parent {$user->name} pour l’élève #{$validated['eleve_id']}"
            );


            return back()->with(
                'success',
                "Compte parent créé avec succès ✅ Identifiant : {$email}"
            )->with('temporary_password', $temporaryPassword);

        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    'error' => 'Échec lors de la création du compte parent : ' . $e->getMessage()
                ])
                ->withInput();
        }
    }


    /**
     * Ajouter un autre enfant à un compte parent existant
     */
    public function linkChild(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'eleve_id' => 'required|exists:eleves,id',
        ]);

        $parent = User::where('id', $validated['user_id'])
            ->where('role', 'parent')
            ->firstOrFail();

        $exists = DB::table('parent_eleve_user')
            ->where('user_id', $parent->id)
            ->where('eleve_id', $validated['eleve_id'])
            ->exists();

        if ($exists) {
            return back()->with(
                'warning',
                'Cet enfant est déjà lié à ce compte parent.'
            );
        }

        DB::table('parent_eleve_user')->insert([
            'user_id' => $parent->id,
            'eleve_id' => $validated['eleve_id'],
            'relation' => 'parents',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        logAction(
            'Association parent',
            "Ajout de l’élève #{$validated['eleve_id']} au compte parent {$parent->name}"
        );

        return back()->with(
            'success',
            'Enfant ajouté au compte parent avec succès ✅'
        );
    }
}
