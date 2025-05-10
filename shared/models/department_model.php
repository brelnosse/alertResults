<?php
/**
 * Modèle pour la gestion des départements
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les départements
 * dans le système, y compris la récupération, l'ajout, la mise à jour et la suppression.
 * 
 * @package     AlertResults
 * @subpackage  Models
 * @category    Département
 * @author      v0
 * @version     1.0
 */

// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Récupère tous les départements
 * 
 * @param bool $activeOnly Si true, récupère uniquement les départements actifs
 * @return array Liste de tous les départements
 */
function getAllDepartments($activeOnly = false) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM departements";
        if ($activeOnly) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY nom";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des départements: ' . $e->getMessage());
        return [];
    }
}

// /**
//  * Récupère un département par son ID
//  * 
//  * @param int $id ID du département
//  * @return array|bool Informations du département ou false en cas d'erreur
//  */
// function getDepartmentById($id) {
//     global $pdo;
    
//     try {
//         $stmt = $pdo->prepare("SELECT * FROM departements WHERE id = ?");
//         $stmt->execute([$id]);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         error_log('Erreur lors de la récupération du département: ' . $e->getMessage());
//         return false;
//     }
// }

function getDepartmentId($derpartmentName) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM departements WHERE nom = ?");
        $stmt->execute([$derpartmentName]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'ID du département: ' . $e->getMessage());
        return false;
    }
}
/**
 * Récupère un département par son code
 * 
 * @param string $code Code du département
 * @return array|bool Informations du département ou false en cas d'erreur
 */
function getDepartmentByCode($code) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM departements WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du département par code: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère un département par son nom
 * 
 * @param string $nom Nom du département
 * @return array|bool Informations du département ou false en cas d'erreur
 */
function getDepartmentByName($nom) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM departements WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du département par nom: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute un nouveau département
 * 
 * @param string $nom Nom du département
 * @param string $code Code du département
 * @param string $description Description du département
 * @param bool $active Statut d'activation du département
 * @return int|bool ID du département ajouté ou false en cas d'erreur
 */
function addDepartment($nom, $code, $description = '', $active = true) {
    global $pdo;
    
    try {
        // Vérifier si le département existe déjà
        $stmt = $pdo->prepare("SELECT id FROM departements WHERE nom = ? OR code = ?");
        $stmt->execute([$nom, $code]);
        if ($stmt->rowCount() > 0) {
            return false; // Le département existe déjà
        }
        
        $stmt = $pdo->prepare("INSERT INTO departements (nom, code, description, active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nom, $code, $description, $active ? 1 : 0]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour un département existant
 * 
 * @param int $id ID du département
 * @param string $nom Nom du département
 * @param string $code Code du département
 * @param string $description Description du département
 * @param bool $active Statut d'activation du département
 * @return bool True si la mise à jour a réussi, false sinon
 */
function updateDepartment($id, $nom, $code, $description = '', $active = true) {
    global $pdo;
    
    try {
        // Vérifier si le département existe
        $stmt = $pdo->prepare("SELECT id FROM departements WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 0) {
            return false; // Le département n'existe pas
        }
        
        // Vérifier si le nom ou le code existe déjà pour un autre département
        $stmt = $pdo->prepare("SELECT id FROM departements WHERE (nom = ? OR code = ?) AND id != ?");
        $stmt->execute([$nom, $code, $id]);
        if ($stmt->rowCount() > 0) {
            return false; // Le nom ou le code existe déjà pour un autre département
        }
        
        $stmt = $pdo->prepare("UPDATE departements SET nom = ?, code = ?, description = ?, active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nom, $code, $description, $active ? 1 : 0, $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la mise à jour du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime un département
 * 
 * @param int $id ID du département
 * @return bool True si la suppression a réussi, false sinon
 */
function deleteDepartment($id) {
    global $pdo;
    
    try {
        // Vérifier si le département est utilisé dans d'autres tables
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departement_specialites WHERE departement_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Le département est utilisé dans la table departement_specialites
        }
        
        // Vérifier si des administrateurs sont associés à ce département
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_details WHERE departement_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Le département est utilisé dans la table admin_details
        }
        
        // Vérifier si des étudiants sont associés à ce département
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_details WHERE departement_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Le département est utilisé dans la table student_details
        }
        
        // Vérifier si des enseignants sont associés à ce département
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_details WHERE departement_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Le département est utilisé dans la table teacher_details
        }
        
        // Supprimer le département
        $stmt = $pdo->prepare("DELETE FROM departements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Désactive un département sans le supprimer
 * 
 * @param int $id ID du département
 * @return bool True si la désactivation a réussi, false sinon
 */
function deactivateDepartment($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE departements SET active = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la désactivation du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Active un département
 * 
 * @param int $id ID du département
 * @return bool True si l'activation a réussi, false sinon
 */
function activateDepartment($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE departements SET active = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'activation du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les départements gérés par un chef de département
 * 
 * @param int $userId ID de l'utilisateur (chef de département)
 * @return array Liste des départements gérés par le chef de département
 */
function getDepartmentsByHead($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT d.* 
            FROM departements d
            JOIN admin_details ad ON d.id = ad.departement_id
            JOIN users u ON ad.user_id = u.id
            WHERE u.id = ? AND ad.role = 'chef_departement'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des départements par chef: ' . $e->getMessage());
        return [];
    }
}

/**
 * Vérifie si un utilisateur est chef d'un département spécifique
 * 
 * @param int $userId ID de l'utilisateur
 * @param int $departmentId ID du département
 * @return bool True si l'utilisateur est chef du département, false sinon
 */
function isHeadOfDepartment($userId, $departmentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM admin_details 
            WHERE user_id = ? AND departement_id = ? AND role = 'chef_departement'
        ");
        $stmt->execute([$userId, $departmentId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification du chef de département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le chef d'un département spécifique
 * 
 * @param int $departmentId ID du département
 * @return array|bool Informations sur le chef de département ou false si aucun chef n'est trouvé
 */
function getDepartmentHead($departmentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, ad.* 
            FROM users u
            JOIN admin_details ad ON u.id = ad.user_id
            WHERE ad.departement_id = ? AND ad.role = 'chef_departement'
            LIMIT 1
        ");
        $stmt->execute([$departmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du chef de département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Compte le nombre d'étudiants par département
 * 
 * @param int $departmentId ID du département (optionnel)
 * @return array|int Nombre d'étudiants par département ou pour un département spécifique
 */
function countStudentsByDepartment($departmentId = null) {
    global $pdo;
    
    try {
        if ($departmentId !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM student_details 
                WHERE departement_id = ?
            ");
            $stmt->execute([$departmentId]);
            return $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("
                SELECT d.id, d.nom, COUNT(sd.id) as count
                FROM departements d
                LEFT JOIN student_details sd ON d.id = sd.departement_id
                GROUP BY d.id, d.nom
                ORDER BY d.nom
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des étudiants par département: ' . $e->getMessage());
        return $departmentId !== null ? 0 : [];
    }
}

/**
 * Compte le nombre d'enseignants par département
 * 
 * @param int $departmentId ID du département (optionnel)
 * @return array|int Nombre d'enseignants par département ou pour un département spécifique
 */
function countTeachersByDepartment($departmentId = null) {
    global $pdo;
    
    try {
        if ($departmentId !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM teacher_details 
                WHERE departement_id = ?
            ");
            $stmt->execute([$departmentId]);
            return $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("
                SELECT d.id, d.nom, COUNT(td.id) as count
                FROM departements d
                LEFT JOIN teacher_details td ON d.id = td.departement_id
                GROUP BY d.id, d.nom
                ORDER BY d.nom
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des enseignants par département: ' . $e->getMessage());
        return $departmentId !== null ? 0 : [];
    }
}

/**
 * Vérifie si un département existe
 * 
 * @param int $id ID du département
 * @return bool True si le département existe, false sinon
 */
function departmentExists($nom) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departements WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'existence du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les statistiques d'un département
 * 
 * @param int $departmentId ID du département
 * @return array Statistiques du département
 */
function getDepartmentStats($departmentId) {
    $stats = [
        'total_students' => countStudentsByDepartment($departmentId),
        'total_teachers' => countTeachersByDepartment($departmentId),
        'total_specialities' => 0,
        'pending_students' => 0,
        'approved_students' => 0,
        'rejected_students' => 0
    ];
    
    global $pdo;
    
    try {
        // Compter le nombre de spécialités
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM departement_specialites 
            WHERE departement_id = ?
        ");
        $stmt->execute([$departmentId]);
        $stats['total_specialities'] = $stmt->fetchColumn();
        
        // Compter les étudiants par statut
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as count
            FROM student_details
            WHERE departement_id = ?
            GROUP BY status
        ");
        $stmt->execute([$departmentId]);
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stats['pending_students'] = isset($statusCounts['pending']) ? $statusCounts['pending'] : 0;
        $stats['approved_students'] = isset($statusCounts['approved']) ? $statusCounts['approved'] : 0;
        $stats['rejected_students'] = isset($statusCounts['rejected']) ? $statusCounts['rejected'] : 0;
        
        return $stats;
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des statistiques du département: ' . $e->getMessage());
        return $stats;
    }
}
?>
