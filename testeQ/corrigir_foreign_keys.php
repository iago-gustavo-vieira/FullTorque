<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>Corrigir Foreign Keys</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { color: #109349; }
        a { color: #109349; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php
require_once 'config.php';

$conexao = conectarBD();
echo "<h2>Corrigindo Foreign Keys</h2>";

// 1. Verificar se existe coluna id_usuario na tabela veiculos
if (colunaExiste($conexao, 'veiculos', 'id_usuario')) {
    echo "⚠️ Encontrada coluna 'id_usuario' na tabela veiculos<br>";
    
    // Remover foreign key constraint
    $result = $conexao->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'veiculos' 
        AND COLUMN_NAME = 'id_usuario'
        AND CONSTRAINT_NAME != 'PRIMARY'
    ");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $constraint = $row['CONSTRAINT_NAME'];
            $conexao->query("ALTER TABLE veiculos DROP FOREIGN KEY `$constraint`");
            echo "✅ Foreign key '$constraint' removida<br>";
        }
    }
    
    // Verificar se usuario_id já existe
    if (colunaExiste($conexao, 'veiculos', 'usuario_id')) {
        // Copiar dados de id_usuario para usuario_id
        $conexao->query("UPDATE veiculos SET usuario_id = id_usuario WHERE id_usuario IS NOT NULL");
        echo "✅ Dados copiados de id_usuario para usuario_id<br>";
        
        // Remover coluna id_usuario
        $conexao->query("ALTER TABLE veiculos DROP COLUMN id_usuario");
        echo "✅ Coluna 'id_usuario' removida<br>";
    } else {
        // Renomear id_usuario para usuario_id
        $conexao->query("ALTER TABLE veiculos CHANGE id_usuario usuario_id INT NULL");
        echo "✅ Coluna 'id_usuario' renomeada para 'usuario_id'<br>";
    }
} else {
    echo "✅ Coluna 'id_usuario' não existe (OK)<br>";
}

// 2. Garantir que usuario_id existe
if (!colunaExiste($conexao, 'veiculos', 'usuario_id')) {
    $conexao->query("ALTER TABLE veiculos ADD COLUMN usuario_id INT NULL AFTER id");
    echo "✅ Coluna 'usuario_id' adicionada<br>";
}

// 3. Remover todas as foreign keys antigas da tabela veiculos
$result = $conexao->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
    AND TABLE_NAME = 'veiculos' 
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $constraint = $row['CONSTRAINT_NAME'];
        $conexao->query("ALTER TABLE veiculos DROP FOREIGN KEY `$constraint`");
        echo "✅ Foreign key '$constraint' removida<br>";
    }
}

echo "<br>✅ Todas as foreign keys foram corrigidas!<br>";
echo "✅ A coluna 'usuario_id' agora está livre de constraints<br>";

$conexao->close();
echo "<br><a href='veiculo-novo.php'>Testar cadastro de veículo</a> | ";
echo "<a href='index.php'>Voltar ao sistema</a>";
?>
</body>
</html>
