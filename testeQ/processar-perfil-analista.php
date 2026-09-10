<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    $nome = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $telefone = limparDados($_POST['telefone']);
    $especialidade = limparDados($_POST['especialidade']);
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Validações
    if (empty($nome) || empty($email)) {
        exibirAlerta('danger', 'Nome e email são obrigatórios!');
        header("Location: analista-perfil.php");
        exit;
    }
    
    // Verificar se email já existe para outro usuário
    $stmt_check = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $email, $_SESSION['usuario_id']);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        exibirAlerta('danger', 'Este email já está sendo usado por outro usuário!');
        header("Location: analista-perfil.php");
        exit;
    }
    
    // Validar senha se fornecida
    if (!empty($nova_senha)) {
        if ($nova_senha !== $confirmar_senha) {
            exibirAlerta('danger', 'As senhas não coincidem!');
            header("Location: analista-perfil.php");
            exit;
        }
        if (strlen($nova_senha) < 6) {
            exibirAlerta('danger', 'A senha deve ter pelo menos 6 caracteres!');
            header("Location: analista-perfil.php");
            exit;
        }
    }
    
    try {
        $conexao->begin_transaction();
        
        // Atualizar dados do usuário
        if (!empty($nova_senha)) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt_user = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, senha = ? WHERE id = ?");
            $stmt_user->bind_param("ssssi", $nome, $email, $telefone, $senha_hash, $_SESSION['usuario_id']);
        } else {
            $stmt_user = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
            $stmt_user->bind_param("sssi", $nome, $email, $telefone, $_SESSION['usuario_id']);
        }
        $stmt_user->execute();
        
        // Atualizar especialidade do analista
        $stmt_analista = $conexao->prepare("UPDATE analistas SET especialidade = ? WHERE id = ?");
        $stmt_analista->bind_param("si", $especialidade, $_SESSION['analista_id']);
        $stmt_analista->execute();
        
        // Atualizar dados da sessão
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_email'] = $email;
        
        $conexao->commit();
        
        registrarLog('perfil_atualizado', "Analista atualizou perfil");
        exibirAlerta('success', 'Perfil atualizado com sucesso!');
        
    } catch (Exception $e) {
        $conexao->rollback();
        exibirAlerta('danger', 'Erro ao atualizar perfil: ' . $e->getMessage());
    }
    
    $conexao->close();
}

header("Location: analista-perfil.php");
exit;
?>