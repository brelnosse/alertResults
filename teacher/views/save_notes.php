<?php
// Initialiser la session si ce n'est pas déjà fait
session_start();

// Vérifier si l'utilisateur est connecté en tant qu'enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

// Récupérer l'ID de l'enseignant connecté
$teacher_id = $_SESSION['user_id'];

// Connexion à la base de données
require_once '../../shared/config/db_connect.php';

// Vérifier si la requête est de type POST et contient des données JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Vérifier si les données sont valides
    if (!$data || !isset($data['matiereId']) || !isset($data['notes'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }
    
    // Récupérer les données
    $matiere_id = $data['matiereId'];
    $periode = $data['periode'];
    $notes = $data['notes'];
    $matiere = $data['matiereLibelle'];
    
    // Vérifier que la période est valide
    $periodes_valides = ['cc1', 'cc2', 'cc3', 'cc4', 'SN1', 'SN2', 'RAT1', 'RAT2'];
    if (!in_array($periode, $periodes_valides)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Période invalide']);
        exit;
    }
    
    // Vérifier que l'enseignant est bien assigné à cette matière
    $stmt = $pdo->prepare("SELECT id FROM matieres_enseignees 
                          WHERE id_enseignant = ? AND matiere = ? AND status = 'approved'");
    $stmt->execute([$teacher_id, $matiere]);
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à enregistrer des notes pour cette matière']);
        exit;
    }
    
    // Récupérer l'ID de l'enseignant dans la table teacher_details
    $stmt = $pdo->prepare("SELECT id FROM teacher_details WHERE user_id = ?");
    $stmt->execute([$teacher_id]);
    $teacher_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher_detail) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Détails de l\'enseignant introuvables']);
        exit;
    }
    
    $teacher_detail_id = $teacher_detail['id'];
    
    // Préparer les colonnes à mettre à jour en fonction de la période
    $note_column = strtolower($periode) . '_note';
    $bonus_column = strtolower($periode) . '_bonus';
    
    // Pour les périodes SN1, SN2, RAT1, RAT2, il n'y a pas de bonus
    if (in_array($periode, ['SN1', 'SN2', 'RAT1', 'RAT2'])) {
        $columns_to_update = "$periode = :note";
        $has_bonus = false;
    } else {
        $columns_to_update = "$note_column = :note, $bonus_column = :bonus";
        $has_bonus = true;
    }
    
    // Commencer une transaction
    $pdo->beginTransaction();
    
    try {
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        // Traiter chaque note
        foreach ($notes as $matricule => $note_data) {
            // Récupérer l'ID de l'étudiant à partir du matricule
            $stmt = $pdo->prepare("SELECT sd.id 
                                  FROM student_details sd 
                                  JOIN users u ON sd.user_id = u.id 
                                  WHERE sd.matricule = ?");
            $stmt->execute([$matricule]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                $error_count++;
                $errors[] = "Étudiant avec matricule $matricule introuvable";
                continue;
            }
            
            $student_id = $student['id'];
            
            // Vérifier si une note existe déjà pour cet étudiant et cette matière
            $stmt = $pdo->prepare("SELECT id FROM notes WHERE id_etudiant = ? AND id_matiere = ?");
            $stmt->execute([$student_id, $matiere_id]);
            $existing_note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Préparer les données pour l'insertion ou la mise à jour
            $note_value = isset($note_data[$periode]) ? $note_data[$periode] : 0;
            
            // Pour les périodes avec bonus
            if ($has_bonus) {
                $bonus_value = isset($note_data[$periode . '_bonus']) ? $note_data[$periode . '_bonus'] : 0;
                
                if ($existing_note) {
                    // Mettre à jour la note existante
                    $stmt = $pdo->prepare("UPDATE notes SET $note_column = :note, $bonus_column = :bonus, 
                                          date_modification = NOW() WHERE id = :id");
                    $stmt->execute([
                        'note' => $note_value,
                        'bonus' => $bonus_value,
                        'id' => $existing_note['id']
                    ]);
                } else {
                    // Insérer une nouvelle note
                    $stmt = $pdo->prepare("INSERT INTO notes (id_etudiant, id_enseignant, id_matiere, $note_column, $bonus_column) 
                                          VALUES (:student_id, :teacher_id, :matiere_id, :note, :bonus)");
                    $stmt->execute([
                        'student_id' => $student_id,
                        'teacher_id' => $teacher_detail_id,
                        'matiere_id' => $matiere_id,
                        'note' => $note_value,
                        'bonus' => $bonus_value
                    ]);
                }
            } else {
                // Pour les périodes sans bonus (SN1, SN2, RAT1, RAT2)
                if ($existing_note) {
                    // Mettre à jour la note existante
                    $stmt = $pdo->prepare("UPDATE notes SET $periode = :note, 
                                          date_modification = NOW() WHERE id = :id");
                    $stmt->execute([
                        'note' => $note_value,
                        'id' => $existing_note['id']
                    ]);
                } else {
                    // Insérer une nouvelle note
                    $stmt = $pdo->prepare("INSERT INTO notes (id_etudiant, id_enseignant, id_matiere, $periode) 
                                          VALUES (:student_id, :teacher_id, :matiere_id, :note)");
                    $stmt->execute([
                        'student_id' => $student_id,
                        'teacher_id' => $teacher_detail_id,
                        'matiere_id' => $matiere_id,
                        'note' => $note_value
                    ]);
                }
            }
            
            $success_count++;
        }
        
        // Si tout s'est bien passé, valider la transaction
        if ($error_count === 0) {
            $pdo->commit();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => "Notes enregistrées avec succès pour $success_count étudiants"
            ]);
        } else {
            // S'il y a eu des erreurs, annuler la transaction
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => "Des erreurs sont survenues lors de l'enregistrement des notes",
                'errors' => $errors
            ]);
        }
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => "Une erreur est survenue: " . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
