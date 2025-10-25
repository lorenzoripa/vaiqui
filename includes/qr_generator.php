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
    
    // Scarica l'immagine
    $image_data = file_get_contents($qr_url);
    
    if ($image_data === false) {
        return false;
    }
    
    // Salva l'immagine
    $result = file_put_contents($filename, $image_data);
    
    return $result !== false;
}

// Funzione per generare QR code per un link
function generateLinkQRCode($link_url, $user_id, $link_id = null) {
    $qr_dir = "assets/qr_codes/";
    
    // Crea la directory se non esiste
    if (!file_exists($qr_dir)) {
        mkdir($qr_dir, 0755, true);
    }
    
    // Nome file unico
    $filename = $qr_dir . "qr_" . $user_id . "_" . ($link_id ?: time()) . ".png";
    
    // Genera e salva il QR code
    if (saveQRCode($link_url, $filename)) {
        return $filename;
    }
    
    return false;
}

// Funzione per generare QR code per link accorciato
function generateShortLinkQRCode($short_code, $user_id) {
    global $pdo;
    
    // Ottieni l'URL completo del link accorciato
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/s/' . $short_code;
    
    return generateLinkQRCode($base_url, $user_id, 'short_' . $short_code);
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
    $qr_path = "assets/qr_codes/qr_" . $user_id . "_short_" . $short_code . ".png";
    
    if (file_exists($qr_path)) {
        return $qr_path;
    }
    
    return generateShortLinkQRCode($short_code, $user_id);
}

// Funzione per eliminare QR code
function deleteQRCode($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return true;
}
?>
