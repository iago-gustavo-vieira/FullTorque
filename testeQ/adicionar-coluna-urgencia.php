<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Adicionando Coluna Urgência</h2>";

// Verificar se a coluna já existe
$result = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'urgencia'");

if ($result->num_rows == 0) {
    // Adicionar a coluna urgencia
    $sql = "ALTER TABLE relatorios_cliente ADD COLUMN urgencia ENUM('baixa', 'media', 'alta') DEFAULT 'media' AFTER descricao_problema";
    
    if ($conexao->query($sql)) {
        echo "<p style='color: green;'>✅ Coluna 'urgencia' adicionada com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao adicionar coluna: " . $conexao->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Coluna 'urgencia' já existe na tabela.</p>";
}

// Mostrar estrutura atual da tabela
echo "<hr><h3>Estrutura da Tabela relatorios_cliente:</h3>";
$result = $conexao->query("DESCRIBE relatorios_cliente");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>