<?php
/**
 * Webhook per Deploy Automatico da GitHub
 * 
 * ISTRUZIONI:
 * 1. Configura questo file nel tuo server
 * 2. Aggiungi l'URL di questo file come webhook in GitHub
 * 3. Ogni push su GitHub farà il deploy automatico
 */

// Configurazione
$secret = 'your_webhook_secret_here'; // Cambia con una stringa sicura
$repo_path = '/path/to/vaiqui'; // Percorso del progetto sul server
$branch = 'main'; // Branch da monitorare

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Ottieni il payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';

// Verifica la signature (sicurezza)
if (!empty($secret)) {
    $expected_signature = 'sha1=' . hash_hmac('sha1', $payload, $secret);
    if (!hash_equals($expected_signature, $signature)) {
        http_response_code(403);
        die('Invalid signature');
    }
}

// Decodifica il payload JSON
$data = json_decode($payload, true);

// Verifica che sia un push sul branch corretto
if (isset($data['ref']) && $data['ref'] === "refs/heads/{$branch}") {
    
    // Log dell'evento
    $log_message = "[" . date('Y-m-d H:i:s') . "] Deploy triggered by push to {$branch}\n";
    file_put_contents(__DIR__ . '/deploy.log', $log_message, FILE_APPEND);
    
    // Esegui git pull
    $output = [];
    $return_code = 0;
    
    $command = "cd {$repo_path} && git pull origin {$branch} 2>&1";
    exec($command, $output, $return_code);
    
    // Log del risultato
    $log_message = "[" . date('Y-m-d H:i:s') . "] Git pull result: " . implode("\n", $output) . "\n";
    file_put_contents(__DIR__ . '/deploy.log', $log_message, FILE_APPEND);
    
    if ($return_code === 0) {
        // Deploy riuscito
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Deploy completed successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Opzionale: Invia notifica email
        // sendDeployNotification('success', $output);
        
    } else {
        // Deploy fallito
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Deploy failed',
            'error' => implode("\n", $output),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Opzionale: Invia notifica email di errore
        // sendDeployNotification('error', $output);
    }
    
} else {
    // Non è un push sul branch monitorato
    http_response_code(200);
    echo json_encode([
        'status' => 'ignored',
        'message' => 'Push not on monitored branch',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Invia notifica email del deploy (opzionale)
 */
function sendDeployNotification($status, $output) {
    $to = 'admin@tuodominio.com'; // Cambia con la tua email
    $subject = "Deploy VaiQui - {$status}";
    $message = "Deploy status: {$status}\n\nOutput:\n" . implode("\n", $output);
    
    mail($to, $subject, $message);
}

?>
