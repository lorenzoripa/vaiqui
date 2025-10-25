<?php
require_once 'config/database.php';

echo "🔄 Aggiornamento database per personalizzazioni...\n";

try {
    // Aggiungi colonne per personalizzazione se non esistono
    $columns_to_add = [
        'theme' => 'VARCHAR(20) DEFAULT "default"',
        'custom_css' => 'TEXT DEFAULT NULL',
        'background_image' => 'VARCHAR(500) DEFAULT NULL',
        'button_style' => 'VARCHAR(20) DEFAULT "default"',
        'font_family' => 'VARCHAR(50) DEFAULT "default"',
        'primary_color' => 'VARCHAR(7) DEFAULT NULL',
        'secondary_color' => 'VARCHAR(7) DEFAULT NULL',
        'text_color' => 'VARCHAR(7) DEFAULT NULL',
        'background_color' => 'VARCHAR(100) DEFAULT NULL',
        'button_color' => 'VARCHAR(7) DEFAULT NULL',
        'button_text_color' => 'VARCHAR(7) DEFAULT NULL',
        'border_radius' => 'INT DEFAULT 12',
        'shadow_style' => 'VARCHAR(20) DEFAULT "subtle"',
        'animation_style' => 'VARCHAR(20) DEFAULT "subtle"',
        'social_provider' => 'VARCHAR(20) DEFAULT NULL',
        'social_id' => 'VARCHAR(100) DEFAULT NULL',
        'avatar' => 'VARCHAR(500) DEFAULT NULL'
    ];

    foreach ($columns_to_add as $column => $definition) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
            echo "✅ Aggiunta colonna: $column\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️  Colonna $column già esistente\n";
            } else {
                echo "❌ Errore aggiungendo $column: " . $e->getMessage() . "\n";
            }
        }
    }

    // Crea tabella settings se non esiste
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Tabella settings creata/verificata\n";

    // Crea tabella per link dinamici
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
    echo "✅ Tabella dynamic_links creata/verificata\n";

    // Crea tabella per link evento
    $sql = "CREATE TABLE IF NOT EXISTS event_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        url TEXT NOT NULL,
        event_date TIMESTAMP NOT NULL,
        timezone VARCHAR(50) DEFAULT 'Europe/Rome',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Tabella event_links creata/verificata\n";

    // Crea tabella per link programmati
    $sql = "CREATE TABLE IF NOT EXISTS scheduled_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        url TEXT NOT NULL,
        start_date TIMESTAMP NOT NULL,
        end_date TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Tabella scheduled_links creata/verificata\n";

    // Crea tabella per link intelligenti
    $sql = "CREATE TABLE IF NOT EXISTS smart_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        urls JSON NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Tabella smart_links creata/verificata\n";

    echo "\n🎉 Database aggiornato con successo!\n";
    echo "Ora puoi utilizzare tutte le funzionalità di personalizzazione.\n";

} catch (PDOException $e) {
    echo "❌ Errore durante l'aggiornamento: " . $e->getMessage() . "\n";
}
?>
