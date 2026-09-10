<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurar Email Automático</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #CE2B37; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #CE2B37; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #a01e28; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #2196F3; }
        .success { background: #e8f5e9; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #4CAF50; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Configuração Rápida de Email</h1>
        
        <div class="info">
            <strong>📧 Use seu Gmail pessoal!</strong><br>
            Vamos configurar automaticamente para enviar emails reais usando sua conta do Gmail.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Seu Email do Gmail:</label>
                <input type="email" name="gmail" required placeholder="seuemail@gmail.com">
            </div>
            
            <div class="form-group">
                <label>Senha de App do Gmail:</label>
                <input type="password" name="senha" required placeholder="xxxx xxxx xxxx xxxx">
                <small style="color: #666;">
                    <a href="https://myaccount.google.com/apppasswords" target="_blank">Clique aqui para gerar uma senha de app</a>
                </small>
            </div>
            
            <button type="submit">Configurar Agora</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $gmail = $_POST['gmail'];
            $senha = $_POST['senha'];
            
            // Configurar php.ini
            $phpIni = 'C:\xampp\php\php.ini';
            $sendmailIni = 'C:\xampp\sendmail\sendmail.ini';
            
            // Backup
            copy($phpIni, $phpIni . '.backup');
            copy($sendmailIni, $sendmailIni . '.backup');
            
            // Configurar sendmail.ini
            $sendmailConfig = "
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=auto
auth_username=$gmail
auth_password=$senha
force_sender=$gmail
";
            file_put_contents($sendmailIni, $sendmailConfig);
            
            echo '<div class="success">
                ✅ <strong>Configurado com sucesso!</strong><br>
                Agora os emails serão enviados automaticamente!<br>
                <strong>IMPORTANTE:</strong> Reinicie o Apache no XAMPP.
            </div>';
        }
        ?>
    </div>
</body>
</html>
