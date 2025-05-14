<?php
// matiere_model.php (Version procédurale)

// Inclut le fichier de configuration de la base de données
require_once '../../shared/config/db_connect.php';

/**
 * Récupère toutes les matières de la base de données.
 *
 * @return array Un tableau contenant toutes les matières, ou un tableau vide si aucune matière n'est trouvée.
 */
function getAllMatieres() {
    global $pdo;
    $query = "SELECT * FROM matieres";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMatiereIdByLibelle($libelle) {
    global $pdo;
    $query = "SELECT id FROM matieres WHERE libelle = :libelle";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':libelle', $libelle, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn(); // Retourne l'ID de la matière
}
/**
 * Récupère une matière spécifique par son ID.
 *
 * @param int $id L'ID de la matière à récupérer.
 * @return array|null Un tableau contenant les informations de la matière, ou null si la matière n'est pas trouvée.
 */
function getMatiereById($id) {
    global $pdo;
    $query = "SELECT * FROM matieres WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Ajoute une nouvelle matière à la base de données.
 *
 * @param string $libelle Le libellé de la matière.
 * @param string $code Le code de la matière.
 * @param int $credit Le nombre de crédits de la matière.
 * @param string $niveau Le niveau de la matière.
 * @return int|false L'ID de la nouvelle matière insérée, ou false en cas d'erreur.
 */
function addMatiere($libelle, $code, $credit, $niveau) {
    global $pdo;
    $query = "INSERT INTO matieres (libelle, code, credit, niveau) VALUES (:libelle, :code, :credit, :niveau)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':libelle', $libelle, PDO::PARAM_STR);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':credit', $credit, PDO::PARAM_INT);
    $stmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
    $stmt->execute();
    return $pdo->lastInsertId(); // Retourne l'ID de la nouvelle matière
}

/**
 * Met à jour une matière existante dans la base de données.
 *
 * @param int $id L'ID de la matière à modifier.
 * @param string $libelle Le nouveau libellé de la matière.
 * @param string $code Le nouveau code de la matière.
 * @param int $credit Le nouveau nombre de crédits de la matière.
  * @param string $niveau Le nouveau niveau de la matière.
 * @return bool True en cas de succès, false en cas d'erreur.
 */
function updateMatiere($id, $libelle, $code, $credit, $niveau) {
    global $pdo;
    $query = "UPDATE matieres SET libelle = :libelle, code = :code, credit = :credit, niveau = :niveau WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':libelle', $libelle, PDO::PARAM_STR);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':credit', $credit, PDO::PARAM_INT);
    $stmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
    return $stmt->execute();
}

/**
 * Supprime une matière de la base de données.
 *
 * @param int $id L'ID de la matière à supprimer.
 * @return bool True en cas de succès, false en cas d'erreur.
 */
function deleteMatiere($id) {
    global $pdo;
    $query = "DELETE FROM matieres WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Vérifie si une matière existe dans la base de données en utilisant le code de la matière.
 *
 * @param string $code Le code de la matière à vérifier.
 * @return bool True si la matière existe, false sinon.
 */
function matiereExists($code) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM matieres WHERE code = :code";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt->execute();
    return (bool) $stmt->fetchColumn(); // Retourne true si le count est > 0
}

/**
 * Récupère les matières par niveau.
 *
 * @param string $niveau Le niveau des matières à récupérer.
 * @return array Un tableau contenant les matières du niveau spécifié, ou un tableau vide si aucune matière n'est trouvée.
 */
function getMatieresByNiveau($niveau) {
    global $pdo;
    $query = "SELECT * FROM matieres WHERE niveau = :niveau";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si l'ID de l'enseignant est présent dans la table matieres_enseignees.
 *
 * @param PDO $pdo La connexion à la base de données.
 * @param int $enseignant_id L'ID de l'enseignant à vérifier.
 * @return bool True si l'ID de l'enseignant est présent, false sinon.
 */
function enseignantIdEstPresent($enseignant_id){
    global $pdo;
    $query = "SELECT COUNT(*) FROM matieres_enseignees WHERE id_enseignant = :id_enseignant";
    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_enseignant', $enseignant_id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn(); // Retourne true si le count est > 0
    } catch (PDOException $e) {
        // Gestion des erreurs : enregistrer l'erreur et retourner false
        error_log("Erreur lors de la vérification de l'ID de l'enseignant : " . $e->getMessage());
        return false;
    }
}
?>
