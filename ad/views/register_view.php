<?php
// Inclure le fichier d'initialisation
require_once '../init_ad.php';

// Vérifier si l'utilisateur est déjà connecté et le rediriger vers le tableau de bord si c'est le cas
redirectIfAdmin();
$formData = [];
if(isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
} else {
    $formData = [
        'firstname' => '',
        'lastname' => '',
        'phone' => '',
        'email' => '',
        'role' => '',
        'department' => ''
    ];
}
// var_dump($formData);
$formErrors = [];
if(isset($_SESSION['form_errors'])) {
    $formErrors = $_SESSION['form_errors'];
} else {
    $formErrors = [
        'firstname' => '',
        'lastname' => '',
        'phone' => '',
        'email' => '',
        'role' => '',
        'department' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inscription Administrateur</title>
    <link rel="stylesheet" href="../../shared/assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/authentication.css">
</head>
<body>
    <div class="container">
        <div class="pattern-side">
            <div class="pattern-element circle1"></div>
            <div class="pattern-element circle2"></div>
            <div class="pattern-element circle3"></div>
            <div class="pattern-element circle4"></div>
            <div class="pattern-element circle5"></div>
            <div class="pattern-element circle6"></div>
            <div class="pattern-element circle7"></div>
            <div class="pattern-element circle8"></div>
            <div class="pattern-element circle9"></div>
        </div>
        <div class="form-side">
            <h1>Inscription Administrateur</h1>
            <form id="admin-form" action="../controllers/user_controller_admin.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">Prénom</label>
                        <input type="text" id="firstname" name="firstname" value="<?= $formData['firstname']; ?>" required>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['firstname']) ? $formErrors['firstname'] : '' ?></p>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Nom</label>
                        <input type="text" id="lastname" name="lastname" value="<?= $formData['lastname']; ?>" required>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['lastname']) ? $formErrors['lastname'] : '' ?></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?= $formData['phone']; ?>" placeholder="6XXXXXXXX" required>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['phone']) ? $formErrors['phone'] : '' ?></p>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" value="<?= $formData['email']; ?>" required>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['email']) ? $formErrors['email'] : '' ?></p>
                </div>
                
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required>
                        <option value="">Sélectionnez un rôle</option>
                        <option value="directeur" <?= $formData['role'] == 'directeur' ? 'selected' : '' ?>>Directeur de l'école</option>
                        <option value="chef" <?= $formData['role'] == 'chef' ? 'selected' : '' ?>>Chef de département</option>
                    </select>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['role']) ? $formErrors['role'] : '' ?></p>
                </div>
                
                <div class="form-group" id="department-group">
                    <label for="department">Département</label>
                    <select id="department" name="department" required>
                        <option value="" <?= empty($formData['department']) ? 'selected' : '' ?>>Sélectionnez un département</option>
                        <option value="prepa-ingenieur" <?= $formData['department'] == 'prepa-ingenieur' ? 'selected' : '' ?>>Prépa-Ingénieur</option>
                        <option value="ti" <?= $formData['department'] == 'ti' ? 'selected' : '' ?>>TI (DUT et Licence)</option>
                        <option value="ingenieur" <?= $formData['department'] == 'ingenieur' ? 'selected' : '' ?>>Ingénieur</option>
                        <option value="bts" <?= $formData['department'] == 'bts' ? 'selected' : '' ?>>BTS</option>
                    </select>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['departement']) ? $formErrors['departement'] : '' ?></p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le mot de passe</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <div class="error-message"></div>
                    </div>
                </div>
                
                <button type="submit">S'inscrire</button>
            </form>
            <div class="login-link">
                <a href="../index.php">Déjà inscrit? Connectez-vous</a>
            </div>
        </div>
    </div>
    <script src="../../shared/assets/js/common.js"></script>
    <script src="../../shared/assets/js/circle.js"></script>
</body>
</html>
<?php
    if(isset($_SESSION['form_data'])){
        unset($_SESSION['form_data']);
        $formData = [];
    }
    if(isset($_SESSION['form_errors'])){
        unset($_SESSION['form_errors']);
        $formErrors = [];
    }
