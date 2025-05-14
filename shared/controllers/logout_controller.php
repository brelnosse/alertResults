<?php
// Démarrer la session
session_start();

// Inclure le modèle de token
require_once __DIR__ . '/../models/token_model.php';

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
$path ="";
if($userType == 'admin'){
    $path = "../../ad/index.php";
}
$path = "../../$userType/index.php";
// Rediriger vers la page de connexion
header('Location: '.$path);
exit;
?>
