<?php
require_once 'config.php';

$conexao = conectarBD();

$admin_email = "admin@autoservice.com";
$admin_senha = password_hash("admin123", PASSWORD_DEFAULT);
$admin_nome = "Administrador";
$admin_cpf = "000.000.000-00";
$data_cadastro = date('Y-m-d H:i:s');

// Deletar admin antigo se existir
$conexao->query("DELETE FROM usuarios WHERE email = '$admin_email'");

// Criar admin novo
$sql = "INSERT INTO usuarios (nome, email, cpf, senha, nivel_acesso, data_cadastro) 
        VALUES ('$admin_nome', '$admin_email', '$admin_cpf', '$admin_senha', 'admin', '$data_cadastro')";

$sucesso = $conexao->query($sql);
$erro = $conexao->error;

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Admin - FullTorque</title>
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
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 { color: #CE2B37; margin-bottom: 20px; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .credentials {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .credentials strong { color: #109349; }
        .btn {
            background: #109349;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        .btn:hover { background: #0d7a3a; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Criar Administrador</h1>
    
    <?php if ($sucesso): ?>
        <div class="success">
            <h2>✅ Admin Criado com Sucesso!</h2>
            <p>O administrador foi criado e está pronto para uso.</p>
        </div>
        
        <div class="credentials">
            <h3>🔑 Credenciais de Acesso:</h3>
            <p><strong>Email:</strong> admin@autoservice.com</p>
            <p><strong>Senha:</strong> admin123</p>
            <p><strong>Nível:</strong> Administrador</p>
        </div>
        
        <a href="admin-login.php" class="btn">🚀 Fazer Login Agora</a>
        <a href="verificar-usuarios.php" class="btn">👥 Ver Usuários</a>
    <?php else: ?>
        <div class="error">
            <h2>❌ Erro ao Criar Admin</h2>
            <p><?php echo htmlspecialchars($erro); ?></p>
        </div>
        
        <a href="verificar-usuarios.php" class="btn">👥 Ver Usuários</a>
        <a href="home.php" class="btn">🏠 Voltar</a>
    <?php endif; ?>
</div>
</body>
</html>
