<?php
/**
 * Modèle pour la gestion des associations entre départements et spécialités
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les associations
 * entre départements et spécialités dans le système.
 * 
 * @package     AlertResults
 * @subpackage  Models
 * @category    DepartementSpecialite
 * @author      v0
 * @version     1.0
 */

// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Récupère toutes les associations département-spécialité
 * 
 * @return array Liste de toutes les associations département-spécialité
 */
function getAllDepartmentSpecialities() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT ds.*, d.nom as departement_nom, s.nom as specialite_nom
            FROM departement_specialites ds
            JOIN departements d ON ds.departement_id = d.id
            JOIN specialites s ON ds.specialite_id = s.id
            ORDER BY d.nom, s.nom
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des associations département-spécialité: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère une association département-spécialité par son ID
 * 
 * @param int $id ID de l'association
 * @return array|bool Informations de l'association ou false en cas d'erreur
 */
function getDepartmentSpecialityById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT ds.*, d.nom as departement_nom, s.nom as specialite_nom
            FROM departement_specialites ds
            JOIN departements d ON ds.departement_id = d.id
            JOIN specialites s ON ds.specialite_id = s.id
            WHERE ds.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère une association département-spécialité par département et spécialité
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @return array|bool Informations de l'association ou false en cas d'erreur
 */
function getDepartmentSpecialityByDepartmentAndSpeciality($departmentId, $specialityId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT ds.*, d.nom as departement_nom, s.nom as specialite_nom
            FROM departement_specialites ds
            JOIN departements d ON ds.departement_id = d.id
            JOIN specialites s ON ds.specialite_id = s.id
            WHERE ds.departement_id = ? AND ds.specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute une nouvelle association département-spécialité
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @return int|bool ID de l'association ajoutée ou false en cas d'erreur
 */
function addDepartmentSpeciality($departmentId, $specialityId) {
    global $pdo;
    
    try {
        // Vérifier si l'association existe déjà
        $stmt = $pdo->prepare("
            SELECT id FROM departement_specialites 
            WHERE departement_id = ? AND specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        if ($stmt->rowCount() > 0) {
            return false; // L'association existe déjà
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO departement_specialites (departement_id, specialite_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$departmentId, $specialityId]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime une association département-spécialité
 * 
 * @param int $id ID de l'association
 * @return bool True si la suppression a réussi, false sinon
 */
function deleteDepartmentSpeciality($id) {
    global $pdo;
    
    try {
        // Vérifier si l'association est utilisée par des étudiants
        $stmt = $pdo->prepare("
            SELECT ds.departement_id, ds.specialite_id
            FROM departement_specialites ds
            WHERE ds.id = ?
        ");
        $stmt->execute([$id]);
        $association = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($association) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM student_details 
                WHERE departement_id = ? AND specialite_id = ?
            ");
            $stmt->execute([$association['departement_id'], $association['specialite_id']]);
            if ($stmt->fetchColumn() > 0) {
                return false; // L'association est utilisée par des étudiants
            }
            
            // Vérifier si l'association est utilisée par des enseignants
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM teacher_specialites ts
                JOIN teacher_details td ON ts.teacher_id = td.id
                WHERE td.departement_id = ? AND ts.specialite_id = ?
            ");
            $stmt->execute([$association['departement_id'], $association['specialite_id']]);
            if ($stmt->fetchColumn() > 0) {
                return false; // L'association est utilisée par des enseignants
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM departement_specialites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime une association département-spécialité par département et spécialité
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @return bool True si la suppression a réussi, false sinon
 */
function deleteDepartmentSpecialityByDepartmentAndSpeciality($departmentId, $specialityId) {
    global $pdo;
    
    try {
        // Vérifier si l'association est utilisée par des étudiants
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM student_details 
            WHERE departement_id = ? AND specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        if ($stmt->fetchColumn() > 0) {
            return false; // L'association est utilisée par des étudiants
        }
        
        // Vérifier si l'association est utilisée par des enseignants
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM teacher_specialites ts
            JOIN teacher_details td ON ts.teacher_id = td.id
            WHERE td.departement_id = ? AND ts.specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        if ($stmt->fetchColumn() > 0) {
            return false; // L'association est utilisée par des enseignants
        }
        
        $stmt = $pdo->prepare("
            DELETE FROM departement_specialites 
            WHERE departement_id = ? AND specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si une association département-spécialité existe
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @return bool True si l'association existe, false sinon
 */
function departmentSpecialityExists($departmentId, $specialityId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM departement_specialites 
            WHERE departement_id = ? AND specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'existence de l\'association département-spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les spécialités disponibles pour un cycle et un niveau dans un département
 * 
 * @param int $departmentId ID du département
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @return array Liste des spécialités disponibles
 */
function getSpecialitiesForCycleAndLevelInDepartment($departmentId, $cycle, $niveau) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM specialites s
            JOIN departement_specialites ds ON s.id = ds.specialite_id
            JOIN cycle_niveau_specialite cns ON s.id = cns.specialite_id
            WHERE ds.departement_id = ? AND cns.cycle = ? AND cns.niveau = ? AND s.active = 1
            ORDER BY s.nom
        ");
        $stmt->execute([$departmentId, $cycle, $niveau]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des spécialités pour cycle et niveau dans un département: ' . $e->getMessage());
        return [];
    }
}

/**
 * Vérifie si une combinaison département-spécialité-cycle-niveau est valide
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @return bool True si la combinaison est valide, false sinon
 */
function isValidDepartmentSpecialityCycleLevel($departmentId, $specialityId, $cycle, $niveau) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM departement_specialites ds
            JOIN cycle_niveau_specialite cns ON ds.specialite_id = cns.specialite_id
            WHERE ds.departement_id = ? AND ds.specialite_id = ? AND cns.cycle = ? AND cns.niveau = ?
        ");
        $stmt->execute([$departmentId, $specialityId, $cycle, $niveau]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de la validité de la combinaison département-spécialité-cycle-niveau: ' . $e->getMessage());
        return false;
    }
}

/**
 * Compte le nombre d'étudiants par département et spécialité
 * 
 * @param int $departmentId ID du département
 * @param int $specialityId ID de la spécialité
 * @return int Nombre d'étudiants
 */
function countStudentsByDepartmentAndSpeciality($departmentId, $specialityId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM student_details 
            WHERE departement_id = ? AND specialite_id = ?
        ");
        $stmt->execute([$departmentId, $specialityId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des étudiants par département et spécialité: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Récupère les statistiques des associations département-spécialité
 * 
 * @return array Statistiques des associations
 */
function getDepartmentSpecialityStats() {
    global $pdo;
    
    try {
        $stats = [
            'total_associations' => 0,
            'departments_with_specialities' => 0,
            'specialities_with_departments' => 0,
            'most_popular_department' => null,
            'most_popular_speciality' => null
        ];
        
        // Nombre total d'associations
        $stmt = $pdo->query("SELECT COUNT(*) FROM departement_specialites");
        $stats['total_associations'] = $stmt->fetchColumn();
        
        // Nombre de départements avec au moins une spécialité
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT departement_id) 
            FROM departement_specialites
        ");
        $stats['departments_with_specialities'] = $stmt->fetchColumn();
        
        // Nombre de spécialités avec au moins un département
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT specialite_id) 
            FROM departement_specialites
        ");
        $stats['specialities_with_departments'] = $stmt->fetchColumn();
        
        // Département le plus populaire (avec le plus de spécialités)
        $stmt = $pdo->query("
            SELECT d.id, d.nom, COUNT(ds.specialite_id) as count
            FROM departements d
            JOIN departement_specialites ds ON d.id = ds.departement_id
            GROUP BY d.id, d.nom
            ORDER BY count DESC
            LIMIT 1
        ");
        $stats['most_popular_department'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Spécialité la plus populaire (avec le plus de départements)
        $stmt = $pdo->query("
            SELECT s.id, s.nom, COUNT(ds.departement_id) as count
            FROM specialites s
            JOIN departement_specialites ds ON s.id = ds.specialite_id
            GROUP BY s.id, s.nom
            ORDER BY count DESC
            LIMIT 1
        ");
        $stats['most_popular_speciality'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $stats;
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des statistiques des associations département-spécialité: ' . $e->getMessage());
        return $stats;
    }
}
?>
