# VaiQui - Il tuo Linktree personale

Un clone di Linktree sviluppato in PHP puro, con database MySQL e interfaccia moderna.

## 🚀 Caratteristiche

### 🔐 Autenticazione e Sicurezza
- **Autenticazione completa**: Registrazione e login sicuri
- **Social Login**: Login con Google OAuth 2.0
- **Hash password**: Sicurezza avanzata con password_hash()
- **Gestione sessioni**: Sistema di sessioni robusto
- **Protezione XSS**: Validazione e sanitizzazione input

### 🔗 Gestione Link Avanzata
- **Link tradizionali**: Aggiungi, modifica, elimina e riordina i tuoi link
- **Link accorciati**: Sistema completo di URL shortening con statistiche
- **Link dinamici**: Link che cambiano in base a condizioni specifiche
- **Link evento**: Link attivi solo in date specifiche
- **Link programmati**: Link con date di inizio e fine
- **Link intelligenti**: Link che cambiano in base all'orario/giorno

### 📊 Analytics e Statistiche
- **Statistiche dettagliate**: Click totali, click giornalieri, dispositivi
- **Grafici interattivi**: Visualizzazione dati con Chart.js
- **Analytics per link**: Statistiche individuali per ogni link
- **Tracking avanzato**: Dispositivo, browser, paese, ora
- **Export dati**: Esporta le tue statistiche

### 🎨 Personalizzazione
- **Profilo personalizzabile**: Nome, biografia e avatar
- **Temi personalizzabili**: Diversi stili per il tuo profilo
- **Personalizzazione avanzata**: Colori, font, stili pulsanti, CSS personalizzato
- **Icone personalizzate**: Supporto completo Font Awesome
- **Colori personalizzati**: Ogni link può avere il suo colore
- **Indirizzo e mappa**: Mostra il tuo indirizzo con mappa interattiva
- **Design responsive**: Ottimizzato per desktop e mobile

### 🔧 Funzionalità Avanzate
- **QR Code**: Generazione automatica QR code per ogni link
- **Drag & Drop**: Riordina i link facilmente
- **URL friendly**: Profili accessibili tramite /username
- **SEO friendly**: Meta tag ottimizzati per i social
- **API REST**: Endpoint per integrazioni esterne
- **Sistema di backup**: Backup automatico dei dati

## 📋 Requisiti

- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Server web (Apache/Nginx)
- Estensioni PHP: PDO, PDO_MySQL

## 🛠️ Installazione

### 🚀 Deploy Rapido (5 minuti)

1. **GitHub Setup**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/TUOUSERNAME/vaiqui.git
   git push -u origin main
   ```

2. **Server Setup**
   ```bash
   git clone https://github.com/TUOUSERNAME/vaiqui.git
   cd vaiqui
   chmod +x plesk-setup.sh
   ./plesk-setup.sh
   ```

3. **Configurazione Database**
   - Crea database MySQL
   - Modifica `config/database.php`
   - Visita il sito

### 📖 Guide Dettagliate

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Guida completa deployment
- **[QUICK-START.md](QUICK-START.md)** - Setup in 5 minuti
- **[plesk-deploy.md](plesk-deploy.md)** - Deploy specifico per Plesk
- **[SETUP-COMPLETE.md](SETUP-COMPLETE.md)** - Riepilogo setup

## 📁 Struttura del progetto

```
vaiqui/
├── 📄 index.php              # Homepage e login
├── 📄 profile.php            # Profilo pubblico
├── 📄 dashboard.php          # Dashboard utente
├── 📄 customize.php          # Personalizzazione avanzata
├── 📁 config/
│   ├── 📄 database.php       # Configurazione database
│   └── 📄 database.example.php
├── 📁 includes/
│   ├── 📄 functions.php      # Funzioni principali
│   ├── 📄 social_login.php   # Social login
│   └── 📄 profile_customization.php
├── 📁 assets/
│   ├── 📁 css/               # Stili
│   ├── 📁 js/                # JavaScript
│   └── 📁 images/            # Immagini
├── 📁 auth/                  # Callback social login
├── 📁 api/                   # API endpoints
├── 📄 .htaccess              # Rewrite rules
├── 📄 .gitignore             # Git ignore
├── 📄 webhook.php            # Deploy automatico
├── 📄 plesk-setup.sh         # Setup automatico
├── 📄 deploy.sh              # Deploy con backup
├── 📄 backup.sh              # Backup completo
└── 📄 monitor.php            # Monitoraggio sistema
```

## 🔄 Deploy Automatico

### **GitHub Webhook**
1. **GitHub** → **Settings** → **Webhooks**
2. **Payload URL**: `https://tuodominio.com/webhook.php`
3. **Secret**: `your_webhook_secret`
4. **Events**: `Just the push event`

### **Deploy Manuale**
```bash
./deploy.sh
```

## 🛠️ Script Disponibili

| Script | Descrizione |
|--------|-------------|
| `plesk-setup.sh` | Setup iniziale automatico |
| `deploy.sh` | Deploy con backup |
| `backup.sh` | Backup completo |
| `update.sh` | Aggiornamento da GitHub |
| `webhook.php` | Deploy automatico |
| `monitor.php` | Monitoraggio sistema |

## 🔐 Sicurezza

### **File Protetti**
- ❌ `config/database.php` (NON committare)
- ❌ `.env` (NON committare)
- ❌ `logs/*` (NON committare)
- ❌ `cache/*` (NON committare)

### **Permessi Corretti**
```bash
chmod 755 .
chmod 644 *.php
chmod 644 .htaccess
chmod 755 assets/
chmod 755 includes/
```

## 📊 Monitoraggio

### **Health Check**
```
https://tuodominio.com/monitor.php
```

### **Log Files**
```bash
tail -f deploy.log
tail -f logs/error.log
```

## 🚨 Troubleshooting

### **Database Connection Failed**
```bash
mysql -u vaiqui_db_usr -p vaiqui_db
```

### **500 Internal Server Error**
```bash
tail -f /var/log/apache2/error.log
```

### **Git Pull Failed**
```bash
git reset --hard origin/main
```

### **Webhook Non Funziona**
```bash
curl -X POST https://tuodominio.com/webhook.php
```

## 📞 Supporto

- 📖 **Guida Completa**: `DEPLOYMENT.md`
- ⚡ **Quick Start**: `QUICK-START.md`
- 🏗️ **Plesk Deploy**: `plesk-deploy.md`
- 🎯 **Features**: `FEATURES.md`
- 📝 **Changelog**: `CHANGELOG.md`

## 🎯 Funzionalità Principali

### **👤 Gestione Utenti**
- Registrazione e login sicuri
- Social login con Google
- Profili personalizzabili
- Avatar e biografia

### **🔗 Gestione Link**
- Link tradizionali con icone e colori
- Link accorciati con QR code
- Link dinamici e programmati
- Analytics dettagliate

### **🎨 Personalizzazione**
- Temi predefiniti e personalizzati
- Colori e font personalizzati
- CSS personalizzato
- Anteprima in tempo reale

### **📍 Posizione**
- Indirizzo utente
- Mappa interattiva OpenStreetMap
- Geocoding automatico

### **📊 Analytics**
- Click tracking avanzato
- Statistiche per dispositivo/browser
- Grafici interattivi
- Export dati

## 🚀 Deploy in Produzione

### **Plesk (Raccomandato)**
```bash
# Setup automatico
./plesk-setup.sh

# Deploy automatico
./deploy.sh
```

### **cPanel**
```bash
# Adatta gli script per cPanel
# Modifica percorsi in deploy.sh
```

### **VPS**
```bash
# Per server VPS dedicati
# Configura Apache/Nginx
# Setup MySQL
```

## 📈 Performance

- **Caching**: Cache automatica per performance
- **CDN**: Supporto per CDN esterni
- **Compression**: Gzip compression
- **Minification**: CSS/JS minificati
- **Database**: Query ottimizzate

## 🔄 Aggiornamenti

### **Automatico**
- Push su GitHub → Deploy automatico
- Backup prima dell'aggiornamento
- Rollback automatico in caso di errori

### **Manuale**
```bash
git pull origin main
./deploy.sh
```

---

**🎉 VaiQui è pronto per il deployment!**

Ora puoi:
- ✅ Deploy su GitHub
- ✅ Setup su Plesk
- ✅ Configurazione database
- ✅ Test funzionalità
- ✅ Deploy automatico

**Buon lavoro!** 🚀✨

---

**Versione**: 1.2.0  
**Ultima modifica**: 25 Ottobre 2025  
**Licenza**: MIT