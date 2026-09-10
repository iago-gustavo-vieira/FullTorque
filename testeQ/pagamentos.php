<?php
ob_start();
require_once 'header.php';
verificarLogin();
require_once 'config.php';

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Processar remoção de cartão
if ($_POST && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'remover_cartao') {
        $cartao_id = intval($_POST['cartao_id']);
        
        $stmt = $conexao->prepare("DELETE FROM cartoes_usuario WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $cartao_id, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            exibirAlerta('success', 'Cartão removido com sucesso!');
        } else {
            exibirAlerta('error', 'Erro ao remover cartão.');
        }
    } elseif ($_POST['acao'] == 'cadastrar_cartao') {
        // Verificar limite de cartões
        $count_query = $conexao->prepare("SELECT COUNT(*) as total FROM cartoes_usuario WHERE usuario_id = ?");
        $count_query->bind_param("i", $_SESSION['usuario_id']);
        $count_query->execute();
        $count_result = $count_query->get_result()->fetch_assoc();
        
        if ($count_result['total'] >= 5) {
            exibirAlerta('error', 'Limite máximo de 5 cartões atingido.');
        } else {
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
        }
    } elseif ($_POST['acao'] == 'processar_pagamento') {
        $pagamento_id = intval($_POST['pagamento_id']);
        
        $stmt = $conexao->prepare("UPDATE pagamentos SET status = 'aprovado', data_pagamento = NOW(), data_atualizacao = NOW() WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $pagamento_id, $_SESSION['usuario_id']);
        
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

// Se não há pagamentos, criar alguns de teste (apenas se o usuário existir)
if ($meus_pagamentos->num_rows == 0) {
    // Verificar se o usuário existe
    $user_check = $conexao->prepare("SELECT id FROM usuarios WHERE id = ?");
    $user_check->bind_param("i", $_SESSION['usuario_id']);
    $user_check->execute();
    
    if ($user_check->get_result()->num_rows > 0) {
        $test_payments = [
            ['Serviço de Manutenção', 150.00, 'pendente'],
            ['Troca de Óleo', 80.00, 'pendente'],
            ['Revisão Completa', 300.00, 'aprovado']
        ];
        
        foreach ($test_payments as $payment) {
            $stmt = $conexao->prepare("INSERT INTO pagamentos (usuario_id, descricao, valor, status, data_vencimento, data_criacao) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW())");
            $stmt->bind_param("isds", $_SESSION['usuario_id'], $payment[0], $payment[1], $payment[2]);
            $stmt->execute();
        }
        
        // Recarregar pagamentos
        $pagamentos = $conexao->prepare("SELECT * FROM pagamentos WHERE usuario_id = ? ORDER BY data_criacao DESC");
        $pagamentos->bind_param("i", $_SESSION['usuario_id']);
        $pagamentos->execute();
        $meus_pagamentos = $pagamentos->get_result();
    }
}

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

?>



<style>
.payment-container {
    max-width: 1400px;
    margin: 0 auto;
}

.stats-section {
    margin-bottom: 30px;
}

.main-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.left-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.right-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tutorial-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.step-number {
    background: #28a745;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.1rem;
}

.step-content p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.tip {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #28a745;
}

.tip i {
    color: #28a745;
    font-size: 1.2rem;
    margin-top: 2px;
    flex-shrink: 0;
}

.tip strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.tip p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
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
    background: linear-gradient(135deg, #28a745, #155724);
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
    overflow: hidden;
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

.btn {
    background: #28a745;
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
    background: #155724;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
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
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
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
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.wallet-cards {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cartao-item {
    background: white;
    color: #333;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    position: relative;
    border-left: 4px solid #34c2dbff;
    min-height: 120px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.cartao-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.cartao-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.cartao-logo {
    flex-shrink: 0;
}

.logo-bandeira {
    width: 60px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.7rem;
    color: #333;
    text-align: center;
    line-height: 1.2;
    position: relative;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.logo-bandeira.visa {
    background: linear-gradient(135deg, #1a1f71, #0f4c81);
    color: white;
}

.logo-bandeira.visa::before {
    content: 'VISA';
    font-weight: 900;
    font-size: 1.1rem;
    letter-spacing: 2px;
}

.logo-bandeira.mastercard {
    background: linear-gradient(135deg, #eb001b, #f79e1b);
    color: white;
}

.logo-bandeira.mastercard::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #eb001b;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
}

.logo-bandeira.mastercard::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #f79e1b;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.9;
}

.logo-bandeira.elo {
    background: linear-gradient(135deg, #ffcb05, #ff6900);
    color: white;
}

.logo-bandeira.elo::before {
    content: 'elo';
    font-weight: 900;
    font-size: 1.2rem;
    text-transform: lowercase;
    font-style: italic;
}

.logo-bandeira.amex {
    background: linear-gradient(135deg, #006fcf, #0077be);
    color: white;
}

.logo-bandeira.amex::before {
    content: 'AMEX';
    font-weight: 900;
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.logo-bandeira.hipercard {
    background: linear-gradient(135deg, #d50000, #ff1744);
    color: white;
}

.logo-bandeira.hipercard::before {
    content: 'HIPER';
    font-weight: 900;
    font-size: 0.8rem;
    letter-spacing: 1px;
}

.logo-bandeira.diners {
    background: linear-gradient(135deg, #004b87, #0066cc);
    color: white;
}

.logo-bandeira.diners::before {
    content: 'DINERS';
    font-weight: 900;
    font-size: 0.7rem;
    letter-spacing: 1px;
}

.logo-bandeira.discover {
    background: linear-gradient(135deg, #ff6000, #ff9500);
    color: white;
}

.logo-bandeira.discover::before {
    content: 'DISCOVER';
    font-weight: 900;
    font-size: 0.6rem;
    letter-spacing: 0.5px;
}

.cartao-info {
    flex: 1;
}

.cartao-numero {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 2px;
    color: #2c3e50;
}

.cartao-nome {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.cartao-detalhes {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.cartao-instituicao {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cartao-bandeira {
    font-weight: 600;
    font-size: 0.85rem;
    color: #34c2dbff;
}

.cartao-banco {
    font-size: 0.8rem;
    color: #666;
}

.cartao-extras {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-end;
}

.cartao-tipo {
    background: #34c2dbff;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.cartao-validade {
    font-size: 0.85rem;
    color: #666;
}

.cartao-acoes {
    position: absolute;
    top: 20px;
    right: 20px;
}

.btn-remove {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.btn-remove:hover {
    background: #c0392b;
    transform: translateY(-1px);
}

.cartao-bandeira {
    font-weight: bold;
    font-size: 0.8rem;
}

.cartao-banco {
    font-size: 0.7rem;
    opacity: 0.8;
}

/* Cores das bandeiras - padrão do sistema */
.visa .cartao-item { border-left-color: #1a1f71; }
.mastercard .cartao-item { border-left-color: #eb001b; }
.elo .cartao-item { border-left-color: #ffcb05; }
.amex .cartao-item { border-left-color: #006fcf; }
.hipercard .cartao-item { border-left-color: #d50000; }
.diners .cartao-item { border-left-color: #004b87; }
.discover .cartao-item { border-left-color: #ff6000; }

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
    border-color: #28a745;
    background: #f8fff8;
}

.option-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.option-info i {
    font-size: 1.5rem;
    color: #28a745;
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

.dados-transferencia {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
    border: 1px solid #dee2e6;
}

.dado-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.dado-item:last-child {
    border-bottom: none;
}

.dado-item strong {
    color: #495057;
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

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #28a745;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #28a745;
}

.icon-line {
    height: 5px;
    background-color: #28a745;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(40, 167, 69, 0.5);
}

.icon-fix {
    top: 8px;
    width: 5px;
    left: 26px;
    z-index: 1;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #fff;
}

@keyframes icon-line-tip {
    0% { width: 0; left: 1px; top: 19px; }
    54% { width: 0; left: 1px; top: 19px; }
    70% { width: 50px; left: -8px; top: 37px; }
    84% { width: 17px; left: 21px; top: 48px; }
    100% { width: 25px; left: 14px; top: 45px; }
}

@keyframes icon-line-long {
    0% { width: 0; right: 46px; top: 54px; }
    65% { width: 0; right: 46px; top: 54px; }
    84% { width: 55px; right: 0px; top: 35px; }
    100% { width: 47px; right: 8px; top: 38px; }
}

.historico-container {
    max-height: 650px;
    overflow-y: auto;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.historico-container .table {
    margin: 0;
}

.historico-container .table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 10;
    border-bottom: 2px solid #dee2e6;
}

.payment-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.payment-method-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.payment-method-card:hover {
    transform: translateY(-5px);
    border-color: #28a745;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
}

.method-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.8rem;
    color: white;
}

.pix-color { background: linear-gradient(135deg, #32BCAD, #28a085); }
.boleto-color { background: linear-gradient(135deg, #FF6B35, #e74c3c); }
.transfer-color { background: linear-gradient(135deg, #4A90E2, #3498db); }
.paypal-color { background: linear-gradient(135deg, #0070BA, #005ea6); }
.mercadopago-color { background: linear-gradient(135deg, #00B1EA, #0099cc); }
.picpay-color { background: linear-gradient(135deg, #21C25E, #27ae60); }

.method-info h4 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.1rem;
}

.method-info p {
    margin: 0 0 5px 0;
    color: #666;
    font-size: 0.9rem;
}

.method-info small {
    color: #999;
    font-size: 0.8rem;
}

.payment-info {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    color: #666;
}

.payment-info i {
    color: #28a745;
    margin-right: 8px;
}

/* Tema Alemanha */
.theme-alemanha .stat-card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.2);
}

.theme-alemanha .stat-card.success .stat-icon { color: #FFCE00; }
.theme-alemanha .stat-card.warning .stat-icon { color: #FFCE00; }
.theme-alemanha .stat-card.danger .stat-icon { color: #DD0100; }

.theme-alemanha .stat-content h3 { color: #ccc; }
.theme-alemanha .stat-content p { color: white; }

.theme-alemanha .card {
    background: #1a1a1a;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.2);
}

.theme-alemanha .card-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.theme-alemanha .card-body {
    background: #1a1a1a;
    color: white;
}

.theme-alemanha .cobrancas-destaque {
    background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
    border-color: #FFCE00;
}

.theme-alemanha .alert-header h3 { color: #FFCE00; }

.theme-alemanha .cobranca-item {
    background: #2a2a2a;
    border-left-color: #FFCE00;
    color: white;
}

.theme-alemanha .cobranca-info strong { color: white; }
.theme-alemanha .cobranca-info .valor { background: #FFCE00; color: #000; }

.theme-alemanha .btn {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn:hover {
    background: #e6b800;
}

.theme-alemanha .form-group label { color: #FFCE00; }

.theme-alemanha .form-group input,
.theme-alemanha .form-group select {
    background: #2a2a2a;
    border-color: #444;
    color: white;
}

.theme-alemanha .form-group input:focus,
.theme-alemanha .form-group select:focus {
    border-color: #FFCE00;
    box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.1);
}

.theme-alemanha .table th {
    background: #2a2a2a;
    color: #FFCE00;
    border-bottom-color: #444;
}

.theme-alemanha .card-body .table th {
    background: #2a2a2a !important;
    color: #FFCE00 !important;
    border-bottom-color: #444 !important;
}

.theme-alemanha .table td {
    color: white;
    border-bottom-color: #333;
}

.theme-alemanha .badge.success { background: rgba(255, 206, 0, 0.2); color: #FFCE00; }
.theme-alemanha .badge.warning { background: rgba(255, 206, 0, 0.2); color: #FFCE00; }
.theme-alemanha .badge.danger { background: rgba(221, 1, 0, 0.2); color: #DD0100; }

.theme-alemanha .cartoes-grid {
    background: #1a1a1a;
}

.theme-alemanha .cartao-item {
    background: #2a2a2a;
    color: white;
    border-left-color: #FFCE00;
}

.theme-alemanha .cartao-numero { color: white; }
.theme-alemanha .cartao-nome { color: #ccc; }
.theme-alemanha .cartao-bandeira { color: #FFCE00; }
.theme-alemanha .cartao-banco { color: #999; }
.theme-alemanha .cartao-tipo { background: #FFCE00; color: #000; }
.theme-alemanha .cartao-validade { color: #999; }

.theme-alemanha .btn-remove {
    background: #DD0100;
}

.theme-alemanha .btn-remove:hover {
    background: #c00;
}

.theme-alemanha .payment-method-card {
    background: #2a2a2a;
    border-color: #444;
    color: white;
}

.theme-alemanha .payment-method-card:hover {
    border-color: #FFCE00;
    box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3);
}

.theme-alemanha .method-info h4 { color: white; }
.theme-alemanha .method-info p { color: #ccc; }
.theme-alemanha .method-info small { color: #999; }

.theme-alemanha .payment-info {
    background: #2a2a2a;
    color: #ccc;
}

.theme-alemanha .payment-info i { color: #FFCE00; }

.theme-alemanha .modal-content {
    background: #1a1a1a;
    color: white;
}

.theme-alemanha .modal-header {
    background: #2a2a2a;
    border-bottom-color: #444;
}

.theme-alemanha .modal-body {
    background: #1a1a1a;
}

.theme-alemanha .payment-option {
    background: #2a2a2a;
    border-color: #444;
}

.theme-alemanha .payment-option:hover {
    border-color: #FFCE00;
    background: #333;
}

.theme-alemanha .qr-code {
    background: #2a2a2a;
}

.theme-alemanha .dados-transferencia {
    background: #2a2a2a;
    border-color: #444;
}

.theme-alemanha .dado-item {
    border-bottom-color: #444;
}

.theme-alemanha .dado-item strong { color: #FFCE00; }

.theme-alemanha .codigo-barras-visual {
    background: #2a2a2a;
    border-color: #444;
}

.theme-alemanha .close { color: #999; }
.theme-alemanha .close:hover { color: #FFCE00; }

.theme-alemanha .spinner {
    border-top-color: #FFCE00;
}

.theme-alemanha .check-icon {
    border-color: #FFCE00;
}

.theme-alemanha .icon-line {
    background-color: #FFCE00;
}

.theme-alemanha .icon-circle {
    border-color: rgba(255, 206, 0, 0.5);
}

.theme-alemanha #modalSucesso h2 {
    color: #FFCE00;
}

.theme-alemanha .historico-container {
    border-color: #444;
}

.theme-alemanha .tip {
    background: #2a2a2a;
    border-left-color: #FFCE00;
}

.theme-alemanha .tip i { color: #FFCE00; }
.theme-alemanha .tip strong { color: white; }
.theme-alemanha .tip p { color: #ccc; }

.theme-alemanha .step-number {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .step-content h4 { color: white; }
.theme-alemanha .step-content p { color: #ccc; }

@media (max-width: 768px) {
    .main-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .payment-methods-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .payment-method-card {
        padding: 15px;
    }
    
    .method-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .cobranca-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-container {
        padding: 0 10px;
        overflow-x: hidden;
    }
    
    .card {
        margin-left: 0;
        margin-right: 0;
    }
    
    .cartoes-grid {
        padding: 10px;
        overflow-x: hidden;
    }
    
    .wallet-cards {
        width: 100%;
        overflow-x: hidden;
    }
    
    .cartao-item {
        padding: 12px;
        gap: 10px;
        min-height: auto;
        width: 100%;
        max-width: 100%;
        margin: 0;
        overflow: hidden;
    }
    
    .cartao-logo {
        flex-shrink: 0;
    }
    
    .logo-bandeira {
        width: 50px;
        height: 35px;
        font-size: 0.6rem;
    }
    
    .cartao-info {
        flex: 1;
        min-width: 0;
    }
    
    .cartao-numero {
        font-size: 1rem;
        letter-spacing: 1px;
        word-break: break-all;
    }
    
    .cartao-nome {
        font-size: 0.85rem;
    }
    
    .cartao-detalhes {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .cartao-instituicao,
    .cartao-extras {
        align-items: flex-start;
    }
    
    .cartao-bandeira,
    .cartao-banco,
    .cartao-tipo,
    .cartao-validade {
        font-size: 0.75rem;
    }
    
    .cartao-acoes {
        position: static;
        margin-left: auto;
    }
    
    .btn-remove {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }
    
    .historico-container {
        max-height: 500px;
    }
    
    .card-body {
        padding: 15px;
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
    <div class="stats-section">
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
    </div>

    <!-- Métodos de Pagamento Disponíveis -->
    <?php if ($stats['total_pendente'] > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Métodos de Pagamento Disponíveis</h2>
        </div>
        <div class="card-body">
            <div class="payment-methods-grid">
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('pix'), 100);">
                    <div class="method-icon pix-color">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="method-info">
                        <h4>PIX</h4>
                        <p>Pagamento instantâneo</p>
                        <small>Disponível 24h</small>
                    </div>
                </div>
                
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('boleto'), 100);">
                    <div class="method-icon boleto-color">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <div class="method-info">
                        <h4>Boleto Bancário</h4>
                        <p>Pagamento tradicional</p>
                        <small>Vencimento em 3 dias</small>
                    </div>
                </div>
                
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('transferencia'), 100);">
                    <div class="method-icon transfer-color">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="method-info">
                        <h4>Transferência</h4>
                        <p>TED/DOC</p>
                        <small>1 dia útil</small>
                    </div>
                </div>
                
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('paypal'), 100);">
                    <div class="method-icon paypal-color">
                        <i class="fab fa-paypal"></i>
                    </div>
                    <div class="method-info">
                        <h4>PayPal</h4>
                        <p>Pagamento internacional</p>
                        <small>Seguro e protegido</small>
                    </div>
                </div>
                
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('mercadopago'), 100);">
                    <div class="method-icon mercadopago-color">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="method-info">
                        <h4>Mercado Pago</h4>
                        <p>Carteira digital</p>
                        <small>Aprovação imediata</small>
                    </div>
                </div>
                
                <div class="payment-method-card" onclick="abrirModalPagamento(1, 100); setTimeout(() => selecionarPagamento('picpay'), 100);">
                    <div class="method-icon picpay-color">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="method-info">
                        <h4>PicPay</h4>
                        <p>Pagamento mobile</p>
                        <small>Pelo celular</small>
                    </div>
                </div>
            </div>
            <div class="payment-info">
                <p><i class="fas fa-info-circle"></i> Clique em qualquer método para ver como funciona</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Layout Principal -->
    <div class="main-layout">
        <!-- Coluna Esquerda: Cartões -->
        <div class="left-column">

            <!-- Cartões Cadastrados -->
            <?php if ($cartoes_cadastrados->num_rows > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> Meus Cartões</h2>
                    <button onclick="verificarLimiteCartoes()" class="btn">
                        <i class="fas fa-plus"></i> Adicionar Cartão
                    </button>
                </div>
                <div class="card-body">
                    <div class="cartoes-grid">
                        <div class="wallet-cards">
                            <?php while ($cartao = $cartoes_cadastrados->fetch_assoc()): ?>
                            <div class="cartao-item" id="cartao-<?php echo $cartao['id']; ?>" data-numero="<?php echo $cartao['numero']; ?>">
                                <div class="cartao-logo">
                                    <div class="logo-bandeira"></div>
                                </div>
                                <div class="cartao-info">
                                    <div class="cartao-numero">**** **** **** <?php echo substr($cartao['numero'], -4); ?></div>
                                    <div class="cartao-nome"><?php echo htmlspecialchars($cartao['nome']); ?></div>
                                    <div class="cartao-detalhes">
                                        <div class="cartao-instituicao">
                                            <span class="cartao-bandeira"></span>
                                            <span class="cartao-banco"></span>
                                        </div>
                                        <div class="cartao-extras">
                                            <span class="cartao-tipo"><?php echo ucfirst($cartao['tipo']); ?></span>
                                            <span class="cartao-validade"><?php echo $cartao['validade']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="cartao-acoes">
                                    <button onclick="removerCartao(<?php echo $cartao['id']; ?>)" class="btn-remove" title="Remover cartão">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> Meus Cartões</h2>
                    <button onclick="verificarLimiteCartoes()" class="btn">
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
        </div>

        <!-- Coluna Direita: Histórico -->
        <div class="right-column">

            <!-- Lista de Pagamentos -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Histórico de Pagamentos</h2>
                </div>
                <div class="card-body">
                    <?php if ($meus_pagamentos->num_rows > 0): ?>
                    <div class="historico-container">
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
                                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                                <span style="color: #28a745; font-weight: bold;"><i class="fas fa-check-circle"></i> Pago</span>
                                                <a href="gerar-comprovante.php?pagamento_id=<?php echo $pagamento['id']; ?>" target="_blank" class="btn btn-sm" style="font-size: 0.8rem; padding: 5px 10px;">
                                                    <i class="fas fa-file-invoice"></i> Comprovante
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
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
                        <input type="text" name="validade" id="validadeInput" placeholder="MM/AA" maxlength="5" pattern="\d{2}/\d{2}" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" id="cvvInput" placeholder="000" maxlength="3" pattern="\d{3}" required>
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
                        <i class="fas fa-qrcode" style="color: #32BCAD;"></i>
                        <div>
                            <strong>PIX</strong>
                            <small>Pagamento instantâneo - Disponível 24h</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('boleto')">
                    <div class="option-info">
                        <i class="fas fa-barcode" style="color: #FF6B35;"></i>
                        <div>
                            <strong>Boleto Bancário</strong>
                            <small>Vencimento em 3 dias úteis</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('transferencia')">
                    <div class="option-info">
                        <i class="fas fa-university" style="color: #4A90E2;"></i>
                        <div>
                            <strong>Transferência Bancária</strong>
                            <small>TED/DOC - Processamento em 1 dia útil</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('paypal')">
                    <div class="option-info">
                        <i class="fab fa-paypal" style="color: #0070BA;"></i>
                        <div>
                            <strong>PayPal</strong>
                            <small>Pagamento seguro internacional</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('mercadopago')">
                    <div class="option-info">
                        <i class="fas fa-wallet" style="color: #00B1EA;"></i>
                        <div>
                            <strong>Mercado Pago</strong>
                            <small>Carteira digital - Aprovação imediata</small>
                        </div>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selecionarPagamento('picpay')">
                    <div class="option-info">
                        <i class="fas fa-mobile-alt" style="color: #21C25E;"></i>
                        <div>
                            <strong>PicPay</strong>
                            <small>Pagamento pelo celular</small>
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
                <button onclick="baixarBoletoPDF()" class="btn" style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-download"></i> Baixar Boleto PDF
                </button>
                <button onclick="copiarCodigoBarras()" class="btn" style="width: 100%; background: #6c757d;">
                    <i class="fas fa-copy"></i> Copiar Código de Barras
                </button>
            </div>
            
            <!-- Conteúdo Transferência -->
            <div id="transferenciaContent" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; text-align: center;">Dados para Transferência</h4>
                    <div class="dados-transferencia">
                        <div class="dado-item">
                            <strong>Banco:</strong> FullTorque Bank (999)
                        </div>
                        <div class="dado-item">
                            <strong>Agência:</strong> 1234-5
                        </div>
                        <div class="dado-item">
                            <strong>Conta:</strong> 12345678-9
                        </div>
                        <div class="dado-item">
                            <strong>CNPJ:</strong> 12.345.678/0001-99
                        </div>
                        <div class="dado-item">
                            <strong>Favorecido:</strong> FullTorque Ltda
                        </div>
                    </div>
                </div>
                <button onclick="copiarDadosTransferencia()" class="btn" style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-copy"></i> Copiar Dados
                </button>
                <button onclick="confirmarPagamento('transferencia')" class="btn" style="width: 100%; background: #28a745;">
                    <i class="fas fa-check"></i> Confirmar Transferência
                </button>
            </div>
            
            <!-- Conteúdo PayPal -->
            <div id="paypalContent" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fab fa-paypal" style="font-size: 4rem; color: #0070BA; margin-bottom: 15px;"></i>
                    <p>Você será redirecionado para o PayPal para completar o pagamento.</p>
                    <p><small>Pagamento seguro e protegido</small></p>
                </div>
                <button onclick="redirecionarPayPal()" class="btn" style="width: 100%; background: #0070BA;">
                    <i class="fab fa-paypal"></i> Pagar com PayPal
                </button>
            </div>
            
            <!-- Conteúdo Mercado Pago -->
            <div id="mercadopagoContent" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-wallet" style="font-size: 4rem; color: #00B1EA; margin-bottom: 15px;"></i>
                    <p>Pagamento rápido e seguro com Mercado Pago</p>
                    <p><small>Aprovação imediata</small></p>
                </div>
                <button onclick="redirecionarMercadoPago()" class="btn" style="width: 100%; background: #00B1EA;">
                    <i class="fas fa-wallet"></i> Pagar com Mercado Pago
                </button>
            </div>
            
            <!-- Conteúdo PicPay -->
            <div id="picpayContent" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-mobile-alt" style="font-size: 4rem; color: #21C25E; margin-bottom: 15px;"></i>
                    <p>Escaneie o QR Code com o app PicPay</p>
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode('PicPay - Valor: R$ 100,00'); ?>" alt="QR Code PicPay">
                    </div>
                </div>
                <button onclick="confirmarPagamento('picpay')" class="btn" style="width: 100%; background: #21C25E;">
                    <i class="fas fa-check"></i> Confirmar Pagamento PicPay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Pagamento -->
<div id="modalConfirmarPagamento" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> Confirmar Pagamento</h3>
            <span class="close" onclick="fecharModal('modalConfirmarPagamento')">&times;</span>
        </div>
        <div class="modal-body" style="text-align: center;">
            <i class="fas fa-credit-card" style="font-size: 3rem; color: #28a745; margin-bottom: 20px;"></i>
            <p style="margin-bottom: 10px; color: #333; font-size: 1.1rem; font-weight: 600;">Deseja confirmar este pagamento?</p>
            <p id="valorPagamento" style="margin-bottom: 30px; color: #28a745; font-size: 1.5rem; font-weight: bold;"></p>
            <div style="display: flex; gap: 10px;">
                <button onclick="fecharModal('modalConfirmarPagamento')" class="btn" style="flex: 1; background: #6c757d;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button onclick="processarPagamentoCartao()" class="btn" style="flex: 1;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Processando -->
<div id="modalProcessando" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-body" style="text-align: center; padding: 50px;">
            <div class="spinner" style="margin: 0 auto 30px;"></div>
            <h3 style="margin-bottom: 10px;">Processando Pagamento...</h3>
            <p style="color: #666;">Por favor, aguarde</p>
        </div>
    </div>
</div>

<!-- Modal Sucesso -->
<div id="modalSucesso" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-body" style="text-align: center; padding: 50px;">
            <div class="success-checkmark">
                <div class="check-icon">
                    <span class="icon-line line-tip"></span>
                    <span class="icon-line line-long"></span>
                    <div class="icon-circle"></div>
                    <div class="icon-fix"></div>
                </div>
            </div>
            <h2 style="color: #28a745; margin: 30px 0 10px;">Pagamento Realizado!</h2>
            <p style="color: #666; margin-bottom: 30px;">Seu pagamento foi processado com sucesso</p>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <a id="btnBaixarComprovante" href="#" target="_blank" class="btn" style="flex: 1; background: #28a745; text-decoration: none;">
                    <i class="fas fa-download"></i> Baixar Comprovante
                </a>
                <button onclick="compartilharComprovante()" class="btn" style="flex: 1; background: #17a2b8;">
                    <i class="fas fa-share-alt"></i> Compartilhar
                </button>
            </div>
            <button onclick="fecharSucesso()" class="btn" style="width: 100%; background: #6c757d;">
                <i class="fas fa-times"></i> Fechar
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirmar Remoção -->
<div id="modalConfirmarRemocao" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header" style="background: #dc3545;">
            <h3 style="color: white;"><i class="fas fa-exclamation-triangle"></i> Confirmar Remoção</h3>
            <span class="close" onclick="fecharModal('modalConfirmarRemocao')" style="color: white;">&times;</span>
        </div>
        <div class="modal-body" style="text-align: center;">
            <i class="fas fa-credit-card" style="font-size: 3rem; color: #dc3545; margin-bottom: 20px;"></i>
            <p style="font-size: 1.1rem; margin-bottom: 20px;">Tem certeza que deseja remover este cartão?</p>
            <p style="color: #666; font-size: 0.9rem;">Esta ação não pode ser desfeita.</p>
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button onclick="fecharModal('modalConfirmarRemocao')" class="btn" style="flex: 1; background: #6c757d;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button id="confirmarRemocaoBtn" class="btn" style="flex: 1; background: #dc3545;">
                    <i class="fas fa-trash"></i> Remover
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var pagamentoAtual = null;

// Função para identificar bandeira do cartão
function identificarBandeira(numero) {
    numero = numero.replace(/\s/g, '');
    
    if (/^4/.test(numero)) return 'visa';
    if (/^5[1-5]/.test(numero) || /^2[2-7]/.test(numero)) return 'mastercard';
    if (/^3[47]/.test(numero)) return 'amex';
    if (/^6(?:011|5)/.test(numero)) return 'discover';
    if (/^30[0-5]/.test(numero) || /^36/.test(numero) || /^38/.test(numero)) return 'diners';
    if (/^60/.test(numero)) return 'hipercard';
    if (/^50/.test(numero) || /^636/.test(numero) || /^438/.test(numero) || /^504/.test(numero)) return 'elo';
    
    return 'unknown';
}

// Função para identificar banco pelo BIN
function identificarBanco(numero) {
    numero = numero.replace(/\s/g, '');
    var bin = numero.substring(0, 6);
    
    // Bancos principais do Brasil
    if (/^(400700|400701|400702|400703|400704|400705|400706|400707|400708|400709)/.test(bin)) return 'bb';
    if (/^(341771|341772|341773|341774|341775|341776|341777|341778|341779)/.test(bin)) return 'itau';
    if (/^(403626|403627|403628|403629|403630|403631|403632|403633|403634)/.test(bin)) return 'bradesco';
    if (/^(553842|553843|553844|553845|553846|553847|553848|553849|553850)/.test(bin)) return 'santander';
    if (/^(104627|104628|104629|104630|104631|104632|104633|104634|104635)/.test(bin)) return 'caixa';
    if (/^(526167|526168|526169|526170|526171|526172|526173|526174|526175)/.test(bin)) return 'nubank';
    if (/^(416229|416230|416231|416232|416233|416234|416235|416236|416237)/.test(bin)) return 'inter';
    
    return 'outro';
}

// Função para aplicar cores e identificação aos cartões
function aplicarIdentificacaoCartoes() {
    document.querySelectorAll('.cartao-item').forEach(function(cartao) {
        var numero = cartao.dataset.numero;
        if (!numero) return;
        
        var bandeira = identificarBandeira(numero);
        var banco = identificarBanco(numero);
        
        // Aplicar classe da bandeira
        cartao.className = cartao.className.replace(/\b(visa|mastercard|elo|amex|hipercard|diners|discover)\b/g, '');
        cartao.classList.add(bandeira);
        
        // Aplicar classe do banco
        cartao.className = cartao.className.replace(/\bbanco-\w+\b/g, '');
        if (banco !== 'outro') {
            cartao.classList.add('banco-' + banco);
        }
        
        // Atualizar logo da bandeira
        var logoElement = cartao.querySelector('.logo-bandeira');
        var bandeiraSpan = cartao.querySelector('.cartao-bandeira');
        var bancoSpan = cartao.querySelector('.cartao-banco');
        
        var bandeiraTexto = {
            'visa': 'VISA',
            'mastercard': 'MASTER',
            'elo': 'ELO',
            'amex': 'AMEX',
            'hipercard': 'HIPER',
            'diners': 'DINERS',
            'discover': 'DISCOVER'
        };
        
        var bancoTexto = {
            'bb': 'Banco do Brasil',
            'itau': 'Itaú',
            'bradesco': 'Bradesco',
            'santander': 'Santander',
            'caixa': 'Caixa Econômica',
            'nubank': 'Nubank',
            'inter': 'Banco Inter'
        };
        
        if (logoElement) {
            // Limpar conteúdo e classes anteriores
            logoElement.textContent = '';
            logoElement.className = 'logo-bandeira';
            
            // Adicionar classe da bandeira para aplicar o CSS correspondente
            if (bandeira !== 'unknown') {
                logoElement.classList.add(bandeira);
            } else {
                logoElement.textContent = 'CARD';
                logoElement.style.background = 'rgba(255,255,255,0.9)';
                logoElement.style.color = '#333';
            }
        }
        
        if (bandeiraSpan) {
            bandeiraSpan.textContent = bandeiraTexto[bandeira] || 'CARTÃO';
        }
        
        if (bancoSpan) {
            bancoSpan.textContent = bancoTexto[banco] || 'Outro banco';
        }
    });
}

// Função para remover cartão
function removerCartao(cartaoId) {
    document.getElementById('modalConfirmarRemocao').style.display = 'block';
    document.getElementById('confirmarRemocaoBtn').onclick = function() {
        var form = document.createElement('form');
        form.method = 'POST';
        
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'acao';
        input1.value = 'remover_cartao';
        form.appendChild(input1);
        
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'cartao_id';
        input2.value = cartaoId;
        form.appendChild(input2);
        
        document.body.appendChild(form);
        form.submit();
    };
}

function verificarLimiteCartoes() {
    var cartoes = document.querySelectorAll('.cartao-item').length;
    if (cartoes >= 5) {
        alert('Limite máximo de 5 cartões atingido.');
        return;
    }
    abrirModalCadastro();
}

function abrirModalCadastro() {
    document.getElementById('modalCadastro').style.display = 'block';
}

function abrirModalPagamento(id, valor) {
    pagamentoAtual = id;
    valorPagamentoAtual = valor;
    document.querySelector('.payment-options').style.display = 'block';
    var contents = document.querySelectorAll('[id$="Content"]');
    for (var i = 0; i < contents.length; i++) {
        contents[i].style.display = 'none';
    }
    document.getElementById('modalPagamento').style.display = 'block';
}

var cartaoSelecionado = null;
var valorPagamentoAtual = 0;

function selecionarPagamento(tipo, cartaoId) {
    if (tipo === 'cartao') {
        cartaoSelecionado = cartaoId;
        fecharModal('modalPagamento');
        document.getElementById('valorPagamento').textContent = 'R$ ' + valorPagamentoAtual.toFixed(2).replace('.', ',');
        document.getElementById('modalConfirmarPagamento').style.display = 'block';
        return;
    }
    
    document.querySelector('.payment-options').style.display = 'none';
    var contents = document.querySelectorAll('[id$="Content"]');
    for (var i = 0; i < contents.length; i++) {
        contents[i].style.display = 'none';
    }
    
    var contentId = tipo + 'Content';
    var contentElement = document.getElementById(contentId);
    if (contentElement) {
        contentElement.style.display = 'block';
    } else {
        console.log('Content element not found: ' + contentId);
        alert('Método de pagamento selecionado: ' + tipo);
        setTimeout(function() {
            confirmarPagamento(tipo);
        }, 1000);
    }
}

function processarPagamentoCartao() {
    fecharModal('modalConfirmarPagamento');
    document.getElementById('modalProcessando').style.display = 'block';
    
    // Processar pagamento no backend
    var formData = new FormData();
    formData.append('acao', 'processar_pagamento');
    formData.append('pagamento_id', pagamentoAtual);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        setTimeout(function() {
            fecharModal('modalProcessando');
            document.getElementById('btnBaixarComprovante').href = 'gerar-comprovante.php?pagamento_id=' + pagamentoAtual;
            document.getElementById('modalSucesso').style.display = 'block';
        }, 2000);
    })
    .catch(error => {
        console.error('Erro:', error);
        setTimeout(function() {
            fecharModal('modalProcessando');
            document.getElementById('btnBaixarComprovante').href = 'gerar-comprovante.php?pagamento_id=' + pagamentoAtual;
            document.getElementById('modalSucesso').style.display = 'block';
        }, 2000);
    });
}

function compartilharComprovante() {
    var url = 'gerar-comprovante.php?pagamento_id=' + pagamentoAtual;
    var fullUrl = window.location.origin + window.location.pathname.replace(/[^/]*$/, '') + url;
    
    if (navigator.share) {
        navigator.share({
            title: 'Comprovante de Pagamento',
            text: 'Comprovante de pagamento FullTorque',
            url: fullUrl
        }).catch(err => console.log('Erro ao compartilhar:', err));
    } else {
        navigator.clipboard.writeText(fullUrl).then(() => {
            alert('Link do comprovante copiado para a área de transferência!');
        });
    }
}

function fecharSucesso() {
    fecharModal('modalSucesso');
    location.reload();
}

function abrirComprovante() {
    window.open('gerar-comprovante.php?pagamento_id=' + pagamentoAtual, '_blank');
}



function confirmarPagamento(metodo) {
    if (!pagamentoAtual) {
        alert('Erro: Nenhum pagamento selecionado.');
        return;
    }
    
    if (window.showPaymentSuccess) {
        window.showPaymentSuccess();
    }
    
    setTimeout(function() {
        var form = document.createElement('form');
        form.method = 'POST';
        
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'acao';
        input1.value = 'processar_pagamento';
        form.appendChild(input1);
        
        var input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'pagamento_id';
        input2.value = pagamentoAtual;
        form.appendChild(input2);
        
        var input3 = document.createElement('input');
        input3.type = 'hidden';
        input3.name = 'metodo_pagamento';
        input3.value = metodo;
        form.appendChild(input3);
        
        document.body.appendChild(form);
        form.submit();
    }, 2000);
}

function baixarBoletoPDF() {
    if (!pagamentoAtual) return;
    
    var pdfWindow = window.open('', '_blank');
    pdfWindow.document.write('<!DOCTYPE html><html><head><title>Boleto</title></head><body><h1>Boleto Simulado</h1><p>ID: ' + pagamentoAtual + '</p></body></html>');
    pdfWindow.document.close();
    
    setTimeout(function() {
        confirmarPagamento('boleto');
    }, 1000);
}

function copiarCodigoBarras() {
    var codigo = '99999.99999 99999.999999 99999.999999 9 99999999999999';
    navigator.clipboard.writeText(codigo).then(function() {
        alert('Código de barras copiado!');
    });
}

function copiarDadosTransferencia() {
    var dados = 'Banco: FullTorque Bank (999)\nAgência: 1234-5\nConta: 12345678-9\nCNPJ: 12.345.678/0001-99\nFavorecido: FullTorque Ltda';
    navigator.clipboard.writeText(dados).then(function() {
        alert('Dados para transferência copiados!');
    });
}

function redirecionarPayPal() {
    alert('Redirecionando para PayPal...');
    setTimeout(function() {
        confirmarPagamento('paypal');
    }, 2000);
}

function redirecionarMercadoPago() {
    alert('Redirecionando para Mercado Pago...');
    setTimeout(function() {
        confirmarPagamento('mercadopago');
    }, 2000);
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

document.addEventListener('submit', function(e) {
    if (e.target.querySelector('#validadeInput')) {
        var validade = e.target.querySelector('#validadeInput').value;
        var cvv = e.target.querySelector('#cvvInput').value;
        
        if (validade.length !== 5 || !validade.includes('/')) {
            e.preventDefault();
            alert('Por favor, preencha a validade completa no formato MM/AA');
            return false;
        }
        
        if (cvv.length !== 3) {
            e.preventDefault();
            alert('Por favor, preencha os 3 dígitos do CVV');
            return false;
        }
    }
});

document.addEventListener('input', function(e) {
    if (e.target.name === 'numero') {
        var value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
        var groups = value.match(/.{1,4}/g);
        var formattedValue = groups ? groups.join(' ') : value;
        e.target.value = formattedValue;
        
        // Identificar bandeira em tempo real
        if (value.length >= 4) {
            var bandeira = identificarBandeira(value);
            var banco = identificarBanco(value);
            
            // Mostrar preview da bandeira
            var preview = document.getElementById('cartao-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'cartao-preview';
                preview.style.cssText = 'margin-top: 10px; padding: 10px; border-radius: 5px; font-size: 12px;';
                e.target.parentNode.appendChild(preview);
            }
            
            var bandeiraTexto = {
                'visa': 'VISA',
                'mastercard': 'MASTERCARD',
                'elo': 'ELO',
                'amex': 'AMERICAN EXPRESS',
                'hipercard': 'HIPERCARD',
                'diners': 'DINERS CLUB',
                'discover': 'DISCOVER'
            };
            
            var bancoTexto = {
                'bb': 'Banco do Brasil',
                'itau': 'Itaú',
                'bradesco': 'Bradesco',
                'santander': 'Santander',
                'caixa': 'Caixa',
                'nubank': 'Nubank',
                'inter': 'Inter'
            };
            
            preview.innerHTML = '<strong>Bandeira:</strong> ' + (bandeiraTexto[bandeira] || 'Não identificada') + 
                              '<br><strong>Banco:</strong> ' + (bancoTexto[banco] || 'Outro banco');
        }
    }
    
    if (e.target.name === 'validade') {
        var value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    }
    
    if (e.target.name === 'cvv') {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
    }
});

// Aplicar identificação quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    aplicarIdentificacaoCartoes();
});
</script>
<script src="theme-controller.js"></script>

<?php include 'footer.php'; ?>