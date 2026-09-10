<?php
require_once 'config.php';

$conexao = conectarBD();

// Remove cartões duplicados mantendo apenas o mais recente de cada número
$sql = "DELETE c1 FROM cartoes_usuario c1
        INNER JOIN cartoes_usuario c2 
        WHERE c1.id < c2.id 
        AND c1.usuario_id = c2.usuario_id 
        AND c1.numero = c2.numero";

if ($conexao->query($sql)) {
    echo "Cartões duplicados removidos com sucesso!";
} else {
    echo "Erro ao remover duplicados: " . $conexao->error;
}

$conexao->close();
?>