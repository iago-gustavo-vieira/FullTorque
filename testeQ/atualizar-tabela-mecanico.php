<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Atualizando Tabela relatorios_mecanico</h2>";

// Verificar estrutura atual
echo "<h3>Estrutura Atual:</h3>";
$result = $conexao->query("DESCRIBE relatorios_mecanico");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

// Adicionar colunas necessárias
$colunas = [
    "data_proposta" => "DATETIME NULL AFTER diagnostico",
    "observacoes_data" => "TEXT NULL AFTER data_proposta", 
    "status_agendamento" => "ENUM('pendente', 'confirmado', 'rejeitado') DEFAULT 'pendente' AFTER observacoes_data"
];

foreach ($colunas as $coluna => $definicao) {
    // Verificar se a coluna já existe
    $result = $conexao->query("SHOW COLUMNS FROM relatorios_mecanico LIKE '$coluna'");
    
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE relatorios_mecanico ADD COLUMN $coluna $definicao";
        
        if ($conexao->query($sql)) {
            echo "<p style='color: green;'>✅ Coluna '$coluna' adicionada com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao adicionar coluna '$coluna': " . $conexao->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Coluna '$coluna' já existe.</p>";
    }
}

echo "<hr><h3>Estrutura Final:</h3>";
$result = $conexao->query("DESCRIBE relatorios_mecanico");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>