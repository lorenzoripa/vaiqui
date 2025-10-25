-- ===================================================
-- Aggiornamento Database: Funzionalità Indirizzo e Mappa
-- ===================================================
-- Esegui questo script per aggiungere i campi necessari
-- per la funzionalità di indirizzo e mappa interattiva

-- Aggiungi colonne per indirizzo e mappa alla tabella users
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS show_map BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) DEFAULT NULL;

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

