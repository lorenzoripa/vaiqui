-- Aggiunge la colonna click_count alla tabella links (se non esiste)
-- NOTA: Se la colonna esiste già, MySQL genererà un errore che puoi ignorare
ALTER TABLE links ADD COLUMN click_count INT DEFAULT 0;
