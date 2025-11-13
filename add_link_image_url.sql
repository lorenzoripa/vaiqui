-- Aggiunge la colonna image_url alla tabella links (per card con immagine)
ALTER TABLE links ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL;
