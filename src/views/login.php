<div class="card mx-auto shadow" style="max-width: 600px;">
    <div class="card-body">
        <h2 class="card-title text-center mb-4">Connexion</h2>
        <form id="loginForm" action="/login" method="post" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
                <div class="invalid-feedback" id="usernameError"></div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback" id="passwordError"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <div class="mt-3 text-center">
            <p>Pas encore de compte ? <a href="/register">Inscrivez-vous</a></p>
        </div>
    </div>
</div> 