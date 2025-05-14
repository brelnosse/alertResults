<?php
    require_once __DIR__ . '/../../shared/init.php';
    require_once __DIR__ . '/../../shared/models/matiere_model.php';
    require_once __DIR__ . '/../init_teacher.php';

    // Vérifier si l'utilisateur est connecté en tant qu'administrateur
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
        header('Location: ../index.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | Système de Gestion Académique</title>
    <!-- <link rel="stylesheet" href="../../shared/assets/css/common.css"> -->
     <?php
        if(enseignantIdEstPresent($_SESSION['user_id'])){
            echo '<link rel="stylesheet" href="../../shared/assets/css/dashboard.css"><link rel="stylesheet" href="../assets/css/dashboard_default.css">';
        }else{
            echo '<link rel="stylesheet" href="../../shared/assets/css/dashboard.css"><link rel="stylesheet" href="../assets/css/dashboard_default.css">';
        }
     ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <?php include_once __DIR__ . '/../views/sidebar.php'; ?>
    <?php include_once __DIR__ . '/../views/navbar.php'; ?>
    <!-- Main Content -->
    <div class="dashboard">
        <div class="hero">
            <h2>Bienvenue, M/Mme. <?= ucwords($_SESSION['user_name']); ?></h2>
            
            <?php
            if(enseignantIdEstPresent($_SESSION['user_id']) AND $_SESSION['status'] != 'approved'){ ?>
                <p>Vous avez déjà sélectionné vos matières. Vous pouvez consulter vos matières sélectionnées et les modifier si nécessaire.</p>
            <?php
            }elseif($_SESSION['status'] == 'approved'){ ?>
                <p>Vous avez déjà sélectionné vos matières. Vous pouvez consulter vos matières sélectionnées et les modifier si nécessaire.</p>
                <p class="info"><i class="fas fa-info-circle"></i> Vous pouvez maintenant consulter les notes que vous avez déjà saisies</p>
            <?php
            }else{ ?>
                <p>Afin de pouvoir renseigner les notes des étudiants, il vous faut sélectionner la matière que vous enseignez ainsi que les différentes salles dans lesquelles vous l'enseignez. Une fois cela fait, vous recevrez une confirmation par mail du chef de département vous autorisant à remplir les notes.</p>
                <p class="info"><i class="fas fa-info-circle"></i> Cette etape est importante et ne sera fait que lors de votre premier connexion à l'application</p>
            <?php
            }
            ?>
        </div>
        <div class="dashboard-grid" style="margin-top: 20px;">
            <div class="card">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">127</div>
                            <div class="stat-label">Étudiants</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">42</div>
                            <div class="stat-label">Évaluations complétées</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">7</div>
                            <div class="stat-label">Notes en attente</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">3</div>
                            <div class="stat-label">Messages non lus</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Activité récente</div>
                        <a href="#" class="btn btn-outline">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <ul class="activity-list">
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Notes saisies pour Mathématiques S2</div>
                                    <div class="activity-time">Aujourd'hui, 10:45</div>
                                </div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Sophie Martin a contesté sa note</div>
                                    <div class="activity-time">Hier, 14:30</div>
                                </div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Rapport d'évaluation généré</div>
                                    <div class="activity-time">12 Mai, 09:15</div>
                                </div>
                            </li>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Notes validées par le directeur</div>
                                    <div class="activity-time">10 Mai, 16:50</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Calendrier</div>
                        <a href="#" class="btn btn-outline">Voir agenda</a>
                    </div>
                    <div class="card-body">
                        <div class="calendar">
                            <div class="calendar-header">
                                <button class="btn btn-outline">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="calendar-month">Mai 2025</div>
                                <button class="btn btn-outline">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <div class="calendar-cell calendar-weekday">L</div>
                            <div class="calendar-cell calendar-weekday">M</div>
                            <div class="calendar-cell calendar-weekday">M</div>
                            <div class="calendar-cell calendar-weekday">J</div>
                            <div class="calendar-cell calendar-weekday">V</div>
                            <div class="calendar-cell calendar-weekday">S</div>
                            <div class="calendar-cell calendar-weekday">D</div>
                            
                            <div class="calendar-cell calendar-day"></div>
                            <div class="calendar-cell calendar-day">1</div>
                            <div class="calendar-cell calendar-day">2</div>
                            <div class="calendar-cell calendar-day">3</div>
                            <div class="calendar-cell calendar-day">4</div>
                            <div class="calendar-cell calendar-day">5</div>
                            <div class="calendar-cell calendar-day">6</div>
                            
                            <div class="calendar-cell calendar-day">7</div>
                            <div class="calendar-cell calendar-day">8</div>
                            <div class="calendar-cell calendar-day">9</div>
                            <div class="calendar-cell calendar-day today">10</div>
                            <div class="calendar-cell calendar-day has-event">11</div>
                            <div class="calendar-cell calendar-day">12</div>
                            <div class="calendar-cell calendar-day">13</div>
                            
                            <div class="calendar-cell calendar-day">14</div>
                            <div class="calendar-cell calendar-day has-event">15</div>
                            <div class="calendar-cell calendar-day">16</div>
                            <div class="calendar-cell calendar-day">17</div>
                            <div class="calendar-cell calendar-day">18</div>
                            <div class="calendar-cell calendar-day">19</div>
                            <div class="calendar-cell calendar-day">20</div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter un événement
                        </button>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <div class="card-title">Notes à saisir prochainement</div>
                </div>
                <div class="card-body" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f5f7fa; text-align: left;">
                                <th style="padding: 12px 15px;">Classe</th>
                                <th style="padding: 12px 15px;">Matière</th>
                                <th style="padding: 12px 15px;">Type d'évaluation</th>
                                <th style="padding: 12px 15px;">Date limite</th>
                                <th style="padding: 12px 15px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 15px;">Terminale S2</td>
                                <td style="padding: 12px 15px;">Physique</td>
                                <td style="padding: 12px 15px;">Examen final</td>
                                <td style="padding: 12px 15px;">15 Mai 2025</td>
                                <td style="padding: 12px 15px;">
                                    <button class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                                        <i class="fas fa-edit"></i> Saisir
                                    </button>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 15px;">Première S1</td>
                                <td style="padding: 12px 15px;">Chimie</td>
                                <td style="padding: 12px 15px;">Contrôle continu</td>
                                <td style="padding: 12px 15px;">20 Mai 2025</td>
                                <td style="padding: 12px 15px;">
                                    <button class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                                        <i class="fas fa-edit"></i> Saisir
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 15px;">Seconde B</td>
                                <td style="padding: 12px 15px;">Sciences</td>
                                <td style="padding: 12px 15px;">Projet</td>
                                <td style="padding: 12px 15px;">22 Mai 2025</td>
                                <td style="padding: 12px 15px;">
                                    <button class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                                        <i class="fas fa-edit"></i> Saisir
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-outline">Voir toutes les échéances</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/dashboard2.js"></script>
    <!-- 
        <div class="hero pending">
            <h2>Vous avez déjà sélectionné vos matières</h2>
            <p>Vos informations sont en attente de vérification par le chef de département.</p>
            <p class="info"><i class="fas fa-info-circle"></i> Une fois la vérification éffectuée, vous recevrez une confirmation par mail.</p>
        </div> -->
    <script>
        const matieresniveau1libelle = [];
        const matieresniveau1 = <?= json_encode(getMatieresByNiveau(1)) ?>; 
        for(let i = 0; i < matieresniveau1.length; i++){
            matieresniveau1libelle.push(matieresniveau1[i].libelle);
        }

        const matieresniveau2libelle = [];
        const matieresniveau2 = <?= json_encode(getMatieresByNiveau(2)) ?>; 
        for(let i = 0; i < matieresniveau2.length; i++){
            matieresniveau2libelle.push(matieresniveau2[i].libelle);
        }
        // Données des matières disponibles par niveau
        const matieresByNiveau = {
            "1": {
                "A": matieresniveau1libelle,
                "B": matieresniveau1libelle,
                "C": matieresniveau1libelle,
                "D": matieresniveau1libelle
            },
            "2": {
                "A": matieresniveau2libelle,
                "B": matieresniveau2libelle,
                "C": matieresniveau2libelle,
                "D": matieresniveau2libelle
            }
        };
     </script>
    <script src="../assets/js/dashboard1.js"></script>
</body>
</html>