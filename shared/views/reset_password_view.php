<?php
// Démarrer la session
session_start();

// Récupérer le token de l'URL
$token = $_GET['token'] ?? '';

// Si le token n'est pas présent, rediriger vers la page de demande de réinitialisation
if (empty($token)) {
    header('Location: forgot_password_view.php');
    exit;
}

// Récupérer les messages d'erreur ou de succès
$errorMessage = $_SESSION['error_message'] ?? '';
$successMessage = $_SESSION['success_message'] ?? '';

// Nettoyer les variables de session
unset($_SESSION['error_message'], $_SESSION['success_message']);

// Déterminer le type de portail en fonction du chemin d'accès
$portalType = '';
$formAction = '';
$returnUrl = '';

$currentPath = $_SERVER['PHP_SELF'];

if (strpos($currentPath, '/ad/') !== false) {
    $portalType = 'admin';
    $formAction = '../../shared/controllers/password_controller.php?action=reset';
    $returnUrl = '../index.php';
} elseif (strpos($currentPath, '/student/') !== false) {
    $portalType = 'student';
    $formAction = '../../shared/controllers/password_controller.php?action=reset';
    $returnUrl = '../index.php';
} elseif (strpos($currentPath, '/teacher/') !== false) {
    $portalType = 'teacher';
    $formAction = '../../shared/controllers/password_controller.php?action=reset';
    $returnUrl = '../index.php';
} else {
    $portalType = '';
    $formAction = 'shared/controllers/password_controller.php?action=reset';
    $returnUrl = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Réinitialisation de mot de passe</title>
    <link rel="stylesheet" href="<?php echo $portalType ? '../../shared/assets/css/common.css' : 'shared/assets/css/common.css'; ?>">
    <style>
        .container {
            height: 500px;
        }
        .error-container {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            color: #d32f2f;
            margin: 5px 0;
            font-size: 14px;
        }
        .success-container {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            color: #2e7d32;
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pattern-side">
            <!-- Les cercles seront générés dynamiquement par JavaScript -->
        </div>
        <form class="form-side" id="reset-password-form" method="POST" action="<?php echo $formAction; ?>">
            <h1>Réinitialisation de mot de passe</h1>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-container">
                    <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-container">
                    <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre nouveau mot de passe" required>
                <div class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre nouveau mot de passe" required>
                <div class="error-message"></div>
            </div>
            
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <button type="submit" id="reset-btn">Réinitialiser mon mot de passe</button>
            
            <div class="login-link">
                <a href="<?php echo $returnUrl; ?>">Retour à la page de connexion</a>
            </div>
        </form>
    </div>
    
    <script src="<?php echo $portalType ? '../../shared/assets/js/circle.js' : 'shared/assets/js/circle.js'; ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const resetBtn = document.getElementById("reset-btn");
            const form = document.getElementById("reset-password-form");
            
            resetBtn.addEventListener("click", function(e) {
                e.preventDefault();
                
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirm_password").value;
                
                // Réinitialiser les messages d'erreur
                const errorMessages = document.querySelectorAll(".error-message");
                errorMessages.forEach((element) => {
                    element.style.display = "none";
                    element.textContent = "";
                });
                
                const inputs = document.querySelectorAll("input");
                inputs.forEach((input) => {
                    input.classList.remove("error");
                });
                
                let isValid = true;
                
                // Validation du mot de passe
                if (!password) {
                    const passwordError = document.querySelector("#password + .error-message");
                    if (passwordError) {
                        passwordError.textContent = "Le mot de passe est obligatoire";
                        passwordError.style.display = "block";
                        document.getElementById("password").classList.add("error");
                        isValid = false;
                    }
                } else if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
                    const passwordError = document.querySelector("#password + .error-message");
                    if (passwordError) {
                        passwordError.textContent = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre";
                        passwordError.style.display = "block";
                        document.getElementById("password").classList.add("error");
                        isValid = false;
                    }
                }
                
                // Validation de la confirmation du mot de passe
                if (!confirmPassword) {
                    const confirmPasswordError = document.querySelector("#confirm_password + .error-message");
                    if (confirmPasswordError) {
                        confirmPasswordError.textContent = "La confirmation du mot de passe est obligatoire";
                        confirmPasswordError.style.display = "block";
                        document.getElementById("confirm_password").classList.add("error");
                        isValid = false;
                    }
                } else if (password !== confirmPassword) {
                    const confirmPasswordError = document.querySelector("#confirm_password + .error-message");
                    if (confirmPasswordError) {
                        confirmPasswordError.textContent = "Les mots de passe ne correspondent pas";
                        confirmPasswordError.style.display = "block";
                        document.getElementById("confirm_password").classList.add("error");
                        isValid = false;
                    }
                }
                
                if (isValid) {
                    this.innerHTML = '<span style="display: inline-block; animation: spin 1s infinite linear;">↻</span>';
                    
                    // Soumettre le formulaire
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
