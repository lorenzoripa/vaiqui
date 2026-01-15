-- ===================================================
-- Aggiornamento Database: Funzionalità Indirizzo e Mappa
-- ===================================================
-- Esegui questo script per aggiungere i campi necessari
-- per la funzionalità di indirizzo e mappa interattiva

-- Aggiungi colonne per indirizzo e mappa alla tabella users
-- NOTA: Se le colonne esistono già, MySQL genererà un errore che puoi ignorare
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL;
ALTER TABLE users ADD COLUMN show_map BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL;
ALTER TABLE users ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL;

-- Verifica che le colonne siano state aggiunte correttamente
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM 
    INFORMATION_SCHEMA.COLUMNS 
WHERE 
    TABLE_NAME = 'users' 
    AND COLUMN_NAME IN ('address', 'show_map', 'latitude', 'longitude');

