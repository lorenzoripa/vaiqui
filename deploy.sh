#!/bin/bash
# Script di Deploy Automatico per VaiQui
# Questo script può essere eseguito manualmente o tramite webhook

set -e  # Exit on any error

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funzioni per output colorato
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Configurazione
PROJECT_DIR="/var/www/vhosts/tuodominio.com/httpdocs/vaiqui"
BACKUP_DIR="backups"
LOG_FILE="deploy.log"
BRANCH="main"

# Verifica che siamo nella directory corretta
if [ ! -f "index.php" ]; then
    print_error "index.php non trovato. Assicurati di essere nella directory di VaiQui."
    exit 1
fi

print_info "🚀 Avvio deploy VaiQui..."

# 1. Backup prima del deploy
print_info "📦 Creazione backup..."
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
fi

BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"
print_info "Backup: $BACKUP_NAME"

# Backup database (se configurato)
if [ -f "config/database.php" ]; then
    print_info "🗄️  Backup database..."
    # Estrai credenziali dal file di configurazione
    DB_NAME=$(grep -o "dbname = '[^']*'" config/database.php | cut -d"'" -f2)
    DB_USER=$(grep -o "username = '[^']*'" config/database.php | cut -d"'" -f2)
    
    if [ ! -z "$DB_NAME" ] && [ ! -z "$DB_USER" ]; then
        mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/${BACKUP_NAME}_database.sql" 2>/dev/null || print_warning "Backup database fallito (credenziali non valide)"
    fi
fi

# Backup file
print_info "📁 Backup file..."
tar -czf "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" \
    --exclude=cache \
    --exclude=logs \
    --exclude=backups \
    --exclude=.git \
    . 2>/dev/null || print_warning "Backup file fallito"

print_success "Backup completato: $BACKUP_DIR/${BACKUP_NAME}_*"

# 2. Git pull
print_info "⬇️  Aggiornamento da GitHub..."
if git pull origin "$BRANCH"; then
    print_success "Git pull completato"
else
    print_error "Git pull fallito"
    exit 1
fi

# 3. Aggiorna permessi
print_info "🔐 Aggiornamento permessi..."
chmod 755 . 2>/dev/null || true
find . -name "*.php" -exec chmod 644 {} \; 2>/dev/null || true
chmod 644 .htaccess 2>/dev/null || true
chmod 755 assets/ 2>/dev/null || true
chmod 755 includes/ 2>/dev/null || true
chmod 755 config/ 2>/dev/null || true

print_success "Permessi aggiornati"

# 4. Verifica file importanti
print_info "🔍 Verifica file importanti..."
REQUIRED_FILES=("index.php" "profile.php" "dashboard.php" "config/database.php")
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "✅ $file"
    else
        print_error "❌ $file mancante"
        exit 1
    fi
done

# 5. Test sintassi PHP
print_info "🐘 Test sintassi PHP..."
if find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; > /dev/null 2>&1; then
    print_success "Sintassi PHP OK"
else
    print_warning "Errori di sintassi PHP rilevati"
fi

# 6. Pulisci cache (se presente)
print_info "🧹 Pulizia cache..."
if [ -d "cache" ]; then
    rm -rf cache/* 2>/dev/null || true
    print_success "Cache pulita"
fi

# 7. Aggiorna timestamp
echo "$(date): Deploy completato" >> "$LOG_FILE"

# 8. Riepilogo
print_success "🎉 Deploy completato con successo!"
print_info "📊 Riepilogo:"
print_info "  - Backup: $BACKUP_DIR/${BACKUP_NAME}_*"
print_info "  - Branch: $BRANCH"
print_info "  - Timestamp: $(date)"
print_info "  - Log: $LOG_FILE"

# 9. Test rapido (opzionale)
if [ -f "monitor.php" ]; then
    print_info "🔍 Test sistema..."
    if curl -s "https://tuodominio.com/monitor.php" > /dev/null 2>&1; then
        print_success "Sistema operativo"
    else
        print_warning "Test sistema fallito"
    fi
fi

print_success "🚀 VaiQui aggiornato e pronto!"
