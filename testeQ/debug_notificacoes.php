<?php
require_once 'config.php';
verificarLogin();
$conexao = conectarBD();

echo "<h2>Debug - Notificações</h2>";

// Verificar tabelas
echo "<h3>1. Verificando tabelas:</h3>";
$tabelas = ['notificacoes', 'usuarios'];
foreach ($tabelas as $tabela) {
    $result = $conexao->query("SHOW TABLES LIKE '$tabela'");
    echo "Tabela '$tabela': " . ($result->num_rows > 0 ? "✅ Existe" : "❌ Não existe") . "<br>";
}

// Verificar usuários
echo "<h3>2. Usuários cadastrados:</h3>";
$usuarios = $conexao->query("SELECT id, nome, nivel_acesso FROM usuarios");
while ($user = $usuarios->fetch_assoc()) {
    echo "ID: {$user['id']} - Nome: {$user['nome']} - Nível: {$user['nivel_acesso']}<br>";
}

// Verificar notificações
echo "<h3>3. Notificações na tabela:</h3>";
$notifs = $conexao->query("SELECT * FROM notificacoes ORDER BY data_criacao DESC LIMIT 10");
if ($notifs->num_rows > 0) {
    while ($notif = $notifs->fetch_assoc()) {
        echo "ID: {$notif['id']} - Usuário: {$notif['usuario_id']} - Título: {$notif['titulo']} - Lida: " . ($notif['lida'] ? 'Sim' : 'Não') . " - Data: {$notif['data_criacao']}<br>";
    }
} else {
    echo "❌ Nenhuma notificação encontrada<br>";
}

// Verificar notificações do usuário atual
echo "<h3>4. Notificações do usuário atual (ID: {$_SESSION['usuario_id']}):</h3>";
$user_notifs = $conexao->query("SELECT * FROM notificacoes WHERE usuario_id = {$_SESSION['usuario_id']} ORDER BY data_criacao DESC");
if ($user_notifs->num_rows > 0) {
    while ($notif = $user_notifs->fetch_assoc()) {
        echo "Título: {$notif['titulo']} - Lida: " . ($notif['lida'] ? 'Sim' : 'Não') . " - Data: {$notif['data_criacao']}<br>";
    }
} else {
    echo "❌ Nenhuma notificação para este usuário<br>";
}

// Testar inserção
echo "<h3>5. Teste de inserção:</h3>";
$stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, lida, data_criacao) VALUES (?, 'sistema', 'Teste Debug', 'Esta é uma notificação de teste', 0, NOW())");
$stmt->bind_param("i", $_SESSION['usuario_id']);
if ($stmt->execute()) {
    echo "✅ Notificação de teste inserida com sucesso!<br>";
} else {
    echo "❌ Erro ao inserir notificação: " . $stmt->error . "<br>";
}
?>

<a href="notificacoes.php">Ver Notificações</a> | 
<a href="enviar_promocoes.php">Enviar Promoções</a>