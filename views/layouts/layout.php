<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' . e (APP_NAME) : e (APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php e (url('assets/css/style.css')); ?>">
    <!-- Framework désactivé pour le nouveau CSS -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> -->
</head>

<body>

    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="<?php e (url()); ?>"><?php e (APP_NAME); ?></a>
            </div>


            <ul class="nav-menu">
                <li><a href="<?php e (url()); ?>">Accueil</a></li>
                <li><a href="<?php e (url('home/about')); ?>">À propos</a></li>
                <li><a href="<?php e (url('home/profile')); ?>">Mon Profil</a></li>
                <li><a href="<?php e (url('home/contact')); ?>">Contact</a></li>
                <?php if (is_logged_in()): ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php e (url('admin/dashboard')); ?>">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?php e (url('auth/logout')); ?>">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="<?php e (url('auth/login')); ?>">Connexion</a></li>
                    <li><a href="<?php e (url('auth/register')); ?>">Inscription</a></li>
                    <!-- <li><a href="<?php e (url('auth/forgot-password')); ?>">Mot de passe oublié</a></li> -->
                <?php endif; ?>
            </ul>

        </nav>
    </header>

<?php 
    $currentRoute = $_GET['url'] ?? ''; 

    if ($currentRoute === '' || $currentRoute === 'profil') : ?>
        <div class="search-bar">
            <form action="<?= e(url('media')) ?>" method="get">
                <input type="text" name="q" value="<?= e($q ?? '') ?>" placeholder="Titre/type/genre/auteur…">
                <button class="btn">Rechercher</button>
            </form>
        </div>
<?php endif; ?>

    <main class="main-content">
        <?php flash_messages(); ?>
        <?php echo $content ?? ''; ?>
    </main>
    <!--MSDuplouich section pour le cursor Matrix -->
         <section>
        <p class="text">
        </p>
    </section>
    <!--MSDuplouich section pour le cursor Matrix fin -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?php e (date('Y')); ?> <?php e (APP_NAME); ?>. Tous droits réservés.</p>
            <p>Version <?php e (APP_VERSION); ?></p>
        </div>
    </footer>

    <script src="<?php e (url('assets/js/app.js')); ?>"></script>
         <!-- script pour cursor Matrix -->
    <script>
        let paragraph = document.querySelector('.text');
        let text = 'Médiathèque G3'.repeat(300);
        paragraph.textContent = text;
        paragraph.innerHTML = paragraph.textContent.replace(/\S/g, "<span>$&</span>")
    </script>
</body>

</html>

