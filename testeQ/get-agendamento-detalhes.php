<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once 'config.php';
ob_clean();
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$agendamento_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

$stmt = $conexao->prepare("
    SELECT a.*, v.marca, v.modelo, v.placa, v.ano, v.cor
    FROM agendamentos a 
    JOIN veiculos v ON a.veiculo_id = v.id 
    WHERE a.id = ? AND a.usuario_id = ?
");
$stmt->bind_param("ii", $agendamento_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado']);
    exit;
}

$agendamento = $result->fetch_assoc();

// Buscar serviços
$stmt = $conexao->prepare("
    SELECT s.nome, ai.quantidade, ai.preco 
    FROM agendamento_itens ai 
    JOIN servicos s ON ai.servico_id = s.id 
    WHERE ai.agendamento_id = ?
");
$stmt->bind_param("i", $agendamento_id);
$stmt->execute();
$servicos_result = $stmt->get_result();

if ($servicos_result->num_rows > 0) {
    $servicos_html = '<ul style="margin: 0; padding-left: 20px; list-style: none;">';
    while ($servico = $servicos_result->fetch_assoc()) {
        $servicos_html .= '<li>• ' . $servico['nome'] . ' - R$ ' . number_format($servico['preco'], 2, ',', '.') . '</li>';
    }
    $servicos_html .= '</ul>';
} else {
    $servicos_html = 'Nenhum serviço cadastrado';
}

$status_map = [
    'agendado' => 'agendado',
    'confirmado' => 'confirmado',
    'em_andamento' => 'em-andamento',
    'concluido' => 'concluido',
    'cancelado' => 'cancelado'
];

echo json_encode([
    'success' => true,
    'data' => formatarData($agendamento['data_agendamento'], 'd/m/Y'),
    'data_raw' => $agendamento['data_agendamento'],
    'hora' => substr($agendamento['hora_inicio'], 0, 5) . ' - ' . substr($agendamento['hora_fim'], 0, 5),
    'hora_raw' => substr($agendamento['hora_inicio'], 0, 5),
    'veiculo' => $agendamento['marca'] . ' ' . $agendamento['modelo'] . ' (' . $agendamento['placa'] . ')',
    'status' => ucfirst(str_replace('_', ' ', $agendamento['status'])),
    'status_class' => $status_map[$agendamento['status']] ?? 'agendado',
    'servicos' => $servicos_html,
    'observacoes' => !empty($agendamento['observacoes']) ? $agendamento['observacoes'] : 'Sem observações',
    'criado_em' => isset($agendamento['created_at']) ? formatarData($agendamento['created_at'], 'd/m/Y H:i') : 'N/A',
    'total' => 'R$ ' . number_format($agendamento['valor_total'] ?? 0, 2, ',', '.')
]);

$conexao->close();
exit;
