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
    const modal = document.getElementById('linkModal');
    const form = document.getElementById('linkForm');
    const title = document.getElementById('modalTitle');
    
    if (linkId) {
        // Modifica link esistente
        title.textContent = 'Modifica Link';
        form.action = 'dashboard.php?action=update_link';
        form.querySelector('input[name="link_id"]').value = linkId;
        
        // Carica i dati del link (dovresti implementare questa funzione)
        loadLinkData(linkId);
    } else {
        // Nuovo link
        title.textContent = 'Aggiungi Link';
        form.action = 'dashboard.php?action=add_link';
        form.querySelector('input[name="link_id"]').value = '';
        form.reset();
    }
    
    openModal('linkModal');
}

function loadLinkData(linkId) {
    // Questa funzione dovrebbe caricare i dati del link dal server
    // Per ora lasciamo vuota, implementerai con AJAX
    console.log('Caricamento dati per link ID:', linkId);
}

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
    
    // Auto-focus sul primo campo vuoto
    const firstEmptyInput = document.querySelector('input:not([value]):not([type="hidden"])');
    if (firstEmptyInput) {
        firstEmptyInput.focus();
    }
});

// Utility per copiare URL
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostra un messaggio di conferma
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copiato!';
        button.style.background = '#28a745';
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = '';
        }, 2000);
    });
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
