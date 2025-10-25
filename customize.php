<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/profile_customization.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user = getUser($_SESSION['user_id']);
$customization = getProfileCustomization($_SESSION['user_id']);
$themes = getAvailableThemes();
$button_styles = getAvailableButtonStyles();
$fonts = getAvailableFonts();

// Gestione del salvataggio delle personalizzazioni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_customization') {
    $customization_data = [
        'theme' => $_POST['theme'],
        'custom_css' => $_POST['custom_css'],
        'background_image' => $_POST['background_image'] ?? '',
        'button_style' => $_POST['button_style'],
        'font_family' => $_POST['font_family'],
        'primary_color' => $_POST['primary_color'],
        'secondary_color' => $_POST['secondary_color'],
        'text_color' => $_POST['text_color'],
        'background_color' => $_POST['background_color'],
        'button_color' => $_POST['button_color'],
        'button_text_color' => $_POST['button_text_color'],
        'border_radius' => (int)$_POST['border_radius'],
        'shadow_style' => $_POST['shadow_style'],
        'animation_style' => $_POST['animation_style']
    ];
    
    // Debug: mostra i dati che stiamo cercando di salvare
    if (isset($_GET['debug'])) {
        echo "<pre>Dati da salvare: " . print_r($customization_data, true) . "</pre>";
    }
    
    if (saveProfileCustomization($_SESSION['user_id'], $customization_data)) {
        $success = "Personalizzazione salvata con successo!";
        $customization = getProfileCustomization($_SESSION['user_id']);
    } else {
        $error = "Errore durante il salvataggio delle personalizzazioni. Controlla che le colonne del database esistano.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizza Profilo - VaiQui</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700&family=Playfair+Display:wght@400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .customize-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .preview-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .customize-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        @media (max-width: 1024px) {
            .customize-container {
                grid-template-columns: 1fr;
            }
            
            .preview-section {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-palette"></i> Personalizza Profilo</h1>
                <p>Personalizza l'aspetto del tuo profilo pubblico</p>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Torna al Dashboard
                    </a>
                    <a href="profile.php?user=<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> Anteprima Profilo
                    </a>
                    <a href="test_customization.php" class="btn btn-outline">
                        <i class="fas fa-bug"></i> Test Debug
                    </a>
                </div>
            </div>
        </div>

        <div class="customize-container">
            <!-- Sezione Personalizzazione -->
            <div class="customize-section">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="customizationForm">
                    <input type="hidden" name="action" value="save_customization">
                    
                    <!-- Temi Predefiniti -->
                    <div class="customization-group">
                        <h3><i class="fas fa-palette"></i> Tema</h3>
                        <div class="theme-grid">
                            <?php foreach ($themes as $theme_id => $theme): ?>
                                <div class="theme-option" data-theme="<?php echo $theme_id; ?>">
                                    <div class="theme-preview" style="background: <?php echo $theme['background_color']; ?>">
                                        <div class="theme-colors">
                                            <div class="color-dot" style="background: <?php echo $theme['primary_color']; ?>"></div>
                                            <div class="color-dot" style="background: <?php echo $theme['secondary_color']; ?>"></div>
                                        </div>
                                    </div>
                                    <span><?php echo $theme['name']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="theme" id="selectedTheme" value="<?php echo $customization['theme'] ?? 'default'; ?>">
                    </div>

                    <!-- Colori Personalizzati -->
                    <div class="customization-group">
                        <h3><i class="fas fa-paint-brush"></i> Colori Personalizzati</h3>
                        <div style="margin-bottom: 15px;">
                            <button type="button" id="resetThemeColors" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset ai Colori del Tema
                            </button>
                        </div>
                        <div class="color-grid">
                            <div class="color-input">
                                <label for="primary_color">Colore Primario</label>
                                <input type="color" id="primary_color" name="primary_color" 
                                       value="<?php echo $customization['primary_color'] ?? '#667eea'; ?>">
                            </div>
                            <div class="color-input">
                                <label for="secondary_color">Colore Secondario</label>
                                <input type="color" id="secondary_color" name="secondary_color" 
                                       value="<?php echo $customization['secondary_color'] ?? '#764ba2'; ?>">
                            </div>
                            <div class="color-input">
                                <label for="text_color">Colore Testo</label>
                                <input type="color" id="text_color" name="text_color" 
                                       value="<?php echo $customization['text_color'] ?? '#ffffff'; ?>">
                            </div>
                            <div class="color-input">
                                <label for="button_color">Colore Bottoni</label>
                                <input type="color" id="button_color" name="button_color" 
                                       value="<?php echo $customization['button_color'] ?? '#f8f9fa'; ?>">
                            </div>
                            <div class="color-input">
                                <label for="button_text_color">Colore Testo Bottoni</label>
                                <input type="color" id="button_text_color" name="button_text_color" 
                                       value="<?php echo $customization['button_text_color'] ?? '#333'; ?>">
                            </div>
                            <div class="color-input">
                                <label for="background_color">Colore Sfondo</label>
                                <input type="text" id="background_color" name="background_color" 
                                       value="<?php echo $customization['background_color'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; ?>"
                                       placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
                                <small>Puoi usare colori esadecimali (#667eea) o gradienti CSS</small>
                            </div>
                        </div>
                    </div>

                    <!-- Stile Bottoni -->
                    <div class="customization-group">
                        <h3><i class="fas fa-square"></i> Stile Bottoni</h3>
                        <select name="button_style" class="form-control">
                            <?php foreach ($button_styles as $style_id => $style_name): ?>
                                <option value="<?php echo $style_id; ?>" 
                                        <?php echo ($customization['button_style'] ?? 'default') === $style_id ? 'selected' : ''; ?>>
                                    <?php echo $style_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Font -->
                    <div class="customization-group">
                        <h3><i class="fas fa-font"></i> Font</h3>
                        <select name="font_family" class="form-control">
                            <?php foreach ($fonts as $font_id => $font_name): ?>
                                <option value="<?php echo $font_id; ?>" 
                                        <?php echo ($customization['font_family'] ?? 'default') === $font_id ? 'selected' : ''; ?>>
                                    <?php echo $font_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Impostazioni Avanzate -->
                    <div class="customization-group">
                        <h3><i class="fas fa-cog"></i> Impostazioni Avanzate</h3>
                        
                        <div class="form-group">
                            <label for="border_radius">Raggio Bordi (px)</label>
                            <input type="range" id="border_radius" name="border_radius" 
                                   min="0" max="50" value="<?php echo $customization['border_radius'] ?? 12; ?>">
                            <span id="border_radius_value"><?php echo $customization['border_radius'] ?? 12; ?>px</span>
                        </div>

                        <div class="form-group">
                            <label for="shadow_style">Stile Ombre</label>
                            <select name="shadow_style" class="form-control">
                                <option value="subtle" <?php echo ($customization['shadow_style'] ?? 'subtle') === 'subtle' ? 'selected' : ''; ?>>Sottile</option>
                                <option value="strong" <?php echo ($customization['shadow_style'] ?? 'subtle') === 'strong' ? 'selected' : ''; ?>>Forte</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="animation_style">Stile Animazioni</label>
                            <select name="animation_style" class="form-control">
                                <option value="subtle" <?php echo ($customization['animation_style'] ?? 'subtle') === 'subtle' ? 'selected' : ''; ?>>Sottile</option>
                                <option value="strong" <?php echo ($customization['animation_style'] ?? 'subtle') === 'strong' ? 'selected' : ''; ?>>Forte</option>
                            </select>
                        </div>
                    </div>

                    <!-- CSS Personalizzato -->
                    <div class="customization-group">
                        <h3><i class="fas fa-code"></i> CSS Personalizzato</h3>
                        <textarea name="custom_css" rows="8" class="form-control" 
                                  placeholder="/* Inserisci il tuo CSS personalizzato qui */"><?php echo htmlspecialchars($customization['custom_css'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salva Personalizzazione
                    </button>
                </form>
            </div>

            <!-- Anteprima Profilo -->
            <div class="preview-section">
                <div class="preview-header">
                    <h3><i class="fas fa-eye"></i> Anteprima</h3>
                </div>
                <div id="profilePreview" class="profile-preview">
                    <!-- L'anteprima verrà aggiornata dinamicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Aggiorna anteprima in tempo reale
        function updatePreview() {
            const form = document.getElementById('customizationForm');
            const formData = new FormData(form);
            
            // Simula l'anteprima del profilo
            const preview = document.getElementById('profilePreview');
            const backgroundValue = formData.get('background_color') || formData.get('primary_color') || '#667eea';
            preview.innerHTML = `
                <div class="profile-page" style="
                    font-family: ${formData.get('font_family') || 'inherit'};
                    background: ${backgroundValue};
                    color: ${formData.get('text_color') || 'white'};
                ">
                    <div class="profile-header" style="background: ${formData.get('primary_color') || '#667eea'};">
                        <div class="profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <h1 class="profile-name">${formData.get('display_name') || 'Il Tuo Nome'}</h1>
                        <p class="profile-bio">La tua biografia qui...</p>
                    </div>
                    <div class="profile-links">
                        <a href="#" class="profile-link" style="
                            background: ${formData.get('button_color') || '#f8f9fa'};
                            color: ${formData.get('button_text_color') || '#333'};
                            border-radius: ${formData.get('border_radius') || 12}px;
                        ">
                            <div class="profile-link-icon" style="background: ${formData.get('secondary_color') || '#764ba2'};">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="profile-link-title">Esempio Link</div>
                        </a>
                    </div>
                </div>
            `;
        }

        // Event listeners per aggiornare l'anteprima
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
            
            // Seleziona il tema corrente
            const currentTheme = document.getElementById('selectedTheme').value;
            if (currentTheme) {
                const themeOption = document.querySelector(`[data-theme="${currentTheme}"]`);
                if (themeOption) {
                    themeOption.classList.add('selected');
                }
            }
            
            // Aggiorna anteprima quando cambiano i valori
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('input', updatePreview);
                element.addEventListener('change', updatePreview);
            });

            // Gestione slider border radius
            const borderRadiusSlider = document.getElementById('border_radius');
            const borderRadiusValue = document.getElementById('border_radius_value');
            
            borderRadiusSlider.addEventListener('input', function() {
                borderRadiusValue.textContent = this.value + 'px';
                updatePreview();
            });

        // Gestione selezione tema
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedTheme').value = this.dataset.theme;
                
                // Aggiorna i colori automaticamente quando si seleziona un tema
                updateThemeColors(this.dataset.theme);
                updatePreview();
            });
        });
        
        // Gestione reset colori tema
        document.getElementById('resetThemeColors').addEventListener('click', function() {
            const currentTheme = document.getElementById('selectedTheme').value;
            if (currentTheme) {
                updateThemeColors(currentTheme);
                updatePreview();
            }
        });
        
        // Funzione per aggiornare i colori in base al tema selezionato
        function updateThemeColors(themeId) {
            const themes = {
                'default': {
                    primary_color: '#667eea',
                    secondary_color: '#764ba2',
                    text_color: '#ffffff',
                    background_color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    button_color: '#f8f9fa',
                    button_text_color: '#333'
                },
                'dark': {
                    primary_color: '#2c3e50',
                    secondary_color: '#34495e',
                    text_color: '#ffffff',
                    background_color: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
                    button_color: '#ecf0f1',
                    button_text_color: '#2c3e50'
                },
                'minimal': {
                    primary_color: '#ffffff',
                    secondary_color: '#f8f9fa',
                    text_color: '#333',
                    background_color: '#ffffff',
                    button_color: '#f8f9fa',
                    button_text_color: '#333'
                },
                'colorful': {
                    primary_color: '#e74c3c',
                    secondary_color: '#f39c12',
                    text_color: '#ffffff',
                    background_color: 'linear-gradient(135deg, #e74c3c 0%, #f39c12 100%)',
                    button_color: '#ecf0f1',
                    button_text_color: '#2c3e50'
                },
                'ocean': {
                    primary_color: '#3498db',
                    secondary_color: '#2980b9',
                    text_color: '#ffffff',
                    background_color: 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)',
                    button_color: '#ecf0f1',
                    button_text_color: '#2c3e50'
                },
                'forest': {
                    primary_color: '#27ae60',
                    secondary_color: '#2ecc71',
                    text_color: '#ffffff',
                    background_color: 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)',
                    button_color: '#ecf0f1',
                    button_text_color: '#2c3e50'
                }
            };
            
            const theme = themes[themeId];
            if (theme) {
                document.getElementById('primary_color').value = theme.primary_color;
                document.getElementById('secondary_color').value = theme.secondary_color;
                document.getElementById('text_color').value = theme.text_color;
                document.getElementById('background_color').value = theme.background_color;
                document.getElementById('button_color').value = theme.button_color;
                document.getElementById('button_text_color').value = theme.button_text_color;
            }
        }
        });
    </script>
</body>
</html>
