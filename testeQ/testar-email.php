<?php
require_once 'config.php';
require_once 'enviar_email.php';

$emailTeste = isset($_GET['email']) ? $_GET['email'] : '';
$enviado = false;
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $emailTeste = limparDados($_POST['email']);
    
    if (filter_var($emailTeste, FILTER_VALIDATE_EMAIL)) {
        $assunto = "Teste de Email - FullTorque";
        $mensagem = "<!DOCTYPE html><html><body style='font-family: Arial, sans-serif; padding: 20px;'>";
        $mensagem .= "<div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>";
        $mensagem .= "<h2 style='color: #CE2B37;'>✅ Email Funcionando!</h2>";
        $mensagem .= "<p>Se você recebeu este email, significa que o sistema de envio está configurado corretamente.</p>";
        $mensagem .= "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
        $mensagem .= "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
        $mensagem .= "</div></body></html>";
        
        $enviado = enviarEmail($emailTeste, $assunto, $mensagem);
        
        if (!$enviado) {
            $erro = error_get_last()['message'] ?? 'Erro desconhecido';
        }
    } else {
        $erro = 'Email inválido';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testar Email - FullTorque</title>
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
        }
        h1 { color: #CE2B37; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="email"]:focus {
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
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
        .config-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
        .config-info strong { color: #109349; }
    </style>
</head>
<body>
<div class="container">
    <h1>📧 Testar Envio de Email</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php if ($enviado): ?>
            <div class="success">
                <strong>✅ Email enviado com sucesso!</strong><br>
                Verifique a caixa de entrada de <strong><?php echo htmlspecialchars($emailTeste); ?></strong><br>
                <small>Pode levar alguns minutos. Verifique também a pasta de SPAM.</small>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>❌ Falha ao enviar email</strong><br>
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="info">
        <strong>ℹ️ Como funciona:</strong><br>
        Digite seu email abaixo e clique em "Enviar Teste". Você receberá um email de confirmação se tudo estiver configurado corretamente.
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Seu Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($emailTeste); ?>" placeholder="seuemail@exemplo.com" required>
        </div>
        <button type="submit" class="btn">📤 Enviar Email de Teste</button>
    </form>
    
    <div class="config-info">
        <strong>📋 Configuração Atual:</strong><br>
        <strong>Servidor:</strong> <?php echo $_SERVER['HTTP_HOST']; ?><br>
        <strong>Remetente:</strong> noreply@<?php echo str_replace('www.', '', $_SERVER['HTTP_HOST']); ?><br>
        <strong>Método:</strong> mail() do PHP (Hostgator SMTP)<br>
        <strong>Status:</strong> <?php echo function_exists('mail') ? '✅ Disponível' : '❌ Indisponível'; ?>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="recuperar-senha.php" style="color: #109349; text-decoration: none;">← Voltar para Recuperar Senha</a>
    </div>
</div>
</body>
</html>
