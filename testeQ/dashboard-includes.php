<?php
// Arquivo para incluir os estilos e scripts do dashboard aprimorado
// Adicione este arquivo no header.php ou diretamente no index.php
?>

<!-- Dashboard Enhanced Styles -->
<link rel="stylesheet" href="assets/css/dashboard-enhanced.css">

<!-- Dashboard Effects JavaScript -->
<script src="assets/js/dashboard-effects.js"></script>

<!-- Fontes adicionais para melhor tipografia -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Ícones adicionais -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Aplicar fonte Inter globalmente */
body, * {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Melhorias adicionais de tipografia */
.card-title, .section-title {
    font-weight: 600;
    letter-spacing: -0.025em;
}

.card-value {
    font-weight: 800;
    letter-spacing: -0.05em;
}

/* Efeitos de foco para acessibilidade */
.card:focus-within,
.action-btn:focus,
.action-btn2:focus,
.action-btn3:focus,
.card-link:focus,
.section-action:focus {
    outline: 2px solid rgba(255, 255, 255, 0.6);
    outline-offset: 2px;
}

/* Melhorias para dispositivos touch */
@media (hover: none) and (pointer: coarse) {
    .card:hover,
    .action-btn:hover,
    .action-btn2:hover,
    .action-btn3:hover {
        transform: none;
    }
    
    .card:active {
        transform: scale(0.98);
    }
    
    .action-btn:active,
    .action-btn2:active,
    .action-btn3:active {
        transform: scale(0.9);
    }
}

/* Indicador de carregamento */
.loading-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    padding: 20px;
    color: #fff;
    font-weight: 600;
    z-index: 9999;
    display: none;
}

.loading-spinner {
    width: 30px;
    height: 30px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Melhorias para modo escuro automático */
@media (prefers-color-scheme: dark) {
    body {
        background: linear-gradient(-45deg, #1a1a2e, #16213e, #0f3460, #533483);
    }
}

/* Animação de entrada para novos elementos */
.fade-in-new {
    animation: fadeInScale 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Efeito de destaque para elementos importantes */
.highlight-pulse {
    animation: highlightPulse 2s ease-in-out infinite;
}

@keyframes highlightPulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
    }
}

/* Melhorias para performance */
.card,
.section,
.action-btn,
.action-btn2,
.action-btn3 {
    will-change: transform;
    backface-visibility: hidden;
    perspective: 1000px;
}

/* Suporte para reduced motion */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .floating-particles {
        display: none;
    }
}
</style>

<script>
// Função para mostrar indicador de carregamento
function showLoading(message = 'Carregando...') {
    const loader = document.createElement('div');
    loader.className = 'loading-indicator';
    loader.id = 'dashboard-loader';
    loader.innerHTML = `
        <div class="loading-spinner"></div>
        <div>${message}</div>
    `;
    document.body.appendChild(loader);
    loader.style.display = 'block';
}

// Função para esconder indicador de carregamento
function hideLoading() {
    const loader = document.getElementById('dashboard-loader');
    if (loader) {
        loader.style.display = 'none';
        loader.remove();
    }
}

// Melhorar performance com lazy loading de imagens
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// Adicionar suporte a gestos touch para mobile
let touchStartX = 0;
let touchStartY = 0;

document.addEventListener('touchstart', function(e) {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
});

document.addEventListener('touchend', function(e) {
    if (!touchStartX || !touchStartY) return;
    
    const touchEndX = e.changedTouches[0].clientX;
    const touchEndY = e.changedTouches[0].clientY;
    
    const diffX = touchStartX - touchEndX;
    const diffY = touchStartY - touchEndY;
    
    // Detectar swipe horizontal
    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
        if (diffX > 0) {
            // Swipe left
            console.log('Swipe left detected');
        } else {
            // Swipe right
            console.log('Swipe right detected');
        }
    }
    
    touchStartX = 0;
    touchStartY = 0;
});

// Função para adicionar efeito de shake em elementos com erro
function shakeElement(element) {
    element.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        element.style.animation = '';
    }, 500);
}

// CSS para animação de shake
const shakeCSS = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
`;

const shakeStyle = document.createElement('style');
shakeStyle.textContent = shakeCSS;
document.head.appendChild(shakeStyle);
</script>