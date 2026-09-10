<?php
require_once 'config.php';
header('Content-Type: application/json');

$mecanico_id = isset($_GET['mecanico_id']) ? (int)$_GET['mecanico_id'] : 0;
$data = isset($_GET['data']) ? $_GET['data'] : '';

if (!$mecanico_id || !$data) {
    echo json_encode([]);
    exit;
}

$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT hora_inicio FROM agendamentos WHERE mecanico_id = ? AND data_agendamento = ? AND status != 'cancelado' AND hora_inicio IS NOT NULL");
$stmt->bind_param("is", $mecanico_id, $data);
$stmt->execute();
$result = $stmt->get_result();

$horarios_ocupados = [];
while ($row = $result->fetch_assoc()) {
    $horarios_ocupados[] = substr($row['hora_inicio'], 0, 5);
}

$stmt->close();
$conexao->close();

echo json_encode($horarios_ocupados);
?>
