<?php
function enviarEmail($destinatario, $assunto, $mensagemHtml) {
    $dominio = $_SERVER['HTTP_HOST'];
    $remetente = "noreply@" . str_replace('www.', '', $dominio);
    $nomeRemetente = "FullTorque";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $nomeRemetente <$remetente>\r\n";
    $headers .= "Reply-To: $remetente\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 1\r\n";
    
    ini_set('sendmail_from', $remetente);
    
    return @mail($destinatario, $assunto, $mensagemHtml, $headers);
}
?>
