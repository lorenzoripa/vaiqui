#!/bin/bash
# Script di Setup per Plesk - VaiQui
# Esegui questo script sul server Plesk per configurare automaticamente VaiQui

echo "🚀 VaiQui - Setup Automatico per Plesk"
echo "======================================"

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funzione per stampare messaggi colorati
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verifica che siamo nella directory corretta
if [ ! -f "index.php" ]; then
    print_error "index.php non trovato. Assicurati di essere nella directory di VaiQui."
    exit 1
fi

print_status "Directory VaiQui trovata"

# 1. Crea directory necessarie
echo "📁 Creazione directory..."
mkdir -p logs
mkdir -p cache
mkdir -p uploads
print_status "Directory create"

# 2. Imposta permessi
echo "🔐 Configurazione permessi..."
chmod 755 .
chmod 644 *.php
chmod 644 .htaccess
chmod 755 assets/
chmod 755 includes/
chmod 755 config/
chmod 755 logs/
chmod 755 cache/
chmod 755 uploads/
print_status "Permessi configurati"

# 3. Crea file di configurazione se non esiste
if [ ! -f "config/database.php" ]; then
    echo "⚙️  Creazione file di configurazione..."
    cp config/database.example.php config/database.php
    print_warning "File config/database.php creato. RICORDA di modificarlo con le tue credenziali!"
else
    print_status "File di configurazione già presente"
fi

# 4. Crea file .env se necessario
if [ ! -f ".env" ]; then
    echo "📝 Creazione file .env..."
    cat > .env << EOF
# VaiQui Environment Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tuodominio.com

# Database (modifica con i tuoi valori)
DB_HOST=localhost
DB_NAME=vaiqui_db
DB_USER=vaiqui_db_usr
DB_PASS=your_password_here

# Social Login (opzionale)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# Security
WEBHOOK_SECRET=your_webhook_secret_here
EOF
    print_warning "File .env creato. RICORDA di modificarlo con i tuoi valori!"
fi

# 5. Configura PHP settings
echo "🐘 Configurazione PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    print_status "PHP versione $PHP_VERSION rilevata"
    
    # Verifica estensioni necessarie
    echo "🔍 Verifica estensioni PHP..."
    php -m | grep -q PDO && print_status "PDO: ✅" || print_warning "PDO: ❌ (necessario)"
    php -m | grep -q pdo_mysql && print_status "PDO MySQL: ✅" || print_warning "PDO MySQL: ❌ (necessario)"
    php -m | grep -q curl && print_status "cURL: ✅" || print_warning "cURL: ❌ (necessario)"
    php -m | grep -q json && print_status "JSON: ✅" || print_warning "JSON: ❌ (necessario)"
    php -m | grep -q gd && print_status "GD: ✅" || print_warning "GD: ❌ (necessario per QR codes)"
else
    print_error "PHP non trovato. Assicurati che PHP sia installato."
fi

# 6. Test connessione database (se configurato)
if [ -f "config/database.php" ]; then
    echo "🗄️  Test connessione database..."
    php -r "
    try {
        require_once 'config/database.php';
        echo 'Database connection: ✅\n';
    } catch (Exception \$e) {
        echo 'Database connection: ❌ - ' . \$e->getMessage() . '\n';
    }
    " 2>/dev/null || print_warning "Impossibile testare la connessione database"
fi

# 7. Crea script di backup
echo "💾 Creazione script di backup..."
cat > backup.sh << 'EOF'
#!/bin/bash
# Script di backup per VaiQui

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="backups"
DB_NAME="vaiqui_db"
DB_USER="vaiqui_db_usr"

# Crea directory backup se non esiste
mkdir -p $BACKUP_DIR

# Backup database
echo "Backing up database..."
mysqldump -u $DB_USER -p $DB_NAME > $BACKUP_DIR/vaiqui_db_$DATE.sql

# Backup file (escludendo cache e logs)
echo "Backing up files..."
tar -czf $BACKUP_DIR/vaiqui_files_$DATE.tar.gz \
    --exclude=cache \
    --exclude=logs \
    --exclude=backups \
    --exclude=*.log \
    .

echo "Backup completed: $BACKUP_DIR/vaiqui_*_$DATE.*"
EOF

chmod +x backup.sh
print_status "Script di backup creato (backup.sh)"

# 8. Crea script di aggiornamento
echo "🔄 Creazione script di aggiornamento..."
cat > update.sh << 'EOF'
#!/bin/bash
# Script di aggiornamento per VaiQui

echo "🔄 Aggiornamento VaiQui..."

# Backup prima dell'aggiornamento
echo "📦 Creazione backup..."
./backup.sh

# Git pull
echo "⬇️  Download aggiornamenti..."
git pull origin main

# Aggiorna permessi
echo "🔐 Aggiornamento permessi..."
chmod 755 .
chmod 644 *.php
chmod 644 .htaccess
chmod 755 assets/
chmod 755 includes/
chmod 755 config/

echo "✅ Aggiornamento completato!"
EOF

chmod +x update.sh
print_status "Script di aggiornamento creato (update.sh)"

# 9. Crea file di monitoraggio
echo "📊 Configurazione monitoraggio..."
cat > monitor.php << 'EOF'
<?php
// Monitoraggio VaiQui
header('Content-Type: application/json');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'ok',
    'checks' => []
];

// Verifica PHP
$status['checks']['php_version'] = PHP_VERSION;

// Verifica estensioni
$extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'gd'];
foreach ($extensions as $ext) {
    $status['checks']['extensions'][$ext] = extension_loaded($ext);
}

// Verifica database
try {
    require_once 'config/database.php';
    $status['checks']['database'] = 'connected';
} catch (Exception $e) {
    $status['checks']['database'] = 'error: ' . $e->getMessage();
    $status['status'] = 'error';
}

// Verifica file importanti
$important_files = ['index.php', 'profile.php', 'dashboard.php', 'config/database.php'];
foreach ($important_files as $file) {
    $status['checks']['files'][$file] = file_exists($file);
}

echo json_encode($status, JSON_PRETTY_PRINT);
?>
EOF

print_status "File di monitoraggio creato (monitor.php)"

# 10. Riepilogo finale
echo ""
echo "🎉 Setup completato!"
echo "==================="
echo ""
echo "📋 Prossimi passi:"
echo "1. Modifica config/database.php con le tue credenziali database"
echo "2. Modifica .env con i tuoi valori"
echo "3. Configura il database MySQL in Plesk"
echo "4. Testa l'installazione visitando il tuo dominio"
echo "5. Configura i webhook GitHub per deploy automatico"
echo ""
echo "🛠️  Script disponibili:"
echo "- ./backup.sh    : Backup database e file"
echo "- ./update.sh    : Aggiornamento da GitHub"
echo "- monitor.php    : Monitoraggio stato sistema"
echo ""
echo "📚 Documentazione:"
echo "- DEPLOYMENT.md  : Guida completa deployment"
echo "- README.md      : Documentazione progetto"
echo "- FEATURES.md    : Funzionalità disponibili"
echo ""
print_status "Setup VaiQui completato con successo!"
