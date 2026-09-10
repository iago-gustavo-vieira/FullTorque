<?php
// Helper para adicionar header padrão nas páginas admin
function renderAdminHeader($titulo, $subtitulo = '') {
    $usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Admin';
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
        <link rel="icon" type="image/jpeg" href="icone.jpg">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="themes.css">
        <link rel="stylesheet" href="admin-global-styles.css">
        <script src="prevent-back-navigation-admin.js"></script>
    </head>
    <body>
    <?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text">Bem-vindo, <?php echo $usuario_nome; ?>!</div>
            <h1><?php echo $titulo; ?></h1>
            <?php if ($subtitulo): ?>
                <p><?php echo $subtitulo; ?></p>
            <?php endif; ?>
        </div>
    <?php
}

function renderAdminFooter() {
    ?>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme') || 'default';
        if (savedTheme === 'theme-alemanha') {
            document.body.classList.add('theme-alemanha');
        }
        
        // Inicializar menu mobile
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                sidebar.classList.toggle('active');
                
                // Criar/remover overlay
                let overlay = document.querySelector('.mobile-overlay');
                if (sidebar.classList.contains('active')) {
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'mobile-overlay';
                        overlay.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0,0,0,0.5);
                            z-index: 1000;
                            backdrop-filter: blur(2px);
                        `;
                        document.body.appendChild(overlay);
                        
                        overlay.addEventListener('click', function() {
                            sidebar.classList.remove('active');
                            overlay.remove();
                        });
                    }
                } else {
                    if (overlay) overlay.remove();
                }
            });
            
            // Fechar menu ao clicar em itens do menu
            const menuItems = sidebar.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                        const overlay = document.querySelector('.mobile-overlay');
                        if (overlay) overlay.remove();
                    }
                });
            });
        }
    });
    </script>
    </body>
    </html>
    <?php
}
?>
