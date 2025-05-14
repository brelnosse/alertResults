<?php
// Récupérer le nom de la page actuelle
$current_page = basename($_SERVER['PHP_SELF']);

// Définir les correspondances entre les pages et les liens
$menu_items = [
    'dashboard.php' => 'dashboard.php', // Page d'accueil
    'fillnote.php' => 'fillnote.php',
    'classes.php' => '#',
    'statistiques.php' => '#',
    'messages.php' => '#',
    'profil.php' => '#',
    'parametres.php' => '#',
    'logout.php' => '#'
];
?>

<nav class="navbar">
    <div class="navbar-brand">SGA</div>
    
    <div class="navbar-user">
        <img src="" alt="Photo de profil" class="user-avatar">
        <div class="user-name">M/Mme <?= ucwords($_SESSION['user_name']); ?></div>
        <div class="user-role">Enseignant</div>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
        </li>
        <li class="nav-item">
            <?php
                if(enseignantIdEstPresent($_SESSION['user_id']) AND $_SESSION['status'] == 'approved'){ ?>
                <a href="fillnote.php" class="nav-link <?= ($current_page === 'fillnote.php') ? 'active' : '' ?>">
                    <i class="fas fa-edit"></i>
                    <span>Saisie de notes</span>
                </a>                    
            <?php
                }else{ ?>   
                <a href="#" onclick="alert('Vous devez renseignez au moins une salle et attendre la validation par email avant de pouvoir remplir les notes');" class="nav-link">
                    <i class="fas fa-edit"></i>
                    <span>Saisie de notes</span>
                </a> 
            <?php
                }
            ?>
        </li>
        <li class="nav-item">
            <a href="classes.php" class="nav-link <?= ($current_page === 'classes.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Classes</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="statistiques.php" class="nav-link <?= ($current_page === 'statistiques.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Statistiques</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="messages.php" class="nav-link <?= ($current_page === 'messages.php') ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <span class="badge">3</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="profil.php" class="nav-link <?= ($current_page === 'profil.php') ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i>
                <span>Profil</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../../shared/controllers/logout_controller.php" class="nav-link <?= ($current_page === 'logout.php') ? 'active' : '' ?>">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</nav>