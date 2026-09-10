/**
 * MOBILE ENHANCEMENTS - FULLTORQUE
 * Melhorias específicas para dispositivos móveis
 */

document.addEventListener('DOMContentLoaded', function() {
    // Detectar se é dispositivo móvel
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    if (isMobile || isTouch) {
        document.body.classList.add('is-mobile');
        initMobileEnhancements();
    }
    
    // Detectar orientação
    handleOrientationChange();
    window.addEventListener('orientationchange', handleOrientationChange);
    window.addEventListener('resize', handleResize);
});

/**
 * Inicializar melhorias para mobile
 */
function initMobileEnhancements() {
    // Melhorar touch nos botões
    enhanceTouchButtons();
    
    // Otimizar formulários para mobile
    optimizeForms();
    
    // Melhorar navegação mobile
    enhanceMobileNavigation();
    
    // Otimizar carrossel para touch
    optimizeCarouselForTouch();
    
    // Adicionar feedback visual para touch
    addTouchFeedback();
    
    // Otimizar imagens para mobile
    optimizeImagesForMobile();
    
    // Melhorar acessibilidade mobile
    enhanceMobileAccessibility();
}

/**
 * Melhorar touch nos botões
 */
function enhanceTouchButtons() {
    const buttons = document.querySelectorAll('.btn, button, .menu-item, .carousel-btn');
    
    buttons.forEach(button => {
        // Garantir tamanho mínimo de toque
        const computedStyle = window.getComputedStyle(button);
        const minSize = 44; // 44px é o tamanho mínimo recomendado
        
        if (parseInt(computedStyle.height) < minSize) {
            button.style.minHeight = minSize + 'px';
        }
        
        if (parseInt(computedStyle.width) < minSize && button.classList.contains('carousel-btn')) {
            button.style.minWidth = minSize + 'px';
        }
        
        // Adicionar classe para estilização específica de touch
        button.classList.add('touch-optimized');
        
        // Melhorar feedback visual
        button.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        button.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
}

/**
 * Otimizar formulários para mobile
 */
function optimizeForms() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        // Evitar zoom no iOS
        if (input.type !== 'file') {
            const currentFontSize = window.getComputedStyle(input).fontSize;
            if (parseInt(currentFontSize) < 16) {
                input.style.fontSize = '16px';
            }
        }
        
        // Melhorar tipos de input para mobile
        if (input.type === 'email') {
            input.setAttribute('inputmode', 'email');
        } else if (input.type === 'tel') {
            input.setAttribute('inputmode', 'tel');
        } else if (input.name === 'cpf') {
            input.setAttribute('inputmode', 'numeric');
        }
        
        // Adicionar autocomplete apropriado
        if (input.name === 'nome') {
            input.setAttribute('autocomplete', 'name');
        } else if (input.name === 'email') {
            input.setAttribute('autocomplete', 'email');
        } else if (input.name === 'telefone') {
            input.setAttribute('autocomplete', 'tel');
        }
    });
}

/**
 * Melhorar navegação mobile
 */
function enhanceMobileNavigation() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && sidebar) {
        // Melhorar animação do menu
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isActive = sidebar.classList.contains('active');
            
            if (isActive) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        
        // Fechar menu ao clicar em link
        const menuLinks = sidebar.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeMobileMenu();
                }
            });
        });
        
        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !mobileMenuToggle.contains(e.target)) {
                closeMobileMenu();
            }
        });
        
        // Fechar menu com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
    
    // Melhorar navegação da home
    if (navMenu) {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
            });
        }
    }
}

/**
 * Abrir menu mobile
 */
function openMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    
    sidebar.classList.add('active');
    mobileMenuToggle.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Adicionar overlay
    if (!document.querySelector('.mobile-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(overlay);
        
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
        
        overlay.addEventListener('click', closeMobileMenu);
    }
}

/**
 * Fechar menu mobile
 */
function closeMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const overlay = document.querySelector('.mobile-overlay');
    
    sidebar.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    document.body.style.overflow = '';
    
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

/**
 * Otimizar carrossel para touch
 */
function optimizeCarouselForTouch() {
    const carousel = document.querySelector('.promocoes-carousel');
    const track = document.querySelector('.carousel-track');
    
    if (!carousel || !track) return;
    
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let startTime = 0;
    
    // Touch events
    carousel.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startTime = Date.now();
        isDragging = true;
        carousel.style.cursor = 'grabbing';
        
        // Pausar autoplay
        if (typeof resetAutoPlay === 'function') {
            clearInterval(carouselInterval);
        }
    });
    
    carousel.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        currentX = e.touches[0].clientX;
        const diffX = currentX - startX;
        
        // Adicionar resistência nas bordas
        const resistance = 0.3;
        const maxDrag = carousel.offsetWidth * 0.3;
        const dragDistance = Math.max(-maxDrag, Math.min(maxDrag, diffX * resistance));
        
        track.style.transform = `translateX(calc(-${currentSlide * 100}% + ${dragDistance}px))`;
    });
    
    carousel.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        
        isDragging = false;
        carousel.style.cursor = 'grab';
        
        const diffX = currentX - startX;
        const diffTime = Date.now() - startTime;
        const velocity = Math.abs(diffX) / diffTime;
        
        // Determinar se deve mudar slide
        const threshold = carousel.offsetWidth * 0.2;
        const shouldChange = Math.abs(diffX) > threshold || velocity > 0.5;
        
        if (shouldChange) {
            if (diffX > 0) {
                // Swipe right - slide anterior
                if (typeof moveCarousel === 'function') {
                    moveCarousel(-1);
                }
            } else {
                // Swipe left - próximo slide
                if (typeof moveCarousel === 'function') {
                    moveCarousel(1);
                }
            }
        } else {
            // Voltar para posição original
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
        
        // Reiniciar autoplay
        if (typeof startAutoPlay === 'function') {
            setTimeout(startAutoPlay, 1000);
        }
    });
    
    // Prevenir scroll durante drag
    carousel.addEventListener('touchmove', function(e) {
        if (isDragging) {
            e.preventDefault();
        }
    }, { passive: false });
}

/**
 * Adicionar feedback visual para touch
 */
function addTouchFeedback() {
    const style = document.createElement('style');
    style.textContent = `
        .touch-optimized {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
        
        .touch-active {
            transform: scale(0.95);
            opacity: 0.8;
            transition: all 0.1s ease;
        }
        
        .is-mobile .btn:active,
        .is-mobile .menu-item:active {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .is-mobile .carousel-btn:active {
            background-color: rgba(255, 255, 255, 0.4);
        }
    `;
    document.head.appendChild(style);
}

/**
 * Otimizar imagens para mobile
 */
function optimizeImagesForMobile() {
    const images = document.querySelectorAll('img');
    
    images.forEach(img => {
        // Lazy loading nativo
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
        
        // Adicionar classe para otimização
        img.classList.add('mobile-optimized');
        
        // Melhorar carregamento
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
        
        // Fallback para imagens quebradas
        img.addEventListener('error', function() {
            this.style.display = 'none';
        });
    });
    
    // Adicionar estilos para imagens otimizadas
    const style = document.createElement('style');
    style.textContent = `
        .mobile-optimized {
            transition: opacity 0.3s ease;
            opacity: 0;
        }
        
        .mobile-optimized.loaded {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .mobile-optimized {
                height: auto;
                max-width: 100%;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Melhorar acessibilidade mobile
 */
function enhanceMobileAccessibility() {
    // Melhorar foco para navegação por teclado
    const focusableElements = document.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.classList.add('keyboard-focused');
        });
        
        element.addEventListener('blur', function() {
            this.classList.remove('keyboard-focused');
        });
    });
    
    // Adicionar estilos de foco melhorados
    const style = document.createElement('style');
    style.textContent = `
        .keyboard-focused {
            outline: 2px solid #CE2B37;
            outline-offset: 2px;
        }
        
        .theme-alemanha .keyboard-focused {
            outline-color: #FFCE00;
        }
        
        @media (max-width: 768px) {
            .keyboard-focused {
                outline-width: 3px;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Lidar com mudança de orientação
 */
function handleOrientationChange() {
    setTimeout(() => {
        // Recalcular dimensões após mudança de orientação
        const carousel = document.querySelector('.promocoes-carousel');
        if (carousel && typeof updateCarousel === 'function') {
            updateCarousel();
        }
        
        // Fechar menu se estiver aberto
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
        
        // Ajustar altura do viewport
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }, 100);
}

/**
 * Lidar com redimensionamento
 */
function handleResize() {
    // Debounce para evitar muitas execuções
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(() => {
        handleOrientationChange();
    }, 250);
}

/**
 * Utilitários para detecção de dispositivo
 */
const DeviceDetection = {
    isIOS: () => /iPad|iPhone|iPod/.test(navigator.userAgent),
    isAndroid: () => /Android/.test(navigator.userAgent),
    isTablet: () => window.innerWidth >= 768 && window.innerWidth <= 1024,
    isMobile: () => window.innerWidth < 768,
    hasTouch: () => 'ontouchstart' in window || navigator.maxTouchPoints > 0,
    
    getScreenSize: () => {
        if (window.innerWidth < 480) return 'small';
        if (window.innerWidth < 768) return 'medium';
        if (window.innerWidth < 1024) return 'large';
        return 'desktop';
    }
};

// Exportar para uso global
window.DeviceDetection = DeviceDetection;
window.MobileEnhancements = {
    openMobileMenu,
    closeMobileMenu,
    handleOrientationChange,
    handleResize
};