<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Limpando Analistas Duplicados</h2>";

// Buscar analistas duplicados na tabela analistas
$stmt = $conexao->prepare("
    SELECT nome, COUNT(*) as total 
    FROM analistas 
    GROUP BY nome 
    HAVING COUNT(*) > 1
");
$stmt->execute();
$duplicados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($duplicados)) {
    echo "<h3>Analistas Duplicados Encontrados:</h3>";
    foreach ($duplicados as $dup) {
        echo "<p>- " . $dup['nome'] . " (" . $dup['total'] . " registros)</p>";
    }
    
    // Remover duplicados, mantendo apenas o primeiro registro de cada nome
    foreach ($duplicados as $dup) {
        $nome = $dup['nome'];
        
        // Buscar todos os registros deste nome
        $stmt = $conexao->prepare("SELECT id FROM analistas WHERE nome = ? ORDER BY id ASC");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $ids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Manter o primeiro, remover os outros
        for ($i = 1; $i < count($ids); $i++) {
            $id_para_remover = $ids[$i]['id'];
            
            // Atualizar usuarios que referenciam este analista_id duplicado
            $stmt_update = $conexao->prepare("UPDATE usuarios SET analista_id = ? WHERE analista_id = ?");
            $stmt_update->bind_param("ii", $ids[0]['id'], $id_para_remover);
            $stmt_update->execute();
            
            // Remover o registro duplicado
            $stmt_delete = $conexao->prepare("DELETE FROM analistas WHERE id = ?");
            $stmt_delete->bind_param("i", $id_para_remover);
            $stmt_delete->execute();
            
            echo "<p style='color: green;'>✅ Removido analista duplicado ID: $id_para_remover</p>";
        }
    }
} else {
    echo "<p style='color: green;'>✅ Nenhum analista duplicado encontrado.</p>";
}

// Mostrar analistas finais
echo "<hr><h3>Analistas Únicos:</h3>";
$analistas = $conexao->query("SELECT * FROM analistas ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
foreach ($analistas as $analista) {
    echo "<div style='background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
    echo "<strong>" . $analista['nome'] . "</strong><br>";
    echo "Especialidade: " . $analista['especialidade'] . "<br>";
    echo "ID: " . $analista['id'];
    echo "</div>";
}

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>