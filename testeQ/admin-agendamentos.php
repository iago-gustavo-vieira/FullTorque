<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Processar alteração de status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado'])) {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            registrarLog('agendamento_status', "Status do agendamento ID: $id alterado para $status");
            exibirAlerta('success', 'Status do agendamento alterado com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível alterar o status do agendamento.');
        }
        
        $conexao->close();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-agendamentos.php");
    exit;
}

// Definir filtros
$filtro_status = isset($_GET['status_filtro']) ? limparDados($_GET['status_filtro']) : null;
$filtro_data = isset($_GET['data']) ? limparDados($_GET['data']) : null;
$filtro_cliente = isset($_GET['cliente']) ? (int)$_GET['cliente'] : null;

// Construir a consulta SQL com filtros
$sql = "SELECT a.*, u.nome as cliente, v.marca, v.modelo, v.placa 
        FROM agendamentos a 
        JOIN usuarios u ON a.usuario_id = u.id 
        JOIN veiculos v ON a.veiculo_id = v.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($filtro_status) {
    $sql .= " AND a.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if ($filtro_data) {
    $sql .= " AND a.data_agendamento = ?";
    $params[] = $filtro_data;
    $types .= "s";
}

if ($filtro_cliente) {
    $sql .= " AND a.usuario_id = ?";
    $params[] = $filtro_cliente;
    $types .= "i";
}

$sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio ASC";

// Buscar agendamentos
$conexao = conectarBD();

$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$agendamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar clientes para o filtro
$clientes = $conexao->query("SELECT id, nome FROM usuarios WHERE nivel_acesso = 'cliente' ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

$conexao->close();

// Título da página
$titulo_pagina = "Gerenciar Agendamentos";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="admin-responsive.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --highlight-color: <?php echo COR_DESTAQUE; ?>;
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
            transition: all 0.3s;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
        }
        
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
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
        
        .page-header > h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
        }
        
        .page-header > p {
            opacity: 1;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-info h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-subtitle {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        

        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 8px rgba(255, 206, 0, 0.3);
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
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .theme-alemanha .card-body {
            background: #000000;
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
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .theme-alemanha .filter-group label {
            color: #FFCE00;
        }
        
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .theme-alemanha .filter-group select,
        .theme-alemanha .filter-group input {
            background: #1a1a1a;
            border: 1px solid #DD0100;
            color: #ffffff;
        }
        
        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: #109349;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background: #109349;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d7a3a;
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            color: #212529;
            text-decoration: none;
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 700;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #109349;
        }
        
        .theme-alemanha th {
            background: #1a1a1a;
            color: #FFCE00;
            border-bottom: 2px solid #DD0100;
        }
        
        tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .theme-alemanha tbody tr:hover {
            background: #1a1a1a;
        }
        
        .theme-alemanha td {
            color: #ffffff;
            border-bottom-color: #333;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .badge-success {
            background: rgba(16, 147, 73, 0.1);
            color: #109349;
            border: 1px solid rgba(16, 147, 73, 0.2);
        }
        
        .badge-warning {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
            border: 1px solid rgba(243, 156, 18, 0.2);
        }
        
        .badge-danger {
            background: rgba(221, 1, 0, 0.1);
            color: #DD0100;
            border: 1px solid rgba(221, 1, 0, 0.2);
        }
        
        .badge-info {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.2);
        }
        
        .badge-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.2);
        }
        
        .theme-alemanha .badge-success {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
            border: 1px solid #FFCE00;
        }
        
        .theme-alemanha .badge-warning {
            background: rgba(221, 1, 0, 0.2);
            color: #DD0100;
            border: 1px solid #DD0100;
        }
        
        .theme-alemanha .badge-danger {
            background: rgba(221, 1, 0, 0.3);
            color: #DD0100;
            border: 1px solid #DD0100;
        }
        
        .theme-alemanha .badge-info {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
            border: 1px solid #FFCE00;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background: white;
            min-width: 240px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            border-radius: 12px;
            overflow: hidden;
            right: 0;
            border: 1px solid #e9ecef;
            backdrop-filter: blur(10px);
        }
        
        .theme-alemanha .dropdown-content {
            background: #1a1a1a;
            border: 1px solid #DD0100;
        }
        
        .dropdown-content a {
            color: #495057;
            padding: 12px 18px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .theme-alemanha .dropdown-content a {
            color: #ffffff;
            border-bottom: 1px solid #333;
        }
        
        .dropdown-content a:last-child {
            border-bottom: none;
        }
        
        .dropdown-content a:hover {
            background: linear-gradient(135deg, #109349 0%, #0d7a3a 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .theme-alemanha .dropdown-content a:hover {
            background: linear-gradient(135deg, #DD0100 0%, #FFCE00 100%);
            color: #000000;
        }
        
        .dropdown-content a i {
            width: 14px;
            text-align: center;
        }
        
        .dropdown-toggle::after {
            content: '';
            display: inline-block;
            margin-left: 5px;
            vertical-align: middle;
            border-top: 4px solid;
            border-right: 4px solid transparent;
            border-left: 4px solid transparent;
        }
        
        .dropdown.active .dropdown-content {
            display: block;
        }
        
        .agendamentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .agendamento-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .theme-alemanha .agendamento-card {
            background: #1a1a1a;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 8px rgba(255, 206, 0, 0.2);
        }
        
        .agendamento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .agendamento-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .theme-alemanha .agendamento-header {
            border-bottom-color: #333;
        }
        
        .agendamento-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #109349;
        }
        
        .theme-alemanha .agendamento-id {
            color: #FFCE00;
        }
        
        .agendamento-info {
            margin-bottom: 15px;
        }
        
        .agendamento-info .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #555;
        }
        
        .theme-alemanha .agendamento-info .info-row {
            color: #ffffff;
        }
        
        .agendamento-info .info-row i {
            color: #109349;
            width: 20px;
            text-align: center;
        }
        
        .theme-alemanha .agendamento-info .info-row i {
            color: #FFCE00;
        }
        
        .agendamento-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .theme-alemanha .agendamento-actions {
            border-top-color: #333;
        }
        
        .agendamento-actions .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
        
        .agendamento-actions .dropdown {
            flex: 1;
            min-width: 120px;
        }
        
        .agendamento-actions .dropdown .btn {
            width: 100%;
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
            
            .page-header > h1,
            .page-header > p {
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
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-info h1 {
                font-size: 1.5rem;
            }
            
            .header-subtitle {
                font-size: 0.9rem;
            }
            

            
            .card-header {
                flex-direction: column;
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
                gap: 15px;
            }
            
            .filter-group {
                width: 100%;
                min-width: auto;
            }
            
            .filter-group[style] {
                flex-direction: row !important;
                gap: 10px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .agendamentos-grid {
                grid-template-columns: 1fr;
            }
            
            .agendamento-card {
                padding: 15px;
            }
            
            .agendamento-actions .btn {
                min-width: auto;
                font-size: 0.8rem;
                padding: 8px 10px;
            }
            
            .agendamento-actions .dropdown {
                min-width: auto;
            }
            
            .table-container {
                font-size: 12px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 800px;
            }
            
            th, td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
            
            .dropdown-content {
                min-width: 200px;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                padding: 15px 10px;
            }
            
            .header-info h1 {
                font-size: 1.2rem;
            }
            
            .header-stats {
                gap: 10px;
            }
            

            
            .card-header h2 {
                font-size: 1rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            table {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <?php mostrarAlerta(); ?>
        
        <div class="container-fluid">
            <!-- Cabeçalho da Página -->
            <div class="page-header">
                <div class="mobile-welcome-text">Gerenciar Agendamentos</div>
                <h1><i class="fas fa-calendar-alt"></i> Gerenciar Agendamentos</h1>
                <p>Gerencie todos os agendamentos do sistema</p>
                <div class="header-content">
                    <div class="header-info" style="display: none;">
                        <h1><i class="fas fa-calendar-alt"></i> Gerenciar Agendamentos</h1>
                        <p class="header-subtitle">Gerencie todos os agendamentos do sistema</p>
                    </div>

                </div>
            </div>

        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-filter"></i> Filtros</h2>
            </div>
            <div class="card-body">
                <form action="admin-agendamentos.php" method="get" class="filter-form">
                    <div class="filter-group">
                        <label for="status_filtro">Status</label>
                        <select id="status_filtro" name="status_filtro">
                            <option value="">Todos</option>
                            <option value="agendado" <?php echo $filtro_status == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                            <option value="confirmado" <?php echo $filtro_status == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                            <option value="em_andamento" <?php echo $filtro_status == 'em_andamento' ? 'selected' : ''; ?>>Em andamento</option>
                            <option value="concluido" <?php echo $filtro_status == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo $filtro_status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="data">Data</label>
                        <input type="date" id="data" name="data" value="<?php echo $filtro_data; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="cliente">Cliente</label>
                        <select id="cliente" name="cliente">
                            <option value="">Todos</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" <?php echo $filtro_cliente == $cliente['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cliente['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        
                        <?php if ($filtro_status || $filtro_data || $filtro_cliente): ?>
                            <a href="admin-agendamentos.php" class="btn btn-secondary" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Lista de Agendamentos</h2>
                <a href="admin-agendamento-novo.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Agendamento
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($agendamentos)): ?>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                        <p style="font-size: 1.1rem; font-weight: 500;">Nenhum agendamento encontrado</p>
                    </div>
                <?php else: ?>
                    <div class="agendamentos-grid">
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <div class="agendamento-card">
                                <div class="agendamento-header">
                                    <div class="agendamento-id">#<?php echo $agendamento['id']; ?></div>
                                    <span class="badge badge-<?php 
                                        switch($agendamento['status']) {
                                            case 'agendado': echo 'info'; break;
                                            case 'confirmado': echo 'success'; break;
                                            case 'em_andamento': echo 'warning'; break;
                                            case 'concluido': echo 'success'; break;
                                            case 'cancelado': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
                                    </span>
                                </div>
                                <div class="agendamento-info">
                                    <div class="info-row">
                                        <i class="fas fa-user"></i>
                                        <span><strong>Cliente:</strong> <?php echo $agendamento['cliente']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-car"></i>
                                        <span><strong>Veículo:</strong> <?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-id-card"></i>
                                        <span><strong>Placa:</strong> <?php echo $agendamento['placa']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-calendar"></i>
                                        <span><strong>Data:</strong> <?php echo formatarData($agendamento['data_agendamento'], 'd/m/Y'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-clock"></i>
                                        <span><strong>Horário:</strong> <?php echo substr($agendamento['hora_inicio'], 0, 5) . ' - ' . substr($agendamento['hora_fim'], 0, 5); ?></span>
                                    </div>
                                </div>
                                <div class="agendamento-actions">
                                    <a href="admin-agendamento-detalhes.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </a>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-warning btn-sm dropdown-toggle">
                                            <i class="fas fa-cog"></i> Status
                                        </button>
                                        <div class="dropdown-content">
                                            <a href="admin-agendamentos.php?status=agendado&id=<?php echo $agendamento['id']; ?>">
                                                <i class="fas fa-calendar"></i> Agendado
                                            </a>
                                            <a href="admin-agendamentos.php?status=confirmado&id=<?php echo $agendamento['id']; ?>">
                                                <i class="fas fa-check"></i> Confirmado
                                            </a>
                                            <a href="admin-agendamentos.php?status=em_andamento&id=<?php echo $agendamento['id']; ?>">
                                                <i class="fas fa-spinner"></i> Em Andamento
                                            </a>
                                            <a href="admin-agendamentos.php?status=concluido&id=<?php echo $agendamento['id']; ?>">
                                                <i class="fas fa-check-circle"></i> Concluído
                                            </a>
                                            <a href="admin-agendamentos.php?status=cancelado&id=<?php echo $agendamento['id']; ?>">
                                                <i class="fas fa-times-circle"></i> Cancelado
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
            
            // Funcionalidade para os dropdowns
            const dropdownButtons = document.querySelectorAll('.dropdown-toggle');
            
            dropdownButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdown = this.closest('.dropdown');
                    
                    // Fecha todos os outros dropdowns
                    document.querySelectorAll('.dropdown').forEach(function(d) {
                        if (d !== dropdown) {
                            d.classList.remove('active');
                        }
                    });
                    
                    // Alterna o estado do dropdown atual
                    dropdown.classList.toggle('active');
                });
            });
            
            // Fecha os dropdowns quando clicar fora deles
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown').forEach(function(d) {
                        d.classList.remove('active');
                    });
                }
            });
        });
    </script>
</body>
</html>