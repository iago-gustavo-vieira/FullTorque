<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['mecanico_id']) || !isset($_GET['data'])) {
    echo json_encode(['error' => 'Parâmetros obrigatórios não fornecidos']);
    exit;
}

$mecanico_id = (int)$_GET['mecanico_id'];
$data = $_GET['data'];

// Validar formato da data
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    echo json_encode(['error' => 'Formato de data inválido']);
    exit;
}

// Não permitir datas passadas
if ($data < date('Y-m-d')) {
    echo json_encode(['error' => 'Data não pode ser no passado']);
    exit;
}

// Verificar se é dia útil (segunda a sexta)
$dia_semana = date('N', strtotime($data));
if ($dia_semana > 5) { // Sábado ou domingo
    echo json_encode([]);
    exit;
}

$conexao = conectarBD();

// Buscar horários da agenda do mecânico para a data específica
$stmt = $conexao->prepare("
    SELECT ma.id, ma.hora_inicio, ma.hora_fim, ma.disponivel,
           CASE 
               WHEN da.id IS NOT NULL THEN 0 
               ELSE ma.disponivel 
           END as disponivel_real
    FROM mecanico_agenda ma
    LEFT JOIN diagnostico_agendamentos da ON ma.id = da.agenda_id 
        AND da.status NOT IN ('cancelado')
    WHERE ma.mecanico_id = ? 
        AND ma.data_disponivel = ?
        AND ma.disponivel = 1
    ORDER BY ma.hora_inicio
");

$stmt->bind_param("is", $mecanico_id, $data);
$stmt->execute();
$result = $stmt->get_result();

$horarios = [];
while ($row = $result->fetch_assoc()) {
    $horarios[] = [
        'id' => $row['id'],
        'hora_inicio' => $row['hora_inicio'],
        'hora_fim' => $row['hora_fim'],
        'disponivel' => (bool)$row['disponivel_real']
    ];
}

$stmt->close();
$conexao->close();

echo json_encode($horarios);
?>