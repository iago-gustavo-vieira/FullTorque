<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_GET['pagamento_id'])) {
    die('Acesso negado');
}

$pagamento_id = intval($_GET['pagamento_id']);
$conexao = conectarBD();

$stmt = $conexao->prepare("SELECT p.*, u.nome as usuario_nome, u.email as usuario_email 
                           FROM pagamentos p 
                           JOIN usuarios u ON p.usuario_id = u.id 
                           WHERE p.id = ? AND p.usuario_id = ?");
$stmt->bind_param("ii", $pagamento_id, $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die('Pagamento não encontrado');
}

$pagamento = $resultado->fetch_assoc();

if ($pagamento['status'] !== 'aprovado') {
    die('Pagamento não aprovado');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .comprovante {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #28a745, #155724);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .status-badge {
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            margin-top: 15px;
            font-weight: bold;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 600;
        }
        
        .info-value {
            color: #333;
            font-weight: bold;
        }
        
        .valor-destaque {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }
        
        .valor-destaque .label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .valor-destaque .valor {
            color: #28a745;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .comprovante {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="comprovante" id="comprovante">
        <div class="header">
            <h1>🏁 FullTorque</h1>
            <p>Comprovante de Pagamento</p>
            <div class="status-badge">✓ PAGAMENTO APROVADO</div>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h2>Dados do Pagamento</h2>
                <div class="info-row">
                    <span class="info-label">Número do Comprovante:</span>
                    <span class="info-value">#<?php echo str_pad($pagamento['id'], 8, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data do Pagamento:</span>
                    <span class="info-value"><?php 
                        $data = $pagamento['data_pagamento'] ?: $pagamento['data_atualizacao'] ?: date('Y-m-d H:i:s');
                        echo date('d/m/Y H:i:s', strtotime($data)); 
                    ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Descrição:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pagamento['descricao']); ?></span>
                </div>
            </div>
            
            <div class="valor-destaque">
                <div class="label">Valor Pago</div>
                <div class="valor">R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="info-section">
                <h2>Dados do Cliente</h2>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pagamento['usuario_nome']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">E-mail:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pagamento['usuario_email']); ?></span>
                </div>
            </div>
            
            <div class="actions">
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Imprimir
                </button>
                <button onclick="baixarPDF()" class="btn btn-primary">
                    📥 Baixar PDF
                </button>
                <button onclick="compartilhar()" class="btn btn-secondary">
                    📤 Compartilhar
                </button>
            </div>
        </div>
        
        <div class="footer">
            <p>Este é um comprovante válido de pagamento.</p>
            <p>FullTorque - Sistema de Gestão Automotiva</p>
            <p>Emitido em <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
    
    <script>
        function baixarPDF() {
            window.print();
        }
        
        function compartilhar() {
            if (navigator.share) {
                navigator.share({
                    title: 'Comprovante de Pagamento',
                    text: 'Comprovante de pagamento FullTorque #<?php echo str_pad($pagamento['id'], 8, '0', STR_PAD_LEFT); ?>',
                    url: window.location.href
                }).catch(err => console.log('Erro ao compartilhar:', err));
            } else {
                const url = window.location.href;
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copiado para a área de transferência!');
                });
            }
        }
    </script>
</body>
</html>
