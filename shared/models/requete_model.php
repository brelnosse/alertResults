<?php
/**
 * Modèle pour la gestion des requêtes d'étudiants
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les requêtes
 * des étudiants concernant des erreurs potentielles dans les matières.
 * 
 * @package     AlertResults
 * @subpackage  Models
 * @category    Requêtes
 * @author      v0
 * @version     1.0
 */

// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Crée une nouvelle requête
 * 
 * @param int $studentId ID de l'étudiant
 * @param int $teacherId ID de l'enseignant
 * @param int $matiereId ID de la matière
 * @param string $sujet Sujet de la requête
 * @param string $description Description détaillée de la requête
 * @param string $type Type de requête (erreur_note, erreur_absence, autre)
 * @param int $departmentId ID du département
 * @return int|bool ID de la requête créée ou false en cas d'erreur
 */
function createRequete($studentId, $teacherId, $matiereId, $sujet, $description, $type, $departmentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO requetes (
                student_id, 
                teacher_id, 
                matiere_id, 
                department_id,
                sujet, 
                description, 
                type,
                statut, 
                date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
        ");
        
        $stmt->execute([
            $studentId, 
            $teacherId, 
            $matiereId, 
            $departmentId,
            $sujet, 
            $description, 
            $type
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de la création de la requête: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère toutes les requêtes d'un étudiant
 * 
 * @param int $studentId ID de l'étudiant
 * @return array Liste des requêtes de l'étudiant
 */
function getRequetesByStudent($studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   m.libelle as matiere_nom, 
                   u.firstname as teacher_firstname, 
                   u.lastname as teacher_lastname,
                   d.nom as department_nom
            FROM requetes r
            LEFT JOIN matieres m ON r.matiere_id = m.id
            LEFT JOIN users u ON r.teacher_id = u.id
            LEFT JOIN departements d ON r.department_id = d.id
            WHERE r.student_id = ?
            ORDER BY r.date_creation DESC
        ");
        
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des requêtes: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère une requête spécifique par son ID
 * 
 * @param int $requeteId ID de la requête
 * @return array|bool Informations de la requête ou false en cas d'erreur
 */
function getRequeteById($requeteId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   m.libelle as matiere_nom, 
                   u.firstname as teacher_firstname, 
                   u.lastname as teacher_lastname,
                   d.nom as department_nom,
                   s.firstname as student_firstname,
                   s.lastname as student_lastname
            FROM requetes r
            LEFT JOIN matieres m ON r.matiere_id = m.id
            LEFT JOIN users u ON r.teacher_id = u.id
            LEFT JOIN users s ON r.student_id = s.id
            LEFT JOIN departements d ON r.department_id = d.id
            WHERE r.id = ?
        ");
        
        $stmt->execute([$requeteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de la requête: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les réponses à une requête
 * 
 * @param int $requeteId ID de la requête
 * @return array Liste des réponses à la requête
 */
function getReponsesForRequete($requeteId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT rr.*, 
                   u.firstname, 
                   u.lastname, 
                   u.user_type
            FROM requetes_reponses rr
            JOIN users u ON rr.user_id = u.id
            WHERE rr.requete_id = ?
            ORDER BY rr.date_creation ASC
        ");
        
        $stmt->execute([$requeteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des réponses: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ajoute une réponse à une requête
 * 
 * @param int $requeteId ID de la requête
 * @param int $userId ID de l'utilisateur qui répond
 * @param string $contenu Contenu de la réponse
 * @return int|bool ID de la réponse créée ou false en cas d'erreur
 */
function addReponseToRequete($requeteId, $userId, $contenu) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO requetes_reponses (
                requete_id, 
                user_id, 
                contenu, 
                date_creation
            ) VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$requeteId, $userId, $contenu]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout de la réponse: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour le statut d'une requête
 * 
 * @param int $requeteId ID de la requête
 * @param string $statut Nouveau statut (en_attente, approuvee, rejetee, resolue)
 * @param int $userId ID de l'utilisateur qui met à jour le statut
 * @return bool True si la mise à jour a réussi, false sinon
 */
function updateRequeteStatus($requeteId, $statut, $userId = null) {
    global $pdo;
    
    try {
        $query = "UPDATE requetes SET statut = ?, date_modification = NOW()";
        $params = [$statut];
        
        if ($statut === 'approuvee' || $statut === 'rejetee') {
            $query .= ", validated_by = ?";
            $params[] = $userId;
        } elseif ($statut === 'resolue') {
            $query .= ", resolved_by = ?, date_resolution = NOW()";
            $params[] = $userId;
        }
        
        $query .= " WHERE id = ?";
        $params[] = $requeteId;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la mise à jour du statut de la requête: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les requêtes en attente pour un département
 * 
 * @param int $departmentId ID du département
 * @return array Liste des requêtes en attente
 */
function getPendingRequetesByDepartment($departmentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   m.libelle as matiere_nom, 
                   t.firstname as teacher_firstname, 
                   t.lastname as teacher_lastname,
                   s.firstname as student_firstname,
                   s.lastname as student_lastname
            FROM requetes r
            LEFT JOIN matieres m ON r.matiere_id = m.id
            LEFT JOIN users t ON r.teacher_id = t.id
            LEFT JOIN users s ON r.student_id = s.id
            WHERE r.department_id = ? AND r.statut = 'en_attente'
            ORDER BY r.date_creation ASC
        ");
        
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des requêtes en attente: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les requêtes approuvées pour un enseignant
 * 
 * @param int $teacherId ID de l'enseignant
 * @return array Liste des requêtes approuvées
 */
function getApprovedRequetesByTeacher($teacherId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   m.libelle as matiere_nom, 
                   s.firstname as student_firstname, 
                   s.lastname as student_lastname,
                   d.nom as department_nom
            FROM requetes r
            LEFT JOIN matieres m ON r.matiere_id = m.id
            LEFT JOIN users s ON r.student_id = s.id
            LEFT JOIN departements d ON r.department_id = d.id
            WHERE r.teacher_id = ? AND r.statut = 'approuvee'
            ORDER BY r.date_creation ASC
        ");
        
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des requêtes approuvées: ' . $e->getMessage());
        return [];
    }
}

/**
 * Vérifie si un utilisateur a le droit de voir une requête
 * 
 * @param int $requeteId ID de la requête
 * @param int $userId ID de l'utilisateur
 * @param string $userType Type d'utilisateur (student, teacher, admin)
 * @return bool True si l'utilisateur a le droit, false sinon
 */
function canUserAccessRequete($requeteId, $userId, $userType) {
    global $pdo;
    
    try {
        if ($userType === 'admin') {
            // Les administrateurs ont accès à toutes les requêtes
            return true;
        } elseif ($userType === 'student') {
            // Les étudiants ont accès uniquement à leurs propres requêtes
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM requetes WHERE id = ? AND student_id = ?");
            $stmt->execute([$requeteId, $userId]);
            return $stmt->fetchColumn() > 0;
        } elseif ($userType === 'teacher') {
            // Les enseignants ont accès aux requêtes qui leur sont adressées et qui sont approuvées
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM requetes WHERE id = ? AND teacher_id = ? AND statut IN ('approuvee', 'resolue')");
            $stmt->execute([$requeteId, $userId]);
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification des droits d\'accès: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le nombre de requêtes par statut pour un étudiant
 * 
 * @param int $studentId ID de l'étudiant
 * @return array Nombre de requêtes par statut
 */
function getRequeteCountsByStatusForStudent($studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT statut, COUNT(*) as count
            FROM requetes
            WHERE student_id = ?
            GROUP BY statut
        ");
        
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Initialiser tous les statuts à 0
        $counts = [
            'en_attente' => 0,
            'approuvee' => 0,
            'rejetee' => 0,
            'resolue' => 0,
            'total' => 0
        ];
        
        // Mettre à jour avec les résultats de la requête
        foreach ($results as $statut => $count) {
            $counts[$statut] = $count;
            $counts['total'] += $count;
        }
        
        return $counts;
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des requêtes: ' . $e->getMessage());
        return [
            'en_attente' => 0,
            'approuvee' => 0,
            'rejetee' => 0,
            'resolue' => 0,
            'total' => 0
        ];
    }
}

/**
 * Récupère les enseignants qui enseignent à un étudiant
 * 
 * @param int $studentId ID de l'étudiant
 * @return array Liste des enseignants
 */
function getTeachersForStudent($studentId) {
    global $pdo;
    
    try {
        // Récupérer d'abord les détails de l'étudiant
        $stmt = $pdo->prepare("
            SELECT cycle, niveau, specialite, classe, departement_id
            FROM student_details
            WHERE user_id = ?
        ");
        $stmt->execute([$studentId]);
        $studentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$studentDetails) {
            return [];
        }
        
        // Récupérer les enseignants qui enseignent des matières pour cette classe/niveau/spécialité
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.firstname, u.lastname
            FROM users u
            JOIN teacher_details td ON u.id = td.user_id
            JOIN matieres_enseignees me ON td.id = me.id_enseignant
            JOIN matieres m ON me.id_matiere = m.id
            WHERE m.niveau = ? AND me.statut = 'approved'
            ORDER BY u.lastname, u.firstname
        ");
        
        $stmt->execute([$studentDetails['niveau']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des enseignants: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les matières enseignées par un enseignant pour un niveau donné
 * 
 * @param int $teacherId ID de l'enseignant
 * @param string $niveau Niveau de l'étudiant
 * @return array Liste des matières
 */
function getMatieresForTeacherAndNiveau($teacherId, $niveau) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT m.id, m.libelle, m.code
            FROM matieres m
            JOIN matieres_enseignees me ON m.id = me.id_matiere
            JOIN teacher_details td ON me.id_enseignant = td.id
            WHERE td.user_id = ? AND m.niveau = ? AND me.statut = 'approved'
            ORDER BY m.libelle
        ");
        
        $stmt->execute([$teacherId, $niveau]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des matières: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère le département d'un étudiant
 * 
 * @param int $studentId ID de l'étudiant
 * @return int|bool ID du département ou false en cas d'erreur
 */
function getStudentDepartment($studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT departement_id
            FROM student_details
            WHERE user_id = ?
        ");
        
        $stmt->execute([$studentId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du département de l\'étudiant: ' . $e->getMessage());
        return false;
    }
}
?>
