-- Script per rendere un utente amministratore
-- Sostituisci 'username' con lo username dell'utente che vuoi promuovere ad admin

-- Opzione 1: Promuovi un utente specifico per username
UPDATE users SET role = 'admin' WHERE username = 'username';

-- Opzione 2: Promuovi il primo utente registrato
-- UPDATE users SET role = 'admin' WHERE id = (SELECT MIN(id) FROM users);

-- Opzione 3: Promuovi un utente specifico per ID
-- UPDATE users SET role = 'admin' WHERE id = 1;

