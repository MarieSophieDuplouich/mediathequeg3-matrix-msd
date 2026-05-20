<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' . e(APP_NAME) : e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php e(url('assets/css/style.css')); ?>">

</head>

<body>


    <header class="header">

        <div class="header-animation">
            <p class="header-text"></p>
        </div>

        <nav class="navbar">
            <ul class="nav-menu">
                <li style="--i:9;"><a href="<?php e(url()); ?>"><?php e(APP_NAME); ?></a></li>
                <li style="--i:8;"><a href="<?php e(url()); ?>">Accueil</a></li>
                <li style="--i:7;"><a href="<?php e(url('home/about')); ?>">À propos</a></li>
                <li style="--i:6;"><a href="<?php e(url('home/profile')); ?>">Mon Profil</a></li>
                <li style="--i:5;"><a href="<?php e(url('home/contact')); ?>">Contact</a></li>
                <?php if (is_logged_in()): ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li style="--i:4;"><a href="<?php e(url('admin/dashboard')); ?>">Dashboard</a></li>
                    <?php endif; ?>
                    <li style="--i:3;"><a href="<?php e(url('auth/logout')); ?>">Déconnexion</a></li>
                <?php else: ?>
                    <li style="--i:2;"><a href="<?php e(url('auth/login')); ?>">Connexion</a></li>
                    <li style="--i:1;"><a href="<?php e(url('auth/register')); ?>">Inscription</a></li>
                    <!-- <li><a href="<?php e(url('auth/forgot-password')); ?>">Mot de passe oublié</a></li> -->
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

    <!-- MS animation du curseur ici et en Javascript -->
    <section>
        <p class="text">
        </p>
    </section>
    <!-- MS animation du curseur ici et en Javascript fin -->


    <footer class="footer">

        <div class="footer-content">
            <p>&copy; <?php e(date('Y')); ?> <?php e(APP_NAME); ?>. Tous droits réservés.</p>
            <p>Version <?php e(APP_VERSION); ?></p>
        </div>
    </footer>

    <script src="<?php e(url('assets/js/app.js')); ?>"></script>
    <!-- MS animation du curseur ici et en Javascript -->
    <script>
        let paragraph = document.querySelector('.text');
        let text = 'Médiathèque G3'.repeat(300);

        // MS animation du curseur ici et en Javascript
        text.split('').forEach(char => {
            if (char === ' ') {
                paragraph.appendChild(document.createTextNode(' '));
            } else {
                let span = document.createElement('span');
                span.textContent = char;
                paragraph.appendChild(span);
            }
        });

        // Animation Matrix dans le header
        let headerParagraph = document.querySelector('.header-text');
        let headerString = 'Médiathèque G3'.repeat(300);

        headerString.split('').forEach(char => {
            if (char === ' ') {
                headerParagraph.appendChild(document.createTextNode(' '));
            } else {
                let span = document.createElement('span');
                span.textContent = char;
                headerParagraph.appendChild(span);
            }
        });
    </script>


    <!-- MS animation du curseur ici et en Javascript fin-->
</body>

</html>