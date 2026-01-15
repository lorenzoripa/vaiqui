<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/social_login.php';

// Se l'utente è loggato, reindirizza al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Gestione del login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $user = loginUser($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Credenziali non valide";
        }
    } elseif ($_POST['action'] === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Le password non coincidono";
        } else {
            $result = registerUser($username, $email, $password);
            if ($result === true) {
                $success = "Registrazione completata! Ti abbiamo inviato un'email di verifica. Controlla la tua casella di posta e clicca sul link per verificare il tuo account.";
                $email_sent = true;
            } else {
                $error = $result;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaiQui - Accedi o Registrati</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style media="not all">
        /* Legacy: stile spostato in assets/css/style.css */
    </style>
</head>
<body class="theme-landing auth-page">
    <!-- Mini Hero Section -->
    <section class="hero-mini">
        <h1><i class="fas fa-link"></i> VaiQui</h1>
        <p>Il tuo Linktree personale</p>
    </section>

    <!-- Auth Section -->
    <section class="auth-section">
        <div class="container">
            <div class="back-to-landing">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Torna alla home</a>
            </div>
            
            <div class="auth-container">

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

            <div class="auth-tabs">
                <button class="tab-btn active" onclick="showTab('login')">Login</button>
                <button class="tab-btn" onclick="showTab('register')">Registrati</button>
            </div>

            <!-- Form Login -->
            <form id="login-form" class="auth-form active" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Accedi
                </button>
            </form>

            <!-- Social Login -->
            <div class="social-login">
                <div class="divider">
                    <span>oppure</span>
                </div>
                
                <div class="social-buttons">
                    <a href="<?php echo getGoogleAuthUrl(); ?>" class="btn-social btn-google">
                        <i class="fab fa-google"></i> Continua con Google
                    </a>
                </div>
            </div>

            <!-- Form Registrazione -->
            <form id="register-form" class="auth-form" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="register-username">Username</label>
                    <input type="text" id="register-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="register-confirm-password">Conferma Password</label>
                    <input type="password" id="register-confirm-password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Registrati
                </button>
            </form>
            </div>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>

