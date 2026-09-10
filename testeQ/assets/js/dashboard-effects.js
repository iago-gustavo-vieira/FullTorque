// Dashboard Effects - Efeitos interativos e animações

document.addEventListener('DOMContentLoaded', function() {
    
    // Criar partículas flutuantes de fundo
    createFloatingParticles();
    
    // Adicionar efeitos de hover nos cards
    addCardEffects();
    
    // Adicionar contador animado nos valores
    animateCounters();
    
    // Adicionar efeito de digitação no título
    addTypingEffect();
    
    // Adicionar efeitos de parallax suave
    addParallaxEffect();
    
    // Adicionar notificações visuais
    addVisualNotifications();
});

// Criar partículas flutuantes
function createFloatingParticles() {
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'floating-particles';
    document.body.appendChild(particlesContainer);
    
    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.width = (Math.random() * 10 + 5) + 'px';
        particle.style.height = particle.style.width;
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
        particlesContainer.appendChild(particle);
    }
}

// Efeitos nos cards
function addCardEffects() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach((card, index) => {
        // Efeito de entrada escalonada
        card.style.animationDelay = (index * 0.2) + 's';
        
        // Efeito de brilho no hover
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 25px 50px rgba(255, 255, 255, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.1)';
        });
        
        // Efeito de clique
        card.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(-8px) scale(0.98)';
        });
        
        card.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
    });
}

// Contador animado
function animateCounters() {
    const counters = document.querySelectorAll('.card-value');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

function animateCounter(element) {
    const target = parseInt(element.textContent);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Efeito de digitação no título
function addTypingEffect() {
    const titles = document.querySelectorAll('.section-title');
    
    titles.forEach((title, index) => {
        const text = title.textContent;
        title.textContent = '';
        title.style.borderRight = '2px solid #fff';
        
        setTimeout(() => {
            let i = 0;
            const typeTimer = setInterval(() => {
                title.textContent += text.charAt(i);
                i++;
                if (i >= text.length) {
                    clearInterval(typeTimer);
                    setTimeout(() => {
                        title.style.borderRight = 'none';
                    }, 500);
                }
            }, 100);
        }, index * 1000);
    });
}

// Efeito parallax suave
function addParallaxEffect() {
    let ticking = false;
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.card, .section');
        
        parallaxElements.forEach((element, index) => {
            const speed = 0.5 + (index * 0.1);
            const yPos = -(scrolled * speed);
            element.style.transform = `translateY(${yPos}px)`;
        });
        
        ticking = false;
    }
    
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestTick);
}

// Notificações visuais
function addVisualNotifications() {
    // Criar container de notificações
    const notificationContainer = document.createElement('div');
    notificationContainer.id = 'notification-container';
    notificationContainer.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        pointer-events: none;
    `;
    document.body.appendChild(notificationContainer);
    
    // Mostrar notificação de boas-vindas
    setTimeout(() => {
        showNotification('Bem-vindo ao seu dashboard! 🚗', 'success');
    }, 1000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px 20px;
        margin-bottom: 10px;
        color: #fff;
        font-weight: 500;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        transform: translateX(400px);
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        pointer-events: auto;
        cursor: pointer;
    `;
    
    notification.textContent = message;
    
    const container = document.getElementById('notification-container');
    container.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remover
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }, 4000);
    
    // Remover ao clicar
    notification.addEventListener('click', () => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    });
}

// Efeitos adicionais para botões
document.addEventListener('click', function(e) {
    if (e.target.matches('.action-btn, .action-btn2, .action-btn3, .card-link, .section-action')) {
        createRippleEffect(e);
    }
});

function createRippleEffect(e) {
    const button = e.target.closest('.action-btn, .action-btn2, .action-btn3, .card-link, .section-action');
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    `;
    
    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// Adicionar CSS para animação de ripple
const rippleCSS = `
@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.floating-particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.particle {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg);
        opacity: 0.7;
    }
    50% { 
        transform: translateY(-20px) rotate(180deg);
        opacity: 1;
    }
}
`;

const style = document.createElement('style');
style.textContent = rippleCSS;
document.head.appendChild(style);

// Efeito de loading suave para elementos
function addLoadingEffects() {
    const elements = document.querySelectorAll('.card, .section, table tr');
    
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Executar efeitos de loading
setTimeout(addLoadingEffects, 500);

// Adicionar efeito de hover nas linhas da tabela
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05))';
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.background = '';
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});

// Função para adicionar brilho nos ícones
function addIconGlow() {
    const icons = document.querySelectorAll('.card-icon i, .action-btn i, .action-btn2 i, .action-btn3 i');
    
    icons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.textShadow = '0 0 20px rgba(255, 255, 255, 0.8)';
            this.style.transform = 'scale(1.2)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.textShadow = '';
            this.style.transform = '';
        });
    });
}

// Executar efeito de brilho nos ícones
setTimeout(addIconGlow, 1000);