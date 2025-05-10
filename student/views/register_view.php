<?php
session_start();
$formData = [];
if(isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
} else {
    $formData = [
        'firstname' => '',
        'lastname' => '',
        'birthdate' => '',
        'phone' => '',
        'email' => '',
        'matricule' => '',
        'cycle' => '',
        'niveau' => '',
        'specialite' => '',
        'classe' => ''
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
        'birthdate' => '',
        'phone' => '',
        'email' => '',
        'matricule' => '',
        'cycle' => '',
        'niveau' => '',
        'specialite' => '',
        'classe' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inscription Étudiant</title>
    <link rel="stylesheet" href="../../shared/assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/authentication.css">
</head>
<body>
    <div class="container">
    <div class="pattern-side">
  <!-- Les cercles seront générés dynamiquement par JavaScript -->
</div>
        <div class="form-side">
            <h1>Inscription Étudiant</h1>
            <form id="student-form" action="../controllers/user_controller_student.php" method="POST">
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
                    <label for="birthdate">Date de naissance</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?= $formData['birthdate']; ?>" required>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['birthdate']) ? $formErrors['birthdate'] : '' ?></p>
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
                    <p class="error"><?= isset($formErrors['email']) ? $formErrors['email'] : '' ?></p>
                </div>
                
                <div class="form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" value="<?= $formData['matricule']; ?>" required>
                    <div class="error-message"></div>
                    <p class="error"><?= isset($formErrors['matricule']) ? $formErrors['matricule'] : '' ?></p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cycle">Cycle</label>
                        <select id="cycle" name="cycle" required>
                            <option value="" <?= empty($formData['cycle']) ? 'selected' : '' ?>>Sélectionnez un cycle</option>
                            <option value="prepa-ingenieur" <?= $formData['cycle'] == 'prepa-ingenieur' ? 'selected' : '' ?>>Prépa-Ingénieur</option>
                            <option value="bts" <?= $formData['cycle'] == 'bts' ? 'selected' : '' ?>>BTS</option>
                            <option value="ingenieur" <?= $formData['cycle'] == 'ingenieur' ? 'selected' : '' ?>>Ingénieur</option>
                            <option value="dut" <?= $formData['cycle'] == 'dut' ? 'selected' : '' ?>>DUT</option>
                            <option value="licence" <?= $formData['cycle'] == 'licence' ? 'selected' : '' ?>>Licence</option>
                        </select>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['cycle']) ? $formErrors['cycle'] : '' ?></p>
                    </div>
                    <div class="form-group">
                        <label for="niveau">Niveau</label>
                        <select id="niveau" name="niveau" required>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['niveau']) ? $formErrors['niveau'] : '' ?></p>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <select id="specialite" name="specialite" required>
                            <option value="">Sélectionnez une spécialité</option>
                        </select>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['specialite']) ? $formErrors['specialite'] : '' ?></p>
                    </div>
                    <div class="form-group">
                        <label for="classe">Classe</label>
                        <select id="classe" name="classe" required>
                            <option value="">Sélectionnez une classe</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                        <div class="error-message"></div>
                        <p class="error"><?= isset($formErrors['classe']) ? $formErrors['classe'] : '' ?></p>
                    </div>
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
