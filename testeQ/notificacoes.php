<?php
require_once 'config.php';
verificarLogin();

// Define os botões do cabeçalho
$botoes_header = [
    [
        'url' => 'index.php',
        'icone' => 'fas fa-arrow-left',
        'texto' => 'Voltar',
        'classe' => 'btn-outline'
    ]
];

$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Processar ações
if ($_GET) {
    if (isset($_GET['acao'])) {
        if ($_GET['acao'] == 'marcar_lida' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $conexao->query("UPDATE notificacoes SET lida = 1 WHERE id = $id AND usuario_id = $usuario_id");
            header("Location: notificacoes.php");
            exit;
        }
        
        if ($_GET['acao'] == 'excluir' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $conexao->query("DELETE FROM notificacoes WHERE id = $id AND usuario_id = $usuario_id");
            header("Location: notificacoes.php");
            exit;
        }
        
        if ($_GET['acao'] == 'marcar_todas_lidas') {
            $conexao->query("UPDATE notificacoes SET lida = 1 WHERE usuario_id = $usuario_id");
            header("Location: notificacoes.php");
            exit;
        }
        
        if ($_GET['acao'] == 'excluir_todas') {
            $conexao->query("DELETE FROM notificacoes WHERE usuario_id = $usuario_id");
            header("Location: notificacoes.php");
            exit;
        }
    }
}

// Buscar notificações da tabela notificacoes
$result = $conexao->query("
    SELECT * FROM notificacoes 
    WHERE usuario_id = $usuario_id 
    ORDER BY data_criacao DESC
");

$notificacoes = [];
while ($row = $result->fetch_assoc()) {
    $notificacoes[] = $row;
}

$conexao->close();
include 'header.php';
?>

<style>
.notifications-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0;
}

.page-header {
    text-align: center;
    margin-bottom: 30px;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.page-header h1 {
    font-size: 2rem;
    margin-bottom: 8px;
    color: var(--secondary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.page-header p {
    font-size: 1rem;
    color: #666;
    margin: 0;
}

.stats-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.notifications-page .stat-item {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    color: var(--text-color);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s;
}



.stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 5px;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 500;
}

.actions-bar {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 25px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.actions-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-action {
    background: var(--success-color);
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-action:hover {
    background: #0d7a3a;
}

.btn-danger {
    background: var(--error-color);
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-back {
    background: var(--primary-color);
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-back:hover {
    background: #0d7a3a;
}

.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    position: relative;
    border-left: 4px solid #109349;
}

.notification-card.read {
    opacity: 0.7;
    border-left-color: #6c757d;
}





.notification-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding-right: 80px;
}

.notification-icon {
    width: 50px;
    height: 50px;
    background: #109349;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 15px;
    flex-shrink: 0;
}

.notification-info {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 8px;
    line-height: 1.3;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: #666;
    flex-wrap: wrap;
}

.notification-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-new {
    background: #ffe6e6;
    color: #e74c3c;
}

.status-read {
    background: #e8f5e8;
    color: #27ae60;
}

.notification-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.3s;
    text-decoration: none;
    font-weight: bold;
}

.btn-read {
    background: var(--success-color);
    color: white;
}

.btn-read:hover {
    background: #0d7a3a;
}

.btn-delete {
    background: var(--error-color);
    color: white;
}

.btn-delete:hover {
    background: #c0392b;
}

.notification-content {
    margin-bottom: 15px;
    line-height: 1.5;
    color: #555;
    padding-right: 80px;
}

.notification-badge {
    display: inline-block;
    background: #109349;
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-title {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-text {
    color: #666;
    margin-bottom: 5px;
}

.empty-subtext {
    font-size: 0.9rem;
    color: #999;
}

.btn-pagar-notif {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #109349;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.btn-pagar-notif:hover {
    background: #0d7a3a;
    color: white;
}

.notification-card[data-tipo="cobranca"] {
    border-left-color: #f39c12;
}

.notification-card[data-tipo="cobranca"] .notification-badge {
    background: #f39c12;
}

/* Tema Alemanha */
.theme-alemanha .page-header {
    background: #1a1a1a;
    color: white;
}

.theme-alemanha .page-header h1 { color: #FFCE00; }
.theme-alemanha .page-header p { color: #ccc; }

.theme-alemanha .stat-item {
    background: #1a1a1a;
    color: white;
}

.theme-alemanha .stat-number { color: #FFCE00; }
.theme-alemanha .stat-label { color: #ccc; }

.theme-alemanha .actions-bar {
    background: #1a1a1a;
}

.theme-alemanha .btn-action {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-action:hover { background: #e6b800; }

.theme-alemanha .btn-danger { background: #DD0100; }
.theme-alemanha .btn-danger:hover { background: #c00; }

.theme-alemanha .btn-back {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-back:hover { background: #e6b800; }

.theme-alemanha .notification-card {
    background: #1a1a1a;
    border-left-color: #FFCE00;
    color: white;
}

.theme-alemanha .notification-card.read {
    opacity: 0.7;
    border-left-color: #999;
}

.theme-alemanha .notification-icon { background: #FFCE00; }
.theme-alemanha .notification-icon i { color: #000; }

.theme-alemanha .notification-title { color: #FFCE00; }
.theme-alemanha .notification-meta { color: #ccc; }
.theme-alemanha .notification-content { color: #ccc; }

.theme-alemanha .status-new {
    background: rgba(221, 1, 0, 0.2);
    color: #DD0100;
}

.theme-alemanha .status-read {
    background: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .btn-read {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-read:hover { background: #e6b800; }

.theme-alemanha .btn-delete { background: #DD0100; }
.theme-alemanha .btn-delete:hover { background: #c00; }

.theme-alemanha .notification-badge { background: #FFCE00; color: #000; }

.theme-alemanha .notification-card[data-tipo="cobranca"] {
    border-left-color: #DD0100;
}

.theme-alemanha .notification-card[data-tipo="cobranca"] .notification-badge {
    background: #DD0100;
    color: white;
}

.theme-alemanha .btn-pagar-notif {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-pagar-notif:hover {
    background: #e6b800;
    color: #000;
}

.theme-alemanha .empty-state {
    background: #1a1a1a;
    color: white;
}

.theme-alemanha .empty-title { color: #FFCE00; }
.theme-alemanha .empty-text { color: #ccc; }
.theme-alemanha .empty-subtext { color: #999; }

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 1.5rem;
        flex-direction: column;
        gap: 5px;
    }
    
    .stats-bar {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .actions-bar {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .actions-group {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .notification-card {
        padding: 15px;
    }
    
    .notification-header {
        padding-right: 0;
        margin-bottom: 10px;
    }
    
    .notification-content {
        padding-right: 0;
        margin-bottom: 10px;
    }
    
    .notification-actions {
        position: static;
        justify-content: center;
        margin-top: 15px;
    }
    
    .notification-meta {
        font-size: 0.8rem;
    }
}
</style>

<div class="notifications-page">
    <div class="page-header">
        <h1><i class="fas fa-bell"></i> Central de Notificações</h1>
        <p>Fique por dentro de todas as novidades e promoções</p>
    </div>

    <?php
    $total = count($notificacoes);
    $nao_lidas = count(array_filter($notificacoes, function($n) { return !$n['lida']; }));
    $lidas = $total - $nao_lidas;
    ?>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $nao_lidas; ?></div>
            <div class="stat-label">Não Lidas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $lidas; ?></div>
            <div class="stat-label">Lidas</div>
        </div>
    </div>

    <?php if ($total > 0): ?>
        <div class="actions-bar">
            <div class="actions-group">
                <?php if ($nao_lidas > 0): ?>
                    <a href="notificacoes.php?acao=marcar_todas_lidas" class="btn-action">
                        <i class="fas fa-check"></i> Marcar Todas como Lidas
                    </a>
                <?php endif; ?>
                <a href="#" onclick="confirmarExcluirTodas()" class="btn-danger">
                    <i class="fas fa-trash"></i> Excluir Todas
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="notifications-container">
        <?php if (count($notificacoes) > 0): ?>
            <?php foreach ($notificacoes as $notif): ?>
                <div class="notification-card <?php echo $notif['lida'] ? 'read' : ''; ?>" data-tipo="<?php echo $notif['tipo']; ?>">
                    <div class="notification-actions">
                        <?php if (!$notif['lida']): ?>
                            <a href="notificacoes.php?acao=marcar_lida&id=<?php echo $notif['id']; ?>" class="action-btn btn-read" title="Marcar como lida">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php endif; ?>
                        <a href="#" onclick="confirmarExclusao(<?php echo $notif['id']; ?>)" class="action-btn btn-delete" title="Excluir">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    
                    <div class="notification-header">
                        <div class="notification-icon">
                            <i class="fas fa-bell" style="color: white;"></i>
                        </div>
                        <div class="notification-info">
                            <div class="notification-title"><?php echo $notif['titulo']; ?></div>
                            <div class="notification-meta">
                                <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($notif['data_criacao'])); ?></span>
                                <span class="notification-status <?php echo $notif['lida'] ? 'status-read' : 'status-new'; ?>">
                                    <?php echo $notif['lida'] ? 'Lida' : 'Nova'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notification-content">
                        <?php echo nl2br($notif['mensagem']); ?>
                        <?php if ($notif['tipo'] == 'cobranca'): ?>
                            <div style="margin-top: 15px;">
                                <a href="pagamentos.php" class="btn-pagar-notif">
                                    <i class="fas fa-credit-card"></i> Ver Pagamentos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-badge">
                        Notificação
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                <div class="empty-title">Nenhuma notificação</div>
                <div class="empty-text">Você não possui notificações no momento.</div>
                <div class="empty-subtext">Notificações excluídas não são exibidas aqui.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="modal-confirmacao" class="modal-confirmacao">
    <div class="modal-confirmacao-content">
        <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="modal-titulo">Confirmar Exclusão</h3>
        <p id="modal-mensagem">Deseja realmente excluir esta notificação?</p>
        <div class="modal-actions">
            <button onclick="fecharModal()" class="btn-modal-cancelar">Cancelar</button>
            <button id="btn-confirmar" class="btn-modal-confirmar">Excluir</button>
        </div>
    </div>
</div>

<style>
.modal-confirmacao {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-confirmacao-content {
    background: linear-gradient(135deg, #109349 0%, #ffffff 50%, #CE2B37 100%);
    padding: 3px;
    border-radius: 15px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: slideIn 0.3s ease;
    position: relative;
}

.modal-confirmacao-content::before {
    content: '';
    position: absolute;
    inset: 3px;
    background: white;
    border-radius: 13px;
    z-index: 1;
}

.modal-confirmacao-content > * {
    position: relative;
    z-index: 2;
}

.theme-alemanha .modal-confirmacao-content {
    background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
}

.theme-alemanha .modal-confirmacao-content::before {
    background: #1a1a1a;
}

.modal-icon {
    font-size: 50px;
    margin: 30px 0 15px;
    color: #f39c12;
}

.theme-alemanha .modal-icon { color: #FFCE00; }

.modal-confirmacao-content h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.3rem;
}

.theme-alemanha .modal-confirmacao-content h3 { color: #FFCE00; }

.modal-confirmacao-content p {
    margin: 0 0 25px 0;
    color: #666;
    line-height: 1.4;
    padding: 0 20px;
}

.theme-alemanha .modal-confirmacao-content p { color: #ccc; }

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 0 20px 30px;
}

.btn-modal-cancelar,
.btn-modal-confirmar {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-modal-cancelar {
    background: #6c757d;
    color: white;
}

.btn-modal-cancelar:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-modal-confirmar {
    background: #e74c3c;
    color: white;
}

.btn-modal-confirmar:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.theme-alemanha .btn-modal-confirmar {
    background: #DD0100;
}

.theme-alemanha .btn-modal-confirmar:hover {
    background: #c00;
}

@keyframes slideIn {
    from { transform: translateY(-50px) scale(0.9); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

@media (max-width: 480px) {
    .modal-confirmacao-content {
        width: 95%;
        max-width: 350px;
    }
    
    .modal-icon { font-size: 40px; }
    
    .modal-confirmacao-content h3 { font-size: 1.1rem; }
    
    .modal-actions {
        flex-direction: column;
        padding: 0 15px 25px;
    }
    
    .btn-modal-cancelar,
    .btn-modal-confirmar {
        width: 100%;
    }
}
</style>

<script>
function confirmarExclusao(id) {
    const modal = document.getElementById('modal-confirmacao');
    const btnConfirmar = document.getElementById('btn-confirmar');
    const titulo = document.getElementById('modal-titulo');
    const mensagem = document.getElementById('modal-mensagem');
    
    titulo.textContent = 'Confirmar Exclusão';
    mensagem.textContent = 'Deseja realmente excluir esta notificação?';
    
    modal.style.display = 'flex';
    
    btnConfirmar.onclick = function() {
        window.location.href = 'notificacoes.php?acao=excluir&id=' + id;
    };
}

function confirmarExcluirTodas() {
    const modal = document.getElementById('modal-confirmacao');
    const btnConfirmar = document.getElementById('btn-confirmar');
    const titulo = document.getElementById('modal-titulo');
    const mensagem = document.getElementById('modal-mensagem');
    
    titulo.textContent = 'Excluir Todas as Notificações';
    mensagem.textContent = 'Esta ação irá excluir TODAS as suas notificações. Deseja continuar?';
    
    modal.style.display = 'flex';
    
    btnConfirmar.onclick = function() {
        window.location.href = 'notificacoes.php?acao=excluir_todas';
    };
}

function fecharModal() {
    document.getElementById('modal-confirmacao').style.display = 'none';
}

// Fechar modal ao clicar fora
document.getElementById('modal-confirmacao').onclick = function(e) {
    if (e.target === this) {
        fecharModal();
    }
};
</script>

<?php include 'footer.php'; ?>