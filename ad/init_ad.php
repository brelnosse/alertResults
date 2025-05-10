<?php
/**
 * Fichier d'initialisation pour les administrateurs
 * Vérifie si l'utilisateur est connecté en tant qu'administrateur
 */

// Inclure le fichier d'initialisation commun
require_once __DIR__ . '/../shared/init.php';

// Inclure le modèle de token
require_once __DIR__ . '/../shared/models/token_model.php';
require_once __DIR__ . '/../shared/models/login_model.php';

// Vérifier si un token de connexion est présent dans les cookies
if (!isUserLoggedIn() && isset($_COOKIE['remember_token_admin'])) {
    $token = $_COOKIE['remember_token_admin'];
    
    try {
        $user = validateRememberToken($token, 'admin');
        
        if ($user) {
            // Récupérer les détails spécifiques de l'utilisateur
            $userDetails = getUserDetails($user['id'], 'admin');
            
            // Créer la session utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['user_logged_in'] = true;
            
            if ($userDetails) {
                $_SESSION['admin_role'] = $userDetails['role'];
                $_SESSION['admin_department'] = $userDetails['department'];
            }
        } else {
            // Token invalide, le supprimer
            removeRememberCookie('admin');
        }
    } catch (Exception $e) {
        // Gérer l'erreur
        error_log("Erreur lors de la validation du token: " . $e->getMessage());
        // Supprimer le cookie en cas d'erreur
        removeRememberCookie('admin');
    }
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * @return bool True si l'utilisateur est un administrateur, false sinon
 */
function isAdmin() {
    return isUserType('admin');
}

/**
 * Vérifie si l'utilisateur est un administrateur et le redirige vers la page de connexion si ce n'est pas le cas
 * @param string $loginUrl URL de la page de connexion
 * @return void
 */
function requireAdmin($loginUrl = '') {
    requireUserType('admin', $loginUrl);
}

/**
 * Vérifie si l'utilisateur est connecté en tant qu'administrateur et le redirige vers le tableau de bord si c'est le cas
 * @return void
 */
function redirectIfAdmin() {
    if (isUserLoggedIn() && isAdmin()) {
        redirectTo(getBasePath() . '/ad/views/dashboard.php');
    }
}

// Fonction pour vérifier si l'utilisateur a un rôle spécifique
function hasRole($role) {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === $role;
}

// Fonction pour vérifier si l'utilisateur a au moins un des rôles spécifiés
function hasAnyRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], $roles);
}

// Fonction pour restreindre l'accès aux utilisateurs ayant un rôle spécifique
function requireRole($roles, $redirectUrl = '') {
    requireAdmin();
    
    if (!hasAnyRole($roles)) {
        if (empty($redirectUrl)) {
            $redirectUrl = getBasePath() . '/ad/views/dashboard.php';
        }
        
        // Stocker un message d'erreur
        $_SESSION['error_message'] = "Vous n'avez pas les autorisations nécessaires pour accéder à cette page.";
        
        redirectTo($redirectUrl);
    }
}
?>
