<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/templates.php';

// Ottieni l'username dall'URL
$username = $_GET['user'] ?? '';

if (empty($username)) {
    header('HTTP/1.0 404 Not Found');
    exit('Profilo non trovato');
}

// Ottieni i dati del profilo pubblico
$profile = getPublicProfile($username);

if (!$profile) {
    header('HTTP/1.0 404 Not Found');
    exit('Profilo non trovato');
}

// Gestione click sui link
if (isset($_GET['click']) && is_numeric($_GET['click'])) {
    $link_id = $_GET['click'];
    
    // Verifica che il link appartenga all'utente
    $link_belongs_to_user = false;
    foreach ($profile['links'] as $link) {
        if ($link['id'] == $link_id) {
            $link_belongs_to_user = true;
            break;
        }
    }
    
    if ($link_belongs_to_user) {
        // Registra il click
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        recordClick($profile['id'], $link_id, $ip_address, $user_agent, $referer);
        
        // Reindirizza al link
        $link_url = $profile['links'][array_search($link_id, array_column($profile['links'], 'id'))]['url'];
        header('Location: ' . $link_url);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['display_name'] ?? $profile['username']); ?> - VaiQui</title>
    <meta name="description" content="<?php echo htmlspecialchars($profile['bio'] ?? 'Profilo su VaiQui'); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($profile['display_name'] ?? $profile['username']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($profile['bio'] ?? 'Profilo su VaiQui'); ?>">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700&family=Playfair+Display:wght@400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <?php
    // Ottieni le personalizzazioni del profilo
    $template = $profile['template'] ?? 'default';
    $user_settings = [
        'template' => $template,
        'background_type' => $profile['background_type'] ?? 'gradient',
        'background_color' => $profile['background_color'] ?? '#667eea',
        'background_gradient' => $profile['background_gradient'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'background_image' => $profile['background_image'] ?? null,
        'text_color' => $profile['text_color'] ?? '#ffffff',
        'link_style' => $profile['link_style'] ?? 'card',
        'link_color' => $profile['link_color'] ?? null,
        'link_hover_color' => $profile['link_hover_color'] ?? null,
        'button_border_radius' => $profile['button_border_radius'] ?? 12,
        'button_shadow' => $profile['button_shadow'] ?? true,
        'font_family' => $profile['font_family'] ?? 'system',
        'profile_layout' => $profile['profile_layout'] ?? 'centered',
        'show_social_icons' => $profile['show_social_icons'] ?? true,
        'custom_css' => $profile['custom_css'] ?? null
    ];
    
    $template_css = getTemplateCSS($template, $user_settings);
    ?>
    
    <style>
        <?php echo $template_css; ?>
        
        /* Stili aggiuntivi per il profilo */
        .profile-page {
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
        }
        
        .profile-avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .profile-bio {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .profile-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .profile-link {
            display: block;
            padding: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .profile-link-image {
            min-height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .profile-link-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .profile-link-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .profile-link-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-page theme-<?php echo htmlspecialchars($profile['theme']); ?>">
            <div class="profile-header">
                <?php if ($profile['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($profile['avatar']); ?>" 
                         alt="Avatar" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($profile['display_name'] ?? $profile['username']); ?>
                </h1>
                
                <?php if ($profile['bio']): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="profile-links">
                <?php if (empty($profile['links'])): ?>
                    <div class="empty-profile">
                        <i class="fas fa-link"></i>
                        <h3>Nessun link disponibile</h3>
                        <p>Questo profilo non ha ancora condiviso nessun link.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($profile['links'] as $link): ?>
                        <?php $has_image = !empty($link['image_url']); ?>
                        <a href="profile.php?user=<?php echo htmlspecialchars($username); ?>&click=<?php echo $link['id']; ?>" 
                           class="profile-link<?php echo $has_image ? ' profile-link-image' : ''; ?>"
                           <?php if ($has_image): ?>style="background-image: url('<?php echo htmlspecialchars($link['image_url']); ?>');"<?php endif; ?>>
                            <?php if ($has_image): ?>
                                <div class="profile-link-image-overlay">
                                    <div class="profile-link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                        <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                    </div>
                                    <div class="profile-link-title">
                                        <?php echo htmlspecialchars($link['title']); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="profile-link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                    <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                </div>
                                <div class="profile-link-title">
                                    <?php echo htmlspecialchars($link['title']); ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($profile['address']) && $profile['show_map']): ?>
                <div class="profile-address">
                    <h3><i class="fas fa-map-marker-alt"></i> Dove Trovarci</h3>
                    <p class="address-text"><?php echo nl2br(htmlspecialchars($profile['address'])); ?></p>
                    
                    <?php if (!empty($profile['latitude']) && !empty($profile['longitude'])): ?>
                        <div id="map" class="profile-map"></div>
                    <?php endif; ?>
                </div>
            <?php elseif (!empty($profile['address'])): ?>
                <!-- Debug: indirizzo presente ma mappa disattivata -->
                <div class="profile-address" style="background: #fff3cd; border: 1px solid #ffc107;">
                    <h3><i class="fas fa-map-marker-alt"></i> Dove Trovarci</h3>
                    <p class="address-text"><?php echo nl2br(htmlspecialchars($profile['address'])); ?></p>
                    <small style="color: #856404;">
                        <i class="fas fa-info-circle"></i> 
                        Mappa disattivata nelle impostazioni
                    </small>
                </div>
            <?php endif; ?>
            
            <?php
                $social_links = [];
                $social_config = [
                    'social_instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
                    'social_facebook'  => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
                    'social_tiktok'    => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
                    'social_twitter'   => ['label' => 'Twitter', 'icon' => 'fab fa-x-twitter'],
                    'social_linkedin'  => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
                    'social_youtube'   => ['label' => 'YouTube', 'icon' => 'fab fa-youtube']
                ];

                foreach ($social_config as $key => $meta) {
                    if (!empty($profile[$key])) {
                        $social_links[] = [
                            'url' => $profile[$key],
                            'label' => $meta['label'],
                            'icon' => $meta['icon']
                        ];
                    }
                }
            ?>

            <?php if (!empty($social_links)): ?>
                <div class="profile-social">
                    <h3><i class="fas fa-share-alt"></i> Seguimi sui social</h3>
                    <div class="social-links">
                        <?php foreach ($social_links as $social): ?>
                            <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" rel="noopener" class="social-link">
                                <i class="<?php echo $social['icon']; ?>"></i>
                                <span><?php echo htmlspecialchars($social['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['debug'])): ?>
                <!-- Debug info -->
                <div style="background: #f8f9fa; padding: 15px; margin: 20px; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                    <strong>Debug Info:</strong><br>
                    Address: <?php echo htmlspecialchars($profile['address'] ?? 'NULL'); ?><br>
                    Show Map: <?php echo ($profile['show_map'] ?? false) ? 'TRUE' : 'FALSE'; ?><br>
                    Latitude: <?php echo $profile['latitude'] ?? 'NULL'; ?><br>
                    Longitude: <?php echo $profile['longitude'] ?? 'NULL'; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-footer">
                <p>Creato con <i class="fas fa-heart" style="color: #e74c3c;"></i> su VaiQui</p>
            </div>
        </div>
    </div>
    
    <?php if (!empty($profile['latitude']) && !empty($profile['longitude']) && $profile['show_map']): ?>
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        
        <script>
            // Inizializza la mappa
            document.addEventListener('DOMContentLoaded', function() {
                const lat = <?php echo $profile['latitude']; ?>;
                const lng = <?php echo $profile['longitude']; ?>;
                
                const map = L.map('map').setView([lat, lng], 15);
                
                // Tile layer di OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);
                
                // Aggiungi marker
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('<?php echo htmlspecialchars($profile['display_name'] ?? $profile['username']); ?>');
            });
        </script>
    <?php endif; ?>

    <script>
        // Analytics per i click
        document.querySelectorAll('.profile-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Invia analytics (opzionale)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'click', {
                        'event_category': 'link',
                        'event_label': this.querySelector('.profile-link-title').textContent
                    });
                }
            });
        });
        
        // Animazione di caricamento
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.profile-link');
            links.forEach((link, index) => {
                link.style.animationDelay = (index * 0.1) + 's';
                link.classList.add('fade-in-up');
            });
        });
    </script>
    
    <style>
        .profile-avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            font-size: 2rem;
        }
        
        .empty-profile {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-profile i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .profile-footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 0.9rem;
        }

        .profile-social {
            margin: 30px 20px;
            padding: 20px;
            background: rgba(255,255,255,0.15);
            border-radius: 15px;
            backdrop-filter: blur(6px);
        }

        .profile-social h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .social-links {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }

        .social-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            background: rgba(0,0,0,0.2);
            color: inherit;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .social-link i {
            font-size: 1.2rem;
        }

        .social-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            color: inherit;
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>
