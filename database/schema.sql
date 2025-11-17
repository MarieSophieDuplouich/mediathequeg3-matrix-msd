-- Schéma de base de données pour l'application PHP MVC
-- Exécutez ce script dans votre base de données MySQL

CREATE DATABASE IF NOT EXISTS php_mvc_app CHARACTER SET utf8 COLLATE utf8_general_ci;
USE php_mvc_app;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index pour optimiser les recherches
CREATE INDEX idx_users_email ON users(email);

-- Données de test (optionnel)
-- Mot de passe : "password123"
INSERT INTO users (name, email, password) VALUES 
('John Doe', 'john@example.com', '$2y$10$/vD8hGtkBJsAae2TiSkbV.jg0bnNDAFv8xBewH14.OKvR0PpeVbq6'),
('Jane Smith', 'jane@example.com', '$2y$10$/vD8hGtkBJsAae2TiSkbV.jg0bnNDAFv8xBewH14.OKvR0PpeVbq6'),
('Admin User', 'admin@example.com', '$2y$10$/vD8hGtkBJsAae2TiSkbV.jg0bnNDAFv8xBewH14.OKvR0PpeVbq6');

-- Ajoute une colonne deleted_at pour la suppression "soft"
-- Permet de marquer un utilisateur comme supprimé sans effacer ses données
ALTER TABLE users ADD COLUMN deleted_at DATETIME NULL AFTER updated_at;

-- Table de messages de contact (exemple d'extension)
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL
);


-- Table de paramètres de configuration
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Paramètres par défaut
INSERT INTO settings (key_name, value, description) VALUES 
('site_name', 'PHP MVC Starter', 'Nom du site web'),
('maintenance_mode', '0', 'Mode maintenance (0 = désactivé, 1 = activé)'),
('max_login_attempts', '5', 'Nombre maximum de tentatives de connexion'),
('session_timeout', '3600', 'Timeout de session en secondes');

-- Vue pour les statistiques
CREATE VIEW user_stats AS
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_7d
FROM users; 

CREATE TABLE type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movies INT,
    books INT,
    games INT
)
--Media table
CREATE TABLE medias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,  
    cover VARCHAR(255) DEFAULT 'default.jpg',
    stock INT DEFAULT 1 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES type(id) ON DELETE CASCADE

);

--Media_detail table
CREATE TABLE media_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_id INT NOT NULL UNIQUE,
        genre_movies ENUM('Action','Aventure','Comédie','Drame','Fantastique','Horreur','Science-fiction','Thriller','Romance','Documentaire','Animation','Mystère','Crime','Guerre','Western','Musical','Historique','Biopic','Famille') NOT NULL,
        genre_books ENUM('Aventure','Théâtre','Manga','Drame','Fantastique','Horreur','Science-fiction','Thriller','Romance','Poésie','BD','Polar','Historique','Biographie','Philosophie') NOT NULL,
        genre_games ENUM('Action','Aventure','RPG','MMORPG','FPS','TPS','Jeux de sport','Courses','Simulation','Stratégie','Battle Royal','MOBA','Combat','Plateforme','Horreur','Puzzle/Réflexion','Rogue-like/Rogue-lite','Indie') NOT NULL,
    author VARCHAR(100),
    isbn VARCHAR(20) UNIQUE,
    pages INT,
    year YEAR,
    director VARCHAR(100),
    duration INT,
    classification ENUM('Tous publics','-12','-16','-18'),
    publisher VARCHAR(100),
    plateform ENUM('PC', 'PlayStation','Xbox','Nintendo','Mobile'),
    min_age ENUM('3','7','12','16','18'),
    description TEXT,
    FOREIGN KEY (media_id) REFERENCES medias(id) ON DELETE CASCADE
);

--Loan table
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_id INT NOT NULL,
    loan_date DATE NOT NULL,
    expected_return_date DATE NOT NULL,
    actual_return_date DATE NOT NULL,
    status ENUM('en cours','rendu') DEFAULT 'en cours' NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (media_id) REFERENCES medias(id) ON DELETE CASCADE
);

--Role to users
ALTER TABLE users
ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user';