<?php
require_once __DIR__ . '/../../shared/config/db_connect.php';
require_once __DIR__ . '/../../shared/utils/mailer.php';
require_once __DIR__ . '/../../shared/models/user_model.php';

/**
 * Récupère la liste des enseignants en attente de validation
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @return array La liste des enseignants en attente de validation
 */
function getPendingTeachers($department, $adminRole) {
    global $pdo;
    
    try {
        $sql = "SELECT DISTINCT 
    u.id, 
    u.firstname, 
    u.lastname, 
    u.email, 
    u.phone, 
    'pending' as status,
    td.id as teacher_detail_id
FROM users u 
JOIN teacher_details td ON u.id = td.user_id 
JOIN matieres_enseignees me ON u.id = me.id_enseignant
WHERE me.status = 'pending'";
        
        // Si l'administrateur est un chef de département, limiter aux enseignants de son département
        if ($adminRole === 'chef' && !empty($department)) {
            // Pour l'instant, nous n'avons pas de lien direct entre enseignants et départements
            // Cette partie pourrait être adaptée selon votre structure de données
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des enseignants en attente: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les matières enseignées par un enseignant
 * 
 * @param int $teacherId L'ID de l'enseignant
 * @return array La liste des matières enseignées
 */
function getTeacherSubjects($teacherId) {
    global $pdo;
    
    try {
        $sql = "SELECT me.*, m.libelle as matiere_libelle, m.code as matiere_code, m.credit, m.niveau as niveau_matiere
                FROM matieres_enseignees me
                LEFT JOIN matieres m ON me.matiere = m.libelle OR me.matiere = m.code
                WHERE me.id_enseignant = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teacherId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des matières enseignées: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère la liste des enseignants filtrés par statut et autres critères
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @param string $status Le statut des comptes (pending, approved, rejected, all)
 * @param string $searchTerm Le terme de recherche
 * @return array La liste des enseignants filtrés
 */
function getFilteredTeachers($department, $adminRole, $status = 'pending', $searchTerm = '') {
    global $pdo;
    
    try {
        $sql = "SELECT 
                    u.id, 
                    u.firstname, 
                    u.lastname, 
                    u.email, 
                    u.phone, 
                    MAX(me.status) as status, 
                    MAX(td.rejection_reason) as rejection_reason, 
                    td.id as teacher_detail_id, 
                    MAX(CONCAT(a.firstname, ' ', a.lastname)) as validator_name 
                FROM users u 
                JOIN teacher_details td ON u.id = td.user_id 
                JOIN matieres_enseignees me ON u.id = me.id_enseignant 
                LEFT JOIN users a ON td.validated_by = a.id 
                WHERE 1=1";
        
        $params = [];
        
        // Filtrer par statut
        if ($status !== 'all') {
            $sql .= " AND me.status = ?";
            $params[] = $status;
        }
        
        // Recherche par terme
        if (!empty($searchTerm)) {
            $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchParam = "%$searchTerm%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // // Grouper par enseignant pour éviter les doublons
        $sql .= " GROUP BY     u.id, u.firstname, u.lastname, u.email, u.phone, td.id";
        
        // // Trier par date de création (les plus récents d'abord)
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des enseignants filtrés: ' . $e->getMessage());
        error_log('Requête SQL: ' . $sql);
        return $e->getMessage();
    }
}

/**
 * Compte le nombre d'enseignants par statut
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @param string $status Le statut des comptes (pending, approved, rejected, all)
 * @param string $searchTerm Le terme de recherche (optionnel)
 * @return int Le nombre d'enseignants
 */
function countTeachersByStatus($department, $adminRole, $status = 'pending', $searchTerm = '') {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(DISTINCT u.id) 
                FROM users u 
                JOIN teacher_details td ON u.id = td.user_id 
                JOIN matieres_enseignees me ON u.id = me.id_enseignant
                WHERE 1=1";
        
        $params = [];
        
        // Filtrer par statut
        if ($status !== 'all') {
            $sql .= " AND me.status = ?";
            $params[] = $status;
        }
        
        // Recherche par terme
        if (!empty($searchTerm)) {
            $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchParam = "%$searchTerm%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erreur lors du comptage des enseignants: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Approuve le compte d'un enseignant
 * 
 * @param int $teacherId L'ID de l'enseignant
 * @param int $adminId L'ID de l'administrateur qui approuve le compte
 * @return bool True si l'approbation a réussi, false sinon
 */
function approveTeacherAccount($teacherId, $adminId) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Mise à jour du statut des matières enseignées
        $stmt = $pdo->prepare("UPDATE matieres_enseignees SET 
                              status = 'approved'
                              WHERE id_enseignant = ?");
        $result = $stmt->execute([$teacherId]);
        
        if ($result) {
            // Mise à jour des informations de validation dans teacher_details
            $stmt = $pdo->prepare("UPDATE teacher_details SET 
                                  validated_by = ?, 
                                  updated_at = NOW(), 
                                  validated_at = NOW(),
                                  rejection_reason = NULL 
                                  WHERE user_id = ?");
            $stmt->execute([$adminId, $teacherId]);
            
            // Récupérer les informations de l'enseignant pour l'email
            $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, u.email 
                                  FROM users u 
                                  WHERE u.id = ?");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($teacher) {
                // Envoyer un email de confirmation à l'enseignant
                $mailer = new Mailer();
                $mailer->sendTeacherAccountApprovalEmail($teacher['email'], $teacher['firstname'], $teacher['lastname']);
            }
            
            $pdo->commit();
            return true;
        }
        
        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erreur lors de l\'approbation du compte enseignant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rejette le compte d'un enseignant
 * 
 * @param int $teacherId L'ID de l'enseignant
 * @param int $adminId L'ID de l'administrateur qui rejette le compte
 * @param string $reason La raison du rejet
 * @return bool True si le rejet a réussi, false sinon
 */
function rejectTeacherAccount($teacherId, $adminId, $reason) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Mise à jour du statut des matières enseignées
        $stmt = $pdo->prepare("UPDATE matieres_enseignees SET 
                              status = 'rejected'
                              WHERE id_enseignant = ?");
        $result = $stmt->execute([$teacherId]);
        
        if ($result) {
            // Mise à jour des informations de validation dans teacher_details
            $stmt = $pdo->prepare("UPDATE teacher_details SET 
                                  validated_by = ?, 
                                  updated_at = NOW(), 
                                  validated_at = NOW(),
                                  rejection_reason = ? 
                                  WHERE user_id = ?");
            $stmt->execute([$adminId, $reason, $teacherId]);
            
            // Récupérer les informations de l'enseignant pour l'email
            $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, u.email 
                                  FROM users u 
                                  WHERE u.id = ?");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($teacher) {
                // Envoyer un email de rejet à l'enseignant
                $mailer = new Mailer();
                $mailer->sendTeacherAccountRejectionEmail($teacher['email'], $teacher['firstname'], $teacher['lastname'], $reason);
            }
            
            $pdo->commit();
            return true;
        }
        
        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erreur lors du rejet du compte enseignant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Approuve ou rejette une matière enseignée spécifique
 * 
 * @param int $matiereEnseigneeId L'ID de la matière enseignée
 * @param string $status Le nouveau statut (approved/rejected)
 * @param int $adminId L'ID de l'administrateur qui effectue l'action
 * @param string $reason La raison du rejet (optionnel)
 * @return bool True si l'opération a réussi, false sinon
 */
function updateMatiereEnseigneeStatus($matiereEnseigneeId, $status, $adminId, $reason = null) {
    global $pdo;
    
    try {
        // $pdo->beginTransaction();
        
        // Mise à jour du statut de la matière enseignée
        $stmt = $pdo->prepare("UPDATE matieres_enseignees SET 
                              status = ?,
                              rejection_reason = ?,
                              validated_by = ?,
                              validated_at = NOW()
                              WHERE id = ?");
        $result = $stmt->execute([$status, $reason, $adminId, $matiereEnseigneeId]);
        
        if ($result) {
            // Récupérer les informations de l'enseignant pour l'email
            $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, u.email, me.matiere 
                                  FROM matieres_enseignees me
                                  JOIN users u ON me.id_enseignant = u.id
                                  WHERE me.id = ?");
            $stmt->execute([$matiereEnseigneeId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                // Envoyer un email à l'enseignant
                $mailer = new Mailer();
                if ($status === 'approved') {
                    $mailer->sendTeacherAccountApprovalEmail($data['email'], $data['firstname'], $data['lastname'], $data['matiere']);
                } else {
                    $mailer->sendTeacherAccountRejectionEmail($data['email'], $data['firstname'], $data['lastname'], $data['matiere'], $reason);
                }
            }
            
            $pdo->commit();
            return true;
        }
        
        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erreur lors de la mise à jour du statut de la matière enseignée: ' . $e->getMessage());
        return false;
    }
}

/**
 * Définit un message flash pour la prochaine requête
 * 
 * @param string $type Le type de message (success, error, info, warning)
 * @param string $message Le message à afficher
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Formate une date pour l'affichage
 * 
 * @param string $date La date à formater
 * @return string La date formatée
 */
function formatDate($date) {
    if (empty($date)) return 'N/A';
    
    $datetime = new DateTime($date);
    return $datetime->format('d/m/Y H:i');
}
?>
