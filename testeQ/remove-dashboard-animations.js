// Script para forçar remoção de animações dos dashboard-welcome
// Executa assim que o DOM estiver carregado

document.addEventListener('DOMContentLoaded', function() {
    removeDashboardAnimations();
});

// Também executa imediatamente caso o DOM já esteja carregado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', removeDashboardAnimations);
} else {
    removeDashboardAnimations();
}

function removeDashboardAnimations() {
    // Selecionar todos os elementos dashboard-welcome
    const dashboardElements = document.querySelectorAll('.dashboard-welcome');
    
    dashboardElements.forEach(function(element) {
        // Forçar remoção de animações via JavaScript
        element.style.animation = 'none !important';
        element.style.backgroundSize = 'auto !important';
        element.style.backgroundPosition = 'initial !important';
        element.style.backgroundAttachment = 'scroll !important';
        element.style.transform = 'none !important';
        element.style.transition = 'none !important';
        
        // Remover classes que possam causar animação
        element.classList.remove('animate-ready', 'animate-in', 'gradient-shift');
        
        // Forçar remoção de pseudo-elementos
        const style = document.createElement('style');
        style.textContent = `
            .dashboard-welcome::before,
            .dashboard-welcome::after {
                display: none !important;
                content: none !important;
            }
        `;
        document.head.appendChild(style);
        
        // Aplicar estilo estático diretamente
        applyStaticStyle(element);
    });
    
    // Remover keyframes de animação se existirem
    removeAnimationKeyframes();
}

function applyStaticStyle(element) {
    // Verificar se é tema alemanha
    const isGermanTheme = document.body.classList.contains('theme-alemanha');
    
    if (isGermanTheme) {
        // Estilo tema alemanha
        element.style.background = 'linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%)';
        element.style.border = '2px solid #FFCE00';
    } else {
        // Estilo tema itália
        element.style.background = 'linear-gradient(135deg, #109349 0%, #109349 33%, #FFFFFF 33%, #FFFFFF 66%, #DD0101 66%, #DD0101 100%)';
        element.style.border = '2px solid #109349';
    }
    
    // Aplicar estilos comuns
    element.style.color = 'white';
    element.style.borderRadius = '20px';
    element.style.padding = '30px';
    element.style.marginBottom = '30px';
    element.style.display = 'flex';
    element.style.justifyContent = 'space-between';
    element.style.alignItems = 'center';
    element.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
    element.style.position = 'static';
    element.style.overflow = 'visible';
}

function removeAnimationKeyframes() {
    // Procurar por stylesheets e remover keyframes de animação
    const stylesheets = document.styleSheets;
    
    for (let i = 0; i < stylesheets.length; i++) {
        try {
            const stylesheet = stylesheets[i];
            const rules = stylesheet.cssRules || stylesheet.rules;
            
            for (let j = rules.length - 1; j >= 0; j--) {
                const rule = rules[j];
                if (rule.type === CSSRule.KEYFRAMES_RULE && 
                    (rule.name === 'gradientShift' || rule.name.includes('gradient'))) {
                    stylesheet.deleteRule(j);
                }
            }
        } catch (e) {
            // Ignorar erros de CORS
            console.log('Não foi possível acessar stylesheet:', e);
        }
    }
}

// Observar mudanças no DOM para elementos adicionados dinamicamente
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        mutation.addedNodes.forEach(function(node) {
            if (node.nodeType === 1) { // Element node
                if (node.classList && node.classList.contains('dashboard-welcome')) {
                    applyStaticStyle(node);
                }
                // Verificar elementos filhos
                const dashboardChildren = node.querySelectorAll && node.querySelectorAll('.dashboard-welcome');
                if (dashboardChildren) {
                    dashboardChildren.forEach(applyStaticStyle);
                }
            }
        });
    });
});

// Iniciar observação
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Executar novamente após um pequeno delay para garantir
setTimeout(removeDashboardAnimations, 100);
setTimeout(removeDashboardAnimations, 500);