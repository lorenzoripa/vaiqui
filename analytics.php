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

// Ottieni statistiche generali
$stats = getUserStats($_SESSION['user_id']);

// Statistiche avanzate
try {
    // Click per giorno (ultimi 30 giorni)
    $stmt = $pdo->prepare("
        SELECT DATE(clicked_at) as date, COUNT(*) as clicks 
        FROM analytics 
        WHERE user_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(clicked_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $daily_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Click per ora del giorno
    $stmt = $pdo->prepare("
        SELECT HOUR(clicked_at) as hour, COUNT(*) as clicks 
        FROM analytics 
        WHERE user_id = ? 
        GROUP BY HOUR(clicked_at)
        ORDER BY hour ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $hourly_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top link per click
    $stmt = $pdo->prepare("
        SELECT l.title, l.url, l.click_count, l.color
        FROM links l 
        WHERE l.user_id = ? AND l.is_active = TRUE
        ORDER BY l.click_count DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $top_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Click per dispositivo
    $stmt = $pdo->prepare("
        SELECT device_type, COUNT(*) as clicks 
        FROM analytics 
        WHERE user_id = ? 
        GROUP BY device_type 
        ORDER BY clicks DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $device_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Click per browser
    $stmt = $pdo->prepare("
        SELECT browser, COUNT(*) as clicks 
        FROM analytics 
        WHERE user_id = ? 
        GROUP BY browser 
        ORDER BY clicks DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $browser_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Click per paese
    $stmt = $pdo->prepare("
        SELECT country, COUNT(*) as clicks 
        FROM analytics 
        WHERE user_id = ? 
        GROUP BY country 
        ORDER BY clicks DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $country_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistiche mensili
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(clicked_at, '%Y-%m') as month,
            COUNT(*) as clicks
        FROM analytics 
        WHERE user_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(clicked_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $monthly_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $daily_clicks = [];
    $hourly_clicks = [];
    $top_links = [];
    $device_clicks = [];
    $browser_clicks = [];
    $country_clicks = [];
    $monthly_clicks = [];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-chart-line"></i> Analytics Avanzate</h1>
                <p>Analizza le performance dei tuoi link</p>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Torna al Dashboard
                    </a>
                    <a href="short-links.php" class="btn btn-outline">
                        <i class="fas fa-compress"></i> Link Accorciati
                    </a>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Statistiche Overview -->
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
                    <div class="stat-card">
                        <h3><?php echo count($top_links); ?></h3>
                        <p>Link Popolari</p>
                    </div>
                </div>

                <!-- Grafici -->
                <div class="charts-section">
                    <div class="chart-container">
                        <h3><i class="fas fa-chart-area"></i> Click per Giorno (Ultimi 30 giorni)</h3>
                        <canvas id="dailyChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3><i class="fas fa-clock"></i> Click per Ora del Giorno</h3>
                        <canvas id="hourlyChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3><i class="fas fa-mobile-alt"></i> Click per Dispositivo</h3>
                        <canvas id="deviceChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3><i class="fas fa-globe"></i> Click per Browser</h3>
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>

                <!-- Top Links -->
                <div class="top-links-section">
                    <h3><i class="fas fa-trophy"></i> Link Più Cliccati</h3>
                    <div class="top-links-list">
                        <?php if (empty($top_links)): ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-bar"></i>
                                <p>Nessun dato disponibile</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($top_links as $index => $link): ?>
                                <div class="top-link-item">
                                    <div class="rank"><?php echo $index + 1; ?></div>
                                    <div class="link-icon" style="background-color: <?php echo htmlspecialchars($link['color']); ?>">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <div class="link-info">
                                        <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                                        <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                                    </div>
                                    <div class="link-clicks">
                                        <span class="click-count"><?php echo $link['click_count']; ?></span>
                                        <span class="click-label">click</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dati per i grafici
        const dailyData = <?php echo json_encode($daily_clicks); ?>;
        const hourlyData = <?php echo json_encode($hourly_clicks); ?>;
        const deviceData = <?php echo json_encode($device_clicks); ?>;
        const browserData = <?php echo json_encode($browser_clicks); ?>;

        // Grafico click per giorno
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(item => item.date),
                datasets: [{
                    label: 'Click',
                    data: dailyData.map(item => item.clicks),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Grafico click per ora
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(item => item.hour + ':00'),
                datasets: [{
                    label: 'Click',
                    data: hourlyData.map(item => item.clicks),
                    backgroundColor: '#ff8c42'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Grafico dispositivi
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: deviceData.map(item => item.device_type),
                datasets: [{
                    data: deviceData.map(item => item.clicks),
                    backgroundColor: ['#667eea', '#ff8c42', '#f093fb', '#f5576c']
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

        // Grafico browser
        const browserCtx = document.getElementById('browserChart').getContext('2d');
        new Chart(browserCtx, {
            type: 'bar',
            data: {
                labels: browserData.map(item => item.browser),
                datasets: [{
                    label: 'Click',
                    data: browserData.map(item => item.clicks),
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
