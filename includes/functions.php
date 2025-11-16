<?php
require_once __DIR__ . '/../config/database.php';

// Funzione per registrare un nuovo utente
function registerUser($username, $email, $password) {
    global $pdo;
    
    try {
        // Controlla se l'email o username esistono già
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            return "Email o username già esistenti";
        }
        
        // Hash della password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Genera token di verifica email
        $verification_token = bin2hex(random_bytes(32));
        $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Inserisci il nuovo utente
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, display_name, verification_token, verification_token_expires, email_verified) VALUES (?, ?, ?, ?, ?, ?, FALSE)");
        $stmt->execute([$username, $email, $hashedPassword, $username, $verification_token, $verification_expires]);
        
        $user_id = $pdo->lastInsertId();
        
        // Invia email di verifica
        sendVerificationEmail($user_id, $email, $verification_token);
        
        return true;
    } catch (PDOException $e) {
        return "Errore durante la registrazione: " . $e->getMessage();
    }
}

// Funzione per il login
function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per ottenere i dati dell'utente
function getUser($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per ottenere i link di un utente
function getUserLinks($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM links WHERE user_id = ? AND is_active = TRUE ORDER BY position ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Funzione per aggiungere un nuovo link
function addLink($user_id, $title, $url, $icon = '', $color = '#007bff', $image_url = null) {
    global $pdo;
    
    try {
        // Ottieni la posizione massima
        $stmt = $pdo->prepare("SELECT MAX(position) as max_pos FROM links WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = ($result['max_pos'] ?? 0) + 1;
        
        $stmt = $pdo->prepare("INSERT INTO links (user_id, title, url, icon, color, image_url, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $url, $icon, $color, $image_url ?: null, $position]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per aggiornare un link
function updateLink($link_id, $user_id, $title, $url, $icon = '', $color = '#007bff', $image_url = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE links SET title = ?, url = ?, icon = ?, color = ?, image_url = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $url, $icon, $color, $image_url ?: null, $link_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per eliminare un link
function deleteLink($link_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM links WHERE id = ? AND user_id = ?");
        $stmt->execute([$link_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per aggiornare il profilo utente
function updateProfile($user_id, $display_name, $bio, $avatar = null) {
    global $pdo;
    
    try {
        if ($avatar) {
            $stmt = $pdo->prepare("UPDATE users SET display_name = ?, bio = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$display_name, $bio, $avatar, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET display_name = ?, bio = ? WHERE id = ?");
            $stmt->execute([$display_name, $bio, $user_id]);
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Aggiorna impostazioni template e personalizzazione
function updateTemplateSettings($user_id, $settings) {
    global $pdo;
    
    try {
        $fields = [];
        $values = [];
        
        if (isset($settings['template'])) {
            $fields[] = 'template = ?';
            $values[] = $settings['template'];
        }
        
        if (isset($settings['background_type'])) {
            $fields[] = 'background_type = ?';
            $values[] = $settings['background_type'];
        }
        
        if (isset($settings['background_color'])) {
            $fields[] = 'background_color = ?';
            $values[] = $settings['background_color'];
        }
        
        if (isset($settings['background_gradient'])) {
            $fields[] = 'background_gradient = ?';
            $values[] = $settings['background_gradient'];
        }
        
        if (isset($settings['background_image'])) {
            $fields[] = 'background_image = ?';
            $values[] = $settings['background_image'];
        }
        
        if (isset($settings['text_color'])) {
            $fields[] = 'text_color = ?';
            $values[] = $settings['text_color'];
        }
        
        if (isset($settings['link_style'])) {
            $fields[] = 'link_style = ?';
            $values[] = $settings['link_style'];
        }
        
        if (isset($settings['link_color'])) {
            $fields[] = 'link_color = ?';
            $values[] = $settings['link_color'];
        }
        
        if (isset($settings['link_hover_color'])) {
            $fields[] = 'link_hover_color = ?';
            $values[] = $settings['link_hover_color'];
        }
        
        if (isset($settings['button_border_radius'])) {
            $fields[] = 'button_border_radius = ?';
            $values[] = (int)$settings['button_border_radius'];
        }
        
        if (isset($settings['button_shadow'])) {
            $fields[] = 'button_shadow = ?';
            $values[] = $settings['button_shadow'] ? 1 : 0;
        }
        
        if (isset($settings['font_family'])) {
            $fields[] = 'font_family = ?';
            $values[] = $settings['font_family'];
        }
        
        if (isset($settings['profile_layout'])) {
            $fields[] = 'profile_layout = ?';
            $values[] = $settings['profile_layout'];
        }
        
        if (isset($settings['show_social_icons'])) {
            $fields[] = 'show_social_icons = ?';
            $values[] = $settings['show_social_icons'] ? 1 : 0;
        }
        
        if (isset($settings['custom_css'])) {
            $fields[] = 'custom_css = ?';
            $values[] = $settings['custom_css'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $user_id;
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        return $stmt->execute($values);
    } catch (PDOException $e) {
        error_log("Errore updateTemplateSettings: " . $e->getMessage());
        return false;
    }
}

// Funzione per registrare un click su un link
function recordClick($user_id, $link_id, $ip_address, $user_agent, $referer = null) {
    global $pdo;
    
    try {
        // Registra l'analytics
        $stmt = $pdo->prepare("INSERT INTO analytics (user_id, link_id, ip_address, user_agent, referer) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $link_id, $ip_address, $user_agent, $referer]);
        
        // Incrementa il contatore dei click
        $stmt = $pdo->prepare("UPDATE links SET click_count = click_count + 1 WHERE id = ?");
        $stmt->execute([$link_id]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per ottenere le statistiche di un utente
function getUserStats($user_id) {
    global $pdo;
    
    try {
        // Totale click
        $stmt = $pdo->prepare("SELECT SUM(click_count) as total_clicks FROM links WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_clicks = $stmt->fetch(PDO::FETCH_ASSOC)['total_clicks'] ?? 0;
        
        // Click oggi
        $stmt = $pdo->prepare("SELECT COUNT(*) as today_clicks FROM analytics WHERE user_id = ? AND DATE(clicked_at) = CURDATE()");
        $stmt->execute([$user_id]);
        $today_clicks = $stmt->fetch(PDO::FETCH_ASSOC)['today_clicks'] ?? 0;
        
        // Link attivi
        $stmt = $pdo->prepare("SELECT COUNT(*) as active_links FROM links WHERE user_id = ? AND is_active = TRUE");
        $stmt->execute([$user_id]);
        $active_links = $stmt->fetch(PDO::FETCH_ASSOC)['active_links'] ?? 0;
        
        return [
            'total_clicks' => $total_clicks,
            'today_clicks' => $today_clicks,
            'active_links' => $active_links
        ];
    } catch (PDOException $e) {
        return [
            'total_clicks' => 0,
            'today_clicks' => 0,
            'active_links' => 0
        ];
    }
}

// Funzione per ottenere il profilo pubblico di un utente
function getPublicProfile($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, display_name, bio, avatar, theme, address, show_map, latitude, longitude, social_instagram, social_facebook, social_tiktok, social_twitter, social_linkedin, social_youtube, template, background_type, background_color, background_gradient, background_image, text_color, link_style, link_color, link_hover_color, button_border_radius, button_shadow, font_family, profile_layout, show_social_icons, custom_css FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user['links'] = getUserLinks($user['id']);
        }
        
        return $user;
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per riordinare i link
function reorderLinks($user_id, $link_orders) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        foreach ($link_orders as $link_id => $position) {
            $stmt = $pdo->prepare("UPDATE links SET position = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$position, $link_id, $user_id]);
        }
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

// ========== FUNZIONI PER LINK ACCORCIATI ==========

// Genera un codice breve unico
function generateShortCode($length = 6) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Crea un link accorciato
function createShortLink($user_id, $original_url, $title = '', $description = '', $custom_code = '') {
    global $pdo;
    
    try {
        // Se è stato fornito un codice personalizzato, controlla che sia unico
        if ($custom_code) {
            $stmt = $pdo->prepare("SELECT id FROM short_links WHERE short_code = ?");
            $stmt->execute([$custom_code]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Codice personalizzato già in uso'];
            }
            $short_code = $custom_code;
        } else {
            // Genera un codice unico
            do {
                $short_code = generateShortCode();
                $stmt = $pdo->prepare("SELECT id FROM short_links WHERE short_code = ?");
                $stmt->execute([$short_code]);
            } while ($stmt->fetch());
        }
        
        $stmt = $pdo->prepare("INSERT INTO short_links (user_id, original_url, short_code, title, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $original_url, $short_code, $title, $description]);
        
        return ['success' => true, 'short_code' => $short_code];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Errore durante la creazione del link: ' . $e->getMessage()];
    }
}

// Ottieni i link accorciati di un utente
function getUserShortLinks($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE user_id = ? AND is_active = TRUE ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Reindirizza un link accorciato
function redirectShortLink($short_code) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE short_code = ? AND is_active = TRUE");
        $stmt->execute([$short_code]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            return false;
        }
        
        // Controlla se il link è scaduto
        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
            return false;
        }
        
        // Incrementa il contatore dei click
        $stmt = $pdo->prepare("UPDATE short_links SET click_count = click_count + 1 WHERE id = ?");
        $stmt->execute([$link['id']]);
        
        // Registra il click per analytics
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Analizza user agent per device info
        $device_info = parseUserAgent($user_agent);
        
        $stmt = $pdo->prepare("INSERT INTO short_link_clicks (short_link_id, ip_address, user_agent, referer, country, city, device_type, browser, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $link['id'], 
            $ip_address, 
            $user_agent, 
            $referer,
            $device_info['country'],
            $device_info['city'],
            $device_info['device_type'],
            $device_info['browser'],
            $device_info['os']
        ]);
        
        return $link['original_url'];
    } catch (PDOException $e) {
        return false;
    }
}

// Analizza user agent per informazioni dettagliate
function parseUserAgent($user_agent) {
    $device_type = 'Desktop';
    $browser = 'Unknown';
    $os = 'Unknown';
    
    // Rileva dispositivo
    if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
        $device_type = 'Mobile';
    } elseif (preg_match('/Tablet|iPad/', $user_agent)) {
        $device_type = 'Tablet';
    }
    
    // Rileva browser
    if (preg_match('/Chrome/', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Firefox/', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Safari/', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Edge/', $user_agent)) {
        $browser = 'Edge';
    }
    
    // Rileva OS
    if (preg_match('/Windows/', $user_agent)) {
        $os = 'Windows';
    } elseif (preg_match('/Mac/', $user_agent)) {
        $os = 'macOS';
    } elseif (preg_match('/Linux/', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/Android/', $user_agent)) {
        $os = 'Android';
    } elseif (preg_match('/iPhone|iPad/', $user_agent)) {
        $os = 'iOS';
    }
    
    return [
        'device_type' => $device_type,
        'browser' => $browser,
        'os' => $os,
        'country' => 'Unknown', // Potresti integrare un servizio di geolocalizzazione
        'city' => 'Unknown'
    ];
}

// Ottieni statistiche dettagliate per un link accorciato
function getShortLinkStats($short_link_id) {
    global $pdo;
    
    try {
        // Statistiche generali
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE id = ?");
        $stmt->execute([$short_link_id]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            return false;
        }
        
        // Click per giorno (ultimi 30 giorni)
        $stmt = $pdo->prepare("
            SELECT DATE(clicked_at) as date, COUNT(*) as clicks 
            FROM short_link_clicks 
            WHERE short_link_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(clicked_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$short_link_id]);
        $daily_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Click per paese
        $stmt = $pdo->prepare("
            SELECT country, COUNT(*) as clicks 
            FROM short_link_clicks 
            WHERE short_link_id = ? 
            GROUP BY country 
            ORDER BY clicks DESC
        ");
        $stmt->execute([$short_link_id]);
        $country_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Click per dispositivo
        $stmt = $pdo->prepare("
            SELECT device_type, COUNT(*) as clicks 
            FROM short_link_clicks 
            WHERE short_link_id = ? 
            GROUP BY device_type 
            ORDER BY clicks DESC
        ");
        $stmt->execute([$short_link_id]);
        $device_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Click per browser
        $stmt = $pdo->prepare("
            SELECT browser, COUNT(*) as clicks 
            FROM short_link_clicks 
            WHERE short_link_id = ? 
            GROUP BY browser 
            ORDER BY clicks DESC
        ");
        $stmt->execute([$short_link_id]);
        $browser_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Click per ora del giorno
        $stmt = $pdo->prepare("
            SELECT HOUR(clicked_at) as hour, COUNT(*) as clicks 
            FROM short_link_clicks 
            WHERE short_link_id = ? 
            GROUP BY HOUR(clicked_at)
            ORDER BY hour ASC
        ");
        $stmt->execute([$short_link_id]);
        $hourly_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'link' => $link,
            'daily_clicks' => $daily_clicks,
            'country_clicks' => $country_clicks,
            'device_clicks' => $device_clicks,
            'browser_clicks' => $browser_clicks,
            'hourly_clicks' => $hourly_clicks
        ];
    } catch (PDOException $e) {
        return false;
    }
}

// Elimina un link accorciato
function deleteShortLink($short_link_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM short_links WHERE id = ? AND user_id = ?");
        $stmt->execute([$short_link_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Aggiorna indirizzo utente
function updateUserAddress($user_id, $address, $show_map, $latitude = null, $longitude = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET address = ?, show_map = ?, latitude = ?, longitude = ? WHERE id = ?");
        $stmt->execute([$address, $show_map, $latitude, $longitude, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Errore updateUserAddress: " . $e->getMessage());
        return false;
    }
}

// Geocodifica un indirizzo usando Nominatim (OpenStreetMap)
function geocodeAddress($address) {
    if (empty($address)) {
        return ['lat' => null, 'lng' => null];
    }
    
    $address = urlencode($address);
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
    
    // Imposta User-Agent per rispettare le policy di Nominatim
    $options = [
        'http' => [
            'header' => "User-Agent: VaiQui/1.0\r\n"
        ]
    ];
    $context = stream_context_create($options);
    
    try {
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['lat' => null, 'lng' => null];
        }
        
        $data = json_decode($response, true);
        
        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return [
                'lat' => (float)$data[0]['lat'],
                'lng' => (float)$data[0]['lon']
            ];
        }
    } catch (Exception $e) {
        error_log("Errore geocoding: " . $e->getMessage());
    }
    
    return ['lat' => null, 'lng' => null];
}

// ========== FUNZIONI PER AREA AMMINISTRATIVA ==========

// Verifica se un utente è admin
function isAdmin($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && ($user['role'] === 'admin');
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni tutti gli utenti (per admin)
function getAllUsers($limit = 100, $offset = 0, $search = '') {
    global $pdo;
    
    try {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        // Query base con LEFT JOIN per evitare problemi con subquery
        $query = "SELECT u.id, u.username, u.email, 
                         COALESCE(u.display_name, u.username) as display_name, 
                         COALESCE(u.role, 'user') as role, 
                         u.created_at,
                         COUNT(DISTINCT l.id) as link_count,
                         COALESCE(SUM(l.click_count), 0) as click_count
                  FROM users u
                  LEFT JOIN links l ON l.user_id = u.id";
        
        $params = [];
        if (!empty($search)) {
            $query .= " WHERE u.username LIKE ? OR u.email LIKE ? OR u.display_name LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $query .= " GROUP BY u.id, u.username, u.email, u.display_name, u.role, u.created_at";
        $query .= " ORDER BY u.created_at DESC LIMIT " . $limit . " OFFSET " . $offset;
        
        $stmt = $pdo->prepare($query);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Converti click_count a intero
        foreach ($result as &$user) {
            $user['click_count'] = (int)($user['click_count'] ?? 0);
            $user['link_count'] = (int)($user['link_count'] ?? 0);
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Errore getAllUsers: " . $e->getMessage());
        error_log("Query: " . $query);
        return [];
    }
}

// Conta totale utenti
function getTotalUsers($search = '') {
    global $pdo;
    
    try {
        $query = "SELECT COUNT(*) as total FROM users";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE username LIKE ? OR email LIKE ? OR display_name LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Aggiorna ruolo utente
function updateUserRole($user_id, $role) {
    global $pdo;
    
    try {
        $allowed_roles = ['user', 'admin'];
        if (!in_array($role, $allowed_roles)) {
            return false;
        }
        
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $user_id]);
    } catch (PDOException $e) {
        error_log("Errore updateUserRole: " . $e->getMessage());
        return false;
    }
}

// Elimina utente (con tutti i suoi dati correlati)
function deleteUser($user_id) {
    global $pdo;
    
    try {
        // Prima elimina le immagini caricate dall'utente
        $stmt = $pdo->prepare("SELECT image_url FROM links WHERE user_id = ? AND image_url IS NOT NULL AND image_url LIKE 'uploads/link_images/%'");
        $stmt->execute([$user_id]);
        $links_with_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($links_with_images as $link) {
            if (!empty($link['image_url'])) {
                $image_path = __DIR__ . '/../' . $link['image_url'];
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }
            }
        }
        
        // Elimina anche i QR code dell'utente
        $qr_dir = __DIR__ . '/../assets/qr_codes/';
        if (is_dir($qr_dir)) {
            $qr_pattern = $qr_dir . 'qr_' . $user_id . '_*';
            $qr_files = glob($qr_pattern);
            foreach ($qr_files as $qr_file) {
                @unlink($qr_file);
            }
        }
        
        // Le foreign key con ON DELETE CASCADE elimineranno automaticamente:
        // - links
        // - analytics
        // - short_links
        // - etc.
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            error_log("Utente ID $user_id eliminato con successo");
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Errore deleteUser: " . $e->getMessage());
        return false;
    }
}

// Ottieni statistiche generali per admin
function getAdminStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Totale utenti
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Utenti attivi (con almeno un link)
        $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM links");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Totale link
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM links");
        $stats['total_links'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Totale click
        $stmt = $pdo->query("SELECT SUM(click_count) as total FROM links");
        $stats['total_clicks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Utenti registrati oggi
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
        $stats['users_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Errore getAdminStats: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_users' => 0,
            'total_links' => 0,
            'total_clicks' => 0,
            'users_today' => 0
        ];
    }
}

// ========== FUNZIONI PER VERIFICA EMAIL ==========

// Invia email di verifica
function sendVerificationEmail($user_id, $email, $token) {
    global $pdo;
    
    try {
        // Ottieni username per personalizzare l'email
        $stmt = $pdo->prepare("SELECT username, display_name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $username = $user['display_name'] ?? $user['username'];
        
        // Costruisci URL di verifica
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base_url = $scheme . '://' . $_SERVER['HTTP_HOST'];
        $verify_url = $base_url . '/verify_email.php?token=' . urlencode($token);
        
        // Soggetto email
        $subject = "Verifica il tuo account VaiQui";
        
        // Corpo email HTML
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Benvenuto su VaiQui!</h1>
                </div>
                <div class='content'>
                    <p>Ciao <strong>{$username}</strong>,</p>
                    <p>Grazie per esserti registrato su VaiQui! Per completare la registrazione, verifica il tuo indirizzo email cliccando sul pulsante qui sotto:</p>
                    <p style='text-align: center;'>
                        <a href='{$verify_url}' class='button'>Verifica Email</a>
                    </p>
                    <p>Oppure copia e incolla questo link nel tuo browser:</p>
                    <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>{$verify_url}</p>
                    <p><strong>Nota:</strong> Questo link scadrà tra 24 ore.</p>
                    <p>Se non hai creato un account su VaiQui, ignora questa email.</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " VaiQui - Il tuo Linktree personale</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Headers email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: VaiQui <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
        $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        
        // Invia email
        $sent = mail($email, $subject, $message, $headers);
        
        if (!$sent) {
            error_log("Errore invio email verifica a: $email");
        }
        
        return $sent;
    } catch (Exception $e) {
        error_log("Errore sendVerificationEmail: " . $e->getMessage());
        return false;
    }
}

// Verifica token email
function verifyEmailToken($token) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, email, verification_token_expires FROM users WHERE verification_token = ? AND email_verified = FALSE");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token non valido o email già verificata'];
        }
        
        // Verifica scadenza token
        if (strtotime($user['verification_token_expires']) < time()) {
            return ['success' => false, 'message' => 'Token scaduto. Richiedi un nuovo link di verifica.'];
        }
        
        // Aggiorna utente come verificato
        $stmt = $pdo->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL, verification_token_expires = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return ['success' => true, 'user_id' => $user['id'], 'email' => $user['email']];
    } catch (PDOException $e) {
        error_log("Errore verifyEmailToken: " . $e->getMessage());
        return ['success' => false, 'message' => 'Errore durante la verifica'];
    }
}

// Rigenera token di verifica e reinvia email
function resendVerificationEmail($user_id) {
    global $pdo;
    
    try {
        // Verifica che l'utente esista e non sia già verificato
        $stmt = $pdo->prepare("SELECT id, email, username, display_name, email_verified FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utente non trovato'];
        }
        
        if ($user['email_verified']) {
            return ['success' => false, 'message' => 'Email già verificata'];
        }
        
        // Genera nuovo token
        $verification_token = bin2hex(random_bytes(32));
        $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Aggiorna token nel database
        $stmt = $pdo->prepare("UPDATE users SET verification_token = ?, verification_token_expires = ? WHERE id = ?");
        $stmt->execute([$verification_token, $verification_expires, $user_id]);
        
        // Invia email
        $sent = sendVerificationEmail($user_id, $user['email'], $verification_token);
        
        if ($sent) {
            return ['success' => true, 'message' => 'Email di verifica inviata con successo'];
        } else {
            return ['success' => false, 'message' => 'Errore durante l\'invio dell\'email'];
        }
    } catch (PDOException $e) {
        error_log("Errore resendVerificationEmail: " . $e->getMessage());
        return ['success' => false, 'message' => 'Errore durante la rigenerazione del token'];
    }
}
?>
