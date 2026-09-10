<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limparDados($_POST['email']);
    $senha = $_POST['senha'];
    
    $conexao = conectarBD();
    $login_ok = false;
    $erro_debug = '';
    
    // Verificar na tabela usuarios
    $stmt = $conexao->prepare("SELECT id, nome, email, senha, nivel_acesso FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Verificar senha com password_verify ou comparação direta (para senhas antigas)
        if (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha']) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'] ?? 'cliente';
            registrarLog('login', "Usuário logado: {$usuario['nome']} ($email)", $usuario['id']);
            $login_ok = true;
        } else {
            $erro_debug = 'Senha incorreta';
        }
    } else {
        $erro_debug = 'Email não encontrado';
    }
    $stmt->close();
    

    
    $conexao->close();
    
    if ($login_ok) {
        header("Location: index.php");
        exit();
    }
    
    $mensagem_erro = DEBUG_MODE && !empty($erro_debug) ? "E-mail ou senha incorretos. ($erro_debug)" : 'E-mail ou senha incorretos.';
    
    $_SESSION['alerta'] = [
        'tipo' => 'error',
        'mensagem' => $mensagem_erro
    ];
    $_SESSION['email_tentativa'] = $email;
    
    header("Location: home.php");
    exit();
}
?>
