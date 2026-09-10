<?php
require_once 'config.php';
verificarLogin();

// Verificar se é analista
if (!isset($_SESSION['analista_id']) || $_SESSION['analista_id'] <= 0) {
    exibirAlerta('danger', 'Acesso negado.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: analista-dashboard.php');
    exit;
}

$relatorio_id = isset($_POST['relatorio_id']) ? (int)$_POST['relatorio_id'] : 0;
$data_proposta = isset($_POST['data_proposta']) ? $_POST['data_proposta'] : '';
$observacoes = isset($_POST['observacoes']) ? limparDados($_POST['observacoes']) : '';

if ($relatorio_id <= 0 || empty($data_proposta)) {
    exibirAlerta('danger', 'Dados obrigatórios não preenchidos.');
    header('Location: analista-dashboard.php');
    exit;
}

$conexao = conectarBD();

// Verificar se o relatório existe e tem diagnóstico
$stmt = $conexao->prepare("
    SELECT rm.id, rc.usuario_id, u.nome as cliente_nome, u.email as cliente_email
    FROM relatorios_mecanico rm
    JOIN relatorios_cliente rc ON rm.relatorio_cliente_id = rc.id
    JOIN usuarios u ON rc.usuario_id = u.id
    WHERE rc.id = ? AND rc.analista_id = ?
");
$stmt->bind_param("ii", $relatorio_id, $_SESSION['analista_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exibirAlerta('danger', 'Relatório não encontrado.');
    header('Location: analista-dashboard.php');
    exit;
}

$relatorio = $result->fetch_assoc();
$stmt->close();

// Atualizar com a data proposta
$stmt = $conexao->prepare("
    UPDATE relatorios_mecanico 
    SET data_proposta = ?, observacoes_data = ?, status_agendamento = 'pendente' 
    WHERE id = ?
");
$stmt->bind_param("ssi", $data_proposta, $observacoes, $relatorio['id']);

if ($stmt->execute()) {
    // Criar notificação para o cliente
    $stmt_notif = $conexao->prepare("
        INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) 
        VALUES (?, 'proposta_data', 'Nova Proposta de Data', 'O analista propôs uma data para atendimento do seu veículo. Verifique e confirme.')
    ");
    $stmt_notif->bind_param("i", $relatorio['usuario_id']);
    $stmt_notif->execute();
    $stmt_notif->close();
    
    registrarLog('proposta_data', "Data proposta para relatório ID: $relatorio_id");
    exibirAlerta('success', 'Data proposta enviada com sucesso! O cliente será notificado.');
} else {
    exibirAlerta('danger', 'Erro ao propor data: ' . $conexao->error);
}

$stmt->close();
$conexao->close();

header('Location: analista-dashboard.php');
exit;
?>