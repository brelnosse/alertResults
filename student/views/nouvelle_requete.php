<?php
require_once '../init_std.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle requête - AlertResults</title>
    <!-- <link rel="stylesheet" href="../../shared/assets/css/common.css"> -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-title {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #004080;
            outline: none;
        }
        
        .form-control.error {
            border-color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: #004080;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #002d5a;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-secondary:hover {
            background-color: #e9ecef;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .form-info {
            background-color: #e7f3fe;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        
        .form-info h4 {
            margin-top: 0;
            color: #0c5460;
        }
        
        .form-info p {
            margin-bottom: 0;
            color: #0c5460;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> AlertResults</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../controllers/logout_controller.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="active"><a href="requete.php"><i class="fas fa-clipboard-list"></i> Mes requêtes</a></li>
                <li><a href="index.php?page=notes"><i class="fas fa-chart-bar"></i> Mes notes</a></li>
                <li><a href="index.php?page=profile"><i class="fas fa-user"></i> Mon profil</a></li>
            </ul>
        </nav>
        
        <main>
            <h2><i class="fas fa-plus-circle"></i> Nouvelle requête</h2>
            
            <div class="form-info">
                <h4><i class="fas fa-info-circle"></i> Information</h4>
                <p>Votre requête sera d'abord examinée par le chef de département avant d'être transmise à l'enseignant concerné.</p>
            </div>
            
            <div class="form-container">
                <h3 class="form-title">Formulaire de requête</h3>
                
                <form action="index.php?page=nouvelle_requete" method="post">
                    <div class="form-group">
                        <label for="teacher_id">Enseignant concerné</label>
                        <select name="teacher_id" id="teacher_id" class="form-control <?php echo $errorHandler->hasError('teacher_id') ? 'error' : ''; ?>">
                            <option value="">Sélectionnez un enseignant</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>" <?php echo (isset($_POST['teacher_id']) && $_POST['teacher_id'] == $teacher['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($teacher['firstname'] . ' ' . $teacher['lastname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($errorHandler->hasError('teacher_id')): ?>
                            <div class="error-message"><?php echo $errorHandler->getError('teacher_id'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="matiere_id">Matière concernée</label>
                        <select name="matiere_id" id="matiere_id" class="form-control <?php echo $errorHandler->hasError('matiere_id') ? 'error' : ''; ?>">
                            <option value="">Sélectionnez d'abord un enseignant</option>
                            <?php if (!empty($matieres)): ?>
                                <?php foreach ($matieres as $matiere): ?>
                                    <option value="<?php echo $matiere['id']; ?>" <?php echo (isset($_POST['matiere_id']) && $_POST['matiere_id'] == $matiere['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($matiere['libelle'] . ' (' . $matiere['code'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if ($errorHandler->hasError('matiere_id')): ?>
                            <div class="error-message"><?php echo $errorHandler->getError('matiere_id'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type de requête</label>
                        <select name="type" id="type" class="form-control <?php echo $errorHandler->hasError('type') ? 'error' : ''; ?>">
                            <option value="">Sélectionnez un type</option>
                            <option value="erreur_note" <?php echo (isset($_POST['type']) && $_POST['type'] == 'erreur_note') ? 'selected' : ''; ?>>Erreur de note</option>
                            <option value="erreur_absence" <?php echo (isset($_POST['type']) && $_POST['type'] == 'erreur_absence') ? 'selected' : ''; ?>>Erreur d'absence</option>
                            <option value="autre" <?php echo (isset($_POST['type']) && $_POST['type'] == 'autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                        <?php if ($errorHandler->hasError('type')): ?>
                            <div class="error-message"><?php echo $errorHandler->getError('type'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="sujet">Sujet</label>
                        <input type="text" name="sujet" id="sujet" class="form-control <?php echo $errorHandler->hasError('sujet') ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($_POST['sujet'] ?? ''); ?>" placeholder="Résumez votre requête en quelques mots">
                        <?php if ($errorHandler->hasError('sujet')): ?>
                            <div class="error-message"><?php echo $errorHandler->getError('sujet'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description détaillée</label>
                        <textarea name="description" id="description" class="form-control <?php echo $errorHandler->hasError('description') ? 'error' : ''; ?>" placeholder="Décrivez en détail votre problème"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <?php if ($errorHandler->hasError('description')): ?>
                            <div class="error-message"><?php echo $errorHandler->getError('description'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php?page=requetes" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Soumettre la requête
                        </button>
                    </div>
                </form>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> AlertResults. Tous droits réservés.</p>
        </footer>
    </div>
    
    <script src="../../shared/assets/js/common.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chargement dynamique des matières en fonction de l'enseignant sélectionné
            const teacherSelect = document.getElementById('teacher_id');
            const matiereSelect = document.getElementById('matiere_id');
            
            teacherSelect.addEventListener('change', function() {
                const teacherId = this.value;
                
                if (teacherId) {
                    // Réinitialiser le select des matières
                    matiereSelect.innerHTML = '<option value="">Chargement...</option>';
                    
                    // Requête AJAX pour récupérer les matières
                    fetch(`index.php?page=get_matieres_for_teacher&teacher_id=${teacherId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Réinitialiser le select des matières
                            matiereSelect.innerHTML = '<option value="">Sélectionnez une matière</option>';
                            
                            // Ajouter les options
                            data.forEach(matiere => {
                                const option = document.createElement('option');
                                option.value = matiere.id;
                                option.textContent = `${matiere.libelle} (${matiere.code})`;
                                matiereSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des matières:', error);
                            matiereSelect.innerHTML = '<option value="">Erreur lors du chargement</option>';
                        });
                } else {
                    // Réinitialiser le select des matières si aucun enseignant n'est sélectionné
                    matiereSelect.innerHTML = '<option value="">Sélectionnez d\'abord un enseignant</option>';
                }
            });
        });
    </script>
</body>
</html>
