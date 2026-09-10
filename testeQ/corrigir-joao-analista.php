<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Correção: Transformar João Oliveira em Analista</h2>";

// 1. Verificar estrutura da tabela analistas
echo "<h3>1. Verificando estrutura da tabela analistas:</h3>";
$result = $conexao->query("DESCRIBE analistas");
while ($row = $result->fetch_assoc()) {
    echo "<p>- {$row['Field']} ({$row['Type']})</p>";
}

// 2. Verificar se João existe como cliente
echo "<h3>2. Verificando João como cliente:</h3>";
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ?");
$email_joao = "joao.oliveira@autoservice.com";
$stmt->bind_param("s", $email_joao);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario_joao = $resultado->fetch_assoc();
    echo "<p>✓ João encontrado como cliente (ID: {$usuario_joao['id']})</p>";
    
    // 3. Simplesmente atualizar o analista_id na tabela usuarios
    echo "<h3>3. Marcando João como analista:</h3>";
    
    // Usar um ID de analista existente ou criar um novo registro simples
    $stmt_analista = $conexao->prepare("SELECT id FROM analistas LIMIT 1");
    $stmt_analista->execute();
    $analista_result = $stmt_analista->get_result();
    
    if ($analista_result->num_rows > 0) {
        // Usar ID de analista existente
        $analista_existente = $analista_result->fetch_assoc();
        $analista_id = $analista_existente['id'];
        echo "<p>Usando analista_id existente: $analista_id</p>";
    } else {
        // Criar um registro básico na tabela analistas
        $stmt_insert = $conexao->prepare("INSERT INTO analistas (nome) VALUES (?)");
        $nome_analista = "João Oliveira";
        $stmt_insert->bind_param("s", $nome_analista);
        $stmt_insert->execute();
        $analista_id = $conexao->insert_id;
        echo "<p>Novo analista criado com ID: $analista_id</p>";
    }
    
    // 4. Atualizar tabela usuarios
    $stmt_update = $conexao->prepare("UPDATE usuarios SET analista_id = ? WHERE id = ?");
    $stmt_update->bind_param("ii", $analista_id, $usuario_joao['id']);
    
    if ($stmt_update->execute()) {
        echo "<p>✓ <strong>SUCESSO!</strong> João Oliveira agora é um analista.</p>";
        echo "<p>Credenciais: joao.oliveira@autoservice.com / 123456</p>";
    } else {
        echo "<p>❌ Erro ao marcar usuário como analista</p>";
    }
} else {
    echo "<p>❌ João Oliveira não encontrado na tabela usuarios</p>";
}

$conexao->close();
?>