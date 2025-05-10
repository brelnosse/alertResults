<?php
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../models/password_reset_model.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../controllers/user_common_controller.php';

// Démarrer la session
session_start();

// Récupérer l'action demandée
$action = $_GET['action'] ?? '';

// Exécuter l'action appropriée
switch ($action) {
    case 'forgot':
        handleForgotPassword();
        break;
    case 'reset':
        handleResetPassword();
        break;
    default:
        // Action non reconnue, rediriger vers la page d'accueil
        header('Location: ../../index.php');
        exit;
}

/**
 * Gère la demande de réinitialisation de mot de passe
 */
function handleForgotPassword() {
    // Récupérer et nettoyer les données du formulaire
    $email = sanitizeInput($_POST['email'] ?? '');
    $portalType = sanitizeInput($_POST['portal_type'] ?? '');
    
    // Tableau pour stocker les erreurs
    $errors = [];
    
    // Validation des données
    if (empty($email)) {
        $errors['email'] = 'L\'adresse email est obligatoire';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'L\'adresse email est invalide';
    }
    
    // Si des erreurs sont présentes, rediriger avec un message d'erreur
    if (!empty($errors)) {
        $_SESSION['error_message'] = 'Veuillez entrer une adresse email valide';
        redirectToForgotPasswordPage($portalType);
    }
    
    // Récupérer l'utilisateur par son email
    $user = getUserByEmail($email);
    
    // Si l'utilisateur n'existe pas, ne pas révéler cette information pour des raisons de sécurité
    if (!$user) {
        // Simuler un délai pour éviter les attaques par timing
        sleep(1);
        
        $_SESSION['success_message'] = 'Si votre adresse email est enregistrée, vous recevrez un lien de réinitialisation';
        redirectToForgotPasswordPage($portalType);
    }
    
    // Vérifier que l'utilisateur correspond au type de portail
    if (!empty($portalType) && $user['user_type'] !== $portalType) {
        $_SESSION['error_message'] = 'Cette adresse email n\'est pas associée à ce type de compte';
        redirectToForgotPasswordPage($portalType);
    }
    
    // Créer un token de réinitialisation
    $token = createPasswordResetToken($user['id'], $user['email'], $user['user_type']);
    
    if (!$token) {
        $_SESSION['error_message'] = 'Une erreur est survenue. Veuillez réessayer ultérieurement';
        redirectToForgotPasswordPage($portalType);
    }
    
    // Envoyer l'email de réinitialisation avec PHPMailer
    $emailSent = Mailer::sendPasswordResetEmail(
        $user['email'],
        $token,
        $user['user_type'],
        $user['firstname'],
        $user['lastname']
    );
    
    if (!$emailSent) {
        $_SESSION['error_message'] = 'Une erreur est survenue lors de l\'envoi de l\'email. Veuillez réessayer ultérieurement';
        redirectToForgotPasswordPage($portalType);
    }
    
    // Rediriger avec un message de succès
    $_SESSION['success_message'] = 'Un email de réinitialisation a été envoyé à votre adresse email';
    redirectToForgotPasswordPage($portalType);
}

/**
 * Gère la réinitialisation du mot de passe
 */
function handleResetPassword() {
    // Récupérer et nettoyer les données du formulaire
    $token = sanitizeInput($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Tableau pour stocker les erreurs
    $errors = [];
    
    // Validation des données
    if (empty($token)) {
        $errors['token'] = 'Le token est invalide ou a expiré';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est obligatoire';
    } elseif (!validatePassword($password)) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
    }
    
    if (empty($confirmPassword)) {
        $errors['confirm_password'] = 'La confirmation du mot de passe est obligatoire';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Si des erreurs sont présentes, rediriger avec un message d'erreur
    if (!empty($errors)) {
        $_SESSION['error_message'] = 'Veuillez corriger les erreurs dans le formulaire';
        redirectToResetPasswordPage($token);
    }
    
    // Valider le token
    $userData = validatePasswordResetToken($token);
    
    if (!$userData) {
        $_SESSION['error_message'] = 'Le lien de réinitialisation est invalide ou a expiré';
        redirectToForgotPasswordPage(determinePortalType());
    }
    
    // Mettre à jour le mot de passe
    $passwordUpdated = updateUserPassword($userData['user_id'], $password);
    
    if (!$passwordUpdated) {
        $_SESSION['error_message'] = 'Une erreur est survenue lors de la mise à jour du mot de passe';
        redirectToResetPasswordPage($token);
    }
    
    // Envoyer un email de confirmation
    Mailer::sendPasswordChangedEmail(
        $userData['email'],
        $userData['firstname'],
        $userData['lastname']
    );
    
    // Supprimer le token
    deletePasswordResetToken($token);
    
    // Rediriger vers la page de connexion avec un message de succès
    $_SESSION['success_message'] = $userData['email'].' ---Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe';
    redirectToLoginPage($userData['user_type']);
}

/**
 * Redirige vers la page de demande de réinitialisation de mot de passe
 * 
 * @param string $portalType Type de portail (admin, student, teacher)
 */
function redirectToForgotPasswordPage($portalType) {
    $redirectUrl = '';
    
    switch ($portalType) {
        case 'admin':
            $redirectUrl = '../../ad/views/forgot_password_view.php';
            break;
        case 'student':
            $redirectUrl = '../../student/views/forgot_password_view.php';
            break;
        case 'teacher':
            $redirectUrl = '../../teacher/views/forgot_password_view.php';
            break;
        default:
            $redirectUrl = '../../shared/views/forgot_password_view.php';
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Redirige vers la page de réinitialisation de mot de passe
 * 
 * @param string $token Token de réinitialisation
 */
function redirectToResetPasswordPage($token) {
    $portalType = determinePortalType();
    $redirectUrl = '';
    
    switch ($portalType) {
        case 'admin':
            $redirectUrl = '../../ad/views/reset_password_view.php?token=' . $token;
            break;
        case 'student':
            $redirectUrl = '../../student/views/reset_password_view.php?token=' . $token;
            break;
        case 'teacher':
            $redirectUrl = '../../teacher/views/reset_password_view.php?token=' . $token;
            break;
        default:
            $redirectUrl = '../../shared/views/reset_password_view.php?token=' . $token;
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Redirige vers la page de connexion
 * 
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 */
function redirectToLoginPage($userType) {
    $redirectUrl = '';
    
    switch ($userType) {
        case 'admin':
            $redirectUrl = '../../ad/index.php';
            break;
        case 'student':
            $redirectUrl = '../../student/index.php';
            break;
        case 'teacher':
            $redirectUrl = '../../teacher/index.php';
            break;
        default:
            $redirectUrl = '../../index.php';
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Détermine le type de portail en fonction du chemin d'accès
 * 
 * @return string Type de portail (admin, student, teacher)
 */
function determinePortalType() {
    $currentPath = $_SERVER['PHP_SELF'];
    
    if (strpos($currentPath, '/ad/') !== false) {
        return 'admin';
    } elseif (strpos($currentPath, '/student/') !== false) {
        return 'student';
    } elseif (strpos($currentPath, '/teacher/') !== false) {
        return 'teacher';
    }
    
    return '';
}

