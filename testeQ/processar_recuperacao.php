<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';
require_once 'enviar_email.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = limparDados($_POST['email']);
    $cpf = limparDados($_POST['cpf']);
    
    if (empty($email)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor, informe seu email.']);
        exit;
    }
    
    if (empty($cpf) || strlen($cpf) != 4 || !ctype_digit($cpf)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor, informe os últimos 4 dígitos do CPF.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Email inválido.']);
        exit;
    }
    
    $conexao = conectarBD();
    
    // Verifica se a tabela existe
    $result = $conexao->query("SHOW TABLES LIKE 'recuperacao_senha'");
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
        $conexao->query($sql);
    }
    
    $stmt = $conexao->prepare("SELECT id, nome, cpf FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Validar últimos 4 dígitos do CPF
        $cpfUsuario = preg_replace('/[^0-9]/', '', $usuario['cpf']);
        $ultimos4Digitos = substr($cpfUsuario, -4);
        
        if ($cpf !== $ultimos4Digitos) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Os últimos 4 dígitos do CPF não correspondem ao cadastro.']);
            $conexao->close();
            exit;
        }
        
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Limpa tokens antigos
        $conexao->query("DELETE FROM recuperacao_senha WHERE usuario_id = {$usuario['id']} AND expira < NOW()");
        
        $stmt = $conexao->prepare("INSERT INTO recuperacao_senha (usuario_id, token, expira) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $usuario['id'], $token, $expira);
        
        if ($stmt->execute()) {
            $link_recuperacao = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redefinir-senha.php?token=" . $token;
            
            $assunto = "Recuperação de Senha - FullTorque";
            $mensagem = "<!DOCTYPE html><html><body style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>";
            $mensagem .= "<div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
            $mensagem .= "<h2 style='color: #CE2B37; margin-bottom: 20px;'>🔑 Recuperação de Senha</h2>";
            $mensagem .= "<p style='font-size: 16px; color: #333;'>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>!</p>";
            $mensagem .= "<p style='font-size: 14px; color: #666; line-height: 1.6;'>Recebemos uma solicitação para redefinir sua senha no sistema FullTorque.</p>";
            $mensagem .= "<p style='font-size: 14px; color: #666; line-height: 1.6;'>Clique no botão abaixo para criar uma nova senha:</p>";
            $mensagem .= "<div style='text-align: center; margin: 30px 0;'>";
            $mensagem .= "<a href='$link_recuperacao' style='background: #CE2B37; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;'>Redefinir Senha</a>";
            $mensagem .= "</div>";
            $mensagem .= "<p style='font-size: 13px; color: #999; border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px;'>⏰ Este link expira em 1 hora por segurança.</p>";
            $mensagem .= "<p style='font-size: 13px; color: #999;'>Se você não solicitou esta recuperação, ignore este email.</p>";
            $mensagem .= "</div></body></html>";
            
            $emailEnviado = enviarEmail($email, $assunto, $mensagem);
            
            registrarLog('recuperacao_senha', 'Solicitação de recuperação de senha para: ' . $email, $usuario['id']);
            
            $response = ['sucesso' => true];
            
            if ($emailEnviado) {
                $response['mensagem'] = '✅ Email enviado com sucesso! Verifique sua caixa de entrada e spam.';
            } else {
                $response['mensagem'] = '⚠️ Email não pôde ser enviado. Use o link abaixo:';
                $response['link'] = $link_recuperacao;
            }
            
            echo json_encode($response);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao processar a solicitação. Tente novamente.']);
        }
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Email não encontrado no sistema. Verifique se digitou corretamente ou cadastre-se.']);
    }
    
    $conexao->close();
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
}

// INSTRUÇÕES PARA ENVIAR EMAILS REAIS:
// 1. Acesse: http://localhost/FullTorque/testeQ/configurar_email_automatico.php
// 2. Coloque seu Gmail e senha de app
// 3. Reinicie o Apache
// 4. Pronto! Os emails serão enviados automaticamente
?>
