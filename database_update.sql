-- Aggiornamento database per personalizzazioni VaiQui
-- Esegui questo script nel tuo database MySQL

-- Aggiungi colonne per personalizzazione alla tabella users
ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'default';
ALTER TABLE users ADD COLUMN custom_css TEXT DEFAULT NULL;
ALTER TABLE users ADD COLUMN background_image VARCHAR(500) DEFAULT NULL;
ALTER TABLE users ADD COLUMN button_style VARCHAR(20) DEFAULT 'default';
ALTER TABLE users ADD COLUMN font_family VARCHAR(50) DEFAULT 'default';
ALTER TABLE users ADD COLUMN primary_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN secondary_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN text_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN background_color VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN button_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN button_text_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN border_radius INT DEFAULT 12;
ALTER TABLE users ADD COLUMN shadow_style VARCHAR(20) DEFAULT 'subtle';
ALTER TABLE users ADD COLUMN animation_style VARCHAR(20) DEFAULT 'subtle';
ALTER TABLE users ADD COLUMN social_provider VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(500) DEFAULT NULL;
-- Aggiungi colonne social (esegui solo se non esistono già)
-- Se una colonna esiste già, MySQL genererà un errore che puoi ignorare
ALTER TABLE users ADD COLUMN social_instagram VARCHAR(200) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_facebook VARCHAR(200) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_tiktok VARCHAR(200) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_twitter VARCHAR(200) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_linkedin VARCHAR(200) DEFAULT NULL;
ALTER TABLE users ADD COLUMN social_youtube VARCHAR(200) DEFAULT NULL;

-- Card con immagine per link
ALTER TABLE links ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;

-- Crea tabella settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crea tabella per link dinamici
CREATE TABLE IF NOT EXISTS dynamic_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    url TEXT NOT NULL,
    conditions JSON,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Crea tabella per link evento
CREATE TABLE IF NOT EXISTS event_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    url TEXT NOT NULL,
    event_date TIMESTAMP NOT NULL,
    timezone VARCHAR(50) DEFAULT 'Europe/Rome',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Crea tabella per link programmati
CREATE TABLE IF NOT EXISTS scheduled_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    url TEXT NOT NULL,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Crea tabella per link intelligenti
CREATE TABLE IF NOT EXISTS smart_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    urls JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Aggiorna tabella analytics per supportare più informazioni
ALTER TABLE analytics ADD COLUMN country VARCHAR(100) DEFAULT NULL;
ALTER TABLE analytics ADD COLUMN city VARCHAR(100) DEFAULT NULL;
ALTER TABLE analytics ADD COLUMN device_type VARCHAR(50) DEFAULT NULL;
ALTER TABLE analytics ADD COLUMN browser VARCHAR(100) DEFAULT NULL;
ALTER TABLE analytics ADD COLUMN os VARCHAR(100) DEFAULT NULL;

-- Aggiungi colonne per indirizzo e mappa (Funzionalità posizione)
-- Queste colonne permettono di mostrare un indirizzo e una mappa interattiva nel profilo
-- NOTA: Se le colonne esistono già, MySQL genererà un errore che puoi ignorare
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL;
ALTER TABLE users ADD COLUMN show_map BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL;
ALTER TABLE users ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL;

-- Campo per ruolo utente (admin o user) - Area Amministrativa
ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user';
-- Crea indice solo se non esiste (controlla manualmente se necessario)
CREATE INDEX idx_users_role ON users(role);

-- Verifica email
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN verification_token_expires DATETIME DEFAULT NULL;
-- Crea indice solo se non esiste (controlla manualmente se necessario)
CREATE INDEX idx_verification_token ON users(verification_token);

-- Template e personalizzazione profilo
ALTER TABLE users ADD COLUMN template VARCHAR(50) DEFAULT 'default';
ALTER TABLE users ADD COLUMN background_type VARCHAR(20) DEFAULT 'gradient';
ALTER TABLE users ADD COLUMN background_color VARCHAR(7) DEFAULT '#667eea';
ALTER TABLE users ADD COLUMN background_gradient VARCHAR(200) DEFAULT 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
ALTER TABLE users ADD COLUMN background_image VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN text_color VARCHAR(7) DEFAULT '#ffffff';
ALTER TABLE users ADD COLUMN link_style VARCHAR(50) DEFAULT 'card';
ALTER TABLE users ADD COLUMN link_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN link_hover_color VARCHAR(7) DEFAULT NULL;
ALTER TABLE users ADD COLUMN button_border_radius INT DEFAULT 12;
ALTER TABLE users ADD COLUMN button_shadow BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN font_family VARCHAR(100) DEFAULT 'system';
ALTER TABLE users ADD COLUMN profile_layout VARCHAR(50) DEFAULT 'centered';
ALTER TABLE users ADD COLUMN show_social_icons BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN custom_css TEXT DEFAULT NULL;
