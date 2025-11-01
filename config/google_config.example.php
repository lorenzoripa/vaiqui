<?php
/**
 * Configurazione Google OAuth 2.0
 * 
 * Per configurare l'autenticazione con Google:
 * 1. Vai su https://console.cloud.google.com/
 * 2. Crea un nuovo progetto o seleziona uno esistente
 * 3. Abilita Google+ API
 * 4. Vai su "Credenziali" e crea "ID client OAuth 2.0"
 * 5. Aggiungi gli URI di reindirizzamento autorizzati:
 *    - http://tuo-dominio.com/auth/google_callback.php
 *    - https://tuo-dominio.com/auth/google_callback.php (per produzione)
 * 6. Copia i valori qui sotto
 * 7. Rinomina questo file in google_config.php
 */

// ID Client Google OAuth
define('GOOGLE_CLIENT_ID', 'your_google_client_id_here');

// Secret Client Google OAuth
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here');

// URI di reindirizzamento (sarà generato automaticamente)
define('GOOGLE_REDIRECT_URI', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/auth/google_callback.php");

// Scopes richiesti (non modificare a meno che non sia necessario)
define('GOOGLE_SCOPES', 'openid email profile');
?>


