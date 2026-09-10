<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Verificando João Oliveira como Analista</h2>";

// 1. Verificar se João existe na tabela usuarios
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ?");
$email_joao = "joao.oliveira@autoservice.com";
$stmt->bind_param("s", $email_joao);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario_joao = $resultado->fetch_assoc();
    echo "<p>✓ João encontrado na tabela usuarios:</p>";
    echo "<ul>";
    echo "<li>ID: {$usuario_joao['id']}</li>";
    echo "<li>Nome: {$usuario_joao['nome']}</li>";
    echo "<li>Email: {$usuario_joao['email']}</li>";
    echo "<li>Analista ID: " . ($usuario_joao['analista_id'] ?: 'NULL') . "</li>";
    echo "</ul>";
    
    // 2. Verificar se existe na tabela analistas
    if ($usuario_joao['analista_id']) {
        $stmt2 = $conexao->prepare("SELECT * FROM analistas WHERE id = ?");
        $stmt2->bind_param("i", $usuario_joao['analista_id']);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();
        
        if ($resultado2->num_rows > 0) {
            $analista = $resultado2->fetch_assoc();
            echo "<p>✓ João encontrado na tabela analistas:</p>";
            echo "<ul>";
            echo "<li>ID: {$analista['id']}</li>";
            echo "<li>Nome: {$analista['nome']}</li>";
            echo "<li>Especialidade: " . ($analista['especialidade'] ?: 'Não definida') . "</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ João NÃO encontrado na tabela analistas</p>";
        }
    } else {
        echo "<p>❌ João não tem analista_id definido</p>";
    }
    
    // 3. Verificar diagnósticos enviados para João
    echo "<h3>Diagnósticos para João:</h3>";
    $stmt3 = $conexao->prepare("SELECT rc.*, u.nome as cliente_nome FROM relatorios_cliente rc JOIN usuarios u ON rc.usuario_id = u.id WHERE rc.analista_id = ?");
    $stmt3->bind_param("i", $usuario_joao['analista_id']);
    $stmt3->execute();
    $diagnosticos = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (count($diagnosticos) > 0) {
        echo "<p>✓ Encontrados " . count($diagnosticos) . " diagnósticos:</p>";
        foreach ($diagnosticos as $diag) {
            echo "<p>- ID: {$diag['id']}, Cliente: {$diag['cliente_nome']}, Status: {$diag['status']}, Data: {$diag['data_envio']}</p>";
        }
    } else {
        echo "<p>❌ Nenhum diagnóstico encontrado para João</p>";
    }
    
} else {
    echo "<p>❌ João Oliveira não encontrado na tabela usuarios</p>";
}

$conexao->close();
?>

<p><a href="analista-dashboard.php">← Voltar ao Dashboard</a></p>