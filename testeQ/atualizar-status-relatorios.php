<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Atualizando Status dos Relatórios</h2>";

// Verificar estrutura atual da coluna status
$result = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'status'");
$coluna = $result->fetch_assoc();

echo "<h3>Status Atual:</h3>";
echo "<p>" . $coluna['Type'] . "</p>";

// Atualizar ENUM para incluir novos status
$sql = "ALTER TABLE relatorios_cliente MODIFY COLUMN status ENUM('pendente', 'analisado', 'aceito', 'rejeitado', 'solicitou_mudanca', 'realizado') DEFAULT 'pendente'";

if ($conexao->query($sql)) {
    echo "<p style='color: green;'>✅ Status atualizados com sucesso!</p>";
} else {
    echo "<p style='color: red;'>❌ Erro ao atualizar status: " . $conexao->error . "</p>";
}

// Verificar estrutura final
echo "<h3>Status Final:</h3>";
$result = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'status'");
$coluna = $result->fetch_assoc();
echo "<p>" . $coluna['Type'] . "</p>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>