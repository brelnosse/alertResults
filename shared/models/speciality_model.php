<?php
/**
 * Modèle pour la gestion des spécialités
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les spécialités
 * dans le système, y compris la récupération, l'ajout, la mise à jour et la suppression.
 * 
 * @package     AlertResults
 * @subpackage  Models
 * @category    Spécialité
 * @author      v0
 * @version     1.0
 */

// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Récupère toutes les spécialités
 * 
 * @param bool $activeOnly Si true, récupère uniquement les spécialités actives
 * @return array Liste de toutes les spécialités
 */
function getAllSpecialities($activeOnly = false) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM specialites";
        if ($activeOnly) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY nom";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des spécialités: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère une spécialité par son ID
 * 
 * @param int $id ID de la spécialité
 * @return array|bool Informations de la spécialité ou false en cas d'erreur
 */
function getSpecialityById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM specialites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère une spécialité par son code
 * 
 * @param string $code Code de la spécialité
 * @return array|bool Informations de la spécialité ou false en cas d'erreur
 */
function getSpecialityByCode($code) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM specialites WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de la spécialité par code: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère une spécialité par son nom
 * 
 * @param string $nom Nom de la spécialité
 * @return array|bool Informations de la spécialité ou false en cas d'erreur
 */
function getSpecialityByName($nom) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM specialites WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de la spécialité par nom: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute une nouvelle spécialité
 * 
 * @param string $nom Nom de la spécialité
 * @param string $code Code de la spécialité
 * @param string $description Description de la spécialité
 * @param bool $active Statut d'activation de la spécialité
 * @return int|bool ID de la spécialité ajoutée ou false en cas d'erreur
 */
function addSpeciality($nom, $code, $description = '', $active = true) {
    global $pdo;
    
    try {
        // Vérifier si la spécialité existe déjà
        $stmt = $pdo->prepare("SELECT id FROM specialites WHERE nom = ? OR code = ?");
        $stmt->execute([$nom, $code]);
        if ($stmt->rowCount() > 0) {
            return false; // La spécialité existe déjà
        }
        
        $stmt = $pdo->prepare("INSERT INTO specialites (nom, code, description, active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nom, $code, $description, $active ? 1 : 0]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour une spécialité existante
 * 
 * @param int $id ID de la spécialité
 * @param string $nom Nom de la spécialité
 * @param string $code Code de la spécialité
 * @param string $description Description de la spécialité
 * @param bool $active Statut d'activation de la spécialité
 * @return bool True si la mise à jour a réussi, false sinon
 */
function updateSpeciality($id, $nom, $code, $description = '', $active = true) {
    global $pdo;
    
    try {
        // Vérifier si la spécialité existe
        $stmt = $pdo->prepare("SELECT id FROM specialites WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 0) {
            return false; // La spécialité n'existe pas
        }
        
        // Vérifier si le nom ou le code existe déjà pour une autre spécialité
        $stmt = $pdo->prepare("SELECT id FROM specialites WHERE (nom = ? OR code = ?) AND id != ?");
        $stmt->execute([$nom, $code, $id]);
        if ($stmt->rowCount() > 0) {
            return false; // Le nom ou le code existe déjà pour une autre spécialité
        }
        
        $stmt = $pdo->prepare("UPDATE specialites SET nom = ?, code = ?, description = ?, active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nom, $code, $description, $active ? 1 : 0, $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la mise à jour de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime une spécialité
 * 
 * @param int $id ID de la spécialité
 * @return bool True si la suppression a réussi, false sinon
 */
function deleteSpeciality($id) {
    global $pdo;
    
    try {
        // Vérifier si la spécialité est utilisée dans d'autres tables
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departement_specialites WHERE specialite_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // La spécialité est utilisée dans la table departement_specialites
        }
        
        // Vérifier si des étudiants sont associés à cette spécialité
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_details WHERE specialite_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // La spécialité est utilisée dans la table student_details
        }
        
        // Vérifier si des enseignants sont associés à cette spécialité
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_specialites WHERE specialite_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // La spécialité est utilisée dans la table teacher_specialites
        }
        
        // Supprimer la spécialité
        $stmt = $pdo->prepare("DELETE FROM specialites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Désactive une spécialité sans la supprimer
 * 
 * @param int $id ID de la spécialité
 * @return bool True si la désactivation a réussi, false sinon
 */
function deactivateSpeciality($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE specialites SET active = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la désactivation de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Active une spécialité
 * 
 * @param int $id ID de la spécialité
 * @return bool True si l'activation a réussi, false sinon
 */
function activateSpeciality($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE specialites SET active = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'activation de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les spécialités d'un département
 * 
 * @param int $departmentId ID du département
 * @param bool $activeOnly Si true, récupère uniquement les spécialités actives
 * @return array Liste des spécialités du département
 */
function getSpecialitiesByDepartment($departmentId, $activeOnly = false) {
    global $pdo;
    
    try {
        $sql = "
            SELECT s.* 
            FROM specialites s
            JOIN departement_specialites ds ON s.id = ds.specialite_id
            WHERE ds.departement_id = ?
        ";
        
        if ($activeOnly) {
            $sql .= " AND s.active = 1";
        }
        
        $sql .= " ORDER BY s.nom";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des spécialités par département: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les départements d'une spécialité
 * 
 * @param int $specialityId ID de la spécialité
 * @param bool $activeOnly Si true, récupère uniquement les départements actifs
 * @return array Liste des départements de la spécialité
 */
function getDepartmentsBySpeciality($specialityId, $activeOnly = false) {
    global $pdo;
    
    try {
        $sql = "
            SELECT d.* 
            FROM departements d
            JOIN departement_specialites ds ON d.id = ds.departement_id
            WHERE ds.specialite_id = ?
        ";
        
        if ($activeOnly) {
            $sql .= " AND d.active = 1";
        }
        
        $sql .= " ORDER BY d.nom";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$specialityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des départements par spécialité: ' . $e->getMessage());
        return [];
    }
}

/**
 * Associe une spécialité à un département
 * 
 * @param int $specialityId ID de la spécialité
 * @param int $departmentId ID du département
 * @return bool True si l'association a réussi, false sinon
 */
function associateSpecialityToDepartment($specialityId, $departmentId) {
    global $pdo;
    
    try {
        // Vérifier si l'association existe déjà
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM departement_specialites 
            WHERE specialite_id = ? AND departement_id = ?
        ");
        $stmt->execute([$specialityId, $departmentId]);
        if ($stmt->fetchColumn() > 0) {
            return true; // L'association existe déjà
        }
        
        // Créer l'association
        $stmt = $pdo->prepare("
            INSERT INTO departement_specialites (specialite_id, departement_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$specialityId, $departmentId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'association de la spécialité au département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Dissocie une spécialité d'un département
 * 
 * @param int $specialityId ID de la spécialité
 * @param int $departmentId ID du département
 * @return bool True si la dissociation a réussi, false sinon
 */
function dissociateSpecialityFromDepartment($specialityId, $departmentId) {
    global $pdo;
    
    try {
        // Vérifier si des étudiants utilisent cette association
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM student_details 
            WHERE specialite_id = ? AND departement_id = ?
        ");
        $stmt->execute([$specialityId, $departmentId]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Des étudiants utilisent cette association
        }
        
        // Vérifier si des enseignants utilisent cette association
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM teacher_specialites ts
            JOIN teacher_details td ON ts.teacher_id = td.id
            WHERE ts.specialite_id = ? AND td.departement_id = ?
        ");
        $stmt->execute([$specialityId, $departmentId]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Des enseignants utilisent cette association
        }
        
        // Supprimer l'association
        $stmt = $pdo->prepare("
            DELETE FROM departement_specialites 
            WHERE specialite_id = ? AND departement_id = ?
        ");
        $stmt->execute([$specialityId, $departmentId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la dissociation de la spécialité du département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si une spécialité est associée à un département
 * 
 * @param int $specialityId ID de la spécialité
 * @param int $departmentId ID du département
 * @return bool True si la spécialité est associée au département, false sinon
 */
function isSpecialityAssociatedToDepartment($specialityId, $departmentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM departement_specialites 
            WHERE specialite_id = ? AND departement_id = ?
        ");
        $stmt->execute([$specialityId, $departmentId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'association spécialité-département: ' . $e->getMessage());
        return false;
    }
}

/**
 * Compte le nombre d'étudiants par spécialité
 * 
 * @param int $specialityId ID de la spécialité (optionnel)
 * @return array|int Nombre d'étudiants par spécialité ou pour une spécialité spécifique
 */
function countStudentsBySpeciality($specialityId = null) {
    global $pdo;
    
    try {
        if ($specialityId !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM student_details 
                WHERE specialite_id = ?
            ");
            $stmt->execute([$specialityId]);
            return $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("
                SELECT s.id, s.nom, COUNT(sd.id) as count
                FROM specialites s
                LEFT JOIN student_details sd ON s.id = sd.specialite_id
                GROUP BY s.id, s.nom
                ORDER BY s.nom
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des étudiants par spécialité: ' . $e->getMessage());
        return $specialityId !== null ? 0 : [];
    }
}

/**
 * Compte le nombre d'enseignants par spécialité
 * 
 * @param int $specialityId ID de la spécialité (optionnel)
 * @return array|int Nombre d'enseignants par spécialité ou pour une spécialité spécifique
 */
function countTeachersBySpeciality($specialityId = null) {
    global $pdo;
    
    try {
        if ($specialityId !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM teacher_specialites 
                WHERE specialite_id = ?
            ");
            $stmt->execute([$specialityId]);
            return $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("
                SELECT s.id, s.nom, COUNT(ts.teacher_id) as count
                FROM specialites s
                LEFT JOIN teacher_specialites ts ON s.id = ts.specialite_id
                GROUP BY s.id, s.nom
                ORDER BY s.nom
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des enseignants par spécialité: ' . $e->getMessage());
        return $specialityId !== null ? 0 : [];
    }
}

/**
 * Vérifie si une spécialité existe
 * 
 * @param int $id ID de la spécialité
 * @return bool True si la spécialité existe, false sinon
 */
function specialityExists($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM specialites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'existence de la spécialité: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les spécialités par cycle et niveau
 * 
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @param int $departmentId ID du département (optionnel)
 * @return array Liste des spécialités pour le cycle et le niveau spécifiés
 */
function getSpecialitiesByCycleAndLevel($cycle, $niveau, $departmentId = null) {
    global $pdo;
    
    try {
        $params = [$cycle, $niveau];
        $sql = "
            SELECT s.* 
            FROM specialites s
            JOIN cycle_niveau_specialite cns ON s.id = cns.specialite_id
            WHERE cns.cycle = ? AND cns.niveau = ?
        ";
        
        if ($departmentId !== null) {
            $sql .= " AND s.id IN (
                SELECT specialite_id 
                FROM departement_specialites 
                WHERE departement_id = ?
            )";
            $params[] = $departmentId;
        }
        
        $sql .= " ORDER BY s.nom";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des spécialités par cycle et niveau: ' . $e->getMessage());
        return [];
    }
}

/**
 * Associe une spécialité à un cycle et un niveau
 * 
 * @param int $specialityId ID de la spécialité
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @return bool True si l'association a réussi, false sinon
 */
function associateSpecialityToCycleAndLevel($specialityId, $cycle, $niveau) {
    global $pdo;
    
    try {
        // Vérifier si l'association existe déjà
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM cycle_niveau_specialite 
            WHERE specialite_id = ? AND cycle = ? AND niveau = ?
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        if ($stmt->fetchColumn() > 0) {
            return true; // L'association existe déjà
        }
        
        // Créer l'association
        $stmt = $pdo->prepare("
            INSERT INTO cycle_niveau_specialite (specialite_id, cycle, niveau, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'association de la spécialité au cycle et niveau: ' . $e->getMessage());
        return false;
    }
}

/**
 * Dissocie une spécialité d'un cycle et d'un niveau
 * 
 * @param int $specialityId ID de la spécialité
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @return bool True si la dissociation a réussi, false sinon
 */
function dissociateSpecialityFromCycleAndLevel($specialityId, $cycle, $niveau) {
    global $pdo;
    
    try {
        // Vérifier si des étudiants utilisent cette association
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM student_details 
            WHERE specialite_id = ? AND cycle = ? AND niveau = ?
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Des étudiants utilisent cette association
        }
        
        // Supprimer l'association
        $stmt = $pdo->prepare("
            DELETE FROM cycle_niveau_specialite 
            WHERE specialite_id = ? AND cycle = ? AND niveau = ?
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la dissociation de la spécialité du cycle et niveau: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si une spécialité est associée à un cycle et un niveau
 * 
 * @param int $specialityId ID de la spécialité
 * @param string $cycle Cycle (Licence, Master, Doctorat)
 * @param int $niveau Niveau (1, 2, 3)
 * @return bool True si la spécialité est associée au cycle et niveau, false sinon
 */
function isSpecialityAssociatedToCycleAndLevel($specialityId, $cycle, $niveau) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM cycle_niveau_specialite 
            WHERE specialite_id = ? AND cycle = ? AND niveau = ?
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'association spécialité-cycle-niveau: ' . $e->getMessage());
        return false;
    }
}

function tableExists($tableName) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_name = ?
        ");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'existence de la table: ' . $e->getMessage());
        return false;
    }
}

function getSpecialiteId($specialityName) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id 
            FROM specialites 
            WHERE nom = ?
        ");
        $stmt->execute([$specialityName]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'ID de la spécialité: ' . $e->getMessage());
        return false;
    }
}

function isSpecialiteAvailableForCycleAndNiveau($specialityId, $cycle, $niveau) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM cycle_niveau_specialite 
            WHERE specialite_id = ? AND cycle = ? AND niveau = ?
        ");
        $stmt->execute([$specialityId, $cycle, $niveau]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de la disponibilité de la spécialité pour le cycle et le niveau: ' . $e->getMessage());
        return false;
    }
}

function getDepartmentHeadsBySpecialite($specialite){
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT d.* 
            FROM departements d
            JOIN departement_specialites ds ON d.id = ds.departement_id
            WHERE ds.specialite_id = ? AND d.head = 1
        ");
        $stmt->execute([$specialite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des responsables de départements par spécialité: ' . $e->getMessage());
        return [];
    }
}

function addSpecialite($specialityName) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO specialites (nom, created_at) 
            VALUES (?, NOW())
        ");
        $stmt->execute([$specialityName]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout de la spécialité: ' . $e->getMessage());
        return false;
    }
}
function addTeacherSpecialite($userId, $specialiteName) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO teacher_specialites (teacher_id, specialite_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, $specialiteName]);
        return true;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout de la spécialité à l\'enseignant: ' . $e->getMessage());
        return false;
    }
}
?>
