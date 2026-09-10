<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'config.php';

$relatorio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tema = isset($_GET['tema']) ? $_GET['tema'] : 'italia';

if (!$relatorio_id) {
    die('ID do relatório não fornecido');
}

$conexao = conectarBD();
$stmt = $conexao->prepare("
    SELECT rc.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone,
           'Não atribuído' as analista_nome, 
           v.marca, v.modelo, v.placa, v.ano
    FROM relatorios_cliente rc
    JOIN usuarios u ON rc.usuario_id = u.id
    JOIN veiculos v ON rc.veiculo_id = v.id
    WHERE rc.id = ?
");
$stmt->bind_param("i", $relatorio_id);
$stmt->execute();
$relatorio = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexao->close();

if (!$relatorio) {
    die('Relatório não encontrado');
}

$cor1 = $tema === 'alemanha' ? '#000000' : '#109349';
$cor2 = $tema === 'alemanha' ? '#DD0100' : '#FFFFFF';
$cor3 = $tema === 'alemanha' ? '#FFCE00' : '#DD0101';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Diagnóstico #<?php echo $relatorio_id; ?> - Full Torque</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, <?php echo $cor1; ?> 0%, <?php echo $cor1; ?> 33%, <?php echo $cor2; ?> 33%, <?php echo $cor2; ?> 66%, <?php echo $cor3; ?> 66%, <?php echo $cor3; ?> 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .content {
            padding: 40px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid <?php echo $cor1; ?>;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .card-title {
            color: <?php echo $cor1; ?>;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            background: <?php echo $cor3; ?>;
            color: white;
            text-transform: uppercase;
        }
        
        .diagnostico-box {
            background: #f8f9fa;
            border: 2px solid <?php echo $cor1; ?>;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            line-height: 1.8;
        }
        
        .footer {
            background: <?php echo $cor1; ?>;
            color: white;
            text-align: center;
            padding: 30px;
            font-size: 0.9rem;
        }
        
        .btn-print {
            margin-top: 15px;
            padding: 12px 30px;
            background: white;
            color: <?php echo $cor1; ?>;
            border: 2px solid white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Full Torque</h1>
            <p>Relatório de Diagnóstico #<?php echo $relatorio['id']; ?></p>
        </div>
        
        <div class="content">
            <div class="card">
                <div class="card-title">📋 Informações do Relatório</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Data de Envio</div>
                        <div class="info-value"><?php echo formatarData($relatorio['data_envio'], 'd/m/Y H:i'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span class="status-badge"><?php echo ucfirst($relatorio['status']); ?></span></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-title">👤 Dados do Cliente</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nome</div>
                        <div class="info-value"><?php echo $relatorio['cliente_nome']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $relatorio['cliente_email']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telefone</div>
                        <div class="info-value"><?php echo $relatorio['cliente_telefone']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Analista Responsável</div>
                        <div class="info-value"><?php echo $relatorio['analista_nome']; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-title">🚗 Dados do Veículo</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Marca/Modelo</div>
                        <div class="info-value"><?php echo $relatorio['marca'] . ' ' . $relatorio['modelo']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Placa</div>
                        <div class="info-value"><?php echo $relatorio['placa']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ano</div>
                        <div class="info-value"><?php echo $relatorio['ano']; ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($relatorio['endereco_completo'])): ?>
            <div class="card">
                <div class="card-title">📍 Endereço</div>
                <div class="info-item">
                    <div class="info-value"><?php echo $relatorio['endereco_completo']; ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($relatorio['descricao_problema'])): ?>
            <div class="card">
                <div class="card-title">⚠️ Problema Relatado</div>
                <div class="info-item">
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($relatorio['descricao_problema'])); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-title">🔍 Diagnóstico</div>
                <?php if (!empty($relatorio['diagnostico'])): ?>
                    <div class="diagnostico-box">
                        <?php echo nl2br(htmlspecialchars($relatorio['diagnostico'])); ?>
                    </div>
                <?php else: ?>
                    <div class="info-item">
                        <div class="info-value" style="color: #999; font-style: italic;">Diagnóstico pendente</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <strong>Full Torque</strong> - Sistema de Diagnóstico Automotivo<br>
            Gerado em <?php echo date('d/m/Y H:i'); ?><br>
            <button onclick="window.print()" class="btn-print no-print">🖨️ Imprimir / Salvar PDF</button>
        </div>
    </div>
</body>
</html>
