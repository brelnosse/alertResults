<?php
// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Vérifie si un email existe déjà dans la base de données
 * 
 * @param string $email L'email à vérifier
 * @return bool True si l'email existe, false sinon
 */
function emailExists($email) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un numéro de téléphone existe déjà dans la base de données
 * 
 * @param string $phone Le numéro de téléphone à vérifier
 * @return bool True si le numéro existe, false sinon
 */
function phoneExists($phone) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un matricule existe déjà dans la base de données (pour les étudiants)
 * 
 * @param string $matricule Le matricule à vérifier
 * @return bool True si le matricule existe, false sinon
 */
function matriculeExists($matricule) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_details WHERE matricule = ?");
    $stmt->execute([$matricule]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie s'il existe déjà un administrateur avec le rôle de directeur
 * 
 * @return bool True s'il existe déjà un directeur, false sinon
 */
function directorExists() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_details WHERE role = 'directeur'");
    $stmt->execute();
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Ajoute un nouvel utilisateur dans la base de données
 * 
 * @param array $userData Les données de l'utilisateur
 * @return int|bool L'ID de l'utilisateur créé ou false en cas d'erreur
 */
function addUser($userData) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Insertion dans la table users
        $stmt = $pdo->prepare("
            INSERT INTO users (firstname, lastname, email, phone, password, user_type, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userData['firstname'],
            $userData['lastname'],
            $userData['email'],
            $userData['phone'],
            $userData['password'], // Déjà hashé dans le contrôleur
            $userData['user_type']
        ]);
        
        $userId = $pdo->lastInsertId();
        
        $pdo->commit();
        return $userId;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erreur lors de l\'ajout de l\'utilisateur: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute les détails d'un enseignant
 * 
 * @param int $userId L'ID de l'utilisateur
 * @param array $teacherData Les données de l'enseignant
 * @return bool True si l'opération a réussi, false sinon
 */
function addTeacherDetails($userId, $teacherData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO teacher_details (user_id, created_at) 
            VALUES (?, NOW())
        ");
        
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout des détails de l\'enseignant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute les détails d'un administrateur
 * 
 * @param int $userId L'ID de l'utilisateur
 * @param array $adminData Les données de l'administrateur
 * @return bool True si l'opération a réussi, false sinon
 */
function addAdminDetails($userId, $adminData) {
    global $pdo;
    
    try {
        // Vérifier s'il existe déjà un directeur si le rôle est directeur
        if ($adminData['role'] === 'directeur' && directorExists()) {
            throw new Exception('Il existe déjà un directeur dans le système');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_details (user_id, role, department, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $adminData['role'],
            $adminData['department'] // Peut être NULL pour les directeurs
        ]);
        return true;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout des détails de l\'administrateur: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log('Erreur: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère un département par son ID
 * 
 * @param int $id ID du département
 * @return array|bool Informations du département ou false en cas d'erreur
 */
function getDepartmentById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM departements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du département: ' . $e->getMessage());
        return false;
    }
}
/**
 * Ajoute les détails d'un étudiant
 * 
 * @param int $userId L'ID de l'utilisateur
 * @param array $studentData Les données de l'étudiant
 * @return bool True si l'opération a réussi, false sinon
 */
function addStudentDetails($userId, $studentData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO student_details (
                user_id, 
                birthdate, 
                matricule, 
                cycle, 
                niveau, 
                specialite, 
                classe, 
                status,
                created_at
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $userId,
            $studentData['birthdate'],
            $studentData['matricule'],
            $studentData['cycle'],
            $studentData['niveau'],
            $studentData['specialite'],
            $studentData['classe']
        ]);
        return true;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'ajout des détails de l\'étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les étudiants en attente de validation pour un département spécifique
 * 
 * @param string $department Le département
 * @return array Les étudiants en attente
 */
function getPendingStudentsByDepartment($department) {
    global $pdo;
    
    try {
        // Vérifier si les nouvelles tables existent
        $stmt = $pdo->query("SHOW TABLES LIKE 'departement_specialites'");
        $useNewTables = ($stmt->rowCount() > 0);
        
        if ($useNewTables) {
            // Utiliser la nouvelle structure avec les tables de relation
            return getPendingStudentsByDepartmentNew($department);
        } else {
            // Utiliser l'ancienne méthode directe
            return getPendingStudentsByDepartmentOld($department);
        }
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des étudiants en attente: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ancienne méthode pour récupérer les étudiants en attente par département
 * 
 * @param string $department Le département
 * @return array Les étudiants en attente
 */
function getPendingStudentsByDepartmentOld($department) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                u.id, 
                u.firstname, 
                u.lastname, 
                u.email, 
                u.phone, 
                u.created_at,
                sd.matricule, 
                sd.birthdate, 
                sd.cycle, 
                sd.niveau, 
                sd.specialite, 
                sd.classe
            FROM 
                users u
            JOIN 
                student_details sd ON u.id = sd.user_id
            WHERE 
                sd.status = 'pending'
        ";
        
        // Si un département est spécifié, filtrer par spécialité
        // Cette logique dépend de comment vous associez les départements aux spécialités
        if (!empty($department)) {
            // Exemple: associer des spécialités à des départements
            $departmentSpecialties = getDepartmentSpecialtiesOld($department);
            if (!empty($departmentSpecialties)) {
                $placeholders = implode(',', array_fill(0, count($departmentSpecialties), '?'));
                $query .= " AND sd.specialite IN ($placeholders)";
                $params = $departmentSpecialties;
            } else {
                // Si aucune spécialité n'est associée au département, ne rien retourner
                return [];
            }
        } else {
            $params = [];
        }
        
        $query .= " ORDER BY u.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des étudiants en attente (ancienne méthode): ' . $e->getMessage());
        return [];
    }
}

/**
 * Nouvelle méthode pour récupérer les étudiants en attente par département
 * 
 * @param string $department Le département
 * @return array Les étudiants en attente
 */
function getPendingStudentsByDepartmentNew($department) {
    global $pdo;
    
    try {
        // Récupérer l'ID du département
        $departmentId = null;
        if (!empty($department)) {
            $stmt = $pdo->prepare("SELECT id FROM departements WHERE nom = ? OR code = ?");
            $stmt->execute([$department, $department]);
            $departmentId = $stmt->fetchColumn();
            
            if (!$departmentId) {
                // Département non trouvé
                return [];
            }
        }
        
        // Construire la requête de base
        $query = "
            SELECT 
                u.id, 
                u.firstname, 
                u.lastname, 
                u.email, 
                u.phone, 
                u.created_at,
                sd.matricule, 
                sd.birthdate, 
                sd.cycle, 
                sd.niveau, 
                sd.specialite, 
                sd.classe
            FROM 
                users u
            JOIN 
                student_details sd ON u.id = sd.user_id
            WHERE 
                sd.status = 'pending'
        ";
        
        $params = [];
        
        // Si un département est spécifié, filtrer par les spécialités de ce département
        if ($departmentId) {
            $query .= " AND sd.specialite IN (
                SELECT s.nom FROM specialites s
                JOIN departement_specialites ds ON s.id = ds.specialite_id
                WHERE ds.departement_id = ?
            )";
            $params[] = $departmentId;
        }
        
        $query .= " ORDER BY u.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des étudiants en attente (nouvelle méthode): ' . $e->getMessage());
        return [];
    }
}

/**
 * Ancienne méthode pour récupérer les spécialités d'un département
 * 
 * @param string $department Le département
 * @return array Les spécialités du département
 */
function getDepartmentSpecialtiesOld($department) {
    // Cette fonction doit être adaptée selon votre logique d'association
    // entre départements et spécialités
    $departmentMap = [
        'informatique' => ['dev fullstack web', 'data-science', 'robotic', 'gl', 'rs', 'msi', 'iia', 'iwd', 'pam'],
        'prepa' => ['prepa 3il', 'ingenieur 1']
        // Ajoutez d'autres départements selon votre structure
    ];
    
    return $departmentMap[strtolower($department)] ?? [];
}

/**
 * Valide ou rejette un compte étudiant
 * 
 * @param int $studentId L'ID de l'étudiant
 * @param string $action L'action (approve/reject)
 * @param string $rejectionReason La raison du rejet (si applicable)
 * @param int $adminId L'ID de l'administrateur qui valide/rejette
 * @return bool True si l'opération a réussi, false sinon
 */
function validateStudentAccount($studentId, $action, $rejectionReason = null, $adminId = null) {
    global $pdo;
    
    try {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $query = "
            UPDATE student_details 
            SET 
                status = ?, 
                rejection_reason = ?, 
                validated_by = ?, 
                validated_at = NOW() 
            WHERE 
                user_id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status, $rejectionReason, $adminId, $studentId]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la validation du compte étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les détails d'un étudiant
 * 
 * @param int $studentId L'ID de l'étudiant
 * @return array|bool Les détails de l'étudiant ou false en cas d'erreur
 */
function getStudentDetails($studentId) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                u.id, 
                u.firstname, 
                u.lastname, 
                u.email, 
                u.phone, 
                u.created_at,
                sd.matricule, 
                sd.birthdate, 
                sd.cycle, 
                sd.niveau, 
                sd.specialite, 
                sd.classe,
                sd.status,
                sd.rejection_reason
            FROM 
                users u
            JOIN 
                student_details sd ON u.id = sd.user_id
            WHERE 
                u.id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$studentId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des détails de l\'étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si le compte étudiant est validé
 * 
 * @param int $studentId L'ID de l'étudiant
 * @return bool True si le compte est validé, false sinon
 */
function isStudentAccountApproved($studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT status FROM student_details WHERE user_id = ?");
        $stmt->execute([$studentId]);
        
        $status = $stmt->fetchColumn();
        
        return $status === 'approved';
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification du statut du compte étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le statut du compte étudiant
 * 
 * @param int $studentId L'ID de l'étudiant
 * @return string|bool Le statut du compte ou false en cas d'erreur
 */
function getStudentAccountStatus($studentId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT status, rejection_reason FROM student_details WHERE user_id = ?");
        $stmt->execute([$studentId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération du statut du compte étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les détails d'un enseignant par son ID utilisateur
 * 
 * @param int $userId L'ID de l'utilisateur
 * @return array|bool Les détails de l'enseignant ou false en cas d'erreur
 */
function getTeacherDetails($userId) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                td.*
            FROM 
                teacher_details td
            WHERE 
                td.user_id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des détails de l\'enseignant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les détails d'un étudiant par son ID utilisateur
 * 
 * @param int $userId L'ID de l'utilisateur
 * @return array|bool Les détails de l'étudiant ou false en cas d'erreur
 */
function getStudentDetailsByUserId($userId) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                sd.*
            FROM 
                student_details sd
            WHERE 
                sd.user_id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des détails de l\'étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les détails d'un administrateur par son ID utilisateur
 * 
 * @param int $userId L'ID de l'utilisateur
 * @return array|bool Les détails de l'administrateur ou false en cas d'erreur
 */
function getAdminDetails($userId) {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                ad.*
            FROM 
                admin_details ad
            WHERE 
                ad.user_id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des détails de l\'administrateur: ' . $e->getMessage());
        return false;
    }
}
?>
