<?php
$titulo = "Solicitações de Orçamento";
require_once 'header.php';

if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Buscar solicitações de orçamento
$stmt = $conexao->prepare("
    SELECT so.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone,
           v.marca, v.modelo, v.placa, v.ano
    FROM solicitacoes_orcamento so
    JOIN usuarios u ON so.usuario_id = u.id
    LEFT JOIN relatorios_cliente rc ON so.diagnostico_id = rc.id
    LEFT JOIN veiculos v ON rc.veiculo_id = v.id
    WHERE so.mecanico_id = ?
    ORDER BY so.data_solicitacao DESC
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$orcamentos = $stmt->get_result();

$conexao->close();
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calculator"></i> Solicitações de Orçamento</h2>
    </div>
    <div class="card-body">
        <?php if ($orcamentos->num_rows > 0): ?>
            <?php while ($orcamento = $orcamentos->fetch_assoc()): ?>
                <div class="orcamento-card">
                    <div class="orcamento-header">
                        <div class="cliente-info">
                            <h4><i class="fas fa-user"></i> <?php echo $orcamento['cliente_nome']; ?></h4>
                            <p><i class="fas fa-car"></i> <?php echo $orcamento['marca'] . ' ' . $orcamento['modelo'] . ' (' . $orcamento['placa'] . ')'; ?></p>
                        </div>
                        <div class="urgencia-badge urgencia-<?php echo $orcamento['urgencia']; ?>">
                            <?php echo ucfirst($orcamento['urgencia']); ?>
                        </div>
                    </div>
                    
                    <div class="descricao-problema">
                        <h5><i class="fas fa-clipboard-list"></i> Descrição Detalhada</h5>
                        <p><?php echo nl2br(htmlspecialchars($orcamento['descricao_detalhada'])); ?></p>
                    </div>
                    
                    <?php if ($orcamento['observacoes']): ?>
                    <div class="observacoes">
                        <h5><i class="fas fa-comment"></i> Observações</h5>
                        <p><?php echo nl2br(htmlspecialchars($orcamento['observacoes'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    $fotos = json_decode($orcamento['fotos'], true);
                    if ($fotos && count($fotos) > 0): 
                    ?>
                    <div class="fotos-container">
                        <h5><i class="fas fa-camera"></i> Fotos Enviadas (<?php echo count($fotos); ?>)</h5>
                        <div class="fotos-grid">
                            <?php foreach ($fotos as $foto): ?>
                                <div class="foto-item" onclick="abrirFoto('uploads/orcamentos/<?php echo $foto; ?>')">
                                    <img src="uploads/orcamentos/<?php echo $foto; ?>" alt="Foto do problema">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="orcamento-actions">
                        <button onclick="responderOrcamento(<?php echo $orcamento['id']; ?>)" class="btn btn-primary">
                            <i class="fas fa-reply"></i> Responder Orçamento
                        </button>
                        <a href="tel:<?php echo $orcamento['cliente_telefone']; ?>" class="btn btn-success">
                            <i class="fas fa-phone"></i> Ligar
                        </a>
                        <a href="mailto:<?php echo $orcamento['cliente_email']; ?>" class="btn btn-info">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                    
                    <div class="data-solicitacao">
                        <small><i class="fas fa-clock"></i> Solicitado em: <?php echo formatarData($orcamento['data_solicitacao'], 'd/m/Y H:i'); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calculator"></i>
                <h3>Nenhuma solicitação de orçamento</h3>
                <p>Você ainda não recebeu solicitações de orçamento detalhado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Foto -->
<div id="modalFoto" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9);">
    <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
        <img id="fotoModal" style="max-width: 90%; max-height: 90%; object-fit: contain;">
        <button onclick="fecharFoto()" style="position: absolute; top: 20px; right: 30px; background: none; border: none; color: white; font-size: 30px; cursor: pointer;">×</button>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background: #109349;
    color: white;
    padding: 20px;
}

.card-body {
    padding: 20px;
}

.orcamento-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f9f9f9;
}

.orcamento-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.cliente-info h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.cliente-info p {
    margin: 0;
    color: #666;
}

.urgencia-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.urgencia-normal {
    background: #d4edda;
    color: #155724;
}

.urgencia-urgente {
    background: #fff3cd;
    color: #856404;
}

.urgencia-emergencia {
    background: #f8d7da;
    color: #721c24;
}

.descricao-problema, .observacoes {
    margin-bottom: 15px;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border-left: 4px solid #109349;
}

.descricao-problema h5, .observacoes h5 {
    margin: 0 0 10px 0;
    color: #109349;
}

.fotos-container {
    margin-bottom: 15px;
}

.fotos-container h5 {
    margin-bottom: 10px;
    color: #109349;
}

.fotos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
}

.foto-item {
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s;
}

.foto-item:hover {
    transform: scale(1.05);
}

.foto-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.orcamento-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-primary {
    background: #109349;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.data-solicitacao {
    text-align: right;
    color: #666;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #ddd;
}

body.theme-alemanha .card-header {
    background: #000;
    color: #FFCE00;
}

body.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

body.theme-alemanha .descricao-problema,
body.theme-alemanha .observacoes {
    border-left-color: #FFCE00;
}

body.theme-alemanha .descricao-problema h5,
body.theme-alemanha .observacoes h5,
body.theme-alemanha .fotos-container h5 {
    color: #FFCE00;
}

@media (max-width: 768px) {
    .orcamento-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .orcamento-actions {
        flex-direction: column;
    }
    
    .fotos-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>

<script>
function abrirFoto(src) {
    document.getElementById('fotoModal').src = src;
    document.getElementById('modalFoto').style.display = 'block';
}

function fecharFoto() {
    document.getElementById('modalFoto').style.display = 'none';
}

function responderOrcamento(id) {
    alert('Funcionalidade de resposta de orçamento será implementada em breve.');
}

// Fechar modal ao clicar fora da imagem
document.getElementById('modalFoto').onclick = function(event) {
    if (event.target === this) {
        fecharFoto();
    }
}
</script>

<?php require_once 'footer.php'; ?>