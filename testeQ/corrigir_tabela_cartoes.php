<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a tabela existe e corrigir o campo tipo
$sql = "ALTER TABLE cartoes_usuario MODIFY COLUMN tipo VARCHAR(10) NOT NULL";

if ($conexao->query($sql)) {
    echo "Campo tipo corrigido com sucesso!";
} else {
    echo "Erro: " . $conexao->error;
}
?>