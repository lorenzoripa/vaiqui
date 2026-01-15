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
            color: #1a1a1a;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            z-index: 1000;
            padding: 15px 0;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #667eea;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .btn-header {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-header:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.7; }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            padding: 40px 20px;
            max-width: 1200px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 30px;
            animation: fadeInDown 0.8s ease;
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 900;
            margin-bottom: 25px;
            line-height: 1.1;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero .subtitle {
            font-size: 1.5rem;
            margin-bottom: 40px;
            opacity: 0.95;
            font-weight: 300;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .hero-features {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 50px;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.95rem;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.8s both;
        }

        .btn-hero {
            padding: 18px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 100px 20px;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 60px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            text-align: left;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: #667eea;
        }

        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #1a1a1a;
            font-weight: 700;
        }

        .feature-card p {
            color: #666;
            line-height: 1.7;
            font-size: 1rem;
        }

        /* Demo Section */
        .demo-section {
            padding: 100px 20px;
            background: #f8f9fa;
        }

        .demo-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .demo-text h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        .demo-text p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .demo-features {
            list-style: none;
        }

        .demo-features li {
            padding: 12px 0;
            color: #333;
            font-size: 1.05rem;
        }

        .demo-features li i {
            color: #667eea;
            margin-right: 10px;
        }

        .demo-image {
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            text-align: center;
            color: white;
        }

        .demo-image i {
            font-size: 8rem;
            opacity: 0.9;
        }

        /* Stats Section */
        .stats {
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .stat-item p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        /* Testimonials */
        .testimonials {
            padding: 100px 20px;
            background: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .testimonial-card {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 25px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .testimonial-info h4 {
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .testimonial-info p {
            color: #666;
            font-size: 0.9rem;
        }

        /* FAQ Section */
        .faq {
            padding: 100px 20px;
            background: #f8f9fa;
        }

        .faq-list {
            max-width: 800px;
            margin: 60px auto 0;
        }

        .faq-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .faq-question {
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #1a1a1a;
            transition: all 0.3s;
        }

        .faq-question:hover {
            background: #f8f9fa;
        }

        .faq-question i {
            color: #667eea;
            transition: transform 0.3s;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s;
            color: #666;
            line-height: 1.7;
        }

        .faq-item.active .faq-answer {
            padding: 0 30px 25px;
            max-height: 500px;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, #667eea 0%, #ff8c42 100%);
            text-align: center;
            color: white;
        }

        .cta-section h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 900;
        }

        .cta-section p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 20px 30px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .footer-section a {
            display: block;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .subtitle {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .demo-content {
                grid-template-columns: 1fr;
            }

            .cta-section h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="theme-landing landing-page">
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="landing.php" class="logo">
                <i class="fas fa-link"></i> VaiQui
            </a>
            <nav class="nav-links">
                <a href="#features">Funzionalità</a>
                <a href="#demo">Demo</a>
                <a href="#testimonials">Testimonianze</a>
                <a href="#faq">FAQ</a>
            </nav>
            <a href="auth.php" class="btn-header">Accedi</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-heart"></i> Amato da migliaia di creatori
            </div>
            <h1>Il tuo Linktree personale,<br>tutto in uno.</h1>
            <p class="subtitle">Crea la tua pagina professionale in pochi minuti. Raggruppa tutti i tuoi link social, progetti e contenuti in un unico posto elegante e personalizzabile.</p>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-link"></i>
                    <span>Bio Pages</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-compress"></i>
                    <span>Link Accorciati</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Codes</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </div>
            </div>

            <div class="cta-buttons">
                <a href="auth.php" class="btn-hero btn-hero-primary">
                    <i class="fas fa-rocket"></i> Inizia Gratis
                </a>
                <a href="#demo" class="btn-hero btn-hero-secondary">
                    <i class="fas fa-play-circle"></i> Vedi Demo
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Tutto ciò di cui hai bisogno</h2>
            <p class="section-subtitle">Una piattaforma completa per gestire la tua presenza online in modo professionale</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Bio Link Pages</h3>
                    <p>Crea la tua pagina bio link unica e altamente personalizzabile con facilità. Colori personalizzati, componenti pronti all'uso, impostazioni SEO e molto altro.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-compress"></i>
                    </div>
                    <h3>Link Accorciati</h3>
                    <p>Un servizio di accorciamento URL all'avanguardia con pianificazione, limiti di scadenza, targeting avanzato e supporto per deep links.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3>QR Codes</h3>
                    <p>Sistema completo di generazione QR code con colori personalizzati, logo personalizzato, forme multiple e template predefiniti.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analytics Integrate</h3>
                    <p>Analytics facili da capire ma dettagliate per tutti i tuoi link. Continenti, paesi, dispositivi, browser e molto altro ancora.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>La tua pagina è perfettamente ottimizzata per tutti i dispositivi. Esperienza utente fluida su desktop, tablet e smartphone.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Sicuro e Privato</h3>
                    <p>I tuoi dati sono protetti e la tua privacy è garantita. Protezione password, avvisi per contenuti sensibili e molto altro.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section class="demo-section" id="demo">
        <div class="container">
            <div class="demo-content">
                <div class="demo-text">
                    <h2>Pagine Bio Link</h2>
                    <p>Crea la tua pagina bio link unica e personalizzabile con facilità. Con VaiQui puoi creare una pagina professionale che riflette la tua identità.</p>
                    <ul class="demo-features">
                        <li><i class="fas fa-check-circle"></i> Colori e branding personalizzati</li>
                        <li><i class="fas fa-check-circle"></i> Tonnellate di componenti pronti all'uso</li>
                        <li><i class="fas fa-check-circle"></i> Impostazioni SEO</li>
                        <li><i class="fas fa-check-circle"></i> Protezione password e avvisi contenuti</li>
                        <li><i class="fas fa-check-circle"></i> Template e temi predefiniti</li>
                    </ul>
                </div>
                <div class="demo-image">
                    <i class="fas fa-laptop-code"></i>
                    <p style="margin-top: 20px; font-size: 1.2rem;">Anteprima Dashboard</p>
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

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <h2 class="section-title">Perché le persone ci amano</h2>
            <p class="section-subtitle">Ascolta cosa dicono i nostri utenti</p>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Questa piattaforma ha completamente trasformato il modo in cui gestiamo i nostri workflow. È intuitiva, veloce e ha fatto risparmiare al nostro team innumerevoli ore ogni settimana."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">MW</div>
                        <div class="testimonial-info">
                            <h4>Marco Rossi</h4>
                            <p>Content Creator</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Ero scettico all'inizio, ma in pochi giorni ho visto quanto più produttivo è diventato il nostro team. Il supporto è anche incredibilmente reattivo."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">LS</div>
                        <div class="testimonial-info">
                            <h4>Laura Bianchi</h4>
                            <p>Influencer</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Abbiamo provato più strumenti prima, ma nulla si avvicina a questo. L'onboarding è stato fluido e l'intero team è stato operativo in poco tempo."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">GV</div>
                        <div class="testimonial-info">
                            <h4>Giuseppe Verdi</h4>
                            <p>Imprenditore</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq" id="faq">
        <div class="container">
            <h2 class="section-title">Domande Frequenti</h2>
            <p class="section-subtitle">Risposte alle tue domande più comuni</p>
            
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Come posso iniziare?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Semplicemente registrati per un account e segui i passaggi di onboarding. Sarai pronto a usare la piattaforma in pochi minuti.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Offrite supporto clienti?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Sì, il nostro team di supporto è disponibile 24/7 via email. Miriamo a rispondere a tutte le richieste entro poche ore.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>I miei dati sono sicuri?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Prendiamo la sicurezza dei dati molto seriamente. Tutte le informazioni sono crittografate e sottoposte a backup regolari per garantire che i tuoi dati siano al sicuro e protetti.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Avrò bisogno di competenze tecniche?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Affatto. La nostra piattaforma è progettata per essere user-friendly, senza bisogno di codifica per iniziare.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Cosa rende questo diverso da altri strumenti?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Ci concentriamo sulla semplicità e sulle prestazioni. La nostra piattaforma è leggera, facile da usare e progettata per aiutarti a ottenere risultati più velocemente.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Inizia ora</h2>
            <p>Inizia a usare il coltellino svizzero per i marketer.</p>
            <a href="auth.php" class="btn-hero btn-hero-primary" style="font-size: 1.2rem; padding: 20px 50px;">
                <i class="fas fa-rocket"></i> Registrati Gratis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>VaiQui</h4>
                <p style="color: rgba(255,255,255,0.7);">Il tuo Linktree personale. Crea la tua pagina professionale in pochi minuti.</p>
            </div>
            <div class="footer-section">
                <h4>Prodotto</h4>
                <a href="#features">Funzionalità</a>
                <a href="#demo">Demo</a>
                <a href="#testimonials">Testimonianze</a>
                <a href="#faq">FAQ</a>
            </div>
            <div class="footer-section">
                <h4>Supporto</h4>
                <a href="#">Documentazione</a>
                <a href="#">Contatti</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Termini di Servizio</a>
            </div>
            <div class="footer-section">
                <h4>Social</h4>
                <a href="#"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
                <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
            </div>
        </div>
        <div class="footer-bottom">
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
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                const isActive = item.classList.contains('active');
                
                // Chiudi tutti gli altri
                document.querySelectorAll('.faq-item').forEach(faq => {
                    faq.classList.remove('active');
                });
                
                // Apri/chiudi quello cliccato
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });

        // Header scroll effect
        let lastScroll = 0;
        const header = document.querySelector('.header');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            } else {
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.05)';
            }
            
            lastScroll = currentScroll;
        });
    </script>
</body>
</html>
