<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Adicionando Campo na Tabela notificacoes</h2>";

try {
    // Adicionar campo link_acao para botão nas notificações
    try {
        $sql = "ALTER TABLE notificacoes ADD COLUMN link_acao VARCHAR(255) NULL";
        $conexao->query($sql);
        echo "<p>✓ Campo 'link_acao' adicionado na tabela notificacoes</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Campo já existe ou erro: " . $e->getMessage() . "</p>";
    }
    
    echo "<p><strong>SUCESSO!</strong> Campo adicionado com sucesso.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>