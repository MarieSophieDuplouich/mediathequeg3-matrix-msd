<?php
require_once MODEL_PATH . '/media_model.php';
require_once ROOT_PATH . '/includes/helpers.php';

// catalogue + search + filtres
function media_index() {
    $q         = trim(get('q','')); // search query
    $type      = trim(get('type','')); // filtre type
    $genre     = trim(get('genre','')); // filtre genre
    $availStr  = isset($_GET['available']) ? trim(get('available','')) : ''; // dispo raw
    $available = ($availStr === '') ? true : ($availStr === '1'); // default = only dispo

    $availView = ($availStr === '') ? '1' : $availStr; // pour view

    $perPage = 20; // nb item per page
    $total = media_count_dispo($q ?: null, $type ?: null, $genre ?: null, $available); // count total
    [$page,$pages,$offset,$limit] = paginate($total, $perPage); // paginate
    $items = media_list_dispo($limit, $offset, $q ?: null, $type ?: null, $genre ?: null, $available); // fetch list

    load_view_with_layout('media/index', [
        'title' => 'Catalogue',
        'items' => $items, // data list
        'page' => $page, // current page
        'pages' => $pages, // nb pages
        'q' => $q, // search value
        'type' => $type, // filtre type
        'genre' => $genre, // filtre genre
        'available' => $availView // dispo view
    ]);
}

// detail media
function media_detail($id) {
    if (!$item = media_get($id)) { load_404(); return; } // if not found -> 404

    $loan = null;
    if (is_logged_in()) {
        $loan = get_active_loan_for(current_user_id(), $id); // emprunt actif user
    }

    load_view_with_layout('media/detail', [
        'title' => $item['title'], // titre media
        'item' => $item, // data media
        'active_loan' => $loan // si prêt actif
    ]);
}

// emprunter media
function media_loan($id) {
    require_login(); // check login
    if (!is_post() || !verify_csrf_token(post('csrf_token'))) {
        set_flash('error','Action invalide.'); // csrf fail
        redirect('media/detail/'.$id); return;
    }
    try {
        $expected = media_borrow_item(current_user_id(), $id); // create emprunt
        $d = date('d/m/Y', strtotime($expected)); // format date retour
        set_flash('success', "Emprunt ok, retour $d."); // success msg
    } catch (Exception $e) {
        set_flash('error', $e->getMessage()); // catch err
    }
    redirect('media/detail/'.$id); // back detail
}

// retour media
function media_return($id) {
    require_login(); // check login
    if (!is_post() || !verify_csrf_token(post('csrf_token'))) {
        set_flash('error','Action invalide.'); // csrf fail
        redirect('media/detail/'.$id); return;
    }
    try {
        media_return_item(current_user_id(), $id); // retour ok
        set_flash('success','Retour enregistré.'); // msg ok
    } catch (Exception $e) {
        set_flash('error',$e->getMessage()); // err msg
    }
    redirect('media/detail/'.$id); // back detail
}
