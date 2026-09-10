<?php
require_once 'config.php';
verificarLogin();

if (!isset($_GET['diagnostico_id']) || !isset($_GET['data']) || !isset($_GET['hora'])) {
    exibirAlerta('error', 'Dados incompletos para agendamento.');
    header('Location: relatorios.php');
    exit;
}

$diagnostico_id = (int)$_GET['diagnostico_id'];
$data = $_GET['data'];
$hora = $_GET['hora'];
$observacoes = $_GET['observacoes'] ?? '';

$conexao = conectarBD();

// Verificar se o diagnóstico pertence ao usuário
$stmt = $conexao->prepare("SELECT veiculo_id, mecanico_id FROM relatorios_cliente WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $diagnostico_id, $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    exibirAlerta('error', 'Diagnóstico não encontrado.');
    header('Location: relatorios.php');
    exit;
}

$diagnostico = $resultado->fetch_assoc();
$veiculo_id = $diagnostico['veiculo_id'];
$mecanico_id = $diagnostico['mecanico_id'];

// Calcular hora_fim (1 hora depois)
$hora_inicio = $hora . ':00';
$hora_fim = date('H:i:s', strtotime($hora_inicio . ' +1 hour'));

// Verificar se já existe agendamento neste horário
$check_stmt = $conexao->prepare("
    SELECT id FROM agendamentos 
    WHERE data_agendamento = ? 
    AND hora_inicio = ? 
    AND status IN ('agendado', 'confirmado')
");
$check_stmt->bind_param("ss", $data, $hora_inicio);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    exibirAlerta('error', 'Este horário já está ocupado. Por favor, selecione outro horário.');
    $conexao->close();
    header('Location: relatorios.php');
    exit;
}
$check_stmt->close();

// Inserir agendamento
$stmt = $conexao->prepare("
    INSERT INTO agendamentos (usuario_id, veiculo_id, mecanico_id, data_agendamento, hora_inicio, hora_fim, observacoes, status, data_criacao) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'agendado', NOW())
");
$stmt->bind_param("iiissss", $_SESSION['usuario_id'], $veiculo_id, $mecanico_id, $data, $hora_inicio, $hora_fim, $observacoes);

if ($stmt->execute()) {
    registrarLog('agendamento_criado', "Agendamento criado para diagnóstico ID: $diagnostico_id em $data às $hora");
    exibirAlerta('success', '✅ Serviço agendado com sucesso! Você receberá uma confirmação em breve.');
} else {
    exibirAlerta('error', 'Erro ao agendar serviço: ' . $conexao->error);
}

$conexao->close();
header('Location: relatorios.php');
exit;
?>
