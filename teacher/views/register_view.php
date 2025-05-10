<?php
session_start();
$formData = [];
if(isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
} else {
    $formData = [
        'firstname' => '',
        'lastname' => '',
        'phone' => '',
        'email' => '',
        'password' => '',
        'confirmPassword' => ''
    ];
}
$formErrors = [];
if(isset($_SESSION['form_errors'])) {
    $formErrors = $_SESSION['form_errors'];
} else {
    $formErrors = [
        'firstname' => '',
        'lastname' => '',
        'phone' => '',
        'email' => '',
        'password' => '',
        'confirmPassword' => ''
    ];
}
// var_dump($formErrors);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inscription Enseignant</title>
    <link rel="stylesheet" href="../../shared/assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/authentication.css">
</head>
<body>
<div class="container">
        <div class="pattern-side">
  <!-- Les cercles seront générés dynamiquement par JavaScript -->
</div>
        <div class="form-side">
            <h1>Inscription Enseignant</h1>
            <form id="teacher-form" action="../controllers/user_controller_teacher.php" method="POST">
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
                    <input type="tel" id="phone" name="phone" placeholder="6XXXXXXXX" value="<?= $formData['phone']; ?>" required>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['phone']) ? $formErrors['phone'] : '' ?></p>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" value="<?= $formData['email']; ?>" required>
                    <div class="error-message"></div>
                    <p class='error'><?= isset($formErrors['email']) ? $formErrors['email'] : '' ?></p>
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
