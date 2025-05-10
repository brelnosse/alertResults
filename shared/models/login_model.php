<?php
// Inclure la connexion à la base de données avec un chemin absolu
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Vérifie les identifiants de l'utilisateur
 * 
 * @param string $email L'email de l'utilisateur
 * @param string $password Le mot de passe de l'utilisateur
 * @param string $portalType Le type de portail (admin, student, teacher)
 * @return array|bool Les données de l'utilisateur si les identifiants sont corrects, false sinon
 */
function verifyUserCredentials($email, $password, $portalType) {
    global $pdo;
    
    try {
        // Récupérer l'utilisateur par son email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Vérifier que le type d'utilisateur correspond au portail
            if ($user['user_type'] !== $portalType) {
                return false; // L'utilisateur tente de se connecter au mauvais portail
            }
            
            // Vérifier que l'utilisateur existe dans la table spécifique à son type
            if (!userExistsInTypeTable($user['id'], $user['user_type'])) {
                return false; // L'utilisateur n'existe pas dans la table spécifique
            }
            
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification des identifiants: ' . $e->getMessage());
        return false;
    }
}

// /**
//  * Vérifie si le compte étudiant est approuvé
//  * 
//  * @param int $userId L'ID de l'utilisateur
//  * @return bool True si le compte est approuvé, false sinon
//  */
// function isStudentAccountApproved($userId) {
//     global $pdo;
    
//     try {
//         $stmt = $pdo->prepare("SELECT status FROM student_details WHERE user_id = ?");
//         $stmt->execute([$userId]);
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
//         // Si le statut est "approved", le compte est approuvé
//         return ($result && $result['status'] === 'approved');
//     } catch (PDOException $e) {
//         error_log('Erreur lors de la vérification du statut du compte étudiant: ' . $e->getMessage());
//         return false;
//     }
// }

// /**
//  * Récupère les détails d'un étudiant
//  * 
//  * @param int $userId L'ID de l'utilisateur
//  * @return array|bool Les détails de l'étudiant ou false en cas d'erreur
//  */
// function getStudentDetails($userId) {
//     global $pdo;
    
//     try {
//         $stmt = $pdo->prepare("SELECT * FROM student_details WHERE user_id = ?");
//         $stmt->execute([$userId]);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         error_log('Erreur lors de la récupération des détails de l\'étudiant: ' . $e->getMessage());
//         return false;
//     }
// }

/**
 * Vérifie si l'utilisateur existe dans la table spécifique à son type
 * 
 * @param int $userId L'ID de l'utilisateur
 * @param string $userType Le type d'utilisateur (admin, student, teacher)
 * @return bool True si l'utilisateur existe dans la table spécifique, false sinon
 */
function userExistsInTypeTable($userId, $userType) {
    global $pdo;
    
    try {
        $table = '';
        
        // Déterminer la table à utiliser selon le type d'utilisateur
        switch ($userType) {
            case 'student':
                $table = 'student_details';
                break;
            case 'teacher':
                $table = 'teacher_details';
                break;
            case 'admin':
                $table = 'admin_details';
                break;
            default:
                return false;
        }
        
        // Vérifier si l'utilisateur existe dans la table spécifique
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification de l\'existence de l\'utilisateur: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les détails spécifiques de l'utilisateur selon son type
 * 
 * @param int $userId L'ID de l'utilisateur
 * @param string $userType Le type d'utilisateur (student, teacher, admin)
 * @return array|bool Les détails de l'utilisateur ou false en cas d'erreur
 */
function getUserDetails($userId, $userType) {
    global $pdo;
    
    try {
        $table = '';
        
        // Déterminer la table à utiliser selon le type d'utilisateur
        switch ($userType) {
            case 'student':
                $table = 'student_details';
                break;
            case 'teacher':
                $table = 'teacher_details';
                break;
            case 'admin':
                $table = 'admin_details';
                break;
            default:
                return false;
        }
        
        // Récupérer les détails de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des détails de l\'utilisateur: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour la date de dernière connexion d'un utilisateur
 * 
 * @param int $userId L'ID de l'utilisateur
 * @return bool True si la mise à jour a réussi, false sinon
 */
function updateLastLogin($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET last_login = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la mise à jour de la dernière connexion: ' . $e->getMessage());
        return false;
    }
}

?>
