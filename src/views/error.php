<div class="text-center mt-5">
    <div class="alert alert-danger" role="alert">
        <h2 class="alert-heading"><?= $title ?? 'Erreur' ?></h2>
        <p class="mb-0"><?= $message ?? 'Une erreur est survenue.' ?></p>
    </div>
    <div class="mt-4">
        <a href="/home" class="btn btn-primary">Retourner à l'accueil</a>
        <?php if (!$is_authenticated): ?>
        <a href="/login" class="btn btn-outline-secondary ms-2">Se connecter</a>
        <?php else: ?>
        <a href="/logout" class="btn btn-outline-secondary ms-2">Se déconnecter</a>
        <?php endif; ?>
    </div>
</div> 