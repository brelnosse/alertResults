<?php
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../models/login_model.php';
require_once __DIR__ . '/../models/token_model.php';
require_once __DIR__ . '/../controllers/user_common_controller.php';

// Démarrer la session
session_start();

// Récupérer l'action demandée
$action = $_GET['action'] ?? '';

// Exécuter l'action appropriée
switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        // Action non reconnue, rediriger vers la page d'accueil
        header('Location: ../../index.php');
        exit;
}

/**
 * Gère la connexion d'un utilisateur
 */
function handleLogin() {
    // Récupérer et nettoyer les données du formulaire
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Déterminer le type de portail en fonction du chemin d'accès
    $portalType = determinePortalType();
    
    // Tableau pour stocker les erreurs
    $errors = [];
    
    // Validation des données
    if (empty($email)) {
        $errors['email'] = 'L\'adresse email est obligatoire';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'L\'adresse email est invalide';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est obligatoire';
    }
    
    // Si des erreurs sont présentes, renvoyer une réponse JSON avec les erreurs
    if (!empty($errors)) {
        jsonResponse(false, 'Veuillez corriger les erreurs dans le formulaire', ['errors' => $errors]);
    }
    
    // Vérifier les identifiants de l'utilisateur
    $user = verifyUserCredentials($email, $password, $portalType);
    
    if ($user) {
        // Mettre à jour la date de dernière connexion
        updateLastLogin($user['id']);
        
        // Récupérer les détails spécifiques de l'utilisateur
        $userDetails = getUserDetails($user['id'], $user['user_type']);
        
        // Créer la session utilisateur
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_firstname'] = $user['firstname'];
        $_SESSION['user_lastname'] = $user['lastname'];
        $_SESSION['user_logged_in'] = true;
        
        // Ajouter des informations spécifiques selon le type d'utilisateur
        if ($user['user_type'] === 'admin' && $userDetails) {
            $_SESSION['admin_role'] = $userDetails['role'];
            $_SESSION['admin_department'] = $userDetails['department'];
        } elseif ($user['user_type'] === 'student' && $userDetails) {
            $_SESSION['student_matricule'] = $userDetails['matricule'];
            $_SESSION['student_cycle'] = $userDetails['cycle'];
            $_SESSION['student_niveau'] = $userDetails['niveau'];
            $_SESSION['student_specialite'] = $userDetails['specialite'];
            $_SESSION['student_classe'] = $userDetails['classe'];
        }
        
        // Si l'option "Se souvenir de moi" est cochée, créer un cookie
        if ($remember) {
            // Générer un token unique
            $token = generateRememberToken($user['id'], $user['user_type']);
            
            // Stocker le token dans un cookie qui expire dans 30 jours
            setRememberCookie($token, $user['user_type']);
        }
        
        // Déterminer l'URL de redirection
        $redirectUrl = '';
        switch ($user['user_type']) {
            case 'student':
                $redirectUrl = '../../student/views/dashboard.php';
                break;
            case 'teacher':
                $redirectUrl = '../../teacher/views/dashboard.php';
                break;
            case 'admin':
                $redirectUrl = '../../ad/views/dashboard.php';
                break;
        }
        
        // Renvoyer une réponse JSON avec succès et l'URL de redirection
        jsonResponse(true, 'Connexion réussie', ['redirect' => $redirectUrl]);
    } else {
        // Si les identifiants sont incorrects ou si l'utilisateur tente de se connecter au mauvais portail
        jsonResponse(false, 'Identifiants incorrects ou vous n\'êtes pas autorisé à vous connecter à ce portail');
    }
}

/**
 * Gère la déconnexion d'un utilisateur
 */
function handleLogout() {
    // Récupérer le type d'utilisateur
    $userType = $_SESSION['user_type'] ?? '';
    
    // Supprimer le cookie de connexion automatique
    if (!empty($userType)) {
        // Supprimer le token de la base de données
        if (isset($_SESSION['user_id'])) {
            deleteRememberToken($_SESSION['user_id'], $userType);
        }
        
        // Supprimer le cookie
        removeRememberCookie($userType);
    }
    
    // Détruire toutes les variables de session
    $_SESSION = [];
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de connexion
    header('Location: ../../index.php');
    exit;
}

function getCallingDirectory() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        
        if (strpos($referer, '/student/') !== false) {
            return 'student';
        } elseif (strpos($referer, '/teacher/') !== false) {
            return 'teacher';
        } elseif (strpos($referer, '/ad/') !== false) {
            return 'admin';
        }
    }
    
    return 'unknown';
}

$source = getCallingDirectory();

/**
 * Détermine le type de portail en fonction du chemin d'accès
 * 
 * @return string Le type de portail (admin, student, teacher)
 */
function determinePortalType() {
    return getCallingDirectory();

}
?>
