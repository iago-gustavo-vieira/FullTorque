<?php
require_once 'config.php';
verificarLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: agendamentos.php");
    exit;
}

$agendamento_id = (int)$_GET['id'];
$conexao = conectarBD();

$stmt = $conexao->prepare("
    SELECT a.*, v.marca, v.modelo, v.placa, v.ano, v.cor
    FROM agendamentos a
    JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.id = ? AND a.usuario_id = ?
");
$stmt->bind_param("ii", $agendamento_id, $_SESSION['usuario_id']);
$stmt->execute();
$agendamento = $stmt->get_result()->fetch_assoc();

if (!$agendamento) {
    header("Location: agendamentos.php");
    exit;
}

$stmt = $conexao->prepare("
    SELECT ai.*, s.nome, s.descricao
    FROM agendamento_itens ai
    JOIN servicos s ON ai.servico_id = s.id
    WHERE ai.agendamento_id = ?
");
$stmt->bind_param("i", $agendamento_id);
$stmt->execute();
$servicos = $stmt->get_result();

$conexao->close();

$botoes_header = [
    [
        'url' => 'agendamentos.php',
        'icone' => 'fas fa-arrow-left',
        'texto' => 'Voltar'
    ]
];

require_once 'header.php';
?>

<style>
.detalhes-container {
    max-width: 1200px;
    margin: 0 auto;
}

.detalhes-header {
    background: linear-gradient(135deg, #DD0101 0%, #109349 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.theme-alemanha .detalhes-header {
    background: linear-gradient(135deg, #000 0%, #FFCE00 100%);
}

.detalhes-header h1 {
    margin: 0 0 10px 0;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 8px;
}

.status-agendado { background: #3498db; color: white; }
.status-confirmado { background: #f1c40f; color: #333; }
.status-em_andamento { background: #e67e22; color: white; }
.status-concluido { background: #2ecc71; color: white; }
.status-cancelado { background: #e74c3c; color: white; }

.info-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.theme-alemanha .info-card {
    background: #000;
    border: 1px solid #333;
}

.info-card i {
    font-size: 1.8rem;
    color: #DD0101;
    margin-bottom: 8px;
}

.theme-alemanha .info-card i {
    color: #FFCE00;
}

.info-card .label {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 5px;
}

.theme-alemanha .info-card .label {
    color: #999;
}

.info-card .value {
    font-size: 1rem;
    font-weight: 700;
    color: #333;
}

.theme-alemanha .info-card .value {
    color: white;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}

.card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.theme-alemanha .card {
    background: #000;
    border: 1px solid #333;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.2rem;
    font-weight: 700;
    color: #DD0101;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.theme-alemanha .card-title {
    color: #FFCE00;
    border-bottom-color: #333;
}

.veiculo-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.veiculo-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #DD0101, #109349);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.theme-alemanha .veiculo-icon {
    background: linear-gradient(135deg, #000, #FFCE00);
}

.veiculo-info h3 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.1rem;
}

.theme-alemanha .veiculo-info h3 {
    color: white;
}

.veiculo-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.theme-alemanha .veiculo-info p {
    color: #999;
}

.specs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.spec-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
}

.theme-alemanha .spec-item {
    background: #1a1a1a;
}

.spec-label {
    color: #666;
    font-weight: 500;
}

.theme-alemanha .spec-label {
    color: #999;
}

.spec-value {
    color: #333;
    font-weight: 700;
}

.theme-alemanha .spec-value {
    color: white;
}

.servico-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.theme-alemanha .servico-item {
    background: #1a1a1a;
}

.servico-info h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.theme-alemanha .servico-info h4 {
    color: white;
}

.servico-info p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 0.85rem;
}

.theme-alemanha .servico-info p {
    color: #999;
}

.servico-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #DD0101;
}

.theme-alemanha .servico-meta {
    color: #FFCE00;
}

.servico-preco {
    font-size: 1.2rem;
    font-weight: 700;
    color: #109349;
}

.theme-alemanha .servico-preco {
    color: #FFCE00;
}

.resumo {
    background: linear-gradient(135deg, #DD0101, #109349);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-top: 15px;
}

.theme-alemanha .resumo {
    background: linear-gradient(135deg, #000, #FFCE00);
}

.resumo-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.resumo-item:last-child {
    border: none;
    font-size: 1.1rem;
    font-weight: 700;
    padding-top: 12px;
}

.obs-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    border-radius: 8px;
    color: #856404;
}

.theme-alemanha .obs-box {
    background: #2a2a2a;
    border-left-color: #FFCE00;
    color: #FFCE00;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    margin-bottom: 10px;
}

.btn-primary {
    background: #DD0101;
    color: white;
}

.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .info-cards {
        grid-template-columns: 1fr;
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .theme-alemanha .info-cards {
        background: #000;
        border: 1px solid #333;
    }
    
    .info-card {
        box-shadow: none;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .theme-alemanha .info-card {
        border-bottom-color: #333;
        border: none;
    }
    
    .info-card:last-child {
        border-bottom: none;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .specs {
        grid-template-columns: 1fr;
    }
    
    .veiculo-info {
        flex-direction: column;
        text-align: center;
    }
    
    .servico-item {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>

<div class="detalhes-container">
    <div class="detalhes-header">
        <h1><i class="fas fa-calendar-check"></i> Agendamento #<?php echo $agendamento_id; ?></h1>
        <p>Detalhes do agendamento</p>
        <span class="status-badge status-<?php echo $agendamento['status']; ?>">
            <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
        </span>
    </div>

    <div class="info-cards">
        <div class="info-card">
            <i class="fas fa-calendar-alt"></i>
            <div class="label">Data</div>
            <div class="value"><?php echo formatarData($agendamento['data_agendamento'], 'd/m/Y'); ?></div>
        </div>
        <div class="info-card">
            <i class="fas fa-clock"></i>
            <div class="label">Horário</div>
            <div class="value"><?php echo substr($agendamento['hora_inicio'], 0, 5) . ' - ' . substr($agendamento['hora_fim'], 0, 5); ?></div>
        </div>
        <div class="info-card">
            <i class="fas fa-plus-circle"></i>
            <div class="label">Criado em</div>
            <div class="value"><?php echo formatarData($agendamento['data_criacao'], 'd/m/Y'); ?></div>
        </div>
    </div>

    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-car"></i> Veículo
                </div>
                <div class="veiculo-info">
                    <div class="veiculo-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div>
                        <h3><?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></h3>
                        <p>Ano: <?php echo $agendamento['ano']; ?></p>
                    </div>
                </div>
                <div class="specs">
                    <div class="spec-item">
                        <span class="spec-label">Placa</span>
                        <span class="spec-value"><?php echo $agendamento['placa']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Cor</span>
                        <span class="spec-value"><?php echo $agendamento['cor']; ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($agendamento['observacoes'])): ?>
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-comment-alt"></i> Observações
                </div>
                <div class="obs-box">
                    <?php echo nl2br(htmlspecialchars($agendamento['observacoes'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-title">
                    <i class="fas fa-tools"></i> Serviços (<?php echo $servicos->num_rows; ?>)
                </div>
                <?php if ($servicos->num_rows > 0): ?>
                    <?php 
                    $total = 0;
                    while ($servico = $servicos->fetch_assoc()): 
                        $total += $servico['preco'] * $servico['quantidade'];
                    ?>
                        <div class="servico-item">
                            <div class="servico-info">
                                <h4><?php echo $servico['nome']; ?></h4>
                                <p><?php echo $servico['descricao']; ?></p>
                                <div class="servico-meta">
                                    <span><i class="fas fa-times"></i> Qtd: <?php echo $servico['quantidade']; ?></span>
                                </div>
                            </div>
                            <div class="servico-preco">
                                <?php echo formatarMoeda($servico['preco']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="resumo">
                        <div class="resumo-item">
                            <span><i class="fas fa-calculator"></i> Valor Total</span>
                            <span><?php echo formatarMoeda($total); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">Nenhum serviço registrado</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-bolt"></i> Ações
                </div>
                <a href="agendamentos.php" class="action-btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <?php if ($agendamento['status'] == 'agendado'): ?>
                <a href="agendamento-cancelar.php?id=<?php echo $agendamento_id; ?>" class="action-btn btn-danger">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <?php endif; ?>
                <a href="#" onclick="window.print(); return false;" class="action-btn btn-secondary">
                    <i class="fas fa-print"></i> Imprimir
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

</body>
</html>
