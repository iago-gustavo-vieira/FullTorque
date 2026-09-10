<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = isset($_GET['data']) ? $_GET['data'] : '';

if (!$data) {
    echo json_encode([]);
    exit;
}

$conexao = conectarBD();

$stmt = $conexao->prepare("
    SELECT TIME_FORMAT(hora_inicio, '%H:%i') as horario
    FROM agendamentos
    WHERE data_agendamento = ? 
    AND status IN ('agendado', 'confirmado')
");
$stmt->bind_param("s", $data);
$stmt->execute();
$resultado = $stmt->get_result();

$horariosOcupados = [];
while ($row = $resultado->fetch_assoc()) {
    $horariosOcupados[] = $row['horario'];
}

$stmt->close();
$conexao->close();

echo json_encode($horariosOcupados);
?>
