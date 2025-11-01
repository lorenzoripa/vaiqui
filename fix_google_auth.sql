-- Fix per autenticazione Google
-- Aggiunge le colonne necessarie per l'autenticazione con Google OAuth

-- Aggiungi colonna google_id se non esiste
ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL;

-- Aggiungi colonna avatar_url se non esiste (per l'immagine del profilo da Google)
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(500) DEFAULT NULL;

-- Crea indice per velocizzare le ricerche su google_id
CREATE INDEX IF NOT EXISTS idx_google_id ON users(google_id);

-- Verifica che la tabella users sia pronta per social login
-- Se avatar_url è già presente come 'avatar', rinomina la colonna
-- ALTER TABLE users CHANGE avatar avatar_url VARCHAR(500) DEFAULT NULL;


