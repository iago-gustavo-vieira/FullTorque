<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a coluna já existe
$check = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'primeiro_acesso'");

if ($check->num_rows == 0) {
    $sql = "ALTER TABLE usuarios ADD COLUMN primeiro_acesso TINYINT(1) DEFAULT 1";
} else {
    echo "✅ Campo 'primeiro_acesso' já existe!";
    $conexao->close();
    exit;
}

if ($conexao->query($sql)) {
    echo "✅ Campo 'primeiro_acesso' adicionado com sucesso!";
} else {
    echo "❌ Erro: " . $conexao->error;
}

$conexao->close();
?>
