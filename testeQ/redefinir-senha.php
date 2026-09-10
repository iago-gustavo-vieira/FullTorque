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
                    $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Senha redefinida com sucesso! Faça login com sua nova senha.'];
                    header('Location: home.php');
                    exit;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - FullTorque</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #009246 0%, #009246 33%, #ffffff 33%, #ffffff 66%, #CE2B37 66%, #CE2B37 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        
        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .theme-alemanha .container {
            background: #1a1a1a;
            color: white;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            height: 80px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        
        .theme-alemanha h1 {
            color: #FFCE00;
        }
        
        p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .theme-alemanha p {
            color: #ccc;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: #fde8e8;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-success {
            background: #e6f7ef;
            color: #2ecc71;
            border-left: 4px solid #2ecc71;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            transition: color 0.3s;
            margin-top: 14px;
        }
        
        .password-toggle:hover {
            color: #CE2B37;
        }
        
        .theme-alemanha .password-toggle {
            color: #ccc;
        }
        
        .theme-alemanha .password-toggle:hover {
            color: #FFCE00;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            transition: all 0.3s;
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
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .theme-alemanha label {
            color: #FFCE00;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
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
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #009246, #CE2B37);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .theme-alemanha .btn {
            background: linear-gradient(135deg, #000000, #FFCE00);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 43, 55, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #CE2B37;
            text-decoration: none;
            font-weight: 500;
        }
        
        .theme-alemanha .back-link a {
            color: #FFCE00;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 10px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .flag-icon {
            width: 20px;
            height: 14px;
            border-radius: 2px;
        }
        
        .italy-flag {
            background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%);
        }
        
        .germany-flag {
            background: linear-gradient(to bottom, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .container {
                padding: 25px 20px;
                max-width: 100%;
            }
            
            .logo img {
                height: 60px;
            }
            
            h1 {
                font-size: 1.5rem;
                margin-bottom: 8px;
            }
            
            p {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            label {
                font-size: 14px;
                margin-bottom: 6px;
            }
            
            input {
                padding: 10px;
                font-size: 14px;
            }
            
            .btn {
                padding: 12px;
                font-size: 15px;
            }
            
            .alert {
                padding: 12px;
                font-size: 14px;
            }
            
            .theme-toggle {
                top: 10px;
                right: 10px;
                padding: 8px;
            }
            
            .password-toggle {
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 1.3rem;
            }
            
            p {
                font-size: 0.85rem;
            }
            
            input {
                padding: 9px;
                font-size: 13px;
            }
            
            .btn {
                padding: 11px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="theme-toggle">
        <span class="flag-icon italy-flag"></span>
        <label style="cursor: pointer;">
            <input type="checkbox" id="themeToggle" style="display: none;">
            <span style="color: white;">🇩🇪</span>
        </label>
    </div>
    
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="FullTorque">
        </div>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($tokenValido): ?>
            <h1>Redefinir Senha</h1>
            <p>Digite sua nova senha abaixo</p>
            
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
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6" placeholder="Digite a senha novamente">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_senha', this)"></i>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-check"></i>
                    Redefinir Senha
                </button>
            </form>
        <?php else: ?>
            <h1>Link Inválido</h1>
            <p>Este link de recuperação é inválido ou já expirou.</p>
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
            
            // Critérios de força
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Definir nível
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
            strengthText.textContent = 'Força da senha: ' + text;
            strengthText.style.color = getComputedStyle(strengthBar).backgroundColor;
        }
        
        // Função para voltar ao início
        function voltarAoInicio() {
            // Limpar formulário de recuperação e mensagem via sessionStorage
            sessionStorage.setItem('limparRecuperacao', 'true');
            
            // Se tem histórico, volta
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Se não tem histórico (abriu em nova aba), fecha a aba ou vai para home
                window.close();
                // Se não conseguir fechar (bloqueado pelo navegador), redireciona
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 100);
            }
        }
    </script>
</body>
</html>
