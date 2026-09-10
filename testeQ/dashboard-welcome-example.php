<?php
/**
 * Exemplo de uso do Dashboard Welcome com texto de boas-vindas
 * FullTorque - Sistema de Oficina
 */

// Incluir configurações (ajustar conforme sua estrutura)
require_once 'config.php';
require_once 'dashboard-welcome-helper.php';

// Simular dados de sessão para exemplo
if (!isset($_SESSION['usuario_nome'])) {
    $_SESSION['usuario_nome'] = 'João Silva'; // Exemplo
    $_SESSION['usuario_id'] = 1;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FullTorque</title>
    
    <!-- CSS necessários -->
    <link rel="stylesheet" href="themes.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="mobile-specific.css">
    <link rel="stylesheet" href="dashboard-welcome-global.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Adicionar estilos inline se necessário -->
    <?php add_dashboard_welcome_inline_styles(); ?>
</head>
<body>
    <!-- Exemplo de sidebar com informações do usuário -->
    <div class="sidebar">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></div>
                <div class="user-role">Cliente</div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="content">
        <!-- Dashboard Welcome com texto de boas-vindas -->
        <div class="dashboard-welcome">
            <!-- O texto de boas-vindas será adicionado aqui via JavaScript -->
            
            <div class="welcome-message">
                <h2>
                    <span class="text-white">Bem-vindo à</span> 
                    <span class="text-green">FullTorque</span>
                </h2>
                <p>Sua oficina de confiança com qualidade italiana</p>
            </div>
            
            <div class="welcome-actions">
                <a href="agendamento-novo.php" class="btn">
                    <i class="fas fa-calendar-plus"></i> Novo Agendamento
                </a>
                <a href="veiculos.php" class="btn">
                    <i class="fas fa-car"></i> Meus Veículos
                </a>
            </div>
        </div>
        
        <!-- Outros conteúdos do dashboard -->
        <div class="dashboard-content">
            <h3>Conteúdo do Dashboard</h3>
            <p>Aqui ficaria o restante do conteúdo do dashboard...</p>
        </div>
    </div>
    
    <!-- JavaScript necessário -->
    <script src="dashboard-welcome-user.js"></script>
    
    <!-- Definir nome do usuário -->
    <?php set_dashboard_welcome_user(); ?>
    
    <!-- Controle de tema (se necessário) -->
    <script>
        // Exemplo de controle de tema
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar tema salvo
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
        });
    </script>
    
    <!-- Debug (apenas em desenvolvimento) -->
    <?php 
    if (defined('DEBUG') && DEBUG) {
        debug_dashboard_welcome_user();
    }
    ?>
    
    <style>
        /* Estilos de exemplo para demonstração */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            font-size: 2rem;
            color: #3498db;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .user-role {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .dashboard-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .theme-alemanha .dashboard-welcome {
            background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
        }
        
        .welcome-message h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .welcome-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .dashboard-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .content {
                margin-left: 0;
                padding: 15px;
            }
            
            .dashboard-welcome {
                padding: 20px 15px;
            }
            
            .welcome-message h2 {
                font-size: 1.5rem;
            }
            
            .welcome-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</body>
</html>