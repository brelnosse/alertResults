<?php
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/token_model.php';
require_once __DIR__ . '/../models/user_model.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * 
 * @param string|array $roles Le(s) rôle(s) à vérifier
 * @return bool True si l'utilisateur a le rôle spécifié, false sinon
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userType = $_SESSION['user_type'];
    
    if (is_array($roles)) {
        return in_array($userType, $roles);
    } else {
        return $userType === $roles;
    }
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * 
 * @return bool True si l'utilisateur est un administrateur, false sinon
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Vérifie si l'utilisateur est un étudiant
 * 
 * @return bool True si l'utilisateur est un étudiant, false sinon
 */
function isStudent() {
    return hasRole('student');
}

/**
 * Vérifie si l'utilisateur est un enseignant
 * 
 * @return bool True si l'utilisateur est un enseignant, false sinon
 */
function isTeacher() {
    return hasRole('teacher');
}

/**
 * Vérifie si l'administrateur est un chef de département
 * 
 * @return bool True si l'administrateur est un chef de département, false sinon
 */
function isDepartmentHead() {
    if (!isAdmin()) {
        return false;
    }
    
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'chef_departement';
}

/**
 * Vérifie si l'administrateur est un directeur
 * 
 * @return bool True si l'administrateur est un directeur, false sinon
 */
function isDirector() {
    if (!isAdmin()) {
        return false;
    }
    
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'directeur';
}

/**
 * Redirige l'utilisateur s'il n'est pas connecté
 * 
 * @param string $redirectUrl L'URL de redirection
 */
function requireLogin($redirectUrl = '/login.html') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Redirige l'utilisateur s'il n'a pas le rôle spécifié
 * 
 * @param string|array $roles Le(s) rôle(s) autorisé(s)
 * @param string $redirectUrl L'URL de redirection
 */
function requireRole($roles, $redirectUrl = '/login.html') {
    requireLogin($redirectUrl);
    
    if (!hasRole($roles)) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Vérifie le cookie "Se souvenir de moi" et connecte l'utilisateur si valide
 */
function checkRememberMeCookie() {
    if (isLoggedIn() || !isset($_COOKIE['remember_token'])) {
        return;
    }
    
    $token = $_COOKIE['remember_token'];
    $user = getUserByRememberToken($token);
    
    if ($user) {
        // Connecter l'utilisateur
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['user_email'] = $user['email'];
        
        // Récupérer les détails spécifiques selon le type d'utilisateur
        if ($user['user_type'] === 'admin') {
            $adminDetails = getAdminDetails($user['id']);
            if ($adminDetails) {
                $_SESSION['admin_role'] = $adminDetails['role'];
                $_SESSION['admin_department'] = $adminDetails['department'];
            }
        } elseif ($user['user_type'] === 'student') {
            $studentDetails = getStudentDetailsByUserId($user['id']);
            if ($studentDetails) {
                $_SESSION['student_matricule'] = $studentDetails['matricule'];
                $_SESSION['student_cycle'] = $studentDetails['cycle'];
                $_SESSION['student_niveau'] = $studentDetails['niveau'];
                $_SESSION['student_specialite'] = $studentDetails['specialite'];
                $_SESSION['student_classe'] = $studentDetails['classe'];
            }
        } elseif ($user['user_type'] === 'teacher') {
            $teacherDetails = getTeacherDetails($user['id']);
            if ($teacherDetails) {
                // Stocker les détails de l'enseignant si nécessaire
            }
        }
        
        // Mettre à jour la date de dernière connexion
        updateLastLogin($user['id']);
        
        // Renouveler le token
        deleteRememberToken($token);
        $newToken = generateRememberToken($user['id']);
        setcookie('remember_token', $newToken, time() + 30 * 24 * 60 * 60, '/', '', false, true);
    }
}

// Vérifier le cookie "Se souvenir de moi" à chaque chargement de page
checkRememberMeCookie();
?>
