<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();

// Buscar relatórios de clientes (diagnósticos)
$diagnosticos = $conexao->query("
    SELECT rc.*, u.nome as cliente_nome, v.marca, v.modelo, v.placa
    FROM relatorios_cliente rc
    LEFT JOIN usuarios u ON rc.usuario_id = u.id
    LEFT JOIN veiculos v ON rc.veiculo_id = v.id
    ORDER BY rc.data_envio DESC
");

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnósticos - Admin</title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--secondary-color);
        }
        
        .btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pendente {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .status-em_andamento {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }
        
        .status-concluido {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f1f1;
            color: #555;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-stethoscope"></i> Diagnósticos</h1>
            <a href="admin.php" class="btn">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
        
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Veículo</th>
                            <th>Mecânico</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($diagnosticos && $diagnosticos->num_rows > 0): ?>
                            <?php while ($diagnostico = $diagnosticos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $diagnostico['cliente_nome'] ?? 'N/A'; ?></td>
                                    <td><?php echo ($diagnostico['marca'] ?? '') . ' ' . ($diagnostico['modelo'] ?? '') . ' (' . ($diagnostico['placa'] ?? '') . ')'; ?></td>
                                    <td>A definir</td>
                                    <td>
                                        <span class="status status-<?php echo $diagnostico['status'] ?? 'pendente'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $diagnostico['status'] ?? 'pendente')); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($diagnostico['data_envio'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="#" class="action-btn" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                    Nenhum diagnóstico encontrado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>