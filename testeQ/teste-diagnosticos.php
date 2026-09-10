<?php
require_once 'config.php';

echo "<h2>Teste de Diagnósticos - Estrutura das Tabelas</h2>";

$conexao = conectarBD();

// Verificar se as tabelas existem
$tabelas = ['relatorios_cliente', 'analistas', 'mecanicos', 'veiculos', 'usuarios', 'relatorios_mecanico'];

foreach ($tabelas as $tabela) {
    $result = $conexao->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Tabela '$tabela' existe</p>";
        
        // Mostrar estrutura da tabela
        $estrutura = $conexao->query("DESCRIBE $tabela");
        echo "<details><summary>Estrutura da tabela $tabela</summary>";
        echo "<table border='1' style='margin: 10px;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        while ($campo = $estrutura->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$campo['Field']}</td>";
            echo "<td>{$campo['Type']}</td>";
            echo "<td>{$campo['Null']}</td>";
            echo "<td>{$campo['Key']}</td>";
            echo "<td>{$campo['Default']}</td>";
            echo "</tr>";
        }
        echo "</table></details>";
    } else {
        echo "<p style='color: red;'>✗ Tabela '$tabela' NÃO existe</p>";
    }
}

// Contar registros em cada tabela
echo "<h3>Contagem de Registros</h3>";
foreach ($tabelas as $tabela) {
    $result = $conexao->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows > 0) {
        $count = $conexao->query("SELECT COUNT(*) as total FROM $tabela")->fetch_assoc()['total'];
        echo "<p>Tabela '$tabela': $count registros</p>";
    }
}

// Testar a consulta do admin-relatorios.php
echo "<h3>Teste da Consulta de Relatórios</h3>";
try {
    $stmt = $conexao->prepare("
        SELECT rc.*, u.nome as cliente_nome, u.email as cliente_email, 
               COALESCE(a.nome, m.nome, 'Não atribuído') as analista_nome, 
               v.marca, v.modelo, v.placa,
               rm.diagnostico, rm.status as resposta_status
        FROM relatorios_cliente rc
        JOIN usuarios u ON rc.usuario_id = u.id
        LEFT JOIN analistas a ON rc.analista_id = a.id
        LEFT JOIN mecanicos m ON rc.mecanico_id = m.id
        JOIN veiculos v ON rc.veiculo_id = v.id
        LEFT JOIN relatorios_mecanico rm ON rc.id = rm.relatorio_cliente_id
        ORDER BY rc.data_envio DESC
        LIMIT 5
    ");
    $stmt->execute();
    $relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo "<p style='color: green;'>✓ Consulta executada com sucesso!</p>";
    echo "<p>Encontrados " . count($relatorios) . " relatórios</p>";
    
    if (count($relatorios) > 0) {
        echo "<table border='1' style='margin: 10px;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Analista/Mecânico</th><th>Veículo</th><th>Status</th></tr>";
        foreach ($relatorios as $rel) {
            echo "<tr>";
            echo "<td>{$rel['id']}</td>";
            echo "<td>{$rel['cliente_nome']}</td>";
            echo "<td>{$rel['analista_nome']}</td>";
            echo "<td>{$rel['marca']} {$rel['modelo']}</td>";
            echo "<td>{$rel['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na consulta: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>