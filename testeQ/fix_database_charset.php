<?php
require_once 'config.php';
$conexao = conectarBD();

echo "<h2>Corrigindo Charset do Banco de Dados</h2>";

// Definir charset para utf8mb4
$conexao->set_charset("utf8mb4");

// Alterar charset das tabelas existentes
$tabelas = ['notificacoes', 'promocoes', 'usuarios'];

foreach ($tabelas as $tabela) {
    $result = $conexao->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows > 0) {
        echo "Alterando charset da tabela '$tabela'...<br>";
        $conexao->query("ALTER TABLE $tabela CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Tabela '$tabela' atualizada<br>";
    } else {
        echo "❌ Tabela '$tabela' não encontrada<br>";
    }
}

// Alterar charset do banco de dados
$db_name = 'auto_service'; // Substitua pelo nome do seu banco
$conexao->query("ALTER DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "✅ Charset do banco de dados atualizado<br>";

echo "<br><strong>Correção concluída! Agora os emojis devem funcionar corretamente.</strong>";
?>