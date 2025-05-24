<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg">
            <div class="card-header py-3 text-center">
                <h2 class="h3 mb-0">Rejoignez-nous</h2>
            </div>
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus display-3 text-primary mb-3"></i>
                    <h3 class="mb-1">Inscription</h3>
                    <p class="text-muted">Créez votre compte pour partager vos lectures</p>
                </div>
                
                <form id="registerForm" action="/register" method="post" autocomplete="off">
                    <?php use Core\Security; echo Security::csrfField(); ?>
                    <div class="mb-4">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Choisissez un nom d'utilisateur" required>
                        </div>
                        <div class="invalid-feedback" id="usernameError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Créez un mot de passe sécurisé" required autocomplete="off" oncopy="return false" onpaste="return false" oncut="return false">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" tabindex="-1">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control border-start-0" id="password_confirm" name="password_confirm" placeholder="Confirmez votre mot de passe" required autocomplete="off" oncopy="return false" onpaste="return false" oncut="return false">
                        </div>
                        <div class="invalid-feedback" id="password_confirmError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-person-plus me-2"></i>Créer mon compte
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <p class="mb-0">Déjà inscrit ? <a href="/login" class="text-decoration-none">Connectez-vous</a></p>
                </div>
            </div>
        </div>
    </div>
</div> 