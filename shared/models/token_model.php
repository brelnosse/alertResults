<?php
// Inclure la connexion à la base de données
require_once __DIR__ . '/../config/db_connect.php';
/**
 * Génère un token de connexion pour un utilisateur
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return string Token généré
 */
function generateRememberToken($userId, $userType) {
    // Générer un token aléatoire
    $token = bin2hex(random_bytes(32));
    
    // Générer une date d'expiration (30 jours)
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Stocker le token dans la base de données
    storeRememberToken($userId, $token, $userType, $expiry);
    
    return $token;
}

/**
 * Stocke un token de connexion dans la base de données
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $token Token à stocker
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @param string $expiry Date d'expiration du token
 * @return bool True si le token a été stocké avec succès, false sinon
 */
function storeRememberToken($userId, $token, $userType, $expiry) {
    global $pdo;
    
    try {
        // Supprimer les anciens tokens de l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND user_type = ?");
        $stmt->execute([$userId, $userType]);
        
        // Insérer le nouveau token
        $stmt = $pdo->prepare("
            INSERT INTO remember_tokens (user_id, token, user_type, expiry, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$userId, $token, $userType, $expiry]);
    } catch (PDOException $e) {
        error_log('Erreur lors du stockage du token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Valide un token de connexion
 * 
 * @param string $token Token à valider
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return array|bool Données de l'utilisateur si le token est valide, false sinon
 */
function validateRememberToken($token, $userType) {
    global $pdo;
    
    try {
        // Récupérer le token
        $stmt = $pdo->prepare("
            SELECT rt.*, u.* 
            FROM remember_tokens rt
            JOIN users u ON rt.user_id = u.id
            WHERE rt.token = ? 
            AND rt.user_type = ?
            AND rt.expiry > NOW()
        ");
        
        $stmt->execute([$token, $userType]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Régénérer un nouveau token pour plus de sécurité
            $newToken = regenerateRememberToken($result['user_id'], $userType);
            
            // Mettre à jour le cookie avec le nouveau token
            setRememberCookie($newToken, $userType);
            
            return [
                'id' => $result['user_id'],
                'email' => $result['email'],
                'firstname' => $result['firstname'],
                'lastname' => $result['lastname'],
                'user_type' => $result['user_type']
            ];
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la validation du token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Régénère un token de connexion
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return string Nouveau token
 */
function regenerateRememberToken($userId, $userType) {
    return generateRememberToken($userId, $userType);
}
/**
 * Vérifie si un token est valide et récupère l'utilisateur associé
 * 
 * @param string $token Le token à vérifier
 * @return array|bool Les données de l'utilisateur ou false si le token est invalide
 */
function getUserByRememberToken($token) {
    global $pdo;
    
    try {
        // Récupérer tous les tokens non expirés
        $stmt = $pdo->prepare("
            SELECT ut.*, u.* 
            FROM user_tokens ut
            JOIN users u ON ut.user_id = u.id
            WHERE ut.expires_at > NOW()
        ");
        $stmt->execute();
        
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Vérifier chaque token avec password_verify
        foreach ($tokens as $tokenData) {
            if (password_verify($token, $tokenData['token'])) {
                // Token valide, retourner les données de l'utilisateur
                return [
                    'id' => $tokenData['user_id'],
                    'firstname' => $tokenData['firstname'],
                    'lastname' => $tokenData['lastname'],
                    'email' => $tokenData['email'],
                    'user_type' => $tokenData['user_type']
                ];
            }
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification du token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Supprime un token spécifique
 * 
 * @param string $token Le token à supprimer
 * @return bool True si la suppression a réussi, false sinon
 */
function deleteRememberToken($token) {
    global $pdo;
    
    try {
        // Récupérer tous les tokens
        $stmt = $pdo->prepare("SELECT id, token FROM user_tokens");
        $stmt->execute();
        
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Trouver le token correspondant
        foreach ($tokens as $tokenData) {
            if (password_verify($token, $tokenData['token'])) {
                // Supprimer le token
                $deleteStmt = $pdo->prepare("DELETE FROM user_tokens WHERE id = ?");
                $deleteStmt->execute([$tokenData['id']]);
                
                return true;
            }
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de la suppression du token: ' . $e->getMessage());
        return false;
    }
}
/**
 * Définit un cookie de connexion
 * 
 * @param string $token Token à stocker dans le cookie
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return void
 */
function setRememberCookie($token, $userType) {
    $cookieName = 'remember_token_' . $userType;
    setcookie($cookieName, $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
}

/**
 * Supprime un cookie de connexion
 * 
 * @param string $userType Type d'utilisateur (admin, student, teacher)
 * @return void
 */
function removeRememberCookie($userType) {
    $cookieName = 'remember_token_' . $userType;
    setcookie($cookieName, '', time() - 3600, '/', '', false, true);
}
/**
 * Supprime tous les tokens expirés
 * 
 * @return int Le nombre de tokens supprimés
 */
function cleanExpiredTokens() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Erreur lors du nettoyage des tokens expirés: ' . $e->getMessage());
        return 0;
    }
}
?>
