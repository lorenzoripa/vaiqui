<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Ottieni il codice breve dall'URL
$short_code = $_GET['code'] ?? '';

if (empty($short_code)) {
    header('HTTP/1.0 404 Not Found');
    exit('Link non trovato');
}

// Reindirizza al link originale
$original_url = redirectShortLink($short_code);

if ($original_url) {
    header('Location: ' . $original_url);
    exit();
} else {
    header('HTTP/1.0 404 Not Found');
    exit('Link non trovato o scaduto');
}
?>
