<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Corrigindo Foreign Key - relatorios_cliente</h2>";

try {
    // 1. Verificar constraint atual
    echo "<h3>1. Verificando constraints atuais:</h3>";
    $result = $conexao->query("SHOW CREATE TABLE relatorios_cliente");
    $row = $result->fetch_assoc();
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
    
    // 2. Remover constraint incorreta
    echo "<h3>2. Removendo constraint incorreta:</h3>";
    $conexao->query("ALTER TABLE relatorios_cliente DROP FOREIGN KEY relatorios_cliente_ibfk_2");
    echo "<p>✓ Constraint removida</p>";
    
    // 3. Adicionar constraint correta
    echo "<h3>3. Adicionando constraint correta:</h3>";
    $conexao->query("ALTER TABLE relatorios_cliente ADD CONSTRAINT fk_analista FOREIGN KEY (analista_id) REFERENCES analistas(id) ON DELETE CASCADE");
    echo "<p>✓ Constraint correta adicionada</p>";
    
    echo "<p><strong>SUCESSO!</strong> Foreign key corrigida.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="agendamento-novo.php">← Testar Novo Diagnóstico</a></p>