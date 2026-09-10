<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-veiculos.php");
    exit;
}

$id = (int)$_GET['id'];

// Buscar dados do veículo
$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT v.*, u.nome as proprietario_nome FROM veiculos v LEFT JOIN usuarios u ON v.usuario_id = u.id WHERE v.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exibirAlerta('error', 'Veículo não encontrado.');
    header("Location: admin-veiculos.php");
    exit;
}

$veiculo = $result->fetch_assoc();

// Buscar histórico de agendamentos
$agendamentos = $conexao->prepare("SELECT * FROM agendamentos WHERE veiculo_id = ? ORDER BY data_agendamento DESC LIMIT 10");
$agendamentos->bind_param("i", $id);
$agendamentos->execute();
$historico = $agendamentos->get_result();

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Veículo - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --accent-color: <?php echo COR_DESTAQUE; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
            --text-color: <?php echo COR_TEXTO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
        }
        
        .user-avatar i {
            font-size: 20px;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .menu-item:hover i {
            transform: translateX(3px);
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            max-width: calc(100vw - 270px);
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 1.8rem;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
        }
        
        .header h1 i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
            width: 100%;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.2rem;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            margin: 0;
        }
        
        .card-header h2 i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }
        
        .col {
            flex: 1;
            padding: 10px;
        }
        
        .col-2 {
            flex: 0 0 50%;
            padding: 10px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .info-value {
            color: var(--text-color);
            font-size: 1.1rem;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }
        
        .badge-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .content {
                margin-left: 0;
            }
            
            .row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-car"></i> Detalhes do Veículo</h1>
        </div>
        
        <?php mostrarAlerta(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Informações do Veículo</h2>
                <a href="veiculo-editar.php?id=<?php echo $veiculo['id']; ?>" class="btn">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-2">
                        <div class="info-item">
                            <div class="info-label">Marca</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['marca']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Modelo</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['modelo']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Ano</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['ano']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Placa</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['placa']); ?></div>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="info-item">
                            <div class="info-label">Cor</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['cor']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Proprietário</div>
                            <div class="info-value"><?php echo htmlspecialchars($veiculo['proprietario_nome'] ?? 'Não informado'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data de Cadastro</div>
                            <div class="info-value"><?php echo isset($veiculo['data_cadastro']) ? formatarData($veiculo['data_cadastro'], 'd/m/Y H:i') : 'N/A'; ?></div>
                        </div>
                        <?php if (!empty($veiculo['observacoes'])): ?>
                        <div class="info-item">
                            <div class="info-label">Observações</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($veiculo['observacoes'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Histórico de Agendamentos</h2>
            </div>
            <div class="card-body">
                <?php if ($historico->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Serviço</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($agendamento = $historico->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo formatarData($agendamento['data_agendamento'], 'd/m/Y H:i'); ?></td>
                            <td><?php echo htmlspecialchars($agendamento['servico'] ?? 'Serviço não informado'); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch($agendamento['status']) {
                                    case 'confirmado': $status_class = 'badge-success'; break;
                                    case 'pendente': $status_class = 'badge-warning'; break;
                                    case 'cancelado': $status_class = 'badge-danger'; break;
                                    default: $status_class = 'badge-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($agendamento['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                if (isset($agendamento['valor_total']) && $agendamento['valor_total']) {
                                    echo formatarMoeda($agendamento['valor_total']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="admin-agendamento-detalhes.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem 0; color: #6c757d;">
                    <i class="fas fa-calendar-times fa-3x" style="margin-bottom: 1rem;"></i>
                    <h4>Nenhum agendamento encontrado</h4>
                    <p>Este veículo ainda não possui histórico de agendamentos.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <a href="admin-veiculos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</body>
</html>