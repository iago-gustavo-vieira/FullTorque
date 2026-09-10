<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $avaliacao = limparDados($_POST['avaliacao']);
    $mensagem = limparDados($_POST['mensagem']);
    
    $conexao = conectarBD();
    
    $stmt = $conexao->prepare("INSERT INTO feedbacks (nome, email, avaliacao, mensagem, data_envio) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssis", $nome, $email, $avaliacao, $mensagem);
    
    if ($stmt->execute()) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Feedback enviado com sucesso! Obrigado pela sua opinião.'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => 'Erro ao enviar feedback. Tente novamente.'
        ];
    }
    
    $stmt->close();
    $conexao->close();
    
    header("Location: home.php?feedback=sent#feedback");
    exit;
}
?>
