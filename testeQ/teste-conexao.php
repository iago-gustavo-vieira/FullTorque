<?php
// Teste de conexão com o banco de dados
require_once 'config.php';

echo "<h2>Teste de Conexão - FullTorque</h2>";

try {
    $conexao = conectarBD();
    echo "<p style='color: green;'>✅ Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Testar uma consulta simples
    $result = $conexao->query("SELECT COUNT(*) as total FROM usuarios");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>📊 Total de usuários cadastrados: " . $row['total'] . "</p>";
    }
    
    // Verificar estrutura da tabela usuarios
    $result = $conexao->query("DESCRIBE usuarios");
    if ($result) {
        echo "<h3>📋 Estrutura da tabela 'usuarios':</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    $conexao->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<br><a href='home.php' style='background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voltar ao Site</a>";
?>