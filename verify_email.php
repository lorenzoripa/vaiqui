<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (empty($token)) {
    $message = 'Token di verifica non fornito';
} else {
    $result = verifyEmailToken($token);
    
    if ($result['success']) {
        $success = true;
        $message = 'Email verificata con successo! Ora puoi accedere al tuo account.';
        
        // Se l'utente è già loggato, aggiorna la sessione
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $result['user_id']) {
            // L'utente è già loggato, reindirizza al dashboard
            header('Location: dashboard.php?verified=1');
            exit();
        }
    } else {
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica Email - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style media="not all">
        .verification-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .verification-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .verification-icon.success {
            color: #28a745;
        }
        
        .verification-icon.error {
            color: #dc3545;
        }
        
        .verification-container h1 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .verification-container p {
            margin-bottom: 30px;
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body class="theme-landing auth-page">
    <div class="container">
        <div class="verification-container">
            <?php if ($success): ?>
                <div class="verification-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Email Verificata!</h1>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Vai al Login
                </a>
            <?php else: ?>
                <div class="verification-icon error">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h1>Verifica Fallita</h1>
                <p><?php echo htmlspecialchars($message); ?></p>
                <div style="margin-top: 20px;">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Torna alla Home
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-primary" style="margin-left: 10px;">
                            <i class="fas fa-redo"></i> Richiedi Nuovo Link
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

