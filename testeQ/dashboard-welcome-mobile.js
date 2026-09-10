/**
 * Script para adicionar texto de boas-vindas no dashboard-welcome em modo responsivo
 * Adiciona o nome do usuário logado com cores específicas para cada tema
 */

document.addEventListener('DOMContentLoaded', function() {
    // Função para adicionar texto de boas-vindas
    function addWelcomeText() {
        const dashboardWelcome = document.querySelector('.dashboard-welcome');
        
        if (dashboardWelcome && window.innerWidth <= 768) {
            // Verificar se já existe o texto para evitar duplicação
            if (dashboardWelcome.querySelector('.dashboard-welcome-text')) {
                return;
            }
            
            // Tentar obter o nome do usuário de diferentes fontes
            let userName = '';
            
            // 1. Tentar pegar do elemento user-name na sidebar
            const userNameElement = document.querySelector('.user-name');
            if (userNameElement) {
                userName = userNameElement.textContent.trim();
            }
            
            // 2. Tentar pegar de variáveis PHP se disponíveis
            if (!userName && typeof window.usuarioNome !== 'undefined') {
                userName = window.usuarioNome;
            }
            
            // 3. Tentar pegar do sessionStorage
            if (!userName) {
                userName = sessionStorage.getItem('usuarioNome') || '';
            }
            
            // 4. Fallback para "Usuário" se não encontrar o nome
            if (!userName) {
                userName = 'Usuário';
            }
            
            // Criar elemento de boas-vindas
            const welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'dashboard-welcome-text';
            
            const welcomeTitle = document.createElement('h3');
            welcomeTitle.textContent = `Bem-vindo, ${userName}!`;
            
            welcomeDiv.appendChild(welcomeTitle);
            dashboardWelcome.appendChild(welcomeDiv);
        }
    }
    
    // Função para remover texto em desktop
    function removeWelcomeText() {
        const welcomeText = document.querySelector('.dashboard-welcome-text');
        if (welcomeText && window.innerWidth > 768) {
            welcomeText.remove();
        }
    }
    
    // Executar na inicialização
    addWelcomeText();
    
    // Executar quando a tela for redimensionada
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            addWelcomeText();
        } else {
            removeWelcomeText();
        }
    });
    
    // Observar mudanças de tema para ajustar as cores
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', function() {
            // Pequeno delay para garantir que o tema foi aplicado
            setTimeout(addWelcomeText, 100);
        });
    }
});

// Função global para definir o nome do usuário (pode ser chamada de PHP)
window.setUserName = function(name) {
    sessionStorage.setItem('usuarioNome', name);
    // Recriar o texto de boas-vindas com o novo nome
    const welcomeText = document.querySelector('.dashboard-welcome-text');
    if (welcomeText) {
        welcomeText.remove();
    }
    // Adicionar novamente com o nome atualizado
    if (window.innerWidth <= 768) {
        const dashboardWelcome = document.querySelector('.dashboard-welcome');
        if (dashboardWelcome) {
            const welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'dashboard-welcome-text';
            
            const welcomeTitle = document.createElement('h3');
            welcomeTitle.textContent = `Bem-vindo, ${name}!`;
            
            welcomeDiv.appendChild(welcomeTitle);
            dashboardWelcome.appendChild(welcomeDiv);
        }
    }
};