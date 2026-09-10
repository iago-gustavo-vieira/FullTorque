<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$conexao = conectarBD();

// Buscar todos os relatórios (sem tabela relatorios_mecanico)
$stmt = $conexao->prepare("
    SELECT rc.*, u.nome as cliente_nome, u.email as cliente_email, 
           'Não atribuído' as analista_nome, 
           v.marca, v.modelo, v.placa,
           NULL as diagnostico, NULL as resposta_status
    FROM relatorios_cliente rc
    JOIN usuarios u ON rc.usuario_id = u.id
    JOIN veiculos v ON rc.veiculo_id = v.id
    ORDER BY rc.data_envio DESC
");
$stmt->execute();
$relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Estatísticas
$stats = [
    'total' => count($relatorios),
    'pendentes' => count(array_filter($relatorios, fn($r) => $r['status'] == 'pendente')),
    'analisados' => count(array_filter($relatorios, fn($r) => $r['status'] == 'analisado')),
    'respondidos' => count(array_filter($relatorios, fn($r) => $r['status'] == 'respondido')),
    'concluidos' => count(array_filter($relatorios, fn($r) => !empty($r['diagnostico'])))
];

$conexao->close();

$titulo = "Gerenciar Diagnósticos";
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
        
        body.dark-mode {
            --tertiary-color: #1a1a1a;
            --text-color: #f5f5f5;
            --secondary-color: #1e1e1e;
            background-color: #1a1a1a;
            color: #f5f5f5;
        }
        
        body.dark-mode .card,
        body.dark-mode .stat-card {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .card-header {
            background-color: #333;
            border-color: #444;
        }
        

        
        .sidebar {
            width: 250px;
            background-color: #109349;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
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
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .theme-alemanha .stat-card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 10px rgba(255, 206, 0, 0.3);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #109349;
            margin-bottom: 8px;
        }
        
        .theme-alemanha .stat-number {
            color: #FFCE00;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .theme-alemanha .stat-label {
            color: #ffffff;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 10px rgba(255, 206, 0, 0.3);
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
            padding: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
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
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
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
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-pendente {
            background: #ffeaa7;
            color: #d63031;
        }
        
        .status-analisado {
            background: #74b9ff;
            color: white;
        }
        
        .status-respondido {
            background: #109349;
            color: white;
        }
        
        .theme-alemanha .status-pendente {
            background: #DD0100;
            color: #FFCE00;
        }
        
        .theme-alemanha .status-analisado {
            background: #FFCE00;
            color: #000000;
        }
        
        .theme-alemanha .status-respondido {
            background: #000000;
            color: #FFCE00;
            border: 1px solid #FFCE00;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .action-buttons .btn {
            min-width: 35px;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 3px;
            }
            
            .action-buttons .btn {
                width: 100%;
                min-width: auto;
            }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 900px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .theme-alemanha .modal-content {
            background: #000000;
            border: 2px solid #FFCE00;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.5);
        }
        
        .theme-alemanha .modal-content h2 {
            color: #FFCE00;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #109349;
        }
        
        .theme-alemanha .info-section {
            background: #1a1a1a;
            border-left: 4px solid #FFCE00;
        }
        
        .info-section h4 {
            color: #109349;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .theme-alemanha .info-section h4 {
            color: #FFCE00;
        }
        
        .theme-alemanha .info-section p,
        .theme-alemanha .info-section textarea {
            color: #ffffff;
        }
        
        .custom-popup {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            animation: fadeIn 0.3s;
        }
        
        .popup-content {
            background: white;
            margin: 15% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .popup-header {
            background: #DD0100;
            color: white;
            padding: 20px 25px;
            text-align: center;
        }
        
        .popup-header i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .popup-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .popup-body {
            padding: 25px;
            text-align: center;
        }
        
        .popup-body p {
            color: #555;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        
        .popup-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .popup-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 100px;
        }
        
        .popup-btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .popup-btn-cancel:hover {
            background: #5a6268;
        }
        
        .popup-btn-confirm {
            background: #DD0100;
            color: white;
        }
        
        .popup-btn-confirm:hover {
            background: #b8010d;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-input, .filter-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .theme-alemanha .filter-input,
        .theme-alemanha .filter-select {
            background: #1a1a1a;
            border: 1px solid #DD0100;
            color: #ffffff;
        }
        
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #109349;
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        .results-info {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .theme-alemanha .results-info {
            color: #FFCE00;
        }
        
        .table-row-hidden {
            display: none !important;
        }
        
        .diagnosticos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .diagnostico-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .theme-alemanha .diagnostico-card {
            background: #1a1a1a;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 8px rgba(255, 206, 0, 0.2);
        }
        
        .diagnostico-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .diagnostico-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .theme-alemanha .diagnostico-header {
            border-bottom-color: #333;
        }
        
        .diagnostico-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #109349;
        }
        
        .theme-alemanha .diagnostico-id {
            color: #FFCE00;
        }
        
        .diagnostico-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #555;
        }
        
        .theme-alemanha .info-row {
            color: #ffffff;
        }
        
        .info-row i {
            color: #109349;
            width: 20px;
            text-align: center;
        }
        
        .theme-alemanha .info-row i {
            color: #FFCE00;
        }
        
        .diagnostico-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .theme-alemanha .diagnostico-actions {
            border-top-color: #333;
        }
        
        .diagnostico-actions .btn {
            flex: 1;
            min-width: 100px;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .diagnosticos-grid {
                grid-template-columns: 1fr;
            }
            
            .diagnostico-card {
                padding: 15px;
            }
            
            .diagnostico-actions .btn {
                min-width: auto;
                font-size: 0.8rem;
                padding: 8px 10px;
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
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .container {
                padding: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }
            
            .card-header h2 {
                font-size: 1.1rem;
            }
            
            .results-info {
                font-size: 12px;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
                min-width: auto;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            
            .status-badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
            
            .popup-content {
                width: 95%;
                margin: 30% auto;
            }
            
            .info-section {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header h2 {
                font-size: 1rem;
            }
            
            table {
                font-size: 0.75rem;
            }
            
            th, td {
                padding: 8px 5px;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text">Relatórios e Diagnósticos</div>
            <h1><i class="fas fa-stethoscope"></i> Relatórios e Diagnósticos</h1>
            <p>Gerencie todos os diagnósticos e relatórios dos clientes</p>
        </div>
        <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total de Relatórios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pendentes']; ?></div>
                <div class="stat-label">Pendentes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['analisados']; ?></div>
                <div class="stat-label">Analisados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['respondidos']; ?></div>
                <div class="stat-label">Respondidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['concluidos']; ?></div>
                <div class="stat-label">Concluídos</div>
            </div>
        </div>
        
        <!-- Filtros de Busca -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-filter"></i> Filtros de Busca</h2>
            </div>
            <div class="card-body">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" id="searchInput" placeholder="Buscar por cliente, analista, veículo..." class="filter-input">
                    </div>
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-select">
                            <option value="">Todos os Status</option>
                            <option value="pendente">Pendente</option>
                            <option value="analisado">Analisado</option>
                            <option value="respondido">Respondido</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="diagnosticoFilter" class="filter-select">
                            <option value="">Todos os Diagnósticos</option>
                            <option value="concluido">Concluído</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button onclick="limparFiltros()" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-stethoscope"></i> Todos os Diagnósticos</h2>
                <div class="results-info">
                    <span id="resultsCount">Mostrando <?php echo count($relatorios); ?> resultados</span>
                </div>
            </div>
            <div class="card-body">
                <div class="diagnosticos-grid">
                    <?php foreach ($relatorios as $relatorio): ?>
                        <div class="diagnostico-card" data-cliente="<?php echo strtolower($relatorio['cliente_nome']); ?>" data-analista="<?php echo strtolower($relatorio['analista_nome']); ?>" data-veiculo="<?php echo strtolower($relatorio['marca'] . ' ' . $relatorio['modelo']); ?>" data-status="<?php echo $relatorio['status']; ?>" data-diagnostico="<?php echo $relatorio['diagnostico'] ? 'concluido' : 'pendente'; ?>">
                            <div class="diagnostico-header">
                                <div class="diagnostico-id">#<?php echo $relatorio['id']; ?></div>
                                <span class="status-badge status-<?php echo $relatorio['status']; ?>">
                                    <?php 
                                    switch($relatorio['status']) {
                                        case 'pendente': echo 'Pendente'; break;
                                        case 'analisado': echo 'Analisado'; break;
                                        case 'respondido': echo 'Respondido'; break;
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="diagnostico-info">
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <span><strong>Cliente:</strong> <?php echo $relatorio['cliente_nome']; ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-user-md"></i>
                                    <span><strong>Analista:</strong> <?php echo $relatorio['analista_nome']; ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-car"></i>
                                    <span><strong>Veículo:</strong> <?php echo $relatorio['marca'] . ' ' . $relatorio['modelo']; ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span><strong>Data:</strong> <?php echo formatarData($relatorio['data_envio'], 'd/m/Y H:i'); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-stethoscope"></i>
                                    <span><strong>Diagnóstico:</strong> <?php echo $relatorio['diagnostico'] ? '<span style="color: #109349;">Concluído</span>' : '<span style="color: #f39c12;">Pendente</span>'; ?></span>
                                </div>
                            </div>
                            <div class="diagnostico-actions">
                                <button onclick="verDetalhes(<?php echo $relatorio['id']; ?>)" class="btn btn-info btn-sm" title="Ver detalhes">
                                    <i class="fas fa-eye"></i> Detalhes
                                </button>
                                <button onclick="baixarPDF(<?php echo $relatorio['id']; ?>)" class="btn btn-primary btn-sm" title="Baixar PDF">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                
                                <?php if ($relatorio['status'] == 'pendente' || $relatorio['status'] == 'analisado'): ?>
                                <button onclick="mudarStatus(<?php echo $relatorio['id']; ?>, '<?php echo $relatorio['status']; ?>')" class="btn btn-warning btn-sm" title="Alterar status">
                                    <i class="fas fa-edit"></i> Status
                                </button>
                                <?php elseif ($relatorio['status'] == 'respondido'): ?>
                                <button onclick="voltarStatus(<?php echo $relatorio['id']; ?>)" class="btn btn-secondary btn-sm" title="Voltar status">
                                    <i class="fas fa-undo"></i> Voltar
                                </button>
                                <?php endif; ?>
                                
                                <?php if (!$relatorio['diagnostico'] && $relatorio['status'] != 'pendente'): ?>
                                <button onclick="confirmarDiagnostico(<?php echo $relatorio['id']; ?>)" class="btn btn-success btn-sm" title="Confirmar diagnóstico">
                                    <i class="fas fa-check"></i> Confirmar
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="excluirDiagnostico(<?php echo $relatorio['id']; ?>)" class="btn btn-danger btn-sm" title="Excluir">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Modal para detalhes -->
    <div id="modalDetalhes" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalDetalhes')">&times;</span>
            <div id="conteudoDetalhes">
                <!-- Conteúdo será carregado via JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Modal para mudar status -->
    <div id="modalStatus" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalStatus')">&times;</span>
            <h2><i class="fas fa-edit"></i> Alterar Status do Diagnóstico</h2>
            <form id="formStatus" method="POST" action="processar-status-diagnostico.php">
                <input type="hidden" id="relatorioId" name="relatorio_id">
                <div class="info-section">
                    <h4>Novo Status</h4>
                    <select name="novo_status" id="novoStatus" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 15px;">
                        <option value="pendente">Pendente</option>
                        <option value="analisado">Analisado</option>
                        <option value="respondido">Respondido</option>
                    </select>
                </div>
                <div class="info-section">
                    <h4>Observações (opcional)</h4>
                    <textarea name="observacoes" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px;" placeholder="Adicione observações sobre a mudança de status..."></textarea>
                </div>
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="fecharModal('modalStatus')" class="btn" style="background: #6c757d; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Status
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para confirmar diagnóstico -->
    <div id="modalDiagnostico" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalDiagnostico')">&times;</span>
            <h2><i class="fas fa-check-circle"></i> Confirmar Diagnóstico</h2>
            <form id="formDiagnostico" method="POST" action="processar-diagnostico.php">
                <input type="hidden" id="diagnosticoRelatorioId" name="relatorio_id">
                <div class="info-section">
                    <h4>Diagnóstico Gratuito</h4>
                    <textarea name="diagnostico" required style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; min-height: 120px;" placeholder="Digite o diagnóstico gratuito para o cliente..."></textarea>
                </div>
                <div class="info-section">
                    <h4>Observações Adicionais (opcional)</h4>
                    <textarea name="observacoes_diagnostico" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px;" placeholder="Observações adicionais sobre o diagnóstico..."></textarea>
                </div>
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="fecharModal('modalDiagnostico')" class="btn" style="background: #6c757d; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Custom Popup para confirmar voltar status -->
    <div id="customPopup" class="custom-popup">
        <div class="popup-content">
            <div class="popup-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Confirmar Ação</h3>
            </div>
            <div class="popup-body">
                <p>Tem certeza que deseja voltar o status deste diagnóstico?<br><br>Esta ação irá alterar o status para <strong>"analisado"</strong>.</p>
                <div class="popup-buttons">
                    <button class="popup-btn popup-btn-cancel" onclick="fecharPopup()">Cancelar</button>
                    <button class="popup-btn popup-btn-confirm" onclick="confirmarVoltarStatus()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Custom Popup para confirmar exclusão -->
    <div id="popupExcluir" class="custom-popup">
        <div class="popup-content">
            <div class="popup-header">
                <i class="fas fa-trash-alt"></i>
                <h3>Excluir Diagnóstico</h3>
            </div>
            <div class="popup-body">
                <p>Tem certeza que deseja excluir este diagnóstico?<br><br><strong>Esta ação não pode ser desfeita!</strong></p>
                <div class="popup-buttons">
                    <button class="popup-btn popup-btn-cancel" onclick="fecharPopupExcluir()">Cancelar</button>
                    <button class="popup-btn popup-btn-confirm" onclick="confirmarExclusao()">Excluir</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
        // Aplicar tema salvo
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'default';
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
        });
        
        function baixarPDF(relatorioId) {
            const tema = localStorage.getItem('theme') === 'theme-alemanha' ? 'alemanha' : 'italia';
            window.open('gerar-pdf-diagnostico.php?id=' + relatorioId + '&tema=' + tema, '_blank');
        }
        
        // Script original
        function verDetalhes(relatorioId) {
            // Buscar dados do relatório
            const relatorio = <?php echo json_encode($relatorios); ?>.find(r => r.id == relatorioId);
            
            if (!relatorio) return;
            
            const conteudo = document.getElementById('conteudoDetalhes');
            conteudo.innerHTML = `
                <h2>Relatório #${relatorio.id}</h2>
                
                <div class="info-section">
                    <h4><i class="fas fa-user"></i> Cliente</h4>
                    <p><strong>Nome:</strong> ${relatorio.cliente_nome}</p>
                    <p><strong>Email:</strong> ${relatorio.cliente_email}</p>
                </div>
                
                <div class="info-section">
                    <h4><i class="fas fa-car"></i> Veículo</h4>
                    <p><strong>Veículo:</strong> ${relatorio.marca} ${relatorio.modelo}</p>
                    <p><strong>Placa:</strong> ${relatorio.placa}</p>
                </div>
                
                <div class="info-section">
                    <h4><i class="fas fa-user-md"></i> Analista</h4>
                    <p><strong>Nome:</strong> ${relatorio.analista_nome}</p>
                </div>
                
                <div class="info-section">
                    <h4><i class="fas fa-calendar"></i> Informações do Relatório</h4>
                    <p><strong>Data de Envio:</strong> ${formatarDataJS(relatorio.data_envio)}</p>
                    <p><strong>Status:</strong> ${relatorio.status}</p>
                    ${relatorio.descricao_problema ? `<p><strong>Problema:</strong> ${relatorio.descricao_problema}</p>` : '<p><strong>Tipo:</strong> Revisão Geral</p>'}
                    <p><strong>Endereço:</strong> ${relatorio.endereco_completo}</p>
                </div>
                
                ${relatorio.diagnostico ? `
                    <div class="info-section">
                        <h4><i class="fas fa-stethoscope"></i> Diagnóstico Gratuito</h4>
                        <p>${relatorio.diagnostico}</p>
                        <small style="color: #666;"><i class="fas fa-check-circle"></i> Diagnóstico concluído</small>
                    </div>
                ` : ''}
            `;
            
            document.getElementById('modalDetalhes').style.display = 'block';
        }
        
        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function mudarStatus(relatorioId, statusAtual) {
            document.getElementById('relatorioId').value = relatorioId;
            document.getElementById('novoStatus').value = statusAtual;
            document.getElementById('modalStatus').style.display = 'block';
        }
        
        function confirmarDiagnostico(relatorioId) {
            document.getElementById('diagnosticoRelatorioId').value = relatorioId;
            document.getElementById('modalDiagnostico').style.display = 'block';
        }
        
        let relatorioIdParaVoltar = null;
        let relatorioIdParaExcluir = null;
        
        function voltarStatus(relatorioId) {
            relatorioIdParaVoltar = relatorioId;
            document.getElementById('customPopup').style.display = 'block';
        }
        
        function fecharPopup() {
            document.getElementById('customPopup').style.display = 'none';
            relatorioIdParaVoltar = null;
        }
        
        function excluirDiagnostico(relatorioId) {
            relatorioIdParaExcluir = relatorioId;
            document.getElementById('popupExcluir').style.display = 'block';
        }
        
        function fecharPopupExcluir() {
            document.getElementById('popupExcluir').style.display = 'none';
            relatorioIdParaExcluir = null;
        }
        
        function confirmarExclusao() {
            if (relatorioIdParaExcluir) {
                window.location.href = 'excluir-diagnostico.php?id=' + relatorioIdParaExcluir;
            }
        }
        
        function confirmarVoltarStatus() {
            if (relatorioIdParaVoltar) {
                // Criar formulário temporário para enviar a requisição
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'processar-status-diagnostico.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'relatorio_id';
                inputId.value = relatorioIdParaVoltar;
                
                const inputStatus = document.createElement('input');
                inputStatus.type = 'hidden';
                inputStatus.name = 'novo_status';
                inputStatus.value = 'analisado';
                
                const inputObs = document.createElement('input');
                inputObs.type = 'hidden';
                inputObs.name = 'observacoes';
                inputObs.value = 'Status revertido pelo administrador';
                
                form.appendChild(inputId);
                form.appendChild(inputStatus);
                form.appendChild(inputObs);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function formatarDataJS(dataString) {
            const data = new Date(dataString);
            return data.toLocaleString('pt-BR');
        }
        
        // Funções de filtro
        function filtrarTabela() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const diagnosticoFilter = document.getElementById('diagnosticoFilter').value;
            
            const cards = document.querySelectorAll('.diagnostico-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const cliente = card.dataset.cliente;
                const analista = card.dataset.analista;
                const veiculo = card.dataset.veiculo;
                const status = card.dataset.status;
                const diagnostico = card.dataset.diagnostico;
                
                const matchesSearch = !searchTerm || 
                    cliente.includes(searchTerm) || 
                    analista.includes(searchTerm) || 
                    veiculo.includes(searchTerm);
                
                const matchesStatus = !statusFilter || status === statusFilter;
                
                const matchesDiagnostico = !diagnosticoFilter || diagnostico === diagnosticoFilter;
                
                if (matchesSearch && matchesStatus && matchesDiagnostico) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.getElementById('resultsCount').textContent = `Mostrando ${visibleCount} resultados`;
        }
        
        function limparFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('diagnosticoFilter').value = '';
            filtrarTabela();
        }
        
        // Event listeners para filtros
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').addEventListener('input', filtrarTabela);
            document.getElementById('statusFilter').addEventListener('change', filtrarTabela);
            document.getElementById('diagnosticoFilter').addEventListener('change', filtrarTabela);
        });
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modals = ['modalDetalhes', 'modalStatus', 'modalDiagnostico'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    fecharModal(modalId);
                }
            });
            
            // Fechar popup personalizado
            const popup = document.getElementById('customPopup');
            if (event.target == popup) {
                fecharPopup();
            }
            
            // Fechar popup de exclusão
            const popupExcluir = document.getElementById('popupExcluir');
            if (event.target == popupExcluir) {
                fecharPopupExcluir();
            }
        }
    </script>
</body>
</html>
