<?php
// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Crée un token de réinitialisation de mot de passe
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $email Email de l'utilisateur
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return string|bool Token généré ou false en cas d'erreur
 */
function createPasswordResetToken($userId, $email, $userType) {
    global $pdo;
    
    try {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        
        // Définir la date d'expiration (1 heure)
        $expiry = date('Y-m-d H:i:s', strtotime('+2 hour'));
        
        // Supprimer les anciens tokens pour cet utilisateur
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Insérer le nouveau token
        $stmt = $pdo->prepare("
            INSERT INTO password_reset_tokens (user_id, email, token, user_type, expiry, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$userId, $email, $token, $userType, $expiry]);
        
        if ($result) {
            return $token;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la création du token de réinitialisation: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si un token de réinitialisation est valide
 * 
 * @param string $token Token à vérifier
 * @return array|bool Données de l'utilisateur si le token est valide, false sinon
 */
function validatePasswordResetToken($token) {
    global $pdo;
    
    try {
        // Récupérer le token
        $stmt = $pdo->prepare("
            SELECT prt.*, u.* 
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? 
            AND prt.expiry > NOW()
        ");
        
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la validation du token de réinitialisation: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime un token de réinitialisation
 * 
 * @param string $token Token à supprimer
 * @return bool True si le token a été supprimé, false sinon
 */
function deletePasswordResetToken($token) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression du token de réinitialisation: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère un utilisateur par son email
 * 
 * @param string $email Email de l'utilisateur
 * @return array|bool Données de l'utilisateur ou false si non trouvé
 */
function getUserByEmail($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'utilisateur par email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour le mot de passe d'un utilisateur
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $password Nouveau mot de passe (non hashé)
 * @return bool True si le mot de passe a été mis à jour, false sinon
 */
function updateUserPassword($userId, $password) {
    global $pdo;
    
    try {
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Mettre à jour le mot de passe
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    } catch (PDOException $e) {
        error_log('Erreur lors de la mise à jour du mot de passe: ' . $e->getMessage());
        return false;
    }
}

/**
 * Nettoie les tokens de réinitialisation expirés
 * 
 * @return bool True si le nettoyage a réussi, false sinon
 */
function cleanExpiredPasswordResetTokens() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE expiry < NOW()");
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('Erreur lors du nettoyage des tokens expirés: ' . $e->getMessage());
        return false;
    }
}
?>
