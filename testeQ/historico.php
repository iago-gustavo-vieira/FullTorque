<?php
require_once 'header.php';

$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

$tabela_agendamentos = tabelaExiste($conexao, 'agendamentos');
$tabela_ordens = tabelaExiste($conexao, 'ordens_servico');

if ($tabela_agendamentos || $tabela_ordens) {
    $queries = [];
    $params = [];
    
    if ($tabela_agendamentos) {
        $queries[] = "SELECT 'agendamento' as tipo, a.id, a.data_agendamento as data, a.hora_inicio, a.hora_fim, v.marca, v.modelo, v.placa, NULL as valor_total, a.status FROM agendamentos a JOIN veiculos v ON a.veiculo_id = v.id WHERE a.usuario_id = ? AND a.status IN ('concluido', 'cancelado')";
        $params[] = $usuario_id;
    }
    
    if ($tabela_ordens) {
        $queries[] = "SELECT 'ordem' as tipo, os.id, os.data_abertura as data, NULL as hora_inicio, NULL as hora_fim, v.marca, v.modelo, v.placa, os.valor_total, os.status FROM ordens_servico os JOIN veiculos v ON os.veiculo_id = v.id WHERE os.usuario_id = ? AND os.status IN ('concluida', 'cancelada')";
        $params[] = $usuario_id;
    }
    
    $query = implode(' UNION ', $queries) . ' ORDER BY data DESC';
    $stmt = $conexao->prepare($query);
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $historico = $stmt->get_result();
} else {
    $historico = (object)['num_rows' => 0];
}

$conexao->close();
?>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Histórico de Serviços</div>
    <div class="welcome-message">
        <h2><i class="fas fa-history"></i> Histórico de Serviços</h2>
        <p>Acompanhe todo o histórico dos seus agendamentos e ordens de serviço realizados.</p>
    </div>
    <div class="welcome-actions">
        <a href="agendamento-novo.php" class="btn" data-tooltip="Novo agendamento">
            <i class="fas fa-plus"></i> Novo Agendamento
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <?php mostrarAlerta(); ?>
            
            <?php if ($historico->num_rows > 0): ?>
                <div class="timeline">
                    <?php while ($item = $historico->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon <?php echo $item['status']; ?>">
                                <?php if ($item['tipo'] === 'agendamento'): ?>
                                    <i class="fas fa-calendar-check"></i>
                                <?php else: ?>
                                    <i class="fas fa-clipboard-check"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-title">
                                        <?php if ($item['tipo'] === 'agendamento'): ?>
                                            Agendamento #<?php echo str_pad($item['id'], 6, '0', STR_PAD_LEFT); ?>
                                        <?php else: ?>
                                            Ordem de Serviço #<?php echo str_pad($item['id'], 6, '0', STR_PAD_LEFT); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-date">
                                        <?php echo formatarData($item['data'], 'd/m/Y'); ?>
                                    </div>
                                </div>
                                <div class="timeline-body">
                                    <div class="timeline-info">
                                        <span><i class="fas fa-car"></i> <?php echo $item['marca'] . ' ' . $item['modelo'] . ' (' . $item['placa'] . ')'; ?></span>
                                        <?php if ($item['tipo'] === 'agendamento' && !empty($item['hora_inicio'])): ?>
                                            <span><i class="far fa-clock"></i> <?php echo substr($item['hora_inicio'], 0, 5) . ' - ' . substr($item['hora_fim'], 0, 5); ?></span>
                                        <?php endif; ?>
                                        <?php if ($item['tipo'] === 'ordem' && !empty($item['valor_total'])): ?>
                                            <span><i class="fas fa-money-bill-wave"></i> <?php echo formatarMoeda($item['valor_total']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="timeline-status <?php echo $item['status']; ?>">
                                            <?php 
                                            if ($item['status'] === 'concluido' || $item['status'] === 'concluida') {
                                                echo 'Concluído';
                                            } elseif ($item['status'] === 'cancelado' || $item['status'] === 'cancelada') {
                                                echo 'Cancelado';
                                            } else {
                                                echo ucfirst(str_replace('_', ' ', $item['status']));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="timeline-footer">
                                    <?php if ($item['tipo'] === 'agendamento'): ?>
                                        <a href="agendamento-detalhes.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Ver Detalhes
                                        </a>
                                    <?php else: ?>
                                        <a href="ordem-detalhes.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Ver Detalhes
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="padding: 60px 20px; text-align: center; display: flex; flex-direction: column; align-items: center;">
                        <i class="fas fa-history" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 15px; color: #666;">Nenhum histórico encontrado</h3>
                        <p style="color: #999; margin-bottom: 30px;">Você ainda não possui agendamentos ou ordens de serviço concluídos.</p>
                        <a href="agendamento-novo.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agendar Serviço
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.dashboard-welcome {
    background: linear-gradient(135deg, #109349 0%, #109349 33%, #ffffff 33%, #ffffff 66%, #DD0100 66%, #DD0100 100%);
    color: white;
    border: 2px solid #109349;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.theme-alemanha .dashboard-welcome {
    background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
    color: white;
    border: 2px solid #FFCE00;
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.welcome-message p {
    opacity: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
    font-weight: 600;
    color: #fff;
}

.welcome-actions .btn {
    background-color: #109349;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.theme-alemanha .welcome-actions .btn {
    color: #fff;
    background-color: #000000;
}

.welcome-actions .btn:hover {
    background-color: #0d7a3a;
    transform: translateY(-1px);
}

.theme-alemanha .welcome-actions .btn:hover {
    background-color: #333;
}

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-md-12 {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 15px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.12);
}

.theme-alemanha .card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 10px 30px rgba(255,206,0,0.2);
}

.card-body {
    padding: 30px;
}

.theme-alemanha .card-body {
    background: #1a1a1a;
    color: white;
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 20px;
    width: 2px;
    background-color: #ddd;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 60px;
}

.timeline-icon {
    position: absolute;
    left: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #109349;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.timeline-icon.concluido, .timeline-icon.concluida {
    background-color: #109349;
}

.timeline-icon.cancelado, .timeline-icon.cancelada {
    background-color: #e74c3c;
}

.timeline-content {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    padding: 20px;
    border: 1px solid #e0e0e0;
}

.theme-alemanha .timeline-content {
    background-color: #2a2a2a;
    border-color: #3a3a3a;
    color: white;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.timeline-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

.theme-alemanha .timeline-title {
    color: white;
}

.timeline-date {
    font-size: 0.9rem;
    color: #777;
}

.timeline-body {
    margin-bottom: 15px;
}

.timeline-info {
    margin-bottom: 10px;
}

.timeline-info span {
    display: inline-block;
    margin-right: 15px;
    color: #666;
    font-size: 0.9rem;
}

.theme-alemanha .timeline-info span {
    color: #ccc;
}

.timeline-info i {
    margin-right: 5px;
    color: #109349;
}

.timeline-status {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-status.concluido, .timeline-status.concluida {
    background-color: rgba(16, 147, 73, 0.1);
    color: #109349;
}

.timeline-status.cancelado, .timeline-status.cancelada {
    background-color: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.timeline-footer {
    display: flex;
    justify-content: flex-end;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #109349;
    color: white;
}

.btn-primary:hover {
    background: #0d7a3a;
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-primary:hover {
    background: #e6b800;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .dashboard-welcome {
        background-image: url('bem-vindo-italia-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        padding: 15px 12px;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 15px;
        flex-direction: column;
        position: relative;
        min-height: 200px;
    }
    
    .theme-alemanha .dashboard-welcome {
        background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    
    /* Texto de boas-vindas mobile */
    .mobile-welcome-text {
        display: flex;
        position: absolute;
        top: 65px;
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
    
    .welcome-message {
        display: none !important;
    }
    
    .welcome-actions {
        display: none !important;
    }
    
    .welcome-actions {
        margin-top: 20px;
    }
    
    .timeline::before {
        left: 15px;
    }
    
    .timeline-item {
        padding-left: 50px;
    }
    
    .timeline-icon {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
    }
    
    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .timeline-info span {
        display: block;
        margin-bottom: 5px;
    }
}
</style>

<?php require_once 'footer.php'; ?>