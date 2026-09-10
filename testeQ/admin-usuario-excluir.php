<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('error', 'ID do usuário inválido.');
    header("Location: admin-usuarios.php");
    exit;
}

$id = (int)$_GET['id'];

// Não permitir excluir o próprio usuário admin ou usuário ID 1
if ($id == $_SESSION['usuario_id'] || $id == 1) {
    exibirAlerta('error', 'Você não pode excluir este usuário.');
    header("Location: admin-usuarios.php");
    exit;
}

$conexao = conectarBD();

// Buscar dados do usuário
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exibirAlerta('error', 'Usuário não encontrado.');
    header("Location: admin-usuarios.php");
    exit;
}

$usuario = $result->fetch_assoc();

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_exclusao'])) {
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        registrarLog('usuario_excluido', "Usuário excluído: {$usuario['nome']} (ID: $id)");
        exibirAlerta('success', 'Usuário excluído com sucesso.');
        header("Location: admin-usuarios.php");
        exit;
    } else {
        exibirAlerta('error', 'Erro ao excluir usuário.');
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário - <?php echo SISTEMA_NOME; ?></title>
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
        
        .header {
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.2rem;
            color: #e74c3c;
            font-weight: 600;
        }
        
        .container {
            max-width: 500px;
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
            background: #e74c3c;
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
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .warning-icon {
            font-size: 2rem;
            color: #f39c12;
            margin-bottom: 10px;
        }
        
        .warning-text {
            font-size: 1rem;
            color: #856404;
            margin-bottom: 8px;
        }
        
        .warning-subtext {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .user-name {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .user-name h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .user-name-display {
            font-size: 1.3rem;
            font-weight: 600;
            color: #e74c3c;
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 2px solid #e74c3c;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #109349;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 20px;
            }
            
            .header {
                padding: 15px 0;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .container {
                padding: 10px;
            }
            
            .card-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .card-body {
                padding: 20px 15px;
            }
            
            .warning-box {
                padding: 15px;
            }
            
            .warning-icon {
                font-size: 2.5rem;
            }
            
            .warning-text {
                font-size: 1rem;
            }
            
            .user-name {
                padding: 15px;
            }
            
            .user-name-display {
                font-size: 1.1rem;
                padding: 12px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                padding: 15px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .content {
                padding: 60px 10px 15px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .card-header h2 {
                font-size: 1.2rem;
            }
            
            .warning-text {
                font-size: 0.95rem;
            }
            
            .warning-subtext {
                font-size: 0.85rem;
            }
            
            .user-name h3 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-exclamation-triangle"></i> Excluir Usuário</h1>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-times"></i> Confirmar Exclusão</h2>
                    <a href="admin-usuarios.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
                <div class="card-body">
                    <div class="warning-box">
                        <div class="warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="warning-text">
                            <strong>ATENÇÃO!</strong> Esta ação não pode ser desfeita.
                        </div>
                        <div class="warning-subtext">
                            Todos os dados relacionados a este usuário serão permanentemente removidos do sistema.
                        </div>
                    </div>
                    
                    <div class="user-name">
                        <h3>Usuário que será excluído:</h3>
                        <div class="user-name-display">
                            <?php echo $usuario['nome']; ?>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="form-actions">
                            <a href="admin-usuarios.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" name="confirmar_exclusao" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Confirmar Exclusão
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Fechar sidebar ao clicar fora (mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Ajustar layout ao redimensionar
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>