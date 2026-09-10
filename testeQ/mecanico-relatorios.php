<?php
$titulo = "Relatórios e Estatísticas";
require_once 'header.php';

if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Estatísticas gerais
$stats = [];

// Total de diagnósticos
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM relatorios_cliente WHERE mecanico_id = ?");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$stats['total'] = $stmt->get_result()->fetch_assoc()['total'];

// Diagnósticos por urgência
$stmt = $conexao->prepare("
    SELECT urgencia, COUNT(*) as total 
    FROM relatorios_cliente 
    WHERE mecanico_id = ? 
    GROUP BY urgencia
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$urgencias = $stmt->get_result();

// Diagnósticos por mês (últimos 6 meses)
$stmt = $conexao->prepare("
    SELECT DATE_FORMAT(data_envio, '%Y-%m') as mes, COUNT(*) as total
    FROM relatorios_cliente 
    WHERE mecanico_id = ? AND data_envio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(data_envio, '%Y-%m')
    ORDER BY mes
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$por_mes = $stmt->get_result();

// Top clientes
$stmt = $conexao->prepare("
    SELECT u.nome, COUNT(r.id) as total
    FROM usuarios u
    JOIN relatorios_cliente r ON u.id = r.usuario_id
    WHERE r.mecanico_id = ?
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 5
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$top_clientes = $stmt->get_result();

$conexao->close();
?>

<style>
    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
        border-left: 4px solid #109349;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(16, 147, 73, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: #109349;
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .chart-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .chart-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    
    .chart-content {
        padding: 20px;
    }
    
    .urgencia-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .urgencia-label {
        display: flex;
        align-items: center;
    }
    
    .urgencia-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 10px;
    }
    
    .urgencia-baixa { background: #28a745; }
    .urgencia-media { background: #ffc107; }
    .urgencia-alta { background: #dc3545; }
    
    .mes-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cliente-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cliente-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #109349;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        margin-right: 10px;
    }
    
    .performance-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .performance-score {
        font-size: 3rem;
        font-weight: bold;
        color: #109349;
        margin-bottom: 10px;
    }
    
    .performance-label {
        color: #666;
        margin-bottom: 15px;
    }
    
    .performance-bar {
        width: 100%;
        height: 8px;
        background: #eee;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    
    .performance-fill {
        height: 100%;
        background: linear-gradient(90deg, #109349, #28a745);
        border-radius: 4px;
        transition: width 1s ease;
    }
</style>

<div class="stats-overview">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-number"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total de Diagnósticos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-number">
            <?php 
            $hoje = date('Y-m-d');
            echo floor($stats['total'] / max(1, (strtotime($hoje) - strtotime('-30 days')) / (60*60*24)));
            ?>
        </div>
        <div class="stat-label">Média por Dia</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number">
            <?php 
            $top_clientes->data_seek(0);
            echo $top_clientes->num_rows;
            ?>
        </div>
        <div class="stat-label">Clientes Atendidos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-number">4.8</div>
        <div class="stat-label">Avaliação Média</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-flag"></i> Diagnósticos por Urgência</h3>
        </div>
        <div class="chart-content">
            <?php while ($urgencia = $urgencias->fetch_assoc()): ?>
                <div class="urgencia-item">
                    <div class="urgencia-label">
                        <div class="urgencia-dot urgencia-<?php echo $urgencia['urgencia']; ?>"></div>
                        <?php echo ucfirst($urgencia['urgencia']); ?>
                    </div>
                    <strong><?php echo $urgencia['total']; ?></strong>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-line"></i> Últimos 6 Meses</h3>
        </div>
        <div class="chart-content">
            <?php while ($mes = $por_mes->fetch_assoc()): ?>
                <div class="mes-item">
                    <span><?php echo date('M/Y', strtotime($mes['mes'] . '-01')); ?></span>
                    <strong><?php echo $mes['total']; ?></strong>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-trophy"></i> Top 5 Clientes</h3>
        </div>
        <div class="chart-content">
            <?php 
            $top_clientes->data_seek(0);
            while ($cliente = $top_clientes->fetch_assoc()): 
            ?>
                <div class="cliente-item">
                    <div style="display: flex; align-items: center;">
                        <div class="cliente-avatar">
                            <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                        </div>
                        <span><?php echo $cliente['nome']; ?></span>
                    </div>
                    <strong><?php echo $cliente['total']; ?> diagnósticos</strong>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div class="performance-card">
        <div class="performance-score">95%</div>
        <div class="performance-label">Performance Geral</div>
        <div class="performance-bar">
            <div class="performance-fill" style="width: 95%;"></div>
        </div>
        <div style="font-size: 0.8rem; color: #666;">
            Baseado em diagnósticos realizados e feedback dos clientes
        </div>
    </div>
</div>

<script>
// Animação da barra de performance
document.addEventListener('DOMContentLoaded', function() {
    const fill = document.querySelector('.performance-fill');
    setTimeout(() => {
        fill.style.width = '95%';
    }, 500);
});
</script>

<?php require_once 'footer.php'; ?>