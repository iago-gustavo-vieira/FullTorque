<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Corrigindo Todas as Foreign Keys</h2>";

try {
    $conexao->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // 1. Corrigir relatorios_cliente
    echo "<h3>1. Corrigindo relatorios_cliente:</h3>";
    try {
        $conexao->query("ALTER TABLE relatorios_cliente DROP FOREIGN KEY relatorios_cliente_ibfk_2");
    } catch (Exception $e) {}
    try {
        $conexao->query("ALTER TABLE relatorios_cliente DROP FOREIGN KEY fk_analista");
    } catch (Exception $e) {}
    $conexao->query("ALTER TABLE relatorios_cliente ADD CONSTRAINT fk_relatorio_analista FOREIGN KEY (analista_id) REFERENCES analistas(id) ON DELETE CASCADE");
    echo "<p>✓ relatorios_cliente corrigida</p>";
    
    // 2. Corrigir relatorios_mecanico
    echo "<h3>2. Corrigindo relatorios_mecanico:</h3>";
    try {
        $conexao->query("ALTER TABLE relatorios_mecanico DROP FOREIGN KEY relatorios_mecanico_ibfk_2");
    } catch (Exception $e) {}
    $conexao->query("ALTER TABLE relatorios_mecanico ADD CONSTRAINT fk_mecanico_analista FOREIGN KEY (analista_id) REFERENCES analistas(id) ON DELETE CASCADE");
    echo "<p>✓ relatorios_mecanico corrigida</p>";
    
    $conexao->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<p><strong>SUCESSO!</strong> Todas as foreign keys corrigidas.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

$conexao->close();
?>

<p><a href="debug-diagnosticos.php">← Testar Sistema</a></p>