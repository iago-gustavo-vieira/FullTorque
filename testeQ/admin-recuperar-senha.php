<?php
require_once 'config.php';

$erro = '';
$sucesso = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    if (isset($_POST['email']) && !isset($_POST['token']) && !isset($_POST['nova_senha'])) {
        $email = limparDados($_POST['email']);
        $stmt = $conexao->prepare("SELECT id, token_recuperacao FROM usuarios WHERE email = ? AND (id = 1 OR email = 'admin@fulltorque.com')");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $erro = 'Email não encontrado ou não é administrador';
        } else {
            $user = $result->fetch_assoc();
            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_email'] = $email;
            $_SESSION['temp_token'] = $user['token_recuperacao'];
            header('Location: admin-recuperar-senha.php?step=2');
            exit;
        }
    } elseif (isset($_POST['token'])) {
        $token = limparDados($_POST['token']);
        if ($token === $_SESSION['temp_token']) {
            header('Location: admin-recuperar-senha.php?step=3');
            exit;
        } else {
            $erro = 'Token incorreto';
        }
    } elseif (isset($_POST['nova_senha'])) {
        $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $nova_senha, $_SESSION['temp_user_id']);
        if ($stmt->execute()) {
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_token']);
            $sucesso = 'Senha alterada com sucesso!';
            header("refresh:2;url=admin-login.php");
        }
    }
    $conexao->close();
}

if ($step == 2 && !isset($_SESSION['temp_user_id'])) {
    header('Location: admin-recuperar-senha.php');
    exit;
}
if ($step == 3 && !isset($_SESSION['temp_user_id'])) {
    header('Location: admin-recuperar-senha.php');
    exit;
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
    <title>Recuperar Senha - FullTorque</title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
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
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }
        body.theme-alemanha {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        body.theme-alemanha .container { background: #1a1a1a; }
        .header {
            background: linear-gradient(135deg, #CE2B37 0%, #a01e28 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        body.theme-alemanha .header {
            background: linear-gradient(135deg, #FFCE00 0%, #daaf03 100%);
            color: #000;
        }
        .header i { font-size: 3rem; margin-bottom: 15px; }
        .header h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 0.9rem; }
        .body { padding: 40px 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        body.theme-alemanha .form-group label { color: #FFCE00; }
        body.theme-alemanha .body p { color: #ccc; }
        .input-group { position: relative; }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #CE2B37;
            font-size: 1.1rem;
        }
        body.theme-alemanha .input-group i { color: #FFCE00; }
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        body.theme-alemanha .form-control {
            background: #2a2a2a;
            border-color: #444;
            color: white;
        }
        .form-control:focus {
            outline: none;
            border-color: #CE2B37;
            box-shadow: 0 0 0 4px rgba(206, 43, 55, 0.1);
            background: white;
        }
        body.theme-alemanha .form-control:focus {
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
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
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
        body.theme-alemanha .btn {
            background: linear-gradient(135deg, #FFCE00, #daaf03);
            color: #000;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(206, 43, 55, 0.4);
        }
        body.theme-alemanha .btn:hover { box-shadow: 0 10px 30px rgba(255, 206, 0, 0.4); }
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
        body.theme-alemanha .back-link a { color: #FFCE00; }
        .back-link a:hover { text-decoration: underline; }

    </style>
</head>
<body>
    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'theme-alemanha') {
            document.body.classList.add('theme-alemanha');
        }
    </script>
    <div class="container">
        <div class="header">
            <i class="fas fa-key"></i>
            <h1>Recuperar Senha</h1>
            <p>Redefina sua senha de administrador</p>
        </div>
        
        <div class="body">
            <?php if ($erro): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Email Administrativo</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control" required autofocus autocomplete="off">
                        </div>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-arrow-right"></i>
                        Continuar
                    </button>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Token de 4 Dígitos</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="text" name="token" class="form-control" maxlength="4" pattern="[0-9]{4}" required autofocus>
                        </div>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-check"></i>
                        Verificar Token
                    </button>
                </form>
            <?php elseif ($step == 3): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Nova Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="nova_senha" class="form-control" required autofocus>
                        </div>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i>
                        Redefinir Senha
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="admin-login.php">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
