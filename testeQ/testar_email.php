<?php
// Teste de envio de email

$para = "seu-email@exemplo.com"; // ALTERE AQUI
$assunto = "Teste de Email - FullTorque";
$mensagem = "<html><body>";
$mensagem .= "<h2>Teste de Email</h2>";
$mensagem .= "<p>Se você recebeu este email, a configuração está funcionando!</p>";
$mensagem .= "</body></html>";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: FullTorque <noreply@fulltorque.com>" . "\r\n";

if (mail($para, $assunto, $mensagem, $headers)) {
    echo "✅ Email enviado com sucesso para: " . $para;
    echo "<br><br>Verifique sua caixa de entrada (e spam).";
} else {
    echo "❌ Erro ao enviar email.";
    echo "<br><br>Verifique as configurações do PHP.ini e sendmail.ini";
    echo "<br>Consulte o arquivo CONFIGURAR_EMAIL.md para mais informações.";
}
?>
