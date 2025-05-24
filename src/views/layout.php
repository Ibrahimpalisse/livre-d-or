<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mon site' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
<body class="bg-light d-flex flex-column min-vh-100">
    <header class="bg-primary text-white mb-4">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand fw-bold" href="/home">Livre d'or</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/home">Accueil</a>
                        </li>
                        
                        <?php if ($is_authenticated): ?>
                            <?php if ($is_admin): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/dashboard">Dashboard</a>
                            </li>
                            <?php endif; ?>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['username']) ?> <span class="badge bg-secondary ms-1"><?= formatRole($user['role']) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <?php if ($user['role'] !== 'user'): ?>
                                        <li><div class="dropdown-item text-muted"><?= formatRole($user['role']) ?></div></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="/logout" onclick="return clearCache()">Déconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/register">Inscription</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/login">Connexion</a>
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
    <footer class="bg-primary text-white text-center py-3 mt-auto">
        <p>&copy; <?= date('Y') ?> - Livre d'or</p>
    </footer>
    
    <?php if (isset($_SERVER['REQUEST_URI'])): ?>
        <?php if (basename($_SERVER['REQUEST_URI']) === 'dashboard' || strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false): ?>
        <script src="/src/public/js/dashboard.js"></script>
        <script src="/src/public/js/dashboard-validate.js"></script>
        <?php endif; ?>
        
        <?php if (basename($_SERVER['REQUEST_URI']) === 'register' || strpos($_SERVER['REQUEST_URI'], '/register') !== false): ?>
        <script src="/src/public/js/register-validate.js"></script>
        <?php endif; ?>
        
        <?php if (basename($_SERVER['REQUEST_URI']) === 'login' || strpos($_SERVER['REQUEST_URI'], '/login') !== false): ?>
        <script src="/src/public/js/login-validate.js"></script>
        <?php endif; ?>
    <?php endif; ?>
    
    <script src="/src/public/js/regx.js"></script>
    <script src="/src/public/js/auth.js"></script>
</body>
</html>