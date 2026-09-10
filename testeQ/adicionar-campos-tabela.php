<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Adicionando Campos na Tabela relatorios_mecanico</h2>";

try {
    // Adicionar campos para preferência do cliente
    try {
        $sql1 = "ALTER TABLE relatorios_mecanico ADD COLUMN data_preferida_cliente DATETIME NULL";
        $conexao->query($sql1);
        echo "<p>✓ Campo 'data_preferida_cliente' adicionado</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Campo 'data_preferida_cliente' já existe</p>";
    }
    
    try {
        $sql2 = "ALTER TABLE relatorios_mecanico ADD COLUMN observacoes_cliente TEXT NULL";
        $conexao->query($sql2);
        echo "<p>✓ Campo 'observacoes_cliente' adicionado</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Campo 'observacoes_cliente' já existe</p>";
    }
    
    echo "<p><strong>SUCESSO!</strong> Campos adicionados com sucesso.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>