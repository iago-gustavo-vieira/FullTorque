<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Admin - <?php echo defined('SISTEMA_NOME') ? SISTEMA_NOME : 'FullTorque'; ?></title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.2) 33%, rgba(221, 1, 0, 0.15) 33%, rgba(221, 1, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 100%) !important;
            background-color: #1a1a1a !important;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        body.theme-alemanha .container {
            background: #000000;
            color: white;
            box-shadow: 0 10px 30px rgba(255, 206, 0, 0.3);
        }
    </style>
</head>
<body>
<div class="container">
<?php
require_once 'config.php';

$conexao = conectarBD();

// Atualizar usuário admin existente
$email = "admin@autoservice.com";
$senha = password_hash("admin123", PASSWORD_DEFAULT);

// Verificar se existe o admin
$check = $conexao->query("SELECT id, email FROM usuarios WHERE email = '$email'");
if ($check->num_rows > 0) {
    $user = $check->fetch_assoc();
    $conexao->query("UPDATE usuarios SET senha = '$senha' WHERE id = " . $user['id']);
    echo "<strong>✅ Senha do Admin atualizada!</strong><br><br>";
    echo "<strong>Email:</strong> " . $user['email'] . "<br>";
    echo "<strong>Senha:</strong> admin123<br><br>";
    echo "<a href='verificar-admin.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>Verificar Admin</a>";
    echo "<a href='admin-login.php' style='background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Fazer Login</a>";
} else {
    echo "<strong>❌ Admin não encontrado!</strong><br><br>";
    echo "O usuário <strong>admin@autoservice.com</strong> não existe no banco de dados.<br><br>";
    echo "<a href='verificar-admin.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verificar Admin</a>";
}

$conexao->close();
?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'default';
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
    }
});
</script>
</body>
</html>
