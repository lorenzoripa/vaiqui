# Fix Autenticazione Google - Errore 500

## Problemi Identificati e Risolti

### 1. ✅ Funzione PHP Mancante
**Problema**: La funzione `createOrUpdateSocialUser` non esisteva nel codice.  
**Soluzione**: ✅ Aggiunta al file `includes/social_login.php`

### 2. ⚠️ Credenziali Google Non Configurate
**Problema**: Le credenziali Google OAuth non sono configurate (valori di default).  
**Soluzione**: Devi configurare le credenziali reali (vedi istruzioni sotto)

### 3. ⚠️ Colonna Database Mancante
**Problema**: La tabella `users` potrebbe non avere la colonna `google_id`.  
**Soluzione**: Esegui lo script SQL fornito (vedi istruzioni sotto)

---

## Passaggi per Risolvere l'Errore 500

### Passo 1: Aggiorna il Database

Esegui lo script SQL per aggiungere le colonne necessarie:

```bash
mysql -u username -p database_name < fix_google_auth.sql
```

Oppure accedi a phpMyAdmin e esegui il contenuto del file `fix_google_auth.sql`.

### Passo 2: Configura le Credenziali Google OAuth

#### 2.1 Crea un Progetto Google Cloud

1. Vai su [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuovo progetto o seleziona uno esistente
3. Nel menu laterale, vai su **API e servizi** > **Libreria**
4. Cerca e abilita **Google+ API** (o **Google Identity**)

#### 2.2 Crea le Credenziali OAuth 2.0

1. Vai su **API e servizi** > **Credenziali**
2. Clicca su **+ CREA CREDENZIALI** > **ID client OAuth**
3. Seleziona **Applicazione web** come tipo di applicazione
4. Aggiungi gli **URI di reindirizzamento autorizzati**:
   - `http://tuo-dominio.com/auth/google_callback.php` (per sviluppo)
   - `https://tuo-dominio.com/auth/google_callback.php` (per produzione)
5. Clicca su **Crea**
6. Copia il **Client ID** e il **Client Secret**

#### 2.3 Configura le Credenziali nel Codice

1. Copia il file di esempio:
```bash
cp config/google_config.example.php config/google_config.php
```

2. Modifica `config/google_config.php` e inserisci le tue credenziali:
```php
define('GOOGLE_CLIENT_ID', 'IL_TUO_CLIENT_ID_QUI');
define('GOOGLE_CLIENT_SECRET', 'IL_TUO_CLIENT_SECRET_QUI');
```

3. Assicurati che il file `google_config.php` NON sia tracciato da git:
```bash
echo "config/google_config.php" >> .gitignore
```

### Passo 3: Verifica la Configurazione

1. Prova ad accedere con Google
2. Se ricevi ancora errori, controlla i log del server:
```bash
tail -f /var/log/apache2/error.log
# oppure
tail -f /var/log/php-fpm/error.log
```

### Passo 4: Abilita i Log di Debug (opzionale)

Se continui ad avere problemi, aggiungi queste righe all'inizio di `auth/google_callback.php`:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/google_auth.log');
```

---

## Struttura File Modificati

```
vaiqui.it/
├── auth/
│   └── google_callback.php (già presente)
├── config/
│   ├── google_config.example.php (✅ NUOVO - template)
│   └── google_config.php (⚠️ DA CREARE - con le tue credenziali)
├── includes/
│   └── social_login.php (✅ AGGIORNATO - aggiunta funzione createOrUpdateSocialUser)
├── fix_google_auth.sql (✅ NUOVO - script per aggiornare il database)
└── GOOGLE_AUTH_FIX.md (questo file)
```

---

## Checklist Finale

- [ ] Script SQL `fix_google_auth.sql` eseguito sul database
- [ ] Colonna `google_id` presente nella tabella `users`
- [ ] Colonna `avatar_url` presente nella tabella `users`
- [ ] Progetto Google Cloud creato
- [ ] Google+ API abilitata
- [ ] Credenziali OAuth create
- [ ] URI di reindirizzamento configurati correttamente
- [ ] File `config/google_config.php` creato con credenziali reali
- [ ] File `config/google_config.php` aggiunto a `.gitignore`
- [ ] Test di login con Google funzionante

---

## Possibili Errori Residui

### Errore: "redirect_uri_mismatch"
**Soluzione**: Verifica che l'URI di reindirizzamento in Google Cloud Console sia esattamente uguale a quello usato nell'applicazione (incluso http/https).

### Errore: "invalid_client"
**Soluzione**: Verifica che Client ID e Client Secret siano corretti.

### Errore: "access_denied"
**Soluzione**: L'utente ha negato l'autorizzazione. Riprova il login.

### Errore 500 persistente
**Soluzione**: Controlla i log PHP per vedere l'errore esatto. Potrebbe essere un problema con la connessione al database o con le query SQL.

---

## Supporto

Per problemi persistenti, controlla:
1. Log del server web (Apache/Nginx)
2. Log di PHP
3. Log di MySQL
4. Console del browser (F12) per errori JavaScript

## Contatti

Per ulteriore assistenza, contatta il team di sviluppo.


