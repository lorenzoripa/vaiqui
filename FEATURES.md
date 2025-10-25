# 📍 Funzionalità Indirizzo e Mappa

## Panoramica
La funzionalità di indirizzo e mappa permette agli utenti di mostrare la propria posizione sul profilo pubblico con una mappa interattiva basata su OpenStreetMap.

## Come Usare

### 1. Aggiungere un Indirizzo

1. Accedi alla tua dashboard
2. Vai su **Impostazioni** nel menu
3. Cerca la sezione **"Indirizzo e Mappa"**
4. Inserisci il tuo indirizzo completo (esempio: "Via Roma 123, Milano, Italia")
5. Spunta la casella **"Mostra mappa nel profilo pubblico"** se vuoi visualizzare la mappa
6. Clicca su **"Salva Indirizzo"**

### 2. Cosa Succede Quando Salvi

Quando salvi un indirizzo:
- Il sistema utilizza **Nominatim** (servizio di geocoding di OpenStreetMap) per convertire l'indirizzo in coordinate geografiche (latitudine e longitudine)
- Le coordinate vengono salvate nel database
- Se hai attivato "Mostra mappa", la mappa apparirà automaticamente nel tuo profilo pubblico

### 3. Visualizzazione nel Profilo

Nel tuo profilo pubblico, se hai attivato la mappa, gli utenti vedranno:
- **Titolo della sezione**: "Dove Trovarci" con icona
- **Il tuo indirizzo**: Mostrato in formato testo
- **Mappa interattiva**: Mappa OpenStreetMap con un marker sulla tua posizione
  - Gli utenti possono fare zoom in/out
  - Possono trascinare la mappa per esplorare
  - Cliccando sul marker appare il tuo nome

## Caratteristiche Tecniche

### Geocoding
- Utilizza **Nominatim API** di OpenStreetMap
- Conversione automatica indirizzo → coordinate
- Gratuito e open source
- Rispetta le policy di utilizzo con User-Agent personalizzato

### Mappa Interattiva
- Basata su **Leaflet.js** (libreria JavaScript per mappe)
- Tiles di OpenStreetMap
- Zoom livello 15 di default
- Marker personalizzato con popup

### Privacy
- L'indirizzo è visibile solo se attivi "Mostra mappa"
- Puoi inserire un indirizzo generico (es: "Milano, Italia") invece dell'indirizzo esatto
- Puoi disattivare la visualizzazione in qualsiasi momento

## Esempi di Utilizzo

### Caso d'uso 1: Negozio Fisico
```
Indirizzo: Via Garibaldi 45, 20121 Milano, Italia
Mostra mappa: ✓
```
Perfetto per negozi, ristoranti, uffici che vogliono far trovare la loro sede fisica ai clienti.

### Caso d'uso 2: Area Generica
```
Indirizzo: Milano, Italia
Mostra mappa: ✓
```
Se vuoi mostrare solo la città senza dare l'indirizzo esatto.

### Caso d'uso 3: Solo Testo
```
Indirizzo: Zona Duomo, Milano
Mostra mappa: ✗
```
Mostra solo il testo dell'indirizzo senza mappa interattiva.

## Database

### Nuovi Campi nella Tabella `users`

```sql
- address TEXT DEFAULT NULL
  → L'indirizzo testuale inserito dall'utente

- show_map BOOLEAN DEFAULT FALSE
  → Se TRUE, mostra la mappa nel profilo pubblico

- latitude DECIMAL(10, 8) DEFAULT NULL
  → Latitudine della posizione (es: 45.46427)

- longitude DECIMAL(11, 8) DEFAULT NULL
  → Longitudine della posizione (es: 9.18951)
```

## API Utilizzate

### Nominatim (Geocoding)
```
Endpoint: https://nominatim.openstreetmap.org/search
Parametri:
  - q: query dell'indirizzo
  - format: json
  - limit: 1
Headers:
  - User-Agent: VaiQui/1.0
```

### Leaflet.js (Mappa)
```
CDN CSS: https://unpkg.com/leaflet@1.9.4/dist/leaflet.css
CDN JS: https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
```

## Personalizzazione CSS

Gli stili della mappa possono essere personalizzati in `assets/css/style.css`:

```css
.profile-address {
    /* Contenitore sezione indirizzo */
}

.profile-map {
    /* Contenitore mappa */
    height: 400px; /* Modifica l'altezza della mappa */
}
```

## Responsive Design

La mappa si adatta automaticamente ai dispositivi mobili:
- Desktop: 400px di altezza
- Mobile: 300px di altezza

## Limitazioni e Note

1. **Rate Limiting**: Nominatim ha limiti di utilizzo (1 richiesta al secondo)
2. **Precisione**: La geocodifica dipende dalla qualità dell'indirizzo inserito
3. **Connessione**: Richiede connessione internet per caricare le mappe
4. **Privacy**: Non salvare indirizzi personali se non necessario

## Troubleshooting

### La mappa non appare
- Verifica che "Mostra mappa" sia attivo
- Controlla che l'indirizzo sia stato geocodificato correttamente
- Verifica la connessione internet (Leaflet carica risorse esterne)

### L'indirizzo non viene trovato
- Prova a essere più specifico (aggiungi città, provincia, nazione)
- Usa formati standard (Via/Piazza Nome, CAP Città, Nazione)
- Verifica che non ci siano errori di battitura

### La posizione è imprecisa
- Nominatim può dare risultati approssimativi per indirizzi generici
- Per maggiore precisione, inserisci l'indirizzo completo con CAP

## Aggiornamenti Futuri

Possibili miglioramenti:
- [ ] Selezione manuale della posizione trascinando un marker
- [ ] Supporto per Google Maps come alternativa
- [ ] Stili personalizzati per la mappa
- [ ] Indicazioni stradali integrate
- [ ] Multiple locations (più sedi)

