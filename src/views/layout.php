<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Livre d\'or' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<?php
// Définition de la fonction formatRole avant son utilisation
if (!function_exists('formatRole')) {
    function formatRole($role) {
        switch ($role) {
            case 'super_admin': return 'Super Admin';
            case 'admin': return 'Admin';
            case 'disabled': return 'Désactivé';
            case 'user': return 'Utilisateur';
            default: return $role; // Au cas où il y a un rôle non prévu
        }
    }
}
?>
<body class="d-flex flex-column min-vh-100">
    <header class="site-header text-white mb-4">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="/home">
                    <i class="bi bi-book me-2"></i>Livre d'Or
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/home"><i class="bi bi-house-door me-1"></i>Accueil</a>
                        </li>
                        
                        <?php if ($is_authenticated): ?>
                            <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                        </li>
                            <?php endif; ?>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['username']) ?> 
                                    <span class="badge bg-accent ms-1"><?= formatRole($user['role']) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <?php if ($user['role'] !== 'user'): ?>
                                        <li><div class="dropdown-item text-muted"><?= formatRole($user['role']) ?></div></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="#" id="logoutLink"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/register"><i class="bi bi-person-plus me-1"></i>Inscription</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i>Connexion</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container flex-grow-1 my-5">
        <?= $content ?? '' ?>
    </main>
    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-1">&copy; <?= date('Y') ?> - Livre d'or</p>
            <p class="small mb-0 text-light-50">Partagez et découvrez des romans, manhwas et animés</p>
        </div>
    </footer>
    <!-- Modal pour confirmer la déconnexion -->
    <div class="modal fade" id="confirmLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la déconnexion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment vous déconnecter ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="/logout" class="btn btn-danger" id="confirmLogoutBtn">Se déconnecter</a>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_SERVER['REQUEST_URI'])): ?>
    <?php if (basename($_SERVER['REQUEST_URI']) === 'dashboard' || strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false): ?>
        <script src="/public/js/dashboard.js"></script>
        <script src="/public/js/dashboard-validate.js"></script>
    <?php endif; ?>
        
    <?php if (basename($_SERVER['REQUEST_URI']) === 'register' || strpos($_SERVER['REQUEST_URI'], '/register') !== false): ?>
        <script src="/public/js/register-validate.js"></script>
    <?php endif; ?>
        
    <?php if (basename($_SERVER['REQUEST_URI']) === 'login' || strpos($_SERVER['REQUEST_URI'], '/login') !== false): ?>
        <script src="/public/js/login-validate.js"></script>
    <?php endif; ?>
        
    <?php if (basename($_SERVER['REQUEST_URI']) === 'home' || $_SERVER['REQUEST_URI'] === '/' || strpos($_SERVER['REQUEST_URI'], '/home') !== false): ?>
        <script src="/public/js/home.js"></script>
    <?php endif; ?>
    <?php endif; ?>
    
    <script src="/public/js/regx.js"></script>
    <script src="/public/js/auth.js"></script>
    <script src="/public/js/layout.js"></script>
</body>
</html>