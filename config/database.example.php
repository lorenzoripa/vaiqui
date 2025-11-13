<?php
/**
 * Configurazione Database - File di Esempio
 * 
 * ISTRUZIONI:
 * 1. Copia questo file in config/database.php
 * 2. Modifica le credenziali con i tuoi dati
 * 3. NON committare mai config/database.php su Git!
 */

// Configurazione database
$host = 'localhost';
$dbname = 'vaiqui_db';
$username = 'vaiqui_db_usr';
$password = 'your_secure_password_here';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Le tabelle verranno create automaticamente
    createTables($pdo);
    
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

// Funzione per creare le tabelle
function createTables($pdo) {
    // Tabella utenti
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        display_name VARCHAR(100),
        bio TEXT,
        avatar VARCHAR(500),
        theme VARCHAR(50) DEFAULT 'default',
        custom_css TEXT,
        background_image VARCHAR(255),
        button_style VARCHAR(50) DEFAULT 'default',
        font_family VARCHAR(100) DEFAULT 'Roboto',
        primary_color VARCHAR(7) DEFAULT '#667eea',
        secondary_color VARCHAR(7) DEFAULT '#764ba2',
        text_color VARCHAR(7) DEFAULT '#ffffff',
        background_color VARCHAR(7) DEFAULT '#667eea',
        button_color VARCHAR(7) DEFAULT '#f8f9fa',
        button_text_color VARCHAR(7) DEFAULT '#333333',
        border_radius INT DEFAULT 12,
        shadow_style VARCHAR(50) DEFAULT 'subtle',
        animation_style VARCHAR(50) DEFAULT 'subtle',
        address TEXT,
        show_map BOOLEAN DEFAULT FALSE,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        social_provider VARCHAR(20),
        social_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Tabella link
    $sql = "CREATE TABLE IF NOT EXISTS links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        url TEXT NOT NULL,
        icon VARCHAR(100),
        color VARCHAR(7) DEFAULT '#007bff',
        image_url VARCHAR(500) DEFAULT NULL,
        position INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Tabella analytics
    $sql = "CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        link_id INT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        referer VARCHAR(500),
        country VARCHAR(100),
        city VARCHAR(100),
        device_type VARCHAR(50),
        browser VARCHAR(100),
        os VARCHAR(100),
        clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Tabella link accorciati
    $sql = "CREATE TABLE IF NOT EXISTS short_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        original_url TEXT NOT NULL,
        short_code VARCHAR(20) UNIQUE NOT NULL,
        title VARCHAR(200),
        description TEXT,
        click_count INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Tabella click analytics per link accorciati
    $sql = "CREATE TABLE IF NOT EXISTS short_link_clicks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        short_link_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        referer VARCHAR(500),
        country VARCHAR(100),
        city VARCHAR(100),
        device_type VARCHAR(50),
        browser VARCHAR(100),
        os VARCHAR(100),
        clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (short_link_id) REFERENCES short_links(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Tabella dynamic_links
    $sql = "CREATE TABLE IF NOT EXISTS dynamic_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        url TEXT NOT NULL,
        conditions JSON,
        expires_at TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Tabella settings
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}

?>
