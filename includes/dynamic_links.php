<?php
require_once 'config/database.php';
require_once 'config/settings.php';

// ========== FUNZIONI PER LINK DINAMICI ==========

// Crea un link dinamico
function createDynamicLink($user_id, $title, $url, $conditions = [], $expires_at = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO dynamic_links (user_id, title, url, conditions, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $url, json_encode($conditions), $expires_at]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni i link dinamici di un utente
function getUserDynamicLinks($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM dynamic_links WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Valuta le condizioni di un link dinamico
function evaluateDynamicLink($link, $user_data = []) {
    $conditions = json_decode($link['conditions'], true);
    
    if (!$conditions) {
        return true; // Nessuna condizione = sempre attivo
    }
    
    foreach ($conditions as $condition) {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        
        $user_value = $user_data[$field] ?? null;
        
        switch ($operator) {
            case 'equals':
                if ($user_value != $value) return false;
                break;
            case 'not_equals':
                if ($user_value == $value) return false;
                break;
            case 'contains':
                if (strpos($user_value, $value) === false) return false;
                break;
            case 'not_contains':
                if (strpos($user_value, $value) !== false) return false;
                break;
            case 'greater_than':
                if ($user_value <= $value) return false;
                break;
            case 'less_than':
                if ($user_value >= $value) return false;
                break;
            case 'is_empty':
                if (!empty($user_value)) return false;
                break;
            case 'is_not_empty':
                if (empty($user_value)) return false;
                break;
        }
    }
    
    return true;
}

// ========== FUNZIONI PER LINK EVENTI ==========

// Crea un link evento
function createEventLink($user_id, $title, $url, $event_date, $timezone = 'Europe/Rome') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO event_links (user_id, title, url, event_date, timezone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $url, $event_date, $timezone]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni i link evento di un utente
function getUserEventLinks($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM event_links WHERE user_id = ? ORDER BY event_date ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Controlla se un link evento è attivo
function isEventLinkActive($event_link) {
    $now = new DateTime();
    $event_date = new DateTime($event_link['event_date']);
    
    // Link attivo se l'evento è nel futuro
    return $event_date > $now;
}

// ========== FUNZIONI PER LINK PROGRAMMATI ==========

// Crea un link programmato
function createScheduledLink($user_id, $title, $url, $start_date, $end_date = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO scheduled_links (user_id, title, url, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $url, $start_date, $end_date]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni i link programmati di un utente
function getUserScheduledLinks($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM scheduled_links WHERE user_id = ? ORDER BY start_date ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Controlla se un link programmato è attivo
function isScheduledLinkActive($scheduled_link) {
    $now = new DateTime();
    $start_date = new DateTime($scheduled_link['start_date']);
    $end_date = $scheduled_link['end_date'] ? new DateTime($scheduled_link['end_date']) : null;
    
    // Link attivo se siamo tra start_date e end_date
    if ($now < $start_date) {
        return false; // Non ancora iniziato
    }
    
    if ($end_date && $now > $end_date) {
        return false; // Già scaduto
    }
    
    return true;
}

// ========== FUNZIONI PER LINK INTELLIGENTI ==========

// Crea un link intelligente che cambia in base al tempo
function createSmartLink($user_id, $title, $urls = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO smart_links (user_id, title, urls) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, json_encode($urls)]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni l'URL corretto per un link intelligente
function getSmartLinkUrl($smart_link, $user_data = []) {
    $urls = json_decode($smart_link['urls'], true);
    
    if (!$urls) {
        return null;
    }
    
    $now = new DateTime();
    $current_hour = (int)$now->format('H');
    $current_day = $now->format('N'); // 1 = lunedì, 7 = domenica
    
    // Logica per selezionare l'URL corretto
    foreach ($urls as $url_config) {
        $conditions = $url_config['conditions'] ?? [];
        $url = $url_config['url'];
        
        $matches = true;
        
        foreach ($conditions as $condition) {
            switch ($condition['type']) {
                case 'time_range':
                    $start_hour = $condition['start_hour'];
                    $end_hour = $condition['end_hour'];
                    
                    if ($current_hour < $start_hour || $current_hour >= $end_hour) {
                        $matches = false;
                    }
                    break;
                    
                case 'day_of_week':
                    $allowed_days = $condition['days'];
                    
                    if (!in_array($current_day, $allowed_days)) {
                        $matches = false;
                    }
                    break;
                    
                case 'date_range':
                    $start_date = new DateTime($condition['start_date']);
                    $end_date = new DateTime($condition['end_date']);
                    
                    if ($now < $start_date || $now > $end_date) {
                        $matches = false;
                    }
                    break;
            }
        }
        
        if ($matches) {
            return $url;
        }
    }
    
    // Se nessuna condizione è soddisfatta, usa l'URL di default
    return $urls[0]['url'] ?? null;
}

// ========== FUNZIONI DI UTILITÀ ==========

// Ottieni tutti i link attivi di un utente (normali + dinamici + eventi + programmati)
function getAllActiveLinks($user_id) {
    $normal_links = getUserLinks($user_id);
    $dynamic_links = getUserDynamicLinks($user_id);
    $event_links = getUserEventLinks($user_id);
    $scheduled_links = getUserScheduledLinks($user_id);
    
    $all_links = [];
    
    // Aggiungi link normali
    foreach ($normal_links as $link) {
        $all_links[] = [
            'type' => 'normal',
            'data' => $link
        ];
    }
    
    // Aggiungi link dinamici attivi
    foreach ($dynamic_links as $link) {
        if (evaluateDynamicLink($link)) {
            $all_links[] = [
                'type' => 'dynamic',
                'data' => $link
            ];
        }
    }
    
    // Aggiungi link evento attivi
    foreach ($event_links as $link) {
        if (isEventLinkActive($link)) {
            $all_links[] = [
                'type' => 'event',
                'data' => $link
            ];
        }
    }
    
    // Aggiungi link programmati attivi
    foreach ($scheduled_links as $link) {
        if (isScheduledLinkActive($link)) {
            $all_links[] = [
                'type' => 'scheduled',
                'data' => $link
            ];
        }
    }
    
    return $all_links;
}

// Crea le tabelle per i link avanzati
function createAdvancedLinkTables() {
    global $pdo;
    
    // Tabella link dinamici
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
    
    // Tabella link evento
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
    
    // Tabella link programmati
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
    
    // Tabella link intelligenti
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
}

// Crea le tabelle
createAdvancedLinkTables();
?>
