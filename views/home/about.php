<div class="page-header">
    <div class="container">
        <h1><?php e($title); ?></h1>
    </div>
</div>

<section class="content">
    <div class="container">
        <div class="content-grid">
            <div class="content-main">
                <h2>À propos de cette application</h2>
                <p><?php e($content); ?></p>
                
                <h3>Architecture MVC</h3>
                <p>Cette application suit le pattern Model-View-Controller :</p>
                <ul>
                    <li><strong>Models</strong> : Gestion des données et logique métier</li>
                    <li><strong>Views</strong> : Présentation et interface utilisateur</li>
                    <li><strong>Controllers</strong> : Logique de contrôle et coordination</li>
                </ul>

                <h3>Fonctionnalités techniques</h3>
                <ul>
                    <li>PHP procédural (pas d'orienté objet)</li>
                    <li>Système de routing simple et efficace</li>
                    <li>Templating HTML/CSS avec layouts</li>
                    <li>Gestion de base de données avec PDO</li>
                    <li>Système d'authentification intégré</li>
                    <li>Protection CSRF</li>
                    <li>Messages flash</li>
                    <li>Validation de formulaires</li>
                </ul>

                <h3>Structure du projet</h3>
                <pre><code>php-starter-cdpi/
├── config/         # Configuration
├── controllers/    # Contrôleurs MVC
├── models/         # Modèles de données
├── views/          # Vues et templates
├── core/           # Système de routing et fonctions core
├── includes/       # Fonctions utilitaires
└── public/         # Point d'entrée et assets</code></pre>
            </div>
            
            <div class="sidebar">
                <div class="info-box">
                    <h4>Informations système</h4>
                    <p><strong>Version PHP :</strong> <?php e (phpversion()); ?></p>
                    <p><strong>Version app :</strong> <?php e (APP_VERSION); ?></p>
                    <p><strong>Base URL :</strong> <code><?php e (BASE_URL); ?></code></p>
                </div>
            </div>
        </div>
    </div>
</section> 