<?php
/**
 * Script pour nettoyer les tokens expirés
 * À exécuter via une tâche cron quotidienne
 */

// Inclure les modèles nécessaires
require_once __DIR__ . '/../shared/models/token_model.php';
require_once __DIR__ . '/../shared/models/password_reset_model.php';

// Nettoyer les tokens de connexion expirés
$resultRememberTokens = cleanExpiredTokens();

// Nettoyer les tokens de réinitialisation de mot de passe expirés
$resultPasswordResetTokens = cleanExpiredPasswordResetTokens();

// Journaliser le résultat
if ($resultRememberTokens) {
    echo "Nettoyage des tokens de connexion expirés effectué avec succès.\n";
} else {
    echo "Erreur lors du nettoyage des tokens de connexion expirés.\n";
}

if ($resultPasswordResetTokens) {
    echo "Nettoyage des tokens de réinitialisation de mot de passe expirés effectué avec succès.\n";
} else {
    echo "Erreur lors du nettoyage des tokens de réinitialisation de mot de passe expirés.\n";
}
?>
