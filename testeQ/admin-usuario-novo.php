<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Processar formulário
if ($_POST) {
    $nome = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $telefone = limparDados($_POST['telefone']);
    $cpf = limparDados($_POST['cpf']);
    $senha = $_POST['senha'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $status = $_POST['status'];
    
    // Validações
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email válido é obrigatório";
    }
    
    if (empty($cpf)) {
        $erros[] = "CPF é obrigatório";
    }
    
    if (empty($senha) || strlen($senha) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres";
    }
    
    if ($senha !== $_POST['confirmar_senha']) {
        $erros[] = "As senhas não coincidem";
    }
    
    if (empty($erros)) {
        $conexao = conectarBD();
        
        // Verificar se email já existe
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            exibirAlerta('error', 'Este email já está cadastrado.');
        } else {
            // Inserir novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Verificar se a coluna status existe
            if (colunaExiste($conexao, 'usuarios', 'status')) {
                $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, telefone, cpf, nivel_acesso, status, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssss", $nome, $email, $senha_hash, $telefone, $cpf, $nivel_acesso, $status);
            } else {
                $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, telefone, cpf, nivel_acesso, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $nome, $email, $senha_hash, $telefone, $cpf, $nivel_acesso);
            }
            
            if ($stmt->execute()) {
                $usuario_id = $conexao->insert_id;
                
                // Adicionar permissões básicas
                $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                $stmt->bind_param("is", $usuario_id, $nivel_acesso);
                $stmt->execute();
                
                // Adicionar permissões extras para admin e gerente
                if ($nivel_acesso == 'admin' || $nivel_acesso == 'gerente') {
                    $permissoes = ['gerenciar_usuarios', 'gerenciar_servicos', 'gerenciar_agendamentos'];
                    
                    if ($nivel_acesso == 'admin') {
                        $permissoes[] = 'gerenciar_ordens';
                    }
                    
                    foreach ($permissoes as $permissao) {
                        $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                        $stmt->bind_param("is", $usuario_id, $permissao);
                        $stmt->execute();
                    }
                }
                
                try {
                    registrarLog('usuario_criado', "Novo usuário criado: $nome ($email)");
                } catch (Exception $e) {
                    // Log error but continue
                    if (DEBUG_MODE) error_log("Erro ao registrar log: " . $e->getMessage());
                }
                
                exibirAlerta('success', 'Usuário criado com sucesso!');
                header("Location: admin-usuarios.php");
                exit;
            } else {
                exibirAlerta('error', 'Erro ao criar usuário.');
            }
        }
        
        $conexao->close();
    } else {
        exibirAlerta('error', implode('<br>', $erros));
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário - <?php echo SISTEMA_NOME; ?></title>
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
            max-width: 1400px;
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
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }
        
        .col {
            flex: 1;
            padding: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #109349;
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
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
        
        .btn-success {
            background-color: #109349;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        small {
            color: #6c757d;
            font-size: 0.875em;
        }
        
        .password-toggle:hover {
            color: var(--primary-color) !important;
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
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <script>
        // Função para mostrar/ocultar senha
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Novo Usuário</h1>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-plus"></i> Cadastrar Novo Usuário</h2>
                    <a href="admin-usuarios.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
                <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" class="form-control" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="telefone">Telefone *</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control" required value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="cpf">CPF *</label>
                                <input type="text" id="cpf" name="cpf" class="form-control" required value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="senha">Senha *</label>
                                <div style="position: relative;">
                                    <input type="password" id="senha" name="senha" class="form-control" required minlength="6" style="padding-right: 45px;">
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('senha')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                </div>
                                <small>Mínimo 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Senha *</label>
                                <div style="position: relative;">
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required minlength="6" style="padding-right: 45px;">
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_senha')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="nivel_acesso">Nível de Acesso *</label>
                                <select id="nivel_acesso" name="nivel_acesso" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="cliente" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="funcionario" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'funcionario') ? 'selected' : ''; ?>>Funcionário</option>
                                    <option value="gerente" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                                    <option value="admin" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="ativo" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                                    <option value="bloqueado" <?php echo (isset($_POST['status']) && $_POST['status'] == 'bloqueado') ? 'selected' : ''; ?>>Bloqueado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="admin-usuarios.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Salvar Usuário
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Máscaras para os campos
            $('#telefone').mask('(00) 00000-0000');
            $('#cpf').mask('000.000.000-00');
        });
    </script>
</body>
</html>
