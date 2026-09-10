<?php
require_once 'config.php';
require_once 'header.php';
verificarLogin();

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Processar cadastro de método de pagamento
if ($_POST && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'cadastrar_cartao') {
        // Verificar limite de cartões
        $count_stmt = $conexao->prepare("SELECT COUNT(*) as total FROM cartoes_usuario WHERE usuario_id = ?");
        $count_stmt->bind_param("i", $_SESSION['usuario_id']);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result()->fetch_assoc();
        
        if ($count_result['total'] >= 7) {
            exibirAlerta('error', 'Limite máximo de 7 cartões atingido!');
        } else {
            $numero = isset($_POST['numero']) ? limparDados($_POST['numero']) : '';
            $nome = isset($_POST['nome']) ? limparDados($_POST['nome']) : '';
            $validade = isset($_POST['validade']) ? limparDados($_POST['validade']) : '';
            $tipo = isset($_POST['tipo']) ? limparDados($_POST['tipo']) : '';
            
            if ($numero && $nome && $validade && $tipo) {
                // Verificar se o cartão já existe
                $check_stmt = $conexao->prepare("SELECT COUNT(*) as existe FROM cartoes_usuario WHERE usuario_id = ? AND numero = ?");
                $check_stmt->bind_param("is", $_SESSION['usuario_id'], $numero);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result()->fetch_assoc();
                
                if ($check_result['existe'] > 0) {
                    exibirAlerta('error', 'Este cartão já foi cadastrado!');
                } else {
                    $stmt = $conexao->prepare("INSERT INTO cartoes_usuario (usuario_id, numero, nome, validade, tipo) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $_SESSION['usuario_id'], $numero, $nome, $validade, $tipo);
                    
                    if ($stmt->execute()) {
                        exibirAlerta('success', 'Cartão cadastrado com sucesso!');
                    } else {
                        exibirAlerta('error', 'Erro ao cadastrar cartão.');
                    }
                }
            } else {
                exibirAlerta('error', 'Preencha todos os campos obrigatórios.');
            }
        }
    } elseif ($_POST['acao'] == 'excluir_cartao') {
        $cartao_id = intval($_POST['cartao_id']);
        
        $stmt = $conexao->prepare("DELETE FROM cartoes_usuario WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $cartao_id, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            exibirAlerta('success', 'Cartão excluído com sucesso!');
        } else {
            exibirAlerta('error', 'Erro ao excluir cartão.');
        }
    }
}

// Buscar cartões cadastrados
$cartoes_query = $conexao->prepare("SELECT id, usuario_id, numero, nome, validade, tipo FROM cartoes_usuario WHERE usuario_id = ? ORDER BY id DESC");
$cartoes_query->bind_param("i", $_SESSION['usuario_id']);
$cartoes_query->execute();
$cartoes_cadastrados = $cartoes_query->get_result();

// Buscar pagamentos do usuário
$pagamentos = $conexao->prepare("SELECT * FROM pagamentos WHERE usuario_id = ? ORDER BY data_criacao DESC");
$pagamentos->bind_param("i", $_SESSION['usuario_id']);
$pagamentos->execute();
$meus_pagamentos = $pagamentos->get_result();

$titulo = "Pagamentos";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos - FullTorque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .payment-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            color: white;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #667eea;
            font-size: 1.3rem;
        }

        .add-card-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-card-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .cards-container {
            display: grid;
            gap: 20px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .credit-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .credit-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .credit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .credit-card[data-tipo="credito"] {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .credit-card[data-tipo="debito"] {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .card-chip {
            width: 40px;
            height: 28px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .card-number {
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 3px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
        }

        .card-info {
            display: flex;
            justify-content: space-between;
            align-items: end;
        }

        .card-holder {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            font-weight: 500;
        }

        .card-brand {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .card-delete {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .credit-card:hover .card-delete {
            opacity: 1;
        }

        .card-delete:hover {
            background: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .payment-method {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .payment-method:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
        }

        .payment-method.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .method-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 15px;
            color: white;
        }

        .pix .method-icon { background: linear-gradient(135deg, #32BCAD, #28a99c); }
        .boleto .method-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .cartao .method-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
        .digital .method-icon { background: linear-gradient(135deg, #ff6b6b, #feca57); }

        .method-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .method-desc {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .method-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .instant { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .normal { background: rgba(243, 156, 18, 0.1); color: #f39c12; }

        .payments-history {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .payment-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .payment-item:hover {
            background: rgba(102, 126, 234, 0.05);
            margin: 0 -30px;
            padding: 20px 30px;
            border-radius: 12px;
        }

        .payment-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 1.2rem;
        }

        .payment-icon.success { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .payment-icon.pending { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .payment-icon.rejected { background: linear-gradient(135deg, #e74c3c, #c0392b); }

        .payment-details {
            flex: 1;
        }

        .payment-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .payment-date {
            font-size: 0.9rem;
            color: #666;
        }

        .payment-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 30px;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .card-preview {
            margin: 20px 0;
            transform: scale(0.8);
            transform-origin: left;
        }

        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-container {
                padding: 15px;
            }

            .hero-section {
                padding: 25px;
            }

            .card-section {
                padding: 20px;
            }
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="hero-title">💳 Pagamentos</h1>
            <p class="hero-subtitle">Gerencie seus cartões e métodos de pagamento de forma segura e moderna</p>
        </div>

        <?php mostrarAlerta(); ?>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Cartões Section -->
            <div class="card-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-wallet"></i>
                        Meus Cartões
                    </h2>
                    <button class="add-card-btn" onclick="openModal('cardModal')">
                        <i class="fas fa-plus"></i>
                        Adicionar
                    </button>
                </div>

                <div class="cards-container">
                    <?php if ($cartoes_cadastrados->num_rows > 0): ?>
                        <?php while ($cartao = $cartoes_cadastrados->fetch_assoc()): ?>
                            <div class="credit-card" data-tipo="<?php echo $cartao['tipo']; ?>">
                                <button class="card-delete" onclick="deleteCard(<?php echo $cartao['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="card-chip"></div>
                                <div class="card-number">**** **** **** <?php echo substr($cartao['numero'], -4); ?></div>
                                <div class="card-info">
                                    <div>
                                        <div class="card-holder"><?php echo htmlspecialchars(substr($cartao['nome'], 0, 20)); ?></div>
                                        <div style="font-size: 0.8rem; opacity: 0.8;"><?php echo $cartao['validade']; ?></div>
                                    </div>
                                    <div class="card-brand"><?php 
                                        $numero = $cartao['numero'];
                                        $primeiro_digito = substr($numero, 0, 1);
                                        $primeiros_dois = substr($numero, 0, 2);
                                        
                                        if ($primeiro_digito == '4') {
                                            echo 'VISA';
                                        } elseif (in_array($primeiros_dois, ['51', '52', '53', '54', '55'])) {
                                            echo 'MASTERCARD';
                                        } elseif (in_array($primeiros_dois, ['50', '63', '64', '65', '66', '67'])) {
                                            echo 'ELO';
                                        } elseif ($primeiros_dois == '60') {
                                            echo 'HIPERCARD';
                                        } elseif (in_array($primeiros_dois, ['34', '37'])) {
                                            echo 'AMEX';
                                        } else {
                                            echo strtoupper($cartao['tipo']);
                                        }
                                    ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-credit-card"></i>
                            <h3>Nenhum cartão cadastrado</h3>
                            <p>Adicione seu primeiro cartão para começar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico Section -->
            <div class="card-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        Últimos Pagamentos
                    </h2>
                </div>

                <div class="cards-container">
                    <?php if ($meus_pagamentos->num_rows > 0): ?>
                        <?php 
                        $count = 0;
                        while ($pagamento = $meus_pagamentos->fetch_assoc() && $count < 5): 
                            $count++;
                            $status_class = $pagamento['status'] == 'aprovado' ? 'success' : ($pagamento['status'] == 'pendente' ? 'pending' : 'rejected');
                            $status_icon = $pagamento['status'] == 'aprovado' ? 'fas fa-check' : ($pagamento['status'] == 'pendente' ? 'fas fa-clock' : 'fas fa-times');
                        ?>
                        <div class="payment-item">
                            <div class="payment-icon <?php echo $status_class; ?>">
                                <i class="<?php echo $status_icon; ?>"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-title"><?php echo htmlspecialchars(substr($pagamento['descricao'] ?: 'Pagamento de serviço', 0, 30)); ?></div>
                                <div class="payment-date"><?php echo date('d/m/Y H:i', strtotime($pagamento['data_criacao'])); ?></div>
                            </div>
                            <div class="payment-amount">R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Dados fictícios para demonstração -->
                        <div class="payment-item">
                            <div class="payment-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-title">Troca de óleo e filtros</div>
                                <div class="payment-date">15/12/2024 14:30</div>
                            </div>
                            <div class="payment-amount">R$ 180,00</div>
                        </div>
                        <div class="payment-item">
                            <div class="payment-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-title">Revisão completa</div>
                                <div class="payment-date">10/12/2024 09:15</div>
                            </div>
                            <div class="payment-amount">R$ 450,00</div>
                        </div>
                        <div class="payment-item">
                            <div class="payment-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-title">Alinhamento</div>
                                <div class="payment-date">08/12/2024 16:45</div>
                            </div>
                            <div class="payment-amount">R$ 120,00</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Métodos de Pagamento -->
        <div class="payments-history">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    Métodos de Pagamento Disponíveis
                </h2>
            </div>

            <div class="payment-methods">
                <div class="payment-method pix" onclick="selectPaymentMethod('pix')">
                    <div class="method-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="method-title">PIX</div>
                    <div class="method-desc">Pagamento instantâneo</div>
                    <span class="method-badge instant">Instantâneo</span>
                </div>

                <div class="payment-method boleto" onclick="selectPaymentMethod('boleto')">
                    <div class="method-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <div class="method-title">Boleto</div>
                    <div class="method-desc">Vencimento em 3 dias</div>
                    <span class="method-badge normal">3 dias</span>
                </div>

                <div class="payment-method cartao" onclick="selectPaymentMethod('cartao')">
                    <div class="method-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="method-title">Cartão</div>
                    <div class="method-desc">Crédito ou débito</div>
                    <span class="method-badge instant">Instantâneo</span>
                </div>

                <div class="payment-method digital" onclick="selectPaymentMethod('digital')">
                    <div class="method-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="method-title">Digital</div>
                    <div class="method-desc">Apple Pay, Google Pay</div>
                    <span class="method-badge instant">Instantâneo</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Cartão -->
    <div id="cardModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-plus"></i>
                    Adicionar Novo Cartão
                </h3>
                <button class="close" onclick="closeModal('cardModal')">&times;</button>
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
                        <select name="tipo" required>
                            <option value="">Selecione o tipo</option>
                            <option value="credito">Crédito</option>
                            <option value="debito">Débito</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Cadastrar Cartão
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteCard(cardId) {
            if (confirm('Tem certeza que deseja excluir este cartão?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir_cartao">
                    <input type="hidden" name="cartao_id" value="${cardId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function selectPaymentMethod(method) {
            // Remove seleção anterior
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Adiciona seleção atual
            event.target.closest('.payment-method').classList.add('selected');
            
            // Aqui você pode adicionar lógica para processar o método selecionado
            console.log('Método selecionado:', method);
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Formatação automática dos campos
        document.addEventListener('input', function(e) {
            if (e.target.name === 'numero') {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let groups = value.match(/.{1,4}/g);
                let formattedValue = groups ? groups.join(' ') : value;
                e.target.value = formattedValue;
            }
            
            if (e.target.name === 'validade') {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            }
        });
    </script>
</body>
</html>