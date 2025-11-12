<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user = getUser($_SESSION['user_id']);
$links = getUserLinks($_SESSION['user_id']);
$stats = getUserStats($_SESSION['user_id']);

// Gestione delle azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_link':
                $title = trim($_POST['title']);
                $url = trim($_POST['url']);
                $icon = trim($_POST['icon']);
                $color = $_POST['color'];
                
                if (addLink($_SESSION['user_id'], $title, $url, $icon, $color)) {
                    $success = "Link aggiunto con successo!";
                } else {
                    $error = "Errore durante l'aggiunta del link";
                }
                break;
                
            case 'update_link':
                $link_id = $_POST['link_id'];
                $title = trim($_POST['title']);
                $url = trim($_POST['url']);
                $icon = trim($_POST['icon']);
                $color = $_POST['color'];
                
                if (updateLink($link_id, $_SESSION['user_id'], $title, $url, $icon, $color)) {
                    $success = "Link aggiornato con successo!";
                } else {
                    $error = "Errore durante l'aggiornamento del link";
                }
                break;
                
            case 'delete_link':
                $link_id = $_POST['link_id'];
                
                if (deleteLink($link_id, $_SESSION['user_id'])) {
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
        }
        
        // Ricarica i dati dopo l'operazione
        $user = getUser($_SESSION['user_id']);
        $links = getUserLinks($_SESSION['user_id']);
        $stats = getUserStats($_SESSION['user_id']);
    }
}

// Gestione riordinamento via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'reorder_links') {
    $order = json_decode($_POST['order'], true);
    if (reorderLinks($_SESSION['user_id'], $order)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
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
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Benvenuto, <?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?>!</p>
                <div class="header-actions">
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
                    <button class="nav-tab active" onclick="showTab('overview')">
                        <i class="fas fa-home"></i> Panoramica
                    </button>
                    <button class="nav-tab" onclick="showTab('links')">
                        <i class="fas fa-link"></i> Link
                    </button>
                    <button class="nav-tab" onclick="showTab('short-links')">
                        <i class="fas fa-compress"></i> Link Accorciati
                    </button>
                    <button class="nav-tab" onclick="showTab('analytics')">
                        <i class="fas fa-chart-line"></i> Analytics
                    </button>
                    <button class="nav-tab" onclick="showTab('profile')">
                        <i class="fas fa-user"></i> Profilo
                    </button>
                    <button class="nav-tab" onclick="showTab('customize')">
                        <i class="fas fa-palette"></i> Personalizza
                    </button>
                    <button class="nav-tab" onclick="showTab('settings')">
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

                <!-- Tab Panoramica -->
                <div id="overview-tab" class="tab-content active">

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
                                     draggable="true">
                                    <div class="link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                        <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                    </div>
                                    <div class="link-info">
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
                <div id="links-tab" class="tab-content">
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
                                         draggable="true">
                                        <div class="link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                            <i class="<?php echo htmlspecialchars($link['icon'] ?: 'fas fa-link'); ?>"></i>
                                        </div>
                                        <div class="link-info">
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
                <div id="short-links-tab" class="tab-content">
                    <div class="section-header">
                        <h2><i class="fas fa-compress"></i> Link Accorciati</h2>
                        <a href="short-links.php" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Gestisci Link Accorciati
                        </a>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-info-circle"></i>
                        <h3>Link Accorciati</h3>
                        <p>Crea link brevi e personalizzabili con statistiche dettagliate e QR code automatici.</p>
                        <ul>
                            <li>URL shortening professionale</li>
                            <li>Codici personalizzati</li>
                            <li>Analytics dettagliate</li>
                            <li>QR Code automatici</li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Analytics -->
                <div id="analytics-tab" class="tab-content">
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
                <div id="profile-tab" class="tab-content">
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
                <div id="customize-tab" class="tab-content">
                    <div class="section-header">
                        <h2><i class="fas fa-palette"></i> Personalizza Profilo</h2>
                        <a href="customize.php" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Personalizza Avanzata
                        </a>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-paint-brush"></i>
                        <h3>Personalizzazione Avanzata</h3>
                        <p>Personalizza completamente l'aspetto del tuo profilo pubblico con temi, colori, font e CSS personalizzato.</p>
                        <ul>
                            <li>Temi predefiniti e personalizzati</li>
                            <li>Colori personalizzabili</li>
                            <li>Font e tipografia</li>
                            <li>CSS personalizzato</li>
                            <li>Anteprima in tempo reale</li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Impostazioni -->
                <div id="settings-tab" class="tab-content">
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
            <form id="linkForm" method="POST">
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

    <script src="assets/js/script.js"></script>
    <script>
        // Gestione tab del dashboard
        function showTab(tabName) {
            // Nascondi tutti i tab
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Rimuovi active da tutti i nav-tab
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostra il tab selezionato
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Attiva il nav-tab selezionato
            event.target.classList.add('active');
        }
        
        // Inizializzazione
        document.addEventListener('DOMContentLoaded', function() {
            // Imposta il tab attivo di default
            showTab('overview');
        });
    </script>
</body>
</html>
