<?php 
    // Démarrer la session pour accéder aux variables de session
    
    // Récupérer les erreurs et les données du formulaire s'il y en a
    $loginErrors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];
    $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : ['email' => ''];
    $successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
    
    // Nettoyer les variables de session
    unset($_SESSION['login_errors'], $_SESSION['form_data'], $_SESSION['success_message']);
    if(isset($_SESSION['flash_message'])) {
        $successMessage = $_SESSION['flash_message']['message'];
        unset($_SESSION['flash_message']);
    }
    // Déterminer le type d'utilisateur en fonction du chemin d'accès
    $userType = '';
    $formAction = '';
    
    $currentPath = $_SERVER['PHP_SELF'];
    
    if (strpos($currentPath, '/ad/') !== false) {
        $userType = 'admin';
        $formAction = '../shared/controllers/login_controller.php';
        $forgotPasswordUrl = 'views/forgot_password_view.php';
    } elseif (strpos($currentPath, '/student/') !== false) {
        $userType = 'student';
        $formAction = '../shared/controllers/login_controller.php';
        $forgotPasswordUrl = 'views/forgot_password_view.php';
    } elseif (strpos($currentPath, '/teacher/') !== false) {
        $userType = 'teacher';
        $formAction = '../shared/controllers/login_controller.php';
        $forgotPasswordUrl = 'views/forgot_password_view.php';
    } else {
        $forgotPasswordUrl = 'shared/views/forgot_password_view.php';
        $formAction = 'shared/controllers/login_controller.php';
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connexion</title>
    <link rel="stylesheet" href="../shared/assets/css/common.css">
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
        .info-container {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info {
            color: #0d47a1;
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
        <form class="form-side" id="login-form" method="POST" action="<?php echo $formAction; ?>">
            <h1>Connectez-vous à votre compte</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-container">
                    <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($loginErrors)): ?>
                <div class="error-container">
                    <?php foreach ($loginErrors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Adresse mail</label>
                <input type="email" id="email" name="email" placeholder="Entrez votre email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                <div class="error-message"></div>
            </div>
            <div class="remember-me" style="display: flex; align-items: center;">
                <input type="checkbox" id="remember" name="remember" style="width: 15px; height: 15px; margin-right: 10px;">
                <label for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" id="login-btn">Se connecter maintenant</button>
            <div class="login-link">
                <a href="<?php echo $forgotPasswordUrl; ?>">Mot de passe oublié ?</a>
            </div>
            <div class="signup-links">
                <p>Pas encore inscrit ? <a href="views/register_view.php">S'inscrire</a></p>
            </div>
    </form>
    </div>
    <script src="../shared/assets/js/login.js"></script>
    <script src="../shared/assets/js/circle.js"></script>
    <script src="../shared/assets/js/common.js"></script>
</body>
</html>
