<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Definir filtros
$filtro_usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : null;
$filtro_acao = isset($_GET['acao']) ? limparDados($_GET['acao']) : null;
$filtro_data = isset($_GET['data']) ? limparDados($_GET['data']) : null;

// Construir a consulta SQL com filtros
$sql = "SELECT l.*, u.nome as usuario_nome 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($filtro_usuario) {
    $sql .= " AND l.usuario_id = ?";
    $params[] = $filtro_usuario;
    $types .= "i";
}

if ($filtro_acao) {
    $sql .= " AND l.acao = ?";
    $params[] = $filtro_acao;
    $types .= "s";
}

if ($filtro_data) {
    $sql .= " AND DATE(l.data_hora) = ?";
    $params[] = $filtro_data;
    $types .= "s";
}

$sql .= " ORDER BY l.data_hora DESC LIMIT 100";

// Buscar logs
$conexao = conectarBD();

$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar usuários para o filtro
$usuarios = $conexao->query("SELECT id, nome FROM usuarios ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

// Buscar ações distintas para o filtro
$acoes = $conexao->query("SELECT DISTINCT acao FROM logs ORDER BY acao")->fetch_all(MYSQLI_ASSOC);

$conexao->close();

// Título da página
$titulo_pagina = "Logs do Sistema";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - <?php echo SISTEMA_NOME; ?></title>
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
            --error-color: #DD0100;
            --text-color: #333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.2) 33%, rgba(221, 1, 0, 0.15) 33%, rgba(221, 1, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 100%) !important;
            background-color: #1a1a1a !important;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #109349 0%, #109349 33%, #FFFFFF 33%, #FFFFFF 66%, #DD0101 66%, #DD0101 100%) !important;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(16, 147, 73, 0.2);
            border: 2px solid #109349;
        }
        
        .theme-alemanha .page-header {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%) !important;
            border: 2px solid #FFCE00;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
        }
        
        .page-header p {
            opacity: 1;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
            font-weight: 600;
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 4px rgba(255, 206, 0, 0.3);
        }
        
        .card-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 25px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
        
        .card-header h2 {
            font-size: 1.3rem;
            color: white;
            display: flex;
            align-items: center;
            font-weight: 600;
            margin: 0;
            gap: 10px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .theme-alemanha .card-body {
            background: #000000;
        }
        
        .logs-card .card-body {
            padding: 0;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }
        
        .theme-alemanha .filter-group label {
            color: #FFCE00;
        }
        
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .theme-alemanha .filter-group select,
        .theme-alemanha .filter-group input {
            background: #1a1a1a;
            border: 1px solid #DD0100;
            color: #ffffff;
        }
        
        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            height: 500px;
            overflow-y: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .theme-alemanha table {
            background: #000000;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .theme-alemanha th {
            background: #1a1a1a;
            color: #FFCE00;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .theme-alemanha tr:hover {
            background: #1a1a1a;
        }
        
        .theme-alemanha td {
            color: #ffffff;
            border-bottom-color: #333;
        }
        
        .log-action {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            background: rgba(16, 147, 73, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(16, 147, 73, 0.2);
        }
        
        .theme-alemanha .log-action {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
            border: 1px solid #FFCE00;
        }
        
        .log-description {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #6c757d;
        }
        
        .theme-alemanha .log-description {
            color: #ffffffb0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease;
            border: 1px solid #e9ecef;
        }
        
        .theme-alemanha .stat-card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 4px rgba(255, 206, 0, 0.3);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .theme-alemanha .stat-number {
            color: #FFCE00;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .theme-alemanha .stat-label {
            color: #ffffff;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .page-header {
                background-image: url('bem-vindo-italia-responsivo.jpg') !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                padding: 15px 12px;
                border-radius: 10px;
                margin-bottom: 15px;
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: flex-end;
                text-align: right;
                position: relative;
            }
            
            .theme-alemanha .page-header {
                background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
                background-position: center !important;
            }
            
            .page-header h1,
            .page-header p {
                display: none !important;
            }
            
            .mobile-welcome-text {
                display: flex;
                position: absolute;
                top: 35px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(255, 255, 255, 0.9);
                color: #109349;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 18px;
                font-weight: 600;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(5px);
                z-index: 10;
                white-space: nowrap;
            }
            
            .theme-alemanha .mobile-welcome-text {
                background: rgba(0, 0, 0, 0.8);
                color: #EFC202;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 15px;
            }
            
            .card-header h2 {
                font-size: 1.1rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .filter-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                width: 100%;
                min-width: auto;
            }
            
            .filter-group[style] {
                flex-direction: row !important;
                gap: 10px;
            }
            
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                height: 400px;
            }
            
            table {
                min-width: 800px;
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .log-description {
                max-width: 150px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .card-header h2 {
                font-size: 1rem;
            }
            
            table {
                font-size: 11px;
            }
            
            th, td {
                padding: 6px 4px;
            }
            
            .log-description {
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text">Logs do Sistema</div>
            <h1><i class="fas fa-clipboard-list"></i> Logs do Sistema</h1>
            <p>Visualize todos os registros de atividades do sistema</p>
        </div>
        
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($logs); ?></div>
                <div class="stat-label">Total de Logs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_unique(array_column($logs, 'usuario_id'))); ?></div>
                <div class="stat-label">Usuários Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_unique(array_column($logs, 'acao'))); ?></div>
                <div class="stat-label">Tipos de Ação</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($logs, fn($l) => date('Y-m-d', strtotime($l['data_hora'])) == date('Y-m-d'))); ?></div>
                <div class="stat-label">Logs Hoje</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-filter"></i> Filtros</h2>
            </div>
            <div class="card-body">
                <form action="admin-logs.php" method="get" class="filter-form">
                    <div class="filter-group">
                        <label for="usuario">Usuário</label>
                        <select id="usuario" name="usuario">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id']; ?>" <?php echo $filtro_usuario == $usuario['id'] ? 'selected' : ''; ?>>
                                    <?php echo $usuario['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="acao">Ação</label>
                        <select id="acao" name="acao">
                            <option value="">Todas</option>
                            <?php foreach ($acoes as $acao): ?>
                                <option value="<?php echo $acao['acao']; ?>" <?php echo $filtro_acao == $acao['acao'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $acao['acao'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="data">Data</label>
                        <input type="date" id="data" name="data" value="<?php echo $filtro_data; ?>">
                    </div>
                    
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        
                        <?php if ($filtro_usuario || $filtro_acao || $filtro_data): ?>
                            <a href="admin-logs.php" class="btn btn-secondary" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card logs-card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Registros de Log</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Descrição</th>
                                <th>IP</th>
                                <th>Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo $log['usuario_nome'] ?? 'Sistema'; ?></td>
                                    <td>
                                        <span class="log-action">
                                            <?php echo ucfirst(str_replace('_', ' ', $log['acao'])); ?>
                                        </span>
                                    </td>
                                    <td class="log-description" title="<?php echo $log['descricao']; ?>">
                                        <?php echo $log['descricao']; ?>
                                    </td>
                                    <td><?php echo $log['ip']; ?></td>
                                    <td><?php echo formatarData($log['data_hora'], 'd/m/Y H:i:s'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Nenhum registro de log encontrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Aplicar tema salvo
            const savedTheme = localStorage.getItem('theme') || 'default';
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
        });
    </script>
</body>
</html>
