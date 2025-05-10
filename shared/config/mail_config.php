<?php
/**
 * Configuration pour l'envoi d'emails
 */
return [
    // Configuration SMTP
    'smtp' => [
        'host' => 'smtp.gmail.com', // Remplacez par votre serveur SMTP
        'port' => 587, // Port SMTP (généralement 587 pour TLS, 465 pour SSL)
        'encryption' => 'tls', // 'tls' ou 'ssl'
        'username' => 'brelnosse2@gmail.com', // Votre adresse email
        'password' => 'uomo yvbi igkh umte', // Votre mot de passe
    ],
    
    // Configuration de l'expéditeur par défaut
    'from' => [
        'email' => 'brelnosse2@gmail.com',
        'name' => 'AlertResults',
    ],
    
    // Autres paramètres
    'debug' => 0, // Niveau de débogage (0 à 4)
    'charset' => 'UTF-8',
];
?>

