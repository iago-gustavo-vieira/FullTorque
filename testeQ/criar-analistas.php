<?php
require_once 'config.php';

$conexao = conectarBD();

// Analistas para criar
$analistas = [
    [
        'nome' => 'Carlos Silva',
        'email' => 'carlos.silva@autoservice.com',
        'telefone' => '(11) 91111-1111',
        'cpf' => '444.444.444-44',
        'senha' => '123456',
        'especialidade' => 'Motor e Transmissão'
    ],
    [
        'nome' => 'João Oliveira', 
        'email' => 'joao.oliveira@autoservice.com',
        'telefone' => '(11) 92222-2222',
        'cpf' => '555.555.555-55',
        'senha' => '123456',
        'especialidade' => 'Freios e Suspensão'
    ],
    [
        'nome' => 'Maria Santos',
        'email' => 'maria.santos@autoservice.com', 
        'telefone' => '(11) 93333-3333',
        'cpf' => '666.666.666-66',
        'senha' => '123456',
        'especialidade' => 'Elétrica Automotiva'
    ]
];

echo "<h2>Criando Analistas:</h2>";

foreach ($analistas as $analista) {
    // Verificar se já existe por email ou CPF
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? OR cpf = ?");
    $stmt->bind_param("ss", $analista['email'], $analista['cpf']);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    
    if ($existe) {
        echo "<p style='color: orange;'>⚠️ " . $analista['nome'] . " já existe</p>";
        continue;
    }
    
    // Criar usuário
    $senha_hash = password_hash($analista['senha'], PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, telefone, cpf, senha, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $analista['nome'], $analista['email'], $analista['telefone'], $analista['cpf'], $senha_hash);
    
    if ($stmt->execute()) {
        $usuario_id = $conexao->insert_id;
        
        // Criar registro na tabela analistas
        $stmt2 = $conexao->prepare("INSERT INTO analistas (nome, especialidade, ativo, data_cadastro) VALUES (?, ?, 1, NOW())");
        $stmt2->bind_param("ss", $analista['nome'], $analista['especialidade']);
        $stmt2->execute();
        $analista_id = $conexao->insert_id;
        
        // Atualizar usuario com analista_id
        $stmt3 = $conexao->prepare("UPDATE usuarios SET analista_id = ? WHERE id = ?");
        $stmt3->bind_param("ii", $analista_id, $usuario_id);
        $stmt3->execute();
        
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<p>✅ <strong>" . $analista['nome'] . "</strong> criado com sucesso!</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar " . $analista['nome'] . ": " . $conexao->error . "</p>";
    }
}

echo "<hr><div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h3>🔑 DADOS DE LOGIN DOS ANALISTAS:</h3>";
echo "<p><strong>Carlos Silva:</strong> carlos.silva@autoservice.com | Senha: 123456</p>";
echo "<p><strong>João Oliveira:</strong> joao.oliveira@autoservice.com | Senha: 123456</p>";
echo "<p><strong>Maria Santos:</strong> maria.santos@autoservice.com | Senha: 123456</p>";
echo "<p><strong>URL Login:</strong> <a href='login.php'>login.php</a></p>";
echo "</div>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #2c3e50; }
a { color: #3498db; }
</style>