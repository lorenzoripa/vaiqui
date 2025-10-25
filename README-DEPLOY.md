# 🚀 VaiQui - Guida Rapida Deployment

## ⚡ Setup Veloce (5 minuti)

### 1. **GitHub Setup**
```bash
# Inizializza Git
git init
git add .
git commit -m "Initial commit"

# Collega a GitHub
git remote add origin https://github.com/TUOUSERNAME/vaiqui.git
git push -u origin main
```

### 2. **Plesk Setup**
```bash
# Sul server Plesk
git clone https://github.com/TUOUSERNAME/vaiqui.git
cd vaiqui
chmod +x plesk-setup.sh
./plesk-setup.sh
```

### 3. **Configurazione Database**
1. **Plesk** → **Database** → **Add Database**
2. Nome: `vaiqui_db`
3. User: `vaiqui_db_usr`
4. Password: `[password_sicura]`

### 4. **Configurazione File**
```bash
# Modifica config/database.php
cp config/database.example.php config/database.php
# Inserisci le tue credenziali database
```

### 5. **Test**
Visita: `https://tuodominio.com/vaiqui`

---

## 🔧 Configurazione Avanzata

### **Deploy Automatico con Webhook**

1. **GitHub** → **Settings** → **Webhooks**
2. **Payload URL**: `https://tuodominio.com/webhook.php`
3. **Secret**: `your_webhook_secret`
4. **Events**: `Just the push event`

### **Configurazione Social Login**

1. **Google Cloud Console** → **Credentials**
2. **OAuth 2.0 Client ID**
3. **Redirect URI**: `https://tuodominio.com/auth/google_callback.php`
4. Aggiorna `includes/social_login.php`

### **SSL e Sicurezza**

1. **Plesk** → **SSL/TLS** → **Let's Encrypt**
2. **Security** → **Firewall** (opzionale)
3. **Backup** → **Scheduled Tasks**

---

## 📁 Struttura File

```
vaiqui/
├── 📄 index.php              # Homepage
├── 📄 profile.php            # Profilo pubblico
├── 📄 dashboard.php          # Dashboard utente
├── 📄 customize.php          # Personalizzazione
├── 📁 config/
│   ├── 📄 database.php       # Config database (NON committare!)
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
└── 📄 DEPLOYMENT.md          # Guida completa
```

---

## 🛠️ Script Utili

### **Backup Automatico**
```bash
./backup.sh
```

### **Aggiornamento da GitHub**
```bash
./update.sh
```

### **Monitoraggio Sistema**
```
https://tuodominio.com/monitor.php
```

---

## 🔐 Sicurezza

### **File da NON Committare**
- `config/database.php` ✅ (in .gitignore)
- `.env` ✅ (in .gitignore)
- `logs/*` ✅ (in .gitignore)
- `cache/*` ✅ (in .gitignore)

### **Permessi Corretti**
```bash
chmod 755 .
chmod 644 *.php
chmod 644 .htaccess
chmod 755 assets/
chmod 755 includes/
```

---

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

### **Rewrite Rules Non Funzionano**
```apache
# Verifica .htaccess
RewriteEngine On
```

### **Social Login Non Funziona**
1. Verifica **Client ID** e **Secret**
2. Controlla **Redirect URI**
3. Verifica **HTTPS** (obbligatorio per OAuth)

---

## 📞 Supporto

- 📖 **Documentazione**: `DEPLOYMENT.md`
- 🐛 **Issues**: GitHub Issues
- 💬 **Discussioni**: GitHub Discussions

---

**🎉 Il tuo VaiQui è pronto!**

Ricorda di:
- ✅ Fare backup regolari
- ✅ Aggiornare le dipendenze
- ✅ Monitorare le performance
- ✅ Tenere aggiornato il codice
