<?php
/**
 * Fichier d'initialisation pour les étudiants
 * Vérifie si l'utilisateur est connecté en tant qu'étudiant
 */

// Inclure le fichier d'initialisation commun
require_once __DIR__ . '/../shared/init.php';

// Inclure le modèle de token
require_once __DIR__ . '/../shared/models/token_model.php';
require_once __DIR__ . '/../shared/models/login_model.php';

// Vérifier si un token de connexion est présent dans les cookies
if (!isUserLoggedIn() && isset($_COOKIE['remember_token_student'])) {
    $token = $_COOKIE['remember_token_student'];
    
    try {
        $user = validateRememberToken($token, 'student');
        
        if ($user) {
            // Récupérer les détails spécifiques de l'utilisateur
            $userDetails = getUserDetails($user['id'], 'student');
            
            // Créer la session utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['user_logged_in'] = true;
            
            if ($userDetails) {
                $_SESSION['student_matricule'] = $userDetails['matricule'];
                $_SESSION['student_cycle'] = $userDetails['cycle'];
                $_SESSION['student_niveau'] = $userDetails['niveau'];
                $_SESSION['student_specialite'] = $userDetails['specialite'];
                $_SESSION['student_classe'] = $userDetails['classe'];
            }
        } else {
            // Token invalide, le supprimer
            removeRememberCookie('student');
        }
    } catch (Exception $e) {
        // Gérer l'erreur
        error_log("Erreur lors de la validation du token: " . $e->getMessage());
        // Supprimer le cookie en cas d'erreur
        removeRememberCookie('student');
    }
}

/**
 * Vérifie si l'utilisateur est un étudiant
 * @return bool True si l'utilisateur est un étudiant, false sinon
 */
function isStudent() {
    return isUserType('student');
}

/**
 * Vérifie si l'utilisateur est un étudiant et le redirige vers la page de connexion si ce n'est pas le cas
 * @param string $loginUrl URL de la page de connexion
 * @return void
 */
function requireStudent($loginUrl = '') {
    requireUserType('student', $loginUrl);
}

/**
 * Vérifie si l'utilisateur est connecté en tant qu'étudiant et le redirige vers le tableau de bord si c'est le cas
 * @return void
 */
function redirectIfStudent() {
    if (isUserLoggedIn() && isStudent()) {
        redirectTo(getBasePath() . '/student/views/dashboard.php');
    }
}
?>
