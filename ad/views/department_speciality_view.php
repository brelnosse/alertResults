<?php
/**
 * Vue pour la gestion des associations département-spécialité
 * 
 * Cette page permet aux administrateurs de gérer les associations entre
 * les départements et les spécialités.
 * 
 * @package     AlertResults
 * @subpackage  Views
 * @category    Administration
 * @author      v0
 * @version     1.0
 */

// Vérifier si l'utilisateur est connecté et est un administrateur
require_once __DIR__ . '/../../shared/controllers/auth_controller.php';
requireLogin();
requireRole('admin');

// Inclure l'en-tête
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Associations Département-Spécialité</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAssociationModal">
                        <i class="fas fa-plus"></i> Ajouter une association
                    </button>
                </div>
            </div>
            
            <!-- Affichage des messages d'erreur ou de succès -->
            <?php if (isset($errorHandler) && $errorHandler->hasErrors()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errorHandler->getAllErrors() as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errorHandler) && $errorHandler->hasSuccess()): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($errorHandler->getSuccessMessage()); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total des associations</h5>
                            <p class="card-text display-6"><?php echo $stats['total_associations']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Départements avec spécialités</h5>
                            <p class="card-text display-6"><?php echo $stats['departments_with_specialities']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Spécialités avec départements</h5>
                            <p class="card-text display-6"><?php echo $stats['specialities_with_departments']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Département le plus populaire</h5>
                            <p class="card-text">
                                <?php if ($stats['most_popular_department']): ?>
                                    <?php echo htmlspecialchars($stats['most_popular_department']['nom']); ?>
                                    <span class="badge bg-light text-dark"><?php echo $stats['most_popular_department']['count']; ?> spécialités</span>
                                <?php else: ?>
                                    Aucun
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tableau des associations -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Liste des associations département-spécialité
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="associationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Département</th>
                                    <th>Spécialité</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($associations as $association): ?>
                                    <tr>
                                        <td><?php echo $association['id']; ?></td>
                                        <td><?php echo htmlspecialchars($association['departement_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($association['specialite_nom']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($association['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAssociationModal<?php echo $association['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <!-- Modal de suppression -->
                                            <div class="modal fade" id="deleteAssociationModal<?php echo $association['id']; ?>" tabindex="-1" aria-labelledby="deleteAssociationModalLabel<?php echo $association['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteAssociationModalLabel<?php echo $association['id']; ?>">Confirmer la suppression</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Êtes-vous sûr de vouloir supprimer l'association entre le département <strong><?php echo htmlspecialchars($association['departement_nom']); ?></strong> et la spécialité <strong><?php echo htmlspecialchars($association['specialite_nom']); ?></strong> ?</p>
                                                            <p class="text-danger">Attention : Cette action est irréversible et ne sera possible que si aucun étudiant ou enseignant n'utilise cette association.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <form action="index.php?page=department_specialities&action=delete" method="post">
                                                                <input type="hidden" name="id" value="<?php echo $association['id']; ?>">
                                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal d'ajout d'association -->
<div class="modal fade" id="addAssociationModal" tabindex="-1" aria-labelledby="addAssociationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssociationModalLabel">Ajouter une association département-spécialité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=department_specialities&action=add" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="departement_id" class="form-label">Département</label>
                        <select class="form-select" id="departement_id" name="departement_id" required>
                            <option value="">Sélectionner un département</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>"><?php echo htmlspecialchars($department['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="specialite_id" class="form-label">Spécialité</label>
                        <select class="form-select" id="specialite_id" name="specialite_id" required>
                            <option value="">Sélectionner une spécialité</option>
                            <?php foreach ($specialities as $speciality): ?>
                                <option value="<?php echo $speciality['id']; ?>"><?php echo htmlspecialchars($speciality['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de DataTables
        $('#associationsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json'
            },
            order: [[0, 'desc']]
        });
    });
</script>

<?php
// Inclure le pied de page
include __DIR__ . '/../includes/footer.php';
?>
