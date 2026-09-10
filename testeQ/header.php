<?php
// Arquivo de cabeçalho comum para todas as páginas internas
require_once 'config.php';

// Verifica se o usuário está logado
verificarLogin();

// Verificar e atualizar informações do usuário
$conexao = conectarBD();
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;

if ($usuario_id > 0) {
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $_SESSION['id'] = $usuario['id'];
        if (!isset($_SESSION['usuario_nivel'])) {
            $_SESSION['usuario_nivel'] = 'cliente';
        }
        if (!isset($_SESSION['mecanico_id'])) {
            $_SESSION['mecanico_id'] = 0;
        }
    }
    $stmt->close();
}
$conexao->close();

// Contar notificações não lidas
$notificacoes_nao_lidas = 0;
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;
$conexao = conectarBD();

// Buscar foto do perfil
$foto_perfil = null;
if ($usuario_id > 0) {
    $check_column = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    if ($check_column->num_rows > 0) {
        $stmt = $conexao->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $foto_perfil = $result->fetch_assoc()['foto_perfil'];
        }
        $stmt->close();
    }
}

// Verificar se tabelas existem
$tabela_notificacoes = $conexao->query("SHOW TABLES LIKE 'notificacoes'")->num_rows > 0;
$tabela_promocoes = $conexao->query("SHOW TABLES LIKE 'notificacoes_promocoes'")->num_rows > 0;

// Contar notificações gerais não lidas
if ($tabela_notificacoes && $usuario_id > 0) {
    $check_column = $conexao->query("SHOW COLUMNS FROM notificacoes LIKE 'usuario_id'");
    if ($check_column->num_rows > 0) {
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificacoes_nao_lidas += $result->fetch_assoc()['total'];
        $stmt->close();
    }
}

// Contar notificações de promoções não lidas
if ($tabela_promocoes) {
    // Criar tabelas se não existirem
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_lidas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        notificacao_promocao_id INT NOT NULL,
        data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_leitura (usuario_id, notificacao_promocao_id)
    )");
    
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_excluidas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        notificacao_promocao_id INT NOT NULL,
        data_exclusao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_exclusao (usuario_id, notificacao_promocao_id)
    )");
    
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total 
        FROM notificacoes_promocoes np 
        WHERE np.enviado = 1 
        AND (np.usuario_id = ? OR np.usuario_id IS NULL)
        AND np.id NOT IN (
            SELECT notificacao_promocao_id 
            FROM notificacoes_lidas 
            WHERE usuario_id = ?
        )
        AND np.id NOT IN (
            SELECT notificacao_promocao_id 
            FROM notificacoes_excluidas 
            WHERE usuario_id = ?
        )
    ");
    $stmt->bind_param("iii", $usuario_id, $usuario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacoes_nao_lidas += $result->fetch_assoc()['total'];
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php include 'mobile-meta.php'; ?>
    <title><?php echo $titulo ?? SISTEMA_NOME; ?></title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="mobile-specific.css">
    <link rel="stylesheet" href="mobile-fix.css">
    <link rel="stylesheet" href="dashboard-welcome-global.css">
    <link rel="stylesheet" href="force-static-dashboard.css">
    <script src="remove-dashboard-animations.js"></script>
    <script src="prevent-back-navigation.js"></script>
    <script src="fix-mobile-menu.js" defer></script>
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --accent-color: <?php echo COR_DESTAQUE; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
            --text-color: <?php echo COR_TEXTO; ?>;
        }
     
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.2) 33%, rgba(221, 1, 0, 0.15) 33%, rgba(221, 1, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 100%) !important;
            background-color: #1a1a1a !important;
        }
        

        
        body.dark-mode .card,
        body.dark-mode .table-container,
        body.dark-mode .info-item,
        body.dark-mode .vehicle-info,
        body.dark-mode .service-item,
        body.dark-mode .summary {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .card-header,
        body.dark-mode th {
            background-color: #333;
            border-color: #444;
        }
        
        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background-color: #333;
            border-color: #444;
            color: #f5f5f5;
        }

        
        .sidebar {
            width: 250px;
            background-color: #109349;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        .theme-alemanha .sidebar{
            background-color: #000000;
            border: none;
             box-shadow: 8px 0 20px #DD0100;
        }
        
        .theme-toggle-container {
            position: relative;
            margin-top: 20px;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .theme-alemanha .theme-toggle-container {
            background: rgba(255, 206, 0, 0.2);
            border-top: 1px solid rgba(255, 206, 0, 0.3);
            box-shadow: 0 -5px 15px rgba(255, 206, 0, 0.4);
        }
        
        .theme-toggle-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .theme-label {
            font-size: 18px;
        }
        
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #109349;
            transition: .4s;
            border-radius: 30px;
        }
        
        .theme-alemanha .slider {
            background-color: #FFCE00;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #000000;
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .flag-icon {
            display: inline-block;
            width: 20px;
            height: 14px;
            border-radius: 2px;
            margin: 0 3px;
        }
        
        .italy-flag {
            background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%) !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .germany-flag {
            background: linear-gradient(to bottom, #000000 0%, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%, #ffce00 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        .sidebar-header {
            padding: 0 0 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .user-info-top {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .user-actions {
            display: flex;
            gap: 8px;
        }
        
        .user-action-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s;
        }
        
        .btn-edit-profile {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }
        
        .btn-edit-profile:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background: rgba(221, 1, 0, 0.8);
            color: white;
        }
        
        .btn-logout:hover {
            background: #DD0100;
            transform: translateY(-2px);
        }
        
        .theme-alemanha .btn-logout {
            background: rgba(255, 206, 0, 0.9);
            color: #000;
        }
        
        .theme-alemanha .btn-logout:hover {
            background: #FFCE00;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #DD0100;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-avatar i {
            font-size: 20px;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-stats {
            padding: 15px 20px;
            margin: 10px 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border-left: 3px solid #CE2A37;
            color: white;
        }
        
        .theme-alemanha .sidebar-stats {
            border-left-color: #FFCE00;
            background: rgba(255, 206, 0, 0.1);
        }
        
        .sidebar-stats .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 0.8rem;
        }
        
        .sidebar-stats .stat-item span:first-child {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        
        .stat-item i {
            margin-right: 8px;
            color: white;
        }
        
        .theme-alemanha .stat-item i {
            color: #FFCE00;
        }
        
        .stat-value {
            font-weight: 600;
            font-size: 1rem;
        }
        

        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #CE2A37;
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        

        
        .page-header-image {
            width: 500px;
            height: 500px;
            object-fit: contain;
        }
        
        .page-header-image-germany {
            display: none;
        }
        
        .theme-alemanha .page-header-image-italy {
            display: none;
        }
        
        .theme-alemanha .page-header-image-germany {
            display: block;
        }
        
        .page-header-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .page-header-actions {
            margin-top: 15px;
        }
        
        .page-header-actions .btn {
            background-color: #109349;
            color: white;
        }
        
        .theme-alemanha .page-header-actions .btn {
            background-color: #FFCE00;
            color: #000;
        }
        
        .page-header-actions .btn:hover {
            background-color: #0d7a3e;
            transform: translateY(-2px);
        }
        
        .theme-alemanha .page-header-actions .btn:hover {
            background-color: #daaf03;
        }
        

        
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            background: transparent;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }
        

        
        /* Estilos para o botão de notificações */
        .notification-bell {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f1f1f1;
            color: #DD0100;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .theme-alemanha .notification-bell{
            background-color: #DD0100;
        }
         .theme-alemanha .notification-bell i{
            color: #FFCE00;
        }
        

        .notification-bell.has-notifications::after {
            content: attr(data-count);
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--error-color);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .theme-alemanha .notification-bell.has-notifications::after {
            background-color: #ffce00;
            color: black;
        }
        
        /* Estilos para o botão de menu mobile */
        .mobile-menu-toggle {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: 5px;
            background-color: #109349;
            color: white;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1002;
            transition: all 0.3s;
            border: none;
            font-size: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .mobile-menu-toggle:hover {
            background-color: #0d7a3e;
            transform: scale(1.05);
        }
        
        .mobile-menu-toggle:active {
            transform: scale(0.95);
        }
        
        .mobile-menu-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 147, 73, 0.3);
        }
        
        .theme-alemanha .mobile-menu-toggle {
            background-color: #FFCE00 !important;
            color: #000000 !important;
        }
        
        .theme-alemanha .mobile-menu-toggle:hover {
            background-color: #e6b800 !important;
        }
        
        .theme-alemanha .mobile-menu-toggle:focus {
            box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.3) !important;
        }
        
        /* Overlay para mobile */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(2px);
        }
        
        /* Animações para os cards */
        .animate-ready {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s, transform 0.5s;
        }
        
        .animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Tooltips */
        .tooltip {
            position: absolute;
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }
        
        .tooltip.show {
            opacity: 1;
        }
        
        /* Estilos para seções do menu */
        .menu-section {
            margin: 10px 0;
        }
        
        .menu-section-title {
            padding: 15px 20px 10px 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .menu-section-title i {
            margin-right: 8px;
            font-size: 14px;
        }
        
        .submenu-item {
            padding-left: 35px;
            font-size: 0.9rem;
        }
        
        .submenu-item i {
            font-size: 16px;
        }
        
        /* Responsividade Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                position: fixed;
                z-index: 1001;
                transition: transform 0.3s ease;
                box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                width: 100%;
            }
            
            .mobile-menu-toggle {
                display: flex;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1002;
                background-color: #109349;
                color: white;
                width: 44px;
                height: 44px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            }
            
            .theme-alemanha .mobile-menu-toggle {
                background-color: #FFCE00 !important;
                color: #000000 !important;
            }
            
            .header {
                margin-top: 0;
                padding-top: 10px;
                background: transparent;
            }
            
            .header h1 {
                font-size: 1.5rem;
                font-weight: 700;
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.8);
                margin-left: 50px;
            }
            
            .theme-alemanha .header h1 {
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.5);
            }
            
            .header-actions {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .sidebar {
                width: 260px;
            }
            
            .content {
                padding: 65px 10px 10px 10px;
            }
            
            .header {
                flex-direction: column;
                gap: 10px;
                margin-bottom: 15px;
                padding-top: 5px;
                background: transparent;
            }
            
            .header h1 {
                font-size: 1.4rem;
                font-weight: 700;
                text-align: left;
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.8);
                margin-left: 50px;
                line-height: 1.3;
            }
            
            .theme-alemanha .header h1 {
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.5);
            }
            
            .notification-bell {
                width: 35px;
                height: 35px;
            }
            
            .user-info {
                padding: 15px;
            }
            
            .user-name {
                font-size: 0.85rem;
            }
            
            .user-role {
                font-size: 0.75rem;
            }
            
            .menu-item {
                padding: 12px 15px;
                font-size: 14px;
            }
            
            .submenu-item {
                padding-left: 25px;
                font-size: 13px;
            }
            
            .menu-section-title {
                padding: 12px 15px 8px 15px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 360px) {
            .sidebar {
                width: 240px;
            }
            
            .content {
                padding: 60px 8px 8px 8px;
            }
            
            .mobile-menu-toggle {
                width: 40px;
                height: 40px;
                top: 15px;
                left: 15px;
                font-size: 16px;
            }
            
            .header {
                background: transparent;
                padding: 15px;
            }
            
            .header h1 {
                font-size: 1.2rem;
                font-weight: 700;
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.8);
                margin-left: 45px;
            }
            
            .theme-alemanha .header h1 {
                color: #fff;
                text-shadow: 0 2px 8px rgba(0,0,0,0.5);
            }
            
            .notification-bell {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle" type="button" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar">
        <div class="sidebar-header" style="text-align: center;">
            <img src="logo.png" alt="<?php echo SISTEMA_NOME; ?>" style="max-width: 250px; height: auto; margin: 0 auto 10px auto; display: block;">

        </div>
        
        <div class="user-info">
            <div class="user-info-top">
                <div class="user-avatar">
                    <?php if (!empty($foto_perfil) && file_exists('uploads/perfil/' . $foto_perfil)): ?>
                        <img src="uploads/perfil/<?php echo htmlspecialchars($foto_perfil); ?>?v=<?php echo time(); ?>" alt="Foto do perfil">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Usuário'; ?></div>
                    <div class="user-role"><?php 
                        if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0) {
                            echo 'Funcionário';
                        } else {
                            echo isset($_SESSION['usuario_nivel']) ? ucfirst($_SESSION['usuario_nivel']) : 'Cliente';
                        }
                    ?></div>
                </div>
            </div>
            
            <div class="user-actions">
                <a href="perfil.php" class="user-action-btn btn-edit-profile">
                    <i class="fas fa-user-edit"></i>
                    <span>Editar</span>
                </a>
                <a href="#" onclick="confirmarSaida(); return false;" class="user-action-btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
        

        
        <div class="sidebar-menu">
            <?php if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0): ?>
                <!-- Menu para Mecânicos/Funcionários -->
                <a href="mecanico-dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="meus-diagnosticos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'meus-diagnosticos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Meus Diagnósticos</span>
                </a>
                <a href="mecanico-orcamentos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-orcamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calculator"></i>
                    <span>Solicitações de Orçamento</span>
                </a>
                <a href="mecanico-agenda.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-agenda.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Minha Agenda</span>
                </a>
                <a href="mecanico-clientes.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-clientes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Meus Clientes</span>
                </a>
                <a href="mecanico-relatorios.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Relatórios</span>
                </a>
                <a href="perfil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Meu Perfil</span>
                </a>
            <?php else: ?>
                <!-- Menu para Clientes -->
                <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Início</span>
                </a>
                <a href="veiculos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'veiculos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span>Meus Veículos</span>
                </a>
                <a href="agendamento-diagnostico.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'agendamento-diagnostico.php' ? 'active' : ''; ?>">
                    <i class="fas fa-stethoscope"></i>
                    <span>Solicitar Diagnóstico</span>
                </a>
                <a href="relatorios.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Meus Diagnósticos</span>
                </a>
                <a href="agendamentos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'agendamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Agendamentos</span>
                </a>
                <a href="pagamentos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'pagamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Pagamentos</span>
                </a>
                <a href="historico.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'historico.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                </a>

                <a href="perfil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Meu Perfil</span>
                </a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] == 'admin'): ?>
            <!-- Submenu Admin -->
            <div class="menu-section">
                <div class="menu-section-title">
                    <i class="fas fa-cogs"></i>
                    <span>Administração</span>
                </div>
                <a href="admin.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard Admin</span>
                </a>
                <a href="admin-relatorios.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-stethoscope"></i>
                    <span>Diagnósticos</span>
                </a>
                <a href="admin-agendamentos.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-agendamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Agendamentos</span>
                </a>
                <a href="admin-servicos.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-servicos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i>
                    <span>Serviços</span>
                </a>
                <a href="admin-mecanicos.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-mecanicos.php' || basename($_SERVER['PHP_SELF']) == 'admin-mecanico-form.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i>
                    <span>Mecânicos</span>
                </a>
                <a href="enviar-notificacoes.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'enviar-notificacoes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span>Enviar Notificações</span>
                </a>
                <a href="admin-usuarios.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-usuarios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Usuários</span>
                </a>
                <a href="admin-veiculos.php" class="menu-item submenu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-veiculos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span>Veículos</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Botão Toggle de Tema -->
        <div class="theme-toggle-container">
            <div class="theme-toggle-wrapper">
                <span class="flag-icon italy-flag"></span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
                    <span class="slider"></span>
                </label>
                <span class="flag-icon germany-flag"></span>
            </div>
        </div>
    </div>
    
    <div class="content">

        
        <div class="header">
            <div class="header-actions">

                <a href="notificacoes.php" class="notification-bell <?php echo $notificacoes_nao_lidas > 0 ? 'has-notifications' : ''; ?>" <?php echo $notificacoes_nao_lidas > 0 ? 'data-count="' . $notificacoes_nao_lidas . '"' : ''; ?> data-tooltip="Notificações" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-bell"></i>
                </a>
            </div>
        </div>
        
        <?php mostrarAlerta(); ?>
        

        
        <!-- Modal de confirmação de saída -->
        <div id="modalSair" class="modal-sair">
            <div class="modal-sair-content">
                <h3><i class="fas fa-sign-out-alt"></i> Confirmar Saída</h3>
                <p>Tem certeza que deseja sair do sistema?</p>
                <div class="modal-sair-actions">
                    <button onclick="fecharModalSair()" class="btn-cancelar">Cancelar</button>
                    <button onclick="window.location.href='logout.php'" class="btn-sair">Sair</button>
                </div>
            </div>
        </div>
        
        <style>
        .modal-sair {
            display: none;
            position: fixed;
            z-index: 10001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal-sair-content {
            background: linear-gradient(135deg, #009246 0%, #ffffff 50%, #CE2B37 100%);
            margin: 15% auto;
            padding: 3px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }
        
        .modal-sair-content::before {
            content: '';
            position: absolute;
            inset: 3px;
            background-color: white;
            border-radius: 13px;
            z-index: 1;
        }
        
        .modal-sair-content > * {
            position: relative;
            z-index: 2;
        }
        
        .theme-alemanha .modal-sair-content {
            background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
        }
        
        .theme-alemanha .modal-sair-content::before {
            background-color: #1a1a1a;
        }
        
        .modal-sair-content h3 {
            background: linear-gradient(90deg, #009246, #CE2B37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 30px 0 15px;
            font-size: 1.5rem;
        }
        
        .theme-alemanha .modal-sair-content h3 {
            background: linear-gradient(90deg, #FFCE00, #DD0100);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .modal-sair-content p {
            color: #666;
            margin-bottom: 25px;
            padding: 0 20px;
        }
        
        .theme-alemanha .modal-sair-content p {
            color: #ffffffb0;
        }
        
        .modal-sair-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 0 20px 30px;
        }
        
        .btn-cancelar, .btn-sair {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-cancelar {
            background: #6c757d;
            color: white;
        }
        
        .btn-cancelar:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-sair {
            background: #CE2B37;
            color: white;
        }
        
        .theme-alemanha .btn-sair {
            background: #FFCE00;
            color: #000;
        }
        
        .btn-sair:hover {
            background: #a01e28;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.4);
        }
        
        .theme-alemanha .btn-sair:hover {
            background: #daaf03;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.4);
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>
        
        <script>
            function confirmarSaida() {
                document.getElementById('modalSair').style.display = 'block';
            }
            
            function fecharModalSair() {
                document.getElementById('modalSair').style.display = 'none';
            }
            
            // Fechar modal ao clicar fora
            window.onclick = function(event) {
                const modal = document.getElementById('modalSair');
                if (event.target == modal) {
                    fecharModalSair();
                }
            }
            
            // Função para alternar tema
            function toggleTheme() {
                const toggle = document.getElementById('themeToggle');
                const isGerman = toggle.checked;
                
                document.body.classList.remove('theme-alemanha');
                if (isGerman) {
                    document.body.classList.add('theme-alemanha');
                    localStorage.setItem('theme', 'theme-alemanha');
                } else {
                    localStorage.setItem('theme', 'default');
                }
            }
            

            
            // Fechar menu ao redimensionar
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.mobile-overlay');
                    if (sidebar) sidebar.classList.remove('active');
                    if (overlay) overlay.remove();
                }
            });
            
            // Função para controlar o menu mobile
            function initMobileMenu() {
                const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                const sidebar = document.querySelector('.sidebar');
                
                if (mobileMenuToggle && sidebar) {
                    // Remover event listeners anteriores
                    mobileMenuToggle.onclick = null;
                    
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
            }
            
            // Aplicar tema salvo ao carregar
            document.addEventListener('DOMContentLoaded', function() {
                const savedTheme = localStorage.getItem('theme') || 'default';
                const toggle = document.getElementById('themeToggle');
                
                if (savedTheme === 'theme-alemanha') {
                    document.body.classList.add('theme-alemanha');
                    if (toggle) toggle.checked = true;
                }
                
                // Inicializar menu mobile
                initMobileMenu();
            });
            
            // Garantir que o menu funcione mesmo se o DOM já estiver carregado
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMobileMenu);
            } else {
                initMobileMenu();
            }
            
            // Backup: tentar inicializar o menu após um pequeno delay
            setTimeout(function() {
                if (!document.querySelector('.mobile-menu-toggle').onclick && 
                    !document.querySelector('.mobile-menu-toggle').hasAttribute('data-initialized')) {
                    initMobileMenu();
                    document.querySelector('.mobile-menu-toggle').setAttribute('data-initialized', 'true');
                }
            }, 100);
        </script>
        
        <!-- Script adicional para garantir funcionamento do menu -->
        <script>
            // Inicialização imediata do menu hamburguer
            (function() {
                function setupMobileMenu() {
                    const toggle = document.querySelector('.mobile-menu-toggle');
                    const sidebar = document.querySelector('.sidebar');
                    
                    if (toggle && sidebar && !toggle.hasAttribute('data-setup')) {
                        toggle.setAttribute('data-setup', 'true');
                        
                        toggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            console.log('Menu hamburguer clicado!');
                            sidebar.classList.toggle('active');
                            
                            // Gerenciar overlay
                            let overlay = document.querySelector('.mobile-overlay');
                            if (sidebar.classList.contains('active')) {
                                if (!overlay) {
                                    overlay = document.createElement('div');
                                    overlay.className = 'mobile-overlay';
                                    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;';
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
                        
                        console.log('Menu hamburguer configurado com sucesso!');
                    }
                }
                
                // Tentar configurar imediatamente
                setupMobileMenu();
                
                // Tentar novamente quando o DOM estiver pronto
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', setupMobileMenu);
                } else {
                    setTimeout(setupMobileMenu, 50);
                }
            })();
        </script>