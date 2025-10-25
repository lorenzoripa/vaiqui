# ⚡ VaiQui - Quick Start Guide

## 🚀 Deploy in 5 Minuti

### **Step 1: GitHub Setup**
```bash
# 1. Crea repository su GitHub
# 2. Nel terminale:
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/TUOUSERNAME/vaiqui.git
git push -u origin main
```

### **Step 2: Plesk Setup**
```bash
# 1. Accedi al server Plesk via SSH
# 2. Vai nella directory del dominio
cd /var/www/vhosts/tuodominio.com/httpdocs

# 3. Clona il repository
git clone https://github.com/TUOUSERNAME/vaiqui.git

# 4. Vai nella cartella
cd vaiqui

# 5. Esegui setup automatico
chmod +x plesk-setup.sh
./plesk-setup.sh
```

### **Step 3: Database**
1. **Plesk** → **Database** → **Add Database**
2. Nome: `vaiqui_db`
3. User: `vaiqui_db_usr` 
4. Password: `[password_sicura]`
5. **OK**

### **Step 4: Configurazione**
```bash
# Modifica le credenziali database
nano config/database.php
# Inserisci: host, dbname, username, password
```

### **Step 5: Test**
Visita: `https://tuodominio.com/vaiqui`

---

## 🔧 Configurazione Avanzata

### **Deploy Automatico**
1. **GitHub** → **Settings** → **Webhooks**
2. **Payload URL**: `https://tuodominio.com/webhook.php`
3. **Secret**: `your_webhook_secret`
4. **Events**: `Just the push event`

### **Social Login (Google)**
1. **Google Cloud Console** → **Credentials**
2. **OAuth 2.0 Client ID**
3. **Redirect URI**: `https://tuodominio.com/auth/google_callback.php`
4. Copia **Client ID** e **Secret** in `includes/social_login.php`

---

## 📁 File Importanti

| File | Descrizione |
|------|-------------|
| `index.php` | Homepage e login |
| `profile.php` | Profilo pubblico |
| `dashboard.php` | Dashboard utente |
| `config/database.php` | **Configurazione database** |
| `.htaccess` | Rewrite rules |
| `webhook.php` | Deploy automatico |

---

## 🛠️ Script Utili

```bash
# Backup completo
./backup.sh

# Aggiornamento da GitHub  
./update.sh

# Monitoraggio sistema
curl https://tuodominio.com/monitor.php
```

---

## 🚨 Problemi Comuni

### **Database Connection Failed**
- ✅ Verifica credenziali in `config/database.php`
- ✅ Controlla che il database esista
- ✅ Verifica permessi utente database

### **500 Internal Server Error**
- ✅ Controlla error log: `tail -f /var/log/apache2/error.log`
- ✅ Verifica permessi file: `chmod 644 *.php`
- ✅ Controlla sintassi PHP: `php -l index.php`

### **Rewrite Rules Non Funzionano**
- ✅ Verifica che mod_rewrite sia abilitato
- ✅ Controlla `.htaccess` nella root
- ✅ Testa con: `curl -I https://tuodominio.com/vaiqui`

### **Social Login Non Funziona**
- ✅ Verifica **Client ID** e **Secret**
- ✅ Controlla **Redirect URI** (deve essere HTTPS)
- ✅ Verifica che il dominio sia in **Authorized domains**

---

## 📞 Supporto

- 📖 **Guida Completa**: `DEPLOYMENT.md`
- 🚀 **Quick Start**: `README-DEPLOY.md`
- 🎯 **Features**: `FEATURES.md`
- 📝 **Changelog**: `CHANGELOG.md`

---

**🎉 VaiQui è pronto!**

Ora puoi:
- ✅ Registrare utenti
- ✅ Creare profili personalizzati
- ✅ Aggiungere link e mappe
- ✅ Configurare social login
- ✅ Monitorare analytics

**Buon lavoro!** 🚀
