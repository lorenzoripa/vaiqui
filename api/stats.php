<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit();
}

// Ottieni l'ID del link dalla query string
$link_id = $_GET['link_id'] ?? '';

if (empty($link_id) || !is_numeric($link_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID link non valido']);
    exit();
}

// Verifica che il link appartenga all'utente
$stmt = $pdo->prepare("SELECT user_id FROM short_links WHERE id = ?");
$stmt->execute([$link_id]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link || $link['user_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit();
}

// Ottieni le statistiche
$stats = getShortLinkStats($link_id);

if ($stats) {
    echo json_encode(['success' => true, 'stats' => $stats]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel recupero delle statistiche']);
}
?>
