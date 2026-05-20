<?php
// Configuration de la base de données
define('DB_HOST', 'bdd');
define('DB_NAME', 'php_mvc_app');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8');
// Configuration générale de l'application
define('HOST',$_ENV["HOST"]);
define('BASE_URL', 'http://'.HOST);
define('APP_NAME', 'MEDIATHEQUE 3');
define('APP_VERSION', '1.0.0');

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CONTROLLER_PATH', ROOT_PATH . '/controllers');
define('MODEL_PATH', ROOT_PATH . '/models');
define('VIEW_PATH', ROOT_PATH . '/views');
define('INCLUDE_PATH', ROOT_PATH . '/includes');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', ROOT_PATH . '/public'); 