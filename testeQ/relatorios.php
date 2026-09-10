<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'config.php';
verificarLogin();

// Define o tÃ­tulo da pÃ¡gina baseado no tipo de usuÃ¡rio
if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0) {
    // Redirecionar mecÃ¢nicos para seu dashboard especÃ­fico
    header("Location: mecanico-dashboard.php");
    exit;
}

// Inclui o cabeÃ§alho
require_once 'header.php';

$conexao = conectarBD();

// Verificar se tabela existe
if (!tabelaExiste($conexao, 'relatorios_cliente')) {
    $relatorios = [];
} else {
    // Buscar relatorios do usuario com respostas dos mecanicos (excluindo cancelados)
        $stmt = $conexao->prepare("
    SELECT DISTINCT rc.*, 'Nao atribuido' as analista_nome, v.marca, v.modelo, v.placa,
           NULL as diagnostico, NULL as resposta_status, NULL as data_proposta, 
           NULL as observacoes_data, NULL as status_agendamento,
           NULL as diagnostico_inicial, NULL as pecas_necessarias, NULL as tempo_estimado,
           NULL as custo_estimado, NULL as prioridade, NULL as resposta_observacoes,
           NULL as data_resposta, NULL as mecanico_nome,
           ag.id as agendamento_id, ag.data_agendamento, ag.hora_inicio, ag.status as agendamento_status
    FROM relatorios_cliente rc
    
    LEFT JOIN veiculos v ON rc.veiculo_id = v.id
    
    
    
    LEFT JOIN agendamentos ag ON ag.veiculo_id = rc.veiculo_id 
        AND ag.usuario_id = rc.usuario_id 
        AND ag.status IN ('agendado', 'confirmado')
    WHERE rc.usuario_id = ? AND (rc.status IS NULL OR rc.status != 'cancelado')
    ORDER BY rc.data_envio DESC
");

    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conexao->close();
?>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Meus RelatÃ³rios</div>
    <div class="welcome-message">
        <h2><i class="fas fa-file-medical-alt"></i> Meus RelatÃ³rios</h2>
        <p>Acompanhe o status dos seus diagnÃ³sticos e relatÃ³rios de manutenÃ§Ã£o.</p>
    </div>
    <div class="welcome-actions">
        <a href="agendamento-diagnostico.php" class="btn" data-tooltip="Solicitar novo diagnÃ³stico">
            <i class="fas fa-plus"></i> Novo DiagnÃ³stico
        </a>
    </div>
</div>

<style>
.dashboard-welcome {
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.theme-alemanha .dashboard-welcome {
    background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
    color: white;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.welcome-message p {
    opacity: 0.8;
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
    font-weight: 500;
}

.welcome-actions .btn:hover {
    background-color: #0d7a3a;
    transform: translateY(-1px);
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.theme-alemanha .container {
    background: #0d0d0d;
    border-radius: 10px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.theme-alemanha .card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 8px 25px rgba(255, 206, 0, 0.2);
    border: 1px solid rgba(255, 206, 0, 0.3);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.12);
}

.theme-alemanha .card:hover {
    box-shadow: 0 12px 35px rgba(255, 206, 0, 0.3);
}

.card-header {
    background: linear-gradient(135deg, #CE2A37, #a91e2a);
    color: white;
    padding: 20px;
    border-bottom: none;
}

.theme-alemanha .card-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.card-body {
    padding: 25px;
}

.theme-alemanha .card-body {
    background: #1a1a1a;
    color: white;
}
        
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: #109349;
    color: white;
}

.btn-primary:hover {
    background: #0d7a3a;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-success {
    background: #109349;
    color: white;
}

.btn-warning {
    background: #f39c12;
    color: white;
}


        
.status-badge {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-pendente {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
    border: 1px solid rgba(243, 156, 18, 0.2);
}

.status-analisado {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    border: 1px solid rgba(52, 152, 219, 0.2);
}

.status-respondido {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
    border: 1px solid rgba(16, 147, 73, 0.2);
}
        
.relatorio-item {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.theme-alemanha .relatorio-item {
    background: #1a1a1a;
    border: 1px solid rgba(255, 206, 0, 0.3);
    color: white;
}

.relatorio-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.theme-alemanha .relatorio-item:hover {
    box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3);
}

.relatorio-header {
    background: #f8f9fa;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.theme-alemanha .relatorio-header {
    background: #2a2a2a;
    border-bottom: 1px solid rgba(255, 206, 0, 0.3);
}

.theme-alemanha .relatorio-header strong {
    color: #FFCE00;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.theme-alemanha .relatorio-header span {
    color: #fff !important;
}

.theme-alemanha .relatorio-header small {
    color: #ddd !important;
}

.theme-alemanha .relatorio-header div {
    color: #fff;
}

.theme-alemanha .relatorio-header div strong {
    color: #FFCE00 !important;
    font-weight: 700;
}

.theme-alemanha .relatorio-header .urgencia-badge {
    background: rgba(255, 206, 0, 0.2) !important;
    color: #FFCE00 !important;
    border: 1px solid #FFCE00 !important;
}

.theme-alemanha .relatorio-header .urgencia-alta {
    background: rgba(221, 1, 0, 0.2) !important;
    color: #DD0100 !important;
    border: 1px solid #DD0100 !important;
}

.relatorio-body {
    padding: 25px;
}

.theme-alemanha .relatorio-body {
    background: #1a1a1a;
}

.theme-alemanha .relatorio-body h4 {
    color: #FFCE00;
}

.theme-alemanha .relatorio-body h5 {
    color: #FFCE00;
}

.theme-alemanha .relatorio-body h6 {
    color: #FFCE00;
}

.theme-alemanha .relatorio-body p {
    color: #ccc;
}

.theme-alemanha .relatorio-body small {
    color: #aaa;
}

.theme-alemanha .relatorio-body strong {
    color: #fff;
}

.theme-alemanha .relatorio-body div[style*="background: white"] {
    background: #2a2a2a !important;
    color: white;
}

.theme-alemanha .relatorio-body div[style*="background: #fff3e0"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #DD0100 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #e3f2fd"] {
    background: linear-gradient(135deg, #1a1a2e, #16213e) !important;
    color: white;
    border: 1px solid rgba(33, 150, 243, 0.3) !important;
}

.theme-alemanha .relatorio-body div[style*="background: #e8f5e8"] {
    background: linear-gradient(135deg, #1a2e1a, #1e3a1e) !important;
    color: white;
    border: 1px solid rgba(76, 175, 80, 0.3) !important;
}

.theme-alemanha .relatorio-body div[style*="background: #f5f5f5"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #FFCE00 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #f8f9fa"] {
    background: #2a2a2a !important;
    color: white;
}

.theme-alemanha .relatorio-body div[style*="background: #d4edda"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #FFCE00 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #fff3cd"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #DD0100 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #f8d7da"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #DD0100 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #d1ecf1"] {
    background: #2a2a2a !important;
    color: white;
    border: 2px solid #FFCE00 !important;
}

.theme-alemanha .relatorio-body div[style*="background: linear-gradient"] {
    background: #2a2a2a !important;
    color: white;
    border-left: 4px solid #DD0100 !important;
}

.theme-alemanha .relatorio-body div[style*="background: #ffebee"] {
    background: linear-gradient(135deg, #2e1a1a, #3a1e1e) !important;
    color: white;
    border: 1px solid rgba(244, 67, 54, 0.3) !important;
}

.theme-alemanha .relatorio-body div[style*="background: #fff3e0"] {
    background: linear-gradient(135deg, #2e1a1a, #3a1e1e) !important;
    color: white;
    border: 1px solid rgba(255, 152, 0, 0.3) !important;
}

.theme-alemanha .relatorio-body div[style*="background: #fff8e1"] {
    background: linear-gradient(135deg, #2e1a1a, #3a1e1e) !important;
    color: white;
    border: 1px solid rgba(255, 193, 7, 0.3) !important;
}

.theme-alemanha .relatorio-body div[style*="background: #f3e5f5"] {
    background: linear-gradient(135deg, #2e1a1a, #3a1e1e) !important;
    color: white;
    border: 1px solid rgba(156, 39, 176, 0.3) !important;
}
        
.veiculo-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    border-left: 4px solid #109349;
}

.theme-alemanha .veiculo-info {
    background: #2a2a2a;
    color: white;
    border-left: 4px solid #FFCE00;
}

.problema-info {
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    border-left: 4px solid #f39c12;
}

.theme-alemanha .problema-info {
    background: #2a2a2a;
    color: white;
    border-left: 4px solid #DD0100;
}

.diagnostico-info {
    background: #d4edda;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
    border-left: 4px solid #109349;
}

.theme-alemanha .diagnostico-info {
    background: #2a2a2a;
    color: white;
    border-left: 4px solid #FFCE00;
}
        
.urgencia-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.urgencia-baixa {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
    border: 1px solid rgba(16, 147, 73, 0.2);
}
.theme-alemanha .urgencia-media{
   background: rgba(243, 157, 18, 1);
   color: #674001ff;
}

.urgencia-media {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
    border: 1px solid rgba(243, 156, 18, 0.2);
}


.urgencia-alta {
    background: rgba(221, 1, 0, 0.1);
    color: #DD0100;
    border: 1px solid rgba(221, 1, 0, 0.2);
}
        
.servico-item {
    background: #f8f9fa;
    padding: 12px;
    margin: 8px 0;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    border-left: 3px solid #109349;
}
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    
/* Estilos para tema alemanha - status dos relatÃ³rios */
body.theme-alemanha .status-realizado {
    background: #2a2a2a !important;
    border: 2px solid #FFCE00 !important;
}

body.theme-alemanha .status-realizado p {
    color: #FFCE00 !important;
}

body.theme-alemanha .status-realizado p:last-child {
    color: #cccccc !important;
}

body.theme-alemanha .status-rejeitado {
    background: #2a2a2a !important;
    border: 2px solid #DD0100 !important;
}

body.theme-alemanha .status-rejeitado p {
    color: #DD0100 !important;
}

body.theme-alemanha .status-solicitou-mudanca {
    background: #2a2a2a !important;
    border: 2px solid #FFCE00 !important;
}

body.theme-alemanha .status-solicitou-mudanca p {
    color: #FFCE00 !important;
}

body.theme-alemanha .status-cancelado {
    background: #2a2a2a !important;
    border: 2px solid #DD0100 !important;
}

body.theme-alemanha .status-cancelado p {
    color: #DD0100 !important;
}

body.theme-alemanha .status-nao-cancelavel {
    background: #2a2a2a !important;
    border: 2px solid #DD0100 !important;
}

body.theme-alemanha .status-nao-cancelavel small {
    color: #DD0100 !important;
}

body.theme-alemanha .status-aguardando {
    background: #2a2a2a !important;
    border-left: 4px solid #FFCE00 !important;
}

body.theme-alemanha .status-aguardando h4 {
    color: #FFCE00 !important;
}

body.theme-alemanha .status-aguardando p {
    color: #cccccc !important;
}

body.theme-alemanha .status-aceito {
    background: #2a2a2a !important;
    border: 2px solid #FFCE00 !important;
}

body.theme-alemanha .status-aceito p {
    color: #FFCE00 !important;
}

/* Estilos para modal de cancelamento - Tema ItÃ¡lia (padrÃ£o) */
.modal-cancelar .modal-content {
    background: white;
    color: #333;
}

.modal-title-cancelar {
    color: #dc3545;
}

.modal-warning {
    color: #dc3545;
}

.btn-cancelar-confirmar {
    background: #dc3545;
    transition: all 0.3s;
}

.btn-cancelar-confirmar:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
}

/* Estilos para modal de cancelamento - Tema Alemanha */
.theme-alemanha .modal-cancelar .modal-content {
    background: #1a1a1a !important;
    color: white !important;
    border: 2px solid #FFCE00;
    box-shadow: 0 10px 30px rgba(255, 206, 0, 0.3) !important;
}

.theme-alemanha .modal-title-cancelar {
    color: #DD0100 !important;
}

.theme-alemanha .modal-text {
    color: #ccc !important;
}

.theme-alemanha .modal-text strong {
    color: #FFCE00 !important;
}

.theme-alemanha .modal-warning {
    color: #DD0100 !important;
}

.theme-alemanha .modal-cancelar .btn-secondary {
    background: #2a2a2a !important;
    color: #FFCE00 !important;
    border: 1px solid #FFCE00 !important;
}

.theme-alemanha .modal-cancelar .btn-secondary:hover {
    background: #FFCE00 !important;
    color: #000 !important;
    transform: translateY(-2px);
}

.theme-alemanha .btn-cancelar-confirmar {
    background: #DD0100 !important;
}

.theme-alemanha .btn-cancelar-confirmar:hover {
    background: #a00 !important;
    box-shadow: 0 5px 15px rgba(221, 1, 0, 0.4) !important;
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
    
    .container {
        padding: 15px;
    }
    
    .card-body, .relatorio-body {
        padding: 20px;
    }
}
</style>

<div class="container">
        
        <?php mostrarAlerta(); ?>
        
        <?php if (empty($relatorios)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 40px;">
                    <i class="fas fa-stethoscope" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                    <h3 style="color: #666; margin-bottom: 15px;">Nenhum diagnÃ³stico encontrado</h3>
                    <p style="color: #999; margin-bottom: 25px;">VocÃª ainda nÃ£o solicitou nenhum diagnÃ³stico para seus veÃ­culos.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($relatorios as $relatorio): ?>
                <div class="relatorio-item" data-relatorio-id="<?php echo $relatorio['id']; ?>">
                    <div class="relatorio-header">
                        <div>
                            <strong style="font-size: 1.1rem; color: var(--secondary-color);">
                                <i class="fas fa-car"></i> <?php echo $relatorio['marca'] . ' ' . $relatorio['modelo']; ?>
                            </strong>
                            <span style="color: #666; margin-left: 10px;">(<?php echo $relatorio['placa']; ?>)</span>
                            <?php if (isset($relatorio['urgencia']) && $relatorio['urgencia']): ?>
                                <span class="urgencia-badge urgencia-<?php echo $relatorio['urgencia']; ?>">
                                    <?php echo ucfirst($relatorio['urgencia']); ?>
                                </span>
                            <?php endif; ?>
                            <br>
                            <small style="color: #666;">
                                <i class="fas fa-user-md"></i> Analista: <?php echo $relatorio['analista_nome'] ?: 'NÃ£o atribuÃ­do'; ?>
                            </small>
                        </div>
                        <div>
                            
                                <?php 
                                switch($relatorio['status']) {
                                    case 'pendente': echo 'Aguardando AnÃ¡lise'; break;
                                    case 'analisado': echo 'Analisado'; break;
                                    case 'respondido': echo 'Respondido'; break;
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="relatorio-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <p style="margin: 0;"><strong><i class="fas fa-calendar"></i> Data do Envio:</strong> <?php echo formatarData($relatorio['data_envio']); ?></p>
                            <button onclick="baixarPDF(<?php echo $relatorio['id']; ?>)" class="btn btn-primary" style="padding: 8px 16px;">
                                <i class="fas fa-file-pdf"></i> Baixar PDF
                            </button>
                            <?php if (isset($relatorio['urgencia']) && $relatorio['urgencia']): ?>
                                <div style="text-align: right;">
                                    <small style="color: #666;">NÃ­vel de UrgÃªncia:</small><br>
                                    <span class="urgencia-badge urgencia-<?php echo $relatorio['urgencia']; ?>">
                                        <i class="fas fa-<?php echo $relatorio['urgencia'] == 'alta' ? 'exclamation-triangle' : ($relatorio['urgencia'] == 'media' ? 'clock' : 'check-circle'); ?>"></i>
                                        <?php echo ucfirst($relatorio['urgencia']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($relatorio['descricao_problema']): ?>
                            <div class="problema-info">
                                <h4 style="margin-bottom: 10px; color: var(--warning-color);"><i class="fas fa-exclamation-triangle"></i> Problema Relatado</h4>
                                <p style="margin: 0;"><?php echo $relatorio['descricao_problema']; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="veiculo-info">
                                <h4 style="margin-bottom: 10px; color: var(--primary-color);"><i class="fas fa-tools"></i> Tipo de ServiÃ§o</h4>
                                <p style="margin: 0;">RevisÃ£o Geral - DiagnÃ³stico completo do veÃ­culo</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($relatorio['diagnostico_inicial']): ?>
                            <div class="diagnostico-info">
                                <h4 style="margin-bottom: 15px; color: var(--success-color);">
                                    <i class="fas fa-user-md"></i> Resposta do MecÃ¢nico: <?php echo $relatorio['mecanico_nome']; ?>
                                </h4>
                                
                                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #109349;">
                                    <h5 style="color: #109349; margin-bottom: 10px;"><i class="fas fa-stethoscope"></i> DiagnÃ³stico</h5>
                                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($relatorio['diagnostico_inicial'])); ?></p>
                                </div>
                                
                                <?php if ($relatorio['pecas_necessarias']): ?>
                                <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ff9800;">
                                    <h5 style="color: #ff9800; margin-bottom: 10px;"><i class="fas fa-cogs"></i> PeÃ§as NecessÃ¡rias</h5>
                                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($relatorio['pecas_necessarias'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                                    <?php if ($relatorio['tempo_estimado']): ?>
                                    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; text-align: center;">
                                        <i class="fas fa-clock" style="font-size: 24px; color: #2196f3; margin-bottom: 8px;"></i>
                                        <h6 style="margin: 0; color: #2196f3;">Tempo Estimado</h6>
                                        <p style="margin: 5px 0 0 0; font-weight: bold;"><?php echo $relatorio['tempo_estimado']; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($relatorio['custo_estimado']): ?>
                                    <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; text-align: center;">
                                        <i class="fas fa-dollar-sign" style="font-size: 24px; color: #4caf50; margin-bottom: 8px;"></i>
                                        <h6 style="margin: 0; color: #4caf50;">Custo Estimado</h6>
                                        <p style="margin: 5px 0 0 0; font-weight: bold;"><?php echo $relatorio['custo_estimado']; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div style="background: <?php 
                                        switch($relatorio['prioridade']) {
                                            case 'critica': echo '#ffebee'; break;
                                            case 'alta': echo '#fff3e0'; break;
                                            case 'media': echo '#fff8e1'; break;
                                            default: echo '#f3e5f5';
                                        }
                                    ?>; padding: 15px; border-radius: 8px; text-align: center;">
                                        <i class="fas fa-flag" style="font-size: 24px; color: <?php 
                                            switch($relatorio['prioridade']) {
                                                case 'critica': echo '#f44336'; break;
                                                case 'alta': echo '#ff9800'; break;
                                                case 'media': echo '#ffc107'; break;
                                                default: echo '#9c27b0';
                                            }
                                        ?>; margin-bottom: 8px;"></i>
                                        <h6 style="margin: 0; color: <?php 
                                            switch($relatorio['prioridade']) {
                                                case 'critica': echo '#f44336'; break;
                                                case 'alta': echo '#ff9800'; break;
                                                case 'media': echo '#ffc107'; break;
                                                default: echo '#9c27b0';
                                            }
                                        ?>;">Prioridade</h6>
                                        <p style="margin: 5px 0 0 0; font-weight: bold; text-transform: capitalize;"><?php echo $relatorio['prioridade']; ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($relatorio['resposta_observacoes']): ?>
                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #607d8b;">
                                    <h5 style="color: #607d8b; margin-bottom: 10px;"><i class="fas fa-comment"></i> ObservaÃ§Ãµes do MecÃ¢nico</h5>
                                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($relatorio['resposta_observacoes'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div style="text-align: right; margin-top: 15px;">
                                    <small style="color: #666;">
                                        <i class="fas fa-calendar"></i> Respondido em: <?php echo formatarData($relatorio['data_resposta'], 'd/m/Y H:i'); ?>
                                    </small>
                                </div>
                                
                                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #109349;">
                                    <h5 style="margin-bottom: 15px; color: #109349;">PrÃ³ximos Passos</h5>
                                    <?php if ($relatorio['agendamento_id']): ?>
                                        <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #28a745;">
                                            <p style="margin: 0 0 10px 0; color: #155724; font-weight: 600;">
                                                <i class="fas fa-check-circle"></i> ServiÃ§o Agendado
                                            </p>
                                            <p style="margin: 0; color: #155724;">
                                                <i class="fas fa-calendar"></i> Data: <?php echo date('d/m/Y', strtotime($relatorio['data_agendamento'])); ?> Ã s <?php echo substr($relatorio['hora_inicio'], 0, 5); ?>
                                            </p>
                                        </div>
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <button onclick="cancelarAgendamento(<?php echo $relatorio['agendamento_id']; ?>)" class="btn" style="background: #dc3545; color: white;">
                                                <i class="fas fa-times"></i> Cancelar Agendamento
                                            </button>
                                            <button onclick="reagendarServico(<?php echo $relatorio['id']; ?>, <?php echo $relatorio['agendamento_id']; ?>)" class="btn btn-warning">
                                                <i class="fas fa-calendar-alt"></i> Reagendar
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <button onclick="agendarServico(<?php echo $relatorio['id']; ?>)" class="btn btn-primary">
                                                <i class="fas fa-calendar-plus"></i> Agendar ServiÃ§o
                                            </button>
                                            <a href="solicitar-orcamento.php?diagnostico_id=<?php echo $relatorio['id']; ?>" class="btn btn-warning" style="text-decoration: none;">
                                                <i class="fas fa-calculator"></i> Solicitar OrÃ§amento Detalhado
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($relatorio['diagnostico']): ?>
                            <div class="diagnostico-info">
                                <h4 style="margin-bottom: 15px; color: var(--success-color);">
                                    <i class="fas fa-clipboard-check"></i> DiagnÃ³stico do Analista
                                </h4>
                                <p style="font-size: 1.05rem; line-height: 1.6; margin-bottom: 15px;"><?php echo $relatorio['diagnostico']; ?></p>
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <small style="color: #666; display: flex; align-items: center;">
                                        <i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 5px;"></i>
                                        DiagnÃ³stico gratuito realizado
                                    </small>
                                    <small style="color: #666;">
                                        <i class="fas fa-clock"></i> <?php echo formatarData($relatorio['data_envio']); ?>
                                    </small>
                                </div>
                                
                                <?php if ($relatorio['status'] == 'analisado'): ?>
                                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid var(--primary-color);">
                                        <h5 style="margin-bottom: 10px; color: var(--primary-color);">O que vocÃª deseja fazer?</h5>
                                        <button onclick="abrirModalAceitar(<?php echo $relatorio['id']; ?>)" class="btn btn-success" style="margin-right: 10px;">
                                            <i class="fas fa-thumbs-up"></i> Aceitar ServiÃ§o
                                        </button>
                                        <button onclick="abrirModalHorario(<?php echo $relatorio['id']; ?>)" class="btn btn-warning" style="margin-right: 10px;">
                                            <i class="fas fa-clock"></i> Solicitar Outro HorÃ¡rio
                                        </button>
                                        <button onclick="abrirModalRejeitar(<?php echo $relatorio['id']; ?>)" class="btn" style="background-color: #6c757d; color: white;">
                                            <i class="fas fa-times"></i> NÃ£o Tenho Interesse
                                        </button>
                                    </div>
                                <?php elseif ($relatorio['status'] == 'aceito'): ?>
                                    <div class="status-aceito" style="margin-top: 15px; padding: 15px; background: #d4edda; border-radius: 5px;">
                                        <p style="color: var(--success-color); margin: 0;"><strong><i class="fas fa-check-circle"></i> ServiÃ§o aceito!</strong> Aguarde contato para agendamento.</p>
                                    </div>
                                <?php elseif ($relatorio['status'] == 'realizado'): ?>
                                    <div class="status-realizado" style="margin-top: 15px; padding: 15px; background: #d1ecf1; border-radius: 5px; border: 2px solid var(--success-color);">
                                        <p style="color: var(--success-color); margin: 0; font-size: 1.1rem;"><strong><i class="fas fa-trophy"></i> ServiÃ§o realizado com sucesso!</strong></p>
                                        <p style="margin: 5px 0 0 0; color: #666;">Obrigado por confiar em nossos serviÃ§os.</p>
                                    </div>
                                <?php elseif ($relatorio['status'] == 'rejeitado'): ?>
                                    <div class="status-rejeitado" style="margin-top: 15px; padding: 15px; background: #f8d7da; border-radius: 5px;">
                                        <p style="color: var(--error-color); margin: 0;"><strong><i class="fas fa-times-circle"></i> ServiÃ§o recusado.</strong> Obrigado pelo seu tempo.</p>
                                    </div>
                                <?php elseif ($relatorio['status'] == 'solicitou_mudanca'): ?>
                                    <div class="status-solicitou-mudanca" style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 5px;">
                                        <p style="color: var(--warning-color); margin: 0;"><strong><i class="fas fa-clock"></i> SolicitaÃ§Ã£o de mudanÃ§a de horÃ¡rio enviada.</strong> O analista entrarÃ¡ em contato.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($relatorio['data_proposta']): ?>
                                <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); padding: 20px; border-radius: 8px; margin: 15px 0; border-left: 4px solid var(--warning-color);">
                                    <h4 style="margin-bottom: 15px; color: var(--warning-color);">
                                        <i class="fas fa-calendar-alt"></i> Proposta de Atendimento
                                    </h4>
                                    <p><strong>Data e Hora:</strong> <?php echo formatarData($relatorio['data_proposta']); ?></p>
                                    <?php if ($relatorio['observacoes_data']): ?>
                                        <p><strong>ObservaÃ§Ãµes:</strong> <?php echo $relatorio['observacoes_data']; ?></p>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top: 15px;">
                                        <?php if ($relatorio['status_agendamento'] == 'pendente'): ?>
                                            <p style="color: #856404; margin-bottom: 10px;"><strong>Status:</strong> Aguardando sua confirmaÃ§Ã£o</p>
                                            <button onclick="confirmarHorario(<?php echo $relatorio['id']; ?>)" class="btn btn-success" style="margin-right: 10px;">
                                                <i class="fas fa-check"></i> Confirmar HorÃ¡rio
                                            </button>
                                            <button onclick="abrirModalHorario(<?php echo $relatorio['id']; ?>)" class="btn btn-warning">
                                                <i class="fas fa-times"></i> Solicitar Nova Data
                                            </button>
                                        <?php elseif ($relatorio['status_agendamento'] == 'confirmado'): ?>
                                            <p style="color: var(--success-color);"><strong><i class="fas fa-check-circle"></i> HorÃ¡rio confirmado!</strong> Aguarde o atendimento na data marcada.</p>
                                        <?php elseif ($relatorio['status_agendamento'] == 'rejeitado'): ?>
                                            <p style="color: var(--error-color);"><strong><i class="fas fa-clock"></i> SolicitaÃ§Ã£o de nova data enviada.</strong> O analista entrarÃ¡ em contato.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="status-aguardando" style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                                <h4 style="margin-bottom: 10px; color: var(--warning-color);">
                                    <i class="fas fa-hourglass-half"></i> Aguardando DiagnÃ³stico
                                </h4>
                                <p style="margin: 0; color: #856404;">Seu relatÃ³rio foi enviado e estÃ¡ sendo analisado pelo nosso analista. VocÃª receberÃ¡ o diagnÃ³stico gratuito em breve.</p>
                                
                                <?php 
                                // Verificar se pode cancelar (2 dias antes da data proposta ou se ainda nÃ£o tem data)
                                $pode_cancelar = true;
                                if ($relatorio['data_proposta']) {
                                    $data_proposta = new DateTime($relatorio['data_proposta']);
                                    $agora = new DateTime();
                                    $diferenca = $agora->diff($data_proposta);
                                    $dias_restantes = $diferenca->days;
                                    
                                    if ($diferenca->invert == 0 && $dias_restantes >= 2) {
                                        $pode_cancelar = true;
                                    } else {
                                        $pode_cancelar = false;
                                    }
                                }
                                ?>
                                
                                <?php if ($pode_cancelar && $relatorio['status'] != 'cancelado'): ?>
                                    <div style="margin-top: 15px;">
                                        <button onclick="editarDiagnostico(<?php echo $relatorio['id']; ?>)" class="btn btn-warning" style="margin-right: 10px;">
                                            <i class="fas fa-edit"></i> Editar DiagnÃ³stico
                                        </button>
                                        <button onclick="cancelarDiagnostico(<?php echo $relatorio['id']; ?>)" class="btn" style="background-color: var(--error-color); color: white;">
                                            <i class="fas fa-times"></i> Cancelar DiagnÃ³stico
                                        </button>
                                    </div>
                                <?php elseif ($relatorio['status'] == 'cancelado'): ?>
                                    <div class="status-cancelado" style="margin-top: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                                        <p style="color: #721c24; margin: 0;">
                                            <i class="fas fa-times-circle"></i> <strong>DiagnÃ³stico cancelado</strong>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="status-nao-cancelavel" style="margin-top: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                                        <small style="color: #721c24;">
                                            <i class="fas fa-info-circle"></i> NÃ£o Ã© possÃ­vel cancelar (menos de 2 dias para o atendimento)
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
    
    <!-- Modais -->
    <div id="modalAceitar" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px;">
            <h3 style="color: var(--success-color);"><i class="fas fa-thumbs-up"></i> Aceitar ServiÃ§o</h3>
            <p>VocÃª confirma que deseja prosseguir com o serviÃ§o baseado no diagnÃ³stico recebido?</p>
            <p><strong>O analista entrarÃ¡ em contato para agendar o atendimento.</strong></p>
            <div style="text-align: right; margin-top: 20px;">
                <button onclick="fecharModal('modalAceitar')" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">Cancelar</button>
                <button onclick="confirmarAcao('aceitar')" class="btn btn-success">Confirmar AceitaÃ§Ã£o</button>
            </div>
        </div>
    </div>
    
    <div id="modalHorario" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 600px;">
            <h3 style="color: var(--warning-color);"><i class="fas fa-clock"></i> Solicitar Outro HorÃ¡rio</h3>
            <p>Informe sua preferÃªncia de data e horÃ¡rio:</p>
            
            <form id="formNovoHorario">
                <div style="margin: 15px 0;">
                    <label>Data e Hora Preferida *</label>
                    <input type="datetime-local" id="nova_data" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin: 15px 0;">
                    <label>ObservaÃ§Ãµes (opcional)</label>
                    <textarea id="observacoes_cliente" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px;" placeholder="Informe qualquer observaÃ§Ã£o sobre sua disponibilidade..."></textarea>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="fecharModal('modalHorario')" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">Cancelar</button>
                    <button type="button" onclick="enviarNovoHorario()" class="btn btn-warning">Enviar SolicitaÃ§Ã£o</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modalRejeitar" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px;">
            <h3 style="color: var(--error-color);"><i class="fas fa-times"></i> NÃ£o Tenho Interesse</h3>
            <p>VocÃª confirma que nÃ£o deseja prosseguir com o serviÃ§o?</p>
            <p><strong>Esta aÃ§Ã£o encerrarÃ¡ o processo de diagnÃ³stico.</strong></p>
            <div style="text-align: right; margin-top: 20px;">
                <button onclick="fecharModal('modalRejeitar')" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">Cancelar</button>
                <button onclick="confirmarAcao('rejeitar')" style="background: var(--error-color); color: white; padding: 10px 20px; border: none; border-radius: 5px;">Confirmar RejeiÃ§Ã£o</button>
            </div>
        </div>
    </div>
    
    <div id="modalCancelar" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 10% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <h3 style="color: #DD0100; margin-bottom: 15px;"><i class="fas fa-trash-alt"></i> Excluir DiagnÃ³stico</h3>
            <p style="margin-bottom: 10px; line-height: 1.5; font-size: 1.05rem;">Tem certeza que deseja <strong>excluir permanentemente</strong> este diagnÃ³stico?</p>
            <p style="color: #DD0100; font-weight: 600; margin-bottom: 20px; font-size: 1.1rem;"><i class="fas fa-exclamation-triangle"></i> Esta aÃ§Ã£o nÃ£o pode ser desfeita!</p>
            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #f39c12;">
                <small style="color: #856404;">
                    <i class="fas fa-info-circle"></i> 
                    O diagnÃ³stico serÃ¡ removido permanentemente do sistema.
                </small>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button onclick="fecharModal('modalCancelar')" class="btn btn-secondary" style="margin-right: 10px;">Cancelar</button>
                <button onclick="confirmarExclusao()" style="background: #DD0100; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    <i class="fas fa-trash"></i> Sim, Excluir
                </button>
            </div>
        </div>
    </div>
    
    <div id="modalCancelarAgendamento" class="modal-cancelar" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <h3 class="modal-title-cancelar" style="color: #dc3545; margin-bottom: 15px;"><i class="fas fa-exclamation-triangle"></i> Cancelar Agendamento</h3>
            <p class="modal-text" style="margin-bottom: 10px; line-height: 1.5; font-size: 1.05rem;">Tem certeza que deseja <strong>cancelar</strong> este agendamento?</p>
            <p class="modal-warning" style="color: #dc3545; font-weight: 600; margin-bottom: 20px;"><i class="fas fa-info-circle"></i> O horÃ¡rio ficarÃ¡ disponÃ­vel novamente.</p>
            <input type="hidden" id="agendamentoIdCancelar">
            <div style="text-align: right; margin-top: 20px;">
                <button onclick="fecharModal('modalCancelarAgendamento')" class="btn btn-secondary" style="margin-right: 10px;">NÃ£o, Manter</button>
                <button onclick="confirmarCancelamentoAgendamento()" class="btn-cancelar-confirmar" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    <i class="fas fa-times"></i> Sim, Cancelar
                </button>
            </div>
        </div>
    </div>
    
    <div id="modalAgendamento" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
        <div style="background-color: white; margin: 3% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 700px;">
            <h3 style="color: #109349; margin-bottom: 15px;"><i class="fas fa-calendar-plus"></i> Agendar ServiÃ§o</h3>
            <p style="margin-bottom: 20px;">Selecione a data e horÃ¡rio para realizar o serviÃ§o:</p>
            
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <button onclick="navegarMesAgendamento(-1)" class="btn btn-secondary" style="padding: 8px 15px;">â† Anterior</button>
                    <h4 id="mesAtualAgendamento" style="margin: 0;"></h4>
                    <button onclick="navegarMesAgendamento(1)" class="btn btn-secondary" style="padding: 8px 15px;">PrÃ³ximo â†’</button>
                </div>
                
                <div id="calendarioAgendamento" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 20px;">
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Dom</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Seg</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Ter</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Qua</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Qui</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">Sex</div>
                    <div style="text-align: center; font-weight: bold; padding: 10px; background: #f8f9fa;">SÃ¡b</div>
                </div>
            </div>
            
            <div id="horariosAgendamento" style="display: none; margin-bottom: 20px;">
                <h5 style="margin-bottom: 10px;">HorÃ¡rios DisponÃ­veis:</h5>
                <div id="horariosGridAgendamento" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;"></div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ObservaÃ§Ãµes (opcional):</label>
                <textarea id="observacoesAgendamento" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px; box-sizing: border-box;" placeholder="Alguma observaÃ§Ã£o sobre o agendamento..."></textarea>
            </div>
            
            <input type="hidden" id="dataAgendamentoSelecionada">
            <input type="hidden" id="horaAgendamentoSelecionada">
            
            <div style="text-align: right; margin-top: 20px;">
                <button onclick="fecharModal('modalAgendamento')" class="btn btn-secondary" style="margin-right: 10px;">Cancelar</button>
                <button onclick="confirmarAgendamento()" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmar Agendamento
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let relatorioAtual = 0;
        
        function abrirModalAceitar(relatorioId) {
            relatorioAtual = relatorioId;
            document.getElementById('modalAceitar').style.display = 'block';
        }
        
        function abrirModalHorario(relatorioId) {
            relatorioAtual = relatorioId;
            document.getElementById('modalHorario').style.display = 'block';
        }
        
        function abrirModalRejeitar(relatorioId) {
            relatorioAtual = relatorioId;
            document.getElementById('modalRejeitar').style.display = 'block';
        }
        
        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function confirmarAcao(acao) {
            window.location.href = 'processar-acao-cliente.php?acao=' + acao + '&id=' + relatorioAtual;
        }
        
        function marcarRealizado(relatorioId) {
            if (confirm('Marcar este serviÃ§o como realizado?')) {
                window.location.href = 'processar-acao-cliente.php?acao=realizado&id=' + relatorioId;
            }
        }
        
        function cancelarDiagnostico(relatorioId) {
            relatorioAtual = relatorioId;
            document.getElementById('modalCancelar').style.display = 'block';
        }
        
        function confirmarExclusao() {
            fetch('processar-acao-cliente.php?acao=excluir&id=' + relatorioAtual)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const relatorioElement = document.querySelector(`[data-relatorio-id="${relatorioAtual}"]`);
                        if (relatorioElement) {
                            relatorioElement.style.transition = 'all 0.3s ease';
                            relatorioElement.style.opacity = '0';
                            relatorioElement.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                relatorioElement.remove();
                                const relatorios = document.querySelectorAll('.relatorio-item');
                                if (relatorios.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                        fecharModal('modalCancelar');
                    } else {
                        alert('Erro ao excluir diagnÃ³stico: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro ao excluir diagnÃ³stico');
                    console.error(error);
                });
        }
        
        function confirmarHorario(relatorioId) {
            if (confirm('Confirmar este horÃ¡rio para o atendimento?')) {
                window.location.href = 'processar-resposta-horario.php?acao=confirmar&id=' + relatorioId;
            }
        }
        
        function rejeitarHorario(relatorioId) {
            abrirModalHorario(relatorioId);
        }
        
        function enviarNovoHorario() {
            const novaData = document.getElementById('nova_data').value;
            const observacoes = document.getElementById('observacoes_cliente').value;
            
            if (!novaData) {
                alert('Por favor, informe a data e horÃ¡rio preferido.');
                return;
            }
            
            const url = 'processar-acao-cliente.php?acao=solicitou_mudanca&id=' + relatorioAtual + 
                       '&nova_data=' + encodeURIComponent(novaData) + 
                       '&observacoes=' + encodeURIComponent(observacoes);
            
            window.location.href = url;
        }
        
        function agendarServico(relatorioId) {
            relatorioAtual = relatorioId;
            document.getElementById('modalAgendamento').style.display = 'block';
            carregarCalendarioAgendamento();
        }
        
        function editarDiagnostico(relatorioId) {
            window.location.href = 'editar-diagnostico.php?id=' + relatorioId;
        }
        
        function solicitarOrcamento(relatorioId) {
            window.location.href = 'solicitar-orcamento.php?diagnostico_id=' + relatorioId;
        }
        
        let mesAtualAgendamento = new Date();
        
        function carregarCalendarioAgendamento() {
            atualizarCalendarioAgendamento();
        }
        
        function navegarMesAgendamento(direcao) {
            mesAtualAgendamento.setMonth(mesAtualAgendamento.getMonth() + direcao);
            atualizarCalendarioAgendamento();
        }
        
        function atualizarCalendarioAgendamento() {
            const meses = ['Janeiro', 'Fevereiro', 'MarÃ§o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            document.getElementById('mesAtualAgendamento').textContent = meses[mesAtualAgendamento.getMonth()] + ' ' + mesAtualAgendamento.getFullYear();
            
            const calendario = document.getElementById('calendarioAgendamento');
            const diasAnteriores = calendario.querySelectorAll('.dia-agendamento');
            diasAnteriores.forEach(dia => dia.remove());
            
            const primeiroDia = new Date(mesAtualAgendamento.getFullYear(), mesAtualAgendamento.getMonth(), 1);
            const ultimoDia = new Date(mesAtualAgendamento.getFullYear(), mesAtualAgendamento.getMonth() + 1, 0);
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            
            for (let i = 0; i < primeiroDia.getDay(); i++) {
                const diaVazio = document.createElement('div');
                diaVazio.className = 'dia-agendamento';
                calendario.appendChild(diaVazio);
            }
            
            for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
                const dataAtual = new Date(mesAtualAgendamento.getFullYear(), mesAtualAgendamento.getMonth(), dia);
                const diaElement = document.createElement('div');
                diaElement.className = 'dia-agendamento';
                diaElement.textContent = dia;
                diaElement.style.cssText = 'text-align: center; padding: 10px; cursor: pointer; border: 1px solid #ddd; border-radius: 5px; transition: all 0.2s;';
                
                if (dataAtual >= hoje && dataAtual.getDay() !== 0) {
                    diaElement.style.background = '#e8f5e8';
                    diaElement.style.color = '#109349';
                    diaElement.onclick = () => selecionarDiaAgendamento(diaElement, dataAtual);
                    diaElement.onmouseover = () => { diaElement.style.background = '#109349'; diaElement.style.color = 'white'; };
                    diaElement.onmouseout = () => { if (!diaElement.classList.contains('selecionado')) { diaElement.style.background = '#e8f5e8'; diaElement.style.color = '#109349'; } };
                } else {
                    diaElement.style.background = '#f5f5f5';
                    diaElement.style.color = '#999';
                    diaElement.style.cursor = 'not-allowed';
                }
                
                calendario.appendChild(diaElement);
            }
        }
        
        function selecionarDiaAgendamento(elemento, data) {
            document.querySelectorAll('.dia-agendamento').forEach(dia => {
                dia.classList.remove('selecionado');
                if (dia.style.background !== '#f5f5f5') {
                    dia.style.background = '#e8f5e8';
                    dia.style.color = '#109349';
                }
            });
            
            elemento.classList.add('selecionado');
            elemento.style.background = '#109349';
            elemento.style.color = 'white';
            
            const dataFormatada = data.getFullYear() + '-' + String(data.getMonth() + 1).padStart(2, '0') + '-' + String(data.getDate()).padStart(2, '0');
            document.getElementById('dataAgendamentoSelecionada').value = dataFormatada;
            
            mostrarHorariosAgendamento();
        }
        
        function mostrarHorariosAgendamento() {
            const container = document.getElementById('horariosAgendamento');
            const grid = document.getElementById('horariosGridAgendamento');
            grid.innerHTML = '';
            
            const data = document.getElementById('dataAgendamentoSelecionada').value;
            const horarios = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
            
            // Buscar horÃ¡rios ocupados
            fetch('verificar-horarios-ocupados-servico.php?data=' + data)
                .then(response => response.json())
                .then(horariosOcupados => {
                    horarios.forEach(horario => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.textContent = horario;
                        
                        const ocupado = horariosOcupados.includes(horario);
                        
                        if (ocupado) {
                            btn.style.cssText = 'padding: 10px; border: 1px solid #dc3545; background: #f8d7da; color: #721c24; border-radius: 5px; cursor: not-allowed; text-decoration: line-through;';
                            btn.disabled = true;
                            btn.title = 'HorÃ¡rio ocupado';
                        } else {
                            btn.style.cssText = 'padding: 10px; border: 1px solid #109349; background: white; color: #109349; border-radius: 5px; cursor: pointer; transition: all 0.2s;';
                            btn.onclick = () => selecionarHorarioAgendamento(btn, horario);
                            btn.onmouseover = () => { if (!btn.classList.contains('selecionado')) { btn.style.background = '#e8f5e8'; } };
                            btn.onmouseout = () => { if (!btn.classList.contains('selecionado')) { btn.style.background = 'white'; } };
                        }
                        
                        grid.appendChild(btn);
                    });
                    
                    container.style.display = 'block';
                })
                .catch(error => {
                    console.error('Erro ao buscar horÃ¡rios:', error);
                    alert('Erro ao carregar horÃ¡rios disponÃ­veis');
                });
        }
        
        function selecionarHorarioAgendamento(elemento, horario) {
            document.querySelectorAll('#horariosGridAgendamento button').forEach(btn => {
                btn.classList.remove('selecionado');
                btn.style.background = 'white';
                btn.style.color = '#109349';
            });
            
            elemento.classList.add('selecionado');
            elemento.style.background = '#109349';
            elemento.style.color = 'white';
            document.getElementById('horaAgendamentoSelecionada').value = horario;
        }
        
        function confirmarAgendamento() {
            const data = document.getElementById('dataAgendamentoSelecionada').value;
            const hora = document.getElementById('horaAgendamentoSelecionada').value;
            const observacoes = document.getElementById('observacoesAgendamento').value;
            
            if (!data || !hora) {
                alert('Por favor, selecione a data e horÃ¡rio.');
                return;
            }
            
            window.location.href = 'processar-agendamento-servico.php?diagnostico_id=' + relatorioAtual + 
                                  '&data=' + encodeURIComponent(data) + 
                                  '&hora=' + encodeURIComponent(hora) + 
                                  '&observacoes=' + encodeURIComponent(observacoes);
        }
        
        function baixarPDF(relatorioId) {
            const tema = localStorage.getItem('theme') === 'theme-alemanha' ? 'alemanha' : 'italia';
            window.open('gerar-pdf-diagnostico.php?id=' + relatorioId + '&tema=' + tema, '_blank');
        }
        
        function cancelarAgendamento(agendamentoId) {
            document.getElementById('agendamentoIdCancelar').value = agendamentoId;
            document.getElementById('modalCancelarAgendamento').style.display = 'block';
        }
        
        function confirmarCancelamentoAgendamento() {
            const agendamentoId = document.getElementById('agendamentoIdCancelar').value;
            window.location.href = 'cancelar-agendamento.php?id=' + agendamentoId;
        }
        
        function reagendarServico(relatorioId, agendamentoId) {
            fetch('cancelar-agendamento.php?id=' + agendamentoId + '&silencioso=1')
                .then(() => {
                    agendarServico(relatorioId);
                });
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modals = ['modalAceitar', 'modalHorario', 'modalRejeitar', 'modalCancelar', 'modalAgendamento', 'modalCancelarAgendamento'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    fecharModal(modalId);
                }
            });
        }
    </script>
</body>
</html>

