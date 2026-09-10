<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

echo "<h2>Teste Rápido de Notificações</h2>";

// Buscar um cliente (não admin)
$cliente = $conexao->query("SELECT id, nome FROM usuarios WHERE nivel_acesso != 'admin' LIMIT 1")->fetch_assoc();

if (!$cliente) {
    echo "<p style='color: red;'>Nenhum cliente encontrado no sistema!</p>";
    exit;
}

echo "<p>Cliente encontrado: <strong>{$cliente['nome']}</strong> (ID: {$cliente['id']})</p>";

// Enviar notificação de teste
$titulo = "Teste - " . date('H:i:s');
$mensagem = "Esta é uma notificação de teste enviada em " . date('d/m/Y H:i:s');

$stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, lida, data_criacao) VALUES (?, 'sistema', ?, ?, 0, NOW())");
$stmt->bind_param("iss", $cliente['id'], $titulo, $mensagem);

if ($stmt->execute()) {
    $notif_id = $stmt->insert_id;
    echo "<p style='color: green;'>✓ Notificação enviada com sucesso! ID: $notif_id</p>";
    
    // Verificar se foi inserida
    $verifica = $conexao->query("SELECT * FROM notificacoes WHERE id = $notif_id")->fetch_assoc();
    echo "<h3>Dados inseridos:</h3>";
    echo "<pre>";
    print_r($verifica);
    echo "</pre>";
    
    echo "<p><a href='notificacoes.php' style='background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Notificações (como cliente)</a></p>";
} else {
    echo "<p style='color: red;'>✗ Erro ao enviar: " . $stmt->error . "</p>";
}

$conexao->close();
?>

<br>
<a href="enviar_promocoes.php">Voltar para Enviar Promoções</a>
