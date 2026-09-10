<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar SMTP - FullTorque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #109349, #CE2B37);
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { color: #CE2B37; margin-bottom: 10px; }
        h2 { color: #109349; font-size: 18px; margin-top: 30px; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196f3; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        ol { line-height: 1.8; }
        .btn { background: #109349; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #0d7a3a; }
    </style>
</head>
<body>
<div class="container">
    <h1>📧 Configurar Envio de Emails</h1>
    <p>Para que o sistema envie emails de recuperação de senha, configure o SMTP no servidor.</p>

    <div class="info">
        <strong>ℹ️ Informação:</strong> O sistema está configurado para usar a função <code>mail()</code> do PHP que requer configuração no servidor.
    </div>

    <h2>🔧 Opção 1: Configurar no Hostgator (Recomendado)</h2>
    <ol>
        <li>Acesse o <strong>cPanel</strong> do Hostgator</li>
        <li>Vá em <strong>Email Accounts</strong></li>
        <li>Crie um email: <code>noreply@seudominio.com</code></li>
        <li>O servidor já está configurado para enviar emails automaticamente</li>
        <li>Teste o sistema de recuperação de senha</li>
    </ol>

    <h2>🔧 Opção 2: Configurar SMTP no php.ini (Localhost)</h2>
    <div class="warning">
        <strong>⚠️ Atenção:</strong> Esta configuração é apenas para testes locais no XAMPP.
    </div>
    
    <ol>
        <li>Localize o arquivo <code>php.ini</code> (geralmente em <code>C:\xampp\php\php.ini</code>)</li>
        <li>Procure pela seção <code>[mail function]</code></li>
        <li>Configure assim:
<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = seuemail@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
</pre>
        </li>
        <li>Abra o arquivo <code>C:\xampp\sendmail\sendmail.ini</code></li>
        <li>Configure assim:
<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=seuemail@gmail.com
auth_password=sua_senha_de_app
force_sender=seuemail@gmail.com
</pre>
        </li>
        <li>Reinicie o Apache no XAMPP</li>
    </ol>

    <h2>🔑 Como criar Senha de App no Gmail</h2>
    <ol>
        <li>Acesse <a href="https://myaccount.google.com/security" target="_blank">Segurança da Conta Google</a></li>
        <li>Ative a <strong>Verificação em duas etapas</strong></li>
        <li>Vá em <strong>Senhas de app</strong></li>
        <li>Selecione <strong>Email</strong> e <strong>Outro (nome personalizado)</strong></li>
        <li>Digite "FullTorque" e clique em <strong>Gerar</strong></li>
        <li>Copie a senha gerada (16 caracteres sem espaços)</li>
        <li>Use essa senha no <code>sendmail.ini</code></li>
    </ol>

    <div class="success">
        <strong>✅ Status Atual:</strong> O sistema já está preparado para enviar emails. Quando configurado corretamente no servidor, os emails serão enviados automaticamente.
    </div>

    <h2>🧪 Testar Envio de Email</h2>
    <p>Após configurar, teste o sistema:</p>
    <ol>
        <li>Acesse a página de <strong>Recuperar Senha</strong></li>
        <li>Digite um email cadastrado</li>
        <li>Digite os últimos 4 dígitos do CPF</li>
        <li>Verifique se o email chegou (pode demorar alguns minutos)</li>
        <li>Verifique também a pasta de <strong>SPAM</strong></li>
    </ol>

    <div style="text-align: center; margin-top: 30px;">
        <a href="recuperar-senha.php" class="btn">🔙 Voltar para Recuperar Senha</a>
    </div>
</div>
</body>
</html>
