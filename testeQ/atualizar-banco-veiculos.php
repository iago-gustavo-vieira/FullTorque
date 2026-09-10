<?php
require_once 'config.php';

// Conecta ao banco de dados
$conexao = conectarBD();

// Adiciona a coluna imagem_url à tabela veiculos se ela não existir
$sql = "SHOW COLUMNS FROM veiculos LIKE 'imagem_url'";
$result = $conexao->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE veiculos ADD COLUMN imagem_url VARCHAR(255) DEFAULT NULL AFTER observacoes";
    if ($conexao->query($sql) === TRUE) {
        echo "Coluna imagem_url adicionada com sucesso à tabela veiculos.<br>";
    } else {
        echo "Erro ao adicionar coluna imagem_url: " . $conexao->error . "<br>";
    }
}

// Atualiza as imagens dos veículos existentes com placeholders
$sql = "UPDATE veiculos SET imagem_url = CONCAT('https://via.placeholder.com/400x200/2c3e50/ffffff?text=', marca, ' ', modelo) WHERE imagem_url IS NULL";

if ($conexao->query($sql) === TRUE) {
    echo "Imagens dos veículos atualizadas com sucesso.<br>";
} else {
    echo "Erro ao atualizar imagens dos veículos: " . $conexao->error . "<br>";
}

// Fecha a conexão
$conexao->close();

echo "<br>Atualização do banco de dados concluída!";
echo "<br><a href='veiculos.php'>Voltar para a página de veículos</a>";
?>