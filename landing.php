<?php
session_start();
// Se l'utente è già loggato, reindirizza al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaiQui - Il tuo Linktree personale</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="landing-linktree">
    <div class="lt-landing">
        <div class="lt-shell">
            <div class="lt-card">
                <div class="lt-header">
                    <div class="lt-logo" aria-hidden="true">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="lt-title">VaiQui</div>
                    <p class="lt-subtitle">
                        Il tuo “Linktree personale”: una pagina unica dove raccogliere link, social, QR code e analytics.
                        Inizia in 2 minuti.
                    </p>
                </div>

                <div class="lt-links">
                    <a class="lt-btn lt-btn-primary" href="index.php">
                        <span class="lt-btn-left">
                            <i class="fas fa-rocket" aria-hidden="true"></i>
                            Inizia gratis
                        </span>
                        <span class="lt-btn-right"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    <a class="lt-btn" href="index.php">
                        <span class="lt-btn-left">
                            <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                            Accedi
                        </span>
                        <span class="lt-btn-right"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    <a class="lt-btn" href="#come-funziona">
                        <span class="lt-btn-left">
                            <i class="fas fa-circle-play" aria-hidden="true"></i>
                            Come funziona
                        </span>
                        <span class="lt-btn-right"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                    </a>

                    <a class="lt-btn" href="#faq">
                        <span class="lt-btn-left">
                            <i class="fas fa-circle-question" aria-hidden="true"></i>
                            FAQ
                        </span>
                        <span class="lt-btn-right"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                    </a>
                </div>

                <div class="lt-section" id="come-funziona">
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-link"></i> Una pagina. Tutto dentro.</div>
                        <p>Crea la tua bio page e aggiungi i tuoi link (anche con immagini) in un layout pulito e mobile-first.</p>
                    </div>
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-qrcode"></i> QR code pronti</div>
                        <p>Genera QR code per i link e condividili offline (biglietti da visita, menù, vetrina, eventi).</p>
                    </div>
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-chart-line"></i> Analytics semplici</div>
                        <p>Vedi click e performance dei link, senza complicazioni.</p>
                    </div>
                </div>

                <div class="lt-section" id="faq">
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-bolt"></i> Quanto ci metto a partire?</div>
                        <p>Registrazione, aggiunta link e pubblicazione: di solito meno di 2 minuti.</p>
                    </div>
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-shield-halved"></i> I dati sono al sicuro?</div>
                        <p>Sì: il focus è su semplicità, performance e buone pratiche lato sicurezza.</p>
                    </div>
                    <div class="lt-mini-card">
                        <div class="lt-mini-title"><i class="fas fa-mobile-screen"></i> Funziona bene su mobile?</div>
                        <p>È pensato per mobile-first, con pulsanti grandi e leggibili.</p>
                    </div>
                </div>

                <div class="lt-footer">
                    <p>&copy; <?php echo date('Y'); ?> VaiQui.</p>
                    <p><a href="index.php">Vai alla pagina di accesso</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Smooth scroll semplice
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', (e) => {
                const href = a.getAttribute('href');
                const target = document.querySelector(href);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>
