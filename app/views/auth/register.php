<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?php e($title); ?></h1>
            <p>Créez votre compte</p>
        </div>
        
        <form method="POST" class="auth-form" action="<?php e (url('auth/register')); ?>">
            <input type="hidden" name="csrf_token" value="<?php e (csrf_token()); ?>">
            
            <div class="form-group">
                <label for="name">Nom Complet</label>
                <input type="text" id="name" name="name" required value="<?php e(post('name','')); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php e ((post('email', ''))); ?>"
                       placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Au moins 8 caractères">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="Confirmez votre mot de passe">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i>
                S'inscrire
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Déjà un compte ? 
                <a href="<?php e (url('auth/login')); ?>">Se connecter</a>
            </p>
        </div>
    </div>
</div> 