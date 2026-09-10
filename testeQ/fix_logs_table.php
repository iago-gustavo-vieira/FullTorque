<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Corrigindo estrutura da tabela logs...</h2>";

// Verificar se a tabela logs existe
$result = $conexao->query("SHOW TABLES LIKE 'logs'");

if ($result->num_rows == 0) {
    // Criar tabela logs se não existir
    $sql_create = "CREATE TABLE logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        acao VARCHAR(100) NOT NULL,
        descricao TEXT,
        ip VARCHAR(45),
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_id (usuario_id),
        INDEX idx_data_hora (data_hora),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conexao->query($sql_create)) {
        echo "<p style='color: green;'>✓ Tabela logs criada com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>✗ Erro ao criar tabela logs: " . $conexao->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Tabela logs já existe.</p>";
    
    // Verificar se há registros com usuario_id inválido
    $invalid_logs = $conexao->query("
        SELECT COUNT(*) as total 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        WHERE l.usuario_id IS NOT NULL AND u.id IS NULL
    ");
    
    if ($invalid_logs) {
        $count = $invalid_logs->fetch_assoc()['total'];
        if ($count > 0) {
            echo "<p style='color: orange;'>⚠ Encontrados $count registros de log com usuario_id inválido.</p>";
            
            // Corrigir registros inválidos
            $fix_query = "UPDATE logs SET usuario_id = NULL WHERE usuario_id NOT IN (SELECT id FROM usuarios)";
            if ($conexao->query($fix_query)) {
                echo "<p style='color: green;'>✓ Registros inválidos corrigidos!</p>";
            } else {
                echo "<p style='color: red;'>✗ Erro ao corrigir registros: " . $conexao->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Todos os registros de log estão válidos.</p>";
        }
    }
}

// Verificar estrutura da tabela
$structure = $conexao->query("DESCRIBE logs");
echo "<h3>Estrutura atual da tabela logs:</h3>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";

while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conexao->close();

echo "<p><a href='admin-usuarios.php'>← Voltar para Usuários</a></p>";
?>