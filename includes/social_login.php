<?php
require_once 'config/database.php';

// Configurazioni per social login
define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');
define('GOOGLE_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/auth/google_callback.php');

// Funzione per generare state token
function generateStateToken() {
    return bin2hex(random_bytes(32));
}

// Funzione per verificare state token
function verifyStateToken($state) {
    if (!isset($_SESSION['oauth_state'])) {
        return false;
    }
    return hash_equals($_SESSION['oauth_state'], $state);
}

// Funzione per ottenere URL di autorizzazione Google
function getGoogleAuthUrl() {
    $state = generateStateToken();
    $_SESSION['oauth_state'] = $state;
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'scope' => 'openid email profile',
        'response_type' => 'code',
        'state' => $state,
        'access_type' => 'offline'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

// Funzione per scambiare code con access token (Google)
function exchangeGoogleCode($code) {
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    ];
    
    $response = makeHttpRequest('https://oauth2.googleapis.com/token', $data);
    return json_decode($response, true);
}

// Funzione per ottenere informazioni utente (Google)
function getGoogleUserInfo($access_token) {
    $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Funzione per fare richieste HTTP
function makeHttpRequest($url, $data = null, $method = 'POST') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        return false;
    }
    
    curl_close($ch);
    return $response;
}

// Funzione per gestire il login/registrazione utente tramite social
function handleSocialLogin($provider, $user_info) {
    global $pdo;
    
    $social_id_field = $provider . '_id';
    $email = $user_info['email'] ?? null;
    $display_name = $user_info['name'] ?? $user_info['given_name'] ?? $user_info['email'];
    $avatar_url = $user_info['picture'] ?? null;
    
    // Cerca utente esistente per social ID
    $stmt = $pdo->prepare("SELECT * FROM users WHERE {$social_id_field} = ?");
    $stmt->execute([$user_info['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Utente esistente, aggiorna sessione
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    } else {
        // Cerca utente esistente per email
        if ($email) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Utente esistente con email, collega l'account social
                $stmt = $pdo->prepare("UPDATE users SET {$social_id_field} = ? WHERE id = ?");
                $stmt->execute([$user_info['id'], $user['id']]);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                return true;
            }
        }
        
        // Nuovo utente, registra
        $username = generateUniqueUsername($display_name);
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Password casuale per social login
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, display_name, avatar_url, {$social_id_field}) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $display_name, $avatar_url, $user_info['id']]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        return true;
    }
}

// Funzione per generare username unico
function generateUniqueUsername($base_name) {
    global $pdo;
    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $base_name));
    if (empty($base_username)) {
        $base_username = 'user';
    }
    $username = $base_username;
    $counter = 1;
    
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $username = $base_username . $counter++;
    }
}
?>
