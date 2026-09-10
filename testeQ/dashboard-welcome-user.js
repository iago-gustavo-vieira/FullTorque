/**
 * Dashboard Welcome User Text - FullTorque
 * Gerencia o texto de boas-vindas com nome do usuário no modo responsivo
 */

document.addEventListener('DOMContentLoaded', function() {
    // Função para adicionar texto de boas-vindas no dashboard-welcome
    function addWelcomeText() {
        const dashboardWelcome = document.querySelector('.dashboard-welcome');
        
        if (!dashboardWelcome) return;
        
        // Verificar se já existe o elemento
        let welcomeText = dashboardWelcome.querySelector('.dashboard-welcome-text');
        
        if (!welcomeText) {
            // Criar elemento de texto de boas-vindas
            welcomeText = document.createElement('div');
            welcomeText.className = 'dashboard-welcome-text';
            
            const welcomeTitle = document.createElement('h3');
            welcomeTitle.id = 'welcome-user-text';
            
            welcomeText.appendChild(welcomeTitle);
            
            // Inserir como primeiro elemento do dashboard-welcome
            dashboardWelcome.insertBefore(welcomeText, dashboardWelcome.firstChild);
        }
        
        // Obter nome do usuário (pode vir de diferentes fontes)
        const userName = getUserName();
        
        if (userName) {
            const welcomeTitle = welcomeText.querySelector('h3');
            welcomeTitle.textContent = `Bem-vindo, ${userName}!`;
        }
    }
    
    // Função para obter o nome do usuário
    function getUserName() {
        // Tentar obter de diferentes fontes
        
        // 1. Verificar se existe uma variável global JavaScript
        if (typeof window.userName !== 'undefined' && window.userName) {
            return window.userName;
        }
        
        // 2. Verificar elemento com classe user-name
        const userNameElement = document.querySelector('.user-name');
        if (userNameElement) {
            return userNameElement.textContent.trim();
        }
        
        // 3. Verificar sidebar user info
        const sidebarUserName = document.querySelector('.sidebar .user-info .user-name');
        if (sidebarUserName) {
            return sidebarUserName.textContent.trim();
        }
        
        // 4. Verificar meta tag com nome do usuário
        const userMeta = document.querySelector('meta[name="user-name"]');
        if (userMeta) {
            return userMeta.getAttribute('content');
        }
        
        // 5. Verificar localStorage
        const storedUserName = localStorage.getItem('userName');
        if (storedUserName) {
            return storedUserName;
        }
        
        // 6. Verificar sessionStorage
        const sessionUserName = sessionStorage.getItem('userName');
        if (sessionUserName) {
            return sessionUserName;
        }
        
        // 7. Fallback para nome genérico
        return 'Usuário';
    }
    
    // Função para atualizar o nome do usuário
    function updateUserName(newName) {
        const welcomeTitle = document.querySelector('#welcome-user-text');
        if (welcomeTitle && newName) {
            welcomeTitle.textContent = `Bem-vindo, ${newName}!`;
            
            // Salvar no localStorage para próximas visitas
            localStorage.setItem('userName', newName);
        }
    }
    
    // Função para mostrar/ocultar texto baseado no tamanho da tela
    function toggleWelcomeTextVisibility() {
        const welcomeText = document.querySelector('.dashboard-welcome-text');
        if (!welcomeText) return;
        
        // Mostrar apenas em telas menores que 768px
        if (window.innerWidth <= 768) {
            welcomeText.style.display = 'block';
        } else {
            welcomeText.style.display = 'none';
        }
    }
    
    // Inicializar
    addWelcomeText();
    toggleWelcomeTextVisibility();
    
    // Listener para redimensionamento da tela
    window.addEventListener('resize', toggleWelcomeTextVisibility);
    
    // Listener para mudanças de orientação (mobile)
    window.addEventListener('orientationchange', function() {
        setTimeout(toggleWelcomeTextVisibility, 100);
    });
    
    // Expor funções globalmente para uso externo
    window.DashboardWelcome = {
        updateUserName: updateUserName,
        addWelcomeText: addWelcomeText,
        getUserName: getUserName
    };
    
    // Observer para detectar mudanças no DOM (caso o dashboard seja carregado dinamicamente)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                const addedNodes = Array.from(mutation.addedNodes);
                const hasDashboard = addedNodes.some(node => 
                    node.nodeType === 1 && 
                    (node.classList?.contains('dashboard-welcome') || 
                     node.querySelector?.('.dashboard-welcome'))
                );
                
                if (hasDashboard) {
                    setTimeout(addWelcomeText, 100);
                }
            }
        });
    });
    
    // Observar mudanças no body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// Função auxiliar para definir nome do usuário via PHP/JavaScript
function setUserName(name) {
    if (window.DashboardWelcome) {
        window.DashboardWelcome.updateUserName(name);
    } else {
        // Se o script ainda não carregou, salvar no localStorage
        localStorage.setItem('userName', name);
    }
}