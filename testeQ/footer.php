    </div> <!-- Fechamento da div content -->

    <?php if (file_exists('components/support-button.php')) include 'components/support-button.php'; ?>
    
    <!-- Banner de Cookies LGPD -->
    <?php if (file_exists('components/cookie-banner.php')) include 'components/cookie-banner.php'; ?>



    <script>
    /**
     * Auto Service - Script principal
     * Funções interativas para melhorar a experiência do usuário
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa os tooltips
        initTooltips();
        
        // Adiciona animações de entrada aos cards
        animateCards();
        
        // Inicializa o menu mobile
        initMobileMenu();
        
        // Inicializa o modo escuro
        initDarkMode();
        
        // Inicializa notificações
        initNotifications();
        

    });

    /**
     * Inicializa tooltips em elementos com o atributo data-tooltip
     */
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        
        tooltips.forEach(element => {
            const tooltipText = element.getAttribute('data-tooltip');
            
            element.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = tooltipText;
                
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                const tooltipHeight = tooltip.offsetHeight;
                
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltipHeight - 10 + window.scrollY + 'px';
                
                setTimeout(() => {
                    tooltip.classList.add('show');
                }, 10);
            });
            
            element.addEventListener('mouseleave', function() {
                const tooltip = document.querySelector('.tooltip');
                if (tooltip) {
                    tooltip.classList.remove('show');
                    setTimeout(() => {
                        tooltip.remove();
                    }, 200);
                }
            });
        });
    }

    /**
     * Adiciona animações de entrada aos cards
     */
    function animateCards() {
        const cards = document.querySelectorAll('.card, .vehicle-card, .service-item, .animate-ready');
        
        if (cards.length === 0) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        cards.forEach(card => {
            observer.observe(card);
        });
    }

    /**
     * Inicializa o menu mobile
     */
    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (!menuToggle || !sidebar) return;
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Fecha o menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }

    /**
     * Inicializa o modo escuro
     */
    function initDarkMode() {
        const darkModeToggle = document.querySelector('.dark-mode-toggle');
        
        if (!darkModeToggle) return;
        
        // Verifica se o modo escuro está ativado no localStorage
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        // Aplica o modo escuro se estiver ativado
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.classList.add('active');
        }
        
        // Alterna o modo escuro ao clicar no botão
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            this.classList.toggle('active');
            
            // Salva a preferência no localStorage
            const isDarkModeNow = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkModeNow);
        });
    }

    /**
     * Inicializa o sistema de notificações
     */
    function initNotifications() {
        const notificationBell = document.querySelector('.notification-bell');
        
        if (!notificationBell) return;
        
        // O link de notificações agora funciona normalmente
    }

    </script>
    <script src="theme-controller.js"></script>
    <script src="mobile-enhancements.js"></script>
</body>
</html>