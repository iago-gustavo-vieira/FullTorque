<?php
require_once 'config.php';
verificarLogin();

// Verificar se é mecânico
if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    exibirAlerta('danger', 'Acesso negado.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mecanico-dashboard.php');
    exit;
}

$conexao = conectarBD();

$relatorio_id = (int)$_POST['relatorio_id'];
$diagnostico = limparDados($_POST['diagnostico']);
$observacoes = limparDados($_POST['observacoes']);

// Validações
if (empty($relatorio_id) || empty($diagnostico)) {
    exibirAlerta('danger', 'Dados obrigatórios não preenchidos.');
    header('Location: mecanico-dashboard.php');
    exit;
}

// Verificar se o relatório pertence ao mecânico
$stmt = $conexao->prepare("SELECT id FROM relatorios_cliente WHERE id = ? AND mecanico_id = ?");
$stmt->bind_param("ii", $relatorio_id, $_SESSION['mecanico_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    exibirAlerta('danger', 'Relatório não encontrado ou não pertence a você.');
    header('Location: mecanico-dashboard.php');
    exit;
}

try {
    $conexao->begin_transaction();
    
    // Inserir ou atualizar diagnóstico
    $stmt = $conexao->prepare("
        INSERT INTO relatorios_mecanico (relatorio_cliente_id, diagnostico, observacoes, data_diagnostico) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        diagnostico = VALUES(diagnostico), 
        observacoes = VALUES(observacoes), 
        data_diagnostico = NOW()
    ");
    $stmt->bind_param("iss", $relatorio_id, $diagnostico, $observacoes);
    $stmt->execute();
    
    // Atualizar status do relatório
    $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'analisado' WHERE id = ?");
    $stmt->bind_param("i", $relatorio_id);
    $stmt->execute();
    
    // Registrar log
    registrarLog('diagnostico_realizado', "Diagnóstico realizado para relatório ID: $relatorio_id");
    
    $conexao->commit();
    
    exibirAlerta('success', 'Diagnóstico enviado com sucesso! O cliente será notificado.');
    
} catch (Exception $e) {
    $conexao->rollback();
    exibirAlerta('danger', 'Erro ao processar diagnóstico: ' . $e->getMessage());
}

header('Location: mecanico-dashboard.php');
$conexao->close();
?>