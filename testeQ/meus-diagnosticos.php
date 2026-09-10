<?php
ob_start();
require_once 'header.php';
verificarLogin();
require_once 'config.php';

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Buscar diagnósticos atribuídos ao funcionário logado
$stmt = $conexao->prepare("
    SELECT r.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone
    FROM relatorios_cliente r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.mecanico_id = ?
    ORDER BY r.data_agendamento ASC, r.hora_agendamento ASC
");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$diagnosticos = $stmt->get_result();

$titulo = "Meus Diagnósticos";
?>

<style>
.diagnosticos-container {
    max-width: 1400px;
    margin: 0 auto;
}

.diagnostico-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-left: 5px solid #28a745;
    transition: all 0.3s;
}

.diagnostico-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.diagnostico-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.diagnostico-titulo {
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.diagnostico-badge {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-agendado {
    background: #fff3cd;
    color: #856404;
}

.badge-concluido {
    background: #d4edda;
    color: #155724;
}

.diagnostico-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #28a745;
    font-size: 1.2rem;
}

.info-content h4 {
    margin: 0;
    font-size: 0.85rem;
    color: #666;
    font-weight: 500;
}

.info-content p {
    margin: 5px 0 0 0;
    font-size: 1rem;
    color: #333;
    font-weight: 600;
}

.diagnostico-descricao {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.diagnostico-descricao h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1rem;
}

.diagnostico-descricao p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.empty-state i {
    font-size: 5rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #333;
}

/* Tema Alemanha */
.theme-alemanha .diagnostico-card {
    background: #1a1a1a;
    border-left-color: #FFCE00;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.2);
}

.theme-alemanha .diagnostico-titulo {
    color: white;
}

.theme-alemanha .diagnostico-header {
    border-bottom-color: #333;
}

.theme-alemanha .info-icon {
    background: #2a2a2a;
    color: #FFCE00;
}

.theme-alemanha .info-content h4 {
    color: #999;
}

.theme-alemanha .info-content p {
    color: white;
}

.theme-alemanha .diagnostico-descricao {
    background: #2a2a2a;
}

.theme-alemanha .diagnostico-descricao h4 {
    color: #FFCE00;
}

.theme-alemanha .diagnostico-descricao p {
    color: #ccc;
}

.theme-alemanha .empty-state {
    color: #999;
}

.theme-alemanha .empty-state h3 {
    color: white;
}

.theme-alemanha .empty-state i {
    color: #333;
}
</style>

<div class="diagnosticos-container">
    <?php if ($diagnosticos->num_rows > 0): ?>
        <?php while ($diag = $diagnosticos->fetch_assoc()): ?>
        <div class="diagnostico-card">
            <div class="diagnostico-header">
                <div class="diagnostico-titulo">
                    <i class="fas fa-clipboard-check"></i>
                    Diagnóstico #<?php echo str_pad($diag['id'], 5, '0', STR_PAD_LEFT); ?>
                </div>
                <span class="diagnostico-badge badge-agendado">
                    <i class="fas fa-calendar-alt"></i> Agendado
                </span>
            </div>
            
            <div class="diagnostico-info">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="info-content">
                        <h4>Cliente</h4>
                        <p><?php echo htmlspecialchars($diag['cliente_nome']); ?></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <h4>Data Agendada</h4>
                        <p><?php echo $diag['data_agendamento'] ? date('d/m/Y', strtotime($diag['data_agendamento'])) : 'Não definida'; ?></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>Horário</h4>
                        <p><?php echo $diag['hora_agendamento'] ?: 'Não definido'; ?></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h4>Contato</h4>
                        <p><?php echo htmlspecialchars($diag['cliente_telefone'] ?: $diag['cliente_email']); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if ($diag['descricao_problema']): ?>
            <div class="diagnostico-descricao">
                <h4><i class="fas fa-exclamation-circle"></i> Descrição do Problema</h4>
                <p><?php echo nl2br(htmlspecialchars($diag['descricao_problema'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>Nenhum diagnóstico atribuído</h3>
            <p>Você não possui diagnósticos agendados no momento.</p>
        </div>
    <?php endif; ?>
</div>

<script src="theme-controller.js"></script>

<?php 
include 'footer.php';
ob_end_flush();
?>
