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

-- Listage des données de la table alertresults.admin_details : ~0 rows (environ)
INSERT INTO `admin_details` (`id`, `user_id`, `role`, `department`, `created_at`, `updated_at`) VALUES
	(8, 19, 'chef', '2', '2025-05-10 06:45:16', NULL),
	(9, 25, 'chef', '2', '2025-05-10 08:01:24', NULL);

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

-- Listage des données de la table alertresults.cycle_niveau_specialite : ~0 rows (environ)
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

-- Listage des données de la table alertresults.departements : ~2 rows (environ)
INSERT INTO `departements` (`id`, `nom`, `code`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'Informatique', 'INFO', 'Département d\'informatique et technologies de l\'information', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(2, 'prepa-ingenieur', 'PREPA', 'Classes préparatoires et cycles préparatoires', '2025-05-10 04:10:33', '2025-05-10 04:11:24'),
	(3, 'Génie Civil', 'GC', 'Département de génie civil et construction', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(4, 'Génie Électrique', 'GE', 'Département de génie électrique et électronique', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(5, 'Sciences de Gestion', 'SG', 'Département des sciences de gestion et management', '2025-05-10 04:10:33', '2025-05-10 04:10:33');

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

-- Listage des données de la table alertresults.departement_specialites : ~13 rows (environ)
INSERT INTO `departement_specialites` (`id`, `departement_id`, `specialite_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(2, 1, 2, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(3, 1, 3, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(4, 1, 4, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(5, 1, 5, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(6, 1, 6, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(7, 1, 7, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(8, 1, 8, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(9, 1, 9, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(10, 2, 10, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(11, 2, 11, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(12, 2, 12, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(13, 2, 13, '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(14, 1, 10, '2025-05-10 04:13:02', '2025-05-10 04:13:02');

-- Listage de la structure de table alertresults. password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','student','teacher') COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expiry` (`expiry`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table alertresults.password_reset_tokens : ~1 rows (environ)

-- Listage de la structure de table alertresults. remember_tokens
DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','student','teacher') COLLATE utf8mb4_unicode_ci NOT NULL,
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

-- Listage des données de la table alertresults.specialites : ~13 rows (environ)
INSERT INTO `specialites` (`id`, `nom`, `code`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'Développement Fullstack Web', 'DEV-WEB', 'Spécialité en développement web frontend et backend', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(2, 'Data Science', 'DATA-SCI', 'Science des données et intelligence artificielle', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(3, 'Robotique', 'ROBOT', 'Conception et programmation de systèmes robotiques', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(4, 'Génie Logiciel', 'GL', 'Ingénierie et conception de logiciels', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(5, 'Réseaux et Systèmes', 'RS', 'Administration des réseaux et systèmes informatiques', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(6, 'Management des Systèmes d\'Information', 'MSI', 'Gestion des systèmes d\'information', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(7, 'Intelligence Artificielle', 'IIA', 'Intelligence artificielle et apprentissage automatique', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(8, 'Internet et Web Development', 'IWD', 'Développement d\'applications internet et web', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(9, 'Programmation et Applications Mobiles', 'PAM', 'Développement d\'applications mobiles', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(10, 'Prépa 3IL', 'PREPA-3IL', 'Classe préparatoire spécifique 3IL', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(11, 'Ingénieur 1', 'ING1', 'Première année du cycle ingénieur', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(12, 'Ingénieur 2', 'ING2', 'Deuxième année du cycle ingénieur', '2025-05-10 04:10:33', '2025-05-10 04:10:33'),
	(13, 'Ingénieur 3', 'ING3', 'Troisième année du cycle ingénieur', '2025-05-10 04:10:33', '2025-05-10 04:10:33');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.student_details : ~0 rows (environ)
INSERT INTO `student_details` (`id`, `user_id`, `birthdate`, `matricule`, `cycle`, `niveau`, `specialite`, `classe`, `created_at`, `updated_at`, `status`, `rejection_reason`, `validated_by`, `validated_at`) VALUES
	(7, 20, '2006-01-30', 'IUC23E0081654', 'prepa-ingenieur', '1', 'prepa 3il', 'B', '2025-05-10 06:46:38', NULL, 'approved', NULL, 19, '2025-05-10 06:49:24'),
	(8, 24, '2006-05-11', 'IUC23E0081659', 'prepa-ingenieur', '1', 'prepa 3il', 'B', '2025-05-10 07:49:56', NULL, 'pending', NULL, NULL, NULL);

-- Listage de la structure de table alertresults. teacher_details
DROP TABLE IF EXISTS `teacher_details`;
CREATE TABLE IF NOT EXISTS `teacher_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `teacher_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.teacher_details : ~0 rows (environ)
INSERT INTO `teacher_details` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
	(6, 23, '2025-05-10 06:56:41', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.users : ~0 rows (environ)
INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `phone`, `password`, `user_type`, `created_at`, `updated_at`, `last_login`) VALUES
	(19, 'Brel', 'Nosse', 'brelnosse@gmail.com', '650194960', '$2y$10$FGDeZY9bQgA/191mF8yQUOl78nHtHh583NYdYsF36SBe71LHn9DMO', 'admin', '2025-05-10 06:45:16', NULL, '2025-05-10 06:48:55'),
	(20, 'Brel', 'Nosse', 'brelnosse2@gmail.com', '676144352', '$2y$10$d4WtoCbCE3llp0gG5Ga9tOADEqxc9DjYKPsnbxNn2GpZILBE/2IAq', 'student', '2025-05-10 06:46:38', NULL, '2025-05-10 06:50:04'),
	(23, 'dongmo', 'guepi', 'jfk19735@gmail.com', '673804558', '$2y$10$iueQoMj2/aI35IaTwAyljOl25vmdNFo1dHNE59IctUlc.IksaKLbK', 'teacher', '2025-05-10 06:56:41', NULL, NULL),
	(24, 'Brel', 'Nosse', 'brelnosse42@gmail.com', '673809889', '$2y$10$1vvqjzERGnQEt4MaKxPzfuWHsgMLiYX09jX2YlhJg/vp2i8pUJwo6', 'student', '2025-05-10 07:49:56', NULL, NULL),
	(25, 'Brel', 'Nosse', 'jfk19736@gmail.com', '673809885', '$2y$10$UQb8qqc2z9w8scbXP.i3Ue8tP6EvczWkfCVWh0XiY/OIsEJ2sky6y', 'admin', '2025-05-10 08:01:24', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table alertresults.user_tokens : ~0 rows (environ)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
