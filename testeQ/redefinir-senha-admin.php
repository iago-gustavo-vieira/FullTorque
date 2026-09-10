<?php
require_once 'config.php';

$usuario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sucesso = false;
$erro = '';

$conexao = conectarBD();

// Buscar usuário
$stmt = $conexao->prepare("SELECT id, nome, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result->fetch_assoc();

// Processar redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    if (empty($nova_senha) || strlen($nova_senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $usuario_id);
        
        if ($stmt->execute()) {
            $sucesso = true;
        } else {
            $erro = 'Erro ao atualizar senha.';
        }
    }
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - FullTorque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #109349, #CE2B37);
            padding: 20px;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 { color: #CE2B37; margin-bottom: 10px; }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .user-info strong { color: #109349; }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #109349;
        }
        .btn {
            background: #109349;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
        }
        .btn:hover { background: #0d7a3a; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #109349;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔑 Redefinir Senha</h1>
    
    <div class="user-info">
        <strong>Usuário:</strong> <?php echo htmlspecialchars($usuario['nome']); ?><br>
        <strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?>
    </div>
    
    <?php if ($sucesso): ?>
        <div class="success">
            <strong>✅ Senha atualizada com sucesso!</strong><br>
            O usuário já pode fazer login com a nova senha.
        </div>
        <div class="back-link">
            <a href="verificar-usuarios.php">← Voltar para Lista de Usuários</a>
        </div>
    <?php else: ?>
        <?php if (!empty($erro)): ?>
            <div class="error">
                <strong>❌ Erro:</strong> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite a senha novamente" required>
            </div>
            
            <button type="submit" class="btn">💾 Salvar Nova Senha</button>
        </form>
        
        <div class="back-link">
            <a href="verificar-usuarios.php">← Cancelar</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
