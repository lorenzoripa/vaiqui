# 🚀 Guida Deployment - VaiQui

Questa guida ti aiuta a deployare VaiQui su GitHub e collegarlo al tuo server Plesk.

## 📋 Prerequisiti

- Account GitHub
- Server Plesk con accesso
- Database MySQL
- PHP 7.4+ sul server
- Accesso SSH o File Manager Plesk

## 🔧 Step 1: Preparazione GitHub

### 1.1 Crea Repository GitHub

1. Vai su [GitHub.com](https://github.com)
2. Clicca **"New repository"**
3. Nome repository: `vaiqui` (o quello che preferisci)
4. Descrizione: `VaiQui - Il tuo Linktree personale`
5. ✅ **Public** (se vuoi che sia open source)
6. ✅ **Add README**
7. Clicca **"Create repository"**

### 1.2 Inizializza Git Locale

```bash
# Nel terminale, dalla cartella del progetto
cd /Users/lorenzoripa/Desktop/Lavoro/vaiqui.it

# Inizializza Git
git init

# Aggiungi tutti i file
git add .

# Prima commit
git commit -m "Initial commit: VaiQui project setup"

# Collega al repository GitHub
git remote add origin https://github.com/TUOUSERNAME/vaiqui.git

# Push del codice
git push -u origin main
```

### 1.3 Configura File Sensibili

**IMPORTANTE**: Non committare mai le credenziali del database!

```bash
# Assicurati che config/database.php sia nel .gitignore
echo "config/database.php" >> .gitignore

# Crea file di esempio
cp config/database.php config/database.example.php

# Rimuovi il file reale dal tracking
git rm --cached config/database.php
```

## 🏗️ Step 2: Configurazione Plesk

### 2.1 Crea Database MySQL

1. **Accedi a Plesk**
2. Vai su **"Database"** → **"Add Database"**
3. Nome database: `vaiqui_db`
4. Username: `vaiqui_db_usr`
5. Password: `[password_sicura]`
6. Clicca **"OK"**

### 2.2 Configura Dominio/Subdominio

**Opzione A: Sottodominio**
1. Vai su **"Domains"** → **"Add Domain"**
2. Nome: `vaiqui.tuodominio.com`
3. Document Root: `/httpdocs/vaiqui`

**Opzione B: Sottocartella**
1. Vai su **"File Manager"**
2. Crea cartella: `/httpdocs/vaiqui`

### 2.3 Configura PHP

1. Vai su **"PHP Settings"**
2. Versione PHP: **7.4+** (raccomandato 8.0+)
3. Estensioni necessarie:
   - ✅ PDO
   - ✅ PDO_MySQL
   - ✅ cURL
   - ✅ JSON
   - ✅ GD (per QR codes)

## 🔄 Step 3: Deploy Automatico con Git

### 3.1 Configura Webhook (Raccomandato)

1. **Nel repository GitHub:**
   - Vai su **Settings** → **Webhooks**
   - **Payload URL**: `https://tuodominio.com/webhook.php`
   - **Content type**: `application/json`
   - **Events**: `Just the push event`
   - Clicca **"Add webhook"**

2. **Crea webhook.php nel server:**
```php
<?php
// webhook.php - Deploy automatico
$secret = 'your_webhook_secret_here';
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';

if (hash_equals('sha1=' . hash_hmac('sha1', $payload, $secret), $signature)) {
    // Esegui git pull
    exec('cd /path/to/vaiqui && git pull origin main');
    error_log('Deploy completed');
}
?>
```

### 3.2 Deploy Manuale

**Opzione A: SSH**
```bash
# Connettiti al server via SSH
ssh user@tuoserver.com

# Vai nella cartella del progetto
cd /path/to/vaiqui

# Clona il repository
git clone https://github.com/TUOUSERNAME/vaiqui.git .

# Aggiorna quando necessario
git pull origin main
```

**Opzione B: File Manager Plesk**
1. Vai su **"File Manager"**
2. Carica tutti i file del progetto
3. Estrai l'archivio nella cartella corretta

## ⚙️ Step 4: Configurazione Finale

### 4.1 Crea config/database.php

```php
<?php
// Configurazione database per produzione
$host = 'localhost';
$dbname = 'vaiqui_db';
$username = 'vaiqui_db_usr';
$password = 'TUA_PASSWORD_DATABASE';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    createTables($pdo);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
?>
```

### 4.2 Configura .htaccess

Assicurati che il file `.htaccess` sia presente:

```apache
RewriteEngine On

# Redirect per profili utente
RewriteRule ^([a-zA-Z0-9_-]+)/?$ profile.php?user=$1 [L,QSA]

# Redirect per link accorciati
RewriteRule ^s/([a-zA-Z0-9]+)/?$ short.php?code=$1 [L,QSA]

# Sicurezza
<Files "config/database.php">
    Order Allow,Deny
    Deny from all
</Files>

# Cache per performance
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>
```

### 4.3 Configura Permessi

```bash
# Imposta permessi corretti
chmod 755 /path/to/vaiqui
chmod 644 /path/to/vaiqui/*.php
chmod 644 /path/to/vaiqui/.htaccess
chmod 755 /path/to/vaiqui/assets/
chmod 755 /path/to/vaiqui/includes/
```

## 🔐 Step 5: Configurazione Sicurezza

### 5.1 SSL Certificate

1. **Plesk** → **SSL/TLS Certificates**
2. **Let's Encrypt** → **Get Certificate**
3. ✅ **Include www subdomain**

### 5.2 Configurazione Social Login

1. **Google OAuth:**
   - Vai su [Google Cloud Console](https://console.cloud.google.com)
   - Crea nuovo progetto o seleziona esistente
   - **APIs & Services** → **Credentials**
   - **Create Credentials** → **OAuth 2.0 Client ID**
   - **Authorized redirect URIs**: `https://tuodominio.com/auth/google_callback.php`

2. **Aggiorna includes/social_login.php:**
```php
define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');
define('GOOGLE_REDIRECT_URI', 'https://tuodominio.com/auth/google_callback.php');
```

## 📊 Step 6: Monitoraggio e Backup

### 6.1 Backup Database

**Script backup automatico:**
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u vaiqui_db_usr -p vaiqui_db > backup_vaiqui_$DATE.sql
```

### 6.2 Monitoraggio Errori

**Crea log/error.log:**
```php
// In config/database.php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
```

## 🚀 Step 7: Test Finale

### 7.1 Checklist Pre-Lancio

- [ ] ✅ Database creato e configurato
- [ ] ✅ File config/database.php creato
- [ ] ✅ Permessi file corretti
- [ ] ✅ SSL certificate attivo
- [ ] ✅ .htaccess configurato
- [ ] ✅ Social login configurato
- [ ] ✅ Test registrazione utente
- [ ] ✅ Test creazione profilo
- [ ] ✅ Test link e mappa
- [ ] ✅ Test responsive design

### 7.2 Test Funzionalità

1. **Registrazione**: Crea un account di test
2. **Profilo**: Personalizza il profilo
3. **Link**: Aggiungi alcuni link
4. **Indirizzo**: Testa la funzionalità mappa
5. **Profilo Pubblico**: Verifica che tutto funzioni

## 🔧 Troubleshooting

### Problema: Database Connection Failed
```bash
# Verifica credenziali database
mysql -u vaiqui_db_usr -p vaiqui_db
```

### Problema: 500 Internal Server Error
```bash
# Controlla error log
tail -f /var/log/apache2/error.log
# o
tail -f /path/to/vaiqui/logs/error.log
```

### Problema: Rewrite Rules Non Funzionano
```apache
# Verifica che mod_rewrite sia abilitato
# In Plesk: Tools & Settings → Apache & nginx Settings
```

## 📞 Supporto

Se hai problemi durante il deployment:

1. **Controlla i log** di errore
2. **Verifica i permessi** dei file
3. **Testa la connessione** database
4. **Controlla la configurazione** PHP

---

**🎉 Congratulazioni! Il tuo VaiQui è ora live!**

Ricorda di:
- ✅ Fare backup regolari del database
- ✅ Aggiornare le dipendenze
- ✅ Monitorare le performance
- ✅ Tenere aggiornato il codice da GitHub
