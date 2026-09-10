<?php
require_once 'config.php';

// Verifica se a tabela existe, se não, cria
$conexao_temp = conectarBD();
$result = $conexao_temp->query("SHOW TABLES LIKE 'recuperacao_senha'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS `recuperacao_senha` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usuario_id` int(11) NOT NULL,
      `token` varchar(64) NOT NULL,
      `expira` datetime NOT NULL,
      `usado` tinyint(1) DEFAULT 0,
      `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `usuario_id` (`usuario_id`),
      KEY `token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conexao_temp->query($sql);
}
$conexao_temp->close();

$erro = '';
$sucesso = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = limparDados($_POST['email']);
    
    if (empty($email)) {
        $erro = "Por favor, informe seu email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        $conexao = conectarBD();
        
        $stmt = $conexao->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Limpa tokens antigos do usuário
            $conexao->query("DELETE FROM recuperacao_senha WHERE usuario_id = {$usuario['id']} AND expira < NOW()");
            
            $stmt = $conexao->prepare("INSERT INTO recuperacao_senha (usuario_id, token, expira) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $usuario['id'], $token, $expira);
            
            if ($stmt->execute()) {
                $link_recuperacao = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redefinir-senha.php?token=" . $token;
                
                $sucesso = "Solicitação processada com sucesso!";
                
                if (DEBUG_MODE) {
                    $sucesso .= "<br><br><strong>Link de recuperação:</strong><br><a href='$link_recuperacao' target='_blank' style='color: #CE2B37; word-break: break-all;'>$link_recuperacao</a>";
                    $sucesso .= "<br><br><small style='color: #666;'>Este link expira em 1 hora. Clique nele para redefinir sua senha.</small>";
                }
                
                registrarLog('recuperacao_senha', 'Solicitação de recuperação de senha para: ' . $email, $usuario['id']);
            } else {
                $erro = "Erro ao processar a solicitação. Tente novamente.";
            }
        } else {
            $sucesso = "Se o email estiver cadastrado em nosso sistema, você receberá as instruções para redefinir sua senha.";
        }
        
        $conexao->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - <?php echo SISTEMA_NOME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #009246 0%, #ffffff 50%, #CE2B37 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            transition: background 0.3s;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #009246, #CE2B37);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        body.theme-alemanha .header {
            background: linear-gradient(135deg, #000000, #FFCE00);
        }
        
        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .message i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .error-message {
            background-color: #fde8e8;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .success-message {
            background-color: #e6f7ef;
            color: #2ecc71;
            border-left: 4px solid #2ecc71;
        }
        
        .success-message a {
            color: #CE2B37;
            font-weight: 600;
            text-decoration: underline;
        }
        
        .success-message a:hover {
            color: #a01e28;
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
        }
        
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
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
            background-color: #009246;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #FFCE00;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        body.theme-alemanha .container {
            background-color: #1a1a1a;
            color: white;
        }
        
        body.theme-alemanha .form-container {
            background-color: #1a1a1a;
        }
        
        body.theme-alemanha .form-group input {
            background-color: #2a2a2a;
            color: white;
            border-color: #444;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        body.theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: #CE2B37;
            box-shadow: 0 0 0 2px rgba(206, 43, 55, 0.2);
            outline: none;
        }
        
        body.theme-alemanha .form-group input:focus {
            border-color: #FFCE00;
            box-shadow: 0 0 0 2px rgba(255, 206, 0, 0.2);
        }
        
        .form-group .icon {
            position: absolute;
            right: 15px;
            bottom: 12px;
            color: #999;
        }
        
        .btn {
            background: linear-gradient(135deg, #009246, #CE2B37);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        body.theme-alemanha .btn {
            background: linear-gradient(135deg, #000000, #FFCE00);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.4);
        }
        
        body.theme-alemanha .btn:hover {
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.4);
        }
        
        .links {
            text-align: center;
            font-size: 14px;
        }
        
        .links a {
            color: #CE2B37;
            text-decoration: none;
            font-weight: 500;
        }
        
        body.theme-alemanha .links a {
            color: #FFCE00;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recuperar Senha</h1>
            <p>Informe seu email para receber um link de recuperação</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($erro)): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($sucesso)): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Seu email cadastrado" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                </button>
                
                <div class="links">
                    <a href="home.php">Voltar para o Início</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="theme-toggle">
        <label class="theme-switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider"></span>
        </label>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const savedTheme = localStorage.getItem('theme');
            
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
                themeToggle.checked = true;
            }
            
            themeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('theme-alemanha');
                    localStorage.setItem('theme', 'theme-alemanha');
                } else {
                    document.body.classList.remove('theme-alemanha');
                    localStorage.setItem('theme', '');
                }
            });
        });
    </script>
</body>
</html>