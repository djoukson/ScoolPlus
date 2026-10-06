<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\AnneeController;
use App\Http\Controllers\HomeController;
use \App\Http\Controllers\ScolariteController;
use App\Http\Controllers\FraisController;
use App\Http\Controllers\MontantFraisController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\DecoupageController;
use App\Http\Controllers\TypeEvaluationController;
use \App\Http\Controllers\EvaluationController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\ParentEleveController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EmploiDuTempsController;
use \App\Http\Controllers\EcoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BourseController;
use App\Http\Controllers\AttributionController;
use App\Http\Controllers\EpreuveController;
use \App\Http\Controllers\LicenseController;
use \App\Http\Controllers\OfflineLicenseController;
use App\Http\Controllers\AbsenceController;
use Illuminate\Http\Request;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\TauxHoraireController;
use App\Http\Controllers\SalaireController;
use App\Http\Controllers\PresenceEnseignantController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\MessageController;


    Route::middleware(['auth.session'])->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('dashboard');
        Route::middleware('role:admin,directeur')->group(function () {
            Route::get('/ecole', [EcoleController::class, 'index'])->name('ecole.index');
            Route::post('/ecole', [EcoleController::class, 'update'])->name('ecole.update');
            Route::get('/users', [UserController::class, 'index'])->name('users');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset');
            Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])
                ->name('users.toggleStatus');
        });
        Route::middleware('role:admin,directeur')->group(function () {
            Route::get('/userlogs/{iduser?}', [UserController::class, 'userlogs'])->name('userlogs');
            Route::get('/logs/print', [UserController::class, 'print'])->name('logs.print');
        });

    });
    /*
    |--------------------------------------------------------------------------
    | Mot de passe oublié
    |--------------------------------------------------------------------------
    */

        Route::get('/mot-de-passe-oublie', [
            ForgotPasswordController::class,
            'showLinkRequestForm'
        ])->name('password.request');

        Route::post('/mot-de-passe-oublie', [
            ForgotPasswordController::class,
            'sendResetLinkEmail'
        ])->middleware('throttle:3,1')->name('password.email');


    /*
    |--------------------------------------------------------------------------
    | Réinitialisation du mot de passe
    |--------------------------------------------------------------------------
    */

    Route::get('/reinitialiser-mot-de-passe/{token}', [
    ResetPasswordController::class,
    'showResetForm'
    ])->name('password.reset');

    Route::post('/reinitialiser-mot-de-passe', [
    ResetPasswordController::class,
    'reset'
    ])->middleware('throttle:5,1')->name('password.update');


    Route::middleware(['auth.session', 'role:admin,directeur'])->prefix('annees-scolaires')->name('annees.')->group(function () {
        Route::get('/', [AnneeController::class, 'index'])->name('index');
        Route::post('/store', [AnneeController::class, 'store'])->name('store');
        Route::delete('/{annee}', [AnneeController::class, 'destroy'])->name('destroy');
        Route::patch('/change/{id}', [AnneeController::class, 'change'])->name('change');
    });

    Route::get('/login', [App\Http\Controllers\UserController::class, 'login'])->name('login');
    Route::post('/logins', [App\Http\Controllers\UserController::class, 'verifylogins'])
        ->name('verifylogins');
    Route::post('/logoutt', [App\Http\Controllers\UserController::class, 'logoutt'])->name('logoutt');

    // routes/web.php
    Route::middleware(['auth'])->prefix('settings')->group(function () {
    Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.updateProfile');
    Route::put('/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.updatePassword');
    Route::put('/teacher', [\App\Http\Controllers\SettingsController::class, 'updateTeacherInfo'])->name('settings.updateTeacherInfo');

    });




    Route::middleware(['auth.session'])->group(function () {

    // Gestion des classes
    Route::get('/classes', [ClasseController::class, 'index'])->middleware('role:admin,directeur,secretaire')->name('classes.index');
    Route::get('/listeclasses', [ClasseController::class, 'listeclasses'])->middleware('role:admin,directeur,secretaire')->name('listeclasses.index');
    Route::post('/classes', [ClasseController::class, 'store'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.store');
    Route::put('/classesupdate/{classe}', [ClasseController::class, 'update'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.update');
    Route::delete('/classes/{classe}', [ClasseController::class, 'destroy'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.destroy');
    Route::get('/classes/{classe}', [ClasseController::class, 'show'])->middleware('role:admin,directeur,secretaire')->name('classes.show');
    Route::get('/classes/{classe}/affecter-enseignant', [ClasseController::class, 'affecterEnseignant'])->middleware('role:admin,directeur,secretaire')->name('classes.affecterEnseignant');
    Route::get('/classes/{classe}/affecter-professeurs', [ClasseController::class, 'affecterProfesseurs'])->middleware('role:admin,directeur,secretaire')->name('classes.affecterProfesseurs');
    Route::get('/classes/{classe}/inscrire-eleves', [ClasseController::class, 'inscrireEleves'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.inscrireEleves');
    // ✅ Affecter un enseignant (primaire)
    Route::post('classes/{id}/affecter-enseignant', [ClasseController::class, 'storeEnseignant'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.storeEnseignant');
    // ✅ Affecter des professeurs + matières (collège/lycée)
    Route::post('classes/{id}/affecter-professeurs', [ClasseController::class, 'storeProfesseurs'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.storeProfesseurs');
    // ✅ Inscrire des élèves
    Route::post('classes/{id}/inscrire-eleves', [ClasseController::class, 'storeEleves'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.storeEleves');
    // Retirer l'enseignant responsable (primaire)
    Route::delete('/classes/{classe}/retirer-enseignant', [ClasseController::class, 'retirerEnseignant'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.retirerEnseignant');
    // Retirer un professeur d'une matière (autres niveaux)
    Route::delete('/classes/{classe}/retirer-professeur/{affectation}', [ClasseController::class, 'retirerProfesseur'])
        ->middleware('role:admin,directeur,secretaire')->name('classe.retirerProfesseur');
    // au nive de eleveshow
    Route::post('/classes/{classe}/affecter-elevesfromclass', [ClasseController::class, 'affecterElevesfromclass'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.affecterElevesfromclass');
    Route::get('/classes/{id}/imprimer', [ClasseController::class, 'imprimer'])->middleware('role:admin,directeur,secretaire')->name('classes.imprimer');
    Route::get('/classes/{id}/imprimercomplete', [ClasseController::class, 'imprimercomplete'])->middleware('role:admin,directeur,secretaire')->name('classes.imprimer.complete');
    Route::get('/classes/{id}/affecter', [ClasseController::class, 'affecterEleves'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.affecterEleves');
    Route::delete('/classes/{classe}/retirer-eleve/{eleve}', [ClasseController::class, 'retirerEleve'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.retirerEleve');
    Route::delete('/classes/{classe}/remove-enseignant', [ClasseController::class, 'removeEnseignant'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.removeEnseignant');
    // Copier les classes de l'année précédente
    Route::post('/classes/copier-annee-precedente', [ClasseController::class, 'copierClasses'])
    ->middleware('role:admin,directeur,secretaire')->name('classes.copierClasses');
    // Supprimer toutes les classes sans élèves
    Route::post('/classes/supprimer-vides', [ClasseController::class, 'supprimerClassesVides'])
    ->middleware('role:admin,directeur,secretaire')->name('classes.supprimerVides');
    Route::middleware('role:admin,directeur,secretaire')->group(function () {
        Route::get('/enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
        Route::post('/enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
        Route::put('/enseignants/{enseignant}', [EnseignantController::class, 'update'])->name('enseignants.update');
        Route::delete('/enseignants/{enseignant}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy');
        Route::patch('/enseignants/{id}/toggle', [EnseignantController::class, 'toggleStatus'])->name('enseignants.toggle');
        Route::post('/affectationsdepuisenseignant', [EnseignantController::class, 'affectationsdepuisenseignant'])->name('affectationsdepuisenseignant.store');
        Route::get('/enseignantclasse', [EnseignantController::class, 'enseignantclasse'])->name('enseignantclasse.index');
        Route::get('/enseignantclasseaffectation/{classeid}', [EnseignantController::class, 'enseignantclasseaffectation'])->name('enseignantclasseaffectation.index');
        Route::get('/enseignants/print', [EnseignantController::class, 'print'])->name('enseignants.print');
        Route::get('/enseignants/niveau/{niveau}', [EnseignantController::class, 'listeParNiveau'])->name('enseignants.liste.niveau');
        Route::get('/enseignants/{enseignant}', [EnseignantController::class, 'show'])->name('enseignants.show');
        Route::post(
            '/enseignants/copier-affectations-annee',
            [EnseignantController::class, 'copierAffectationsAnneePrecedente']
        )->name('affectations.copier.annee');
        Route::post(
            '/enseignants/vider-affectations-annee',
            [EnseignantController::class, 'viderAffectationsAnnee']
        )->name('affectations.vider.annee');
    });
    Route::middleware(['auth', 'role:admin,directeur,secretaire'])->group(function () {
        Route::get('presences-enseignants', [PresenceEnseignantController::class, 'index'])->name('presences-enseignants.index');
        Route::post('presences-enseignantsstore', [PresenceEnseignantController::class, 'store'])->name('presences-enseignants.store');
        Route::put('presences-enseignants/{presence}', [PresenceEnseignantController::class, 'update'])->name('presences-enseignants.update');
        Route::delete('presences-enseignants/{presence}', [PresenceEnseignantController::class, 'destroy'])->name('presences-enseignants.destroy');
    });

    Route::middleware('role:admin,directeur,secretaire')->prefix('personnel')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('personnel.index');
        Route::post('/store', [PersonnelController::class, 'store'])->name('personnel.store');
        Route::put('/update/{id}', [PersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/destroy/{id}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');
        Route::patch('/toggle/{id}', [PersonnelController::class, 'toggle'])->name('personnel.toggle');
        Route::get('/pdf', [PersonnelController::class, 'generatePdf'])->name('personnel.pdf');
        Route::get('/{personnel}', [PersonnelController::class, 'show'])->name('personnel.show');


    });

    Route::middleware('role:admin,directeur,secretaire')->group(function () {
    Route::get('/eleves', [EleveController::class, 'index'])->name('eleves.index');
    Route::get('/elevesparannee', [EleveController::class, 'elevesparannee'])->name('elevesparannee.index');
    Route::post('/eleves', [EleveController::class, 'store'])->name('eleves.store');
    Route::post('/storeeleveinscription', [EleveController::class, 'storeeleveinscription'])->name('storeeleveinscription');
    Route::put('/eleves/{eleve}', [EleveController::class, 'update'])->name('eleves.update');
    Route::delete('/eleves/{eleve}', [EleveController::class, 'destroy'])->name('eleves.destroy');
    Route::patch('/eleves/{id}/toggle', [EleveController::class, 'toggleStatus'])->name('eleves.toggle');
    Route::get('/elevesshow/{id}', [EleveController::class, 'elevesshow'])->name('elevesshow');
    Route::patch('/inscriptions/{inscription}/type', [EleveController::class, 'updateTypeInscription'])->name('inscriptions.type.update');
    // Afficher le profil d’un élève
    Route::get('/eleves/{id}/profil', [EleveController::class, 'profil'])->name('eleves.profil');
    // Afficher la carte d’un élève
    Route::get('/eleves/{id}/carte', [EleveController::class, 'carte'])->name('eleves.carte');
    Route::put('/parent-eleve/{eleve}', [ParentEleveController::class, 'update'])->name('parent-eleve.update');
    Route::post('/eleves/{id}/upload-image', [EleveController::class, 'uploadImage'])->name('eleves.uploadImage');
    Route::put('/inscriptions/{id}/toggleStatusinscription', [InscriptionController::class, 'toggleStatusinscription'])
        ->name('inscriptions.toggleStatusinscription');
    Route::get('/eleves/{id}/print', [EleveController::class, 'print'])->name('eleves.print');
    });

    Route::delete(
    '/classes/{classe}/vider-eleves',
    [ClasseController::class, 'viderEleves']
    )->middleware('role:admin,directeur,secretaire')->name('classes.viderEleves');


    //parents eleves

    Route::middleware('role:admin,directeur')->group(function () {
        Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
        Route::post('/parents/store', [ParentController::class, 'store'])->name('parents.store');
        Route::post('/parents/link-child', [ParentController::class, 'linkChild'])->name('parents.linkChild');
    });

    Route::post(
    '/classes/{classe}/importer-par-matricules',
    [EleveController::class, 'importerParMatricules']
    )->middleware('role:admin,directeur,secretaire')->name('classes.importerParMatricules');
    Route::middleware('role:admin,directeur,secretaire')->group(function () {
    Route::get('/inscriptions', [InscriptionController::class, 'index'])->name('inscriptions.index');
    Route::post('/inscriptions', [InscriptionController::class, 'store'])->name('inscriptions.store');
    Route::put('/inscriptions/{inscription}', [InscriptionController::class, 'update'])->name('inscriptions.update');
    Route::delete('/inscriptions/{inscription}', [InscriptionController::class, 'destroy'])->name('inscriptions.destroy');
    });
    Route::post('/classes/{classe}/importer-eleves', [ClasseController::class, 'importerEleves'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.importerEleves');
    Route::middleware('role:admin,directeur,secretaire')->group(function () {
    Route::get('absence',[AbsenceController::class, 'index'])->name('absence.index');
    Route::post('/absence/store', [AbsenceController::class, 'store'])->name('absences.store');
    // routes/web.php ou routes/api.php
    Route::get('absences/{absence}/edit', [AbsenceController::class, 'edit'])->name('absences.edit');
    Route::delete('absences/{absence}', [AbsenceController::class, 'destroy'])->name('absences.destroy');
    Route::get('absences/{inscription}', [AbsenceController::class, 'show'])->name('absences.show');
    // web.php
    Route::get('/classes/{classe}/matieres', [AbsenceController::class, 'getMatieres'])->name('classes.matieres');
    Route::patch('/absences/{absence}/toggle-justified', [AbsenceController::class, 'toggleJustified'])->name('absences.toggleJustified');
    });

    Route::get('/api/eleves', function (Request $request) {
        $classeId = $request->classe_id;
        if (!$classeId) return [];

        return \App\Models\Inscription::with('eleve')
            ->where('classe_id', $classeId)
            ->where('annee_id', session('annee_id') ?? \App\Models\AnneesScolaire::where('active',1)->first()->id)
            ->get()
            ->map(fn($ins) => [
                'id' => $ins->id,
                'nom' => $ins->eleve->nom,
                'prenom' => $ins->eleve->prenom,
            ]);
    })->middleware('role:admin,directeur,secretaire');



    Route::middleware('role:admin,directeur,secretaire')->group(function () {
        Route::get('/affectations', [AffectationController::class, 'index'])->name('affectations.index');
        Route::post('/affectations', [AffectationController::class, 'store'])->name('affectations.store');
        Route::put('/affectations/{affectation}', [AffectationController::class, 'update'])->name('affectations.update');
        Route::delete('/affectations/{affectation}', [AffectationController::class, 'destroy'])->name('affectations.destroy');
        Route::delete('/affectations/remove-primary/{classe_id}', [AffectationController::class, 'removePrimary'])->name('primaryaffectations.remove');
        Route::delete('/affectationsdestroy/{id}', [AffectationController::class, 'destroyaffectation'])->name('destroyaffectations.destroy');
        Route::delete('/desaffectations/{id}', [AffectationController::class, 'desaffectations'])->name('desaffectations.destroy');
        Route::prefix('titulaires')->group(function () {
            Route::post('/store', [AffectationController::class, 'storeTitulaire'])->name('titulaires.store');
            Route::delete('/destroytitulaire/{id}', [AffectationController::class, 'destroytitulaire'])->name('titulaires.destroy');
        });
    });

    Route::middleware('role:admin,directeur,secretaire')->group(function () {
        Route::get('/matieres', [MatiereController::class, 'index'])->name('matieres.index');
        Route::get('/matiere-de-la-classe/{id}', [MatiereController::class, 'matieredansclasse'])->name('matieres.dansclasse');
        Route::get('/matieres-parclasse', [MatiereController::class, 'matiereparclasse'])->name('matieres.matiereparclasse');
        Route::post('/matieres', [MatiereController::class, 'store'])->name('matieres.store');
        Route::put('/matieres/{matiere}', [MatiereController::class, 'update'])->name('matieres.update');
        Route::delete('/matieres/{matiere}', [MatiereController::class, 'destroy'])->name('matieres.destroy');
    });
    Route::post('/storeProfesseursdansclasse/{classeid}', [MatiereController::class, 'storeProfesseursdansclasse'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.storeProfesseursdansclasse');
    Route::post('/classes/{id}/affecter-matieres', [MatiereController::class, 'affecterMatieres'])
        ->middleware('role:admin,directeur,secretaire')->name('classes.affecterMatieres');
    Route::get('/enseignant/mes-matieres', [MatiereController::class, 'mesMatieres'])->middleware('role:professeur')->name('matieres.mesmatieres');


    Route::patch('/annee/change/{id}', [AnneeController::class, 'change'])->middleware('role:admin,directeur')->name('annee.change');

    Route::middleware('role:admin,directeur,comptable')->group(function () {
    Route::get('/montants', [ScolariteController::class, 'index'])->name('montants.index');
    Route::get('/typefrais', [ScolariteController::class, 'typefrais'])->name('montants.typefrais');

    // Routes protégées
    Route::middleware(['auth'])->group(function () {
        Route::post('/typefrais', [ScolariteController::class, 'typefraisstore'])->name('typefrais.store');
        Route::put('/typefrais/{id}', [ScolariteController::class, 'typefraisupdate'])->name('typefrais.update');
        Route::delete('/typefrais/{id}', [ScolariteController::class, 'typefraisdestroy'])->name('typefrais.destroy');
        Route::get('paiements', [ScolariteController::class, 'index'])->name('paiements.index');

    });

    Route::prefix('frais')->name('frais.')->group(function () {
        Route::get('/', [FraisController::class, 'index'])->name('index');
        Route::get('/create', [FraisController::class, 'create'])->name('create');
        Route::post('/store', [FraisController::class, 'store'])->name('store');
        Route::get('/{frais}/edit', [FraisController::class, 'edit'])->name('edit');
        Route::put('/{frais}', [FraisController::class, 'update'])->name('update');
        Route::delete('/{frais}', [FraisController::class, 'destroy'])->name('destroy');
    });

    // ✅ Gestion des montants de frais (selon classe, année scolaire, etc.)
    Route::prefix('montants-frais')->name('montants_frais.')->group(function () {
        Route::get('/', [MontantFraisController::class, 'index'])->name('index');
        Route::post('/store', [MontantFraisController::class, 'store'])->name('store');
        Route::get('/{montant}/edit', [MontantFraisController::class, 'edit'])->name('edit');
        Route::put('/{montant}', [MontantFraisController::class, 'update'])->name('update');
        Route::delete('/{montant}', [MontantFraisController::class, 'destroy'])->name('destroy');
    });

    // ✅ Gestion des paiements (par élève, par frais, avec historique)
    Route::prefix('paiements')->name('paiements.')->group(function () {
        Route::get('/', [PaiementController::class, 'index'])->name('index');
        Route::get('/listedeseleves/{id}', [PaiementController::class, 'listeeleve'])->name('listeeleve');
        Route::get('/create', [PaiementController::class, 'create'])->name('create');
        Route::post('/store', [PaiementController::class, 'store'])->name('store');
        Route::get('/{paiement}', [PaiementController::class, 'show'])->name('show');
        Route::get('/{paiement}/edit', [PaiementController::class, 'edit'])->name('edit');
        Route::put('/{paiement}', [PaiementController::class, 'update'])->name('update');
        Route::delete('/{paiement}', [PaiementController::class, 'destroy'])->name('destroy');


    });


    Route::get('/paiements/infos-frais/{eleve}/{frais}', [PaiementController::class, 'infosFrais']);
    Route::get('/paiements/print/{id}', [PaiementController::class, 'print'])->name('paiements.print');
    Route::get('/paiements/printAll/{eleve}', [PaiementController::class, 'printAll'])->name('paiements.printAll');
    });


    Route::middleware('role:admin,directeur,secretaire')->prefix('decoupages')->name('decoupages.')->group(function () {
        Route::get('/', [DecoupageController::class, 'index'])->name('index');           // Liste
        Route::get('/create', [DecoupageController::class, 'create'])->name('create');   // Formulaire ajout
        Route::post('/', [DecoupageController::class, 'store'])->name('store');          // Sauvegarde
        Route::get('/{decoupage}/edit', [DecoupageController::class, 'edit'])->name('edit'); // Formulaire modification
        Route::put('/{decoupage}', [DecoupageController::class, 'update'])->name('update'); // Mise à jour
        Route::delete('/{decoupage}', [DecoupageController::class, 'destroy'])->name('destroy'); // Suppression
        Route::post('/copier-annee-precedente',[DecoupageController::class, 'copierDepuisAnneePrecedente'])->name('copierPrecedente');

    });

    Route::middleware('role:admin,directeur,secretaire')->prefix('types-evaluations')->name('types_evaluations.')->group(function () {
        Route::get('/', [TypeEvaluationController::class, 'index'])->name('index');           // Liste
        Route::get('/create', [TypeEvaluationController::class, 'create'])->name('create');   // Formulaire ajout
        Route::post('/', [TypeEvaluationController::class, 'store'])->name('store');          // Sauvegarde
        Route::put('/{typeEvaluation}', [TypeEvaluationController::class, 'update'])->name('update'); // Mise à jour
        Route::delete('/{typeEvaluation}', [TypeEvaluationController::class, 'destroy'])->name('destroy'); // Suppression
    });


    Route::middleware('role:admin,directeur,secretaire,professeur')->group(function () {
    Route::get('/evaluations-parclasse', [EvaluationController::class, 'evaluations'])->name('evaluations.index');
    Route::get('/note/{classe}/evaluations/create', [EvaluationController::class, 'createByClasse'])->name('evaluations.createByClasse');
    Route::get('/evaluations/{id}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
    Route::post('/evaluations/store', [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::delete('/evaluations/{id}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');
    Route::put('/evaluations/{id}', [EvaluationController::class, 'update'])->name('evaluations.update');
    Route::get('/evaluations/{classe}/notes', [EvaluationController::class, 'notes'])->name('evaluations.notes');
    });


    Route::middleware('role:admin,directeur,secretaire')->group(function () {
        Route::get('/bulletins/{classe}/notes', [BulletinController::class, 'bulletin'])->name('bulletins.index');
        Route::get('/bulletins/apercu/{inscriptionId}/{decoupage}', [BulletinController::class, 'apercubulletin'])->name('bulletins.apercu');
        Route::get('/bulletin/{inscription}/pdf', [BulletinController::class, 'pdf'])->name('bulletin.pdf');
        Route::get('/bulletins/eleve/{eleve}', [BulletinController::class, 'bulletinParEleve'])->name('bulletins.eleve');
        Route::get('/bulletins/{classe}/decoupage/{decoupage}', [BulletinController::class, 'bulletinParDecoupage'])->name('bulletins.decoupage');
        Route::get('/bulletinscalculs/{classe}/decoupage/{decoupage}', [BulletinController::class, 'bulletincalculParDecoupage'])->name('bulletinscalcul.decoupage');
        Route::get('bulletins/proclamation/{classe}/{decoupage}', [BulletinController::class, 'proclamation'])->name('bulletins.proclamation');
        Route::post(
            '/bulletins/calculer/{classe}/{decoupage}',
            [BulletinController::class, 'calculerMoyennesParDecoupage']
        )->name('bulletins.calculer');

        Route::post('/bulletins/theme/activer', [BulletinController::class, 'activerTheme'])
            ->middleware('role:admin,directeur')->name('bulletins.theme.activer');
    });


    //Auth::routes();
    Route::middleware(['auth', 'role:admin,directeur'])->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backup.index');
        Route::post('backups/create', [BackupController::class, 'create'])->name('backup.create');
        Route::get('backups/download/{id}', [BackupController::class, 'download'])->name('backup.download');
    });


    Route::middleware('role:admin,directeur,secretaire')->group(function () {
        Route::get('/emplois', [EmploiDuTempsController::class, 'index'])->name('emplois.index');
        Route::get('/create/{classe}', [EmploiDuTempsController::class, 'create'])->name('emplois.create');
        Route::get('/emplois/{classe}', [EmploiDuTempsController::class, 'show'])->name('emplois.show');
        Route::post('/emplois/{classe}', [EmploiDuTempsController::class, 'storeMultiple'])->name('emplois.storeMultiple');
        Route::delete('/emplois/{classe}/reset', [EmploiDuTempsController::class, 'reset'])->name('emplois.reset');
        Route::get('/emplois/{classe}/print', [EmploiDuTempsController::class, 'print'])->name('emplois.print');
        Route::post('/emplois/generate/{classe}', [EmploiDuTempsController::class, 'generateSmart'])->name('emplois.generate');
    });

    // Emploi du temps du professeur connecté
    Route::get('/monemplois', [App\Http\Controllers\EmploiDuTempsController::class, 'monEmploi'])->name('monemplois.index')->middleware('role:professeur');

    Route::middleware('role:admin,directeur,comptable')->prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('services.index');
        Route::post('store', [ServiceController::class, 'store'])->name('services.store');
        Route::get('{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::delete('{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::get('souscriptions', [ServiceController::class, 'souscriptions'])->name('services.souscriptions');
        Route::post('store-souscription', [ServiceController::class, 'storeSouscription'])->name('services.storeSouscription');
        Route::delete('souscriptions/{souscription}', [ServiceController::class, 'destroySouscription'])->name('services.destroySouscription');
        Route::get('classe/{id}/eleves', [ServiceController::class, 'getElevesByClasse']);
        Route::get('/paiements-souscription', [ServiceController::class, 'paiementsSouscriptions'])->name('services.paiementsouscriptions');
        Route::get('/paiement/{service}/souscriptions', [ServiceController::class, 'getSouscriptions']);
        Route::post('/paiement/store', [ServiceController::class, 'storePaiement'])->name('services.storePaiement');
        Route::get('/paiement/{service}/souscriptions', [ServiceController::class, 'getSouscriptionsForService']);
        Route::put('/paiements/{paiement}/update', [ServiceController::class, 'updatePaiement'])->name('services.updatePaiement');
        Route::delete('/paiements/{paiement}', [ServiceController::class, 'deletePaiement'])->name('services.deletePaiement');
        Route::get('/historique/{eleve}/{service}', [ServiceController::class, 'historique'])->name('services.historique');

    });





    // AJAX pour remplir le select des élèves
    // États de scolarité
    Route::middleware('role:admin,directeur,comptable')->group(function () {
        Route::get('/etatscolaritess', [PaiementController::class, 'etatScolarites'])->name('etatscolaritess');
        Route::get('paiements/etat-scolarite/{classe}', [PaiementController::class, 'etatPaiementParClasse'])->name('paiements.etat.paiement.par.classe');
    // Page choix des classes

    // Fiche état de paiement pour 1 classe
    Route::get(
        '/etat-scolarite/classe/{classe}',
        [PaiementController::class,'etatPaiementParClasse']
    )->name('etatPaiementClasse');
    });

    Route::middleware('role:admin,directeur,secretaire,comptable')->group(function () {
        Route::get('/bourses', [BourseController::class, 'index'])->name('bourses.index');
        Route::post('/bourses', [BourseController::class, 'store'])->name('bourses.store');
        Route::delete('/bourses/{id}', [BourseController::class, 'destroy'])->name('bourses.destroy');
        Route::get('/bourses/eleves/{classe_id}', [BourseController::class, 'getElevesByClasse'])->name('bourses.eleves');


    // === Gestion des types de bourses ===
    Route::prefix('bourses')->group(function () {
        Route::get('/', [App\Http\Controllers\BourseController::class, 'index'])->name('bourses.index');
        Route::post('/', [App\Http\Controllers\BourseController::class, 'store'])->name('bourses.store');
        Route::put('/{id}', [App\Http\Controllers\BourseController::class, 'update'])->name('bourses.update');
        Route::delete('/{id}', [App\Http\Controllers\BourseController::class, 'destroy'])->name('bourses.destroy');
    });
    });

    // === Gestion des attributions ===
    Route::middleware('role:admin,directeur,secretaire,comptable')->prefix('attributions')->group(function () {
        Route::get('/', [AttributionController::class, 'index'])->name('attributions.index');
        Route::post('/', [AttributionController::class, 'store'])
            ->name('attributions.store');
        Route::delete('/{id}', [AttributionController::class, 'destroy'])
            ->name('attributions.destroy');
        Route::patch('/toggle/{id}', [AttributionController::class, 'toggleEtat'])
            ->name('attributions.toggle');

    });

    Route::middleware('role:admin,directeur,secretaire,professeur')->group(function () {
        Route::get('/epreuves', [EpreuveController::class, 'index'])->name('epreuves.index');
        Route::post('/epreuves/upload/', [EpreuveController::class, 'upload'])->name('epreuves.upload');
        Route::get('/epreuves/{id}/download', [EpreuveController::class, 'download'])->name('epreuves.download');
        Route::delete('/epreuves/{epreuve}', [EpreuveController::class, 'destroy'])->name('epreuves.destroy');
        Route::put('/epreuves/{id}', [EpreuveController::class, 'update'])->name('epreuves.update');
        Route::patch('/epreuves/{id}/etat', [EpreuveController::class, 'updateEtat'])
            ->middleware('role:admin,directeur,secretaire')->name('epreuves.updateEtat');
    });


    Route::middleware('auth')->group(function () {

    Route::get('/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    Route::get('/messages/search-users', [MessageController::class, 'searchUsers'])
        ->name('messages.searchUsers');

    Route::post('/messages', [MessageController::class, 'store'])
        ->name('messages.store');

    Route::get('/messages/{id}', [MessageController::class, 'show'])
        ->name('messages.show');

    Route::post('/messages/{conversationId}/send', [MessageController::class, 'send'])
        ->name('messages.send');

    Route::post('/messages/{conversationId}/read', [MessageController::class, 'markAsRead'])
        ->name('messages.markAsRead');

});

});
//});

// Modal activation licence (offline)
Route::get('license/activate', function () {
    return view('admin.licenses.activate');
})->name('license.modal');



Route::post('license/activate', [\App\Http\Controllers\OfflineLicenseController::class, 'activate'])
    ->name('offline.license.activate');

Route::middleware(['auth.session', 'role.admin'])->group(function () {
    Route::get('/admin/license/download/{id}',
        [LicenseController::class, 'download']
    )->name('license.download');

    Route::prefix('admin')->group(function () {
        Route::get('licenses', [LicenseController::class, 'index'])->name('admin.licenses.index');
        Route::get('licenses/create', [LicenseController::class, 'create'])->name('admin.licenses.create');
        Route::post('licenses', [LicenseController::class, 'store'])->name('admin.licenses.store');
        Route::get('licenses/{license}/edit', [LicenseController::class, 'edit'])->name('admin.licenses.edit');
        Route::put('licenses/{license}', [LicenseController::class, 'update'])->name('admin.licenses.update');
        Route::delete('licenses/{license}', [LicenseController::class, 'destroy'])->name('admin.licenses.destroy');
    });
});

Route::post('/license/activate', [OfflineLicenseController::class, 'activate'])
    ->name('offline.license.activate');


Route::middleware(['auth.session', 'role:admin,directeur,comptable'])->group(function () {
    // Dépenses
    Route::get('depenses', [DepenseController::class, 'index'])->name('depenses.index');
    Route::post('depenses', [DepenseController::class, 'store'])->name('depenses.store');
    Route::put('depenses/{id}', [DepenseController::class, 'update'])->name('depenses.update');
    Route::delete('depenses/{id}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
    Route::get('depenses/print', [DepenseController::class, 'print'])->name('depenses.print');

    // États & rapports
    Route::get('/etats-rapports', [\App\Http\Controllers\EtatRapportController::class, 'index'])->name('etats.index');
    Route::get('/etats-rapports/print', [\App\Http\Controllers\EtatRapportController::class, 'print'])->name('etats.print');

    Route::middleware('role:admin,directeur')->prefix('taux-horaires')->name('taux_horaires.')->group(function () {
        Route::get('/', [TauxHoraireController::class, 'index'])->name('index');
        Route::get('/create', [TauxHoraireController::class, 'create'])->name('create');
        Route::post('/store', [TauxHoraireController::class, 'store'])->name('store');
        Route::get('/{tauxHoraire}/edit', [TauxHoraireController::class, 'edit'])->name('edit');
        Route::put('/{tauxHoraire}', [TauxHoraireController::class, 'update'])->name('update');
        Route::delete('/{tauxHoraire}', [TauxHoraireController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('salaires')->name('salaires.')->group(function() {
        Route::get('/', [SalaireController::class, 'index'])->name('index'); // Formulaire et vue
        Route::get('/depenses', [SalaireController::class, 'depenses'])->name('depenses'); // Formulaire et vue
        Route::get('/rapports', [SalaireController::class, 'rapports'])->name('rapports'); // Formulaire et vue
        Route::post('/calcul', [SalaireController::class, 'calculer'])->name('calculer'); // Calcul des salaires
        Route::post('/pdf', [SalaireController::class, 'pdf'])->name('pdf');
    });

    Route::resource('depenses', DepenseController::class)->except(['index', 'create', 'edit']);
});

Route::middleware(['auth.session'])->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // ⚠️ counts AVANT {id}
    Route::get('/notifications/counts', [NotificationController::class, 'counts'])
        ->name('notifications.counts');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');

    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->whereNumber('id')
        ->name('notifications.markAsRead');

    Route::get('/notifications/{id}', [NotificationController::class, 'show'])
        ->whereNumber('id')
        ->name('notifications.show');
});
