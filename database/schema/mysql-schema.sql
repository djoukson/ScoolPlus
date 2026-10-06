/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` bigint unsigned NOT NULL,
  `matiere_id` bigint unsigned NOT NULL,
  `decoupage_id` bigint unsigned NOT NULL,
  `heures` decimal(5,2) NOT NULL,
  `is_justified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_absence_matiere` (`matiere_id`),
  KEY `fk_absence_decoupage` (`decoupage_id`),
  KEY `idx_absence_calc` (`inscription_id`,`matiere_id`,`decoupage_id`),
  CONSTRAINT `fk_absence_decoupage` FOREIGN KEY (`decoupage_id`) REFERENCES `decoupages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_absence_inscription` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_absence_matiere` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `affectations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affectations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enseignant_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `matiere_id` bigint unsigned DEFAULT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `heures_attribuees` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_affect_enseignant` (`enseignant_id`),
  KEY `fk_affect_classe` (`classe_id`),
  KEY `fk_affect_matiere` (`matiere_id`),
  KEY `fk_affect_annee` (`annee_id`),
  CONSTRAINT `fk_affect_annee` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_affect_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_affect_enseignant` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_affect_matiere` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `annees_scolaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annees_scolaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attributions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscription_id` bigint unsigned NOT NULL,
  `bourse_id` int NOT NULL,
  `date_attribution` datetime DEFAULT CURRENT_TIMESTAMP,
  `etat` enum('active','desactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'desactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_attribution_bourse` (`bourse_id`),
  KEY `idx_inscription_id` (`inscription_id`),
  CONSTRAINT `fk_attributions_inscriptions` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telephone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirmee','terminee','annulee') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`,`date_debut`,`date_fin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bourse_frais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bourse_frais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bourse_id` int NOT NULL,
  `frais_id` int NOT NULL,
  `pourcentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bourse_id` (`bourse_id`,`frais_id`),
  KEY `fk_bourse_frais_frais` (`frais_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bourses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bourses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bulletin_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulletin_themes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `blade` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `niveau_id` bigint unsigned DEFAULT NULL,
  `type_decoupage` enum('trimestre','semestre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `enseignant_id` bigint unsigned DEFAULT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `decoupage_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_classes_enseignant` (`enseignant_id`),
  KEY `fk_classes_annee` (`annee_id`),
  KEY `fk_classes_niveau` (`niveau_id`),
  KEY `fk_classes_decoupage` (`decoupage_id`),
  CONSTRAINT `fk_classes_annee` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_classes_decoupage` FOREIGN KEY (`decoupage_id`) REFERENCES `decoupages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_classes_enseignant` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_classes_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conversation_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversation_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `conversation_participants_user_id_index` (`user_id`),
  CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eleve_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_eleve_id_type_index` (`eleve_id`,`type`),
  KEY `conversations_created_by_index` (`created_by`),
  CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_eleve_id_foreign` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `classe_id` bigint unsigned NOT NULL,
  `matiere_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cours_classe` (`classe_id`),
  KEY `fk_cours_matiere` (`matiere_id`),
  CONSTRAINT `fk_cours_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cours_matiere` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `decoupages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `decoupages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('trimestre','semestre') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_decoupages_annee` (`annee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `depenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `depenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `montant` decimal(15,2) NOT NULL,
  `date_depense` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ecole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ecole` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `annee_id` int DEFAULT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telephone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `directeur` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_web` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `directeur_primaire` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `annee_id` (`annee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eleves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eleves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `matricule` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` enum('M','F') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `tuteur_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tuteur_tel` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `imglink` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statut` int DEFAULT '1',
  `lieudenaissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `taille_eleve` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pointure_chaussure` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taille_habit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nationalite` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `fk_eleves_user` (`user_id`),
  CONSTRAINT `fk_eleves_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `emplois`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emplois` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cours_id` bigint unsigned NOT NULL,
  `enseignant_id` bigint unsigned DEFAULT NULL,
  `jour` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `salle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_emplois_cours` (`cours_id`),
  KEY `fk_emplois_enseignant` (`enseignant_id`),
  CONSTRAINT `fk_emplois_cours` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emplois_enseignant` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `emplois_du_temps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emplois_du_temps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `classe_id` bigint unsigned NOT NULL,
  `heure_cours_id` bigint unsigned NOT NULL,
  `affectation_id` bigint unsigned NOT NULL,
  `jour` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emplois_du_temps_classe_fk` (`classe_id`),
  KEY `emplois_du_temps_heure_fk` (`heure_cours_id`),
  KEY `emplois_du_temps_affectation_fk` (`affectation_id`),
  CONSTRAINT `emplois_du_temps_affectation_fk` FOREIGN KEY (`affectation_id`) REFERENCES `affectations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `emplois_du_temps_classe_fk` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `emplois_du_temps_heure_fk` FOREIGN KEY (`heure_cours_id`) REFERENCES `heures_cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enseignants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enseignants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `matricule` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tel` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `niveau_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `statut` int DEFAULT '1',
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sexe` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adresse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `groupesanguin` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `salaire_mensuel` decimal(25,0) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_enseignants_user` (`user_id`),
  KEY `enseignants_niveau_id_foreign` (`niveau_id`),
  CONSTRAINT `enseignants_niveau_id_foreign` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_enseignants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `epreuves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epreuves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `classe_id` bigint unsigned DEFAULT NULL,
  `annee_id` bigint unsigned DEFAULT NULL,
  `decoupage_id` bigint unsigned DEFAULT NULL,
  `matiere_id` bigint unsigned DEFAULT NULL,
  `type_evaluation_id` bigint unsigned DEFAULT NULL,
  `nom_fichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `chemin_fichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `etat` enum('valide','attente','a_remplacer','compose') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'attente',
  PRIMARY KEY (`id`),
  KEY `fk_epreuves_matieres` (`matiere_id`),
  KEY `fk_epreuves_types_evaluations` (`type_evaluation_id`),
  KEY `fk_epreuves_classes` (`classe_id`),
  KEY `fk_epreuves_uploaded_by` (`uploaded_by`),
  KEY `fk_epreuves_annee` (`annee_id`),
  KEY `fk_epreuves_decoupage` (`decoupage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` bigint unsigned NOT NULL,
  `matiere_id` bigint unsigned NOT NULL,
  `type_evaluation_id` bigint unsigned NOT NULL,
  `decoupage_id` bigint unsigned NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `date_eval` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_evaluations_matiere` (`matiere_id`),
  KEY `fk_evaluations_type` (`type_evaluation_id`),
  KEY `fk_evaluations_decoupage` (`decoupage_id`),
  KEY `fk_evaluations_inscription` (`inscription_id`),
  CONSTRAINT `fk_evaluations_inscription` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `frais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `heures_cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `heures_cours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eleve_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_inscription` enum('Nouveau','Réinscrit') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Nouveau',
  `statut` enum('actif','transfere','abandon','termine') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status_eleve` enum('Nouveau','Redoublant') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Nouveau',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_eleve_annee` (`eleve_id`,`annee_id`),
  KEY `fk_inscription_classe` (`classe_id`),
  KEY `fk_inscription_annee` (`annee_id`),
  CONSTRAINT `fk_inscription_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inscription_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `license_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `duration_years` float DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `machine_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','expired','revoked') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `matieres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coefficient` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `niveau_id` bigint unsigned DEFAULT NULL,
  `sigle` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_matieres_niveaux` (`niveau_id`),
  CONSTRAINT `fk_matieres_niveaux` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `reply_to_id` bigint unsigned DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  KEY `messages_sender_id_index` (`sender_id`),
  KEY `messages_reply_to_id_index` (`reply_to_id`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_reply_to_id_foreign` FOREIGN KEY (`reply_to_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `montants_frais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `montants_frais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `annee_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `frais_id` bigint unsigned NOT NULL,
  `montant` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `montants_frais_annee_id_classe_id_frais_id_unique` (`annee_id`,`classe_id`,`frais_id`),
  KEY `montants_frais_classe_id_foreign` (`classe_id`),
  KEY `montants_frais_frais_id_foreign` (`frais_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `montants_scolarite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `montants_scolarite` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `montant` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moyennes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moyennes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` bigint unsigned NOT NULL,
  `decoupage_id` bigint unsigned NOT NULL,
  `moyenne` decimal(5,2) NOT NULL,
  `rang` int DEFAULT NULL,
  `appreciation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moyenne_annuelle` decimal(5,2) DEFAULT NULL,
  `rang_annuel` int DEFAULT NULL,
  `appreciation_annuelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_moyenne` (`inscription_id`,`decoupage_id`),
  KEY `fk_moyennes_decoupage` (`decoupage_id`),
  CONSTRAINT `fk_moyennes_decoupage` FOREIGN KEY (`decoupage_id`) REFERENCES `decoupages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_moyennes_inscription` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moyennes_generales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moyennes_generales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `classe_id` bigint unsigned NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `decoupage_id` bigint unsigned NOT NULL,
  `moyenne_classe` decimal(5,2) DEFAULT '0.00',
  `moyenne_forte` decimal(5,2) DEFAULT '0.00',
  `moyenne_faible` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_classe_annee_decoupage` (`classe_id`,`annee_id`,`decoupage_id`),
  KEY `fk_mg_annee` (`annee_id`),
  KEY `fk_mg_decoupage` (`decoupage_id`),
  CONSTRAINT `fk_mg_annee` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mg_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mg_decoupage` FOREIGN KEY (`decoupage_id`) REFERENCES `decoupages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `niveaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `niveaux` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` bigint unsigned NOT NULL,
  `matiere_id` bigint unsigned NOT NULL,
  `type_eval` enum('interro','devoir','examen') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `date_eval` date DEFAULT NULL,
  `trimestre` enum('1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notes_inscription` (`inscription_id`),
  KEY `fk_notes_matiere` (`matiere_id`),
  CONSTRAINT `fk_notes_inscription` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notes_matiere` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paiement_souscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paiement_souscription` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `souscription_id` bigint unsigned DEFAULT NULL,
  `eleve_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `montant` decimal(12,2) NOT NULL,
  `date_paiement` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `paiements_souscription_id_foreign` (`souscription_id`),
  KEY `paiements_eleve_id_foreign` (`eleve_id`),
  KEY `paiements_service_id_foreign` (`service_id`),
  CONSTRAINT `paiements_eleve_id_foreign` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paiements_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paiements_souscription_id_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paiements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eleve_id` bigint unsigned NOT NULL,
  `inscription_id` bigint unsigned DEFAULT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `frais_id` bigint unsigned NOT NULL,
  `montant_paye` decimal(12,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `mode_paiement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paiements_annee_id_foreign` (`annee_id`),
  KEY `paiements_classe_id_foreign` (`classe_id`),
  KEY `paiements_frais_id_foreign` (`frais_id`),
  KEY `idx_paiements_eleve_id` (`eleve_id`),
  KEY `paiements_inscription_id_index` (`inscription_id`),
  CONSTRAINT `fk_paiements_eleves` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_paiements_inscription_scope` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paiements_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paiements_backup` (
  `id` bigint unsigned NOT NULL DEFAULT '0',
  `eleve_id` bigint unsigned NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `frais_id` bigint unsigned NOT NULL,
  `montant_paye` decimal(12,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `mode_paiement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_eleve_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parent_eleve_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `eleve_id` bigint unsigned NOT NULL,
  `relation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_eleve_user_user_id_eleve_id_unique` (`user_id`,`eleve_id`),
  KEY `parent_eleve_user_eleve_id_index` (`eleve_id`),
  CONSTRAINT `parent_eleve_user_eleve_id_foreign` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_eleve_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parent_eleves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parent_eleves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eleve_id` bigint unsigned NOT NULL,
  `pere_nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pere_tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pere_profession` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pere_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pere_adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mere_nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mere_tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mere_profession` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mere_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mere_adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_eleves_eleve_id_foreign` (`eleve_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personnel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sexe` enum('M','F') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `groupesanguin` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire` decimal(10,2) DEFAULT '0.00',
  `tel` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personnel_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `presences_enseignants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presences_enseignants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enseignant_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `nombre_heures` int NOT NULL DEFAULT '0',
  `motif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_presence_enseignant` (`enseignant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `salaire_remunerations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salaire_remunerations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnel_id` bigint unsigned DEFAULT NULL,
  `enseignant_id` bigint unsigned DEFAULT NULL,
  `type` enum('enseignant','personnel') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salaire` decimal(12,2) NOT NULL DEFAULT '0.00',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_personnel_periode` (`personnel_id`,`enseignant_id`,`date_debut`,`date_fin`),
  KEY `enseignant_id` (`enseignant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `montant` decimal(10,2) NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `services_ibfk_1` (`annee_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `souscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `souscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eleve_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `souscriptions_eleves_fk` (`eleve_id`),
  KEY `souscriptions_services_fk` (`service_id`),
  KEY `souscriptions_annees_fk` (`annee_id`),
  CONSTRAINT `souscriptions_annees_fk` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `souscriptions_eleves_fk` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  CONSTRAINT `souscriptions_services_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `taux_horaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taux_horaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `niveau_id` bigint unsigned NOT NULL,
  `taux` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `niveau_id` (`niveau_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `titulaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titulaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enseignant_id` bigint unsigned NOT NULL,
  `classe_id` bigint unsigned NOT NULL,
  `annee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classe_annee_unique` (`classe_id`,`annee_id`),
  UNIQUE KEY `enseignant_annee_unique` (`enseignant_id`,`annee_id`),
  KEY `titulaires_annee_fk` (`annee_id`),
  CONSTRAINT `titulaires_annee_fk` FOREIGN KEY (`annee_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `titulaires_classe_fk` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `titulaires_enseignant_fk` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `types_evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `types_evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom_normalized` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `matricule` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sexe` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','professeur','comptable','directeur','parent','secretaire') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profileimg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `adresse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `groupesanguin` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `session_version` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marque` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `modele` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `categorie` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `ville` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Lomé',
  `carburant` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'Essence',
  `transmission` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'Manuelle',
  `places` tinyint NOT NULL DEFAULT '5',
  `prix_jour` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `photo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statut` enum('disponible','loue','maintenance') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

