// Fix para menu hambúrguer mobile
(function() {
    'use strict';
    
    function setupMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (!toggle || !sidebar) {
            console.log('Elementos não encontrados, tentando novamente...');
            return false;
        }
        
        if (toggle.hasAttribute('data-menu-ready')) {
            return true;
        }
        
        toggle.setAttribute('data-menu-ready', 'true');
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            sidebar.classList.toggle('active');
            
            let overlay = document.querySelector('.mobile-overlay');
            
            if (sidebar.classList.contains('active')) {
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'mobile-overlay';
                    document.body.appendChild(overlay);
                    
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        this.remove();
                    });
                }
            } else {
                if (overlay) overlay.remove();
            }
        });
        
        // Fechar ao clicar em itens do menu
        sidebar.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    const overlay = document.querySelector('.mobile-overlay');
                    if (overlay) overlay.remove();
                }
            });
        });
        
        console.log('✅ Menu mobile configurado!');
        return true;
    }
    
    // Tentar configurar múltiplas vezes
    let attempts = 0;
    const maxAttempts = 10;
    
    function trySetup() {
        if (setupMobileMenu() || attempts >= maxAttempts) {
            return;
        }
        attempts++;
        setTimeout(trySetup, 100);
    }
    
    // Iniciar tentativas
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trySetup);
    } else {
        trySetup();
    }
})();
