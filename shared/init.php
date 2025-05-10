<?php
/**
 * Fichier d'initialisation commun
 * Contient les fonctions communes à tous les types d'utilisateurs
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Vérifie le type d'utilisateur
 * @param string $type Type d'utilisateur à vérifier
 * @return bool True si l'utilisateur est du type spécifié, false sinon
 */
function isUserType($type) {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
}

/**
 * Redirige l'utilisateur vers une URL spécifiée
 * @param string $url URL de redirection
 * @return void
 */
function redirectTo($url) {
    header("Location: $url");
    exit();
}

/**
 * Redirige l'utilisateur vers le tableau de bord approprié en fonction de son type
 * @return void
 */
function redirectToDashboard() {
    if (!isUserLoggedIn()) {
        return;
    }

    if (isUserType('admin')) {
        redirectTo(getBasePath() . '/ad/views/dashboard.php');
    } elseif (isUserType('student')) {
        redirectTo(getBasePath() . '/student/views/dashboard.php');
    } elseif (isUserType('teacher')) {
        redirectTo(getBasePath() . '/teacher/views/dashboard.php');
    }
}

/**
 * Obtient le chemin de base de l'application
 * @return string Chemin de base
 */
function getBasePath() {
    // Déterminer le chemin de base en fonction de l'environnement
    $basePath = '';
    
    // Si nous sommes en développement local
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === 'resultsAlert.test') {
        $basePath = '';
    } else {
        // En production, ajuster si nécessaire
        $basePath = '';
    }
    
    return $basePath;
}

/**
 * Vérifie si l'utilisateur est connecté et le redirige vers le tableau de bord si c'est le cas
 * @return void
 */
function redirectIfLoggedIn() {
    if (isUserLoggedIn()) {
        redirectToDashboard();
    }
}

/**
 * Vérifie si l'utilisateur n'est pas connecté et le redirige vers la page de connexion si c'est le cas
 * @param string $loginUrl URL de la page de connexion
 * @return void
 */
function requireLogin($loginUrl = '') {
    if (!isUserLoggedIn()) {
        if (empty($loginUrl)) {
            // Déterminer l'URL de connexion en fonction du contexte
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            
            if (strpos($scriptPath, '/ad/') !== false) {
                $loginUrl = getBasePath() . '/ad/index.php';
            } elseif (strpos($scriptPath, '/student/') !== false) {
                $loginUrl = getBasePath() . '/student/index.php';
            } elseif (strpos($scriptPath, '/teacher/') !== false) {
                $loginUrl = getBasePath() . '/teacher/index.php';
            } else {
                $loginUrl = getBasePath() . '/index.php';
            }
        }
        
        redirectTo($loginUrl);
    }
}

/**
 * Vérifie si l'utilisateur est du type spécifié et le redirige vers la page de connexion si ce n'est pas le cas
 * @param string $type Type d'utilisateur requis
 * @param string $loginUrl URL de la page de connexion
 * @return void
 */
function requireUserType($type, $loginUrl = '') {
    requireLogin($loginUrl);
    
    if (!isUserType($type)) {
        if (empty($loginUrl)) {
            // Déterminer l'URL de connexion en fonction du type d'utilisateur
            if ($type === 'admin') {
                $loginUrl = getBasePath() . '/ad/index.php';
            } elseif ($type === 'student') {
                $loginUrl = getBasePath() . '/student/index.php';
            } elseif ($type === 'teacher') {
                $loginUrl = getBasePath() . '/teacher/index.php';
            } else {
                $loginUrl = getBasePath() . '/index.php';
            }
        }
        
        // Stocker un message d'erreur
        $_SESSION['error_message'] = "Vous n'avez pas les autorisations nécessaires pour accéder à cette page.";
        
        redirectTo($loginUrl);
    }
}

/**
 * Nettoie et valide les données d'entrée
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Génère une réponse JSON
 * @param bool $success Indicateur de succès
 * @param string $message Message à afficher
 * @param array $data Données supplémentaires
 * @return void
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Redirige avec un message
 * @param string $url URL de redirection
 * @param string $message Message à afficher
 * @param string $type Type de message (success, error, warning, info)
 * @return void
 */
function redirectWithMessage($url, $message, $type = 'success') {
    session_start();
    
    if ($type === 'success') {
        $_SESSION['success_message'] = $message;
    } else {
        $_SESSION['error_message'] = $message;
    }
    
    redirectTo($url);
}

/**
 * Détermine le type de portail en fonction du chemin d'accès
 * @return string Type de portail (admin, student, teacher)
 */
function getPortalType() {
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    
    if (strpos($scriptPath, '/ad/') !== false) {
        return 'admin';
    } elseif (strpos($scriptPath, '/student/') !== false) {
        return 'student';
    } elseif (strpos($scriptPath, '/teacher/') !== false) {
        return 'teacher';
    }
    
    return '';
}
?>
