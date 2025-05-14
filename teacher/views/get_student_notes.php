<?php
// Fichier: teacher/api/get_students_notes.php

// Inclure les fichiers nécessaires
require_once '../../shared/init.php';
require_once '../../shared/config/db_connect.php';
require_once '../../shared/models/matiere_model.php';

// Vérifier si l'utilisateur est connecté en tant qu'enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Récupérer l'ID de l'enseignant connecté
$teacher_id = $_SESSION['user_id'];

// Récupérer les paramètres de la requête
$matiere = isset($_GET['matiere']) ? $_GET['matiere'] : null;
$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : null;
$salle = isset($_GET['salle']) ? $_GET['salle'] : null;

// Valider les paramètres
if (empty($matiere) || empty($niveau) || empty($salle)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

try {
    global $pdo;
    
    // Récupérer l'ID de l'enseignant dans la table teacher_details
    $teacherDetailQuery = "SELECT id FROM teacher_details WHERE user_id = :user_id";
    $teacherDetailStmt = $pdo->prepare($teacherDetailQuery);
    $teacherDetailStmt->bindParam(':user_id', $teacher_id, PDO::PARAM_INT);
    $teacherDetailStmt->execute();
    $teacherDetail = $teacherDetailStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacherDetail) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Détails de l\'enseignant non trouvés']);
        exit;
    }
    
    $id_enseignant = $teacherDetail['id'];
    
    // Vérifier si l'enseignant est autorisé à enseigner cette matière dans cette classe
    $checkQuery = "SELECT id FROM matieres_enseignees 
                  WHERE id_enseignant = :id_enseignant 
                  AND matiere = :matiere 
                  AND niveau = :niveau 
                  AND salle = :salle 
                  AND status = 'approved'";
    
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':id_enseignant', $teacher_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':matiere', $matiere, PDO::PARAM_INT);
    $checkStmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
    $checkStmt->bindParam(':salle', $salle, PDO::PARAM_STR);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Vous n\'êtes pas autorisé à enseigner cette matière dans cette classe']);
        exit;
    }
    
    // Récupérer les étudiants de cette classe avec leurs notes
    $query = "SELECT 
                sd.id as id_etudiant,
                sd.matricule,
                u.firstname,
                u.lastname,
                n.id as note_id,
                n.cc1_note,
                n.cc1_bonus,
                n.cc1_final,
                n.cc2_note,
                n.cc2_bonus,
                n.cc2_final,
                n.cc3_note,
                n.cc3_bonus,
                n.cc3_final,
                n.cc4_note,
                n.cc4_bonus,
                n.cc4_final,
                n.SN1,
                n.SN2,
                n.RAT1,
                n.RAT2
              FROM student_details sd
              JOIN users u ON sd.user_id = u.id
              LEFT JOIN notes n ON sd.id = n.id_etudiant AND n.id_matiere = :id_matiere
              WHERE sd.niveau = :niveau
              AND sd.classe = :salle
              AND sd.status = 'approved'
              ORDER BY u.lastname, u.firstname";
    $matiereLibelle = getMatiereIdByLibelle($matiere);
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_matiere', $matiereLibelle, PDO::PARAM_INT);
    $stmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
    $stmt->bindParam(':salle', $salle, PDO::PARAM_STR);
    $stmt->execute();
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les données pour la réponse JSON
    $result = [];
    foreach ($students as $student) {
        // Construire le nom complet
        $fullName = $student['lastname'] . ' ' . $student['firstname'];
        
        // Organiser les notes dans un objet
        $notes = [
            'cc1_note' => $student['cc1_note'],
            'cc1_bonus' => $student['cc1_bonus'],
            'cc1_final' => $student['cc1_final'],
            'cc2_note' => $student['cc2_note'],
            'cc2_bonus' => $student['cc2_bonus'],
            'cc2_final' => $student['cc2_final'],
            'cc3_note' => $student['cc3_note'],
            'cc3_bonus' => $student['cc3_bonus'],
            'cc3_final' => $student['cc3_final'],
            'cc4_note' => $student['cc4_note'],
            'cc4_bonus' => $student['cc4_bonus'],
            'cc4_final' => $student['cc4_final'],
            'SN1' => $student['SN1'],
            'SN2' => $student['SN2'],
            'RAT1' => $student['RAT1'],
            'RAT2' => $student['RAT2']
        ];
        
        // Ajouter l'étudiant au résultat
        $result[] = [
            'id_etudiant' => $student['id_etudiant'],
            'matricule' => $student['matricule'],
            'nom' => $fullName,
            'note_id' => $student['note_id'],
            'notes' => $notes
        ];
    }
    
    // Retourner le résultat en JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (PDOException $e) {
    // Journaliser l'erreur
    error_log('Erreur lors de la récupération des notes: ' . $e->getMessage());
    
    // Retourner une erreur
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des données', 'details' => $e->getMessage()]);
    exit;
}
?>