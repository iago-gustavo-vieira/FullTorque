<?php
session_start();
require_once 'config.php';

$token = isset($_GET['token']) ? limparDados($_GET['token']) : '';
$mensagem = '';
$erro = '';
$tokenValido = false;

if (!empty($token)) {
    $conexao = conectarBD();
    $stmt = $conexao->prepare("SELECT r.id, r.usuario_id, u.email, u.senha FROM recuperacao_senha r JOIN usuarios u ON r.usuario_id = u.id WHERE r.token = ? AND r.expira > NOW() AND r.usado = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $tokenValido = true;
        $dados = $resultado->fetch_assoc();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $novaSenha = $_POST['senha'];
            $confirmarSenha = $_POST['confirmar_senha'];
            
            if (strlen($novaSenha) < 6) {
                $erro = 'A senha deve ter no mínimo 6 caracteres.';
            } elseif ($novaSenha !== $confirmarSenha) {
                $erro = 'As senhas não coincidem.';
            } elseif (password_verify($novaSenha, $dados['senha'])) {
                $erro = 'A nova senha não pode ser igual à senha atual. Por favor, escolha uma senha diferente.';
            } else {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->bind_param("si", $senhaHash, $dados['usuario_id']);
                
                if ($stmt->execute()) {
                    $conexao->query("UPDATE recuperacao_senha SET usado = 1 WHERE id = {$dados['id']}");
                    registrarLog('senha_redefinida', 'Senha redefinida com sucesso', $dados['usuario_id']);
                    $mensagem = 'Senha redefinida com sucesso! Redirecionando...';
                    echo "<script>
                        localStorage.setItem('email_login_redefinicao', '{$dados['email']}');
                        localStorage.setItem('abrir_modal_login', 'true');
                        setTimeout(function() {
                            window.location.href = 'home.php';
                        }, 2000);
                    </script>";
                } else {
                    $erro = 'Erro ao redefinir senha. Tente novamente.';
                }
            }
        }
    } else {
        $erro = 'Link inválido ou expirado. Solicite um novo link de recuperação.';
    }
    $conexao->close();
} else {
    $erro = 'Token não fornecido.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Redefinir Senha - FullTorque</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #009246 0%, #CE2B37 100%);
            min-height: 100vh;
            padding: 15px;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, #000000 0%, #FFCE00 100%);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 50px;
        }
        
        .theme-toggle {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 8px 12px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .flag-icon {
            width: 18px;
            height: 13px;
            border-radius: 2px;
        }
        
        .italy-flag {
            background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%);
        }
        
        .germany-flag {
            background: linear-gradient(to bottom, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%);
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 25px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .theme-alemanha .container {
            background: #1a1a1a;
            color: white;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 1.5rem;
        }
        
        .theme-alemanha h1 {
            color: #FFCE00;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        
        .theme-alemanha .subtitle {
            color: #ccc;
        }
        
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #fde8e8;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .theme-alemanha label {
            color: #FFCE00;
        }
        
        input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .theme-alemanha input {
            background: #2a2a2a;
            border-color: #444;
            color: white;
        }
        
        input:focus {
            outline: none;
            border-color: #CE2B37;
            box-shadow: 0 0 0 3px rgba(206, 43, 55, 0.1);
        }
        
        .theme-alemanha input:focus {
            border-color: #FFCE00;
            box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            cursor: pointer;
            color: #666;
            font-size: 18px;
        }
        
        .theme-alemanha .password-toggle {
            color: #ccc;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
            border-radius: 2px;
        }
        
        .password-strength-text {
            margin-top: 5px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .strength-weak { background: #e74c3c; }
        .strength-medium { background: #f39c12; }
        .strength-good { background: #3498db; }
        .strength-strong { background: #2ecc71; }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #009246, #CE2B37);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .theme-alemanha .btn {
            background: linear-gradient(135deg, #000000, #FFCE00);
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #CE2B37;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .theme-alemanha .back-link a {
            color: #FFCE00;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="logo.png" alt="FullTorque">
        </div>
        <div class="theme-toggle">
            <span class="flag-icon italy-flag"></span>
            <label style="cursor: pointer; margin: 0;">
                <input type="checkbox" id="themeToggle" style="display: none;">
                <span style="color: white; font-size: 14px;">🇩🇪</span>
            </label>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($erro)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $erro; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($mensagem)): ?>
            <div class="alert" style="background: #e6f7ef; color: #2ecc71; border-left: 4px solid #2ecc71;">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $mensagem; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($tokenValido): ?>
            <h1>🔐 Redefinir Senha</h1>
            <p class="subtitle">Digite sua nova senha abaixo</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="senha">Nova Senha</label>
                    <input type="password" id="senha" name="senha" required minlength="6" placeholder="Mínimo 6 caracteres" oninput="checkPasswordStrength()">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('senha', this)"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-strength-text" id="strengthText"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6" placeholder="Digite novamente">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_senha', this)"></i>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-check"></i>
                    Redefinir Senha
                </button>
            </form>
        <?php else: ?>
            <h1>❌ Link Inválido</h1>
            <p class="subtitle">Este link de recuperação é inválido ou já expirou.</p>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="javascript:void(0)" onclick="voltarAoInicio()">
                <i class="fas fa-arrow-left"></i> Voltar para o início
            </a>
        </div>
    </div>
    
    <script>
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
        
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
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
        
        function checkPasswordStrength() {
            const password = document.getElementById('senha').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            let color = '';
            let width = '0%';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
                return;
            }
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                text = 'Fraca';
                color = 'strength-weak';
                width = '25%';
            } else if (strength === 3) {
                text = 'Média';
                color = 'strength-medium';
                width = '50%';
            } else if (strength === 4) {
                text = 'Boa';
                color = 'strength-good';
                width = '75%';
            } else {
                text = 'Forte';
                color = 'strength-strong';
                width = '100%';
            }
            
            strengthBar.className = 'password-strength-bar ' + color;
            strengthBar.style.width = width;
            strengthText.textContent = 'Força: ' + text;
            strengthText.style.color = getComputedStyle(strengthBar).backgroundColor;
        }
        
        function voltarAoInicio() {
            window.open('home.php', '_blank');
            setTimeout(function() {
                window.close();
            }, 500);
        }
    </script>
</body>
</html>
