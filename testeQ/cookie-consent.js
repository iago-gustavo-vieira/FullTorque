/**
 * Sistema de Consentimento de Cookies - FullTorque
 * Garante que o banner apareça em todas as páginas até o usuário aceitar ou recusar
 */

(function() {
    'use strict';
    
    const COOKIE_NAME = 'fulltorque_cookies_consent';
    const COOKIE_EXPIRY_DAYS = 365; // 1 ano após aceitar/recusar
    
    /**
     * Verifica se o usuário já respondeu sobre os cookies
     */
    function checkCookieConsent() {
        const consent = getCookie(COOKIE_NAME);
        
        // Se não há resposta (nem aceitou nem recusou), mostrar banner
        if (!consent) {
            showCookieBanner();
        }
    }
    
    /**
     * Mostra o banner de cookies
     */
    function showCookieBanner() {
        // Aguardar um pouco para garantir que o DOM está carregado
        setTimeout(() => {
            const banner = document.getElementById('cookieBanner');
            if (banner) {
                banner.classList.add('show');
                banner.style.display = 'block';
            }
        }, 500);
    }
    
    /**
     * Aceitar cookies
     */
    window.acceptCookies = function() {
        setCookie(COOKIE_NAME, 'accepted', COOKIE_EXPIRY_DAYS);
        closeCookieBanner();
        showToast('✓ Preferências salvas com sucesso!', 'success');
    };
    
    /**
     * Recusar cookies
     */
    window.rejectCookies = function() {
        setCookie(COOKIE_NAME, 'rejected', COOKIE_EXPIRY_DAYS);
        closeCookieBanner();
        showToast('Você pode alterar suas preferências a qualquer momento.', 'info');
    };
    
    /**
     * Fechar banner de cookies
     */
    function closeCookieBanner() {
        const banner = document.getElementById('cookieBanner');
        if (banner) {
            banner.classList.remove('show');
            setTimeout(() => {
                banner.style.display = 'none';
            }, 400);
        }
    }
    
    /**
     * Definir cookie
     */
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
    }
    
    /**
     * Obter cookie
     */
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
    
    /**
     * Mostrar toast de notificação
     */
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'cookie-toast cookie-toast-' + type;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#2ecc71' : '#3498db'};
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            z-index: 10001;
            font-weight: 600;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 100);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkCookieConsent);
    } else {
        checkCookieConsent();
    }
    
    // Backup: verificar novamente após um delay
    setTimeout(checkCookieConsent, 1000);
    
})();
