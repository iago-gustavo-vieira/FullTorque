<?php
$titulo = "Dashboard do Mecânico";
require_once 'header.php';

// Verificar se o usuário é um mecânico
if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Buscar informações do mecânico
$stmt = $conexao->prepare("SELECT * FROM mecanicos WHERE id = ?");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$mecanico = $stmt->get_result()->fetch_assoc();

// Estatísticas do mecânico
$stats = [];

// Total de diagnósticos
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM relatorios_cliente WHERE mecanico_id = ?");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$stats['total_diagnosticos'] = $stmt->get_result()->fetch_assoc()['total'];

// Diagnósticos hoje
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM relatorios_cliente WHERE mecanico_id = ? AND DATE(data_envio) = CURDATE()");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$stats['diagnosticos_hoje'] = $stmt->get_result()->fetch_assoc()['total'];

// Diagnósticos urgentes
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM relatorios_cliente WHERE mecanico_id = ? AND urgencia = 'alta'");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$stats['diagnosticos_urgentes'] = $stmt->get_result()->fetch_assoc()['total'];

// Últimos diagnósticos
$stmt = $conexao->prepare("
    SELECT r.*, u.nome as cliente_nome, v.marca, v.modelo, v.placa
    FROM relatorios_cliente r
    JOIN usuarios u ON r.usuario_id = u.id
    LEFT JOIN veiculos v ON r.veiculo_id = v.id
    WHERE r.mecanico_id = ?
    ORDER BY r.data_envio DESC
    LIMIT 5
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$ultimos_diagnosticos = $stmt->get_result();

$conexao->close();
?>

<style>
    .dashboard-welcome {
        background: linear-gradient(135deg, #109349 0%, #109349 33%, #ffffff 33%, #ffffff 66%, #DD0100 66%, #DD0100 100%);
        color: white;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 40px;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15), 0 8px 20px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }
    
    .dashboard-welcome::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: welcomeGlow 4s ease-in-out infinite alternate;
        pointer-events: none;
    }
    
    .dashboard-welcome::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        animation: welcomeShine 3s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes welcomeGlow {
        0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.3; }
        100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.1; }
    }
    
    @keyframes welcomeShine {
        0% { transform: translateX(-100%) rotate(45deg); }
        100% { transform: translateX(100%) rotate(45deg); }
    }
    
    .dashboard-welcome h1 {
        position: relative;
        z-index: 2;
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .dashboard-welcome h1 i {
        background: rgba(255, 255, 255, 0.2);
        padding: 15px;
        border-radius: 50%;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        animation: iconFloat 3s ease-in-out infinite;
    }
    
    @keyframes iconFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .dashboard-welcome p {
        position: relative;
        z-index: 2;
        font-size: 1.1rem;
        opacity: 0.9;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        margin-bottom: 25px;
    }
    
    .welcome-stats {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .welcome-stat {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        min-width: 120px;
        transition: all 0.3s ease;
    }
    
    .welcome-stat:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .welcome-stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .welcome-stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .theme-alemanha .dashboard-welcome {
        background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        border-color: rgba(255, 206, 0, 0.3);
    }
    
    .theme-alemanha .dashboard-welcome h1 i {
        background: rgba(255, 206, 0, 0.2);
        color: #FFCE00;
    }
    
    .theme-alemanha .welcome-stat {
        background: rgba(255, 206, 0, 0.15);
        border-color: rgba(255, 206, 0, 0.3);
    }
    
    .theme-alemanha .welcome-stat:hover {
        background: rgba(255, 206, 0, 0.25);
    }
    
    /* Responsividade para a seção bem-vindo */
    @media (max-width: 768px) {
        .dashboard-welcome {
            padding: 25px 20px;
            margin-bottom: 25px;
        }
        
        .dashboard-welcome h1 {
            font-size: 1.8rem;
            flex-direction: column;
            gap: 10px;
        }
        
        .dashboard-welcome h1 i {
            padding: 12px;
            font-size: 1.3rem;
        }
        
        .dashboard-welcome p {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .welcome-stats {
            gap: 15px;
            margin-top: 15px;
        }
        
        .welcome-stat {
            padding: 15px;
            min-width: 100px;
        }
        
        .welcome-stat-number {
            font-size: 1.5rem;
        }
        
        .welcome-stat-label {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 480px) {
        .dashboard-welcome {
            padding: 20px 15px;
        }
        
        .dashboard-welcome h1 {
            font-size: 1.5rem;
        }
        
        .welcome-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .welcome-stat {
            padding: 12px;
            min-width: auto;
        }
        
        .welcome-stat-number {
            font-size: 1.3rem;
        }
    }
    
    /* Efeitos visuais adicionais */
    .dashboard-welcome:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.2), 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .dashboard-welcome:hover::before {
        animation-duration: 2s;
    }
    
    .dashboard-welcome:hover h1 i {
        animation-duration: 1.5s;
        transform: scale(1.1);
    }
    
    /* Melhorias de acessibilidade */
    @media (prefers-reduced-motion: reduce) {
        .dashboard-welcome::before,
        .dashboard-welcome::after,
        .dashboard-welcome h1 i {
            animation: none;
        }
        
        .dashboard-welcome:hover {
            transform: none;
        }
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
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        border-left: 5px solid #109349;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .theme-alemanha .stat-card {
        background: #1a1a1a;
        color: white;
        border-left-color: #FFCE00;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(16, 147, 73, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        color: #109349;
        font-size: 1.5rem;
    }
    
    .theme-alemanha .stat-icon {
        background: rgba(255, 206, 0, 0.1);
        color: #FFCE00;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .theme-alemanha .stat-number {
        color: white;
    }
    
    .stat-label {
        color: #666;
        font-size: 1rem;
    }
    
    .theme-alemanha .stat-label {
        color: #ccc;
    }
    
    .recent-section {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid rgba(16, 147, 73, 0.1);
        transition: all 0.3s ease;
    }
    
    .recent-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .theme-alemanha .recent-section {
        background: #1a1a1a;
        color: white;
    }
    
    .section-header {
        padding: 25px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        position: relative;
    }
    
    .section-header::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, #109349, #0d7a3c);
    }
    
    .theme-alemanha .section-header {
        background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
        border-bottom-color: #333;
    }
    
    .theme-alemanha .section-header::before {
        background: linear-gradient(90deg, #FFCE00, #e6b800);
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    
    .theme-alemanha .section-title {
        color: white;
    }
    
    .diagnostico-item {
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s ease;
    }
    
    .diagnostico-item:hover {
        background-color: #f8f9fa;
    }
    
    .theme-alemanha .diagnostico-item {
        border-bottom-color: #333;
    }
    
    .theme-alemanha .diagnostico-item:hover {
        background-color: #2a2a2a;
    }
    
    .diagnostico-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .cliente-nome {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .theme-alemanha .cliente-nome {
        color: #FFCE00;
    }
    
    .data-diagnostico {
        color: #666;
        font-size: 0.9rem;
    }
    
    .theme-alemanha .data-diagnostico {
        color: #ccc;
    }
    
    .veiculo-info {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .theme-alemanha .veiculo-info {
        color: #ccc;
    }
    
    .urgencia-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .urgencia-baixa {
        background: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
    }
    
    .urgencia-media {
        background: rgba(241, 196, 15, 0.1);
        color: #f1c40f;
    }
    
    .urgencia-alta {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #ddd;
    }
    
    .mecanico-info {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-left: 5px solid #109349;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .mecanico-info::before {
        content: '🔧';
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 3rem;
        opacity: 0.1;
        pointer-events: none;
    }
    
    .mecanico-info:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: rgba(16, 147, 73, 0.05);
        border-radius: 10px;
        border-left: 3px solid #109349;
        transition: all 0.3s ease;
    }
    
    .info-item:hover {
        background: rgba(16, 147, 73, 0.1);
        transform: translateX(5px);
    }
    
    .theme-alemanha .info-item {
        background: rgba(255, 206, 0, 0.05);
        border-left-color: #FFCE00;
    }
    
    .theme-alemanha .info-item:hover {
        background: rgba(255, 206, 0, 0.1);
    }
    
    .info-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #109349, #0d7a3c);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(16, 147, 73, 0.3);
    }
    
    .theme-alemanha .info-icon {
        background: linear-gradient(135deg, #FFCE00, #e6b800);
        color: #000;
        box-shadow: 0 4px 15px rgba(255, 206, 0, 0.3);
    }
    
    .info-content {
        flex: 1;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 3px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .theme-alemanha .info-label {
        color: #ccc;
    }
    
    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .theme-alemanha .info-value {
        color: white;
    }
    
    .theme-alemanha .mecanico-info {
        background: #1a1a1a;
        color: white;
        border-left-color: #FFCE00;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 768px) {
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }
    
    @media (max-width: 480px) {
        .quick-actions {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    
    .action-btn {
        background: linear-gradient(135deg, #109349, #0d7a3c);
        color: white;
        padding: 20px;
        border-radius: 15px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(16, 147, 73, 0.3);
        position: relative;
        overflow: hidden;
        font-weight: 600;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .action-btn:hover::before {
        left: 100%;
    }
    
    .action-btn:hover {
        background: linear-gradient(135deg, #0d7a3c, #0a5d2e);
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 12px 35px rgba(16, 147, 73, 0.4);
    }
    
    .theme-alemanha .action-btn {
        background: linear-gradient(135deg, #FFCE00, #e6b800);
        color: #000;
        box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3);
    }
    
    .theme-alemanha .action-btn:hover {
        background: linear-gradient(135deg, #e6b800, #cc9f00);
        box-shadow: 0 12px 35px rgba(255, 206, 0, 0.4);
    }
    
    /* Melhorias visuais para os cartões de estatísticas */
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border-left: 5px solid #109349;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, rgba(16, 147, 73, 0.1), transparent);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }
    
    .theme-alemanha .stat-card::before {
        background: linear-gradient(135deg, rgba(255, 206, 0, 0.1), transparent);
    }
</style>

<div class="dashboard-welcome">
    <h1>
        <i class="fas fa-wrench"></i> 
        <span>Bem-vindo, <?php echo $mecanico['nome']; ?>!</span>
    </h1>
    <p>🔧 Painel do Mecânico Especializado - <?php echo $mecanico['especialidade']; ?> 🔧</p>
    <p style="font-size: 0.95rem; margin-bottom: 0;">"Excelência italiana em cada diagnóstico, precisão alemã em cada reparo"</p>
    
    <div class="welcome-stats">
        <div class="welcome-stat">
            <div class="welcome-stat-number"><?php echo $stats['total_diagnosticos']; ?></div>
            <div class="welcome-stat-label">Diagnósticos</div>
        </div>
        <div class="welcome-stat">
            <div class="welcome-stat-number"><?php echo $stats['diagnosticos_hoje']; ?></div>
            <div class="welcome-stat-label">Hoje</div>
        </div>
        <div class="welcome-stat">
            <div class="welcome-stat-number"><?php echo $stats['diagnosticos_urgentes']; ?></div>
            <div class="welcome-stat-label">Urgentes</div>
        </div>
        <div class="welcome-stat">
            <div class="welcome-stat-number"><?php echo $mecanico['ativo'] ? '✅' : '❌'; ?></div>
            <div class="welcome-stat-label">Status</div>
        </div>
    </div>
</div>

<div class="mecanico-info">
    <h3><i class="fas fa-user-tie"></i> Suas Informações Profissionais</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="info-item">
            <div class="info-icon"><i class="fas fa-user"></i></div>
            <div class="info-content">
                <div class="info-label">Nome Completo</div>
                <div class="info-value"><?php echo $mecanico['nome']; ?></div>
            </div>
        </div>
        <div class="info-item">
            <div class="info-icon"><i class="fas fa-tools"></i></div>
            <div class="info-content">
                <div class="info-label">Especialidade</div>
                <div class="info-value"><?php echo $mecanico['especialidade']; ?></div>
            </div>
        </div>
        <div class="info-item">
            <div class="info-icon"><i class="fas <?php echo $mecanico['ativo'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i></div>
            <div class="info-content">
                <div class="info-label">Status Atual</div>
                <div class="info-value" style="color: <?php echo $mecanico['ativo'] ? '#2ecc71' : '#e74c3c'; ?>; font-weight: 600;">
                    <?php echo $mecanico['ativo'] ? '✅ Ativo e Disponível' : '❌ Inativo'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="quick-actions">
    <a href="mecanico-diagnosticos.php" class="action-btn">
        <i class="fas fa-stethoscope"></i><br>
        Ver Diagnósticos
    </a>
    <a href="mecanico-agenda.php" class="action-btn">
        <i class="fas fa-calendar-check"></i><br>
        Minha Agenda
    </a>
    <a href="mecanico-clientes.php" class="action-btn">
        <i class="fas fa-users"></i><br>
        Meus Clientes
    </a>
    <a href="mecanico-relatorios.php" class="action-btn">
        <i class="fas fa-chart-line"></i><br>
        Relatórios
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-number"><?php echo $stats['total_diagnosticos']; ?></div>
        <div class="stat-label">Total de Diagnósticos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-number"><?php echo $stats['diagnosticos_hoje']; ?></div>
        <div class="stat-label">Diagnósticos Hoje</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-number"><?php echo $stats['diagnosticos_urgentes']; ?></div>
        <div class="stat-label">Casos Urgentes</div>
    </div>
</div>

<div class="recent-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-clock"></i> Últimos Diagnósticos
        </h3>
    </div>
    
    <?php if ($ultimos_diagnosticos->num_rows > 0): ?>
        <?php while ($diagnostico = $ultimos_diagnosticos->fetch_assoc()): ?>
            <div class="diagnostico-item">
                <div class="diagnostico-header">
                    <div class="cliente-nome">
                        <i class="fas fa-user"></i> <?php echo $diagnostico['cliente_nome']; ?>
                    </div>
                    <div class="data-diagnostico">
                        <?php echo formatarData($diagnostico['data_envio'], 'd/m/Y H:i'); ?>
                    </div>
                </div>
                
                <?php if ($diagnostico['marca']): ?>
                <div class="veiculo-info">
                    <i class="fas fa-car"></i> 
                    <?php echo $diagnostico['marca'] . ' ' . $diagnostico['modelo'] . ' (' . $diagnostico['placa'] . ')'; ?>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="urgencia-badge urgencia-<?php echo $diagnostico['urgencia']; ?>">
                        <i class="fas fa-flag"></i> <?php echo ucfirst($diagnostico['urgencia']); ?>
                    </span>
                    
                    <a href="mecanico-diagnosticos.php" style="color: #109349; text-decoration: none; font-size: 0.9rem;">
                        Ver detalhes <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
        
        <div style="padding: 20px; text-align: center; border-top: 1px solid #eee;">
            <a href="mecanico-diagnosticos.php" class="btn" style="background: #109349;">
                <i class="fas fa-list"></i> Ver Todos os Diagnósticos
            </a>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>Nenhum diagnóstico encontrado</h3>
            <p>Você ainda não possui diagnósticos direcionados para você.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Script para texto de boas-vindas responsivo -->
<script src="dashboard-welcome-mobile.js"></script>
<script>
// Definir o nome do usuário para o script de boas-vindas
if (typeof window.setUserName === 'function') {
    window.setUserName('<?php echo addslashes($mecanico['nome']); ?>');
}
</script>

<?php require_once 'footer.php'; ?>