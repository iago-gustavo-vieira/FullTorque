<?php
require_once 'header.php';

$conexao = conectarBD();

// Verifica se a coluna 'cor' já existe
if (!colunaExiste($conexao, 'veiculos', 'cor')) {
    $sql = "ALTER TABLE veiculos ADD COLUMN cor VARCHAR(50) NULL AFTER ano";
    
    if ($conexao->query($sql)) {
        echo "✅ Coluna 'cor' adicionada com sucesso!";
    } else {
        echo "❌ Erro ao adicionar coluna: " . $conexao->error;
    }
} else {
    echo "ℹ️ A coluna 'cor' já existe na tabela.";
}

$conexao->close();
?>
