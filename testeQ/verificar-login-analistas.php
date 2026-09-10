<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Verificando Login dos Analistas</h2>";

// Verificar todos os usuários com analista_id
$stmt = $conexao->prepare("SELECT id, nome, email, status, analista_id FROM usuarios WHERE analista_id IS NOT NULL");
$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h3>Usuários Analistas Encontrados:</h3>";
foreach ($usuarios as $usuario) {
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
    echo "<strong>Nome:</strong> " . $usuario['nome'] . "<br>";
    echo "<strong>Email:</strong> " . $usuario['email'] . "<br>";
    echo "<strong>Status:</strong> " . $usuario['status'] . "<br>";
    echo "<strong>Analista ID:</strong> " . $usuario['analista_id'] . "<br>";
    echo "<strong>User ID:</strong> " . $usuario['id'];
    echo "</div>";
}

// Verificar se João Oliveira existe
echo "<hr><h3>Verificando João Oliveira:</h3>";
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = 'joao.oliveira@autoservice.com'");
$stmt->execute();
$joao = $stmt->get_result()->fetch_assoc();

if ($joao) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<p>✅ João encontrado!</p>";
    echo "<strong>Nome:</strong> " . $joao['nome'] . "<br>";
    echo "<strong>Email:</strong> " . $joao['email'] . "<br>";
    echo "<strong>Status:</strong> " . $joao['status'] . "<br>";
    echo "<strong>Analista ID:</strong> " . ($joao['analista_id'] ?: 'NULL') . "<br>";
    
    // Verificar senha
    if (password_verify('123456', $joao['senha'])) {
        echo "<strong>Senha:</strong> ✅ Correta (123456)<br>";
    } else {
        echo "<strong>Senha:</strong> ❌ Incorreta - Corrigindo...<br>";
        
        // Corrigir senha
        $nova_senha = password_hash('123456', PASSWORD_DEFAULT);
        $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt_update->bind_param("si", $nova_senha, $joao['id']);
        $stmt_update->execute();
        echo "<strong>Senha corrigida!</strong><br>";
    }
    
    // Verificar status
    if ($joao['status'] != 'ativo') {
        echo "<strong>Status:</strong> ❌ Inativo - Corrigindo...<br>";
        $stmt_status = $conexao->prepare("UPDATE usuarios SET status = 'ativo' WHERE id = ?");
        $stmt_status->bind_param("i", $joao['id']);
        $stmt_status->execute();
        echo "<strong>Status corrigido para ativo!</strong><br>";
    }
    
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<p>❌ João Oliveira não encontrado! Criando...</p>";
    
    // Verificar se existe usuário com CPF similar
    $stmt_cpf = $conexao->prepare("SELECT id, nome, email FROM usuarios WHERE cpf = '555.555.555-55'");
    $stmt_cpf->execute();
    $usuario_cpf = $stmt_cpf->get_result()->fetch_assoc();
    
    if ($usuario_cpf) {
        echo "<p>⚠️ Usuário com CPF 555.555.555-55 já existe:</p>";
        echo "<p>Nome: " . $usuario_cpf['nome'] . "</p>";
        echo "<p>Email: " . $usuario_cpf['email'] . "</p>";
        echo "<p>Atualizando dados para João Oliveira...</p>";
        
        // Atualizar dados existentes
        $stmt_update = $conexao->prepare("UPDATE usuarios SET nome = 'João Oliveira', email = 'joao.oliveira@autoservice.com', senha = ? WHERE id = ?");
        $nova_senha = password_hash("123456", PASSWORD_DEFAULT);
        $stmt_update->bind_param("si", $nova_senha, $usuario_cpf['id']);
        $stmt_update->execute();
        
        echo "<p>✅ Dados atualizados com sucesso!</p>";
    } else {
        // Criar João Oliveira com CPF único
        $nome = "João Oliveira";
        $email = "joao.oliveira@autoservice.com";
        $telefone = "(11) 92222-2222";
        $cpf = "777.777.777-77";
        $senha = password_hash("123456", PASSWORD_DEFAULT);
    
    $stmt_create = $conexao->prepare("INSERT INTO usuarios (nome, email, telefone, cpf, senha, status, data_cadastro) VALUES (?, ?, ?, ?, ?, 'ativo', NOW())");
    $stmt_create->bind_param("sssss", $nome, $email, $telefone, $cpf, $senha);
    
    if ($stmt_create->execute()) {
        $usuario_id = $conexao->insert_id;
        
        // Criar na tabela analistas
        $stmt_analista = $conexao->prepare("INSERT INTO analistas (nome, especialidade, ativo, data_cadastro) VALUES (?, 'Freios e Suspensão', 1, NOW())");
        $stmt_analista->bind_param("s", $nome);
        $stmt_analista->execute();
        $analista_id = $conexao->insert_id;
        
        // Atualizar usuario com analista_id
        $stmt_link = $conexao->prepare("UPDATE usuarios SET analista_id = ? WHERE id = ?");
        $stmt_link->bind_param("ii", $analista_id, $usuario_id);
        $stmt_link->execute();
        
        echo "<p>✅ João Oliveira criado com sucesso!</p>";
        }
    }
    echo "</div>";
}

echo "<hr><div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h3>🔑 Dados de Login Atualizados:</h3>";
echo "<p><strong>João Oliveira:</strong> joao.oliveira@autoservice.com | Senha: 123456</p>";
echo "<p><strong>Carlos Silva:</strong> carlos.silva@autoservice.com | Senha: 123456</p>";
echo "<p><strong>Maria Santos:</strong> maria.santos@autoservice.com | Senha: 123456</p>";
echo "</div>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>