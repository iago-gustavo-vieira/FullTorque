<?php
require_once 'config.php';

$mecanico_id = 1; // Roger
$data = '2025-11-29';

$conexao = conectarBD();

// Verificar se a tabela tem os campos
$check = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'data_agendamento'");
echo "Campo data_agendamento existe: " . ($check->num_rows > 0 ? "SIM" : "NÃO") . "<br>";

$check = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'hora_agendamento'");
echo "Campo hora_agendamento existe: " . ($check->num_rows > 0 ? "SIM" : "NÃO") . "<br><br>";

// Buscar agendamentos
$stmt = $conexao->prepare("SELECT id, mecanico_id, data_agendamento, hora_agendamento, status FROM relatorios_cliente WHERE mecanico_id = ? AND data_agendamento = ?");
$stmt->bind_param("is", $mecanico_id, $data);
$stmt->execute();
$result = $stmt->get_result();

echo "Agendamentos encontrados para mecânico $mecanico_id na data $data:<br><br>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Hora: {$row['hora_agendamento']} | Status: {$row['status']}<br>";
    }
} else {
    echo "Nenhum agendamento encontrado.<br>";
}

$stmt->close();
$conexao->close();
?>
