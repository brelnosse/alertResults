<?php
/**
 * Fichier d'initialisation pour les enseignants
 * Vérifie si l'utilisateur est connecté en tant qu'enseignant
 */

// Inclure le fichier d'initialisation commun
require_once __DIR__ . '/../shared/init.php';

// Inclure le modèle de token
require_once __DIR__ . '/../shared/models/token_model.php';
require_once __DIR__ . '/../shared/models/login_model.php';

// Vérifier si un token de connexion est présent dans les cookies
if (!isUserLoggedIn() && isset($_COOKIE['remember_token_teacher'])) {
    $token = $_COOKIE['remember_token_teacher'];
    
    try {
        $user = validateRememberToken($token, 'teacher');
        
        if ($user) {
            // Récupérer les détails spécifiques de l'utilisateur
            $userDetails = getUserDetails($user['id'], 'teacher');
            // Créer la session utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = 'teacher';
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['user_logged_in'] = true;
        } else {
            // Token invalide, le supprimer
            removeRememberCookie('teacher');
        }
    } catch (Exception $e) {
        // Gérer l'erreur
        error_log("Erreur lors de la validation du token: " . $e->getMessage());
        // Supprimer le cookie en cas d'erreur
        removeRememberCookie('teacher');
    }
}

/**
 * Vérifie si l'utilisateur est un enseignant
 * @return bool True si l'utilisateur est un enseignant, false sinon
 */
function isTeacher() {
    return isUserType('teacher');
}

/**
 * Vérifie si l'utilisateur est un enseignant et le redirige vers la page de connexion si ce n'est pas le cas
 * @param string $loginUrl URL de la page de connexion
 * @return void
 */
function requireTeacher($loginUrl = '') {
    requireUserType('teacher', $loginUrl);
}

/**
 * Vérifie si l'utilisateur est connecté en tant qu'enseignant et le redirige vers le tableau de bord si c'est le cas
 * @return void
 */
function redirectIfTeacher() {
    if (isUserLoggedIn() && isTeacher()) {
        redirectTo(getBasePath() . '/teacher/views/dashboard.php');
    }
}
?>
