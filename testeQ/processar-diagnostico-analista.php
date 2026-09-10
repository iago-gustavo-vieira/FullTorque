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
$diagnostico = isset($_POST['diagnostico']) ? limparDados($_POST['diagnostico']) : '';
$data_proposta = isset($_POST['data_proposta']) ? $_POST['data_proposta'] : '';
$observacoes = isset($_POST['observacoes']) ? limparDados($_POST['observacoes']) : '';

// Validação
if ($relatorio_id <= 0 || empty(trim($diagnostico)) || empty($data_proposta)) {
    exibirAlerta('danger', 'Todos os campos obrigatórios devem ser preenchidos.');
    header('Location: analista-dashboard.php');
    exit;
}

if (strlen(trim($diagnostico)) < 10) {
    exibirAlerta('danger', 'O diagnóstico deve ter pelo menos 10 caracteres.');
    header('Location: analista-dashboard.php');
    exit;
}

$conexao = conectarBD();

// Verificar se o relatório pertence a este analista e ainda não foi respondido
$stmt = $conexao->prepare("
    SELECT rc.id, rc.usuario_id, u.nome as cliente_nome, u.email as cliente_email
    FROM relatorios_cliente rc
    JOIN usuarios u ON rc.usuario_id = u.id
    WHERE rc.id = ? AND rc.analista_id = ? AND rc.status = 'pendente'
");
$stmt->bind_param("ii", $relatorio_id, $_SESSION['analista_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exibirAlerta('danger', 'Relatório não encontrado ou já processado.');
    header('Location: analista-dashboard.php');
    exit;
}

$relatorio = $result->fetch_assoc();
$stmt->close();

// Inserir diagnóstico do analista com data proposta
$stmt = $conexao->prepare("
    INSERT INTO relatorios_mecanico (relatorio_cliente_id, analista_id, diagnostico, data_proposta, observacoes_data) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iisss", $relatorio_id, $_SESSION['analista_id'], $diagnostico, $data_proposta, $observacoes);

if ($stmt->execute()) {
    // Atualizar status do relatório cliente
    $stmt_update = $conexao->prepare("UPDATE relatorios_cliente SET status = 'analisado' WHERE id = ?");
    $stmt_update->bind_param("i", $relatorio_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // Criar notificação para o cliente sobre agendamento
    $mensagem_notif = "Você recebeu um relatório sobre o agendamento, confirme-o agora para realizarmos o diagnóstico do seu veículo";
    
    $stmt_notif = $conexao->prepare("
        INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) 
        VALUES (?, 'agendamento_proposto', 'Confirme seu Agendamento', ?)
    ");
    $stmt_notif->bind_param("is", $relatorio['usuario_id'], $mensagem_notif);
    $stmt_notif->execute();
    $stmt_notif->close();
    
    registrarLog('diagnostico_enviado', "Diagnóstico enviado para relatório ID: $relatorio_id");
    exibirAlerta('success', 'Diagnóstico enviado com sucesso! O cliente será notificado.');
} else {
    exibirAlerta('danger', 'Erro ao enviar diagnóstico: ' . $conexao->error);
}

$stmt->close();
$conexao->close();

header('Location: analista-dashboard.php');
exit;
?>