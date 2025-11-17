<?php
// -------------------------------------------------------------
// Fonction de connexion utilisateur
function auth_login()
{
    // Si déjà connecté, on redirige vers la page d'accueil
    if (is_logged_in()) {
        redirect('home');
    }

    // 2. Génère le token CSRF pour le formulaire (GET)
    csrf_token();

    // 3. Traitement uniquement en POST
    if (is_post()) {

        // 3-a. Vérification stricte du jeton CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Jeton CSRF invalide.');
        }
    }

    // Données à envoyer à la vue
    $data = ['title' => 'Connexion'];

    // Si le formulaire est soumis
    if (is_post()) {
        // On récupère et nettoie les champs
        $email = clean_input(post('email'));
        $password = post('password');

        // Vérification des champs obligatoires
        if (empty($email) || empty($password)) {
            set_flash('error', 'Email et mot de passe obligatoires.');
        } else {
            // Vérifie si l'utilisateur existe
            $user = get_user_by_email($email);
            // Vérifie le mot de passe
            if ($user && verify_password($password, $user['password'])) {
                // Stocke les infos de session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                set_flash('success', 'Connexion réussie !');
                redirect('home');
            } else {
                set_flash('error', 'Email ou mot de passe incorrect.');
            }
        }
    }

    // Affiche la vue de connexion
    load_view_with_layout('auth/login', $data);
}

// -------------------------------------------------------------
// Fonction d'inscription utilisateur
function auth_register()
{
    // Si déjà connecté, on redirige vers l'accueil
    if (is_logged_in()) {
        redirect('home');
    }

    // 2. Génère le token CSRF pour le formulaire (GET)
    csrf_token();

    // 3. Traitement uniquement en POST
    if (is_post()) {

        // 3-a. Vérification stricte du jeton CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Jeton CSRF invalide.');
        }
    }
    
    // Données à envoyer à la vue
    $data = ['title' => 'Inscription'];
    // Si le formulaire est soumis
    if (is_post()) {
        // On récupère et nettoie les champs
        $name = clean_input(post('name'));
        $email = clean_input(post('email'));
        $password = post('password');
        $confirm = post('confirm_password');

        // Vérification des champs obligatoires
        if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
            set_flash('error', 'Tous les champs sont obligatoires.');
            // Règles sur le champ nom
        } elseif (strlen(trim($name)) < 2 || strlen(trim($name)) > 50) {
            set_flash('error', 'Le nom doit faire au minimum 2 et au maximum 50 caractères.');
        } elseif (!preg_match('/^[A-Z][a-z- ]*$/', $name)) {
            set_flash('error', 'Le nom doit avoir la première lettre en majuscule et ne doit contenir que des lettres, espaces et tirets.');
            // Règles sur l'email
        } elseif (strlen(trim($email)) > 100) {
            set_flash('error', "L'email ne peut contenir que 100 caractères maximum.");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', "Format d'email invalide.");
        } elseif (email_exists($email)) {
            // Vérification unicité email avant création
            set_flash('error', "L'email est déjà utilisé par un autre compte.");
            // Règles sur le mot de passe
        } elseif (strlen(trim($password)) < 8) {
            set_flash('error', 'Le mot de passe doit contenir au minimum 8 caractères.');
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            set_flash('error', 'Le mot de passe doit contenir au moins 1 lettre minuscule, 1 lettre majuscule et 1 chiffre.');
        } elseif ($password !== $confirm) {
            set_flash('error', 'Les mots de passe ne correspondent pas.');
        } else {
            // Crée l'utilisateur en base
            $user_id = create_user($name, $email, $password);
            if ($user_id) {
                set_flash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                redirect('auth/login');
            } else {
                set_flash('error', "Erreur lors de l'inscription.");
            }
        }
    }
    // Affiche la vue d'inscription
    load_view_with_layout('auth/register', $data);
}

// -------------------------------------------------------------
// Fonction de déconnexion utilisateur
function auth_logout()
{
    logout(); // Détruit la session et redirige
}