<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Debug - Diagnósticos e Analistas</h2>";

// 1. Verificar analistas
echo "<h3>1. Analistas cadastrados:</h3>";
$analistas = $conexao->query("SELECT * FROM analistas")->fetch_all(MYSQLI_ASSOC);
foreach ($analistas as $analista) {
    echo "<p>ID: {$analista['id']}, Nome: {$analista['nome']}, Especialidade: {$analista['especialidade']}</p>";
}

// 2. Verificar usuários analistas
echo "<h3>2. Usuários com analista_id:</h3>";
$usuarios = $conexao->query("SELECT id, nome, email, analista_id FROM usuarios WHERE analista_id IS NOT NULL")->fetch_all(MYSQLI_ASSOC);
foreach ($usuarios as $user) {
    echo "<p>ID: {$user['id']}, Nome: {$user['nome']}, Email: {$user['email']}, Analista_ID: {$user['analista_id']}</p>";
}

// 3. Verificar diagnósticos enviados
echo "<h3>3. Diagnósticos enviados:</h3>";
$diagnosticos = $conexao->query("SELECT rc.*, u.nome as cliente_nome, a.nome as analista_nome FROM relatorios_cliente rc JOIN usuarios u ON rc.usuario_id = u.id LEFT JOIN analistas a ON rc.analista_id = a.id ORDER BY rc.data_envio DESC")->fetch_all(MYSQLI_ASSOC);

if (count($diagnosticos) > 0) {
    foreach ($diagnosticos as $diag) {
        echo "<p>ID: {$diag['id']}, Cliente: {$diag['cliente_nome']}, Analista: {$diag['analista_nome']}, Status: {$diag['status']}, Data: {$diag['data_envio']}</p>";
    }
} else {
    echo "<p>❌ Nenhum diagnóstico encontrado</p>";
}

// 4. Testar sessão de analista
echo "<h3>4. Teste de sessão:</h3>";
if (isset($_SESSION['analista_id'])) {
    echo "<p>✓ Sessão ativa - Analista ID: {$_SESSION['analista_id']}</p>";
    
    // Buscar diagnósticos para este analista
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM relatorios_cliente WHERE analista_id = ?");
    $stmt->bind_param("i", $_SESSION['analista_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo "<p>Diagnósticos para este analista: {$result['total']}</p>";
} else {
    echo "<p>❌ Nenhuma sessão de analista ativa</p>";
}

$conexao->close();
?>

<p><a href="agendamento-novo.php">Enviar Novo Diagnóstico</a> | <a href="analista-dashboard.php">Dashboard Analista</a></p>