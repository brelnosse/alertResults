<?php
/**
 * Script de migration pour la nouvelle structure de départements et spécialités
 * 
 * Ce script doit être exécuté après la création des nouvelles tables
 * mais avant d'activer le nouveau code dans l'application.
 */

// Inclure la connexion à la base de données
require_once __DIR__ . '/../shared/config/db_connect.php';

// Fonction pour journaliser les messages
function logMessage($message) {
    echo date('Y-m-d H:i:s') . " - $message\n";
    error_log($message);
}

try {
    // Démarrer une transaction
    $pdo->beginTransaction();
    
    logMessage("Début de la migration des départements et spécialités");
    
    // 1. Vérifier si les tables existent
    $tables = ['departements', 'specialites', 'departement_specialites'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            throw new Exception("La table '$table' n'existe pas. Veuillez exécuter le script de création des tables d'abord.");
        }
    }
    
    // 2. Vérifier si la colonne status existe dans student_details
    $stmt = $pdo->query("SHOW COLUMNS FROM student_details LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        logMessage("Ajout de la colonne 'status' à la table student_details");
        $pdo->exec("ALTER TABLE student_details ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        $pdo->exec("ALTER TABLE student_details ADD COLUMN rejection_reason TEXT NULL");
        $pdo->exec("ALTER TABLE student_details ADD COLUMN validated_by INT NULL");
        $pdo->exec("ALTER TABLE student_details ADD COLUMN validated_at DATETIME NULL");
        
        // Définir tous les comptes existants comme approuvés
        $pdo->exec("UPDATE student_details SET status = 'approved', validated_at = NOW()");
        logMessage("Tous les comptes étudiants existants ont été marqués comme approuvés");
    }
    
    // 3. Collecter toutes les spécialités uniques des étudiants existants
    logMessage("Collecte des spécialités existantes");
    $stmt = $pdo->query("SELECT DISTINCT specialite FROM student_details WHERE specialite IS NOT NULL AND specialite != ''");
    $existingSpecialities = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 4. S'assurer que toutes les spécialités existantes sont dans la table specialites
    logMessage("Vérification des spécialités dans la nouvelle table");
    foreach ($existingSpecialities as $speciality) {
        $stmt = $pdo->prepare("SELECT id FROM specialites WHERE nom = ? OR code = ?");
        $stmt->execute([$speciality, strtoupper(str_replace(' ', '-', $speciality))]);
        
        if ($stmt->rowCount() == 0) {
            // La spécialité n'existe pas, l'ajouter
            $code = strtoupper(str_replace(' ', '-', $speciality));
            $stmt = $pdo->prepare("INSERT INTO specialites (nom, code, description) VALUES (?, ?, ?)");
            $stmt->execute([$speciality, $code, "Spécialité migrée automatiquement"]);
            logMessage("Ajout de la spécialité: $speciality");
        }
    }
    
    // 5. Créer une table temporaire pour stocker les mappings
    $pdo->exec("CREATE TEMPORARY TABLE speciality_mapping (old_name VARCHAR(100), new_id INT)");
    
    // 6. Remplir la table temporaire avec les mappings
    foreach ($existingSpecialities as $speciality) {
        $stmt = $pdo->prepare("SELECT id FROM specialites WHERE nom = ? OR code = ?");
        $stmt->execute([$speciality, strtoupper(str_replace(' ', '-', $speciality))]);
        $specialityId = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO speciality_mapping (old_name, new_id) VALUES (?, ?)");
        $stmt->execute([$speciality, $specialityId]);
    }
    
    // 7. Associer les spécialités aux départements appropriés
    // Pour cet exemple, nous associons toutes les spécialités au département "Informatique"
    logMessage("Association des spécialités aux départements");
    $stmt = $pdo->query("SELECT id FROM departements WHERE nom = 'Informatique' OR code = 'INFO' LIMIT 1");
    $defaultDepartmentId = $stmt->fetchColumn();
    
    if (!$defaultDepartmentId) {
        // Si le département Informatique n'existe pas, créer un département par défaut
        $stmt = $pdo->prepare("INSERT INTO departements (nom, code, description) VALUES (?, ?, ?)");
        $stmt->execute(['Informatique', 'INFO', 'Département par défaut pour la migration']);
        $defaultDepartmentId = $pdo->lastInsertId();
        logMessage("Création du département par défaut: Informatique");
    }
    
    // Associer toutes les spécialités au département par défaut
    $stmt = $pdo->query("SELECT old_name, new_id FROM speciality_mapping");
    $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($mappings as $mapping) {
        $specialityId = $mapping['new_id'];
        
        // Vérifier si l'association existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departement_specialites WHERE departement_id = ? AND specialite_id = ?");
        $stmt->execute([$defaultDepartmentId, $specialityId]);
        
        if ($stmt->fetchColumn() == 0) {
            // Créer l'association
            $stmt = $pdo->prepare("INSERT INTO departement_specialites (departement_id, specialite_id) VALUES (?, ?)");
            $stmt->execute([$defaultDepartmentId, $specialityId]);
            logMessage("Association de la spécialité ID $specialityId au département Informatique");
        }
    }
    
    // 8. Nettoyer
    $pdo->exec("DROP TEMPORARY TABLE IF EXISTS speciality_mapping");
    
    // Valider la transaction
    $pdo->commit();
    logMessage("Migration terminée avec succès");
    
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    logMessage("ERREUR: " . $e->getMessage());
    exit(1);
}
?>
