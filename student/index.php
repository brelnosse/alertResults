<?php
// Inclure le fichier d'initialisation
require_once 'init_std.php';

// Vérifier si l'utilisateur est déjà connecté et le rediriger vers le tableau de bord si c'est le cas
redirectIfStudent();

// Si l'utilisateur n'est pas connecté, afficher la page de connexion
require_once '../shared/views/login_view.php';
?>