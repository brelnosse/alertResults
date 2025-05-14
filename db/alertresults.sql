-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.0.30 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Listage de la structure de la base pour alertresults
DROP DATABASE IF EXISTS `alertresults`;
CREATE DATABASE IF NOT EXISTS `alertresults` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `alertresults`;

-- Listage de la structure de table alertresults. admin_details
DROP TABLE IF EXISTS `admin_details`;
CREATE TABLE IF NOT EXISTS `admin_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role` enum('directeur','chef') NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `admin_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.admin_details : ~1 rows (environ)
INSERT INTO `admin_details` (`id`, `user_id`, `role`, `department`, `created_at`, `updated_at`) VALUES
	(8, 19, 'chef', '2', '2025-05-10 06:45:16', NULL);

-- Listage de la structure de table alertresults. cycle_niveau_specialite
DROP TABLE IF EXISTS `cycle_niveau_specialite`;
CREATE TABLE IF NOT EXISTS `cycle_niveau_specialite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `specialite_id` int NOT NULL,
  `cycle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `niveau` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specialite_id` (`specialite_id`,`cycle`,`niveau`),
  CONSTRAINT `cycle_niveau_specialite_ibfk_1` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.cycle_niveau_specialite : ~2 rows (environ)
INSERT INTO `cycle_niveau_specialite` (`id`, `specialite_id`, `cycle`, `niveau`, `created_at`, `updated_at`) VALUES
	(1, 10, 'prepa-ingenieur', 2, '2025-05-10 05:45:21', '2025-05-10 05:48:00'),
	(2, 10, 'prepa-ingenieur', 1, '2025-05-10 05:45:52', '2025-05-10 05:48:05');

-- Listage de la structure de table alertresults. departements
DROP TABLE IF EXISTS `departements`;
CREATE TABLE IF NOT EXISTS `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.departements : ~1 rows (environ)
INSERT INTO `departements` (`id`, `nom`, `code`, `description`, `created_at`, `updated_at`) VALUES
	(2, 'prepa-ingenieur', 'PREPA', 'Classes préparatoires et cycles préparatoires', '2025-05-10 04:10:33', '2025-05-10 04:11:24');

-- Listage de la structure de table alertresults. departement_specialites
DROP TABLE IF EXISTS `departement_specialites`;
CREATE TABLE IF NOT EXISTS `departement_specialites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `departement_id` int NOT NULL,
  `specialite_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departement_id` (`departement_id`,`specialite_id`),
  KEY `fk_dept_spec_specialite` (`specialite_id`),
  CONSTRAINT `fk_dept_spec_departement` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dept_spec_specialite` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.departement_specialites : ~1 rows (environ)
INSERT INTO `departement_specialites` (`id`, `departement_id`, `specialite_id`, `created_at`, `updated_at`) VALUES
	(10, 2, 10, '2025-05-10 04:10:33', '2025-05-10 04:10:33');

-- Listage de la structure de table alertresults. matieres
DROP TABLE IF EXISTS `matieres`;
CREATE TABLE IF NOT EXISTS `matieres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` int NOT NULL,
  `niveau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `coefficient` int DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.matieres : ~5 rows (environ)
INSERT INTO `matieres` (`id`, `libelle`, `code`, `credit`, `niveau`, `created_at`, `updated_at`, `coefficient`) VALUES
	(6, 'Algèbre linéaire', 'ALG101', 3, '1', '2025-05-11 18:18:52', '2025-05-11 18:24:23', 1),
	(7, 'Calcul différentiel et intégral', 'CAL102', 4, '1', '2025-05-11 18:18:52', '2025-05-11 18:18:52', 1),
	(8, 'Programmation orientée objet', 'POO201', 4, '2', '2025-05-11 18:18:52', '2025-05-11 18:18:52', 1),
	(9, 'Systèmes d\'exploitation', 'SYS301', 3, '1', '2025-05-11 18:18:52', '2025-05-11 18:18:52', 1),
	(10, 'Bases de données', 'BDD202', 4, '2', '2025-05-11 18:18:52', '2025-05-11 18:18:52', 1);

-- Listage de la structure de table alertresults. matieres_enseignees
DROP TABLE IF EXISTS `matieres_enseignees`;
CREATE TABLE IF NOT EXISTS `matieres_enseignees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_enseignant` int NOT NULL COMMENT 'ID de l''enseignant',
  `niveau` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Niveau (1, 2, etc.)',
  `salle` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Salle (A, B, C, D)',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Notes complémentaires',
  `matiere` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de la matière enseignée',
  `identifiant_bloc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identifiant unique du bloc',
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date de création de l''enregistrement',
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date de dernière modification',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_matiere_enseignee` (`id_enseignant`,`niveau`,`salle`),
  KEY `idx_niveau_salle` (`niveau`,`salle`),
  KEY `idx_enseignant` (`id_enseignant`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.matieres_enseignees : ~2 rows (environ)
INSERT INTO `matieres_enseignees` (`id`, `id_enseignant`, `niveau`, `salle`, `notes`, `matiere`, `identifiant_bloc`, `status`, `date_creation`, `date_modification`) VALUES
	(17, 23, '2', 'B', '', 'Bases de données', 'subject-block-1', 'approved', '2025-05-11 20:11:30', '2025-05-12 15:27:13'),
	(18, 23, '1', 'A', '', 'Systèmes d\'exploitation', 'subject-block-1', 'approved', '2025-05-12 08:26:27', '2025-05-12 14:56:02');

-- Listage de la structure de table alertresults. moyennes
DROP TABLE IF EXISTS `moyennes`;
CREATE TABLE IF NOT EXISTS `moyennes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int NOT NULL,
  `moyenne_cc1` decimal(5,2) DEFAULT '0.00',
  `moyenne_cc2` decimal(5,2) DEFAULT '0.00',
  `moyenne_cc3` decimal(5,2) DEFAULT '0.00',
  `moyenne_cc4` decimal(5,2) DEFAULT '0.00',
  `moyenne_sn1` decimal(5,2) DEFAULT '0.00',
  `moyenne_sn2` decimal(5,2) DEFAULT '0.00',
  `moyenne_rat1` decimal(5,2) DEFAULT '0.00',
  `moyenne_rat2` decimal(5,2) DEFAULT '0.00',
  `moyenne_generale` decimal(5,2) DEFAULT '0.00',
  `date_calcul` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_moyenne_etudiant` (`id_etudiant`),
  CONSTRAINT `moyennes_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `student_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.moyennes : ~0 rows (environ)

-- Listage de la structure de table alertresults. notes
DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int NOT NULL,
  `id_enseignant` int NOT NULL,
  `id_matiere` int NOT NULL,
  `cc1_note` decimal(5,2) DEFAULT '0.00',
  `cc2_note` decimal(5,2) DEFAULT '0.00',
  `cc3_note` decimal(5,2) DEFAULT '0.00',
  `cc4_note` decimal(5,2) DEFAULT '0.00',
  `cc1_bonus` decimal(5,2) DEFAULT '0.00',
  `cc2_bonus` decimal(5,2) DEFAULT '0.00',
  `cc3_bonus` decimal(5,2) DEFAULT '0.00',
  `cc4_bonus` decimal(5,2) DEFAULT '0.00',
  `cc1_final` decimal(5,2) GENERATED ALWAYS AS ((`cc1_note` + `cc1_bonus`)) STORED,
  `cc2_final` decimal(5,2) GENERATED ALWAYS AS ((`cc2_note` + `cc2_bonus`)) STORED,
  `cc3_final` decimal(5,2) GENERATED ALWAYS AS ((`cc3_note` + `cc3_bonus`)) STORED,
  `cc4_final` decimal(5,2) GENERATED ALWAYS AS ((`cc4_note` + `cc4_bonus`)) STORED,
  `SN1` decimal(5,2) DEFAULT '0.00',
  `SN2` decimal(5,2) DEFAULT '0.00',
  `RAT1` decimal(5,2) DEFAULT '0.00',
  `RAT2` decimal(5,2) DEFAULT '0.00',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified_by_director` tinyint(1) DEFAULT '0',
  `verified_by_dphead` tinyint(1) DEFAULT '0',
  `response` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note` (`id_etudiant`,`id_matiere`),
  KEY `idx_notes_etudiant` (`id_etudiant`),
  KEY `idx_notes_matiere` (`id_matiere`),
  KEY `idx_notes_enseignant` (`id_enseignant`),
  KEY `idx_notes_verification` (`verified_by_director`,`verified_by_dphead`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `student_details` (`id`),
  CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`id_enseignant`) REFERENCES `teacher_details` (`id`),
  CONSTRAINT `notes_ibfk_3` FOREIGN KEY (`id_matiere`) REFERENCES `matieres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.notes : ~0 rows (environ)

-- Listage de la structure de table alertresults. password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','student','teacher') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expiry` (`expiry`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.password_reset_tokens : ~0 rows (environ)

-- Listage de la structure de table alertresults. remember_tokens
DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','student','teacher') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_type` (`user_id`,`user_type`),
  KEY `idx_token` (`token`),
  KEY `idx_expiry` (`expiry`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.remember_tokens : ~0 rows (environ)

-- Listage de la structure de table alertresults. specialites
DROP TABLE IF EXISTS `specialites`;
CREATE TABLE IF NOT EXISTS `specialites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.specialites : ~1 rows (environ)
INSERT INTO `specialites` (`id`, `nom`, `code`, `description`, `created_at`, `updated_at`) VALUES
	(10, 'Prépa 3IL', 'PREPA-3IL', 'Classe préparatoire spécifique 3IL', '2025-05-10 04:10:33', '2025-05-10 04:10:33');

-- Listage de la structure de table alertresults. student_details
DROP TABLE IF EXISTS `student_details`;
CREATE TABLE IF NOT EXISTS `student_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `birthdate` date NOT NULL,
  `matricule` varchar(50) NOT NULL,
  `cycle` varchar(50) NOT NULL,
  `niveau` varchar(10) NOT NULL,
  `specialite` varchar(50) NOT NULL,
  `classe` char(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text,
  `validated_by` int DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  KEY `user_id` (`user_id`),
  KEY `idx_student_details_matricule` (`matricule`),
  KEY `status` (`status`),
  KEY `fk_validated_by` (`validated_by`),
  CONSTRAINT `fk_validated_by` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.student_details : ~1 rows (environ)
INSERT INTO `student_details` (`id`, `user_id`, `birthdate`, `matricule`, `cycle`, `niveau`, `specialite`, `classe`, `created_at`, `updated_at`, `status`, `rejection_reason`, `validated_by`, `validated_at`) VALUES
	(9, 28, '2006-01-30', 'IUC23E0081655', 'prepa-ingenieur', '2', 'prepa 3il', 'B', '2025-05-11 16:56:26', NULL, 'approved', NULL, 19, '2025-05-11 16:56:59');

-- Listage de la structure de table alertresults. teacher_details
DROP TABLE IF EXISTS `teacher_details`;
CREATE TABLE IF NOT EXISTS `teacher_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text,
  `validated_by` int DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `fk_teacher_validated_by` (`validated_by`),
  KEY `idx_teacher_status` (`status`),
  CONSTRAINT `fk_teacher_validated_by` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.teacher_details : ~1 rows (environ)
INSERT INTO `teacher_details` (`id`, `user_id`, `status`, `rejection_reason`, `validated_by`, `validated_at`, `created_at`, `updated_at`) VALUES
	(6, 23, 'approved', NULL, 19, '2025-05-12 14:56:02', '2025-05-10 06:56:41', '2025-05-12 14:56:02');

-- Listage de la structure de table alertresults. users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','teacher','admin') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_phone` (`phone`),
  KEY `idx_users_user_type` (`user_type`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.users : ~3 rows (environ)
INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `phone`, `password`, `user_type`, `created_at`, `updated_at`, `last_login`) VALUES
	(19, 'Brel', 'Nosse', 'brelnosse@gmail.com', '650194960', '$2y$10$FGDeZY9bQgA/191mF8yQUOl78nHtHh583NYdYsF36SBe71LHn9DMO', 'admin', '2025-05-10 06:45:16', NULL, '2025-05-14 10:43:03'),
	(23, 'dongmo', 'guepi', 'jfk19735@gmail.com', '673804558', '$2y$10$iueQoMj2/aI35IaTwAyljOl25vmdNFo1dHNE59IctUlc.IksaKLbK', 'teacher', '2025-05-10 06:56:41', NULL, '2025-05-12 06:05:17'),
	(28, 'brel', 'nosse kenne', 'brelnosse2@gmail.com', '676144352', '$2y$10$cE/kqTnU61Uxy28LwEh6zOpe8CatQC.JMcv.OtD6gWniMMJMPrgi.', 'student', '2025-05-11 16:56:26', NULL, '2025-05-14 10:41:15');

-- Listage de la structure de table alertresults. user_tokens
DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_tokens_user_id` (`user_id`),
  KEY `idx_user_tokens_token` (`token`),
  KEY `idx_user_tokens_expires_at` (`expires_at`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.user_tokens : ~0 rows (environ)

-- Listage de la structure de déclencheur alertresults. update_matieres_enseignees_status_trigger
DROP TRIGGER IF EXISTS `update_matieres_enseignees_status_trigger`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `update_matieres_enseignees_status_trigger` AFTER INSERT ON `teacher_details` FOR EACH ROW BEGIN
    UPDATE matieres_enseignees 
    SET status = NEW.status  -- Utilise NEW.status pour obtenir le statut de la nouvelle entrée dans teacher_details
    WHERE id_enseigant = NEW.id; -- Utilise NEW.id pour cibler le enseignant_id de la table matieres_enseignees
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
