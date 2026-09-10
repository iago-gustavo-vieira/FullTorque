<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$titulo = "Painel Administrativo";

if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();

// Estatísticas principais
$total_usuarios = $conexao->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$total_agendamentos = $conexao->query("SELECT COUNT(*) as total FROM agendamentos")->fetch_assoc()['total'];
$total_veiculos = $conexao->query("SELECT COUNT(*) as total FROM veiculos")->fetch_assoc()['total'];
$total_servicos = $conexao->query("SELECT COUNT(*) as total FROM servicos")->fetch_assoc()['total'];

// Agendamentos por status
$agendamentos_pendentes = $conexao->query("SELECT COUNT(*) as total FROM agendamentos WHERE status IN ('agendado', 'confirmado')")->fetch_assoc()['total'];
$agendamentos_hoje = $conexao->query("SELECT COUNT(*) as total FROM agendamentos WHERE DATE(data_agendamento) = CURDATE()")->fetch_assoc()['total'];

// Receita do mês (se existir tabela de pagamentos)
$receita_mes = 0;
if ($conexao->query("SHOW TABLES LIKE 'pagamentos'")->num_rows > 0) {
    $result = $conexao->query("SELECT SUM(valor) as total FROM pagamentos WHERE MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE()) AND status = 'pago'");
    if ($result && $row = $result->fetch_assoc()) {
        $receita_mes = $row['total'] ?? 0;
    }
}

// Agendamentos recentes
$order_column = colunaExiste($conexao, 'agendamentos', 'data_criacao') ? 'a.data_criacao' : 
                (colunaExiste($conexao, 'agendamentos', 'created_at') ? 'a.created_at' : 'a.id');

$agendamentos_recentes = $conexao->query("
    SELECT a.*, u.nome as cliente, v.marca, v.modelo, v.placa
    FROM agendamentos a 
    JOIN usuarios u ON a.usuario_id = u.id 
    JOIN veiculos v ON a.veiculo_id = v.id 
    ORDER BY $order_column DESC 
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Usuários recentes
$usuarios_recentes = $conexao->query("
    SELECT id, nome, email, nivel_acesso, data_cadastro 
    FROM usuarios 
    ORDER BY data_cadastro DESC 
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Gráfico de agendamentos por mês (últimos 6 meses)
$agendamentos_grafico = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $result = $conexao->query("SELECT COUNT(*) as total FROM agendamentos WHERE DATE_FORMAT(data_agendamento, '%Y-%m') = '$mes'");
    $agendamentos_grafico[] = [
        'mes' => date('M', strtotime("-$i months")),
        'total' => $result->fetch_assoc()['total']
    ];
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
            color: #2c3e50;
            display: flex;
            min-height: 100vh;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.2) 33%, rgba(221, 1, 0, 0.15) 33%, rgba(221, 1, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 100%) !important;
            background-color: #1a1a1a !important;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s;
        }
        
        .page-header {
            background: linear-gradient(135deg, #109349 0%, #109349 33%, #FFFFFF 33%, #FFFFFF 66%, #DD0101 66%, #DD0101 100%) !important;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(16, 147, 73, 0.2);
            border: 2px solid #109349;
        }
        
        .theme-alemanha .page-header {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%) !important;
            border: 2px solid #FFCE00;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
        }
        
        .page-header p {
            opacity: 1;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .theme-alemanha .stat-card {
            background: #000000;
            color: white;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: var(--color);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .theme-alemanha .stat-value {
            color: #FFCE00;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .theme-alemanha .stat-label {
            color: #ffffffb0;
        }
        
        .stat-trend {
            font-size: 0.85rem;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
        }
        
        .theme-alemanha .stat-trend {
            color: #ffffffb0;
            border-top-color: #333;
        }
        
        .trend-up {
            color: #28a745;
        }
        
        .theme-alemanha .trend-up {
            color: #FFCE00;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .theme-alemanha .action-btn {
            background: #000000;
            border: 2px solid #DD0100;
            color: white;
        }
        
        .action-btn:hover {
            border-color: #109349;
            background: #109349;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(16, 147, 73, 0.2);
        }
        
        .theme-alemanha .action-btn:hover {
            border-color: #FFCE00;
            background: #FFCE00;
            color: #000;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .theme-alemanha .card {
            background: #000000;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #109349, #0d7a3a);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
         .theme-alemanha .card-header h3{
            color: white;
         }
        
        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header a {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        
        .card-header a:hover {
            opacity: 1;
        }
        
        .card-body {
            padding: 20px;
            min-height: auto;
        }
        
        .card-body:empty {
            padding: 0;
        }
        
        .list-item {
            display: flex;
            gap: 15px;
            padding: 0;
            margin-bottom: 20px;
            position: relative;
        }
        
        .list-item:last-child {
            margin-bottom: 0;
        }
        
        .list-item:last-child .item-line {
            display: none;
        }
        
        .item-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #109349, #0d7a3a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(16, 147, 73, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .theme-alemanha .item-avatar {
            background: linear-gradient(135deg, #FFCE00, #DD0100);
            box-shadow: 0 4px 10px rgba(255, 206, 0, 0.3);
        }
        
        .item-line {
            position: absolute;
            left: 22px;
            top: 45px;
            width: 2px;
            height: calc(100% + 20px);
            background: #e9ecef;
            z-index: 1;
        }
        
        .theme-alemanha .item-line {
            background: #333;
        }
        
        .item-content {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .theme-alemanha .item-content {
            background: #1a1a1a;
            box-shadow: 0 2px 8px rgba(255,206,0,0.1);
        }
        
        .item-content:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.12);
        }
        
        .theme-alemanha .item-content:hover {
            box-shadow: 0 5px 15px rgba(255,206,0,0.2);
        }
        
        .item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .theme-alemanha .item-title {
            color: white;
        }
        
        .item-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .item-subtitle i {
            color: #109349;
        }
        
        .theme-alemanha .item-subtitle {
            color: #ffffffb0;
        }
        
        .theme-alemanha .item-subtitle i {
            color: #FFCE00;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .chart-container {
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .theme-alemanha .chart-container {
            background: #000000;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .theme-alemanha .chart-container h3 {
            color: white;
        }
        
        .info-title {
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .theme-alemanha .info-title {
            color: white;
        }
        
        .info-value {
            font-size: 3rem;
            font-weight: 700;
        }
        
        .theme-alemanha .info-value {
            color: #FFCE00 !important;
        }
        
        .info-label {
            color: #6c757d;
            margin-top: 10px;
        }
        
        .theme-alemanha .info-label {
            color: #ffffffb0;
        }
        
        .chart-bars {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 200px;
            gap: 10px;
            margin-top: 20px;
        }
        
        .chart-bar {
            flex: 1;
            background: linear-gradient(to top, #109349, #0d7a3a);
            border-radius: 8px 8px 0 0;
            position: relative;
            transition: all 0.3s;
            min-height: 20px;
        }
        
        .theme-alemanha .chart-bar {
            background: linear-gradient(to top, #FFCE00, #DD0100);
        }
        
        .chart-bar:hover {
            opacity: 0.8;
            transform: scaleY(1.05);
        }
        
        .chart-label {
            text-align: center;
            margin-top: 10px;
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
        }
        
        .theme-alemanha .chart-label {
            color: #ffffffb0;
        }
        
        .chart-value {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .theme-alemanha .chart-value {
            color: #FFCE00;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .theme-alemanha .empty-state {
            color: #ffffffb0;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .page-header {
                background-image: url('bem-vindo-italia-responsivo.jpg') !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                padding: 15px 12px;
                border-radius: 10px;
                margin-bottom: 15px;
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: flex-end;
                text-align: right;
                position: relative;
            }
            
            .theme-alemanha .page-header {
                background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
                background-position: center !important;
            }
            
            .page-header h1,
            .page-header p {
                display: none !important;
            }
            
            .mobile-welcome-text {
                display: flex;
                position: absolute;
                top: 35px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(255, 255, 255, 0.9);
                color: #109349;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 18px;
                font-weight: 600;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(5px);
                z-index: 10;
                white-space: nowrap;
            }
            
            .theme-alemanha .mobile-welcome-text {
                background: rgba(0, 0, 0, 0.8);
                color: #EFC202;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-value {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                padding: 15px;
                min-height: 180px;
            }
            .mobile-welcome-text {
                top: 35px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .page-header p {
                font-size: 0.95rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="mobile-welcome-text">Bem-vindo, <?php echo isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Admin'; ?>!</div>
        <h1><i class="fas fa-chart-line"></i> Dashboard Administrativo</h1>
        <p>Visão geral do sistema e estatísticas em tempo real</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card" style="--color: #109349;">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $total_usuarios; ?></div>
                    <div class="stat-label">Total de Usuários</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-trend trend-up">
                <i class="fas fa-arrow-up"></i> Ativos no sistema
            </div>
        </div>
        
        <div class="stat-card" style="--color: #DD0100;">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $agendamentos_pendentes; ?></div>
                    <div class="stat-label">Agendamentos Pendentes</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-calendar-day"></i> <?php echo $agendamentos_hoje; ?> hoje
            </div>
        </div>
        
        <div class="stat-card" style="--color: #2c3e50;">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo $total_veiculos; ?></div>
                    <div class="stat-label">Veículos Cadastrados</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-database"></i> No sistema
            </div>
        </div>
        
        <div class="stat-card" style="--color: #f39c12;">
            <div class="stat-header">
                <div>
                    <div class="stat-value">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></div>
                    <div class="stat-label">Receita do Mês</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-trend trend-up">
                <i class="fas fa-arrow-up"></i> Mês atual
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <a href="admin-usuarios.php" class="action-btn">
            <i class="fas fa-user-plus"></i>
            Novo Usuário
        </a>
        <a href="admin-agendamentos.php" class="action-btn">
            <i class="fas fa-calendar-check"></i>
            Agendamentos
        </a>
        <a href="admin-veiculos.php" class="action-btn">
            <i class="fas fa-car"></i>
            Veículos
        </a>
        <a href="admin-servicos.php" class="action-btn">
            <i class="fas fa-tools"></i>
            Serviços
        </a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="chart-container">
            <h3 class="info-title"><i class="fas fa-tools"></i> Total de Serviços</h3>
            <div style="text-align: center; padding: 20px;">
                <div class="info-value" style="color: #109349;"><?php echo $total_servicos; ?></div>
                <div class="info-label">Serviços cadastrados</div>
            </div>
        </div>
        
        <div class="chart-container">
            <h3 class="info-title"><i class="fas fa-check-circle"></i> Hoje</h3>
            <div style="text-align: center; padding: 20px;">
                <div class="info-value" style="color: #DD0100;"><?php echo $agendamentos_hoje; ?></div>
                <div class="info-label">Agendamentos do dia</div>
            </div>
        </div>
        
        <div class="chart-container">
            <h3 class="info-title"><i class="fas fa-clock"></i> Pendentes</h3>
            <div style="text-align: center; padding: 20px;">
                <div class="info-value" style="color: #f39c12;"><?php echo $agendamentos_pendentes; ?></div>
                <div class="info-label">Aguardando atendimento</div>
            </div>
        </div>
    </div>
    
    <div class="content-grid">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt"></i> Agendamentos Recentes</h3>
                <a href="admin-agendamentos.php">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body">
                <?php if (empty($agendamentos_recentes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>Nenhum agendamento encontrado</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($agendamentos_recentes as $agendamento): ?>
                        <div class="list-item">
                            <div class="item-avatar">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="item-line"></div>
                            <div class="item-content">
                                <div class="item-title"><?php echo $agendamento['cliente']; ?></div>
                                <div class="item-subtitle">
                                    <span><i class="fas fa-car"></i> <?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></span>
                                    <span class="badge badge-<?php 
                                        echo match($agendamento['status']) {
                                            'agendado' => 'info',
                                            'confirmado' => 'success',
                                            'em_andamento' => 'warning',
                                            'concluido' => 'success',
                                            'cancelado' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>"><?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Usuários Recentes</h3>
                <a href="admin-usuarios.php">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body">
                <?php if (empty($usuarios_recentes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-times"></i>
                        <p>Nenhum usuário encontrado</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($usuarios_recentes as $usuario): ?>
                        <div class="list-item">
                            <div class="item-avatar">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="item-line"></div>
                            <div class="item-content">
                                <div class="item-title"><?php echo $usuario['nome']; ?></div>
                                <div class="item-subtitle">
                                    <span><i class="fas fa-envelope"></i> <?php echo $usuario['email']; ?></span>
                                    <span class="badge badge-<?php 
                                        echo match($usuario['nivel_acesso'] ?? 'cliente') {
                                            'admin' => 'danger',
                                            'gerente' => 'warning',
                                            'funcionario' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>"><?php echo ucfirst($usuario['nivel_acesso'] ?? 'cliente'); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar tema salvo
    const savedTheme = localStorage.getItem('theme') || 'default';
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
    }
    
    // Animar cards ao carregar
    const cards = document.querySelectorAll('.stat-card, .card, .action-btn');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
</script>
</body>
</html>
