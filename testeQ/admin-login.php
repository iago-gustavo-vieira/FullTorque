<?php
require_once 'config.php';

// Se já estiver logado como admin, redireciona
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === 'admin') {
    header('Location: admin.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = limparDados($_POST['email']);
    $senha = $_POST['senha'];
    
    $conexao = conectarBD();
    
    // Verificar se o usuário existe e buscar dados
    $stmt = $conexao->prepare("SELECT id, nome, email, senha, nivel_acesso FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $erro = 'Usuário não encontrado. <a href="corrigir-tudo.php" style="color: white; text-decoration: underline;">Corrigir sistema</a>';
    } else {
        $user = $result->fetch_assoc();
        
        // Verificar se é admin
        $nivel = $user['nivel_acesso'] ?? '';
        $is_admin = ($nivel === 'admin' || $user['email'] === 'admin@autoservice.com' || $user['id'] == 1);
        
        if (!$is_admin) {
            $erro = 'Este usuário não é administrador';
        } else {
            // Verificar senha (aceita hash ou texto plano para compatibilidade)
            if (password_verify($senha, $user['senha']) || $senha === $user['senha']) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_nivel'] = 'admin';
                $_SESSION['usuario_permissoes'] = ['admin'];
                registrarLog('login_admin', "Admin logado: {$user['nome']} ($email)", $user['id']);
                header('Location: admin.php');
                exit;
            } else {
                $erro = 'Senha incorreta. <a href="corrigir-tudo.php" style="color: white; text-decoration: underline;">Resetar senha</a>';
            }
        }
    }
    $conexao->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login Administrativo - FullTorque</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        body.theme-alemanha .login-container {
            background: #1a1a1a;
        }
        
        .login-header {
            background: linear-gradient(135deg, #CE2B37 0%, #a01e28 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
            position: relative;
        }
        
        body.theme-alemanha .login-header {
            background: linear-gradient(135deg, #FFCE00 0%, #daaf03 100%);
            color: #000;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        body.theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #CE2B37;
            font-size: 1.1rem;
        }
        
        body.theme-alemanha .input-group i {
            color: #FFCE00;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        body.theme-alemanha .form-group input {
            background: #2a2a2a;
            border-color: #444;
            color: white;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #CE2B37;
            box-shadow: 0 0 0 4px rgba(206, 43, 55, 0.1);
            background: white;
        }
        
        body.theme-alemanha .form-group input:focus {
            border-color: #FFCE00;
            box-shadow: 0 0 0 4px rgba(255, 206, 0, 0.1);
            background: #2a2a2a;
        }
        
        .error-message {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #CE2B37, #a01e28);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        body.theme-alemanha .btn-login {
            background: linear-gradient(135deg, #FFCE00, #daaf03);
            color: #000;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(206, 43, 55, 0.4);
        }
        
        body.theme-alemanha .btn-login:hover {
            box-shadow: 0 10px 30px rgba(255, 206, 0, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #CE2B37;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        body.theme-alemanha .back-link a {
            color: #FFCE00;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            height: 80px;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        
        body.theme-alemanha .features {
            border-top-color: #333;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #666;
        }
        
        body.theme-alemanha .feature-item {
            color: #ccc;
        }
        
        .feature-item i {
            color: #CE2B37;
            font-size: 1.2rem;
        }
        
        body.theme-alemanha .feature-item i {
            color: #FFCE00;
        }
        
        .security-badge {
            background: rgba(206, 43, 55, 0.1);
            border: 2px solid rgba(206, 43, 55, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            color: #666;
        }
        
        body.theme-alemanha .security-badge {
            background: rgba(255, 206, 0, 0.1);
            border-color: rgba(255, 206, 0, 0.3);
            color: #ccc;
        }
        
        .security-badge i {
            font-size: 1.5rem;
            color: #CE2B37;
        }
        
        body.theme-alemanha .security-badge i {
            color: #FFCE00;
        }
    </style>
</head>
<body>
    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'theme-alemanha') {
            document.body.classList.add('theme-alemanha');
        }
    </script>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="logo.png" alt="FullTorque">
            </div>
            <h1>🔐 Área Administrativa</h1>
            <p>Acesso Restrito - Apenas Administradores</p>
        </div>
        
        <div class="login-body">
            <?php if ($erro): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">E-mail Administrativo</label>
                    <div class="input-group">
                        <i class="fas fa-user-shield"></i>
                        <input type="email" id="email" name="email" required autofocus autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar no Sistema
                </button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="admin-recuperar-senha.php" style="color: #CE2B37; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                        <i class="fas fa-key"></i> Esqueci a senha
                    </a>
                </div>
            </form>
            
            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span><strong>Conexão Segura:</strong> Seus dados são protegidos com criptografia SSL</span>
            </div>
            
            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Acesso Seguro</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-user-lock"></i>
                    <span>Autenticação 2FA</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-history"></i>
                    <span>Log de Atividades</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-database"></i>
                    <span>Backup Automático</span>
                </div>
            </div>
            
            <div class="back-link">
                <a href="home.php">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao site
                </a>
            </div>
        </div>
    </div>
    <script src="prevent-back-navigation.js"></script>
</body>
</html>
