<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_senha') {
    $cartao_final = isset($_POST['cartao_final']) ? limparDados($_POST['cartao_final']) : '';
    $senha = isset($_POST['senha']) ? limparDados($_POST['senha']) : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? limparDados($_POST['confirmar_senha']) : '';
    
    if ($senha !== $confirmar_senha) {
        $mensagem = 'As senhas não coincidem!';
        $tipo_mensagem = 'error';
    } elseif (strlen($senha) !== 4) {
        $mensagem = 'A senha deve ter 4 dígitos!';
        $tipo_mensagem = 'error';
    } else {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("UPDATE cartoes_usuario SET senha = ? WHERE usuario_id = ? AND numero LIKE ?");
        $numero_like = '%' . $cartao_final;
        $stmt->bind_param("sis", $senha, $_SESSION['usuario_id'], $numero_like);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $mensagem = 'Senha cadastrada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Cartão não encontrado ou senha já cadastrada.';
            $tipo_mensagem = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Senha do Cartão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-input {
            position: relative;
        }
        
        .password-input input {
            padding-right: 50px;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.2rem;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
        
        .info-box p {
            margin: 0;
            color: #0d47a1;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-lock"></i>
            <h1>Cadastrar Senha do Cartão</h1>
            <p>Crie uma senha de 4 dígitos para proteger seus pagamentos</p>
        </div>
        
        <?php if ($mensagem): ?>
        <div class="alert <?php echo $tipo_mensagem; ?>" style="display: block;">
            <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'times-circle'; ?>"></i> 
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>
        <div id="alertBox" class="alert"></div>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Esta senha será solicitada sempre que você realizar um pagamento com cartão.</p>
        </div>
        
        <form id="formSenha" method="POST">
            <input type="hidden" name="acao" value="cadastrar_senha">
            
            <div class="form-group">
                <label>Número do Cartão (últimos 4 dígitos)</label>
                <input type="text" name="cartao_final" placeholder="0000" maxlength="4" pattern="\d{4}" required>
            </div>
            
            <div class="form-group">
                <label>Senha (4 dígitos)</label>
                <div class="password-input">
                    <input type="password" id="senha" name="senha" placeholder="••••" maxlength="4" pattern="\d{4}" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('senha')"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirmar Senha</label>
                <div class="password-input">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••" maxlength="4" pattern="\d{4}" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha')"></i>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-check"></i> Cadastrar Senha
            </button>
        </form>
    </div>
    
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
        
        <?php if ($tipo_mensagem === 'success'): ?>
        setTimeout(() => {
            window.close();
        }, 2000);
        <?php endif; ?>
        
        document.getElementById('formSenha').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;
            const alertBox = document.getElementById('alertBox');
            
            if (senha !== confirmar) {
                e.preventDefault();
                alertBox.className = 'alert error';
                alertBox.style.display = 'block';
                alertBox.innerHTML = '<i class="fas fa-times-circle"></i> As senhas não coincidem!';
                return;
            }
            
            if (senha.length !== 4) {
                e.preventDefault();
                alertBox.className = 'alert error';
                alertBox.style.display = 'block';
                alertBox.innerHTML = '<i class="fas fa-times-circle"></i> A senha deve ter 4 dígitos!';
                return;
            }
        });
        
        // Permitir apenas números
        document.querySelectorAll('input[type="password"], input[name="cartao_final"]').forEach(input => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
            });
        });
    </script>
</body>
</html>
