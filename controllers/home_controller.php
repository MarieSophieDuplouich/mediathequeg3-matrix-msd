<?php
require_once MODEL_PATH . '/media_model.php';
require_once ROOT_PATH . '/includes/helpers.php';

// home page
function home_index() {
    $perPage = 20; // nb item par page
    $total   = media_count_dispo(null,null,null,true); // count total dispo
    [$page,$pages,$offset,$limit] = paginate($total, $perPage); // gestion pagination
    $items   = media_list_dispo($limit,$offset,null,null,null,true); // fetch media dispo

    load_view_with_layout('home/index', [
        'title'   => 'Accueil', // titre page
        'message' => 'Bienvenue sur la Médiathèque', // msg accueil
        'items'   => $items, // data affichage
        'page'    => $page, // current page
        'pages'   => $pages // nb total pages
    ]);
}

// À propos
function home_about() {
    load_view_with_layout('home/about', [
        'title'   => 'À propos',
        'content' => 'Cette application est un starter kit PHP MVC développé avec une approche procédurale.'
    ]);
}

// Contact
function home_contact() {
    $data = ['title' => 'Contact'];
    if (is_post()) {
        $name = clean_input(post('name'));
        $email = clean_input(post('email'));
        $message = clean_input(post('message'));

        if (empty($name) || empty($email) || empty($message)) {
            set_flash('error', 'Tous les champs sont obligatoires.');
        } elseif (!validate_email($email)) {
            set_flash('error', 'Adresse email invalide.');
        } else {
            set_flash('success', 'Votre message a été envoyé avec succès !');
            redirect('home/contact');
        }
    }
    load_view_with_layout('home/contact', $data);
}

// profile page
function home_profile() {
    require_login(); // user must be logged

    $userId = (int) ($_SESSION['user_id'] ?? 0); // get id user

    $active_loans = loans_list_active_by_user($userId); // emprunt actif
    $history      = loans_list_history_by_user($userId); // historique

    load_view_with_layout('home/profile', [
        'title' => 'Mon profil',
        'active_loans' => $active_loans, // list emprunt
        'history' => $history // list histo
    ]);
}

// return depuis profil
function home_return($loanId) {
    require_login(); // check user logged
    if (!is_post() || !verify_csrf_token(post('csrf_token'))) {
        set_flash('error', 'Action invalid.'); // fail check csrf
        redirect('home/profile');
        return;
    }

    try {
        loan_return_by_id((int)$_SESSION['user_id'], (int)$loanId); // retour prêt
        set_flash('success', 'Retour ok depuis profil.'); // success
    } catch (Exception $e) {
        set_flash('error', $e->getMessage()); // erreur catch
    }
    redirect('home/profile'); // redirect profile
}
