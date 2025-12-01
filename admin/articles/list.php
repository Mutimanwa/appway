<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/article-functions.php';

// Vérifier la session admin
// verifier_session(['admin']);

$page_title = "Gestion des Articles";

// Gérer les actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $result = deleteArticle($_GET['id']);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
            }
            break;
            
        case 'toggle_status':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $result = toggleArticleStatus($_GET['id'], $_GET['status']);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
            }
            break;
    }
}

// Récupérer les filtres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;

$filters = [];
if (isset($_GET['category']) && $_GET['category']) {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['status']) && $_GET['status']) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['author_id']) && $_GET['author_id']) {
    $filters['author_id'] = $_GET['author_id'];
}
if (isset($_GET['search']) && $_GET['search']) {
    $filters['search'] = $_GET['search'];
}

// Récupérer les articles
$data = getAllArticles($page, $per_page, $filters);
$articles = $data['articles'];
$total_pages = $data['total_pages'];

include '../includes/header.php';
?>

<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-article-line mr-2"></i>
                            Gestion des Articles
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line mr-2"></i>Nouvel Article
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-filter-line mr-2"></i>Filtres</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Catégorie</label>
                                    <select name="category" class="form-control">
                                        <option value="">Toutes les catégories</option>
                                        <option value="tech" <?php echo (isset($_GET['category']) && $_GET['category'] == 'tech') ? 'selected' : ''; ?>>Technologie</option>
                                        <option value="business" <?php echo (isset($_GET['category']) && $_GET['category'] == 'business') ? 'selected' : ''; ?>>Business</option>
                                        <option value="tutorial" <?php echo (isset($_GET['category']) && $_GET['category'] == 'tutorial') ? 'selected' : ''; ?>>Tutoriel</option>
                                        <option value="news" <?php echo (isset($_GET['category']) && $_GET['category'] == 'news') ? 'selected' : ''; ?>>Actualités</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="draft" <?php echo (isset($_GET['status']) && $_GET['status'] == 'draft') ? 'selected' : ''; ?>>Brouillon</option>
                                        <option value="published" <?php echo (isset($_GET['status']) && $_GET['status'] == 'published') ? 'selected' : ''; ?>>Publié</option>
                                        <option value="archived" <?php echo (isset($_GET['status']) && $_GET['status'] == 'archived') ? 'selected' : ''; ?>>Archivé</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Auteur</label>
                                    <?php
                                    $pdo = getConnexion();
                                    $authors = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role = 'admin' ORDER BY last_name")->fetchAll();
                                    ?>
                                    <select name="author_id" class="form-control">
                                        <option value="">Tous les auteurs</option>
                                        <?php foreach ($authors as $author): ?>
                                        <option value="<?php echo $author['id']; ?>" 
                                                <?php echo (isset($_GET['author_id']) && $_GET['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Recherche</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Titre, extrait..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-search-line"></i>
                                            </button>
                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="ri-refresh-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tableau des articles -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Vues</th>
                                    <th>Likes</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Aucun article trouvé</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td><?php echo $article['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                        <br><small class="text-muted">/<?php echo $article['slug']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?></td>
                                    <td>
                                        <?php 
                                        $categories = [
                                            'tech' => 'Technologie',
                                            'business' => 'Business',
                                            'tutorial' => 'Tutoriel',
                                            'news' => 'Actualités'
                                        ];
                                        echo $categories[$article['category']] ?? $article['category'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_badges = [
                                            'draft' => 'warning',
                                            'published' => 'success',
                                            'archived' => 'secondary'
                                        ];
                                        $status_labels = [
                                            'draft' => 'Brouillon',
                                            'published' => 'Publié',
                                            'archived' => 'Archivé'
                                        ];
                                        ?>
                                        <span class="badge badge-<?php echo $status_badges[$article['status']] ?? 'secondary'; ?>">
                                            <?php echo $status_labels[$article['status']] ?? $article['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $article['views'] ?? 0; ?></td>
                                    <td><?php echo $article['likes'] ?? 0; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="create.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info" title="Modifier">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <?php if ($article['status'] != 'published'): ?>
                                            <a href="list.php?action=toggle_status&id=<?php echo $article['id']; ?>&status=published" 
                                               class="btn btn-sm btn-success" title="Publier"
                                               onclick="return confirm('Publier cet article ?')">
                                                <i class="ri-send-plane-line"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="list.php?action=toggle_status&id=<?php echo $article['id']; ?>&status=draft" 
                                               class="btn btn-sm btn-warning" title="Mettre en brouillon"
                                               onclick="return confirm('Mettre cet article en brouillon ?')">
                                                <i class="ri-draft-line"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="list.php?action=delete&id=<?php echo $article['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Supprimer"
                                               onclick="return confirm('Supprimer définitivement cet article ?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['author_id']) ? '&author_id=' . $_GET['author_id'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">
                                    Précédent
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['author_id']) ? '&author_id=' . $_GET['author_id'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['author_id']) ? '&author_id=' . $_GET['author_id'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">
                                    Suivant
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <!-- Statistiques -->
                    <div class="row mt-4">
                        <?php 
                        $stats = getArticlesStats();
                        if (!empty($stats)): 
                        ?>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="ri-article-line fs-1"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                            <p class="mb-0">Total Articles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="ri-check-line fs-1"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['published']; ?></h3>
                                            <p class="mb-0">Articles Publiés</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="ri-eye-line fs-1"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_views']); ?></h3>
                                            <p class="mb-0">Total Vues</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="ri-heart-line fs-1"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_likes']); ?></h3>
                                            <p class="mb-0">Total Likes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>