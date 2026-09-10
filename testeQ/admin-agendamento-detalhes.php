<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('error', 'ID do agendamento inválido.');
    header("Location: admin-agendamentos.php");
    exit;
}

$agendamento_id = (int)$_GET['id'];
$conexao = conectarBD();

$stmt = $conexao->prepare("
    SELECT a.*, u.nome as cliente, u.email as cliente_email, u.telefone as cliente_telefone,
           v.marca, v.modelo, v.placa, v.ano, v.cor
    FROM agendamentos a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $agendamento_id);
$stmt->execute();
$agendamento = $stmt->get_result()->fetch_assoc();

if (!$agendamento) {
    exibirAlerta('error', 'Agendamento não encontrado.');
    header("Location: admin-agendamentos.php");
    exit;
}

$conexao->close();

$titulo = "Detalhes do Agendamento";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="themes.css">
    <style>
        :root {
            --primary-color: #109349;
            --secondary-color: #0d7a3a;
            --tertiary-color: #f8f9fa;
            --highlight-color: #109349;
            --success-color: #109349;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --text-color: #333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.dark-mode {
            --tertiary-color: #1a1a1a;
            --text-color: #f5f5f5;
            --secondary-color: #1e1e1e;
            background-color: #1a1a1a;
            color: #f5f5f5;
        }
        
        body.dark-mode .card {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .card-header {
            background-color: #333;
            border-color: #444;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.2rem;
            color: #109349;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .card-header {
            background: #109349;
            color: white;
            padding: 20px 25px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.4rem;
            color: white;
            display: flex;
            align-items: center;
            font-weight: 600;
            margin: 0;
        }
        
        .card-header h2 i {
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .card-body {
            padding: 30px;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #109349;
        }
        
        .info-section h4 {
            color: #109349;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #109349;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-agendado {
            background: #74b9ff;
            color: white;
        }
        
        .status-confirmado {
            background: #109349;
            color: white;
        }
        
        .status-em_andamento {
            background: #f39c12;
            color: white;
        }
        
        .status-concluido {
            background: #27ae60;
            color: white;
        }
        
        .status-cancelado {
            background: #e74c3c;
            color: white;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d7a3a;
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Detalhes do Agendamento #<?php echo $agendamento_id; ?></h1>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Informações do Agendamento</h2>
                    <span class="status-badge status-<?php echo $agendamento['status']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Data do Agendamento</div>
                            <div class="info-value"><?php echo formatarData($agendamento['data_agendamento'], 'd/m/Y'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Horário</div>
                            <div class="info-value"><?php echo substr($agendamento['hora_inicio'], 0, 5) . ' - ' . substr($agendamento['hora_fim'], 0, 5); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Criado em</div>
                            <div class="info-value"><?php echo isset($agendamento['data_criacao']) ? formatarData($agendamento['data_criacao'], 'd/m/Y H:i') : '-'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> Dados do Cliente</h2>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h4><i class="fas fa-user-circle"></i> Informações Pessoais</h4>
                        <p><strong>Nome:</strong> <?php echo $agendamento['cliente']; ?></p>
                        <p><strong>Email:</strong> <?php echo $agendamento['cliente_email']; ?></p>
                        <p><strong>Telefone:</strong> <?php echo $agendamento['cliente_telefone'] ?? 'Não informado'; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-car"></i> Dados do Veículo</h2>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h4><i class="fas fa-car-side"></i> Informações do Veículo</h4>
                        <p><strong>Veículo:</strong> <?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></p>
                        <p><strong>Placa:</strong> <?php echo $agendamento['placa']; ?></p>
                        <p><strong>Ano:</strong> <?php echo $agendamento['ano']; ?></p>
                        <p><strong>Cor:</strong> <?php echo $agendamento['cor']; ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($agendamento['observacoes'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-comment-alt"></i> Observações</h2>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <p><?php echo nl2br(htmlspecialchars($agendamento['observacoes'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="admin-agendamentos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar para Lista
                </a>
                <a href="admin-agendamentos.php?status=confirmado&id=<?php echo $agendamento_id; ?>" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmar Agendamento
                </a>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
</body>
</html>
