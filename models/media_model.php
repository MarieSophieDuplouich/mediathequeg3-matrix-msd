<?php
require_once ROOT_PATH . '/includes/helpers.php';

/**
 * map type texte/num -> id
 * in: "livre"/1/"film"/"game" etc
 * out: 1=livre, 2=film, 3=jeu, sinon null
 */
function _type_to_id($type) {
    if (is_null($type) || $type === '') return null; // rien -> null
    if (is_numeric($type)) return (int)$type; // déjà id -> int

    $type = strtolower(trim($type)); // normalise
    if ($type === 'livre' || $type === 'book' || $type === 'livres' || $type === 'books') return 1; // book
    if ($type === 'film' || $type === 'movie' || $type === 'films' || $type === 'movies') return 2; // movie
    if ($type === 'jeu' || $type === 'game' || $type === 'jeux' || $type === 'games') return 3; // game
    return null; // inconnu
}

// dispo + recherche auto detect type dans q
function _extract_type_from_query(string $q): array {
    $q_norm = mb_strtolower(trim($q), 'UTF-8'); // normalize
    $map = [
        1 => ['livre', 'livres', 'book', 'books'], // book vocab
        2 => ['film', 'films', 'movie', 'movies', 'cinema'], // film vocab
        3 => ['jeu', 'jeux', 'game', 'games'], // game vocab
    ];
    $detected = null; // type id trouvé ou null

    foreach ($map as $type_id => $words) { // scan keywords
        foreach ($words as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/u', $q_norm)) {
                $detected = $type_id; // type spot
                foreach ($words as $rw) { // clean mots type de la query
                    $q_norm = preg_replace('/\b' . preg_quote($rw, '/') . '\b/u', ' ', $q_norm);
                }
                break 2; // break double loop
            }
        }
    }

    $q_clean = trim(preg_replace('/\s{2,}/', ' ', $q_norm)); // squeeze spaces
    return [$detected, $q_clean]; // [type_id|null, q nettoyée]
}

// count medias dispo
function media_count_dispo(?string $q = null, $type = null, ?string $genre = null, ?bool $available = null): int {
    $where  = "1=1"; // base where
    $params = []; // pdo params

    // auto detect type depuis q si type vide
    $detected_type_id = null;
    if (($type === null || $type === '') && $q !== null && $q !== '') {
        [$detected_type_id, $q] = _extract_type_from_query($q); // detect + clean q
    }

    // filtre dispo
    if ($available === true)      $where .= " AND m.stock > 0";
    elseif ($available === false) $where .= " AND m.stock = 0";

    // filtre texte global
    if ($q !== null && $q !== '') {
        $where .= " AND (m.title LIKE ? OR md.author LIKE ? OR md.director LIKE ? OR md.publisher LIKE ? OR md.genre_books LIKE ? OR md.genre_movies LIKE ? OR md.genre_games LIKE ?)";
        $like = '%'.$q.'%';
        array_push($params, $like,$like,$like,$like,$like,$like,$like);
    }

    // filtre type (input > detected)
    $type_id = _type_to_id($type);
    if (is_null($type_id) && !is_null($detected_type_id)) $type_id = $detected_type_id;
    if (!is_null($type_id)) { $where .= " AND m.type_id = ?"; $params[] = $type_id; }

    // filtre genre (dépend du type)
    if ($genre !== null && $genre !== '') {
        if (!is_null($type_id)) {
            if ($type_id === 1) { $where .= " AND md.genre_books = ?";  $params[] = $genre; }
            elseif ($type_id === 2){ $where .= " AND md.genre_movies = ?"; $params[] = $genre; }
            elseif ($type_id === 3){ $where .= " AND md.genre_games = ?";  $params[] = $genre; }
        } else {
            $where .= " AND (md.genre_books = ? OR md.genre_movies = ? OR md.genre_games = ?)";
            array_push($params, $genre, $genre, $genre);
        }
    }

    // query count distinct
    $row = db_select_one(
        "SELECT COUNT(DISTINCT m.id) AS c
         FROM medias m
         LEFT JOIN media_details md ON md.media_id = m.id
         WHERE $where",
        $params
    );
    return (int)($row['c'] ?? 0); // cast int
}

// liste dispo + recherche
function media_list_dispo(int $limit, int $offset, ?string $q = null, $type = null, ?string $genre = null, ?bool $available = null): array {
    $limit  = max(1, min(100, (int)$limit)); // clamp limit 1..100
    $offset = max(0, (int)$offset); // offset >= 0

    $params = [];
    $where  = "1=1";

    // detect type si vide + q
    $detected_type_id = null;
    if (($type === null || $type === '') && $q !== null && $q !== '') {
        [$detected_type_id, $q] = _extract_type_from_query($q); // detect + clean
    }

    // dispo
    if ($available === true) $where .= " AND m.stock > 0";
    elseif ($available === false) $where .= " AND m.stock = 0";

    // texte global
    if ($q !== null && $q !== '') {
        $where .= " AND (m.title LIKE ? OR md.author LIKE ? OR md.director LIKE ? OR md.publisher LIKE ? OR md.genre_books LIKE ? OR md.genre_movies LIKE ? OR md.genre_games LIKE ?)";
        $like = '%'.$q.'%';
        array_push($params, $like,$like,$like,$like,$like,$like,$like);
    }

    // type
    $type_id = _type_to_id($type);
    if (is_null($type_id) && !is_null($detected_type_id)) $type_id = $detected_type_id;
    if (!is_null($type_id)) { $where .= " AND m.type_id = ?"; $params[] = $type_id; }

    // genre
    if ($genre !== null && $genre !== '') {
        if (!is_null($type_id)) {
            if ($type_id === 1) { $where .= " AND md.genre_books = ?";  $params[] = $genre; }
            elseif ($type_id === 2){ $where .= " AND md.genre_movies = ?"; $params[] = $genre; }
            elseif ($type_id === 3){ $where .= " AND md.genre_games = ?";  $params[] = $genre; }
        } else {
            $where .= " AND (md.genre_books = ? OR md.genre_movies = ? OR md.genre_games = ?)";
            array_push($params, $genre, $genre, $genre);
        }
    }

    // select liste + details
    $sql = "SELECT
                m.id, m.title, m.type_id, m.stock, m.cover, m.created_at, md.author, md.isbn, md.pages, md.year, md.director, md.duration, md.classification, md.publisher, md.plateform, md.min_age, md.description, md.genre_books, md.genre_movies, md.genre_games
            FROM medias m
            LEFT JOIN media_details md ON md.media_id = m.id
            WHERE $where
            ORDER BY m.created_at DESC, m.id DESC
            LIMIT $offset, $limit";

    return db_select($sql, $params); // fetch rows
}

// media + details full
function media_get($id) {
    return db_select_one(
        "SELECT 
            m.*,
            md.author, md.isbn, md.pages, md.year, md.director, md.duration, md.classification, md.publisher, md.plateform, md.min_age, md.description, md.genre_books, md.genre_movies, md.genre_games
         FROM medias m
         LEFT JOIN media_details md ON md.media_id = m.id
         WHERE m.id = ?",
        [$id]
    );
}

// alias si non existant
if (!function_exists('get_media_by_id')) {
    function get_media_by_id($id) { return media_get($id); } // alias simple
}

// emprunt actif pour user/media
function get_active_loan_for(int $user_id, int $media_id): ?array {
    $row = db_select_one(
        "SELECT * FROM loans
         WHERE user_id=? AND media_id=? AND actual_return_date IS NULL
         ORDER BY loan_date DESC LIMIT 1",
        [$user_id, $media_id]
    );
    return $row ?: null; // null si none
}

// règles / count loans actifs user
function loan_count_active($user_id) {
    $row = db_select_one(
        "SELECT COUNT(*) AS c FROM loans WHERE user_id=? AND actual_return_date IS NULL",
        [$user_id]
    );
    return (int)($row['c'] ?? 0); // nb actifs
}

// emprunter media
function media_borrow_item($user_id, $media_id) {
    require_login(); // must auth

    // max 3 emprunts
    if (loan_count_active($user_id) >= 3) {
        throw new Exception("Limite de 3 emprunts simultanés atteinte.");
    }

    // info media
    $m = media_get($media_id);

    // check dispo
    if (!$m || (int)$m['stock'] <= 0) {
        throw new Exception("Média non disponible.");
    }

    // date retour attendu +14j
    $expected = date('Y-m-d H:i:s', strtotime('+14 days'));

    db_begin_transaction(); // tx start
    try {
        // insert loan
        db_execute(
            "INSERT INTO loans (user_id, media_id, loan_date, expected_return_date, status)
             VALUES (?, ?, NOW(), ?, 'en cours')",
            [$user_id, $media_id, $expected]
        );
        // baisse stock
        db_execute("UPDATE medias SET stock = stock - 1, updated_at = NOW() WHERE id=?", [$media_id]);

        db_commit(); // ok
        return $expected; // give due date
    } catch (Exception $e) {
        db_rollback(); // nope
        throw $e; // bubble
    }
}

// rendre media
function media_return_item($user_id, $media_id) {
    require_login(); // must auth
    db_begin_transaction(); // tx start
    try {
        // loan actif à rendre
        $row = db_select_one(
            "SELECT id FROM loans
             WHERE user_id=? AND media_id=? AND actual_return_date IS NULL
             ORDER BY loan_date DESC LIMIT 1",
            [$user_id, $media_id]
        );

        if (!$row) { // rien à rendre
            throw new Exception("Aucun emprunt en cours pour ce média.");
        }

        // maj loan -> rendu
        db_execute(
            "UPDATE loans 
             SET actual_return_date = NOW(), status = 'rendu' 
             WHERE id=?",
            [$row['id']]
        );

        // remonte stock
        db_execute("UPDATE medias SET stock = stock + 1, updated_at = NOW() WHERE id=?", [$media_id]);

        db_commit(); // done
    } catch (Exception $e) {
        db_rollback(); // rollback
        throw $e;
    }
}

// profil: liste emprunts actifs user
function loans_list_active_by_user(int $userId): array {
    return db_select(
        "SELECT 
            l.id  AS loan_id, l.loan_date, l.expected_return_date, m.id  AS media_id, m.title, m.cover, m.type_id
         FROM loans l
         JOIN medias m ON m.id = l.media_id
         WHERE l.user_id = ?
           AND l.actual_return_date IS NULL
         ORDER BY l.loan_date DESC",
        [$userId]
    );
}

// profil: historique emprunts user
function loans_list_history_by_user(int $userId): array {
    return db_select(
        "SELECT 
            l.id  AS loan_id, l.loan_date, l.expected_return_date, l.actual_return_date, m.id  AS media_id, m.title, m.cover, m.type_id
         FROM loans l
         JOIN medias m ON m.id = l.media_id
         WHERE l.user_id = ?
           AND l.actual_return_date IS NOT NULL
         ORDER BY l.actual_return_date DESC, l.loan_date DESC",
        [$userId]
    );
}

// profil: rendre via id emprunt
function loan_return_by_id(int $userId, int $loanId): void {
    db_begin_transaction(); // tx
    try {
        // fetch loan actif by id
        $loan = db_select_one(
            "SELECT id, media_id 
             FROM loans 
             WHERE id = ? AND user_id = ? AND actual_return_date IS NULL
             LIMIT 1",
            [$loanId, $userId]
        );
        if (!$loan) { // not found or déjà rendu
            throw new Exception("Emprunt introuvable ou déjà rendu.");
        }

        // set rendu
        db_execute("UPDATE loans SET actual_return_date = NOW(), status = 'rendu' WHERE id = ?", [$loanId]);
        // +1 stock
        db_execute("UPDATE medias SET stock = stock + 1, updated_at = NOW() WHERE id = ?", [$loan['media_id']]);

        db_commit(); // ok
    } catch (Exception $e) {
        db_rollback(); // nope
        throw $e;
    }
}
