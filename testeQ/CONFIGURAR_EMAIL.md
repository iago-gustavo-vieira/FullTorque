# Configuração de Envio de Email

## Para Windows (XAMPP)

### Opção 1: Usar Gmail SMTP (Recomendado)

1. Instale o PHPMailer:
```bash
composer require phpmailer/phpmailer
```

2. Configure no arquivo `config.php`:
```php
// Configurações de Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha-de-app');
define('SMTP_FROM', 'noreply@fulltorque.com');
define('SMTP_FROM_NAME', 'FullTorque');
```

3. No Gmail, ative "Senha de app":
   - Acesse: https://myaccount.google.com/security
   - Ative a verificação em duas etapas
   - Gere uma "Senha de app" para o projeto

### Opção 2: Configurar sendmail no XAMPP

1. Edite o arquivo `php.ini`:
```ini
[mail function]
SMTP=smtp.gmail.com
smtp_port=587
sendmail_from=seu-email@gmail.com
sendmail_path="\"C:\xampp\sendmail\sendmail.exe\" -t"
```

2. Edite o arquivo `sendmail.ini` (em C:\xampp\sendmail\):
```ini
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=seu-email@gmail.com
auth_password=sua-senha-de-app
force_sender=seu-email@gmail.com
```

3. Reinicie o Apache

## Teste de Envio

Execute o arquivo `testar_email.php` para verificar se está funcionando.

## Observações

- A função `mail()` do PHP funciona automaticamente após a configuração
- Em modo DEBUG, o link de recuperação é exibido na tela
- Emails podem cair na caixa de spam inicialmente
- Para produção, use um serviço profissional como SendGrid, Mailgun ou AWS SES
