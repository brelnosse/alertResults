<?php
// Démarrer la session
session_start();

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
    $formAction = '../../shared/controllers/password_controller.php?action=forgot';
    $returnUrl = '../index.php';
} elseif (strpos($currentPath, '/student/') !== false) {
    $portalType = 'student';
    $formAction = '../../shared/controllers/password_controller.php?action=forgot';
    $returnUrl = '../index.php';
} elseif (strpos($currentPath, '/teacher/') !== false) {
    $portalType = 'teacher';
    $formAction = '../../shared/controllers/password_controller.php?action=forgot';
    $returnUrl = '../index.php';
} else {
    $portalType = '';
    $formAction = 'shared/controllers/password_controller.php?action=forgot';
    $returnUrl = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="<?php echo $portalType ? '../../shared/assets/css/common.css' : 'shared/assets/css/common.css'; ?>">
    <style>
        .container {
            height: 400px;
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
        <form class="form-side" id="forgot-password-form" method="POST" action="<?php echo $formAction; ?>">
            <h1>Mot de passe oublié</h1>
            
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
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required>
                <div class="error-message"></div>
            </div>
            
            <input type="hidden" name="portal_type" value="<?php echo $portalType; ?>">
            
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
            const form = document.getElementById("forgot-password-form");
            
            resetBtn.addEventListener("click", function(e) {
                e.preventDefault();
                
                const email = document.getElementById("email").value;
                
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
                
                if (email) {
                    this.innerHTML = '<span style="display: inline-block; animation: spin 1s infinite linear;">↻</span>';
                    
                    // Soumettre le formulaire
                    form.submit();
                } else {
                    // Afficher l'erreur pour l'email vide
                    const emailError = document.querySelector("#email + .error-message");
                    if (emailError) {
                        emailError.textContent = "L'adresse email est obligatoire";
                        emailError.style.display = "block";
                        document.getElementById("email").classList.add("error");
                    }
                }
            });
        });
    </script>
</body>
</html>
