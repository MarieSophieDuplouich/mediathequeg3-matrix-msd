<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?php e($title);?></h1>
            <p>Connectez-vous à votre compte</p>
        </div>
        
        <form method="POST" class="auth-form" action="<?php e (url('auth/login')); ?>">
            <input type="hidden" name="csrf_token" value="<?php e (csrf_token()); ?>">
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php e(str_replace(["\r", "\n"], '', post('email', ''))); ?>"
                       placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Votre mot de passe">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Pas encore de compte ? 
                <a href="<?php e (url('auth/register'));?>">S'inscrire</a>
            </p>
        </div>
    </div>
</div> 