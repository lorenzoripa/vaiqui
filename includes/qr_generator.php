<?php
// Generatore QR Code semplice senza dipendenze esterne
// Usa Google Charts API per generare QR code

function generateQRCode($data, $size = 200) {
    $encoded_data = urlencode($data);
    $qr_url = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encoded_data}";
    return $qr_url;
}

// Funzione per salvare il QR code come immagine
function saveQRCode($data, $filename, $size = 200) {
    $qr_url = generateQRCode($data, $size);
    
    $image_data = false;

    // Prova con cURL se disponibile
    if (function_exists('curl_init')) {
        $ch = curl_init($qr_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VaiQui QR Generator/1.0');
        $image_data = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('QR cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    // Fallback a file_get_contents se cURL non disponibile o fallisce
    if ($image_data === false) {
        $image_data = @file_get_contents($qr_url);
    }

    if ($image_data === false) {
        error_log('QR download fallito per URL: ' . $qr_url);
        return false;
    }

    // Salva l'immagine
    $result = @file_put_contents($filename, $image_data);
    
    if ($result === false) {
        error_log('QR salvataggio fallito per file: ' . $filename);
    }

    return $result !== false;
}

// Funzione per generare QR code per un link
function generateLinkQRCode($link_url, $user_id, $link_id = null) {
    $root_dir = realpath(__DIR__ . '/..');
    $qr_dir_abs = $root_dir . '/assets/qr_codes/';
    $qr_dir_public = 'assets/qr_codes/';

    // Crea la directory se non esiste
    if (!is_dir($qr_dir_abs)) {
        @mkdir($qr_dir_abs, 0755, true);
    }

    // Nome file unico
    $file_basename = 'qr_' . $user_id . '_' . ($link_id ?: time()) . '.png';
    $filename_abs = $qr_dir_abs . $file_basename;
    $filename_public = $qr_dir_public . $file_basename;

    // Genera e salva il QR code
    if (saveQRCode($link_url, $filename_abs)) {
        return $filename_public;
    }

    return false;
}

// Funzione per generare QR code per link accorciato
function generateShortLinkQRCode($short_code, $user_id) {
    global $pdo;
    
    // Ottieni l'URL completo del link accorciato
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    }

    $base_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/short.php?code=' . urlencode($short_code);
    
    $saved_path = generateLinkQRCode($base_url, $user_id, 'short_' . $short_code);

    if ($saved_path) {
        return $saved_path;
    }

    // Fallback: restituisci direttamente l'URL del QR generato da Google Charts
    return generateQRCode($base_url);
}

// Funzione per ottenere il QR code di un link esistente
function getLinkQRCode($user_id, $link_id) {
    global $pdo;
    
    try {
        // Controlla se il QR code esiste già
        $qr_path = "assets/qr_codes/qr_" . $user_id . "_" . $link_id . ".png";
        
        if (file_exists($qr_path)) {
            return $qr_path;
        }
        
        // Ottieni l'URL del link
        $stmt = $pdo->prepare("SELECT url FROM links WHERE id = ? AND user_id = ?");
        $stmt->execute([$link_id, $user_id]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            return false;
        }
        
        // Genera il QR code
        return generateLinkQRCode($link['url'], $user_id, $link_id);
        
    } catch (PDOException $e) {
        return false;
    }
}

// Funzione per ottenere il QR code di un link accorciato
function getShortLinkQRCode($user_id, $short_code) {
    $root_dir = realpath(__DIR__ . '/..');
    $qr_rel = 'assets/qr_codes/qr_' . $user_id . '_short_' . $short_code . '.png';
    $qr_abs = $root_dir . '/' . $qr_rel;

    if (file_exists($qr_abs)) {
        return $qr_rel;
    }

    $generated = generateShortLinkQRCode($short_code, $user_id);

    if ($generated) {
        $generated_abs = $root_dir . '/' . ltrim($generated, '/');
        if (file_exists($generated_abs)) {
            return ltrim($generated, '/');
        }
    }

    return $generated; // potrebbe essere un URL remoto
}

// Funzione per eliminare QR code
function deleteQRCode($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return true;
}
?>
