<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Testando Sistema de Notificações</h2>";

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p>Faça login primeiro!</p>";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Criar algumas notificações de teste se não existirem
$count_promocoes = $conexao->query("SELECT COUNT(*) as total FROM notificacoes_promocoes")->fetch_assoc()['total'];

if ($count_promocoes == 0) {
    echo "<h3>Criando notificações de teste...</h3>";
    
    // Inserir algumas notificações de promoção
    $conexao->query("INSERT INTO notificacoes_promocoes (promocao_id, usuario_id, tipo, titulo, mensagem, enviado, data_envio) VALUES 
        (1, $usuario_id, 'sistema', '🎉 Nova Promoção Disponível!', 'Aproveite 20% de desconto em todos os serviços até o final do mês!', 1, NOW()),
        (1, NULL, 'sistema', '🔥 Oferta Relâmpago!', 'Por tempo limitado: Troca de óleo com 30% de desconto!', 1, NOW()),
        (1, $usuario_id, 'sistema', '💰 Programa de Fidelidade', 'Você acumulou pontos suficientes para resgatar um desconto!', 1, NOW())
    ");
    
    echo "✅ Notificações de teste criadas!<br>";
}

// Mostrar estatísticas
echo "<h3>Estatísticas:</h3>";

$stats = $conexao->query("SELECT COUNT(*) as total FROM notificacoes_promocoes WHERE enviado = 1")->fetch_assoc();
echo "Total de notificações de promoções enviadas: " . $stats['total'] . "<br>";

$stats_user = $conexao->query("SELECT COUNT(*) as total FROM notificacoes_promocoes WHERE (usuario_id = $usuario_id OR usuario_id IS NULL) AND enviado = 1")->fetch_assoc();
echo "Notificações para você: " . $stats_user['total'] . "<br>";

// Verificar notificações lidas
$conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_lidas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    notificacao_promocao_id INT NOT NULL,
    data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_leitura (usuario_id, notificacao_promocao_id)
)");

$lidas = $conexao->query("SELECT COUNT(*) as total FROM notificacoes_lidas WHERE usuario_id = $usuario_id")->fetch_assoc();
echo "Notificações lidas por você: " . $lidas['total'] . "<br>";

$nao_lidas = $stats_user['total'] - $lidas['total'];
echo "Notificações não lidas: " . $nao_lidas . "<br>";

echo "<br><h3>Últimas notificações:</h3>";
$notifs = $conexao->query("SELECT * FROM notificacoes_promocoes WHERE (usuario_id = $usuario_id OR usuario_id IS NULL) AND enviado = 1 ORDER BY data_criacao DESC LIMIT 5");
while ($notif = $notifs->fetch_assoc()) {
    $lida = $conexao->query("SELECT id FROM notificacoes_lidas WHERE usuario_id = $usuario_id AND notificacao_promocao_id = {$notif['id']}")->num_rows > 0;
    echo "- " . $notif['titulo'] . " (" . ($lida ? "Lida" : "Não lida") . ")<br>";
}

echo "<br><p><a href='notificacoes.php'>Ver todas as notificações</a></p>";
echo "<p><a href='index.php'>Voltar ao início</a></p>";

$conexao->close();
?>