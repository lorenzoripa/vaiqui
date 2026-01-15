-- Aggiunge la colonna image_url alla tabella links (per card con immagine)
-- NOTA: Se la colonna esiste già, MySQL genererà un errore che puoi ignorare
ALTER TABLE links ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;
