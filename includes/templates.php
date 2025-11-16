<?php
// Sistema di template per i profili

// Template disponibili
function getAvailableTemplates() {
    return [
        'default' => [
            'name' => 'Default',
            'description' => 'Template classico e pulito',
            'preview' => '#667eea'
        ],
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Design minimalista e moderno',
            'preview' => '#2d3748'
        ],
        'gradient' => [
            'name' => 'Gradient',
            'description' => 'Sfondo con gradienti colorati',
            'preview' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
        ],
        'dark' => [
            'name' => 'Dark',
            'description' => 'Tema scuro elegante',
            'preview' => '#1a1a1a'
        ],
        'light' => [
            'name' => 'Light',
            'description' => 'Tema chiaro e luminoso',
            'preview' => '#ffffff'
        ],
        'neon' => [
            'name' => 'Neon',
            'description' => 'Stile neon vibrante',
            'preview' => '#0a0a0a'
        ],
        'ocean' => [
            'name' => 'Ocean',
            'description' => 'Tema ispirato all\'oceano',
            'preview' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
        ],
        'sunset' => [
            'name' => 'Sunset',
            'description' => 'Colori del tramonto',
            'preview' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
        ],
        'forest' => [
            'name' => 'Forest',
            'description' => 'Tema naturale verde',
            'preview' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
        ],
        'royal' => [
            'name' => 'Royal',
            'description' => 'Stile regale e lussuoso',
            'preview' => 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)'
        ],
        'modern' => [
            'name' => 'Modern',
            'description' => 'Design moderno e pulito',
            'preview' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
        ],
        'vibrant' => [
            'name' => 'Vibrant',
            'description' => 'Colori vivaci e accattivanti',
            'preview' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
        ],
        'professional' => [
            'name' => 'Professional',
            'description' => 'Stile professionale per business',
            'preview' => '#2d3748'
        ],
        'creative' => [
            'name' => 'Creative',
            'description' => 'Perfetto per creatori e artisti',
            'preview' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
        ],
        'elegant' => [
            'name' => 'Elegant',
            'description' => 'Design elegante e raffinato',
            'preview' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)'
        ],
        'bold' => [
            'name' => 'Bold',
            'description' => 'Stile audace e impattante',
            'preview' => 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)'
        ],
        'soft' => [
            'name' => 'Soft',
            'description' => 'Toni morbidi e delicati',
            'preview' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
        ]
    ];
}

// Stili link disponibili
function getAvailableLinkStyles() {
    return [
        'card' => [
            'name' => 'Card',
            'description' => 'Link come card rettangolari'
        ],
        'rounded' => [
            'name' => 'Rounded',
            'description' => 'Link con bordi arrotondati'
        ],
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Link minimalisti'
        ],
        'glass' => [
            'name' => 'Glass',
            'description' => 'Effetto glassmorphism'
        ],
        'neon' => [
            'name' => 'Neon',
            'description' => 'Bordi neon luminosi'
        ]
    ];
}

// Genera CSS per template
function getTemplateCSS($template, $user_settings) {
    $css = '';
    
    // Background
    $background_type = $user_settings['background_type'] ?? 'gradient';
    $background_css = '';
    
    if ($background_type === 'image' && !empty($user_settings['background_image'])) {
        $background_css = "background-image: url('" . htmlspecialchars($user_settings['background_image']) . "'); background-size: cover; background-position: center; background-attachment: fixed;";
    } elseif ($background_type === 'color') {
        $background_css = "background-color: " . ($user_settings['background_color'] ?? '#667eea') . ";";
    } else {
        $gradient = $user_settings['background_gradient'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $background_css = "background: " . $gradient . ";";
    }
    
    $text_color = $user_settings['text_color'] ?? '#ffffff';
    $link_style = $user_settings['link_style'] ?? 'card';
    
    // Template specifico
    switch ($template) {
        case 'minimal':
            $css .= "
                body { background: #f8f9fa; color: #1a1a1a; }
                .profile-container { background: white; border-radius: 0; box-shadow: none; }
                .profile-header { border-bottom: 1px solid #e5e7eb; }
            ";
            break;
            
        case 'dark':
            $css .= "
                body { background: #0a0a0a; color: #ffffff; }
                .profile-container { background: #1a1a1a; border: 1px solid #333; }
                .link-item { background: #2a2a2a; border: 1px solid #444; }
                .link-item:hover { background: #3a3a3a; }
            ";
            break;
            
        case 'light':
            $css .= "
                body { background: #ffffff; color: #1a1a1a; }
                .profile-container { background: #f8f9fa; border: 1px solid #e5e7eb; }
                .link-item { background: white; border: 1px solid #e5e7eb; }
            ";
            break;
            
        case 'neon':
            $css .= "
                body { background: #0a0a0a; color: #00ff88; }
                .profile-container { background: rgba(0, 0, 0, 0.8); border: 2px solid #00ff88; box-shadow: 0 0 20px rgba(0, 255, 136, 0.3); }
                .link-item { background: rgba(0, 255, 136, 0.1); border: 2px solid #00ff88; }
                .link-item:hover { box-shadow: 0 0 15px rgba(0, 255, 136, 0.5); }
            ";
            break;
            
        case 'ocean':
            $background_css = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
            break;
            
        case 'sunset':
            $background_css = "background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);";
            break;
            
        case 'forest':
            $background_css = "background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);";
            break;
            
        case 'royal':
            $background_css = "background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);";
            break;
    }
    
    // CSS base con background personalizzato
    $css .= "
        body { {$background_css} color: {$text_color}; }
    ";
    
    // Font family
    $font_family = $user_settings['font_family'] ?? 'system';
    $font_css = '';
    switch ($font_family) {
        case 'system':
            $font_css = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            break;
        case 'google':
            $font_css = '"Google Sans", sans-serif';
            break;
        case 'inter':
            $font_css = '"Inter", sans-serif';
            break;
        case 'poppins':
            $font_css = '"Poppins", sans-serif';
            break;
        case 'montserrat':
            $font_css = '"Montserrat", sans-serif';
            break;
        case 'roboto':
            $font_css = '"Roboto", sans-serif';
            break;
        default:
            $font_css = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    }
    $css .= "body { font-family: {$font_css}; }";
    
    // Border radius
    $border_radius = $user_settings['button_border_radius'] ?? 12;
    $css .= ".link-item { border-radius: {$border_radius}px; }";
    
    // Button shadow
    $button_shadow = $user_settings['button_shadow'] ?? true;
    if (!$button_shadow) {
        $css .= ".link-item { box-shadow: none !important; }";
    }
    
    // Link colors
    $link_color = $user_settings['link_color'] ?? null;
    $link_hover_color = $user_settings['link_hover_color'] ?? null;
    if ($link_color) {
        $css .= ".link-item { background-color: {$link_color}; }";
    }
    if ($link_hover_color) {
        $css .= ".link-item:hover { background-color: {$link_hover_color}; }";
    }
    
    // Profile layout
    $profile_layout = $user_settings['profile_layout'] ?? 'centered';
    switch ($profile_layout) {
        case 'left':
            $css .= ".profile-container { text-align: left; }";
            break;
        case 'right':
            $css .= ".profile-container { text-align: right; }";
            break;
        case 'centered':
        default:
            $css .= ".profile-container { text-align: center; }";
            break;
    }
    
    // Stili link
    switch ($link_style) {
        case 'rounded':
            $css .= "
                .link-item { border-radius: 20px; }
            ";
            break;
            
        case 'minimal':
            $css .= "
                .link-item { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); }
            ";
            break;
            
        case 'glass':
            $css .= "
                .link-item { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); }
            ";
            break;
            
        case 'neon':
            $css .= "
                .link-item { border: 2px solid {$text_color}; box-shadow: 0 0 10px rgba(102, 126, 234, 0.5); }
                .link-item:hover { box-shadow: 0 0 20px rgba(102, 126, 234, 0.8); }
            ";
            break;
    }
    
    // Custom CSS
    if (!empty($user_settings['custom_css'])) {
        $css .= "\n" . $user_settings['custom_css'];
    }
    
    return $css;
}

// Gradienti predefiniti
function getPresetGradients() {
    return [
        'purple' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'blue' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'pink' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'green' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'orange' => 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)',
        'dark' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
        'sunset' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'ocean' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'forest' => 'linear-gradient(135deg, #134e5e 0%, #71b280 100%)',
        'royal' => 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)'
    ];
}

?>

