<?php
require_once 'config.php';

// Verificar se o link é válido
$token = $_GET['token'] ?? '';
$link_completo = "https://pagamento-fake.com/" . basename($_SERVER['REQUEST_URI']);

$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT p.*, u.nome as cliente_nome FROM pagamentos p LEFT JOIN usuarios u ON p.usuario_id = u.id WHERE p.link_pagamento LIKE ?");
$link_busca = '%' . $token . '%';
$stmt->bind_param("s", $link_busca);
$stmt->execute();
$pagamento = $stmt->get_result()->fetch_assoc();

if (!$pagamento) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-header">
                <h1><i class="fas fa-credit-card"></i> Pagamento</h1>
                <div class="payment-amount">R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="payment-info">
                <h3>Detalhes da Cobrança</h3>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($pagamento['descricao'] ?: 'Pagamento de serviço'); ?></p>
                <p><strong>Vencimento:</strong> <?php echo date('d/m/Y', strtotime($pagamento['data_vencimento'])); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pagamento['cliente_nome']); ?></p>
            </div>

            <div class="payment-methods">
                <h3>Escolha a forma de pagamento</h3>
                
                <div class="payment-options">
                    <button class="payment-btn pix" onclick="pagarPix()">
                        <i class="fas fa-qrcode"></i>
                        <span>PIX</span>
                        <small>Aprovação imediata</small>
                    </button>
                    
                    <button class="payment-btn boleto" onclick="pagarBoleto()">
                        <i class="fas fa-barcode"></i>
                        <span>Boleto</span>
                        <small>Vence em 3 dias úteis</small>
                    </button>
                    
                    <button class="payment-btn cartao" onclick="pagarCartao()">
                        <i class="fas fa-credit-card"></i>
                        <span>Cartão de Crédito</span>
                        <small>Parcelamento disponível</small>
                    </button>
                    
                    <button class="payment-btn debito" onclick="pagarDebito()">
                        <i class="fas fa-credit-card"></i>
                        <span>Cartão de Débito</span>
                        <small>Aprovação imediata</small>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal PIX -->
    <div id="modalPix" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-qrcode"></i> Pagamento via PIX</h3>
                <span class="close" onclick="fecharModal('modalPix')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="qr-code">
                    <div class="fake-qr">
                        <i class="fas fa-qrcode fa-5x"></i>
                        <p>QR Code Simulado</p>
                    </div>
                </div>
                <div class="pix-info">
                    <p><strong>Chave PIX:</strong> pagamento@fake.com</p>
                    <p><strong>Valor:</strong> R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></p>
                    <button class="btn-copy" onclick="copiarChave()">Copiar Chave PIX</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Boleto -->
    <div id="modalBoleto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-barcode"></i> Boleto Bancário</h3>
                <span class="close" onclick="fecharModal('modalBoleto')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="boleto-info">
                    <p><strong>Código de Barras:</strong></p>
                    <div class="codigo-barras">12345.67890 12345.678901 12345.678901 1 23456789012345</div>
                    <p><strong>Vencimento:</strong> <?php echo date('d/m/Y', strtotime($pagamento['data_vencimento'] . ' +3 days')); ?></p>
                    <p><strong>Valor:</strong> R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></p>
                    <button class="btn-download" onclick="baixarBoleto()">Baixar Boleto PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cartão -->
    <div id="modalCartao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-credit-card"></i> Cartão de Crédito</h3>
                <span class="close" onclick="fecharModal('modalCartao')">&times;</span>
            </div>
            <div class="modal-body">
                <form class="cartao-form">
                    <div class="form-group">
                        <label>Número do Cartão</label>
                        <input type="text" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Validade</label>
                            <input type="text" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" placeholder="000" maxlength="3">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nome no Cartão</label>
                        <input type="text" placeholder="Nome como está no cartão">
                    </div>
                    <div class="form-group">
                        <label>Parcelas</label>
                        <select>
                            <option>1x de R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?> sem juros</option>
                            <option>2x de R$ <?php echo number_format($pagamento['valor']/2, 2, ',', '.'); ?> sem juros</option>
                            <option>3x de R$ <?php echo number_format($pagamento['valor']/3, 2, ',', '.'); ?> sem juros</option>
                        </select>
                    </div>
                    <button type="button" class="btn-pagar" onclick="processarCartao()">Pagar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function pagarPix() {
        document.getElementById('modalPix').style.display = 'block';
    }

    function pagarBoleto() {
        document.getElementById('modalBoleto').style.display = 'block';
    }

    function pagarCartao() {
        document.getElementById('modalCartao').style.display = 'block';
    }

    function pagarDebito() {
        alert('Redirecionando para o ambiente seguro do banco...\n(Simulação)');
        setTimeout(() => {
            alert('Pagamento processado com sucesso!\n(Simulação)');
        }, 2000);
    }

    function fecharModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function copiarChave() {
        navigator.clipboard.writeText('pagamento@fake.com');
        alert('Chave PIX copiada!');
    }

    function baixarBoleto() {
        alert('Boleto seria baixado em PDF\n(Simulação)');
    }

    function processarCartao() {
        alert('Processando pagamento...\n(Simulação)');
        setTimeout(() => {
            alert('Pagamento aprovado!\n(Simulação)');
            fecharModal('modalCartao');
        }, 2000);
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    </script>

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .payment-container {
        max-width: 600px;
        width: 100%;
    }

    .payment-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .payment-header {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .payment-header h1 {
        margin: 0 0 15px 0;
        font-size: 1.8rem;
    }

    .payment-amount {
        font-size: 3rem;
        font-weight: bold;
        margin: 0;
    }

    .payment-info {
        padding: 30px;
        border-bottom: 1px solid #eee;
    }

    .payment-info h3 {
        margin: 0 0 15px 0;
        color: #333;
    }

    .payment-info p {
        margin: 8px 0;
        color: #666;
    }

    .payment-methods {
        padding: 30px;
    }

    .payment-methods h3 {
        margin: 0 0 20px 0;
        color: #333;
        text-align: center;
    }

    .payment-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .payment-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 25px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        color: #333;
    }

    .payment-btn:hover {
        border-color: #3498db;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .payment-btn i {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .payment-btn span {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .payment-btn small {
        color: #666;
        font-size: 0.85rem;
    }

    .payment-btn.pix i { color: #00d4aa; }
    .payment-btn.boleto i { color: #ff6b35; }
    .payment-btn.cartao i { color: #4a90e2; }
    .payment-btn.debito i { color: #7b68ee; }

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
        border-radius: 10px;
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
        border-radius: 10px 10px 0 0;
    }

    .modal-body {
        padding: 30px;
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

    .fake-qr {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .fake-qr i {
        color: #333;
        margin-bottom: 10px;
    }

    .pix-info {
        text-align: center;
    }

    .btn-copy, .btn-download, .btn-pagar {
        background: #3498db;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        margin-top: 15px;
        width: 100%;
    }

    .btn-copy:hover, .btn-download:hover, .btn-pagar:hover {
        background: #2980b9;
    }

    .codigo-barras {
        font-family: monospace;
        font-size: 1.2rem;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
        text-align: center;
        letter-spacing: 2px;
    }

    .cartao-form {
        max-width: 400px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
    }

    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    @media (max-width: 768px) {
        .payment-options {
            grid-template-columns: 1fr;
        }
        
        .payment-amount {
            font-size: 2.5rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>