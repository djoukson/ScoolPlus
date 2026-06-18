<?php


/*
php artisan storage:link

ajout de la colonne status_eleve
ALTER TABLE inscriptions
ADD COLUMN status_eleve ENUM('Nouveau', 'Redouble') NOT NULL DEFAULT 'Nouveau';

model

---blade ajouteleveparannee------
---inscription controller--------

Route::put('/inscriptions/{id}/toggleStatusinscription', [InscriptionController::class, 'toggleStatusinscription'])
        ->name('inscriptions.toggleStatusinscription');


  public function toggleStatusinscription($eleveId)
    {
        $anneeEnCours = AnneesScolaire::where('active', 1)->first();
        $anneeId = $anneeEnCours->id;
        $inscription = Inscription::where('eleve_id', $eleveId)
            ->where('annee_id', $anneeId)
            ->firstOrFail();

        $inscription->status_eleve = $inscription->status_eleve === 'Nouveau'
            ? 'Redoublant'
            : 'Nouveau';

        $inscription->save();

        return back()->with('success', 'Statut de l’inscription mis à jour avec succès.');
    }

model titulaire
relation dans modele classe
  public function titulaire()
    {
        $annee_id = session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id');

        return $this->hasOne(Titulaire::class)
            ->where('annee_id', $annee_id)
            ->with('enseignant');
    }


CREATE TABLE `titulaires` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `enseignant_id` BIGINT UNSIGNED NOT NULL,
    `classe_id` BIGINT UNSIGNED NOT NULL,
    `annee_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `classe_annee_unique` (`classe_id`, `annee_id`),
    UNIQUE KEY `enseignant_annee_unique` (`enseignant_id`, `annee_id`),
    CONSTRAINT `titulaires_enseignant_fk` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `titulaires_classe_fk` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `titulaires_annee_fk` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
#balde enseignantclasse modifie
#enseignant controller update
affectation controller update
web update


CREATE USER 'myschoolplus'@'localhost'
IDENTIFIED BY 'MysCh00l+2025!Db#';
GRANT ALL PRIVILEGES ON *.* TO 'myschoolplus'@'localhost'
WITH GRANT OPTION;

FLUSH PRIVILEGES;


DB_USERNAME=myschoolplus
DB_PASSWORD=MysCh00l+2025!Db#


//connexion via cmd
mysql -u myschoolplus -p


delete ol user
DROP USER 'root'@'localhost';
FLUSH PRIVILEGES;

################################################################
mise a jour marricule
dans controller et model fonction generate
WITH cte AS (
  SELECT id, ROW_NUMBER() OVER (ORDER BY id) as rn
  FROM enseignants
)
UPDATE enseignants e
JOIN cte ON e.id = cte.id
SET e.matricule = CONCAT('ENS', LPAD(rn, 2, '0'));
