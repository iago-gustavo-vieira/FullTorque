<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparDados($_POST['nome']);
    $sobrenome = isset($_POST['sobrenome']) ? limparDados($_POST['sobrenome']) : '';
    $cpf = limparDados($_POST['cpf']);
    $telefone = limparDados($_POST['celular']);
    $email = limparDados($_POST['email']);
    $senha = $_POST['senha'];
    
    // Validações
    $erros = [];
    
    // Validar nome (mínimo 2 caracteres)
    if (strlen($nome) < 2) {
        $erros[] = 'Nome deve ter no mínimo 2 caracteres.';
    }
    
    // Validar sobrenome (mínimo 2 caracteres)
    if (strlen($sobrenome) < 2) {
        $erros[] = 'Sobrenome deve ter no mínimo 2 caracteres.';
    }
    
    // Validar CPF (11 dígitos)
    $cpf_numeros = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf_numeros) != 11) {
        $erros[] = 'CPF inválido. Digite 11 dígitos.';
    }
    
    // Validar telefone (10 ou 11 dígitos)
    $telefone_numeros = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone_numeros) < 10 || strlen($telefone_numeros) > 11) {
        $erros[] = 'Telefone inválido.';
    }
    
    // Validar senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
        $erros[] = 'Senha deve ter no mínimo 6 caracteres.';
    }
    
    if (!empty($erros)) {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => implode(' ', $erros)
        ];
        header("Location: home.php");
        exit;
    }
    
    $conexao = conectarBD();
    
    // Verificar se email já existe
    $check = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => '❌ Este e-mail já está cadastrado.'
        ];
        header("Location: home.php");
        exit;
    }
    
    // Verificar se CPF já existe
    $check_cpf = $conexao->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $check_cpf->bind_param("s", $cpf);
    $check_cpf->execute();
    
    if ($check_cpf->get_result()->num_rows > 0) {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => '❌ Este CPF já está cadastrado.'
        ];
        header("Location: home.php");
        exit;
    }
    
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $nome_completo = $nome . ' ' . $sobrenome;
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, cpf, telefone, email, senha, nivel_acesso, data_cadastro) VALUES (?, ?, ?, ?, ?, 'cliente', NOW())");
    $stmt->bind_param("sssss", $nome_completo, $cpf, $telefone, $email, $senha_hash);
    
    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;
        registrarLog('usuario_cadastrado', "Novo usuário cadastrado: $nome_completo ($email)", $usuario_id);
        
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => '✅ Cadastro realizado com sucesso! Faça login para acessar sua conta.'
        ];
        $_SESSION['mostrar_alerta_cadastro'] = true;
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => '❌ Erro ao realizar cadastro. Tente novamente.'
        ];
    }
    
    $stmt->close();
    $conexao->close();
    
    header("Location: home.php");
    exit;
}
?>
