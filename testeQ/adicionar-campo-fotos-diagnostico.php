<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a coluna já existe
$result = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'fotos'");

if ($result->num_rows == 0) {
    // Adicionar coluna fotos
    $sql = "ALTER TABLE relatorios_cliente ADD COLUMN fotos TEXT NULL AFTER urgencia";
    
    if ($conexao->query($sql)) {
        echo "✅ Coluna 'fotos' adicionada com sucesso na tabela relatorios_cliente!<br>";
    } else {
        echo "❌ Erro ao adicionar coluna: " . $conexao->error . "<br>";
    }
} else {
    echo "ℹ️ Coluna 'fotos' já existe na tabela relatorios_cliente.<br>";
}

$conexao->close();

echo "<br><a href='mecanico-diagnosticos.php'>← Voltar para Diagnósticos</a>";
?>
