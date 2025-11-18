<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/qr_generator.php';
require_once 'includes/templates.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// === Configurazione upload immagini link ===
const LINK_IMAGE_DIR = __DIR__ . '/uploads/link_images/';
const LINK_IMAGE_BASE_PATH = 'uploads/link_images/';
const LINK_IMAGE_MAX_SIZE = 500 * 1024; // 500KB
const LINK_IMAGE_ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

if (!is_dir(LINK_IMAGE_DIR)) {
    @mkdir(LINK_IMAGE_DIR, 0755, true);
}

function isLocalLinkImage(string $path = null): bool {
    if (!$path) {
        return false;
    }
    return strpos($path, LINK_IMAGE_BASE_PATH) === 0;
}

function deleteLinkImageIfLocal(?string $path): void {
    if ($path && isLocalLinkImage($path)) {
        $abs = LINK_IMAGE_DIR . basename($path);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}

function handleLinkImageUpload(array $file, int $userId): array {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => null];
    }

    if (($file['size'] ?? 0) > LINK_IMAGE_MAX_SIZE) {
        return ['path' => null, 'error' => 'Immagine troppo grande (max 500KB)'];
    }

    $tmpName = $file['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName) ?: '';
    finfo_close($finfo);

    if (!isset(LINK_IMAGE_ALLOWED_MIME[$mime])) {
        return ['path' => null, 'error' => 'Formato immagine non valido (solo JPG o PNG)'];
    }

    $extension = LINK_IMAGE_ALLOWED_MIME[$mime];
    $filename = 'link_' . $userId . '_' . uniqid('', true) . '.' . $extension;
    $destination = LINK_IMAGE_DIR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        return ['path' => null, 'error' => 'Errore durante il salvataggio dell\'immagine'];
    }

    return ['path' => LINK_IMAGE_BASE_PATH . $filename, 'error' => null];
}

$user = getUser($_SESSION['user_id']);
$links = getUserLinks($_SESSION['user_id']);
$stats = getUserStats($_SESSION['user_id']);
$short_links = getUserShortLinks($_SESSION['user_id']);
$total_short_links = count($short_links);
$total_short_clicks = 0;
foreach ($short_links as $short_link) {
    $total_short_clicks += $short_link['click_count'];
}

$active_tab = $_GET['tab'] ?? 'overview';

// Gestione delle azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_link':
                $title = trim($_POST['title']);
                $url = trim($_POST['url']);
                $icon = trim($_POST['icon']);
                $color = $_POST['color'];
                $image_url_input = trim($_POST['image_url'] ?? '');
                $image_url = $image_url_input !== '' ? $image_url_input : null;

                $uploadResult = handleLinkImageUpload($_FILES['image_file'] ?? [], $_SESSION['user_id']);
                if ($uploadResult['error']) {
                    $error = $uploadResult['error'];
                    break;
                }
                if ($uploadResult['path']) {
                    $image_url = $uploadResult['path'];
                }
                
                if (addLink($_SESSION['user_id'], $title, $url, $icon, $color, $image_url)) {
                    $success = "Link aggiunto con successo!";
                } else {
                    if ($uploadResult['path']) {
                        deleteLinkImageIfLocal($uploadResult['path']);
                    }
                    $error = "Errore durante l'aggiunta del link";
                }
                break;
                
            case 'update_link':
                $link_id = $_POST['link_id'];
                $title = trim($_POST['title']);
                $url = trim($_POST['url']);
                $icon = trim($_POST['icon']);
                $color = $_POST['color'];
                $image_url_input = trim($_POST['image_url'] ?? '');
                $remove_image_requested = ($_POST['remove_image'] ?? '0') === '1';

                $stmt = $pdo->prepare("SELECT image_url FROM links WHERE id = ? AND user_id = ?");
                $stmt->execute([$link_id, $_SESSION['user_id']]);
                $currentLink = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$currentLink) {
                    $error = "Link non trovato";
                    break;
                }

                $currentImage = $currentLink['image_url'] ?? null;
                $finalImageUrl = $currentImage;

                $uploadResult = handleLinkImageUpload($_FILES['image_file'] ?? [], $_SESSION['user_id']);
                if ($uploadResult['error']) {
                    $error = $uploadResult['error'];
                    break;
                }

                if ($uploadResult['path']) {
                    if ($currentImage && $uploadResult['path'] !== $currentImage) {
                        deleteLinkImageIfLocal($currentImage);
                    }
                    $finalImageUrl = $uploadResult['path'];
                    $remove_image_requested = false;
                } else {
                    if ($image_url_input !== '') {
                        if ($currentImage && $image_url_input !== $currentImage) {
                            deleteLinkImageIfLocal($currentImage);
                        }
                        $finalImageUrl = $image_url_input;
                        $remove_image_requested = false;
                    } elseif ($remove_image_requested) {
                        deleteLinkImageIfLocal($currentImage);
                        $finalImageUrl = null;
                    }
                }

                if (updateLink($link_id, $_SESSION['user_id'], $title, $url, $icon, $color, $finalImageUrl)) {
                    $success = "Link aggiornato con successo!";
                } else {
                    if ($uploadResult['path']) {
                        deleteLinkImageIfLocal($uploadResult['path']);
                    }
                    $error = "Errore durante l'aggiornamento del link";
                }
                break;
                
            case 'delete_link':
                $link_id = $_POST['link_id'];
                $stmt = $pdo->prepare("SELECT image_url FROM links WHERE id = ? AND user_id = ?");
                $stmt->execute([$link_id, $_SESSION['user_id']]);
                $linkData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (deleteLink($link_id, $_SESSION['user_id'])) {
                    if ($linkData && !empty($linkData['image_url'])) {
                        deleteLinkImageIfLocal($linkData['image_url']);
                    }
                    $success = "Link eliminato con successo!";
                } else {
                    $error = "Errore durante l'eliminazione del link";
                }
                break;
                
            case 'update_address':
                $address = trim($_POST['address']);
                $show_map = isset($_POST['show_map']) ? 1 : 0;
                
                // Geocodifica l'indirizzo per ottenere latitudine e longitudine
                $coords = geocodeAddress($address);
                
                if (updateUserAddress($_SESSION['user_id'], $address, $show_map, $coords['lat'] ?? null, $coords['lng'] ?? null)) {
                    $success = "Indirizzo aggiornato con successo!";
                    $user = getUser($_SESSION['user_id']); // Ricarica i dati utente
                } else {
                    $error = "Errore durante l'aggiornamento dell'indirizzo";
                }
                break;
                
            case 'update_profile':
                $display_name = trim($_POST['display_name']);
                $bio = trim($_POST['bio']);
                
                if (updateProfile($_SESSION['user_id'], $display_name, $bio)) {
                    $success = "Profilo aggiornato con successo!";
                } else {
                    $error = "Errore durante l'aggiornamento del profilo";
                }
                break;

            case 'update_social':
                $social_fields = [
                    'social_instagram',
                    'social_facebook',
                    'social_tiktok',
                    'social_twitter',
                    'social_linkedin',
                    'social_youtube'
                ];

                $update_pairs = [];
                $values = [];

                foreach ($social_fields as $field) {
                    $value = trim($_POST[$field] ?? '');
                    $update_pairs[] = "$field = ?";
                    $values[] = $value ?: null;
                }

                $values[] = $_SESSION['user_id'];

                $query = "UPDATE users SET " . implode(', ', $update_pairs) . " WHERE id = ?";
                $stmt = $pdo->prepare($query);

                if ($stmt->execute($values)) {
                    $success = "Social aggiornati con successo!";
                } else {
                    $error = "Errore durante l'aggiornamento dei social";
                }
                break;

            case 'resend_verification':
                $result = resendVerificationEmail($_SESSION['user_id']);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;

            case 'update_template_settings':
                require_once 'includes/templates.php';
                $active_tab = 'customize';
                
                $settings = [];
                if (isset($_POST['template'])) {
                    $settings['template'] = $_POST['template'];
                }
                if (isset($_POST['background_type'])) {
                    $settings['background_type'] = $_POST['background_type'];
                }
                if (isset($_POST['background_color'])) {
                    $settings['background_color'] = $_POST['background_color'];
                }
                if (isset($_POST['background_gradient'])) {
                    $settings['background_gradient'] = $_POST['background_gradient'];
                }
                if (isset($_POST['background_image'])) {
                    $settings['background_image'] = $_POST['background_image'];
                }
                if (isset($_POST['text_color'])) {
                    $settings['text_color'] = $_POST['text_color'];
                }
                if (isset($_POST['link_style'])) {
                    $settings['link_style'] = $_POST['link_style'];
                }
                if (isset($_POST['link_color'])) {
                    $settings['link_color'] = $_POST['link_color'];
                }
                if (isset($_POST['link_hover_color'])) {
                    $settings['link_hover_color'] = $_POST['link_hover_color'];
                }
                if (isset($_POST['button_border_radius'])) {
                    $settings['button_border_radius'] = (int)$_POST['button_border_radius'];
                }
                if (isset($_POST['button_shadow'])) {
                    $settings['button_shadow'] = isset($_POST['button_shadow']);
                }
                if (isset($_POST['font_family'])) {
                    $settings['font_family'] = $_POST['font_family'];
                }
                if (isset($_POST['profile_layout'])) {
                    $settings['profile_layout'] = $_POST['profile_layout'];
                }
                if (isset($_POST['show_social_icons'])) {
                    $settings['show_social_icons'] = isset($_POST['show_social_icons']);
                }
                if (isset($_POST['custom_css'])) {
                    $settings['custom_css'] = $_POST['custom_css'];
                }
                
                if (updateTemplateSettings($_SESSION['user_id'], $settings)) {
                    $success = "Impostazioni template aggiornate con successo!";
                    // Ricarica dati utente
        $user = getUser($_SESSION['user_id']);
                } else {
                    $error = "Errore durante l'aggiornamento delle impostazioni";
                }
                break;

            case 'create_short_link':
                $original_url = trim($_POST['original_url']);
                $title = trim($_POST['short_title'] ?? '');
                $description = trim($_POST['short_description'] ?? '');
                $custom_code = trim($_POST['custom_code'] ?? '');

                $result = createShortLink($_SESSION['user_id'], $original_url, $title, $description, $custom_code);
                if ($result['success']) {
                    $success = "Link accorciato creato con successo!";
                } else {
                    $error = $result['message'];
                }
                break;

            case 'delete_short_link':
                $short_link_id = (int)($_POST['short_link_id'] ?? 0);

                if ($short_link_id && deleteShortLink($short_link_id, $_SESSION['user_id'])) {
                    $success = "Link accorciato eliminato con successo!";
    } else {
                    $error = "Errore durante l'eliminazione del link accorciato";
                }
                break;
        }
        
        // Ricarica i dati dopo l'operazione
        $user = getUser($_SESSION['user_id']);
        $links = getUserLinks($_SESSION['user_id']);
        $stats = getUserStats($_SESSION['user_id']);
        $short_links = getUserShortLinks($_SESSION['user_id']);
        $total_short_links = count($short_links);
        $total_short_clicks = 0;
        foreach ($short_links as $short_link) {
            $total_short_clicks += $short_link['click_count'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Stili per la sezione Personalizzazione */
        .customize-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        
        .customize-section h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .customize-section h3 i {
            color: #667eea;
        }
        
        .section-description {
            color: #666;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        /* Grid Template */
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .template-card {
            position: relative;
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }
        
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .template-card.active {
            border-color: #667eea;
            border-width: 3px;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .template-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .template-preview {
            width: 100%;
            height: 120px;
            border-radius: 0;
            transition: transform 0.3s ease;
        }
        
        .template-card:hover .template-preview {
            transform: scale(1.05);
        }
        
        .template-info {
            padding: 15px;
            text-align: center;
        }
        
        .template-info h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .template-info p {
            font-size: 0.85rem;
            color: #666;
            margin: 0;
        }
        
        /* Gradients Grid */
        .gradients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .gradient-option {
            position: relative;
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .gradient-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .gradient-option.active {
            border-color: #667eea;
            border-width: 3px;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .gradient-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .gradient-preview {
            width: 100%;
            height: 80px;
            border-radius: 0;
        }
        
        .gradient-option span {
            display: block;
            padding: 8px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
            background: white;
        }
        
        /* Form Groups migliorati */
        .customize-section .form-group {
            margin-bottom: 25px;
        }
        
        .customize-section .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 0.95rem;
        }
        
        .customize-section .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }
        
        .customize-section .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .customize-section .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .customize-section input[type="color"] {
            width: 80px;
            height: 50px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .customize-section input[type="color"]:hover {
            border-color: #667eea;
            transform: scale(1.05);
        }
        
        .customize-section input[type="number"] {
            max-width: 150px;
        }
        
        .customize-section textarea {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            resize: vertical;
        }
        
        .customize-section input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        /* Background Options */
        .background-options {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        
        .customize-layout {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        .customize-main {
            flex: 1 1 60%;
        }
        
        .customize-preview {
            flex: 1 1 40%;
            background: white;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: sticky;
            top: 80px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: calc(100vh - 120px);
        }
        
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .preview-frame {
            width: 100%;
            border: none;
            border-radius: 12px;
            background: #f8f9fa;
            box-shadow: inset 0 0 0 1px #e5e7eb;
            flex: 1;
            min-height: 400px;
        }
        
        .preview-hint {
            margin-top: 12px;
            font-size: 0.85rem;
            color: #666;
            text-align: center;
        }
        
        .autosave-status {
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #28a745;
        }
        
        .autosave-status.saving {
            color: #ff9800;
        }
        
        .autosave-status.error {
            color: #dc3545;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }
        
        .form-actions .btn {
            padding: 12px 30px;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .templates-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 15px;
            }
            
            .gradients-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 10px;
            }
            
            .customize-section {
                padding: 20px;
            }
            
            .customize-layout {
                flex-direction: column;
            }
            
            .customize-preview {
                position: relative;
                top: 0;
                max-height: none;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Benvenuto, <?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?>!</p>
                <div class="header-actions">
                    <?php if (isAdmin($_SESSION['user_id'])): ?>
                        <a href="admin.php" class="btn btn-outline" style="background: #dc3545; color: white; border-color: #dc3545;">
                            <i class="fas fa-shield-alt"></i> Area Admin
                        </a>
                    <?php endif; ?>
                    <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> Vedi Profilo
                    </a>
                    <button onclick="logout()" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>

            <!-- Menu di Navigazione -->
            <div class="dashboard-nav">
                <div class="nav-tabs">
                    <button type="button" class="nav-tab <?php echo $active_tab === 'overview' ? 'active' : ''; ?>" data-tab="overview" onclick="showTab('overview', this)">
                        <i class="fas fa-home"></i> Panoramica
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'links' ? 'active' : ''; ?>" data-tab="links" onclick="showTab('links', this)">
                        <i class="fas fa-link"></i> Link
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'short-links' ? 'active' : ''; ?>" data-tab="short-links" onclick="showTab('short-links', this)">
                        <i class="fas fa-compress"></i> Link Accorciati
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'analytics' ? 'active' : ''; ?>" data-tab="analytics" onclick="showTab('analytics', this)">
                        <i class="fas fa-chart-line"></i> Analytics
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" data-tab="profile" onclick="showTab('profile', this)">
                        <i class="fas fa-user"></i> Profilo
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'customize' ? 'active' : ''; ?>" data-tab="customize" onclick="showTab('customize', this)">
                        <i class="fas fa-palette"></i> Personalizza
                    </button>
                    <button type="button" class="nav-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" data-tab="settings" onclick="showTab('settings', this)">
                        <i class="fas fa-cog"></i> Impostazioni
                    </button>
                </div>
            </div>

            <div class="dashboard-content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($user['email_verified']) && !$user['email_verified']): ?>
                    <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Email non verificata!</strong> 
                                <p style="margin: 8px 0 0 0; font-size: 0.9rem;">
                                    Controlla la tua casella di posta e clicca sul link di verifica. 
                                    Non hai ricevuto l'email?
                                </p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="resend_verification">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-paper-plane"></i> Reinvia Email
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tab Panoramica -->
                <div id="overview-tab" class="tab-content <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">

                <!-- Statistiche -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_clicks']; ?></h3>
                        <p>Click Totali</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['today_clicks']; ?></h3>
                        <p>Click Oggi</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['active_links']; ?></h3>
                        <p>Link Attivi</p>
                    </div>
                </div>

                <!-- Gestione Link -->
                <div class="links-section">
                    <div class="section-header">
                        <h2><i class="fas fa-link"></i> I Tuoi Link</h2>
                        <button class="btn-add" onclick="openLinkModal()">
                            <i class="fas fa-plus"></i> Aggiungi Link
                        </button>
                    </div>

                    <div class="link-list">
                        <?php if (empty($links)): ?>
                            <div class="empty-state">
                                <i class="fas fa-link"></i>
                                <h3>Nessun link ancora</h3>
                                <p>Aggiungi il tuo primo link per iniziare!</p>
                                <button class="btn btn-primary" onclick="openLinkModal()">
                                    <i class="fas fa-plus"></i> Aggiungi Link
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($links as $link): ?>
                                <div class="link-item"
                                     data-link-id="<?php echo $link['id']; ?>"
                                     data-link-title="<?php echo htmlspecialchars($link['title']); ?>"
                                     data-link-url="<?php echo htmlspecialchars($link['url']); ?>"
                                     data-link-icon="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"
                                     data-link-color="<?php echo htmlspecialchars($link['color'] ?: '#007bff'); ?>"
                                     data-link-image="<?php echo htmlspecialchars($link['image_url'] ?? ''); ?>"
                                     draggable="true">
                                    <div class="link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                        <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                    </div>
                                    <div class="link-info">
                                        <?php if (!empty($link['image_url'])): ?>
                                            <div class="link-image-thumb">
                                                <img src="<?php echo htmlspecialchars($link['image_url']); ?>" alt="Anteprima immagine link">
                                            </div>
                                        <?php endif; ?>
                                        <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                                        <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                                        <div class="link-stats"><?php echo $link['click_count']; ?> click</div>
                                    </div>
                                    <div class="link-actions">
                                        <button class="btn-edit" onclick="openLinkModal(<?php echo $link['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteLink(<?php echo $link['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gestione Profilo -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user"></i> Profilo</h2>
                    </div>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label for="display_name">Nome Visualizzato</label>
                            <input type="text" id="display_name" name="display_name" 
                                   value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="bio">Biografia</label>
                            <textarea id="bio" name="bio" rows="3" 
                                      placeholder="Racconta qualcosa di te..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salva Profilo
                        </button>
                    </form>
                </div>
                </div>

                <!-- Tab Link -->
                <div id="links-tab" class="tab-content <?php echo $active_tab === 'links' ? 'active' : ''; ?>">
                    <div class="links-section">
                        <div class="section-header">
                            <h2><i class="fas fa-link"></i> Gestione Link</h2>
                            <button class="btn-add" onclick="openLinkModal()">
                                <i class="fas fa-plus"></i> Aggiungi Link
                            </button>
                        </div>

                        <div class="link-list">
                            <?php if (empty($links)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-link"></i>
                                    <h3>Nessun link ancora</h3>
                                    <p>Aggiungi il tuo primo link per iniziare!</p>
                                    <button class="btn btn-primary" onclick="openLinkModal()">
                                        <i class="fas fa-plus"></i> Aggiungi Link
                                    </button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($links as $link): ?>
                                    <div class="link-item"
                                         data-link-id="<?php echo $link['id']; ?>"
                                         data-link-title="<?php echo htmlspecialchars($link['title']); ?>"
                                         data-link-url="<?php echo htmlspecialchars($link['url']); ?>"
                                         data-link-icon="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"
                                         data-link-color="<?php echo htmlspecialchars($link['color'] ?: '#007bff'); ?>"
                                         data-link-image="<?php echo htmlspecialchars($link['image_url'] ?? ''); ?>"
                                         draggable="true">
                                        <div class="link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                            <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                        </div>
                                        <div class="link-info">
                                            <?php if (!empty($link['image_url'])): ?>
                                                <div class="link-image-thumb">
                                                    <img src="<?php echo htmlspecialchars($link['image_url']); ?>" alt="Anteprima immagine link">
                                                </div>
                                            <?php endif; ?>
                                            <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                                            <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                                            <div class="link-stats"><?php echo $link['click_count']; ?> click</div>
                                        </div>
                                        <div class="link-actions">
                                            <button class="btn-edit" onclick="openLinkModal(<?php echo $link['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-delete" onclick="deleteLink(<?php echo $link['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Link Accorciati -->
                <div id="short-links-tab" class="tab-content <?php echo $active_tab === 'short-links' ? 'active' : ''; ?>">
                    <div class="section-header">
                        <h2><i class="fas fa-compress"></i> Link Accorciati</h2>
                        <div class="header-actions">
                            <a href="short-links.php" class="btn btn-outline">
                                <i class="fas fa-external-link-alt"></i> Vista completa
                        </a>
                    </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?php echo $total_short_links; ?></h3>
                            <p>Link accorciati</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $total_short_clicks; ?></h3>
                            <p>Click totali</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $total_short_links > 0 ? round($total_short_clicks / $total_short_links, 1) : 0; ?></h3>
                            <p>Media click per link</p>
                        </div>
                    </div>

                    <div class="links-section">
                        <div class="section-header">
                            <h3><i class="fas fa-plus"></i> Crea un nuovo link</h3>
                        </div>
                        <form method="POST" class="short-link-form">
                            <input type="hidden" name="action" value="create_short_link">

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="short-original-url">URL originale</label>
                                    <input type="url" id="short-original-url" name="original_url" required placeholder="https://esempio.com">
                                </div>
                                <div class="form-group">
                                    <label for="custom_code">Codice personalizzato (opzionale)</label>
                                    <input type="text" id="custom_code" name="custom_code" maxlength="20" placeholder="mio-link">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="short-title">Titolo (opzionale)</label>
                                    <input type="text" id="short-title" name="short_title" placeholder="Titolo del link">
                                </div>
                                <div class="form-group">
                                    <label for="short-description">Descrizione (opzionale)</label>
                                    <input type="text" id="short-description" name="short_description" placeholder="Breve descrizione">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-link"></i> Crea link accorciato
                            </button>
                        </form>
                    </div>

                    <div class="links-section">
                        <div class="section-header">
                            <h3><i class="fas fa-list"></i> I tuoi link accorciati</h3>
                        </div>

                        <div class="short-links-list">
                            <?php if (empty($short_links)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-link"></i>
                                    <h3>Nessun link accorciato</h3>
                                    <p>Crea il tuo primo link accorciato per iniziare!</p>
                                </div>
                            <?php else: ?>
                                <?php
                                    $short_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                    $short_base_url = $short_scheme . '://' . $_SERVER['HTTP_HOST'] . '/short.php?code=';
                                ?>
                                <?php foreach ($short_links as $short_link): ?>
                                    <?php 
                                        $short_url = $short_base_url . urlencode($short_link['short_code']);
                                        $qr_raw = getShortLinkQRCode($_SESSION['user_id'], $short_link['short_code']);
                                        $scheme_full = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                        $qr_display = '';
                                        if ($qr_raw) {
                                            if (preg_match('/^https?:\/\//', $qr_raw)) {
                                                $qr_display = $qr_raw;
                                            } else {
                                                $qr_display = $scheme_full . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($qr_raw, '/');
                                            }
                                        }
                                    ?>
                                    <div class="short-link-item">
                                        <div class="short-link-info">
                                            <div class="short-link-title">
                                                <?php echo htmlspecialchars($short_link['title'] ?: 'Senza titolo'); ?>
                                            </div>
                                            <div class="short-link-url">
                                                <strong>Originale:</strong> <?php echo htmlspecialchars($short_link['original_url']); ?>
                                            </div>
                                            <div class="short-link-short">
                                                <strong>Accorciato:</strong>
                                                <a href="<?php echo $short_url; ?>" target="_blank" class="short-url"><?php echo $short_url; ?></a>
                                                <button type="button" class="btn-copy" onclick="copyToClipboard('<?php echo $short_url; ?>', this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <?php if (!empty($short_link['description'])): ?>
                                                <div class="short-link-description">
                                                    <?php echo htmlspecialchars($short_link['description']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="short-link-stats">
                                                <span><i class="fas fa-mouse-pointer"></i> <?php echo $short_link['click_count']; ?> click</span>
                                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($short_link['created_at'])); ?></span>
                                            </div>
                                            <?php if ($qr_display): ?>
                                                <div class="short-link-qr-preview">
                                                    <img src="<?php echo htmlspecialchars($qr_display); ?>" alt="QR Code" class="qr-thumbnail">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="short-link-actions">
                                            <?php if ($qr_display): ?>
                                                <button type="button" class="btn-qr" onclick="showQRModal('<?php echo htmlspecialchars($qr_display); ?>', '<?php echo htmlspecialchars($short_link['title'] ?: 'Link'); ?>')">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn-stats" onclick="window.location.href='short-links.php';">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                            <button type="button" class="btn-delete" onclick="deleteShortLink(<?php echo $short_link['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Analytics -->
                <div id="analytics-tab" class="tab-content <?php echo $active_tab === 'analytics' ? 'active' : ''; ?>">
                    <div class="section-header">
                        <h2><i class="fas fa-chart-line"></i> Analytics</h2>
                        <a href="analytics.php" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Vedi Analytics Complete
                        </a>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Analytics Avanzate</h3>
                        <p>Monitora le performance dei tuoi link con statistiche dettagliate e grafici interattivi.</p>
                        <ul>
                            <li>Click per giorno e ora</li>
                            <li>Analisi dispositivi e browser</li>
                            <li>Top link più cliccati</li>
                            <li>Grafici interattivi</li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Profilo -->
                <div id="profile-tab" class="tab-content <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                    <div class="profile-section">
                        <div class="section-header">
                            <h2><i class="fas fa-user"></i> Gestione Profilo</h2>
                        </div>
                        
                        <form method="POST" class="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="display_name">Nome Visualizzato</label>
                                <input type="text" id="display_name" name="display_name" 
                                       value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="bio">Biografia</label>
                                <textarea id="bio" name="bio" rows="3" 
                                          placeholder="Racconta qualcosa di te..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salva Profilo
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab Personalizza -->
                <div id="customize-tab" class="tab-content <?php echo $active_tab === 'customize' ? 'active' : ''; ?>">
                    <div class="section-header">
                        <h2><i class="fas fa-palette"></i> Personalizza Profilo</h2>
                        <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i> Anteprima Profilo
                        </a>
                    </div>

                    <div class="customize-layout">
                        <div class="customize-main">
                        <form method="POST" id="template-form">
                            <input type="hidden" name="action" value="update_template_settings">
                            <div class="autosave-status" id="autoSaveStatus">Le modifiche vengono salvate automaticamente.</div>

                            <!-- Sezione Template -->
                            <div class="customize-section">
                            <h3><i class="fas fa-layer-group"></i> Seleziona Template</h3>
                            <p class="section-description">Scegli un template predefinito per il tuo profilo</p>
                            
                            <div class="templates-grid">
                                <?php 
                                $templates = getAvailableTemplates();
                                $current_template = $user['template'] ?? 'default';
                                foreach ($templates as $key => $template): 
                                ?>
                                    <label class="template-card <?php echo $current_template === $key ? 'active' : ''; ?>">
                                        <input type="radio" name="template" value="<?php echo $key; ?>" 
                                               <?php echo $current_template === $key ? 'checked' : ''; ?>>
                                        <div class="template-preview" style="background: <?php echo htmlspecialchars($template['preview']); ?>;"></div>
                                        <div class="template-info">
                                            <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($template['description']); ?></p>
                    </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sezione Background -->
                        <div class="customize-section">
                            <h3><i class="fas fa-image"></i> Personalizza Sfondo</h3>
                            <p class="section-description">Scegli il tipo di sfondo per il tuo profilo</p>
                            
                            <div class="form-group">
                                <label>Tipo di Sfondo</label>
                                <select name="background_type" id="background_type" class="form-control">
                                    <option value="gradient" <?php echo ($user['background_type'] ?? 'gradient') === 'gradient' ? 'selected' : ''; ?>>Gradiente</option>
                                    <option value="color" <?php echo ($user['background_type'] ?? '') === 'color' ? 'selected' : ''; ?>>Colore Solido</option>
                                    <option value="image" <?php echo ($user['background_type'] ?? '') === 'image' ? 'selected' : ''; ?>>Immagine</option>
                                </select>
                            </div>

                            <!-- Gradiente -->
                            <div id="gradient-options" class="background-options">
                                <div class="form-group">
                                    <label>Gradiente Predefinito</label>
                                    <div class="gradients-grid">
                                        <?php 
                                        $gradients = getPresetGradients();
                                        $current_gradient = $user['background_gradient'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                                        foreach ($gradients as $name => $gradient): 
                                        ?>
                                            <label class="gradient-option <?php echo $current_gradient === $gradient ? 'active' : ''; ?>">
                                                <input type="radio" name="background_gradient" value="<?php echo htmlspecialchars($gradient); ?>"
                                                       <?php echo $current_gradient === $gradient ? 'checked' : ''; ?>>
                                                <div class="gradient-preview" style="background: <?php echo htmlspecialchars($gradient); ?>;"></div>
                                                <span><?php echo ucfirst($name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Colore Solido -->
                            <div id="color-options" class="background-options" style="display: none;">
                                <div class="form-group">
                                    <label>Colore Sfondo</label>
                                    <input type="color" name="background_color" id="background_color" 
                                           value="<?php echo htmlspecialchars($user['background_color'] ?? '#667eea'); ?>" 
                                           class="form-control" style="width: 100px; height: 50px;">
                                </div>
                            </div>

                            <!-- Immagine -->
                            <div id="image-options" class="background-options" style="display: none;">
                                <div class="form-group">
                                    <label>URL Immagine Sfondo</label>
                                    <input type="url" name="background_image" id="background_image" 
                                           value="<?php echo htmlspecialchars($user['background_image'] ?? ''); ?>" 
                                           class="form-control" placeholder="https://example.com/image.jpg">
                                    <small>Inserisci l'URL di un'immagine per lo sfondo</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sezione Colori Testo -->
                        <div class="customize-section">
                            <h3><i class="fas fa-font"></i> Colore Testo</h3>
                            <p class="section-description">Scegli il colore del testo per il tuo profilo</p>
                            
                            <div class="form-group">
                                <label>Colore Testo</label>
                                <input type="color" name="text_color" id="text_color" 
                                       value="<?php echo htmlspecialchars($user['text_color'] ?? '#ffffff'); ?>" 
                                       class="form-control" style="width: 100px; height: 50px;">
                            </div>
                        </div>

                        <!-- Sezione Stile Link -->
                        <div class="customize-section">
                            <h3><i class="fas fa-link"></i> Stile Link</h3>
                            <p class="section-description">Scegli come visualizzare i link nel tuo profilo</p>
                            
                            <div class="form-group">
                                <label>Stile Link</label>
                                <select name="link_style" class="form-control">
                                    <?php 
                                    $link_styles = getAvailableLinkStyles();
                                    $current_style = $user['link_style'] ?? 'card';
                                    foreach ($link_styles as $key => $style): 
                                    ?>
                                        <option value="<?php echo $key; ?>" <?php echo $current_style === $key ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($style['name']); ?> - <?php echo htmlspecialchars($style['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Colore Link</label>
                                <input type="color" name="link_color" id="link_color" 
                                       value="<?php echo htmlspecialchars($user['link_color'] ?? '#667eea'); ?>" 
                                       class="form-control" style="width: 100px; height: 50px;">
                                <small>Colore di sfondo dei link</small>
                            </div>

                            <div class="form-group">
                                <label>Colore Hover Link</label>
                                <input type="color" name="link_hover_color" id="link_hover_color" 
                                       value="<?php echo htmlspecialchars($user['link_hover_color'] ?? '#5568d3'); ?>" 
                                       class="form-control" style="width: 100px; height: 50px;">
                                <small>Colore quando passi il mouse sui link</small>
                            </div>

                            <div class="form-group">
                                <label>Border Radius (px)</label>
                                <input type="number" name="button_border_radius" id="button_border_radius" 
                                       value="<?php echo htmlspecialchars($user['button_border_radius'] ?? 12); ?>" 
                                       class="form-control" min="0" max="50">
                                <small>Quanto arrotondare gli angoli dei link (0-50px)</small>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="button_shadow" value="1" 
                                           <?php echo ($user['button_shadow'] ?? true) ? 'checked' : ''; ?>>
                                    Mostra ombra sui link
                                </label>
                            </div>
                        </div>

                        <!-- Sezione Layout e Font -->
                        <div class="customize-section">
                            <h3><i class="fas fa-align-center"></i> Layout e Font</h3>
                            <p class="section-description">Personalizza il layout e il font del profilo</p>
                            
                            <div class="form-group">
                                <label>Layout Profilo</label>
                                <select name="profile_layout" class="form-control">
                                    <option value="centered" <?php echo ($user['profile_layout'] ?? 'centered') === 'centered' ? 'selected' : ''; ?>>Centrato</option>
                                    <option value="left" <?php echo ($user['profile_layout'] ?? '') === 'left' ? 'selected' : ''; ?>>Sinistra</option>
                                    <option value="right" <?php echo ($user['profile_layout'] ?? '') === 'right' ? 'selected' : ''; ?>>Destra</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Font Family</label>
                                <select name="font_family" class="form-control">
                                    <option value="system" <?php echo ($user['font_family'] ?? 'system') === 'system' ? 'selected' : ''; ?>>System Default</option>
                                    <option value="inter" <?php echo ($user['font_family'] ?? '') === 'inter' ? 'selected' : ''; ?>>Inter</option>
                                    <option value="poppins" <?php echo ($user['font_family'] ?? '') === 'poppins' ? 'selected' : ''; ?>>Poppins</option>
                                    <option value="montserrat" <?php echo ($user['font_family'] ?? '') === 'montserrat' ? 'selected' : ''; ?>>Montserrat</option>
                                    <option value="roboto" <?php echo ($user['font_family'] ?? '') === 'roboto' ? 'selected' : ''; ?>>Roboto</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="show_social_icons" value="1" 
                                           <?php echo ($user['show_social_icons'] ?? true) ? 'checked' : ''; ?>>
                                    Mostra icone social in fondo al profilo
                                </label>
                            </div>
                        </div>

                        <!-- Sezione CSS Personalizzato -->
                        <div class="customize-section">
                            <h3><i class="fas fa-code"></i> CSS Personalizzato</h3>
                            <p class="section-description">Aggiungi CSS personalizzato per un controllo totale sul design</p>
                            
                            <div class="form-group">
                                <label>CSS Personalizzato</label>
                                <textarea name="custom_css" id="custom_css" class="form-control" rows="8" 
                                          placeholder=".link-item { /* tuo CSS qui */ }"><?php echo htmlspecialchars($user['custom_css'] ?? ''); ?></textarea>
                                <small>Inserisci il tuo CSS personalizzato. Usa con cautela!</small>
                            </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salva Impostazioni
                                </button>
                                <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="btn btn-outline">
                                    <i class="fas fa-external-link-alt"></i> Apri in nuova scheda
                                </a>
                            </div>
                        </form>
                        </div>
                        <div class="customize-preview">
                            <div class="preview-header">
                                <h3><i class="fas fa-desktop"></i> Anteprima Live</h3>
                                <button type="button" class="btn btn-secondary btn-sm" id="refreshPreviewBtn">
                                    <i class="fas fa-sync-alt"></i> Aggiorna
                                </button>
                            </div>
                            <iframe
                                id="profilePreview"
                                class="preview-frame"
                                src="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>&preview=1"
                                title="Anteprima profilo"></iframe>
                            <p class="preview-hint">
                                L'anteprima si aggiorna automaticamente quando modifichi i campi.
                            </p>
                        </div>
                    </div>
                    
                    <script>
                        const previewBaseUrl = <?php echo json_encode('profile.php?user=' . urlencode($user['username']) . '&preview=1'); ?>;
                        
                        // Gestione opzioni background dinamiche
                        document.addEventListener('DOMContentLoaded', function() {
                            const backgroundType = document.getElementById('background_type');
                            const gradientOptions = document.getElementById('gradient-options');
                            const colorOptions = document.getElementById('color-options');
                            const imageOptions = document.getElementById('image-options');
                            const templateForm = document.getElementById('template-form');
                            const autoSaveStatus = document.getElementById('autoSaveStatus');
                            const previewFrame = document.getElementById('profilePreview');
                            const refreshPreviewBtn = document.getElementById('refreshPreviewBtn');
                            let autoSaveTimeout = null;
                            let previewTimeout = null;
                            
                            function updateBackgroundOptions() {
                                const type = backgroundType.value;
                                
                                // Nascondi tutte le opzioni
                                gradientOptions.style.display = 'none';
                                colorOptions.style.display = 'none';
                                imageOptions.style.display = 'none';
                                
                                // Mostra solo l'opzione selezionata
                                if (type === 'gradient') {
                                    gradientOptions.style.display = 'block';
                                } else if (type === 'color') {
                                    colorOptions.style.display = 'block';
                                } else if (type === 'image') {
                                    imageOptions.style.display = 'block';
                                }
                            }
                            
                            // Inizializza
                            updateBackgroundOptions();
                            
                            // Aggiorna quando cambia la selezione
                            backgroundType.addEventListener('change', updateBackgroundOptions);
                            
                            // Evidenzia template selezionato
                            const templateCards = document.querySelectorAll('.template-card');
                            templateCards.forEach(card => {
                                const input = card.querySelector('input[type="radio"]');
                                if (input.checked) {
                                    card.classList.add('active');
                                }
                                input.addEventListener('change', () => {
                                    templateCards.forEach(c => c.classList.remove('active'));
                                    card.classList.add('active');
                                });
                            });
                            
                            // Evidenzia gradiente selezionato
                            const gradientOptionsRadios = document.querySelectorAll('.gradient-option input[type="radio"]');
                            gradientOptionsRadios.forEach(radio => {
                                if (radio.checked) {
                                    radio.closest('.gradient-option').classList.add('active');
                                }
                                radio.addEventListener('change', () => {
                                    document.querySelectorAll('.gradient-option').forEach(option => option.classList.remove('active'));
                                    radio.closest('.gradient-option').classList.add('active');
                                });
                            });
                            
                            function setAutoSaveStatus(message, state = 'success') {
                                if (!autoSaveStatus) return;
                                autoSaveStatus.textContent = message;
                                autoSaveStatus.classList.remove('saving', 'error');
                                if (state === 'saving') {
                                    autoSaveStatus.classList.add('saving');
                                } else if (state === 'error') {
                                    autoSaveStatus.classList.add('error');
                                }
                            }
                            
                            function reloadPreview() {
                                if (!previewFrame) return;
                                const separator = previewBaseUrl.includes('?') ? '&' : '?';
                                previewFrame.src = previewBaseUrl + separator + 'ts=' + Date.now();
                            }
                            
                            function queuePreviewReload() {
                                if (!previewFrame) return;
                                clearTimeout(previewTimeout);
                                previewTimeout = setTimeout(reloadPreview, 300);
                            }
                            
                            function queueAutoSave() {
                                if (!templateForm) return;
                                clearTimeout(autoSaveTimeout);
                                setAutoSaveStatus('Salvataggio in corso...', 'saving');
                                autoSaveTimeout = setTimeout(() => {
                                    const formData = new FormData(templateForm);
                                    formData.append('ajax', '1');
                                    
                                    fetch('ajax/update_template.php', {
                                        method: 'POST',
                                        body: formData,
                                        credentials: 'same-origin'
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            setAutoSaveStatus('Modifiche salvate');
                                            queuePreviewReload();
                                        } else {
                                            setAutoSaveStatus(data.message || 'Errore durante il salvataggio', 'error');
                                        }
                                    })
                                    .catch(() => {
                                        setAutoSaveStatus('Errore di rete', 'error');
                                    });
                                }, 500);
                            }
                            
                            if (templateForm) {
                                const inputs = templateForm.querySelectorAll('input, select, textarea');
                                inputs.forEach(input => {
                                    const eventName = (input.tagName === 'SELECT' || input.type === 'radio' || input.type === 'checkbox') ? 'change' : 'input';
                                    input.addEventListener(eventName, queueAutoSave);
                                });
                            }
                            
                            if (refreshPreviewBtn) {
                                refreshPreviewBtn.addEventListener('click', reloadPreview);
                            }
                        });
                    </script>
                </div>

                <!-- Tab Impostazioni -->
                <div id="settings-tab" class="tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                    <div class="settings-section">
                        <div class="section-header">
                            <h2><i class="fas fa-cog"></i> Impostazioni</h2>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- Indirizzo e Mappa -->
                            <div class="setting-card">
                                <h3><i class="fas fa-map-marker-alt"></i> Indirizzo e Mappa</h3>
                                <p>Aggiungi il tuo indirizzo e mostralo sul profilo</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_address">
                                    <div class="form-group">
                                        <label for="address">Indirizzo</label>
                                        <input type="text" id="address" name="address" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
                                               placeholder="Via Roma 123, Milano, Italia">
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="show_map" value="1" 
                                                   <?php echo ($user['show_map'] ?? false) ? 'checked' : ''; ?>>
                                            Mostra mappa nel profilo pubblico
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salva Indirizzo
                                    </button>
                                    
                                    <div style="margin-top: 15px;">
                                        <a href="test_address.php" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-vial"></i> Test Indirizzo
                                        </a>
                                        <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>&debug=1" 
                                           class="btn btn-secondary btn-sm" target="_blank">
                                            <i class="fas fa-bug"></i> Debug Profilo
                                        </a>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="setting-card">
                                <h3><i class="fas fa-palette"></i> Tema</h3>
                                <p>Scegli il tema per il tuo profilo pubblico</p>
                                <select class="form-control">
                                    <option value="default">Default</option>
                                    <option value="dark">Scuro</option>
                                    <option value="minimal">Minimalista</option>
                                    <option value="colorful">Colorato</option>
                                </select>
                            </div>

                            <div class="setting-card">
                                <h3><i class="fas fa-share-alt"></i> Social</h3>
                                <p>Aggiungi i tuoi profili social da mostrare sul profilo pubblico</p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_social">
                                    <div class="form-group">
                                        <label for="social_instagram"><i class="fab fa-instagram"></i> Instagram</label>
                                        <input type="url" id="social_instagram" name="social_instagram" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_instagram'] ?? ''); ?>"
                                               placeholder="https://instagram.com/tuoprofilo">
                                    </div>
                                    <div class="form-group">
                                        <label for="social_facebook"><i class="fab fa-facebook"></i> Facebook</label>
                                        <input type="url" id="social_facebook" name="social_facebook" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_facebook'] ?? ''); ?>"
                                               placeholder="https://facebook.com/tuoprofilo">
                                    </div>
                                    <div class="form-group">
                                        <label for="social_tiktok"><i class="fab fa-tiktok"></i> TikTok</label>
                                        <input type="url" id="social_tiktok" name="social_tiktok" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_tiktok'] ?? ''); ?>"
                                               placeholder="https://www.tiktok.com/@tuoprofilo">
                                    </div>
                                    <div class="form-group">
                                        <label for="social_twitter"><i class="fab fa-x-twitter"></i> Twitter / X</label>
                                        <input type="url" id="social_twitter" name="social_twitter" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_twitter'] ?? ''); ?>"
                                               placeholder="https://twitter.com/tuoprofilo">
                                    </div>
                                    <div class="form-group">
                                        <label for="social_linkedin"><i class="fab fa-linkedin"></i> LinkedIn</label>
                                        <input type="url" id="social_linkedin" name="social_linkedin" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_linkedin'] ?? ''); ?>"
                                               placeholder="https://www.linkedin.com/in/tuoprofilo">
                                    </div>
                                    <div class="form-group">
                                        <label for="social_youtube"><i class="fab fa-youtube"></i> YouTube</label>
                                        <input type="url" id="social_youtube" name="social_youtube" class="form-control"
                                               value="<?php echo htmlspecialchars($user['social_youtube'] ?? ''); ?>"
                                               placeholder="https://www.youtube.com/@tuocanale">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salva Social
                                    </button>
                                </form>
                            </div>
                            
                            <div class="setting-card">
                                <h3><i class="fas fa-shield-alt"></i> Sicurezza</h3>
                                <p>Gestisci la sicurezza del tuo account</p>
                                <button class="btn btn-secondary">
                                    <i class="fas fa-key"></i> Cambia Password
                                </button>
                            </div>
                            
                            <div class="setting-card">
                                <h3><i class="fas fa-download"></i> Backup</h3>
                                <p>Scarica i tuoi dati</p>
                                <button class="btn btn-secondary">
                                    <i class="fas fa-download"></i> Scarica Dati
                                </button>
                            </div>
                            
                            <div class="setting-card">
                                <h3><i class="fas fa-trash"></i> Account</h3>
                                <p>Elimina il tuo account</p>
                                <button class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Elimina Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per Link -->
    <div id="linkModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Aggiungi Link</h3>
                <button class="close-modal" onclick="closeModal('linkModal')">&times;</button>
            </div>
            <form id="linkForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_link">
                <input type="hidden" name="link_id" value="">
                
                <div class="form-group">
                    <label for="title">Titolo</label>
                    <input type="text" id="title" name="title" required 
                           placeholder="Es: Il mio sito web">
                </div>
                
                <div class="form-group">
                    <label for="url">URL</label>
                    <input type="url" id="url" name="url" required 
                           placeholder="https://esempio.com">
                </div>
                
                <div class="form-group">
                    <label for="icon">Icona (Font Awesome)</label>
                    <input type="text" id="icon" name="icon" 
                           placeholder="fas fa-globe" 
                           value="fas fa-link">
                </div>
                
                <div class="form-group">
                    <label for="color">Colore</label>
                    <input type="color" id="color" name="color" 
                           value="#007bff">
                </div>

                <div class="form-group">
                    <label for="image_url">Immagine (URL opzionale)</label>
                    <input type="url" id="image_url" name="image_url" 
                           placeholder="https://esempio.com/immagine.jpg">
                    <small class="form-hint">Se impostata, il link viene mostrato come card con immagine anche nel profilo pubblico.</small>
                </div>

                <div class="form-group">
                    <label for="image_file">Oppure carica immagine (JPG o PNG, max 500KB)</label>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png">
                    <div class="image-upload-actions">
                        <div class="image-preview hidden" id="imagePreviewWrapper">
                            <img id="imagePreview" src="" alt="Anteprima immagine">
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm hidden" id="removeImageButton">Rimuovi immagine</button>
                    </div>
                </div>

                <input type="hidden" name="remove_image" id="remove_image" value="0">
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('linkModal')">
                        Annulla
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salva Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal QR Code -->
    <div id="qrModal" class="modal">
        <div class="modal-content qr-modal">
            <div class="modal-header">
                <h3 id="qrModalTitle">QR Code</h3>
                <button class="close-modal" onclick="closeModal('qrModal')">&times;</button>
            </div>
            <div class="qr-modal-content">
                <div class="qr-display">
                    <img id="qrImage" src="" alt="QR Code" class="qr-large">
                </div>
                <div class="qr-actions">
                    <button type="button" class="btn btn-primary" onclick="downloadQR()">
                        <i class="fas fa-download"></i> Scarica QR Code
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="copyQRUrl()">
                        <i class="fas fa-copy"></i> Copia URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        const initialTab = '<?php echo $active_tab; ?>';
        
        // Gestione tab del dashboard
        function showTab(tabName, element = null) {
            // Nascondi tutti i tab
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Rimuovi active da tutti i nav-tab
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostra il tab selezionato
            const content = document.getElementById(tabName + '-tab');
            if (content) {
                content.classList.add('active');
            }
            
            // Attiva il nav-tab selezionato
            if (element) {
                element.classList.add('active');
            } else {
                const navButton = document.querySelector(`.nav-tab[data-tab="${tabName}"]`);
                if (navButton) {
                    navButton.classList.add('active');
                }
            }
        }
        
        // Inizializzazione
        document.addEventListener('DOMContentLoaded', function() {
            showTab(initialTab || 'overview');
        });
    </script>
</body>
</html>
