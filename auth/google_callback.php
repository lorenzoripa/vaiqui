<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/social_login.php';

if (isset($_GET['code']) && isset($_GET['state'])) {
    // Verifica state token
    if (!verifyStateToken($_GET['state'])) {
        header('Location: ../index.php?error=invalid_state');
        exit();
    }
    
    // Scambia code con access token
    $token_data = exchangeGoogleCode($_GET['code']);
    
    if (!$token_data || !isset($token_data['access_token'])) {
        header('Location: ../index.php?error=token_exchange_failed');
        exit();
    }
    
    // Ottieni informazioni utente
    $user_info = getGoogleUserInfo($token_data['access_token']);
    
    if (!$user_info || !isset($user_info['email'])) {
        header('Location: ../index.php?error=user_info_failed');
        exit();
    }
    
    // Crea o aggiorna utente
    $user = createOrUpdateSocialUser(
        'google',
        $user_info['id'],
        $user_info['email'],
        $user_info['name'],
        $user_info['picture'] ?? null
    );
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../dashboard.php');
        exit();
    } else {
        header('Location: ../index.php?error=user_creation_failed');
        exit();
    }
} else {
    header('Location: ../index.php?error=missing_parameters');
    exit();
}
?>
