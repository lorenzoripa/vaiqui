<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verifica che l'utente sia loggato e sia admin
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$admin_user = getUser($_SESSION['user_id']);
$is_verified = $admin_user && !empty($admin_user['email_verified']);
if (!$is_verified) {
    header('Location: auth.php?verify=1');
    exit();
}
$stats = getAdminStats();

// Gestione paginazione e ricerca
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;
$search = trim($_GET['search'] ?? '');

$users = getAllUsers($per_page, $offset, $search);
$total_users = getTotalUsers($search);
$total_pages = ceil($total_users / $per_page);

// Debug temporaneo (rimuovere in produzione)
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Total users: " . $total_users . "\n";
    echo "Users found: " . count($users) . "\n";
    echo "Page: " . $page . "\n";
    echo "Offset: " . $offset . "\n";
    print_r($users);
    echo "</pre>";
    exit;
}

// Gestione azioni admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_role':
            $target_user_id = (int)$_POST['user_id'];
            $new_role = $_POST['role'];
            
            // Previeni auto-demozione
            if ($target_user_id === $_SESSION['user_id'] && $new_role !== 'admin') {
                $error = "Non puoi rimuovere i tuoi privilegi admin!";
            } else {
                if (updateUserRole($target_user_id, $new_role)) {
                    $success = "Ruolo utente aggiornato con successo!";
                    // Ricarica lista
                    $users = getAllUsers($per_page, $offset, $search);
                } else {
                    $error = "Errore durante l'aggiornamento del ruolo";
                }
            }
            break;
            
        case 'delete_user':
            $target_user_id = (int)$_POST['user_id'];
            
            // Previeni auto-eliminazione
            if ($target_user_id === $_SESSION['user_id']) {
                $error = "Non puoi eliminare il tuo stesso account!";
            } else {
                if (deleteUser($target_user_id)) {
                    $success = "Utente eliminato con successo!";
                    // Ricarica lista
                    $users = getAllUsers($per_page, $offset, $search);
                    $total_users = getTotalUsers($search);
                    $total_pages = ceil($total_users / $per_page);
                } else {
                    $error = "Errore durante l'eliminazione dell'utente";
                }
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Amministrativa - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style media="not all">
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .admin-stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .admin-stat-card h3 {
            font-size: 2rem;
            margin: 0 0 10px 0;
            color: #667eea;
        }
        
        .admin-stat-card p {
            margin: 0;
            color: #666;
        }
        
        .users-table {
            background: white;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: visible;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 100%;
        }
        
        .users-table table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #f8f9fa;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e5e9;
            white-space: nowrap;
        }
        
        .users-table th:last-child {
            position: sticky;
            right: 0;
            background: #f8f9fa;
            z-index: 10;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }
        
        .users-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e1e5e9;
            white-space: nowrap;
        }
        
        .users-table td:last-child {
            position: sticky;
            right: 0;
            background: white;
            z-index: 5;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }
        
        .users-table tr:hover td:last-child {
            background: #f8f9fa;
        }
        
        .users-table tr:hover {
            background: #f8f9fa;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .role-badge.admin {
            background: #dc3545;
            color: white;
        }
        
        .role-badge.user {
            background: #28a745;
            color: white;
        }
        
        .admin-actions {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
            min-width: 200px;
        }
        
        .admin-actions form {
            display: inline-block;
        }
        
        .admin-actions select {
            padding: 6px 8px;
            font-size: 0.85rem;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            background: white;
            cursor: pointer;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .btn-danger.btn-sm {
            min-width: 36px;
            padding: 6px 8px;
        }
        
        .btn-danger.btn-sm i {
            font-size: 0.9rem;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box form {
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Scrollbar personalizzata per la tabella */
        .users-table::-webkit-scrollbar {
            height: 8px;
        }
        
        .users-table::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .users-table::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .users-table::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Assicura che il container non tagli la tabella */
        .container {
            max-width: 100%;
            overflow-x: visible;
        }
        
        .dashboard {
            max-width: 100%;
            overflow-x: visible;
        }
    </style>
</head>
<body class="theme-landing dashboard-page admin-page">
    <div class="container">
        <div class="dashboard">
            <div class="admin-header">
                <h1><i class="fas fa-shield-alt"></i> Area Amministrativa</h1>
                <p>Benvenuto, <?php echo htmlspecialchars($admin_user['display_name'] ?? $admin_user['username']); ?>!</p>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border-color: white;">
                        <i class="fas fa-arrow-left"></i> Torna al Dashboard
                    </a>
                </div>
            </div>

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
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p><i class="fas fa-users"></i> Utenti Totali</p>
                </div>
                <div class="admin-stat-card">
                    <h3><?php echo $stats['active_users']; ?></h3>
                    <p><i class="fas fa-user-check"></i> Utenti Attivi</p>
                </div>
                <div class="admin-stat-card">
                    <h3><?php echo $stats['total_links']; ?></h3>
                    <p><i class="fas fa-link"></i> Link Totali</p>
                </div>
                <div class="admin-stat-card">
                    <h3><?php echo $stats['total_clicks']; ?></h3>
                    <p><i class="fas fa-mouse-pointer"></i> Click Totali</p>
                </div>
                <div class="admin-stat-card">
                    <h3><?php echo $stats['users_today']; ?></h3>
                    <p><i class="fas fa-calendar-day"></i> Registrati Oggi</p>
                </div>
            </div>

            <!-- Ricerca -->
            <div class="search-box">
                <form method="GET" action="admin.php">
                    <input type="text" name="search" placeholder="Cerca per username, email o nome..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cerca
                    </button>
                    <?php if ($search): ?>
                        <a href="admin.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabella Utenti -->
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Nome</th>
                            <th>Ruolo</th>
                            <th>Link</th>
                            <th>Click</th>
                            <th>Registrato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                                    <p>Nessun utente trovato</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['display_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo $user['role']; ?>">
                                            <?php echo strtoupper($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['link_count'] ?? 0; ?></td>
                                    <td><?php echo $user['click_count'] ?? 0; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Sei sicuro di voler cambiare il ruolo di questo utente?');">
                                                <input type="hidden" name="action" value="update_role">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="role" onchange="this.form.submit()" class="btn-sm">
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', <?php echo $user['link_count'] ?? 0; ?>, <?php echo $user['click_count'] ?? 0; ?>)"
                                                    title="Elimina utente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginazione -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Precedente
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            Successiva <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Conferma Eliminazione Utente -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Conferma Eliminazione Utente</h3>
                <button class="close-modal" onclick="closeModal('deleteUserModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-error">
                    <strong>ATTENZIONE:</strong> Questa azione è irreversibile!
                </div>
                <p>Stai per eliminare l'utente <strong id="deleteUserName"></strong>.</p>
                <div class="delete-user-info">
                    <p><i class="fas fa-link"></i> Link associati: <strong id="deleteUserLinks">0</strong></p>
                    <p><i class="fas fa-mouse-pointer"></i> Click totali: <strong id="deleteUserClicks">0</strong></p>
                </div>
                <p class="warning-text">
                    <i class="fas fa-exclamation-circle"></i> 
                    Eliminando questo utente verranno eliminati anche:
                </p>
                <ul class="warning-list">
                    <li>Tutti i suoi link</li>
                    <li>Tutte le statistiche e analytics</li>
                    <li>Tutti i link accorciati</li>
                    <li>Tutti i dati del profilo</li>
                </ul>
                <form id="deleteUserForm" method="POST">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="deleteUserId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">
                    <i class="fas fa-times"></i> Annulla
                </button>
                <button type="button" class="btn btn-danger" onclick="submitDeleteUser()">
                    <i class="fas fa-trash"></i> Elimina Definitivamente
                </button>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteUser(userId, username, linkCount, clickCount) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = username;
            document.getElementById('deleteUserLinks').textContent = linkCount;
            document.getElementById('deleteUserClicks').textContent = clickCount;
            openModal('deleteUserModal');
        }

        function submitDeleteUser() {
            document.getElementById('deleteUserForm').submit();
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Chiudi modale cliccando fuori
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    </script>

    <style media="not all">
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        .delete-user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .delete-user-info p {
            margin: 8px 0;
            color: #666;
        }

        .warning-text {
            color: #dc3545;
            font-weight: 600;
            margin-top: 15px;
        }

        .warning-list {
            background: #fff3cd;
            padding: 15px 15px 15px 35px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }

        .warning-list li {
            margin: 5px 0;
            color: #856404;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e1e5e9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</body>
</html>

