<?php
// Fonctions utilitaires

/**
 * Sécurise l'affichage d'une chaîne de caractères (protection XSS)
 */
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche une chaîne sécurisée (échappée)
 */
function e($string)
{
    echo escape($string);
}

/**
 * Retourne une chaîne sécurisée sans l'afficher
 */
function esc($string)
{
    return escape($string);
}

/**
 * Génère une URL absolue
 */
function url($path = '')
{
    $base_url = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $base_url . '/' . $path;
}

/**
 * Redirection HTTP
 */
function redirect($path = '')
{
    $url = url($path);
    header("Location: $url");
    exit;
}

/**
 * Génère un token CSRF
 */
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Définit un message flash
 */
function set_flash($type, $message)
{
    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Récupère et supprime les messages flash
 */
function get_flash_messages($type = null)
{
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }

    if ($type) {
        $messages = $_SESSION['flash_messages'][$type] ?? [];
        unset($_SESSION['flash_messages'][$type]);
        return $messages;
    }

    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Vérifie s'il y a des messages flash
 */
function has_flash_messages($type = null)
{
    if (!isset($_SESSION['flash_messages'])) {
        return false;
    }

    if ($type) {
        return !empty($_SESSION['flash_messages'][$type]);
    }

    return !empty($_SESSION['flash_messages']);
}

/**
 * Nettoie une chaîne de caractères
 */
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Valide une adresse email
 */
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Génère un mot de passe sécurisé
 */
function generate_password($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

/**
 * Hache un mot de passe
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Formate une date
 */
function format_date($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

/**
 * Vérifie si une requête est en POST
 */
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si une requête est en GET
 */
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Retourne la valeur d'un paramètre POST
 */
function post($key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Retourne la valeur d'un paramètre GET
 */
function get($key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * Vérifie si un utilisateur est connecté
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        set_flash('error', 'Vous devez être connecté pour accéder à cette page.');
        redirect('auth/login');
        exit;
    }
}

function require_admin() {
    if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
        set_flash('error', 'Accès interdit. Vous devez être administrateur.');
        redirect('/');
        exit;
    }
}

/**
 * Retourne l'ID de l'utilisateur connecté
 */
function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Déconnecte l'utilisateur
 */
function logout()
{
    session_destroy();
    redirect('auth/login');
}

/**
 * Formate un nombre
 */
function format_number($number, $decimals = 2)
{
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Génère un slug à partir d'une chaîne
 */
function generate_slug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}




/**
 * Redimensionne une image vers une taille donnée (par défaut 300x400).
 * 
 * @param string $source Chemin de l’image source
 * @param string $destination Chemin de l’image redimensionnée
 * @param int $max_width Largeur maximale
 * @param int $max_height Hauteur maximale
 * @return bool Succès ou échec
 */
function resize_image(string $source, string $destination, int $width = 300, int $height = 400): bool
{
    $info = getimagesize($source);
    if (!$info) return false;

    // Crée une image de destination à la taille voulue (portrait fixe 300x400)
    $final_image = imagecreatetruecolor($width, $height);

    // Charge l’image source
    switch ($info['mime']) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    // Copie et déforme (remplit tout le cadre, peu importe le ratio d'origine !)
    imagecopyresampled(
        $final_image, $source_image,
        0, 0, 0, 0,
        $width, $height,
        $info[0], $info[1]
    );

    // Sauvegarde
    switch ($info['mime']) {
        case 'image/jpeg':
            imagejpeg($final_image, $destination, 90);
            break;
        case 'image/png':
            imagepng($final_image, $destination);
            break;
        case 'image/gif':
            imagegif($final_image, $destination);
            break;
    }

    imagedestroy($final_image);
    imagedestroy($source_image);

    return true;
}
function paginate(int $total, int $perPage = 20): array {
    $pages = max(1, (int) ceil($total / $perPage)); // nb total pages min=1
    $page  = isset($_GET['page']) ? (int) $_GET['page'] : 1; // page from url

    if ($page < 1) $page = 1; // fix si trop bas
    if ($page > $pages) $page = $pages; // fix si trop haut

    $offset = ($page - 1) * $perPage; // sql offset

    return [$page, $pages, $offset, $perPage]; // return data
}

// session check
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$now  = time(); // current time
$last = $_SESSION['last_activity'] ?? $now; // last act

if (($now - $last) > 7200) { // 2h
    $_SESSION = []; // wipe session

    if (ini_get('session.use_cookies')) { // remove cookie
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy(); // end session
    session_start(); // restart clean

    set_flash('info', 'Session expirée après 2h. Reconnect pls.'); // flash msg
    header('Location: '.url('auth/login')); // redirect login
    exit;
}

$_SESSION['last_activity'] = $now; // update last act

function cover_path($raw) {
  $c = trim((string)$raw); // normalize
  if ($c === '') return 'uploads/covers/default.jpg'; // fallback
  if (strpos($c, '/../public/') === 0) { // clean prefix public
    $c = ltrim(substr($c, strlen('/../public/')), '/');
  }
  if (strpos($c, '/') !== false) return $c; // déjà chemin
  return 'uploads/covers/' . $c; // filename -> path
}
function media_type_label(?int $id): string {
  return [1=>'Livre',2=>'Film',3=>'Jeu'][$id] ?? 'Inconnu';
}
