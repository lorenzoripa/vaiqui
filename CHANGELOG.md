# Changelog - VaiQui

Tutte le modifiche notevoli al progetto saranno documentate in questo file.

## [Aggiornamento] - 2025-10-25

### ✨ Nuove Funzionalità

#### 📍 Indirizzo e Mappa Interattiva
- **Gestione Indirizzo**: Aggiungi il tuo indirizzo fisico nelle Impostazioni
- **Mappa Interattiva**: Mostra una mappa OpenStreetMap nel profilo pubblico
- **Geocoding Automatico**: Conversione automatica indirizzo → coordinate usando Nominatim
- **Privacy**: Scegli se mostrare o nascondere la mappa
- **Responsive**: Mappa ottimizzata per desktop e mobile

**Funzionalità Tecniche:**
- Geocoding con Nominatim API (OpenStreetMap)
- Mappa interattiva con Leaflet.js
- Salvataggio coordinate (latitudine/longitudine) nel database
- Marker personalizzato sulla mappa
- Zoom e navigazione mappa

**File Modificati/Creati:**
- `database_update.sql` - Aggiunto campi: address, show_map, latitude, longitude
- `add_address_fields.sql` - Script SQL specifico per aggiornamento indirizzo
- `dashboard.php` - Aggiunta gestione indirizzo nella sezione Impostazioni
- `profile.php` - Integrata visualizzazione mappa nel profilo pubblico
- `includes/functions.php` - Aggiunte funzioni: `updateUserAddress()`, `geocodeAddress()`
- `assets/css/style.css` - Aggiunti stili per sezione indirizzo e mappa
- `test_geocoding.php` - Pagina di test per verificare geocoding
- `FEATURES.md` - Documentazione completa funzionalità
- `README.md` - Aggiornato con nuova funzionalità

**Come Usare:**
1. Esegui `add_address_fields.sql` o `database_update.sql` nel database
2. Vai su Dashboard → Impostazioni
3. Compila il campo "Indirizzo"
4. Attiva "Mostra mappa nel profilo pubblico"
5. Salva e visualizza il profilo pubblico

#### 🎨 Miglioramenti Personalizzazione

**Fix Temi Predefiniti:**
- Fix: Selezione temi ora aggiorna automaticamente i colori
- Aggiunto: Pulsante "Reset ai Colori del Tema"
- Aggiunto: Tema corrente evidenziato al caricamento
- Aggiunto: Campo background_color per gradienti CSS
- Aggiunto: Campo button_text_color per personalizzazione completa

**File Modificati:**
- `customize.php` - Migliorata gestione temi con JavaScript
- `assets/css/style.css` - Aggiornati stili personalizzazione

---

## [Versione Precedente] - 2025-10-24

### ✨ Funzionalità Implementate

#### 🔐 Social Login
- Login con Google OAuth 2.0
- Registrazione automatica nuovi utenti
- Collegamento account esistenti

#### 🎨 Personalizzazione Avanzata
- Temi predefiniti (Default, Dark, Minimal, Colorful, Ocean, Forest)
- Personalizzazione colori (primario, secondario, testo, pulsanti, sfondo)
- Selezione font (Roboto, Open Sans, Lato, Montserrat, Poppins, ecc.)
- Stili pulsanti personalizzabili
- Border radius, ombre e animazioni
- CSS personalizzato
- Anteprima in tempo reale

#### 🔗 Link Accorciati
- Sistema completo di URL shortening
- Generazione codici brevi personalizzati
- QR Code per ogni link
- Statistiche dettagliate per link accorciato

#### 📊 Analytics Avanzate
- Click giornalieri, settimanali, mensili
- Statistiche per paese
- Statistiche per dispositivo e browser
- Grafici interattivi per orario
- Export dati

#### 🔧 Funzionalità Tecniche
- Link dinamici basati su condizioni
- Link evento con date specifiche
- QR Code generation
- Dashboard completa con navigazione a tab
- Design responsive

---

## Struttura Database

### Tabella `users` - Campi Principali

```sql
-- Campi base
id, username, email, password, display_name, bio, created_at

-- Social login
social_provider, social_id, avatar

-- Personalizzazione
theme, custom_css, background_image, button_style, font_family
primary_color, secondary_color, text_color, background_color
button_color, button_text_color, border_radius, shadow_style, animation_style

-- Indirizzo e mappa (NUOVO)
address, show_map, latitude, longitude
```

### Tabelle Aggiuntive

```sql
-- Gestione link
links - Link tradizionali
short_links - Link accorciati
dynamic_links - Link dinamici
event_links - Link evento
scheduled_links - Link programmati

-- Analytics
analytics - Statistiche link tradizionali
short_link_clicks - Statistiche link accorciati

-- Configurazione
settings - Impostazioni globali
```

---

## TODO / Roadmap

### 🎯 Prossimi Miglioramenti Possibili

- [ ] **Mappa**: Selezione manuale posizione (drag & drop marker)
- [ ] **Mappa**: Supporto per multiple locations
- [ ] **Mappa**: Stili personalizzati mappa
- [ ] **Social**: Aggiunta Facebook e Twitter login
- [ ] **Analytics**: Export PDF reportistica
- [ ] **Temi**: Editor visuale temi personalizzati
- [ ] **Link**: Scheduling avanzato con ricorrenze
- [ ] **Mobile**: App mobile companion
- [ ] **API**: REST API completa per integrazioni
- [ ] **Backup**: Sistema backup automatico
- [ ] **2FA**: Autenticazione a due fattori
- [ ] **Team**: Gestione team e collaboratori
- [ ] **Custom Domain**: Supporto domini personalizzati

---

## Crediti

### Librerie e Servizi Utilizzati

- **OpenStreetMap** - Mappe e geocoding (Nominatim)
- **Leaflet.js** - Libreria mappe interattive
- **Font Awesome** - Icone
- **Google Fonts** - Font web
- **Chart.js** - Grafici analytics
- **PHP QR Code** - Generazione QR codes

### Licenze

- Leaflet: BSD 2-Clause License
- OpenStreetMap: ODbL (Open Database License)
- Font Awesome: SIL OFL 1.1 & MIT License

---

## Supporto

Per bug report, feature request o domande:
- Crea una issue su GitHub
- Email: support@vaiqui.it (esempio)
- Documentazione: README.md, FEATURES.md

---

**Ultima modifica:** 25 Ottobre 2025
**Versione:** 1.2.0

