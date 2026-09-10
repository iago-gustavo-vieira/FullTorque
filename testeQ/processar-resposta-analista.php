<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$relatorio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!in_array($acao, ['aceitar_mudanca', 'recusar_mudanca']) || $relatorio_id <= 0) {
    exibirAlerta('danger', 'Parâmetros inválidos.');
    header('Location: analista-dashboard.php');
    exit;
}

$conexao = conectarBD();

// Verificar se o relatório pertence a este analista
$stmt = $conexao->prepare("SELECT rc.*, u.nome as cliente_nome FROM relatorios_cliente rc JOIN usuarios u ON rc.usuario_id = u.id WHERE rc.id = ? AND rc.analista_id = ? AND rc.status = 'solicitou_mudanca'");
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

if ($acao == 'aceitar_mudanca') {
    $nova_data = isset($_GET['nova_data']) ? limparDados($_GET['nova_data']) : '';
    
    if (empty($nova_data)) {
        exibirAlerta('danger', 'Nova data é obrigatória.');
        header('Location: analista-dashboard.php');
        exit;
    }
    
    // Atualizar data proposta
    $stmt = $conexao->prepare("UPDATE relatorios_mecanico SET data_proposta = ? WHERE relatorio_cliente_id = ?");
    $stmt->bind_param("si", $nova_data, $relatorio_id);
    $stmt->execute();
    
    // Atualizar status para analisado
    $stmt2 = $conexao->prepare("UPDATE relatorios_cliente SET status = 'analisado' WHERE id = ?");
    $stmt2->bind_param("i", $relatorio_id);
    $stmt2->execute();
    
    // Notificar cliente
    $stmt3 = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) VALUES (?, 'nova_data_proposta', 'Nova Data Proposta', 'O analista propôs uma nova data para seu atendimento. Verifique seus diagnósticos.')");
    $stmt3->bind_param("i", $relatorio['usuario_id']);
    $stmt3->execute();
    
    exibirAlerta('success', 'Nova data proposta enviada ao cliente!');
    
} else { // recusar_mudanca
    // Atualizar status para rejeitado
    $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'rejeitado' WHERE id = ?");
    $stmt->bind_param("i", $relatorio_id);
    $stmt->execute();
    
    // Notificar cliente
    $stmt2 = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) VALUES (?, 'solicitacao_recusada', 'Solicitação Recusada', 'O analista não pode atender sua solicitação de mudança de horário.')");
    $stmt2->bind_param("i", $relatorio['usuario_id']);
    $stmt2->execute();
    
    exibirAlerta('success', 'Solicitação recusada.');
}

$conexao->close();
header('Location: analista-dashboard.php');
exit;
?>