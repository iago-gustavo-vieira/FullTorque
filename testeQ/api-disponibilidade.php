<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if (!isset($_GET['mecanico_id']) || !is_numeric($_GET['mecanico_id'])) {
    echo json_encode([]);
    exit;
}

$mecanico_id = (int)$_GET['mecanico_id'];

try {
    $conexao = conectarBD();
    
    // Buscar disponibilidade do mecânico por dia da semana
    $stmt = $conexao->prepare("
        SELECT dia_semana, hora_inicio, hora_fim 
        FROM mecanico_disponibilidade 
        WHERE mecanico_id = ? AND ativo = 1
        ORDER BY dia_semana
    ");
    $stmt->bind_param("i", $mecanico_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $disponibilidade = [];
    while ($row = $result->fetch_assoc()) {
        $disponibilidade[$row['dia_semana']] = [
            'hora_inicio' => $row['hora_inicio'],
            'hora_fim' => $row['hora_fim']
        ];
    }
    
    // Se não há disponibilidade cadastrada, usar padrão
    if (empty($disponibilidade)) {
        $disponibilidade = [
            1 => ['hora_inicio' => '08:00', 'hora_fim' => '17:00'], // Segunda
            2 => ['hora_inicio' => '08:00', 'hora_fim' => '17:00'], // Terça
            3 => ['hora_inicio' => '08:00', 'hora_fim' => '17:00'], // Quarta
            4 => ['hora_inicio' => '08:00', 'hora_fim' => '17:00'], // Quinta
            5 => ['hora_inicio' => '08:00', 'hora_fim' => '17:00'], // Sexta
        ];
    }
    
    $conexao->close();
    echo json_encode($disponibilidade);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>