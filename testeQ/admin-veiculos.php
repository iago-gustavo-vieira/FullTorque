<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Definir filtros
$filtro_marca = isset($_GET['marca']) ? limparDados($_GET['marca']) : null;
$filtro_usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : null;

// Construir a consulta SQL com filtros
$sql = "SELECT v.*, u.nome as nome_usuario, u.email as email_usuario 
        FROM veiculos v 
        JOIN usuarios u ON v.usuario_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($filtro_marca) {
    $sql .= " AND v.marca LIKE ?";
    $params[] = "%$filtro_marca%";
    $types .= "s";
}

if ($filtro_usuario) {
    $sql .= " AND v.usuario_id = ?";
    $params[] = $filtro_usuario;
    $types .= "i";
}

$sql .= " ORDER BY u.nome, v.marca, v.modelo";

// Buscar veículos
$conexao = conectarBD();

$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$veiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar usuários para o filtro
$usuarios = $conexao->query("SELECT id, nome FROM usuarios ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

// Buscar marcas distintas para o filtro
$marcas = $conexao->query("SELECT DISTINCT marca FROM veiculos ORDER BY marca")->fetch_all(MYSQLI_ASSOC);

$conexao->close();

// Título da página
$titulo = "Gerenciar Veículos";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="admin-responsive.css">
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
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
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
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
            border-bottom: 1px solid #333;
        }
        
        .theme-alemanha .card-header h2 {
            color: #FFCE00;
        }
        
        .card-body {
            padding: 20px;
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
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .theme-alemanha .filter-group label {
            color: #FFCE00;
        }
        
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .theme-alemanha .filter-group select,
        .theme-alemanha .filter-group input {
            background: #1a1a1a;
            border: 1px solid #DD0100;
            color: #ffffff;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: #109349;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d7a3a;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .theme-alemanha table {
            background: #000000;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .theme-alemanha th {
            background: #1a1a1a;
            color: #FFCE00;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .theme-alemanha tr:hover {
            background: #1a1a1a;
        }
        
        .theme-alemanha td {
            color: #ffffff;
            border-bottom-color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .view-toggle {
            display: flex;
            gap: 5px;
        }
        
        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .vehicle-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .theme-alemanha .vehicle-card {
            background: #1a1a1a;
            border: 2px solid #DD0100;
            box-shadow: 0 4px 12px rgba(255, 206, 0, 0.3);
        }
        
        .vehicle-card {
            cursor: pointer;
        }
        
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .vehicle-card:active {
            transform: translateY(-2px);
        }
        
        .vehicle-header {
            background: linear-gradient(135deg, #109349, #0d7a3a);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-alemanha .vehicle-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
        
        .vehicle-brand {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .vehicle-id {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .vehicle-info {
            padding: 20px;
        }
        
        .vehicle-info h3 {
            margin: 0 0 15px 0;
            color: var(--text-color);
            font-size: 1.3rem;
        }
        
        .theme-alemanha .vehicle-info h3 {
            color: #FFCE00;
        }
        
        .vehicle-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .theme-alemanha .detail-item {
            color: #ffffff;
        }
        
        .detail-item i {
            color: var(--primary-color);
            width: 16px;
        }
        
        .vehicle-owner {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .theme-alemanha .vehicle-owner {
            background: #000000;
            border: 1px solid #DD0100;
            color: #ffffff;
        }
        
        .vehicle-owner i {
            color: var(--primary-color);
        }
        
        .vehicle-actions {
            padding: 0 20px 20px;
            display: flex;
            gap: 8px;
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
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            animation: slideIn 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #109349, #0d7a3a);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close:hover {
            transform: scale(1.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .search-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .theme-alemanha .search-input {
            background: #1a1a1a;
            border: 2px solid #DD0100;
            color: #ffffff;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        

        
        .vehicle-details-modal {
            display: grid;
            gap: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #999;
        }
        
        .table-row-hover:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
        
        .user-info-table {
            display: flex;
            flex-direction: column;
        }
        
        .user-info-table small {
            color: #666;
            font-size: 0.8rem;
        }
        
        .color-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #ddd;
        }
        
        .empty-state-table {
            padding: 40px;
            color: #666;
        }
        
        .empty-state-table i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-table {
            text-align: center;
        }
        
        code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .theme-alemanha code {
            background: #1a1a1a;
            color: #FFCE00;
            border: 1px solid #DD0100;
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
            

            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
            
            .view-toggle {
                width: 100%;
                justify-content: center;
            }
            
            .vehicles-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .vehicle-card {
                margin-bottom: 10px;
            }
            
            .vehicle-actions {
                flex-direction: column;
            }
            
            .vehicle-actions .btn {
                width: 100%;
            }
            
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 800px;
                font-size: 13px;
            }
            
            th, td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            
            .actions {
                flex-direction: row;
                gap: 5px;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .modal-body {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.2rem;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .card-header h2 {
                font-size: 1rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .vehicle-details {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text">Gerenciar Veículos</div>
            <h1><i class="fas fa-car"></i> Gerenciar Veículos</h1>
            <p>Visualize e gerencie todos os veículos cadastrados</p>
        </div>
        
        <?php mostrarAlerta(); ?>
        

        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-filter"></i> Filtros</h2>
            </div>
            <div class="card-body">
                <!-- Busca em Tempo Real -->
                <div class="search-container">
                    <input type="text" id="vehicleSearch" class="search-input" placeholder="Buscar por marca, modelo, placa ou proprietário...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                
                <form action="admin-veiculos.php" method="get" class="filter-form">
                    <div class="filter-group">
                        <label for="marca">Marca</label>
                        <select id="marca" name="marca">
                            <option value="">Todas</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['marca']; ?>" <?php echo $filtro_marca == $marca['marca'] ? 'selected' : ''; ?>>
                                    <?php echo $marca['marca']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
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
                    
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        
                        <?php if ($filtro_marca || $filtro_usuario): ?>
                            <a href="admin-veiculos.php" class="btn btn-secondary" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-car"></i> Veículos Cadastrados (<?php echo count($veiculos); ?>)</h2>
                <div class="view-toggle">
                    <button class="btn btn-sm" id="gridView" onclick="toggleView('grid')">
                        <i class="fas fa-th-large"></i> Cards
                    </button>
                    <button class="btn btn-sm btn-primary" id="listView" onclick="toggleView('list')">
                        <i class="fas fa-list"></i> Lista
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Vista em Cards -->
                <div id="cardsContainer" class="vehicles-grid" style="display: none;">
                    <?php foreach ($veiculos as $index => $veiculo): ?>
                        <div class="vehicle-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 0.1; ?>s;" onclick="showVehicleModal(<?php echo htmlspecialchars(json_encode($veiculo)); ?>)">
                            <div class="vehicle-header">
                                <div class="vehicle-brand"><?php echo $veiculo['marca']; ?></div>
                                <div class="vehicle-id">#<?php echo $veiculo['id']; ?></div>
                            </div>
                            <div class="vehicle-info">
                                <h3><?php echo $veiculo['modelo']; ?></h3>
                                <div class="vehicle-details">
                                    <div class="detail-item">
                                        <i class="fas fa-id-card"></i>
                                        <span><?php echo $veiculo['placa']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo $veiculo['ano']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-palette"></i>
                                        <span><?php echo $veiculo['cor']; ?></span>
                                    </div>
                                </div>
                                <div class="vehicle-owner">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo $veiculo['nome_usuario']; ?></span>
                                </div>
                            </div>
                            <div class="vehicle-actions" onclick="event.stopPropagation();">
                                <button class="btn btn-primary btn-sm" onclick="showVehicleModal(<?php echo htmlspecialchars(json_encode($veiculo)); ?>)">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </button>
                                <button class="btn btn-success btn-sm" onclick="callOwner('<?php echo $veiculo['nome_usuario']; ?>', '<?php echo $veiculo['email_usuario']; ?>')" title="Ligar para o proprietário">
                                    <i class="fas fa-phone"></i> Ligar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($veiculos)): ?>
                        <div class="empty-state">
                            <i class="fas fa-car-crash"></i>
                            <h3>Nenhum veículo encontrado</h3>
                            <p>Não há veículos cadastrados com os filtros selecionados.</p>
                            <button class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fas fa-refresh"></i> Recarregar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Vista em Lista -->
                <div id="listContainer" class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Placa</th>
                                <th>Ano</th>
                                <th>Cor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <tr class="table-row-hover">
                                    <td><span class="badge"><?php echo $veiculo['id']; ?></span></td>
                                    <td>
                                        <div class="user-info-table">
                                            <strong><?php echo $veiculo['nome_usuario']; ?></strong>
                                            <small><?php echo $veiculo['email_usuario']; ?></small>
                                        </div>
                                    </td>
                                    <td><strong><?php echo $veiculo['marca']; ?></strong></td>
                                    <td><?php echo $veiculo['modelo']; ?></td>
                                    <td><code><?php echo $veiculo['placa']; ?></code></td>
                                    <td><?php echo $veiculo['ano']; ?></td>
                                    <td>
                                        <span class="color-badge" style="background-color: <?php echo strtolower($veiculo['cor']); ?>;"></span>
                                        <?php echo $veiculo['cor']; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="admin-veiculo-detalhes.php?id=<?php echo $veiculo['id']; ?>" class="btn btn-primary btn-sm" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-success btn-sm" onclick="callOwner('<?php echo $veiculo['nome_usuario']; ?>', '<?php echo $veiculo['email_usuario']; ?>')" title="Ligar para o proprietário">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($veiculos)): ?>
                                <tr>
                                    <td colspan="8" class="empty-table">
                                        <div class="empty-state-table">
                                            <i class="fas fa-car-crash"></i>
                                            <p>Nenhum veículo encontrado</p>
                                        </div>
                                    </td>
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
            

            
            // Restaurar visualização salva
            const savedView = localStorage.getItem('vehicleView') || 'list';
            toggleView(savedView);
            
            // Busca em tempo real
            const searchInput = document.getElementById('vehicleSearch');
            if (searchInput) {
                searchInput.addEventListener('input', filterVehicles);
            }
            
            // Fechar modal ao clicar fora
            window.onclick = function(event) {
                const modal = document.getElementById('vehicleModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
        
        // Alternar entre visualizações
        function toggleView(view) {
            const cardsContainer = document.getElementById('cardsContainer');
            const listContainer = document.getElementById('listContainer');
            const gridBtn = document.getElementById('gridView');
            const listBtn = document.getElementById('listView');
            
            if (view === 'grid') {
                cardsContainer.style.display = 'grid';
                listContainer.style.display = 'none';
                gridBtn.classList.add('btn-primary');
                listBtn.classList.remove('btn-primary');
                localStorage.setItem('vehicleView', 'grid');
            } else {
                cardsContainer.style.display = 'none';
                listContainer.style.display = 'block';
                listBtn.classList.add('btn-primary');
                gridBtn.classList.remove('btn-primary');
                localStorage.setItem('vehicleView', 'list');
            }
        }
        
        // Busca em tempo real
        function filterVehicles() {
            const searchTerm = document.getElementById('vehicleSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.vehicle-card');
            const rows = document.querySelectorAll('.table-row-hover');
            
            // Filtrar cards
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
            
            // Filtrar linhas da tabela
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? 'table-row' : 'none';
            });
        }
        
        // Modal de detalhes
        function showVehicleModal(vehicle) {
            const modal = document.getElementById('vehicleModal');
            document.getElementById('modalVehicleInfo').innerHTML = `
                <h3>${vehicle.marca} ${vehicle.modelo}</h3>
                <div class="vehicle-details-modal">
                    <div class="detail-row">
                        <strong>Placa:</strong> <code>${vehicle.placa}</code>
                    </div>
                    <div class="detail-row">
                        <strong>Ano:</strong> ${vehicle.ano}
                    </div>
                    <div class="detail-row">
                        <strong>Cor:</strong> ${vehicle.cor}
                    </div>
                    <div class="detail-row">
                        <strong>Proprietário:</strong> ${vehicle.nome_usuario}
                    </div>
                    <div class="detail-row">
                        <strong>Email:</strong> ${vehicle.email_usuario}
                    </div>
                </div>
            `;
            modal.style.display = 'block';
        }
        
        // Função para ligar para o proprietário
        function callOwner(ownerName, ownerEmail) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.display = 'block';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px; margin-top: 10%;">
                    <div class="modal-header">
                        <h2><i class="fas fa-phone"></i> Contatar Proprietário</h2>
                        <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div style="text-align: center; padding: 20px;">
                            <div style="margin-bottom: 20px;">
                                <i class="fas fa-user-circle" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                                <h3 style="margin: 0; color: #333;">${ownerName}</h3>
                                <p style="color: #666; margin: 5px 0;">${ownerEmail}</p>
                            </div>
                            <div style="display: flex; gap: 10px; justify-content: center;">
                                <button class="btn btn-success" onclick="window.open('tel:+5511999999999', '_self')">
                                    <i class="fas fa-phone"></i> Ligar Agora
                                </button>
                                <button class="btn btn-primary" onclick="window.open('mailto:${ownerEmail}', '_blank')">
                                    <i class="fas fa-envelope"></i> Enviar Email
                                </button>
                            </div>
                            <p style="font-size: 0.8rem; color: #999; margin-top: 15px;">
                                <i class="fas fa-info-circle"></i> 
                                Clique em "Ligar Agora" para iniciar uma chamada ou "Enviar Email" para abrir seu cliente de email.
                            </p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Fechar modal ao clicar fora
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        // Fechar modal
        function closeModal() {
            document.getElementById('vehicleModal').style.display = 'none';
        }
    </script>
    
    <!-- Modal de Detalhes -->
    <div id="vehicleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-car"></i> Detalhes do Veículo</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalVehicleInfo"></div>
            </div>
        </div>
    </div>
</body>
</html>
