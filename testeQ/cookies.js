// Sistema de Gerenciamento de Cookies - FullTorque
// Persiste até o usuário aceitar ou recusar

const COOKIE_NAME = 'fulltorque_cookies_accepted';
const COOKIE_EXPIRY_DAYS = 365; // 1 ano após aceitar

// Verificar se os cookies foram aceitos
function checkCookieConsent() {
    const consent = getCookie(COOKIE_NAME);
    
    // Se não há resposta do usuário (nem aceitou nem recusou), mostrar banner
    if (!consent) {
        setTimeout(() => {
            const banner = document.getElementById('cookieBanner');
            if (banner) {
                banner.classList.add('show');
            }
        }, 500);
    }
}

// Aceitar cookies
function acceptCookies() {
    setCookie(COOKIE_NAME, 'accepted', COOKIE_EXPIRY_DAYS);
    closeCookieBanner();
    showToast('✓ Preferências salvas com sucesso!', 'success');
}

// Recusar cookies
function rejectCookies() {
    setCookie(COOKIE_NAME, 'rejected', COOKIE_EXPIRY_DAYS);
    closeCookieBanner();
    showToast('Você pode alterar suas preferências a qualquer momento.', 'info');
}

// Fechar banner
function closeCookieBanner() {
    const banner = document.getElementById('cookieBanner');
    if (banner) {
        banner.classList.remove('show');
    }
}

// Definir cookie
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
}

// Obter cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Mostrar toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'cookie-toast cookie-toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Adicionar estilos do toast
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .cookie-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #2ecc71;
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        z-index: 10001;
        font-weight: 600;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
    
    .cookie-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .cookie-toast-info {
        background: #3498db;
    }
    
    @media (max-width: 768px) {
        .cookie-toast {
            bottom: 10px;
            right: 10px;
            left: 10px;
            font-size: 14px;
        }
    }
`;
document.head.appendChild(toastStyles);

// Inicializar ao carregar a página
document.addEventListener('DOMContentLoaded', checkCookieConsent);
