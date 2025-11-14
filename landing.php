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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            padding: 40px 20px;
            max-width: 1200px;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
        }

        .hero .subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.95;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero .description {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .btn-hero {
            padding: 18px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hero-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .btn-hero-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(10px);
        }

        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-3px);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 100px 20px;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 60px;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-item p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 20px;
            background: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        .cta-section p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 40px;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .footer p {
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .subtitle {
                font-size: 1.2rem;
            }

            .hero .description {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1><i class="fas fa-link"></i> VaiQui</h1>
            <p class="subtitle">Il tuo Linktree personale</p>
            <p class="description">
                Crea la tua pagina di presentazione professionale in pochi minuti. 
                Raggruppa tutti i tuoi link social, progetti e contenuti in un unico posto elegante e personalizzabile.
            </p>
            <div class="cta-buttons">
                <a href="index.php" class="btn-hero btn-hero-primary">
                    <i class="fas fa-rocket"></i> Inizia Gratis
                </a>
                <a href="#features" class="btn-hero btn-hero-secondary">
                    <i class="fas fa-info-circle"></i> Scopri di più
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Perché scegliere VaiQui?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Setup Veloce</h3>
                    <p>Crea la tua pagina in meno di 5 minuti. Nessuna competenza tecnica richiesta.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Totalmente Personalizzabile</h3>
                    <p>Scegli colori, icone, immagini e layout per riflettere la tua identità.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <h3>Link Accorciati</h3>
                    <p>Crea link corti personalizzati con QR code per condividerli facilmente.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Statistiche Avanzate</h3>
                    <p>Monitora i click, visualizzazioni e performance dei tuoi link in tempo reale.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>La tua pagina è perfettamente ottimizzata per tutti i dispositivi.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Sicuro e Privato</h3>
                    <p>I tuoi dati sono protetti e la tua privacy è garantita.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Gratuito</p>
                </div>
                <div class="stat-item">
                    <h3>∞</h3>
                    <p>Link Illimitati</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Disponibile</p>
                </div>
                <div class="stat-item">
                    <h3>🚀</h3>
                    <p>Veloce</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Pronto a iniziare?</h2>
            <p>Crea la tua pagina professionale in pochi minuti e condividi tutti i tuoi link in un unico posto.</p>
            <a href="index.php" class="btn-hero btn-hero-primary">
                <i class="fas fa-rocket"></i> Crea il tuo profilo gratis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> VaiQui. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script>
        // Smooth scroll per i link interni
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>

