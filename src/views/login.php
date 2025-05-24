<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg">
            <div class="card-header py-3 text-center">
                <h2 class="h3 mb-0">Bienvenue</h2>
            </div>
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-book-half display-3 text-primary mb-3"></i>
                    <h3 class="mb-1">Connexion</h3>
                    <p class="text-muted">Accédez à votre collection de lectures</p>
                </div>
                
                <form id="loginForm" action="/login" method="post" autocomplete="off">
                    <?php use Core\Security; echo Security::csrfField(); ?>
                    <div class="mb-4">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required>
                        </div>
                        <div class="invalid-feedback" id="usernameError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label">Mot de passe</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" tabindex="-1">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <p class="mb-0">Pas encore de compte ? <a href="/register" class="text-decoration-none">Inscrivez-vous</a></p>
                </div>
            </div>
        </div>
    </div>
</div> 