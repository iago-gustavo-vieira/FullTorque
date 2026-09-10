<?php
require_once 'config.php';

$conexao = conectarBD();

$sql = "ALTER TABLE usuarios ADD COLUMN token_recuperacao VARCHAR(4) DEFAULT NULL";

if ($conexao->query($sql) === TRUE) {
    echo "Coluna token_recuperacao adicionada com sucesso!";
} else {
    if ($conexao->errno == 1060) {
        echo "Coluna token_recuperacao já existe!";
    } else {
        echo "Erro: " . $conexao->error;
    }
}

$conexao->close();
?>
