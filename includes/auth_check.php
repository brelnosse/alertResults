<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion avec un message d'erreur
    header('Location: ../login.html?message=Veuillez vous connecter pour accéder à cette page&type=error');
    exit;
}

// Vérifier si l'utilisateur a le bon type pour accéder à cette page
function checkUserType($allowedTypes) {
    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowedTypes)) {
        // Rediriger vers la page de connexion avec un message d'erreur
        header('Location: ../login.html?message=Vous n\'avez pas les droits pour accéder à cette page&type=error');
        exit;
    }
}
?>
