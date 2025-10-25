<?php
require_once 'config/database.php';

// ========== FUNZIONI PER PERSONALIZZAZIONE PROFILO ==========

// Salva le impostazioni di personalizzazione
function saveProfileCustomization($user_id, $customization_data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                theme = ?, 
                custom_css = ?, 
                background_image = ?, 
                button_style = ?, 
                font_family = ?,
                primary_color = ?,
                secondary_color = ?,
                text_color = ?,
                background_color = ?,
                button_color = ?,
                button_text_color = ?,
                border_radius = ?,
                shadow_style = ?,
                animation_style = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $customization_data['theme'],
            $customization_data['custom_css'],
            $customization_data['background_image'],
            $customization_data['button_style'],
            $customization_data['font_family'],
            $customization_data['primary_color'],
            $customization_data['secondary_color'],
            $customization_data['text_color'],
            $customization_data['background_color'],
            $customization_data['button_color'],
            $customization_data['button_text_color'],
            $customization_data['border_radius'],
            $customization_data['shadow_style'],
            $customization_data['animation_style'],
            $user_id
        ]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Ottieni le impostazioni di personalizzazione
function getProfileCustomization($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT theme, custom_css, background_image, button_style, font_family,
                   primary_color, secondary_color, text_color, background_color,
                   button_color, button_text_color, border_radius, shadow_style, animation_style
            FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Genera CSS personalizzato per il profilo
function generateCustomCSS($customization) {
    $css = "
    <style>
    .profile-page {
        font-family: " . ($customization['font_family'] ?: 'inherit') . ";
        background: " . ($customization['background_color'] ?: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') . ";
        color: " . ($customization['text_color'] ?: 'white') . ";
    }
    
    .profile-header {
        background: " . ($customization['primary_color'] ?: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') . ";
    }
    
    .profile-link {
        background: " . ($customization['button_color'] ?: '#f8f9fa') . ";
        color: " . ($customization['button_text_color'] ?: '#333') . ";
        border-radius: " . ($customization['border_radius'] ?: '12px') . "px;
    }
    
    .profile-link:hover {
        transform: translateY(-" . ($customization['animation_style'] === 'subtle' ? '2px' : '3px') . ");
        box-shadow: " . ($customization['shadow_style'] === 'strong' ? '0 8px 25px rgba(0,0,0,0.15)' : '0 5px 15px rgba(0,0,0,0.1)') . ";
    }
    ";
    
    if ($customization['custom_css']) {
        $css .= "\n" . $customization['custom_css'];
    }
    
    $css .= "\n</style>";
    
    return $css;
}

// Temi predefiniti
function getAvailableThemes() {
    return [
        'default' => [
            'name' => 'Default',
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'background_color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'text_color' => 'white',
            'button_color' => '#f8f9fa',
            'button_text_color' => '#333'
        ],
        'dark' => [
            'name' => 'Dark',
            'primary_color' => '#2c3e50',
            'secondary_color' => '#34495e',
            'background_color' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
            'text_color' => 'white',
            'button_color' => '#ecf0f1',
            'button_text_color' => '#2c3e50'
        ],
        'minimal' => [
            'name' => 'Minimal',
            'primary_color' => '#ffffff',
            'secondary_color' => '#f8f9fa',
            'background_color' => '#ffffff',
            'text_color' => '#333',
            'button_color' => '#f8f9fa',
            'button_text_color' => '#333'
        ],
        'colorful' => [
            'name' => 'Colorful',
            'primary_color' => '#e74c3c',
            'secondary_color' => '#f39c12',
            'background_color' => 'linear-gradient(135deg, #e74c3c 0%, #f39c12 100%)',
            'text_color' => 'white',
            'button_color' => '#ecf0f1',
            'button_text_color' => '#2c3e50'
        ],
        'ocean' => [
            'name' => 'Ocean',
            'primary_color' => '#3498db',
            'secondary_color' => '#2980b9',
            'background_color' => 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)',
            'text_color' => 'white',
            'button_color' => '#ecf0f1',
            'button_text_color' => '#2c3e50'
        ],
        'forest' => [
            'name' => 'Forest',
            'primary_color' => '#27ae60',
            'secondary_color' => '#2ecc71',
            'background_color' => 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)',
            'text_color' => 'white',
            'button_color' => '#ecf0f1',
            'button_text_color' => '#2c3e50'
        ]
    ];
}

// Stili bottoni predefiniti
function getAvailableButtonStyles() {
    return [
        'default' => 'Default',
        'rounded' => 'Rounded',
        'square' => 'Square',
        'pill' => 'Pill',
        'outline' => 'Outline',
        'gradient' => 'Gradient'
    ];
}

// Font predefiniti
function getAvailableFonts() {
    return [
        'default' => 'System Default',
        'roboto' => 'Roboto',
        'opensans' => 'Open Sans',
        'lato' => 'Lato',
        'montserrat' => 'Montserrat',
        'poppins' => 'Poppins',
        'nunito' => 'Nunito',
        'playfair' => 'Playfair Display',
        'merriweather' => 'Merriweather'
    ];
}

// Aggiorna la tabella users per supportare la personalizzazione
function updateUsersTableForCustomization() {
    global $pdo;
    
    try {
        // Aggiungi colonne per personalizzazione se non esistono
        $pdo->exec("ALTER TABLE users ADD COLUMN primary_color VARCHAR(7) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN secondary_color VARCHAR(7) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN text_color VARCHAR(7) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN background_color VARCHAR(100) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN button_color VARCHAR(7) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN button_text_color VARCHAR(7) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN border_radius INT DEFAULT 12");
        $pdo->exec("ALTER TABLE users ADD COLUMN shadow_style VARCHAR(20) DEFAULT 'subtle'");
        $pdo->exec("ALTER TABLE users ADD COLUMN animation_style VARCHAR(20) DEFAULT 'subtle'");
    } catch (PDOException $e) {
        // Le colonne potrebbero già esistere
    }
}

// Inizializza le modifiche alla tabella
updateUsersTableForCustomization();
?>
