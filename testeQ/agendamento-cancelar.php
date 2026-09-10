<?php
require_once 'config.php';

// Verifica se o usuário está logado
verificarLogin();

// Verifica se o ID do agendamento foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('danger', 'ID do agendamento inválido.');
    header("Location: agendamentos.php");
    exit;
}

$agendamento_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

// Verifica se o agendamento pertence ao usuário e está no status "agendado"
$stmt = $conexao->prepare("
    SELECT id, status FROM agendamentos 
    WHERE id = ? AND usuario_id = ? AND status = 'agendado'
");
$stmt->bind_param("ii", $agendamento_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    exibirAlerta('danger', 'Agendamento não encontrado, não pertence ao usuário ou não pode ser cancelado.');
    header("Location: agendamentos.php");
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $motivo = !empty($_POST['motivo']) ? limparDados($_POST['motivo']) : 'Cancelado pelo cliente';
    
    // Atualiza o status do agendamento para "cancelado"
    $stmt = $conexao->prepare("
        UPDATE agendamentos 
        SET status = 'cancelado', observacoes = CONCAT(IFNULL(observacoes, ''), '\n\nMotivo do cancelamento: ', ?)
        WHERE id = ? AND usuario_id = ? AND status = 'agendado'
    ");
    $stmt->bind_param("sii", $motivo, $agendamento_id, $usuario_id);
    
    if ($stmt->execute()) {
        exibirAlerta('success', 'Agendamento cancelado com sucesso!');
        registrarLog('agendamento_cancelado', 'Agendamento ID: ' . $agendamento_id . ' cancelado. Motivo: ' . $motivo);
    } else {
        exibirAlerta('danger', 'Erro ao cancelar o agendamento: ' . $conexao->error);
    }
    
    // Redireciona para a página de agendamentos
    header("Location: agendamentos.php");
    exit;
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Agendamento - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            background-color: #f8f9fa;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.theme-alemanha {
            background-color: #000000;
        }
        
        .sidebar {
            width: 250px;
            background-color: #109349;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        .theme-alemanha .sidebar{
            background-color: #000000;
            box-shadow: 8px 0 20px #DD0100;
        }
        
        .sidebar-header {
            padding: 0 0 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
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
            background-color: #DD0100;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .user-avatar i {
            font-size: 20px;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: white;
            position: relative;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #CE2B37;
            transform: translateX(5px);
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2rem;
            color: #2c3e50;
            font-weight: 600;
            animation: fadeInDown 0.6s ease;
        }
        
        .theme-alemanha .header h1 {
            color: white;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn {
            background-color: #CE2B37;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn:hover {
            background-color: #a01e28;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(206, 43, 55, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }
        
        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .theme-alemanha .card {
            background-color: #000000;
            box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3), 0 4px 10px rgba(255, 206, 0, 0.15);
            color: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .theme-alemanha .card:hover {
            box-shadow: 0 15px 40px rgba(255, 206, 0, 0.4), 0 8px 20px rgba(255, 206, 0, 0.25);
        }
        
        .card-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .theme-alemanha .card-header {
            background-color: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        
        .card-header h2 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            font-weight: 600;
        }
        
        .card-header h2 i {
            margin-right: 10px;
            color: #CE2B37;
        }
        
        .theme-alemanha .card-header h2 {
            color: white;
        }
        
        .theme-alemanha .card-header h2 i {
            color: #FFCE00;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .theme-alemanha .form-group textarea {
            background-color: #1a1a1a;
            border-color: #444;
            color: white;
        }
        
        .form-group textarea:focus {
            border-color: #CE2B37;
            outline: none;
            box-shadow: 0 0 0 3px rgba(206, 43, 55, 0.1);
        }
        
        .theme-alemanha .form-group textarea:focus {
            border-color: #FFCE00;
            box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.1);
        }
        
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 18px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: #856404;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease;
        }
        
        .warning-box i {
            margin-right: 12px;
            color: #ffc107;
            font-size: 1.3rem;
        }
        
        .theme-alemanha .warning-box {
            background-color: #2a2a2a;
            border-left-color: #FFCE00;
            color: #FFCE00;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            position: relative;
            overflow: hidden;
        }
        
        .btn-danger::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }
        
        .btn-danger:hover::after {
            width: 300px;
            height: 300px;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
        }
        
        .theme-alemanha .btn-danger {
            background-color: #DD0100;
        }
        
        .theme-alemanha .btn-danger:hover {
            background-color: #b00100;
            box-shadow: 0 8px 20px rgba(221, 1, 0, 0.4);
        }
        
        .btn-outline {
            background-color: transparent;
            color: #CE2B37;
            border: 2px solid #CE2B37;
        }
        
        .theme-alemanha .btn-outline {
            color: #FFCE00;
            border-color: #FFCE00;
        }
        
        .btn-outline:hover {
            background-color: #CE2B37;
            color: white;
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.3);
        }
        
        .theme-alemanha .btn-outline:hover {
            background-color: #FFCE00;
            color: #000;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .theme-toggle-container {
            position: relative;
            margin-top: 20px;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .theme-alemanha .theme-toggle-container {
            background: rgba(255, 206, 0, 0.2);
            border-top: 1px solid rgba(255, 206, 0, 0.3);
            box-shadow: 0 -5px 15px rgba(255, 206, 0, 0.4);
        }
        
        .theme-toggle-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #109349;
            transition: .4s;
            border-radius: 30px;
        }
        
        .theme-alemanha .slider {
            background-color: #FFCE00;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #000000;
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .flag-icon {
            display: inline-block;
            width: 20px;
            height: 14px;
            border-radius: 2px;
            margin: 0 3px;
        }
        
        .italy-flag {
            background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%) !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .germany-flag {
            background: linear-gradient(to bottom, #000000 0%, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%, #ffce00 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                position: fixed;
                z-index: 1001;
                transition: transform 0.3s ease;
                box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <script>
        function confirmarSaida() {
            if(confirm('Tem certeza que deseja sair?')) {
                window.location.href = 'logout.php';
            }
        }
        
        function toggleTheme() {
            const toggle = document.getElementById('themeToggle');
            const isGerman = toggle.checked;
            
            document.body.classList.remove('theme-alemanha');
            if (isGerman) {
                document.body.classList.add('theme-alemanha');
                localStorage.setItem('theme', 'theme-alemanha');
            } else {
                localStorage.setItem('theme', 'default');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'default';
            const toggle = document.getElementById('themeToggle');
            
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
                if (toggle) toggle.checked = true;
            }
        });
    </script>
    <div class="sidebar">
        <div class="sidebar-header" style="text-align: center;">
            <img src="logo.png" alt="<?php echo SISTEMA_NOME; ?>" style="max-width: 250px; height: auto; margin: 0 auto 10px auto; display: block;">
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['usuario_nome']; ?></div>
                <div class="user-role"><?php echo isset($_SESSION['usuario_nivel']) ? ucfirst($_SESSION['usuario_nivel']) : 'Usuário'; ?></div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Início</span>
            </a>
            <a href="veiculos.php" class="menu-item">
                <i class="fas fa-car"></i>
                <span>Meus Veículos</span>
            </a>
            <a href="agendamento-diagnostico.php" class="menu-item">
                <i class="fas fa-stethoscope"></i>
                <span>Solicitar Diagnóstico</span>
            </a>
            <a href="relatorios.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Meus Diagnósticos</span>
            </a>
            <a href="agendamentos.php" class="menu-item active">
                <i class="fas fa-calendar-alt"></i>
                <span>Agendamentos</span>
            </a>
            <a href="pagamentos.php" class="menu-item">
                <i class="fas fa-credit-card"></i>
                <span>Pagamentos</span>
            </a>
            <a href="historico.php" class="menu-item">
                <i class="fas fa-history"></i>
                <span>Histórico</span>
            </a>
            <a href="promocoes.php" class="menu-item">
                <i class="fas fa-gift"></i>
                <span>Promoções</span>
            </a>
            <a href="perfil.php" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Meu Perfil</span>
            </a>
            <a href="#" onclick="confirmarSaida()" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
        
        <div class="theme-toggle-container">
            <div class="theme-toggle-wrapper">
                <span class="flag-icon italy-flag"></span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
                    <span class="slider"></span>
                </label>
                <span class="flag-icon germany-flag"></span>
            </div>
        </div>
    </div>
    
    <div class="content">
        <div class="header">
            <h1>Cancelar Agendamento</h1>
            <a href="agendamentos.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <?php mostrarAlerta(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-times-circle"></i> Confirmar Cancelamento</h2>
            </div>
            <div class="card-body">
                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Atenção! Esta ação não pode ser desfeita. O agendamento será cancelado permanentemente.</span>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $agendamento_id); ?>" method="post">
                    <div class="form-group">
                        <label for="motivo">Motivo do Cancelamento (opcional)</label>
                        <textarea id="motivo" name="motivo" placeholder="Informe o motivo do cancelamento..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Confirmar Cancelamento
                        </button>
                        <a href="agendamentos.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>