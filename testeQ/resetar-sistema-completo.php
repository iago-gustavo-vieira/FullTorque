<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Resetando Sistema Completo</h2>";

try {
    $conexao->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // 1. Limpar dados
    echo "<h3>1. Limpando dados:</h3>";
    $conexao->query("DELETE FROM relatorios_mecanico");
    $conexao->query("DELETE FROM relatorios_cliente");
    $conexao->query("DELETE FROM notificacoes");
    $conexao->query("UPDATE usuarios SET analista_id = NULL");
    $conexao->query("DELETE FROM analistas");
    echo "<p>✓ Dados limpos</p>";
    
    // 2. Criar analistas
    echo "<h3>2. Criando analistas:</h3>";
    $analistas = [
        ['nome' => 'Carlos Silva', 'especialidade' => 'Motor'],
        ['nome' => 'Maria Santos', 'especialidade' => 'Transmissão'], 
        ['nome' => 'João Oliveira', 'especialidade' => 'Diagnóstico Geral']
    ];
    
    $analista_ids = [];
    foreach ($analistas as $analista) {
        $stmt = $conexao->prepare("INSERT INTO analistas (nome, especialidade, experiencia, ativo) VALUES (?, ?, 5, 1)");
        $stmt->bind_param("ss", $analista['nome'], $analista['especialidade']);
        $stmt->execute();
        $analista_ids[] = $conexao->insert_id;
        echo "<p>✓ {$analista['nome']} (ID: {$conexao->insert_id})</p>";
    }
    
    // 3. Configurar usuários
    echo "<h3>3. Configurando usuários:</h3>";
    $usuarios = [
        ['nome' => 'Carlos Silva', 'email' => 'carlos.silva@autoservice.com', 'analista_id' => $analista_ids[0]],
        ['nome' => 'Maria Santos', 'email' => 'maria.santos@autoservice.com', 'analista_id' => $analista_ids[1]],
        ['nome' => 'João Oliveira', 'email' => 'joao.oliveira@autoservice.com', 'analista_id' => $analista_ids[2]]
    ];
    
    foreach ($usuarios as $user) {
        $stmt_check = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $user['email']);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            $usuario_existente = $result->fetch_assoc();
            $stmt_update = $conexao->prepare("UPDATE usuarios SET analista_id = ? WHERE id = ?");
            $stmt_update->bind_param("ii", $user['analista_id'], $usuario_existente['id']);
            $stmt_update->execute();
            echo "<p>✓ {$user['nome']} atualizado</p>";
        } else {
            $senha = password_hash('123456', PASSWORD_DEFAULT);
            $stmt_create = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, telefone, analista_id, data_cadastro) VALUES (?, ?, ?, '(11) 99999-9999', ?, NOW())");
            $stmt_create->bind_param("sssi", $user['nome'], $user['email'], $senha, $user['analista_id']);
            $stmt_create->execute();
            echo "<p>✓ {$user['nome']} criado</p>";
        }
    }
    
    $conexao->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<p><strong>SISTEMA RESETADO!</strong></p>";
    echo "<h3>Credenciais:</h3>";
    echo "<ul>";
    echo "<li>carlos.silva@autoservice.com / 123456</li>";
    echo "<li>maria.santos@autoservice.com / 123456</li>";
    echo "<li>joao.oliveira@autoservice.com / 123456</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="agendamento-novo.php">Testar Novo Diagnóstico</a></p>