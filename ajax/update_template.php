<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/templates.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessione scaduta. Riesegui il login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

$allowed_fields = [
    'template',
    'background_type',
    'background_color',
    'background_gradient',
    'background_image',
    'text_color',
    'link_style',
    'link_color',
    'link_hover_color',
    'button_border_radius',
    'button_shadow',
    'font_family',
    'profile_layout',
    'show_social_icons',
    'custom_css'
];

$settings = [];

foreach ($allowed_fields as $field) {
    if (array_key_exists($field, $_POST)) {
        switch ($field) {
            case 'button_border_radius':
                $settings[$field] = (int)$_POST[$field];
                break;
            case 'button_shadow':
            case 'show_social_icons':
                $settings[$field] = $_POST[$field] ? 1 : 0;
                break;
            default:
                $settings[$field] = trim((string)$_POST[$field]);
                break;
        }
    }
}

if (empty($settings)) {
    echo json_encode(['success' => true, 'message' => 'Nessuna modifica']);
    exit;
}

$result = updateTemplateSettings($_SESSION['user_id'], $settings);

echo json_encode([
    'success' => (bool) $result,
    'message' => $result ? 'Impostazioni aggiornate' : 'Errore durante il salvataggio'
]);


