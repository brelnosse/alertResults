<?php
require_once __DIR__ . '/../../shared/config/db_connect.php';
require_once __DIR__ . '/../../shared/utils/mailer.php';
require_once __DIR__ . '/../../shared/models/user_model.php';

/**
 * Récupère la liste des étudiants en attente de validation
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @return array La liste des étudiants en attente de validation
 */
function getPendingStudents($department, $adminRole) {
    global $pdo;
    
    try {
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email, s.matricule, s.cycle, s.niveau, s.specialite, s.classe 
                FROM users u 
                JOIN student_details s ON u.id = s.user_id 
                WHERE s.status = 'pending'";
        
        // Si l'administrateur est un chef de département, limiter aux étudiants de son département
        if ($adminRole === 'chef_departement' && !empty($department)) {
            // Vérifier si la nouvelle structure est en place
            $stmt = $pdo->query("SHOW TABLES LIKE 'departement_specialites'");
            $useNewTables = ($stmt->rowCount() > 0);
            
            if ($useNewTables) {
                // Utiliser la nouvelle structure
                $sql .= " AND s.specialite IN (
                    SELECT sp.nom FROM specialites sp
                    JOIN departement_specialites ds ON sp.id = ds.specialite_id
                    JOIN departements d ON ds.departement_id = d.id
                    WHERE d.nom = ? OR d.code = ?
                )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$department, $department]);
            } else {
                // Utiliser l'ancienne structure
                $departmentSpecialties = getDepartmentSpecialtiesOld($department);
                if (!empty($departmentSpecialties)) {
                    $placeholders = implode(',', array_fill(0, count($departmentSpecialties), '?'));
                    $sql .= " AND s.specialite IN ($placeholders)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($departmentSpecialties);
                } else {
                    return [];
                }
            }
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des étudiants en attente: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère la liste des étudiants filtrés par statut et autres critères
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @param string $status Le statut des comptes (pending, approved, rejected, all)
 * @param string $specialite La spécialité à filtrer
 * @param string $cycle Le cycle à filtrer
 * @param string $niveau Le niveau à filtrer
 * @param string $searchTerm Le terme de recherche
 * @return array La liste des étudiants filtrés
 */
function getFilteredStudents($department, $adminRole, $status = 'pending', $specialite = '', $cycle = '', $niveau = '', $searchTerm = '') {
    global $pdo;
    
    try {
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email, s.matricule, s.cycle, s.niveau, s.specialite, s.classe, 
                s.status, s.rejection_reason, s.validated_at, 
                CONCAT(a.firstname, ' ', a.lastname) as validator_name
                FROM users u 
                JOIN student_details s ON u.id = s.user_id 
                LEFT JOIN users a ON s.validated_by = a.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtrer par statut
        if ($status !== 'all') {
            $sql .= " AND s.status = ?";
            $params[] = $status;
        }
        
        // Si l'administrateur est un chef de département, limiter aux étudiants de son département
        if ($adminRole === 'chef_departement' && !empty($department)) {
            // Vérifier si la nouvelle structure est en place
            $stmt = $pdo->query("SHOW TABLES LIKE 'departement_specialites'");
            $useNewTables = ($stmt->rowCount() > 0);
            
            if ($useNewTables) {
                // Utiliser la nouvelle structure
                $sql .= " AND s.specialite IN (
                    SELECT sp.nom FROM specialites sp
                    JOIN departement_specialites ds ON sp.id = ds.specialite_id
                    JOIN departements d ON ds.departement_id = d.id
                    WHERE d.nom = ? OR d.code = ?
                )";
                $params[] = $department;
                $params[] = $department;
            } else {
                // Utiliser l'ancienne structure
                $departmentSpecialties = getDepartmentSpecialtiesOld($department);
                if (!empty($departmentSpecialties)) {
                    $placeholders = implode(',', array_fill(0, count($departmentSpecialties), '?'));
                    $sql .= " AND s.specialite IN ($placeholders)";
                    $params = array_merge($params, $departmentSpecialties);
                } else {
                    return [];
                }
            }
        }
        
        // Filtrer par spécialité
        if (!empty($specialite)) {
            $sql .= " AND s.specialite = ?";
            $params[] = $specialite;
        }
        
        // Filtrer par cycle
        if (!empty($cycle)) {
            $sql .= " AND s.cycle = ?";
            $params[] = $cycle;
        }
        
        // Filtrer par niveau
        if (!empty($niveau)) {
            $sql .= " AND s.niveau = ?";
            $params[] = $niveau;
        }
        
        // Recherche par terme
        if (!empty($searchTerm)) {
            $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR s.matricule LIKE ?)";
            $searchParam = "%$searchTerm%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Trier par date de création (les plus récents d'abord)
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des étudiants filtrés: ' . $e->getMessage());
        return [];
    }
}

/**
 * Compte le nombre d'étudiants par statut
 * 
 * @param string $department Le département de l'administrateur
 * @param string $adminRole Le rôle de l'administrateur
 * @param string $status Le statut des comptes (pending, approved, rejected, all)
 * @param string $searchTerm Le terme de recherche (optionnel)
 * @return int Le nombre d'étudiants
 */
function countStudentsByStatus($department, $adminRole, $status = 'pending', $searchTerm = '') {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) FROM users u JOIN student_details s ON u.id = s.user_id WHERE 1=1";
        
        $params = [];
        
        // Filtrer par statut
        if ($status !== 'all') {
            $sql .= " AND s.status = ?";
            $params[] = $status;
        }
        
        // Si l'administrateur est un chef de département, limiter aux étudiants de son département
        if ($adminRole === 'chef_departement' && !empty($department)) {
            // Vérifier si la nouvelle structure est en place
            $stmt = $pdo->query("SHOW TABLES LIKE 'departement_specialites'");
            $useNewTables = ($stmt->rowCount() > 0);
            
            if ($useNewTables) {
                // Utiliser la nouvelle structure
                $sql .= " AND s.specialite IN (
                    SELECT sp.nom FROM specialites sp
                    JOIN departement_specialites ds ON sp.id = ds.specialite_id
                    JOIN departements d ON ds.departement_id = d.id
                    WHERE d.nom = ? OR d.code = ?
                )";
                $params[] = $department;
                $params[] = $department;
            } else {
                // Utiliser l'ancienne structure
                $departmentSpecialties = getDepartmentSpecialtiesOld($department);
                if (!empty($departmentSpecialties)) {
                    $placeholders = implode(',', array_fill(0, count($departmentSpecialties), '?'));
                    $sql .= " AND s.specialite IN ($placeholders)";
                    $params = array_merge($params, $departmentSpecialties);
                } else {
                    return 0;
                }
            }
        }
        
        // Recherche par terme
        if (!empty($searchTerm)) {
            $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR s.matricule LIKE ?)";
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
        error_log('Erreur lors du comptage des étudiants: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Approuve le compte d'un étudiant
 * 
 * @param int $studentId L'ID de l'étudiant
 * @param int $adminId L'ID de l'administrateur qui approuve le compte
 * @return bool True si l'approbation a réussi, false sinon
 */
function approveStudentAccount($studentId, $adminId) {
    global $pdo;
    
    try {
        // Mise à jour du statut de l'étudiant
        $stmt = $pdo->prepare("UPDATE student_details SET 
                               status = 'approved', 
                               validated_by = ?, 
                               validated_at = NOW(), 
                               rejection_reason = NULL 
                               WHERE user_id = ?");
        
        $result = $stmt->execute([$adminId, $studentId]);
        
        if ($result) {
            // Récupérer les informations de l'étudiant pour l'email
            $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, u.email 
                                  FROM users u 
                                  WHERE u.id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                // Envoyer un email de confirmation à l'étudiant
                $mailer = new Mailer();
                $mailer->sendAccountApprovalEmail($student['email'], $student['firstname'], $student['lastname']);
            }
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors de l\'approbation du compte étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rejette le compte d'un étudiant
 * 
 * @param int $studentId L'ID de l'étudiant
 * @param int $adminId L'ID de l'administrateur qui rejette le compte
 * @param string $reason La raison du rejet
 * @return bool True si le rejet a réussi, false sinon
 */
function rejectStudentAccount($studentId, $adminId, $reason) {
    global $pdo;
    
    try {
        // Mise à jour du statut de l'étudiant
        $stmt = $pdo->prepare("UPDATE student_details SET 
                               status = 'rejected', 
                               validated_by = ?, 
                               validated_at = NOW(), 
                               rejection_reason = ? 
                               WHERE user_id = ?");
        
        $result = $stmt->execute([$adminId, $reason, $studentId]);
        
        if ($result) {
            // Récupérer les informations de l'étudiant pour l'email
            $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, u.email 
                                  FROM users u 
                                  WHERE u.id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                // Envoyer un email de rejet à l'étudiant
                $mailer = new Mailer();
                $mailer->sendAccountRejectionEmail($student['email'], $student['firstname'], $student['lastname'], $reason);
            }
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Erreur lors du rejet du compte étudiant: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les spécialités disponibles pour un département
 * 
 * @param string $department Le département
 * @return array La liste des spécialités
 */
function getSpecialitesByDepartment($department) {
    global $pdo;
    
    try {
        // Vérifier si la nouvelle structure est en place
        $stmt = $pdo->query("SHOW TABLES LIKE 'departement_specialites'");
        $useNewTables = ($stmt->rowCount() > 0);
        
        if ($useNewTables) {
            // Utiliser la nouvelle structure
            if (empty($department)) {
                // Si aucun département n'est spécifié (cas du directeur), renvoyer toutes les spécialités
                $stmt = $pdo->prepare("SELECT DISTINCT nom FROM specialites ORDER BY nom");
                $stmt->execute();
            } else {
                // Sinon, renvoyer les spécialités du département
                $stmt = $pdo->prepare("
                    SELECT s.nom 
                    FROM specialites s
                    JOIN departement_specialites ds ON s.id = ds.specialite_id
                    JOIN departements d ON ds.departement_id = d.id
                    WHERE d.nom = ? OR d.code = ?
                    ORDER BY s.nom
                ");
                $stmt->execute([$department, $department]);
            }
        } else {
            // Utiliser l'ancienne structure
            if (empty($department)) {
                // Si aucun département n'est spécifié, renvoyer toutes les spécialités uniques
                $stmt = $pdo->prepare("SELECT DISTINCT specialite FROM student_details WHERE specialite IS NOT NULL ORDER BY specialite");
                $stmt->execute();
            } else {
                // Sinon, renvoyer les spécialités du département selon l'ancienne logique
                $departmentSpecialties = getDepartmentSpecialtiesOld($department);
                return $departmentSpecialties;
            }
        }
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des spécialités: ' . $e->getMessage());
        return [];
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
