<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a coluna já existe
$result = $conexao->query("SHOW COLUMNS FROM pagamentos LIKE 'link_pagamento'");
if ($result->num_rows == 0) {
    // Adicionar coluna link_pagamento se não existir
    $sql = "ALTER TABLE pagamentos ADD COLUMN link_pagamento TEXT";
    $conexao->query($sql);
    echo "Coluna link_pagamento adicionada com sucesso!";
} else {
    echo "Coluna link_pagamento já existe!";
}

echo "Tabela pagamentos atualizada com sucesso!";
?>