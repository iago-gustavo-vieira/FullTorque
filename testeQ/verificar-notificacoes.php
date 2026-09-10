<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['notificacoes' => []]);
    exit;
}

$conexao = conectarBD();

// Buscar notificações não lidas
$stmt = $conexao->prepare("
    SELECT id, tipo, titulo, mensagem, data_criacao 
    FROM notificacoes 
    WHERE usuario_id = ? AND lida = FALSE 
    ORDER BY data_criacao DESC 
    LIMIT 10
");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$notificacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conexao->close();

header('Content-Type: application/json');
echo json_encode(['notificacoes' => $notificacoes]);
?>