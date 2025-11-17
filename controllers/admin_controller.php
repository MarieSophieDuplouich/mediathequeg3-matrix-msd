<?php

// ---------- INCLUSIONS DE FICHIERS EXTERNES ----------

// Charge les fonctions utilitaires globales (ex: is_post, set_flash, redirect, etc.)
require_once ROOT_PATH . '/includes/helpers.php';

// Charge les fonctions métier liées aux médias (ex: create_media, update_media, etc.)
require_once MODEL_PATH . '/media_model.php';

// Charge les fonctions métier liées à l’administration (ex: admin_dashboard_model, delete_media, etc.)
require_once MODEL_PATH . '/admin_model.php';


// ---------- FONCTION PRINCIPALE DU DASHBOARD ADMIN ----------

/**
 * Affiche le dashboard administrateur.
 * Appelle la fonction du modèle qui prépare les données nécessaires (statistiques, médias, emprunts, etc.) et charge la vue correspondante.
 */
function admin_dashboard()
{
    // Le modèle gère toute la logique de récupération des données
    admin_dashboard_model();
}


// ---------- FONCTION DE NORMALISATION DES GENRES ----------

/**
 * Normalise les genres dans un tableau de données selon le type de média.
 * Cela évite d’avoir des genres incohérents pour un type donné (ex: genre_livre sur un film).
 * @param array $data Tableau des données du média (typiquement $_POST)
 * @param int $type_id Identifiant du type de média (1=livre, 2=film, 3=jeu)
 */
function normalize_genres_for_type_id(array &$data, int $type_id): void
{
    // On vide les genres non concernés par le type
    switch ((int) $type_id) {
        case 1: // Livre
            $data['genre_movies'] = null; // Un livre n’a pas de genre film
            $data['genre_games'] = null;  // Un livre n’a pas de genre jeu
            $data['genre_books'] = $data['genre_books'] ?? null; // On conserve le genre livre
            break;
        case 2: // Film
            $data['genre_books'] = null; // Un film n’a pas de genre livre
            $data['genre_games'] = null; // Un film n’a pas de genre jeu
            $data['genre_movies'] = $data['genre_movies'] ?? null; // On conserve le genre film
            break;
        case 3: // Jeu
            $data['genre_books'] = null; // Un jeu n’a pas de genre livre
            $data['genre_movies'] = null; // Un jeu n’a pas de genre film
            $data['genre_games'] = $data['genre_games'] ?? null; // On conserve le genre jeu
            break;
        default:
            // Si type inconnu, on vide tout
            $data['genre_books'] = $data['genre_movies'] = $data['genre_games'] = null;
    }
}


// ---------- FONCTION D’AJOUT D’UN MÉDIA (FORMULAIRE) ----------

/**
 * Gère l’ajout d’un nouveau média dans la base.
 * - Valide les données du formulaire
 * - Gère l’upload de la couverture si présente
 * - Ajoute le média et ses détails en base
 * - Affiche les messages de succès ou d’erreur
 */

function admin_add()
{
    // 1. Accès réservé aux administrateurs
    require_admin();

    // 2. Génère le token CSRF pour le formulaire (GET)
    csrf_token();

    // 3. Traitement uniquement en POST
    if (is_post()) {

        // 3-a. Vérification stricte du jeton CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Jeton CSRF invalide.');
        }

        $type_id = isset($_POST['type_id']) ? (int) $_POST['type_id'] : 0;
        if ($type_id <= 0) {
            set_flash('error', 'Type invalide.'); // Message en cas d’erreur
            redirect('admin/add'); // Redirection vers le formulaire
        }
        $_POST['type_id'] = $type_id;

        // 2. Conversion des champs numériques (année, pages, stock, durée)
        $_POST['year'] = ($_POST['year'] === '' ? null : (int) $_POST['year']);    // Année : null si vide, sinon entier
        $_POST['pages'] = ($_POST['pages'] === '' ? null : (int) $_POST['pages']);   // Pages : null si vide, sinon entier
        $_POST['stock'] = ($_POST['stock'] === '' ? 1 : (int) $_POST['stock']);   // Stock : 1 par défaut ou entier
        $_POST['duration'] = ($_POST['duration'] === '' ? null : (int) $_POST['duration']); // Durée : null si vide, sinon entier

        // 3. Nettoyage des autres champs selon le type de média
        // On évite de remplir la base avec des champs non pertinents
        switch ($type_id) {
            case 2: // Film
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null; // Un film n’a pas d’auteur ni d’ISBN ni de pages
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null; // Un film n’a pas de plateforme ni d’âge minimum ni d’éditeur
                break;
            case 1: // Livre
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null; // Un livre n’a pas de durée, de réalisateur ni de classification
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null; // Un livre n’a pas de plateforme, d’âge min, ni d’éditeur
                break;
            case 3: // Jeu
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null; // Un jeu n’a pas de durée, de réalisateur, ni de classification
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null; // Un jeu n’a pas d’auteur, ISBN, pages
                break;
            default: // Type inconnu, on vide tout ce qui peut être incohérent
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null;
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null;
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null;
        }

        // 4. Normalisation des genres (livre, film, jeu)
        normalize_genres_for_type_id($_POST, $type_id);

        // 5. Validation du formulaire (voir fonction plus bas)
        $errors = validate_media_form($_POST, $_FILES, $type_id);

        // 6. Si aucune erreur, gestion de l’upload de la couverture
        if (empty($errors)) {
            // Si une image a été envoyée et sans erreur PHP
            if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                try {
                    $coverPath = upload_cover_image($_FILES['cover']); // Upload sur le serveur, retourne le nom du fichier
                    $fullPath = ROOT_PATH . '/public/uploads/covers/' . $coverPath; // Chemin complet du fichier sur le serveur
                    resize_image($fullPath, $fullPath, 300, 400); // Redimensionne et écrase le fichier à 300x400px
                    $_POST['cover'] = $coverPath; // Stocke uniquement le nom en base
                } catch (Exception $e) {
                    set_flash('error', $e->getMessage()); // Message en cas d’erreur d’upload
                    redirect('admin/add'); // Retour au formulaire en cas d’erreur
                }
            } else {
                // Si pas d’image envoyée, on utilise l’image par défaut
                $_POST['cover'] = 'uploads/covers/default.jpg'; // Nom de l’image par défaut stocké en base
            }

            // 7. Ajout du média en base (fonction du modèle)
            create_media($_POST); // Ajoute le média avec toutes les infos dans la base
            set_flash('success', 'Média ajouté avec succès.'); // Message succès
            redirect('admin/dashboard'); // Redirection vers le dashboard
        } else {
            // Affichage des erreurs, puis rechargement du formulaire
            set_flash('error', implode('<br>', $errors)); // Transforme le tableau d’erreurs en chaîne HTML
            redirect('admin/add'); // Recharge le formulaire
        }
    }

    // Si GET, on charge la liste des types pour le formulaire d’ajout
    $types = admin_type_list_for_view(); // Récupère la liste des types pour la vue
    load_view_with_layout('admin/form', [
        'title' => 'Ajouter un média',
        'types' => $types
    ]);
}


// ---------- FONCTION DE MODIFICATION D’UN MÉDIA (FORMULAIRE) ----------

/**
 * Modifie un média existant.
 * Valide les données, gère la nouvelle couverture si présente ou conserve l’ancienne.
 */
function admin_edit($id)
{
    // 1. Accès réservé aux administrateurs
    require_admin();

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

    // Récupère le média à éditer pour pré-remplir le formulaire
    $media = media_get($id); // Charge le média depuis la base
    if (!$media) {
        set_flash('error', 'Média non trouvé.');
        redirect('admin/dashboard');
    }

    if (is_post()) {
        // Récupération et typage du type
        $type_id = isset($_POST['type_id']) ? (int) $_POST['type_id'] : 0;
        if ($type_id <= 0) {
            set_flash('error', 'Type invalide.');
            redirect('admin/edit/' . $id);
        }
        $_POST['type_id'] = $type_id;

        // Conversion numérique des champs
        $_POST['year'] = ($_POST['year'] === '' ? null : (int) $_POST['year']);    // Année : null si vide, sinon entier
        $_POST['pages'] = ($_POST['pages'] === '' ? null : (int) $_POST['pages']);   // Pages : null si vide, sinon entier
        $_POST['stock'] = ($_POST['stock'] === '' ? 1 : (int) $_POST['stock']);   // Stock : 1 par défaut ou entier
        $_POST['duration'] = ($_POST['duration'] === '' ? null : (int) $_POST['duration']); // Durée : null si vide, sinon entier

        // Nettoyage selon le type
        switch ($type_id) {
            case 2: // Film
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null; // Un film n’a pas d’auteur ni d’ISBN ni de pages
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null; // Un film n’a pas de plateforme, d’âge min, ni d’éditeur
                break;
            case 1: // Livre
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null; // Un livre n’a pas de durée, de réalisateur ni de classification
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null; // Un livre n’a pas de plateforme, d’âge min, ni d’éditeur
                break;
            case 3: // Jeu
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null; // Un jeu n’a pas de durée, de réalisateur, ni de classification
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null; // Un jeu n’a pas d’auteur, ISBN, pages
                break;
            default:
                $_POST['duration'] = $_POST['director'] = $_POST['classification'] = null;
                $_POST['isbn'] = $_POST['pages'] = $_POST['author'] = null;
                $_POST['plateform'] = $_POST['min_age'] = $_POST['publisher'] = null;
        }

        normalize_genres_for_type_id($_POST, $type_id); // Normalise les genres selon le type

        $errors = validate_media_form($_POST, $_FILES, $type_id); // Valide les champs

        if (empty($errors)) {
            // Gestion de la couverture : conserve l’ancienne si pas de nouvelle
            $cover = $_POST['old_cover'] ?? $media['cover']; // Garde la couverture existante si pas d’upload
            if (!empty($_FILES['cover']['name'])) {
                try {
                    $coverPath = upload_cover_image($_FILES['cover']); // Upload sur le serveur, retourne le nom du fichier
                    $fullPath = ROOT_PATH . '/public/uploads/covers/' . $coverPath; // Chemin complet sur le serveur
                    resize_image($fullPath, $fullPath, 300, 400); // Redimensionne et écrase le fichier à 300x400px
                    $cover = $coverPath; // Stocke uniquement le nom en base
                } catch (Exception $e) {
                    set_flash('error', $e->getMessage());
                    redirect('admin/edit/' . $id);
                }
            }
            $_POST['cover'] = $cover; // Mise à jour de la couverture en base

            // Mise à jour du média en base
            update_media($id, $_POST); // Met à jour le média dans la base
            set_flash('success', 'Média modifié avec succès.');
            redirect('admin/dashboard');
        } else {
            set_flash('error', implode('<br>', $errors));
            redirect('admin/edit/' . $id);
        }
    }

    // GET : charge la liste des types et les données du média pour le formulaire d’édition
    $types = admin_type_list_for_view(); // Récupère la liste des types pour la vue
    load_view_with_layout('admin/form', [
        'title' => 'Modifier un média',
        'media' => $media,
        'types' => $types
    ]);
}


// ---------- FONCTION DE SUPPRESSION D’UN MÉDIA ----------

/**
 * Supprime un média (après vérification) :
 * - Vérifie que le média existe
 * - Supprime l’image du serveur si besoin
 * - Supprime en base
 */
function admin_delete($id)
{
    // 1. Accès réservé aux administrateurs
    require_admin();

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

    // Récupère le média pour vérifier qu’il existe
    $media = media_get($id); // Récupère le média depuis la base
    if (!$media) {
        set_flash('error', 'Média non trouvé.');
        redirect('admin/dashboard');
    }

    // Suppression et gestion du message (la fonction delete_media gère aussi le blocage si emprunt en cours)
    if (delete_media($id)) {
        set_flash('success', 'Média supprimé.'); // Message succès après suppression
    }
    // Si delete_media échoue, le message d’erreur est déjà géré dans le modèle
    redirect('admin/dashboard'); // Redirection vers le dashboard
}


// ---------- FONCTION DE RETOUR D’UN EMPRUNT ----------

/**
 * Enregistre le retour d’un emprunt (actual_return_date).
 * - Vérifie que l’emprunt existe et n’est pas déjà retourné
 * - Met à jour la date de retour et le stock
 */

function admin_delete_image($id)
{
    // 1. Accès réservé aux administrateurs
    require_admin();

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

    // Récupère le média et le nom de la couverture
    $media = db_select_one("SELECT cover FROM medias WHERE id = ?", [$id]);
    $coverName = $media['cover'] ?? 'default.jpg';
    $coverPath = ROOT_PATH . '/public/uploads/covers/' . $coverName;

    // Supprime le fichier si ce n'est pas l'image par défaut
    if ($coverName !== 'default.jpg' && file_exists($coverPath)) {
        unlink($coverPath);
    }

    // Mets default.jpg en base
    db_execute("UPDATE medias SET cover = 'default.jpg' WHERE id = ?", [$id]);
    set_flash('success', "Image supprimée ou remplacée par l'image par défaut.");
    redirect('admin/dashboard');
}
function admin_retour($id)
{
    // 1. Accès réservé aux administrateurs
    require_admin();

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

    $loan = db_select_one("SELECT * FROM loans WHERE id = ?", [$id]); // Récupère l’emprunt concerné
    if (!$loan || $loan['actual_return_date']) {
        set_flash('error', 'Emprunt introuvable ou déjà retourné.');
        redirect('admin/dashboard');
    }
    // Met à jour l’emprunt : actual_return_date + stock
    return_loan($id); // Met à jour la date de retour effective + le stock
    set_flash('success', 'Retour enregistré.');
    redirect('admin/dashboard');
}


// ---------- FONCTION DE VALIDATION DU FORMULAIRE MÉDIA ----------

/**
 * Valide toutes les données du formulaire média.
 * Retourne un tableau d’erreurs (vide si aucune erreur).
 * @param array $data Données du formulaire (typiquement $_POST)
 * @param array $files Tableau des fichiers uploadés ($_FILES)
 * @param int $type_id Type du média
 * @return array Tableau des messages d’erreur
 */
function validate_media_form($data, $files, $type_id)
{
    require_admin(); // Sécurité : validation admin
    $errors = [];

    // Titre : obligatoire, max 200 caractères
    if (empty($data['title']) || strlen($data['title']) > 200) {
        $errors[] = "Titre invalide (1-200 caractères).";
    }

    // Type : obligatoire et doit exister en base
    if (empty($data['type_id']) || !is_numeric($data['type_id'])) {
        $errors[] = "Type invalide.";
    } else {
        $exists = db_select_one("SELECT COUNT(*) AS c FROM type WHERE id = ?", [(int) $data['type_id']]);
        if (!$exists || (int) $exists['c'] === 0) {
            $errors[] = "Type inexistant.";
        }
    }

    // Stock : minimum 1
    if (!isset($data['stock']) || (int) $data['stock'] < 1) {
        $errors[] = "Stock invalide (minimum 1).";
    }

    // Image de couverture : format, taille, dimensions
    if (!empty($files['cover']['name'])) {
        $allowed = ['jpg', 'png', 'gif'];
        $ext = strtolower(pathinfo($files['cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Image invalide (JPG, PNG, GIF).";
        }
        if ($files['cover']['size'] > 2097152) {
            $errors[] = "Image trop volumineuse (max 2 Mo).";
        }
        if (!empty($files['cover']['tmp_name']) && $files['cover']['size'] <= 2097152) {
            $image_info = @getimagesize($files['cover']['tmp_name']);
            if ($image_info && ($image_info[0] < 100 || $image_info[1] < 100)) {
                $errors[] = "Les dimensions de l'image doivent être au moins 100x100 px.";
            }
        }
        if (empty($files['cover']['tmp_name'])) {
            $errors[] = "L'image n'a pas pu être uploadée (fichier trop volumineux ou erreur serveur).";
        }
    }

    // Vérifications spécifiques selon le type de média
    switch ((int) $type_id) {
        case 1: // Livre
            if (empty($data['genre_books'])) {
                $errors[] = "Veuillez choisir un genre (Livre).";
            }
            if (empty($data['isbn']) || !preg_match('/^(\d{10}|\d{13})$/', $data['isbn'])) {
                $errors[] = "ISBN invalide (10 ou 13 chiffres).";
            }
            if ((int) ($data['pages'] ?? 0) < 1 || (int) $data['pages'] > 9999) {
                $errors[] = "Pages invalides (1-9999).";
            }
            $year = (int) ($data['year'] ?? 0);
            if ($year && ($year < 1450 || $year > (int) date('Y'))) {
                $errors[] = "Année invalide (1450-" . date('Y') . ").";
            }
            break;

        case 2: // Film
            if (empty($data['genre_movies'])) {
                $errors[] = "Veuillez choisir un genre (Film).";
            }
            if (empty($data['duration']) || (int) $data['duration'] < 1 || (int) $data['duration'] > 999) {
                $errors[] = "Durée invalide (1-999 min).";
            }
            if (empty($data['classification']) || !in_array($data['classification'], ['Tous publics', '-12', '-16', '-18'])) {
                $errors[] = "Classification invalide.";
            }
            break;

        case 3: // Jeu
            if (empty($data['genre_games'])) {
                $errors[] = "Veuillez choisir un genre (Jeu).";
            }
            if (empty($data['plateform']) || !in_array($data['plateform'], ['PC', 'PlayStation', 'Xbox', 'Nintendo', 'Mobile'])) {
                $errors[] = "Plateforme invalide.";
            }
            if (empty($data['min_age']) || !in_array((string) $data['min_age'], ['3', '7', '12', '16', '18'])) {
                $errors[] = "Âge minimum invalide.";
            }
            break;
    }

    return $errors;
}


// ---------- FONCTION D’UPLOAD ET REDIMENSIONNEMENT DE COUVERTURE ----------

/**
 * Upload et redimensionne une image de couverture.
 * Retourne le nom du fichier stocké.
 * @param array $file Tableau PHP $_FILES['cover']
 * @return string Nom du fichier stocké (ex: cover_abc123.jpg)
 * @throws Exception En cas d’erreur d’upload ou de resize
 */
function upload_cover_image($file)
{
    require_admin(); // Sécurité admin

    $maxSize = 2 * 1024 * 1024; // 2Mo max
    $allowedExtensions = ['jpg', 'png', 'gif']; // Extensions autorisées
    $uploadDir = ROOT_PATH . '/public/uploads/covers/'; // Dossier cible sur le serveur

    // Vérifie les erreurs d'upload PHP
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille autorisée par le serveur.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier est trop volumineux.",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement uploadé.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été envoyé.",
            UPLOAD_ERR_NO_TMP_DIR => "Répertoire temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque.",
            UPLOAD_ERR_EXTENSION => "Upload stoppé par une extension PHP.",
        ];
        $errCode = $file['error'];
        throw new Exception($errors[$errCode] ?? "Erreur inconnue lors de l'upload.");
    }

    // Vérifie la taille du fichier
    if ($file['size'] > $maxSize) {
        throw new Exception("Le fichier est trop volumineux (max. 2Mo).");
    }

    // Vérifie que le fichier est une image valide
    $fileInfo = getimagesize($file['tmp_name']);
    if ($fileInfo === false) {
        throw new Exception("Le fichier n'est pas une image valide.");
    }

    // Vérifie l'extension du fichier
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Format non supporté. Formats autorisés : " . implode(", ", $allowedExtensions));
    }

    // Vérifie l'espace disque disponible
    if (disk_free_space($uploadDir) < $file['size']) {
        throw new Exception("Espace disque insuffisant pour uploader ce fichier.");
    }

    // Génère un nom de fichier unique
    $uniqueId = str_replace('.', '', microtime(true)) . rand(1000, 9999);
    $newFileName = "cover.$uniqueId.$fileExtension"; // Ex: cover.XXXXXXXXXXXX.png
    $destination = $uploadDir . $newFileName; // Chemin complet du fichier

    // Redimensionne et sauvegarde l'image
    if (!resize_image($file['tmp_name'], $destination, 300, 400)) {
        throw new Exception("Le redimensionnement de la couverture a échoué.");
    }

    // Retourne uniquement le nom du fichier (à stocker en base)
    return $newFileName;
}