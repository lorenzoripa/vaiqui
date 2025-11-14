<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verifica che l'utente sia loggato e sia admin
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$admin_user = getUser($_SESSION['user_id']);
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
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e5e9;
        }
        
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #e1e5e9;
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
            gap: 10px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
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
    </style>
</head>
<body>
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
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('ATTENZIONE: Eliminare questo utente eliminerà anche tutti i suoi link e dati. Continuare?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
</body>
</html>

