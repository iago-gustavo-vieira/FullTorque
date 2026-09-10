<?php
require_once 'config.php';
verificarLogin();

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Processar cadastro de método de pagamento
if ($_POST && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'cadastrar_cartao') {
        $numero = isset($_POST['numero']) ? limparDados($_POST['numero']) : '';
        $nome = isset($_POST['nome']) ? limparDados($_POST['nome']) : '';
        $validade = isset($_POST['validade']) ? limparDados($_POST['validade']) : '';
        $tipo = isset($_POST['tipo']) ? limparDados($_POST['tipo']) : '';
        
        if ($numero && $nome && $validade && $tipo) {
            $stmt = $conexao->prepare("INSERT INTO cartoes_usuario (usuario_id, numero, nome, validade, tipo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $_SESSION['usuario_id'], $numero, $nome, $validade, $tipo);
            
            if ($stmt->execute()) {
                exibirAlerta('success', 'Cartão cadastrado com sucesso!');
            } else {
                exibirAlerta('error', 'Erro ao cadastrar cartão.');
            }
        } else {
            exibirAlerta('error', 'Preencha todos os campos.');
        }
    } elseif ($_POST['acao'] == 'processar_pagamento') {
        $pagamento_id = intval($_POST['pagamento_id']);
        $metodo = limparDados($_POST['metodo_pagamento']);
        
        $stmt = $conexao->prepare("UPDATE pagamentos SET metodo_pagamento = ?, status = 'aprovado', data_pagamento = NOW(), data_atualizacao = NOW() WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("sii", $metodo, $pagamento_id, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            exibirAlerta('success', 'Pagamento processado com sucesso!');
        } else {
            exibirAlerta('error', 'Erro ao processar pagamento.');
        }
    }
}

// Buscar cartões cadastrados
$cartoes_query = $conexao->prepare("SELECT * FROM cartoes_usuario WHERE usuario_id = ? ORDER BY data_cadastro DESC");
$cartoes_query->bind_param("i", $_SESSION['usuario_id']);
$cartoes_query->execute();
$cartoes_cadastrados = $cartoes_query->get_result();

// Buscar cobranças pendentes para destaque
$cobrancas_pendentes = $conexao->prepare("SELECT * FROM pagamentos WHERE usuario_id = ? AND status = 'pendente' ORDER BY data_vencimento ASC LIMIT 3");
$cobrancas_pendentes->bind_param("i", $_SESSION['usuario_id']);
$cobrancas_pendentes->execute();
$pendentes_destaque = $cobrancas_pendentes->get_result();

// Buscar pagamentos do usuário
$pagamentos = $conexao->prepare("SELECT * FROM pagamentos WHERE usuario_id = ? ORDER BY data_criacao DESC");
$pagamentos->bind_param("i", $_SESSION['usuario_id']);
$pagamentos->execute();
$meus_pagamentos = $pagamentos->get_result();

// Estatísticas do usuário
$stats_query = $conexao->prepare("
    SELECT 
        SUM(CASE WHEN status = 'aprovado' THEN valor ELSE 0 END) as total_pago,
        SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END) as total_pendente,
        COUNT(CASE WHEN status = 'pendente' AND data_vencimento < CURDATE() THEN 1 END) as vencidos
    FROM pagamentos WHERE usuario_id = ?
");
$stats_query->bind_param("i", $_SESSION['usuario_id']);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

$titulo = "Meus Pagamentos";
include 'header.php';
?>

<style>
.payment-container {
    max-width: 1200px;
    margin: 0 auto;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 20px;
    width: 60px;
    text-align: center;
}

.stat-card.success .stat-icon { color: #28a745; }
.stat-card.warning .stat-icon { color: #ffc107; }
.stat-card.danger .stat-icon { color: #dc3545; }

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #666;
}

.stat-content p {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
    color: #333;
}

.card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 1.3rem;
}

.card-body {
    padding: 25px;
}

.cobrancas-destaque {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border: 2px solid #ffc107;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
}

.alert-header {
    text-align: center;
    margin-bottom: 20px;
}

.alert-header h3 {
    color: #856404;
    margin: 0;
    font-size: 1.5rem;
}

.cobrancas-lista {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cobranca-item {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #ffc107;
}

.cobranca-info strong {
    display: block;
    color: #333;
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.cobranca-info .valor {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 0.9rem;
    margin-right: 10px;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.payment-method {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method:hover {
    border-color: #3498db;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.payment-method.active {
    border-color: #3498db;
    background: #f0f8ff;
}

.payment-method i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #666;
}

.payment-method.active i {
    color: #3498db;
}

.payment-method h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.payment-method p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn-success {
    background: #28a745;
}

.btn-success:hover {
    background: #218838;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
}

.qr-code {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 15px;
    margin: 20px 0;
}

.qr-code img {
    max-width: 200px;
    border-radius: 10px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th,
.table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.badge.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.badge.danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

.cartoes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.cartao-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s;
}

.cartao-item:hover {
    transform: translateY(-5px);
}

.cartao-numero {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 8px;
    letter-spacing: 2px;
}

.cartao-nome {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 8px;
}

.cartao-detalhes {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
}

.cartao-tipo {
    background: rgba(255,255,255,0.2);
    padding: 2px 8px;
    border-radius: 10px;
}

.cartao-icone {
    font-size: 2rem;
    opacity: 0.7;
}

.payment-options {
    max-height: 400px;
    overflow-y: auto;
}

.payment-option {
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-option:hover {
    border-color: #3498db;
    background: #f0f8ff;
}

.option-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.option-info i {
    font-size: 1.5rem;
    color: #3498db;
    width: 30px;
    text-align: center;
}

.option-info strong {
    display: block;
    margin-bottom: 3px;
}

.option-info small {
    color: #666;
}

.codigo-barras-visual {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
    border: 2px solid #ddd;
}

.barras {
    display: flex;
    align-items: end;
    justify-content: center;
    height: 60px;
    gap: 1px;
}

.barra {
    background: #000;
    width: 3px;
    height: 60px;
}

.barra.fina {
    width: 1px;
    height: 50px;
}

.barra.espaco {
    background: transparent;
    width: 2px;
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
    padding: 0;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border-radius: 15px 15px 0 0;
}

.modal-body {
    padding: 25px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: #333;
}

@media (max-width: 768px) {
    .cobranca-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="payment-container">
    <!-- Cobranças Pendentes em Destaque -->
    <?php if ($pendentes_destaque->num_rows > 0): ?>
    <div class="cobrancas-destaque">
        <div class="alert-header">
            <h3><i class="fas fa-exclamation-circle"></i> Você tem cobranças pendentes!</h3>
        </div>
        <div class="cobrancas-lista">
            <?php while ($cobranca = $pendentes_destaque->fetch_assoc()): ?>
            <div class="cobranca-item">
                <div class="cobranca-info">
                    <strong><?php echo htmlspecialchars($cobranca['descricao'] ?: 'Pagamento de serviço'); ?></strong>
                    <span class="valor">R$ <?php echo number_format($cobranca['valor'], 2, ',', '.'); ?></span>
                    <small>Vence em <?php echo date('d/m/Y', strtotime($cobranca['data_vencimento'])); ?></small>
                </div>
                <div class="cobranca-acoes">
                    <?php if ($cobranca['link_pagamento']): ?>
                        <a href="<?php echo $cobranca['link_pagamento']; ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Pagar Agora
                        </a>
                    <?php else: ?>
                        <button onclick="abrirModalPagamento(<?php echo $cobranca['id']; ?>, <?php echo $cobranca['valor']; ?>)" class="btn btn-success btn-sm">
                            <i class="fas fa-credit-card"></i> Pagar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Total Pago</h3>
                <p>R$ <?php echo number_format($stats['total_pago'] ?? 0, 2, ',', '.'); ?></p>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pendente</h3>
                <p>R$ <?php echo number_format($stats['total_pendente'] ?? 0, 2, ',', '.'); ?></p>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3>Vencidos</h3>
                <p><?php echo $stats['vencidos'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <!-- Cartões Cadastrados -->
    <?php if ($cartoes_cadastrados->num_rows > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Meus Cartões</h2>
            <button onclick="abrirModalCadastro()" class="btn">
                <i class="fas fa-plus"></i> Adicionar Cartão
            </button>
        </div>
        <div class="card-body">
            <div class="cartoes-grid">
                <?php while ($cartao = $cartoes_cadastrados->fetch_assoc()): ?>
                <div class="cartao-item">
                    <div class="cartao-info">
                        <div class="cartao-numero">**** **** **** <?php echo substr($cartao['numero'], -4); ?></div>
                        <div class="cartao-nome"><?php echo htmlspecialchars($cartao['nome']); ?></div>
                        <div class="cartao-detalhes">
                            <span class="cartao-tipo"><?php echo ucfirst($cartao['tipo']); ?></span>
                            <span class="cartao-validade"><?php echo $cartao['validade']; ?></span>
                        </div>
                    </div>
                    <div class="cartao-icone">
                        <i class="fas fa-credit-card"></i>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Métodos de Pagamento</h2>
            <button onclick="abrirModalCadastro()" class="btn">
                <i class="fas fa-plus"></i> Cadastrar Cartão
            </button>
        </div>
        <div class="card-body">
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-credit-card fa-3x" style="margin-bottom: 20px; color: #ccc;"></i>
                <h3>Nenhum cartão cadastrado</h3>
                <p>Cadastre um cartão para facilitar seus pagamentos.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Pagamentos -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Histórico de Pagamentos</h2>
        </div>
        <div class="card-body">
            <?php if ($meus_pagamentos->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pagamento = $meus_pagamentos->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($pagamento['descricao'] ?: 'Pagamento de serviço'); ?></strong><br>
                            <small>Criado em <?php echo formatarData($pagamento['data_criacao'], 'd/m/Y H:i'); ?></small>
                        </td>
                        <td><strong>R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></strong></td>
                        <td>
                            <?php 
                            if ($pagamento['data_vencimento']) {
                                echo date('d/m/Y', strtotime($pagamento['data_vencimento']));
                                if ($pagamento['status'] == 'pendente' && $pagamento['data_vencimento'] < date('Y-m-d')) {
                                    echo '<br><span class="badge danger">Vencido</span>';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_class = '';
                            switch($pagamento['status']) {
                                case 'aprovado': $status_class = 'success'; break;
                                case 'pendente': $status_class = 'warning'; break;
                                case 'processando': $status_class = 'info'; break;
                                case 'recusado': case 'cancelado': $status_class = 'danger'; break;
                                default: $status_class = 'secondary';
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo ucfirst($pagamento['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($pagamento['status'] == 'pendente'): ?>
                                <button onclick="abrirModalPagamento(<?php echo $pagamento['id']; ?>, <?php echo $pagamento['valor']; ?>)" class="btn btn-sm">
                                    <i class="fas fa-credit-card"></i> Pagar
                                </button>
                            <?php elseif ($pagamento['status'] == 'aprovado'): ?>
                                <span style="color: #28a745;"><i class="fas fa-check"></i> Pago</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <i class="fas fa-receipt fa-3x" style="margin-bottom: 20px; color: #ccc;"></i>
                <h3>Nenhum pagamento encontrado</h3>
                <p>Você não possui pagamentos registrados no momento.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Método -->
<div id="modalCadastro" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Cadastrar Método de Pagamento</h3>
            <span class="close" onclick="fecharModal('modalCadastro')">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar_cartao">
                
                <div class="form-group">
                    <label>Número do Cartão</label>
                    <input type="text" name="numero" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Validade</label>
                        <input type="text" name="validade" placeholder="MM/AA" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="000" maxlength="3" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nome no Cartão</label>
                    <input type="text" name="nome" placeholder="Nome como está no cartão" required>
                </div>
                
                <div class="form-group">
                    <label>Tipo do Cartão</label>
                    <select name="tipo" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px;">
                        <option value="">Selecione o tipo</option>
                        <option value="credito">Crédito</option>
                        <option value="debito">Débito</option>
                    </select>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-save"></i> Cadastrar Cartão
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pagamento -->
<div id="modalPagamento" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> Escolha a forma de pagamento</h3>
            <span class="close" onclick="fecharModal('modalPagamento')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="payment-options">
                <?php 
                // Resetar o resultado para usar novamente
                $cartoes_cadastrados->data_seek(0);
                if ($cartoes_cadastrados->num_rows > 0): 
                ?>
                <h4 style="margin-bottom: 15px;">Cartões Cadastrados:</h4>
                <?php while ($cartao = $cartoes_cadastrados->fetch_assoc()): ?>
                <div class="payment-option" onclick="selecionarPagamento('cartao', <?php echo $cartao['id']; ?>)">
                    <div class="option-info">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <strong>**** **** **** <?php echo substr($cartao['numero'], -4); ?></strong>
                            <small><?php echo ucfirst($cartao['tipo']); ?> - <?php echo htmlspecialchars($cartao['nome']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <hr style="margin: 20px 0;">
                <?php endif; ?>
                
                <h4 style="margin-bottom: 15px;">Outras opções:</h4>
                
                <div class="payment-option" onclick="selecionarPagamento('pix')">
                    <div class="option-info">
                        <i class="fas fa-qrcode"></i>
                        <div>
                            <strong>PIX</strong>
                            <small>Pagamento instantâneo</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('boleto')">
                    <div class="option-info">
                        <i class="fas fa-barcode"></i>
                        <div>
                            <strong>Boleto</strong>
                            <small>Vencimento em 3 dias</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo PIX -->
            <div id="pixContent" style="display: none;">
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode('PIX Simulado - Valor: R$ 100,00'); ?>" alt="QR Code PIX">
                    <p><strong>Escaneie o QR Code acima</strong></p>
                    <p>Chave PIX: <code>pagamento@simulacao.com</code></p>
                </div>
                <button onclick="confirmarPagamento('pix')" class="btn" style="width: 100%;">
                    <i class="fas fa-check"></i> Confirmar Pagamento PIX
                </button>
            </div>
            
            <!-- Conteúdo Cartão -->
            <div id="cartaoContent" style="display: none;">
                <p style="text-align: center; margin-bottom: 20px;">Processando com cartão selecionado...</p>
                <button onclick="confirmarPagamento('cartao')" class="btn" style="width: 100%;">
                    <i class="fas fa-check"></i> Confirmar Pagamento
                </button>
            </div>
            
            <!-- Conteúdo Boleto -->
            <div id="boletoContent" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <p><strong>Código de Barras:</strong></p>
                    <div class="codigo-barras-visual">
                        <div class="barras">
                            <div class="barra"></div><div class="barra"></div><div class="barra espaco"></div>
                            <div class="barra"></div><div class="barra fina"></div><div class="barra"></div>
                            <div class="barra espaco"></div><div class="barra fina"></div><div class="barra"></div>
                            <div class="barra"></div><div class="barra espaco"></div><div class="barra fina"></div>
                            <div class="barra"></div><div class="barra"></div><div class="barra espaco"></div>
                            <div class="barra fina"></div><div class="barra"></div><div class="barra fina"></div>
                            <div class="barra espaco"></div><div class="barra"></div><div class="barra fina"></div>
                            <div class="barra"></div><div class="barra espaco"></div><div class="barra"></div>
                            <div class="barra fina"></div><div class="barra"></div><div class="barra espaco"></div>
                            <div class="barra"></div><div class="barra"></div><div class="barra fina"></div>
                        </div>
                    </div>
                    <code style="font-size: 0.9rem; margin-top: 10px; display: block;">99999.99999 99999.999999 99999.999999 9 99999999999999</code>
                </div>
                <button onclick="baixarBoletoPDF()" class="btn" style="width: 100%;">
                    <i class="fas fa-download"></i> Baixar Boleto PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais para controle de pagamento
window.pagamentoAtual = null;
window.metodoSelecionado = null;

function abrirModalCadastro() {
    document.getElementById('modalCadastro').style.display = 'block';
}

function abrirModalPagamento(id, valor) {
    try {
        window.pagamentoAtual = id;
        console.log('Abrindo modal para pagamento ID:', id, 'Valor:', valor);
        
        // Resetar modal
        const paymentOptions = document.querySelector('.payment-options');
        if (paymentOptions) {
            paymentOptions.style.display = 'block';
        }
        
        document.querySelectorAll('[id$="Content"]').forEach(el => {
            el.style.display = 'none';
        });
        
        const modal = document.getElementById('modalPagamento');
        if (modal) {
            modal.style.display = 'block';
        } else {
            console.error('Modal de pagamento não encontrado');
        }
    } catch (error) {
        console.error('Erro ao abrir modal de pagamento:', error);
        alert('Erro ao abrir modal de pagamento. Tente recarregar a página.');
    }
}

function selecionarPagamento(tipo, cartaoId = null) {
    try {
        // Esconder opções e mostrar conteúdo específico
        const paymentOptions = document.querySelector('.payment-options');
        if (paymentOptions) {
            paymentOptions.style.display = 'none';
        }
        
        // Esconder todos os conteúdos
        document.querySelectorAll('[id$="Content"]').forEach(el => {
            el.style.display = 'none';
        });
        
        // Mostrar conteúdo específico
        const contentElement = document.getElementById(tipo + 'Content');
        if (contentElement) {
            contentElement.style.display = 'block';
        } else {
            console.error('Elemento não encontrado:', tipo + 'Content');
        }
        
        window.metodoSelecionado = tipo;
        if (cartaoId) {
            window.metodoSelecionado = 'cartao_' + cartaoId;
        }
        
        console.log('Método selecionado:', window.metodoSelecionado);
    } catch (error) {
        console.error('Erro ao selecionar pagamento:', error);
        alert('Erro ao selecionar forma de pagamento. Tente novamente.');
    }
}

function confirmarPagamento(metodo) {
    if (!window.pagamentoAtual) {
        alert('Erro: Nenhum pagamento selecionado.');
        return;
    }
    
    // Mostrar loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    button.disabled = true;
    
    // Simular processamento
    setTimeout(function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="acao" value="processar_pagamento">
            <input type="hidden" name="pagamento_id" value="${window.pagamentoAtual}">
            <input type="hidden" name="metodo_pagamento" value="${metodo}">
        `;
        document.body.appendChild(form);
        form.submit();
    }, 1500);
}

function baixarBoletoPDF() {
    if (!window.pagamentoAtual) return;
    
    // Criar janela para o PDF
    const pdfWindow = window.open('', '_blank');
    const htmlContent = `<!DOCTYPE html>
<html>
<head>
    <title>Boleto Bancário - SIMULAÇÃO</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .boleto { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .banco { font-size: 24px; font-weight: bold; color: #1e3a8a; }
        .simulacao { color: red; font-weight: bold; font-size: 18px; margin: 10px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .info-label { font-weight: bold; color: #666; font-size: 12px; margin-bottom: 5px; }
        .info-value { font-size: 16px; color: #333; }
        .codigo-barras { text-align: center; margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .barras-pdf { display: flex; justify-content: center; align-items: end; height: 60px; gap: 1px; margin: 15px 0; }
        .barra-pdf { background: #000; width: 3px; height: 60px; }
        .barra-pdf.fina { width: 1px; height: 50px; }
        .barra-pdf.espaco { background: transparent; width: 2px; }
        .codigo-numerico { font-family: monospace; font-size: 14px; letter-spacing: 2px; margin-top: 10px; }
        .aviso { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 30px; text-align: center; }
        @media print { body { background: white; } .boleto { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="boleto">
        <div class="header">
            <div class="banco">BANCO SIMULAÇÃO - 999</div>
            <div class="simulacao">*** BOLETO FALSO - APENAS SIMULAÇÃO ***</div>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">BENEFICIÁRIO</div>
                <div class="info-value">Auto Service Ltda</div>
            </div>
            <div class="info-item">
                <div class="info-label">VENCIMENTO</div>
                <div class="info-value">${new Date(Date.now() + 3*24*60*60*1000).toLocaleDateString('pt-BR')}</div>
            </div>
            <div class="info-item">
                <div class="info-label">PAGADOR</div>
                <div class="info-value">Cliente Simulação</div>
            </div>
            <div class="info-item">
                <div class="info-label">VALOR</div>
                <div class="info-value">R$ 100,00</div>
            </div>
            <div class="info-item">
                <div class="info-label">NOSSO NÚMERO</div>
                <div class="info-value">999999999-9</div>
            </div>
            <div class="info-item">
                <div class="info-label">DOCUMENTO</div>
                <div class="info-value">${window.pagamentoAtual}</div>
            </div>
        </div>
        <div class="codigo-barras">
            <div style="font-weight: bold; margin-bottom: 10px;">CÓDIGO DE BARRAS</div>
            <div class="barras-pdf">
                <div class="barra-pdf"></div><div class="barra-pdf"></div><div class="barra-pdf espaco"></div>
                <div class="barra-pdf"></div><div class="barra-pdf fina"></div><div class="barra-pdf"></div>
                <div class="barra-pdf espaco"></div><div class="barra-pdf fina"></div><div class="barra-pdf"></div>
                <div class="barra-pdf"></div><div class="barra-pdf espaco"></div><div class="barra-pdf fina"></div>
                <div class="barra-pdf"></div><div class="barra-pdf"></div><div class="barra-pdf espaco"></div>
                <div class="barra-pdf fina"></div><div class="barra-pdf"></div><div class="barra-pdf fina"></div>
                <div class="barra-pdf espaco"></div><div class="barra-pdf"></div><div class="barra-pdf fina"></div>
                <div class="barra-pdf"></div><div class="barra-pdf espaco"></div><div class="barra-pdf"></div>
                <div class="barra-pdf fina"></div><div class="barra-pdf"></div><div class="barra-pdf espaco"></div>
                <div class="barra-pdf"></div><div class="barra-pdf"></div><div class="barra-pdf fina"></div>
            </div>
            <div class="codigo-numerico">99999.99999 99999.999999 99999.999999 9 99999999999999</div>
        </div>
        <div class="aviso">
            <strong>ATENÇÃO:</strong> Este é um boleto de simulação e não possui valor real.<br>
            Não tente efetuar o pagamento em bancos ou casas lotéricas.
        </div>
    </div>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>`;
    
    pdfWindow.document.write(htmlContent);
    pdfWindow.document.close();
    
    // Confirmar pagamento após gerar PDF
    setTimeout(function() {
        confirmarPagamento('boleto');
    }, 1000);
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Formatação automática do cartão
document.addEventListener('input', function(e) {
    if (e.target.name === 'numero') {
        var value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
        var groups = value.match(/.{1,4}/g);
        var formattedValue = groups ? groups.join(' ') : value;
        e.target.value = formattedValue;
    }
    
    if (e.target.name === 'validade') {
        var value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    }
});

// Garantir que os botões funcionem após carregamento da página
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando eventos de pagamento...');
    
    // Verificar se elementos essenciais existem
    const modalPagamento = document.getElementById('modalPagamento');
    const modalCadastro = document.getElementById('modalCadastro');
    
    if (!modalPagamento) {
        console.error('Modal de pagamento não encontrado!');
    }
    if (!modalCadastro) {
        console.error('Modal de cadastro não encontrado!');
    }
    
    // Adicionar event listeners para botões de pagar
    const pagarButtons = document.querySelectorAll('button[onclick*="abrirModalPagamento"]');
    console.log('Botões de pagar encontrados:', pagarButtons.length);
    
    pagarButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const onclick = this.getAttribute('onclick');
            if (onclick) {
                try {
                    eval(onclick);
                } catch (error) {
                    console.error('Erro ao executar onclick:', error);
                }
            }
        });
    });
    
    // Adicionar event listeners para opções de pagamento
    const paymentOptions = document.querySelectorAll('.payment-option');
    console.log('Opções de pagamento encontradas:', paymentOptions.length);
    
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            const onclick = this.getAttribute('onclick');
            if (onclick) {
                try {
                    eval(onclick);
                } catch (error) {
                    console.error('Erro ao executar onclick da opção:', error);
                }
            }
        });
    });
    
    // Verificar se variáveis globais estão definidas
    if (typeof pagamentoAtual === 'undefined') {
        window.pagamentoAtual = null;
    }
    if (typeof metodoSelecionado === 'undefined') {
        window.metodoSelecionado = null;
    }
    
    console.log('Inicialização de eventos concluída.');
});
</script>

<?php include 'footer.php'; ?>