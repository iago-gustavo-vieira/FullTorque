<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Limpando Todos os Diagnósticos</h2>";

try {
    $conexao->begin_transaction();
    
    // Desabilitar verificação de chave estrangeira temporariamente
    $conexao->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Deletar relatórios de mecânico
    $result1 = $conexao->query("DELETE FROM relatorios_mecanico");
    echo "<p>✓ Relatórios de mecânico deletados: " . $conexao->affected_rows . "</p>";
    
    // Deletar relatórios de cliente
    $result2 = $conexao->query("DELETE FROM relatorios_cliente");
    echo "<p>✓ Relatórios de cliente deletados: " . $conexao->affected_rows . "</p>";
    
    // Reabilitar verificação de chave estrangeira
    $conexao->query("SET FOREIGN_KEY_CHECKS = 1");
    
    $conexao->commit();
    echo "<p><strong>SUCESSO!</strong> Todos os diagnósticos foram removidos.</p>";
    
} catch (Exception $e) {
    $conexao->rollback();
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>