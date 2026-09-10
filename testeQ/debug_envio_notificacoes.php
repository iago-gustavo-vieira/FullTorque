<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

echo "<h2>Debug - Sistema de Notificações</h2>";

// 1. Verificar se a tabela existe
echo "<h3>1. Verificando tabela notificacoes:</h3>";
$result = $conexao->query("SHOW TABLES LIKE 'notificacoes'");
if ($result->num_rows > 0) {
    echo "✓ Tabela 'notificacoes' existe<br>";
    
    // Mostrar estrutura
    $estrutura = $conexao->query("DESCRIBE notificacoes");
    echo "<pre>";
    while ($col = $estrutura->fetch_assoc()) {
        print_r($col);
    }
    echo "</pre>";
} else {
    echo "✗ Tabela 'notificacoes' NÃO existe<br>";
}

// 2. Listar todos os usuários
echo "<h3>2. Usuários no sistema:</h3>";
$usuarios = $conexao->query("SELECT id, nome, email, nivel_acesso FROM usuarios ORDER BY id");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th></tr>";
while ($user = $usuarios->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['nome']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['nivel_acesso']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Listar todas as notificações
echo "<h3>3. Notificações existentes:</h3>";
$notifs = $conexao->query("SELECT n.*, u.nome as usuario_nome FROM notificacoes n LEFT JOIN usuarios u ON n.usuario_id = u.id ORDER BY n.data_criacao DESC LIMIT 20");
if ($notifs->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Usuário</th><th>Tipo</th><th>Título</th><th>Lida</th><th>Data</th></tr>";
    while ($notif = $notifs->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$notif['id']}</td>";
        echo "<td>{$notif['usuario_nome']} (ID: {$notif['usuario_id']})</td>";
        echo "<td>{$notif['tipo']}</td>";
        echo "<td>{$notif['titulo']}</td>";
        echo "<td>" . ($notif['lida'] ? 'Sim' : 'Não') . "</td>";
        echo "<td>{$notif['data_criacao']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhuma notificação encontrada.<br>";
}

// 4. Teste de inserção
echo "<h3>4. Teste de Inserção:</h3>";
if (isset($_GET['testar'])) {
    $usuarios_teste = $conexao->query("SELECT id FROM usuarios WHERE nivel_acesso != 'admin' LIMIT 3");
    $count = 0;
    
    while ($user = $usuarios_teste->fetch_assoc()) {
        $titulo = "Teste de Notificação - " . date('H:i:s');
        $mensagem = "Esta é uma notificação de teste enviada em " . date('d/m/Y H:i:s');
        
        $stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, lida, data_criacao) VALUES (?, 'sistema', ?, ?, 0, NOW())");
        $stmt->bind_param("iss", $user['id'], $titulo, $mensagem);
        
        if ($stmt->execute()) {
            echo "✓ Notificação enviada para usuário ID {$user['id']}<br>";
            $count++;
        } else {
            echo "✗ Erro ao enviar para usuário ID {$user['id']}: " . $stmt->error . "<br>";
        }
    }
    
    echo "<br><strong>Total enviado: $count notificações</strong><br>";
    echo "<a href='debug_envio_notificacoes.php'>Atualizar página</a>";
} else {
    echo "<a href='debug_envio_notificacoes.php?testar=1' style='background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Enviar Notificações de Teste</a>";
}

// 5. Verificar charset
echo "<h3>5. Configuração do Banco:</h3>";
$charset = $conexao->query("SHOW VARIABLES LIKE 'character_set%'");
echo "<pre>";
while ($row = $charset->fetch_assoc()) {
    echo "{$row['Variable_name']}: {$row['Value']}\n";
}
echo "</pre>";

$conexao->close();
?>

<br><br>
<a href="enviar_promocoes.php" style="background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Voltar para Enviar Promoções</a>
