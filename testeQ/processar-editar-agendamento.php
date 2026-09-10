<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$id = (int)$_POST['id'];
$data = limparDados($_POST['data']);
$hora = limparDados($_POST['hora']);
$observacoes = limparDados($_POST['observacoes']);
$usuario_id = $_SESSION['usuario_id'];

$conexao = conectarBD();

$stmt = $conexao->prepare("
    UPDATE agendamentos 
    SET data_agendamento = ?, hora_inicio = ?, observacoes = ?
    WHERE id = ? AND usuario_id = ? AND status = 'agendado'
");
$stmt->bind_param("sssii", $data, $hora, $observacoes, $id, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
}

$conexao->close();
