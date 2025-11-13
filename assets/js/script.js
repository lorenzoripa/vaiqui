// Gestione tabs
function showTab(tabName) {
    // Nascondi tutti i form
    document.querySelectorAll('.auth-form').forEach(form => {
        form.classList.remove('active');
    });
    
    // Rimuovi active da tutti i tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostra il form selezionato
    document.getElementById(tabName + '-form').classList.add('active');
    
    // Attiva il tab selezionato
    event.target.classList.add('active');
}

// Gestione modali
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Chiudi modale cliccando fuori
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Gestione form link
function openLinkModal(linkId = null) {
     const form = document.getElementById('linkForm');
     const title = document.getElementById('modalTitle');
     const actionInput = form.querySelector('input[name="action"]');
     const linkIdInput = form.querySelector('input[name="link_id"]');
     const titleInput = form.querySelector('input[name="title"]');
     const urlInput = form.querySelector('input[name="url"]');
     const iconInput = form.querySelector('input[name="icon"]');
     const colorInput = form.querySelector('input[name="color"]');
     const imageInput = form.querySelector('input[name="image_url"]');
    const fileInput = form.querySelector('input[name="image_file"]');
    const removeImageInput = form.querySelector('input[name="remove_image"]');
    const removeImageButton = document.getElementById('removeImageButton');

    const setPreview = (src) => {
        updateLinkImagePreview(src);
        if (removeImageInput) {
            removeImageInput.value = src ? '0' : removeImageInput.value;
        }
    };
 
     form.action = 'dashboard.php';
 
     if (linkId) {
         // Modifica link esistente
         title.textContent = 'Modifica Link';
         actionInput.value = 'update_link';
         linkIdInput.value = linkId;
 
         const linkItem = document.querySelector(`.link-item[data-link-id="${linkId}"]`);
         if (linkItem) {
             titleInput.value = linkItem.dataset.linkTitle || '';
             urlInput.value = linkItem.dataset.linkUrl || '';
             iconInput.value = linkItem.dataset.linkIcon || 'fas fa-link';
             colorInput.value = linkItem.dataset.linkColor || '#007bff';
             imageInput.value = linkItem.dataset.linkImage || '';
            if (linkItem.dataset.linkImage) {
                setPreview(linkItem.dataset.linkImage);
            } else {
                updateLinkImagePreview('');
            }
         }
        if (removeImageInput) {
            removeImageInput.value = '0';
        }
        if (fileInput) {
            fileInput.value = '';
        }
     } else {
         // Nuovo link
         form.reset();
         title.textContent = 'Aggiungi Link';
         actionInput.value = 'add_link';
         linkIdInput.value = '';
 
         if (!iconInput.value) {
             iconInput.value = 'fas fa-link';
         }
         if (!colorInput.value) {
             colorInput.value = '#007bff';
         }
         if (imageInput) {
             imageInput.value = '';
         }
        if (fileInput) {
            fileInput.value = '';
        }
        updateLinkImagePreview('');
        if (removeImageInput) {
            removeImageInput.value = '0';
        }
     }
 
     openModal('linkModal');
 }

const imagePreviewWrapperEl = document.getElementById('imagePreviewWrapper');
const imagePreviewImgEl = document.getElementById('imagePreview');
const removeImageBtnEl = document.getElementById('removeImageButton');

function resolveLinkImageSrc(src) {
    if (!src) return '';
    if (/^data:/i.test(src)) {
        return src;
    }
    if (/^https?:\/\//i.test(src)) {
        return src;
    }
    return window.location.origin + '/' + src.replace(/^\/+/,'');
}

function updateLinkImagePreview(src) {
    if (!imagePreviewWrapperEl || !imagePreviewImgEl || !removeImageBtnEl) {
        return;
    }
    if (src) {
        imagePreviewImgEl.src = resolveLinkImageSrc(src);
        imagePreviewWrapperEl.classList.remove('hidden');
        removeImageBtnEl.classList.remove('hidden');
    } else {
        imagePreviewImgEl.src = '';
        imagePreviewWrapperEl.classList.add('hidden');
        removeImageBtnEl.classList.add('hidden');
    }
}

// loadLinkData non è più necessario: i dati vengono letti dal DOM

// Validazione form
function validateForm(form) {
    const title = form.querySelector('input[name="title"]').value.trim();
    const url = form.querySelector('input[name="url"]').value.trim();
    
    if (!title) {
        alert('Inserisci un titolo per il link');
        return false;
    }
    
    if (!url) {
        alert('Inserisci un URL per il link');
        return false;
    }
    
    // Validazione URL
    try {
        new URL(url);
    } catch (e) {
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            form.querySelector('input[name="url"]').value = 'https://' + url;
        }
    }
    
    return true;
}

// Gestione eliminazione link
function deleteLink(linkId) {
    if (confirm('Sei sicuro di voler eliminare questo link?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'dashboard.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_link';
        
        const linkIdInput = document.createElement('input');
        linkIdInput.type = 'hidden';
        linkIdInput.name = 'link_id';
        linkIdInput.value = linkId;
        
        form.appendChild(actionInput);
        form.appendChild(linkIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Gestione riordinamento link (drag & drop)
function initDragDrop() {
    const linkList = document.querySelector('.link-list');
    if (!linkList) return;
    
    let draggedElement = null;
    
    linkList.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('link-item')) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        }
    });
    
    linkList.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('link-item')) {
            e.target.style.opacity = '1';
            draggedElement = null;
        }
    });
    
    linkList.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    linkList.addEventListener('drop', function(e) {
        e.preventDefault();
        if (draggedElement && e.target.classList.contains('link-item')) {
            const rect = e.target.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            
            if (e.clientY < midpoint) {
                linkList.insertBefore(draggedElement, e.target);
            } else {
                linkList.insertBefore(draggedElement, e.target.nextSibling);
            }
            
            // Salva il nuovo ordine
            saveLinkOrder();
        }
    });
}

function saveLinkOrder() {
    const linkItems = document.querySelectorAll('.link-item');
    const order = {};
    
    linkItems.forEach((item, index) => {
        const linkId = item.dataset.linkId;
        if (linkId) {
            order[linkId] = index + 1;
        }
    });
    
    // Invia l'ordine al server
    fetch('dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reorder_links',
            order: order
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Ordine dei link aggiornato', 'success');
        } else {
            showNotification(data.message || 'Errore durante il salvataggio dell\'ordine', 'error');
        }
    })
    .catch(() => {
        showNotification('Errore di connessione durante il salvataggio dell\'ordine', 'error');
    });
}

// Gestione upload avatar
function handleAvatarUpload(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Gestione logout
function logout() {
    if (confirm('Sei sicuro di voler effettuare il logout?')) {
        window.location.href = 'logout.php';
    }
}

// Inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza drag & drop se siamo nella dashboard
    if (document.querySelector('.link-list')) {
        initDragDrop();
    }
    
    // Gestione form con validazione
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.id === 'linkForm' && !validateForm(form)) {
                e.preventDefault();
            }
        });
    });

    const linkForm = document.getElementById('linkForm');
    if (linkForm) {
        const fileInput = linkForm.querySelector('input[name="image_file"]');
        const imageUrlInput = linkForm.querySelector('input[name="image_url"]');
        const removeImageInput = linkForm.querySelector('input[name="remove_image"]');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (!this.files || !this.files[0]) {
                    if (!imageUrlInput || !imageUrlInput.value.trim()) {
                        updateLinkImagePreview('');
                    }
                    return;
                }

                const file = this.files[0];
                const allowedTypes = ['image/jpeg', 'image/png'];

                if (!allowedTypes.includes(file.type)) {
                    showNotification('Formato immagine non valido (solo JPG o PNG)', 'error');
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    showNotification('Immagine troppo grande (max 2MB)', 'error');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    updateLinkImagePreview(e.target.result);
                };
                reader.readAsDataURL(file);

                if (removeImageInput) {
                    removeImageInput.value = '0';
                }
            });
        }

        if (removeImageBtnEl) {
            removeImageBtnEl.addEventListener('click', function() {
                if (imageUrlInput) {
                    imageUrlInput.value = '';
                }
                if (fileInput) {
                    fileInput.value = '';
                }
                updateLinkImagePreview('');
                if (removeImageInput) {
                    removeImageInput.value = '1';
                }
            });
        }

        if (imageUrlInput) {
            imageUrlInput.addEventListener('change', function() {
                const value = this.value.trim();
                if (value) {
                    updateLinkImagePreview(value);
                    if (removeImageInput) {
                        removeImageInput.value = '0';
                    }
                } else if (!fileInput || !fileInput.files.length) {
                    updateLinkImagePreview('');
                }
            });
        }
    }
    
    // Auto-focus sul primo campo vuoto
    const firstEmptyInput = document.querySelector('input:not([value]):not([type="hidden"])');
    if (firstEmptyInput) {
        firstEmptyInput.focus();
    }
});

// Utility per copiare URL
function copyToClipboard(text, buttonEl = null) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostra un messaggio di conferma
        const button = buttonEl || event.target;
        const originalText = button.textContent;
        button.textContent = 'Copiato!';
        button.style.background = '#28a745';
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = '';
        }, 2000);
    });
}

// Gestione link accorciati
let currentQRPath = '';
let currentQRUrl = '';

function showQRModal(qrPath, title) {
    currentQRPath = qrPath;
    currentQRUrl = window.location.origin + '/' + qrPath.replace(/^\/+/, '');

    const titleEl = document.getElementById('qrModalTitle');
    const imageEl = document.getElementById('qrImage');

    if (titleEl) {
        titleEl.textContent = 'QR Code - ' + title;
    }
    if (imageEl) {
        imageEl.src = qrPath;
    }

    openModal('qrModal');
}

function downloadQR() {
    if (currentQRPath) {
        const link = document.createElement('a');
        link.href = currentQRPath;
        link.download = 'qr-code.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function copyQRUrl() {
    if (currentQRUrl) {
        navigator.clipboard.writeText(currentQRUrl).then(function() {
            showNotification('URL QR Code copiato negli appunti!', 'success');
        });
    }
}

function deleteShortLink(linkId) {
    if (confirm('Sei sicuro di voler eliminare questo link accorciato?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'dashboard.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_short_link';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'short_link_id';
        idInput.value = linkId;

        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Gestione tema
function setTheme(theme) {
    document.body.className = theme;
    localStorage.setItem('theme', theme);
}

// Carica tema salvato
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        setTheme(savedTheme);
    }
});

// Gestione notifiche
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
        color: white;
        border-radius: 8px;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Animazioni CSS per le notifiche
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
