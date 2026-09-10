<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['notificacao_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$notificacao_id = (int)$_POST['notificacao_id'];

$conexao = conectarBD();

$stmt = $conexao->prepare("
    UPDATE notificacoes 
    SET lida = TRUE 
    WHERE id = ? AND usuario_id = ?
");
$stmt->bind_param("ii", $notificacao_id, $_SESSION['usuario_id']);
$success = $stmt->execute();
$stmt->close();

$conexao->close();

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>