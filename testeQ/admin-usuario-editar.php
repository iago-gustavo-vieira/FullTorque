<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-usuarios.php");
    exit;
}

$id = (int)$_GET['id'];

// Buscar dados do usuário
$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exibirAlerta('error', 'Usuário não encontrado.');
    header("Location: admin-usuarios.php");
    exit;
}

$usuario = $result->fetch_assoc();

// Processar formulário
if ($_POST) {
    $nome = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $telefone = limparDados($_POST['telefone']);
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
    
    if (empty($erros)) {
        // Verificar se email já existe (exceto para o próprio usuário)
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            exibirAlerta('error', 'Este email já está cadastrado por outro usuário.');
        } else {
            // Atualizar usuário
            $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, nivel_acesso = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $nome, $email, $telefone, $nivel_acesso, $status, $id);
            
            if ($stmt->execute()) {
                // Atualizar senha se fornecida
                if (!empty($_POST['nova_senha'])) {
                    $nova_senha = $_POST['nova_senha'];
                    if (strlen($nova_senha) >= 6) {
                        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                        $stmt->bind_param("si", $senha_hash, $id);
                        $stmt->execute();
                    } else {
                        exibirAlerta('warning', 'Senha deve ter pelo menos 6 caracteres. Senha não alterada.');
                    }
                }
                
                // Atualizar permissões
                $stmt = $conexao->prepare("DELETE FROM usuario_permissoes WHERE usuario_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Inserir nova permissão base
                $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $nivel_acesso);
                $stmt->execute();
                
                // Adicionar permissões extras para admin e gerente
                if ($nivel_acesso == 'admin' || $nivel_acesso == 'gerente') {
                    $permissoes = ['gerenciar_usuarios', 'gerenciar_servicos', 'gerenciar_agendamentos'];
                    
                    if ($nivel_acesso == 'admin') {
                        $permissoes[] = 'gerenciar_ordens';
                    }
                    
                    foreach ($permissoes as $permissao) {
                        $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                        $stmt->bind_param("is", $id, $permissao);
                        $stmt->execute();
                    }
                }
                
                try {
                    registrarLog('usuario_editado', "Usuário editado: $nome ($email)");
                } catch (Exception $e) {
                    // Log error but continue
                    if (DEBUG_MODE) error_log("Erro ao registrar log: " . $e->getMessage());
                }
                
                exibirAlerta('success', 'Usuário atualizado com sucesso!');
                
                // Recarregar dados do usuário
                $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
            } else {
                exibirAlerta('error', 'Erro ao atualizar usuário.');
            }
        }
    } else {
        exibirAlerta('error', implode('<br>', $erros));
    }
}

$conexao->close();

$titulo = "Editar Usuário";
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
            max-width: 1000px;
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
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
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
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #109349;
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        .form-control:disabled {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #109349;
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
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
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
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #eee;
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
            background-color: rgba(221, 1, 0, 0.1);
            border-left: 4px solid #DD0100;
            color: #DD0100;
        }
        
        .alert-warning {
            background-color: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning-color);
            color: var(--warning-color);
        }
        
        small {
            color: #6c757d;
            font-size: 0.875em;
            margin-top: 5px;
            display: block;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-user-edit"></i> Editar Usuário</h1>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> Dados do Usuário</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" class="form-control" required value="<?php echo htmlspecialchars($usuario['nome']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($usuario['email']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control" value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <div class="password-field">
                                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" minlength="6">
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('nova_senha')"></i>
                                </div>
                                <small>Deixe em branco para manter a senha atual</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nivel_acesso">Nível de Acesso *</label>
                                <select id="nivel_acesso" name="nivel_acesso" class="form-control" required <?php echo ($usuario['id'] == 1) ? 'disabled' : ''; ?>>
                                    <option value="cliente" <?php echo ($usuario['nivel_acesso'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="funcionario" <?php echo ($usuario['nivel_acesso'] == 'funcionario') ? 'selected' : ''; ?>>Funcionário</option>
                                    <option value="gerente" <?php echo ($usuario['nivel_acesso'] == 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                                    <option value="admin" <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                                <?php if ($usuario['id'] == 1): ?>
                                    <input type="hidden" name="nivel_acesso" value="admin">
                                    <small>O usuário principal não pode ter o nível alterado</small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" class="form-control" required <?php echo ($usuario['id'] == 1) ? 'disabled' : ''; ?>>
                                    <option value="ativo" <?php echo ($usuario['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo ($usuario['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                                    <option value="bloqueado" <?php echo ($usuario['status'] == 'bloqueado') ? 'selected' : ''; ?>>Bloqueado</option>
                                </select>
                                <?php if ($usuario['id'] == 1): ?>
                                    <input type="hidden" name="status" value="ativo">
                                    <small>O usuário principal não pode ser desativado</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h4><i class="fas fa-info-circle"></i> Informações do Sistema</h4>
                            <p><strong>ID:</strong> <?php echo $usuario['id']; ?></p>
                            <p><strong>Cadastrado em:</strong> <?php echo formatarData($usuario['data_cadastro']); ?></p>
                            <p><strong>Última atualização:</strong> <?php echo isset($usuario['data_atualizacao']) ? formatarData($usuario['data_atualizacao']) : 'N/A'; ?></p>
                        </div>
                        
                        <div class="form-actions">
                            <a href="admin-usuarios.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
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
</body>
</html>
