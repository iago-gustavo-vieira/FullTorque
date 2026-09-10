<?php
require_once 'config.php';
verificarLogin();

$agendamento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$silencioso = isset($_GET['silencioso']) ? true : false;

if (!$agendamento_id) {
    if (!$silencioso) {
        exibirAlerta('error', 'ID de agendamento inválido.');
        header('Location: relatorios.php');
    }
    exit;
}

$conexao = conectarBD();

// Verificar se o agendamento pertence ao usuário
$stmt = $conexao->prepare("SELECT id FROM agendamentos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $agendamento_id, $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    if (!$silencioso) {
        exibirAlerta('error', 'Agendamento não encontrado.');
        header('Location: relatorios.php');
    }
    $conexao->close();
    exit;
}

// Cancelar agendamento
$stmt = $conexao->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?");
$stmt->bind_param("i", $agendamento_id);

if ($stmt->execute()) {
    registrarLog('agendamento_cancelado', "Agendamento ID: $agendamento_id cancelado pelo usuário");
    if (!$silencioso) {
        exibirAlerta('success', 'Agendamento cancelado com sucesso.');
        header('Location: relatorios.php?t=' . time());
    }
} else {
    if (!$silencioso) {
        exibirAlerta('error', 'Erro ao cancelar agendamento.');
        header('Location: relatorios.php?t=' . time());
    }
}

$conexao->close();
exit;
?>
