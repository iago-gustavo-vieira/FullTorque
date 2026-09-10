<?php
// Função para enviar email usando serviço gratuito
function enviarEmailRecuperacao($email, $nome, $link) {
    // Usando API do SendGrid (gratuito até 100 emails/dia)
    // Você só precisa criar conta em: https://sendgrid.com/free/
    
    $apiKey = 'SUA_API_KEY_AQUI'; // Coloque sua chave aqui
    
    $data = [
        'personalizations' => [[
            'to' => [['email' => $email, 'name' => $nome]],
            'subject' => 'Recuperação de Senha - FullTorque'
        ]],
        'from' => ['email' => 'noreply@fulltorque.com', 'name' => 'FullTorque'],
        'content' => [[
            'type' => 'text/html',
            'value' => "
                <h2>Olá, $nome!</h2>
                <p>Recebemos uma solicitação para redefinir sua senha.</p>
                <p><a href='$link' style='background: #CE2B37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Redefinir Senha</a></p>
                <p>Este link expira em 1 hora.</p>
                <p>Se você não solicitou, ignore este email.</p>
            "
        ]]
    ];
    
    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode == 202; // SendGrid retorna 202 em sucesso
}
?>
