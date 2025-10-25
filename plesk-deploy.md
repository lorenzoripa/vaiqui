# 🏗️ VaiQui - Deploy su Plesk

## 📋 Checklist Pre-Deploy

- [ ] ✅ Account GitHub creato
- [ ] ✅ Repository GitHub configurato
- [ ] ✅ Accesso SSH al server Plesk
- [ ] ✅ Database MySQL disponibile
- [ ] ✅ Dominio configurato in Plesk

## 🚀 Deploy Step-by-Step

### **Step 1: Preparazione GitHub**

1. **Crea Repository**
   ```
   GitHub → New Repository
   Nome: vaiqui
   Descrizione: VaiQui - Il tuo Linktree personale
   Public/Private: A tua scelta
   ✅ Add README
   ```

2. **Push Codice**
   ```bash
   # Nel tuo computer
   cd /Users/lorenzoripa/Desktop/Lavoro/vaiqui.it
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/TUOUSERNAME/vaiqui.git
   git push -u origin main
   ```

### **Step 2: Configurazione Plesk**

1. **Accedi a Plesk**
   - Vai su `https://tuoserver.com:8443`
   - Login con le tue credenziali

2. **Crea Database**
   ```
   Plesk → Database → Add Database
   Nome: vaiqui_db
   Username: vaiqui_db_usr
   Password: [password_sicura]
   ✅ OK
   ```

3. **Configura Dominio**
   ```
   Plesk → Domains → [tuodominio.com]
   Document Root: /httpdocs/vaiqui
   ```

### **Step 3: Deploy sul Server**

1. **SSH al Server**
   ```bash
   ssh user@tuoserver.com
   cd /var/www/vhosts/tuodominio.com/httpdocs
   ```

2. **Clona Repository**
   ```bash
   git clone https://github.com/TUOUSERNAME/vaiqui.git
   cd vaiqui
   ```

3. **Setup Automatico**
   ```bash
   chmod +x plesk-setup.sh
   ./plesk-setup.sh
   ```

### **Step 4: Configurazione Database**

1. **Crea config/database.php**
   ```bash
   cp config/database.example.php config/database.php
   nano config/database.php
   ```

2. **Inserisci Credenziali**
   ```php
   $host = 'localhost';
   $dbname = 'vaiqui_db';
   $username = 'vaiqui_db_usr';
   $password = 'TUA_PASSWORD_DATABASE';
   ```

### **Step 5: Test e Verifica**

1. **Visita il Sito**
   ```
   https://tuodominio.com/vaiqui
   ```

2. **Test Registrazione**
   - Crea un account di test
   - Verifica che funzioni

3. **Test Profilo**
   - Personalizza il profilo
   - Aggiungi link
   - Testa la mappa

## 🔄 Deploy Automatico

### **Configurazione Webhook**

1. **GitHub Webhook**
   ```
   GitHub → Settings → Webhooks → Add webhook
   Payload URL: https://tuodominio.com/webhook.php
   Content type: application/json
   Secret: your_webhook_secret
   Events: Just the push event
   ```

2. **Configura webhook.php**
   ```php
   $secret = 'your_webhook_secret_here';
   $repo_path = '/var/www/vhosts/tuodominio.com/httpdocs/vaiqui';
   ```

### **Deploy Manuale**

```bash
# Aggiornamento manuale
cd /var/www/vhosts/tuodominio.com/httpdocs/vaiqui
git pull origin main
./deploy.sh
```

## 🛠️ Script Disponibili

| Script | Descrizione |
|--------|-------------|
| `plesk-setup.sh` | Setup iniziale automatico |
| `deploy.sh` | Deploy con backup |
| `backup.sh` | Backup completo |
| `update.sh` | Aggiornamento da GitHub |
| `webhook.php` | Deploy automatico via webhook |

## 🔐 Sicurezza

### **File Sensibili**
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
# Verifica credenziali
mysql -u vaiqui_db_usr -p vaiqui_db
```

### **500 Internal Server Error**
```bash
# Controlla error log
tail -f /var/log/apache2/error.log
```

### **Git Pull Failed**
```bash
# Verifica permessi
ls -la
# Reset se necessario
git reset --hard origin/main
```

### **Webhook Non Funziona**
```bash
# Verifica webhook.php
curl -X POST https://tuodominio.com/webhook.php
# Controlla log
tail -f deploy.log
```

## 📞 Supporto

- 📖 **Guida Completa**: `DEPLOYMENT.md`
- ⚡ **Quick Start**: `QUICK-START.md`
- 🎯 **Features**: `FEATURES.md`
- 📝 **Changelog**: `CHANGELOG.md`

---

**🎉 Il tuo VaiQui è ora live su Plesk!**

Ora puoi:
- ✅ Gestire utenti e profili
- ✅ Personalizzare temi e colori
- ✅ Aggiungere link e mappe
- ✅ Monitorare analytics
- ✅ Deploy automatico da GitHub
