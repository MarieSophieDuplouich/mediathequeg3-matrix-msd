<?php
// Inclusion des fichiers nécessaires (helpers et modèles)
// Helpers : fonctions utilitaires globales
require_once ROOT_PATH . '/includes/helpers.php';
// Modèle médias : gestion des médias, recherche etc.
require_once MODEL_PATH . '/media_model.php';
//require_once MODEL_PATH . '/loan_model.php'; // Non utilisé ici

// -----------------------------------------
// Liste complète des emprunts pour l’admin
// Retourne tous les emprunts avec infos utilisateur et média
function get_all_loans(): array
{
    // Sélectionne tous les emprunts, avec nom utilisateur et titre média
    return db_select("
        SELECT l.*,
               u.name  AS user_name,
               m.title AS media_title
        FROM loans l
        JOIN users  u ON u.id = l.user_id
        JOIN medias m ON m.id = l.media_id
        ORDER BY l.loan_date DESC
    ");
}

// -----------------------------------------
// Dashboard principal de l'admin
// Récupère les médias, les emprunts, et les stats
function admin_dashboard_model()
{
    // Vérifie que l'utilisateur est administrateur
    require_admin();

    // Récupère tous les médias et emprunts
    $medias = search_medias();
    $loans = get_all_loans();

    // Statistiques diverses à afficher dans le dashboard
    $stats = [
        // Nombre de livres, films, jeux
        'total_livres' => (int) (db_select_one("SELECT COUNT(*) AS total FROM medias WHERE type_id = 1")['total'] ?? 0),
        'total_films' => (int) (db_select_one("SELECT COUNT(*) AS total FROM medias WHERE type_id = 2")['total'] ?? 0),
        'total_jeux' => (int) (db_select_one("SELECT COUNT(*) AS total FROM medias WHERE type_id = 3")['total'] ?? 0),

        // Emprunts en cours (non rendus)
        'emprunts_en_cours' => (int) (db_select_one("
        SELECT COUNT(*) AS total
        FROM loans
        WHERE actual_return_date IS NULL
        ")['total'] ?? 0),


        // Emprunts en retard (en cours + date de retour dépassée)
        'emprunts_en_retard' => db_select("
            SELECT l.*, u.name AS user_name, m.title AS media_title
            FROM loans l
            JOIN users u  ON u.id = l.user_id
            JOIN medias m ON m.id = l.media_id
            WHERE l.status = 'en cours'
              AND l.expected_return_date < CURDATE()
        "),

        // Statistiques sur les utilisateurs (table user_stats)
        'total_utilisateur' => db_select_one("SELECT total_users FROM user_stats")['total_users'] ?? 0,
        'nouveaux_utilisateur_7d' => db_select_one("SELECT new_users_7d FROM user_stats")['new_users_7d'] ?? 0,
        'nouveaux_utilisateur_30d' => db_select_one("SELECT new_users_30d FROM user_stats")['new_users_30d'] ?? 0,
    ];

    // Charge la vue dashboard admin avec les données récupérées
    load_view_with_layout('admin/dashboard', [
        'title' => 'Admin',
        'medias' => $medias,
        'loans' => $loans,
        'stats' => $stats
    ]);
}

// -----------------------------------------
// Emprunts en cours par utilisateur
function admin_current_loans_by_users()
{
    require_admin();
    // Sélectionne tous les emprunts non rendus, trié par utilisateur et date emprunt
    $current_loans = db_select("
        SELECT u.id, u.name, m.title, l.loan_date
        FROM users u
        JOIN loans l ON u.id = l.user_id
        JOIN medias m ON l.media_id = m.id
        WHERE l.actual_return_date IS NULL
        ORDER BY u.name, l.loan_date DESC
    ");
    // Charge la vue correspondante avec les données
    load_view_with_layout('admin/current_loans_by_users', [
        'current_loans' => $current_loans
    ]);
}

// -----------------------------------------
// Statistiques d’utilisation par utilisateur
function admin_users_utilisation()
{
    require_admin();

    // Pour chaque utilisateur : total des emprunts, total en cours
    $users_stats = db_select("
        SELECT u.id, u.name, 
        COUNT(l.id) AS total_loans,
        COUNT(CASE WHEN l.actual_return_date IS NULL THEN l.id END) AS loans_in_progress
        FROM users u
        LEFT JOIN loans l ON u.id = l.user_id
        GROUP BY u.id, u.name
        ORDER BY total_loans DESC
    ");
    // Charge la vue stats d'utilisation utilisateur
    load_view_with_layout('admin/users_utilisation', [
        'users_stats' => $users_stats
    ]);
}

// -----------------------------------------
// Historique complet des emprunts
function admin_users_history()
{
    require_admin();

    // Récupère l'historique complet des emprunts avec infos utilisateur et média
    $history = db_select("
        SELECT l.id, u.name AS user_name, m.title AS media_title, l.loan_date, l.expected_return_date, l.actual_return_date
        FROM loans l
        JOIN users u ON l.user_id = u.id
        JOIN medias m ON l.media_id = m.id
        ORDER BY l.loan_date DESC
    ");
    // Charge la vue historique utilisateur
    load_view_with_layout('admin/users_history', ['history' => $history]);
}

// -----------------------------------------
// Traite la suppression d'un utilisateur si le formulaire est soumis
function handle_user_delete_post()
{
    require_admin();
    // Si suppression utilisateur demandée en POST
    if (is_post() && isset($_POST['user_id'])) {
        admin_delete_user((int) $_POST['user_id']);
    }
}

// -----------------------------------------
// Liste des utilisateurs inscrits
function admin_registered_users()
{
    require_admin();

    // Récupère tous les utilisateurs non supprimés
    $users = db_select("
        SELECT id, name, email, created_at
        FROM users
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
    ");
    // Charge la vue liste utilisateurs inscrits
    load_view_with_layout('admin/registered_users', ['users' => $users]);
}

// -----------------------------------------
// Emprunts en retard par utilisateur
function admin_current_late_loans_by_users()
{
    require_admin();
    // Récupère tous les emprunts en retard
    $late_loans = db_select("
        SELECT l.id, u.name AS user_name, m.title AS media_title, l.loan_date, l.expected_return_date
        FROM loans l
        JOIN users u ON l.user_id = u.id
        JOIN medias m ON l.media_id = m.id
        WHERE l.actual_return_date IS NULL AND l.expected_return_date < CURDATE()
        ORDER BY l.expected_return_date ASC
    ");
    // Charge la vue emprunts en retard par utilisateur
    load_view_with_layout('admin/current_late_loans_by_users', ['late_loans' => $late_loans]);
}

// -----------------------------------------
// Suppression d'un média
function delete_media(int $id): bool
{
    require_admin();

    // 1. Vérifier les emprunts en cours pour ce média
    $en_cours = db_select_one("
        SELECT COUNT(*) AS total
        FROM loans
        WHERE media_id = ? AND actual_return_date IS NULL
    ", [$id])['total'] ?? 0;

    if ($en_cours > 0) {
        set_flash('danger', "Impossible de supprimer ce média : il y a des emprunts en cours.");
        return false;
    }

    // 2. Supprimer l'image du serveur si ce n'est pas l'image par défaut
    $media = db_select_one("SELECT cover FROM medias WHERE id = ?", [$id]);
    if ($media && !empty($media['cover']) && $media['cover'] !== 'uploads/covers/default.jpg') {
        if (file_exists($media['cover'])) {
            unlink($media['cover']);
        }
    }

    // 3. Supprimer les détails du média
    db_execute("DELETE FROM media_details WHERE media_id = ?", [$id]);
    // 4. Supprimer le média lui-même
    db_execute("DELETE FROM medias WHERE id = ?", [$id]);

    set_flash('success', "Média supprimé avec succès.");
    return true;
}

// -----------------------------------------
// Suppression d'un utilisateur (soft delete)
function admin_delete_user($user_id)
{
    require_admin();
    // Vérifier les emprunts en cours pour cet utilisateur
    $en_cours = db_select_one("
        SELECT COUNT(*) AS total
        FROM loans
        WHERE user_id = ? AND actual_return_date IS NULL
    ", [$user_id])['total'] ?? 0;

    if ($en_cours > 0) {
        set_flash('danger', "Impossible de supprimer cet utilisateur : il a des emprunts en cours.");
        redirect('admin/registered_users');
        return;
    }

    // Marque l'utilisateur comme supprimé.
    db_execute("UPDATE users SET deleted_at = NOW() WHERE id = ?", [$user_id]);
    set_flash('success', "Utilisateur supprimé. L'historique des emprunts est conservé.");
    redirect('admin/registered_users');
}

// -----------------------------------------
// Retour d'un emprunt
function return_loan($id)
{
    require_admin();
    // Récupère l'emprunt
    $loan = db_select_one("SELECT media_id FROM loans WHERE id = ?", [$id]);
    // Si l'emprunt existe
    if ($loan) {
        // Met à jour la date de retour ET le statut
        db_execute("UPDATE loans SET actual_return_date = NOW(), status = 'rendu' WHERE id = ?", [$id]);
        // Remet un exemplaire dans le stock du média
        db_execute("UPDATE medias SET stock = stock + 1 WHERE id = ?", [$loan['media_id']]);
    }
}

// -----------------------------------------
// Création d'un média (ajout)
function create_media(array $data): int
{
    require_admin();
    $cover = 'default.jpg';
    // Si une couverture personnalisée est fournie, l'utiliser
    if (!empty($data['cover']) && $data['cover'] !== 'uploads/covers/default.jpg') {
        $cover = $data['cover'];
    }
    // Ajoute le média principal
    db_execute(
        "INSERT INTO medias (type_id, title, stock, cover)
         VALUES (?,?,?,?)",
        [
            (int) $data['type_id'],
            $data['title'],
            (int) $data['stock'],
            $cover
        ]
    );
    $id = db_last_insert_id();
    // Ajoute les détails du média
    db_execute(
        "INSERT INTO media_details
         (media_id, author, isbn, pages, year, director, duration, classification, publisher, plateform, min_age, description,
          genre_books, genre_movies, genre_games)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $id,
            $data['author'] ?? null,
            $data['isbn'] ?? null,
            $data['pages'] ?? null,
            $data['year'] ?? null,
            $data['director'] ?? null,
            $data['duration'] ?? null,
            $data['classification'] ?? null,
            $data['publisher'] ?? null,
            $data['plateform'] ?? null,
            $data['min_age'] ?? null,
            $data['description'] ?? null,
            $data['genre_books'] ?? null,
            $data['genre_movies'] ?? null,
            $data['genre_games'] ?? null
        ]
    );

    return $id;
}

// -----------------------------------------
// Mise à jour d'un média
function update_media(int $id, array $data): bool
{
    require_admin();
    // Modifie les données principales du média
    db_execute(
        "UPDATE medias SET type_id=?, title=?, stock=?, cover=? WHERE id=?",
        [
            (int) $data['type_id'],
            $data['title'],
            (int) $data['stock'],
            $data['cover'] ?? null,
            $id
        ]
    );
    // Modifie les détails du média
    db_execute(
        "UPDATE media_details
         SET author=?, isbn=?, pages=?, year=?, director=?, duration=?, classification=?, publisher=?, plateform=?, min_age=?, description=?,
             genre_books=?, genre_movies=?, genre_games=?
         WHERE media_id=?",
        [
            $data['author'] ?? null,
            $data['isbn'] ?? null,
            $data['pages'] ?? null,
            $data['year'] ?? null,
            $data['director'] ?? null,
            $data['duration'] ?? null,
            $data['classification'] ?? null,
            $data['publisher'] ?? null,
            $data['plateform'] ?? null,
            $data['min_age'] ?? null,
            $data['description'] ?? null,
            $data['genre_books'] ?? null,
            $data['genre_movies'] ?? null,
            $data['genre_games'] ?? null,
            $id
        ]
    );

    return true;
}

// -----------------------------------------
// Recherche de médias selon divers critères
// $q = titre, $type_id = type (livre, film, jeu), $genre = genre, $available = dispo/emprunté
function search_medias(string $q = '', ?int $type_id = null, string $genre = '', string $available = ''): array
{
    // Requête de base pour récupérer tous les médias
    $sql = "SELECT m.id, m.title, m.stock, m.cover, m.type_id,
                   md.author, md.director, md.publisher,
                   md.genre_books, md.genre_movies, md.genre_games
            FROM medias m
            LEFT JOIN media_details md ON md.media_id = m.id
            WHERE 1=1";
    $params = [];

    // Filtre sur le titre
    if ($q !== '') {
        $sql .= " AND m.title LIKE ?";
        $params[] = "%$q%";
    }
    // Filtre sur le type
    if (!is_null($type_id)) {
        $sql .= " AND m.type_id = ?";
        $params[] = (int) $type_id;
    }
    // Filtre sur le genre
    if ($genre !== '') {
        if (!is_null($type_id)) {
            // Filtre sur le genre selon le type (livre, film, jeu)
            switch ((int) $type_id) {
                case 1:
                    $sql .= " AND md.genre_books = ?";
                    $params[] = $genre;
                    break;
                case 2:
                    $sql .= " AND md.genre_movies = ?";
                    $params[] = $genre;
                    break;
                case 3:
                    $sql .= " AND md.genre_games = ?";
                    $params[] = $genre;
                    break;
                default:
                    $sql .= " AND (md.genre_books = ? OR md.genre_movies = ? OR md.genre_games = ?)";
                    $params[] = $genre;
                    $params[] = $genre;
                    $params[] = $genre;
            }
        } else {
            // Si type inconnu, filtre sur tous les genres
            $sql .= " AND (md.genre_books = ? OR md.genre_movies = ? OR md.genre_games = ?)";
            $params[] = $genre;
            $params[] = $genre;
            $params[] = $genre;
        }
    }
    // Filtre sur la disponibilité
    if ($available === 'dispo') {
        $sql .= " AND m.stock > 0";
    } elseif ($available === 'emprunte') {
        $sql .= " AND m.stock = 0";
    }

    $sql .= " ORDER BY m.id DESC";
    // Exécute la requête et retourne le résultat
    return db_select($sql, $params);
}

// -----------------------------------------
// Liste des types de médias pour la vue admin
function admin_type_list_for_view(): array
{
    // Récupère tous les types depuis la table type
    $rows = db_select("SELECT id, movies, books, games FROM type ORDER BY id");
    $out = [];
    foreach ($rows as $r) {
        $label = 'Inconnu';
        // Définit le label selon le type
        if (!empty($r['books']))
            $label = 'Livre';
        if (!empty($r['movies']))
            $label = 'Film';
        if (!empty($r['games']))
            $label = 'Jeu';

        $out[] = [
            'id' => (int) $r['id'],
            'label' => $label,
        ];
    }
    return $out;
}