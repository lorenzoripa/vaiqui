<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/qr_generator.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user = getUser($_SESSION['user_id']);
$short_links = getUserShortLinks($_SESSION['user_id']);

// Gestione delle azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_short_link':
                $original_url = trim($_POST['original_url']);
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $custom_code = trim($_POST['custom_code']);
                
                $result = createShortLink($_SESSION['user_id'], $original_url, $title, $description, $custom_code);
                if ($result['success']) {
                    $success = "Link accorciato creato con successo!";
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete_short_link':
                $short_link_id = $_POST['short_link_id'];
                
                if (deleteShortLink($short_link_id, $_SESSION['user_id'])) {
                    $success = "Link accorciato eliminato con successo!";
                } else {
                    $error = "Errore durante l'eliminazione del link";
                }
                break;
        }
        
        // Ricarica i dati dopo l'operazione
        $short_links = getUserShortLinks($_SESSION['user_id']);
    }
}

// Ottieni statistiche generali
$total_short_clicks = 0;
$total_short_links = count($short_links);
foreach ($short_links as $link) {
    $total_short_clicks += $link['click_count'];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Accorciati - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-link"></i> Link Accorciati</h1>
                <p>Accorcia i tuoi link e traccia le statistiche</p>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Torna al Dashboard
                    </a>
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

                <!-- Statistiche -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $total_short_links; ?></h3>
                        <p>Link Accorciati</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $total_short_clicks; ?></h3>
                        <p>Click Totali</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $total_short_links > 0 ? round($total_short_clicks / $total_short_links, 1) : 0; ?></h3>
                        <p>Click per Link</p>
                    </div>
                </div>

                <!-- Crea nuovo link -->
                <div class="links-section">
                    <div class="section-header">
                        <h2><i class="fas fa-plus"></i> Crea Nuovo Link Accorciato</h2>
                    </div>
                    
                    <form method="POST" class="short-link-form">
                        <input type="hidden" name="action" value="create_short_link">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="original_url">URL Originale</label>
                                <input type="url" id="original_url" name="original_url" required 
                                       placeholder="https://esempio.com">
                            </div>
                            <div class="form-group">
                                <label for="custom_code">Codice Personalizzato (opzionale)</label>
                                <input type="text" id="custom_code" name="custom_code" 
                                       placeholder="mio-link" maxlength="20">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Titolo</label>
                                <input type="text" id="title" name="title" 
                                       placeholder="Titolo del link">
                            </div>
                            <div class="form-group">
                                <label for="description">Descrizione</label>
                                <input type="text" id="description" name="description" 
                                       placeholder="Breve descrizione">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-link"></i> Crea Link Accorciato
                        </button>
                    </form>
                </div>

                <!-- Lista link accorciati -->
                <div class="links-section">
                    <div class="section-header">
                        <h2><i class="fas fa-list"></i> I Tuoi Link Accorciati</h2>
                    </div>

                    <div class="short-links-list">
                        <?php if (empty($short_links)): ?>
                            <div class="empty-state">
                                <i class="fas fa-link"></i>
                                <h3>Nessun link accorciato</h3>
                                <p>Crea il tuo primo link accorciato per iniziare!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($short_links as $link): ?>
                                <div class="short-link-item">
                                    <div class="short-link-info">
                                        <div class="short-link-title">
                                            <?php echo htmlspecialchars($link['title'] ?: 'Senza titolo'); ?>
                                        </div>
                                        <div class="short-link-url">
                                            <strong>Originale:</strong> <?php echo htmlspecialchars($link['original_url']); ?>
                                        </div>
                                        <div class="short-link-short">
                                            <strong>Accorciato:</strong> 
                                            <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/short.php?code=' . $link['short_code']; ?>" 
                                               target="_blank" class="short-url">
                                                <?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/short.php?code=' . $link['short_code']; ?>
                                            </a>
                                            <button onclick="copyToClipboard('<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/short.php?code=' . $link['short_code']; ?>')" 
                                                    class="btn-copy">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <?php if ($link['description']): ?>
                                            <div class="short-link-description">
                                                <?php echo htmlspecialchars($link['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="short-link-stats">
                                            <span><i class="fas fa-mouse-pointer"></i> <?php echo $link['click_count']; ?> click</span>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($link['created_at'])); ?></span>
                                        </div>
                                        
                                        <!-- QR Code -->
                                        <div class="qr-code-section">
                                            <?php 
                                            $qr_path = getShortLinkQRCode($_SESSION['user_id'], $link['short_code']);
                                            if ($qr_path): 
                                            ?>
                                                <div class="qr-code">
                                                    <img src="<?php echo htmlspecialchars($qr_path); ?>" 
                                                         alt="QR Code" 
                                                         class="qr-image"
                                                         onclick="showQRModal('<?php echo htmlspecialchars($qr_path); ?>', '<?php echo htmlspecialchars($link['title'] ?: 'Link'); ?>')">
                                                    <button class="btn-qr" onclick="showQRModal('<?php echo htmlspecialchars($qr_path); ?>', '<?php echo htmlspecialchars($link['title'] ?: 'Link'); ?>')">
                                                        <i class="fas fa-qrcode"></i> QR Code
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="short-link-actions">
                                        <button class="btn-stats" onclick="showStats(<?php echo $link['id']; ?>)">
                                            <i class="fas fa-chart-bar"></i> Statistiche
                                        </button>
                                        <button class="btn-delete" onclick="deleteShortLink(<?php echo $link['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Statistiche -->
    <div id="statsModal" class="modal">
        <div class="modal-content stats-modal">
            <div class="modal-header">
                <h3 id="statsModalTitle">Statistiche Link</h3>
                <button class="close-modal" onclick="closeModal('statsModal')">&times;</button>
            </div>
            <div id="statsContent">
                <!-- Le statistiche verranno caricate qui via AJAX -->
            </div>
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
                    <button class="btn btn-primary" onclick="downloadQR()">
                        <i class="fas fa-download"></i> Scarica QR Code
                    </button>
                    <button class="btn btn-secondary" onclick="copyQRUrl()">
                        <i class="fas fa-copy"></i> Copia URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function showStats(linkId) {
            openModal('statsModal');
            
            // Mostra loading
            document.getElementById('statsContent').innerHTML = '<div class="loading-stats"><i class="fas fa-spinner fa-spin"></i> Caricamento statistiche...</div>';
            
            // Carica le statistiche via AJAX
            fetch('api/stats.php?link_id=' + linkId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayStats(data.stats);
                    } else {
                        document.getElementById('statsContent').innerHTML = '<div class="error">Errore nel caricamento delle statistiche</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('statsContent').innerHTML = '<div class="error">Errore di connessione</div>';
                });
        }
        
        function displayStats(stats) {
            const content = `
                <div class="stats-overview">
                    <div class="stat-item">
                        <h4>${stats.link.click_count}</h4>
                        <p>Click Totali</p>
                    </div>
                    <div class="stat-item">
                        <h4>${stats.link.title || 'Senza titolo'}</h4>
                        <p>Titolo Link</p>
                    </div>
                </div>
                
                <div class="charts-container">
                    <div class="chart-section">
                        <h4>Click per Dispositivo</h4>
                        <canvas id="deviceChart"></canvas>
                    </div>
                    
                    <div class="chart-section">
                        <h4>Click per Browser</h4>
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>
            `;
            
            document.getElementById('statsContent').innerHTML = content;
            
            // Crea i grafici
            createDeviceChart(stats.device_clicks);
            createBrowserChart(stats.browser_clicks);
        }
        
        function createDeviceChart(data) {
            const ctx = document.getElementById('deviceChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.device_type),
                    datasets: [{
                        data: data.map(item => item.clicks),
                        backgroundColor: ['#667eea', '#764ba2', '#f093fb']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function createBrowserChart(data) {
            const ctx = document.getElementById('browserChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.browser),
                    datasets: [{
                        label: 'Click',
                        data: data.map(item => item.clicks),
                        backgroundColor: '#667eea'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function deleteShortLink(linkId) {
            if (confirm('Sei sicuro di voler eliminare questo link accorciato?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'short-links.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_short_link';
                
                const linkIdInput = document.createElement('input');
                linkIdInput.type = 'hidden';
                linkIdInput.name = 'short_link_id';
                linkIdInput.value = linkId;
                
                form.appendChild(actionInput);
                form.appendChild(linkIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Funzioni per QR Code
        let currentQRPath = '';
        let currentQRUrl = '';
        
        function showQRModal(qrPath, title) {
            currentQRPath = qrPath;
            currentQRUrl = window.location.origin + '/' + qrPath;
            
            document.getElementById('qrModalTitle').textContent = 'QR Code - ' + title;
            document.getElementById('qrImage').src = qrPath;
            openModal('qrModal');
        }
        
        function downloadQR() {
            if (currentQRPath) {
                const link = document.createElement('a');
                link.href = currentQRPath;
                link.download = 'qr-code.png';
                link.click();
            }
        }
        
        function copyQRUrl() {
            if (currentQRUrl) {
                navigator.clipboard.writeText(currentQRUrl).then(function() {
                    showNotification('URL QR Code copiato negli appunti!', 'success');
                });
            }
        }
    </script>
</body>
</html>
