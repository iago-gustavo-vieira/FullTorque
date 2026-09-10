<?php
require_once 'config.php';
verificarLogin();

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$relatorio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!in_array($acao, ['confirmar', 'rejeitar']) || $relatorio_id <= 0) {
    exibirAlerta('danger', 'Parâmetros inválidos.');
    header('Location: relatorios.php');
    exit;
}

$conexao = conectarBD();

// Verificar se o relatório pertence ao usuário logado
$stmt = $conexao->prepare("
    SELECT rc.id, rc.analista_id, rm.id as mecanico_id
    FROM relatorios_cliente rc
    JOIN relatorios_mecanico rm ON rc.id = rm.relatorio_cliente_id
    WHERE rc.id = ? AND rc.usuario_id = ?
");
$stmt->bind_param("ii", $relatorio_id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exibirAlerta('danger', 'Relatório não encontrado.');
    header('Location: relatorios.php');
    exit;
}

$relatorio = $result->fetch_assoc();
$stmt->close();

// Processar ação
if ($acao == 'confirmar') {
    // Atualizar status do agendamento para confirmado
    $stmt = $conexao->prepare("UPDATE relatorios_mecanico SET status_agendamento = 'confirmado' WHERE relatorio_cliente_id = ?");
    $stmt->bind_param("i", $relatorio_id);
    $sucesso = $stmt->execute();
    
    if ($sucesso) {
        // Criar notificação para o analista
        $stmt_user = $conexao->prepare("SELECT id FROM usuarios WHERE analista_id = ?");
        $stmt_user->bind_param("i", $relatorio['analista_id']);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        
        if ($user_result->num_rows > 0) {
            $analista_user = $user_result->fetch_assoc();
            
            $stmt_notif = $conexao->prepare("
                INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) 
                VALUES (?, 'horario_confirmado', 'Horário Confirmado', 'Cliente confirmou o horário proposto para o atendimento.')
            ");
            $stmt_notif->bind_param("i", $analista_user['id']);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        $stmt_user->close();
        
        registrarLog('horario_confirmado', "Cliente confirmou horário para relatório ID: $relatorio_id");
        exibirAlerta('success', 'Horário confirmado com sucesso! Aguarde o atendimento na data marcada.');
    } else {
        exibirAlerta('danger', 'Erro ao confirmar horário: ' . $conexao->error);
    }
    
} elseif ($acao == 'rejeitar') {
    // Atualizar status do agendamento para rejeitado
    $stmt = $conexao->prepare("UPDATE relatorios_mecanico SET status_agendamento = 'rejeitado' WHERE relatorio_cliente_id = ?");
    $stmt->bind_param("i", $relatorio_id);
    $sucesso = $stmt->execute();
    
    if ($sucesso) {
        // Criar notificação para o analista
        $stmt_user = $conexao->prepare("SELECT id FROM usuarios WHERE analista_id = ?");
        $stmt_user->bind_param("i", $relatorio['analista_id']);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        
        if ($user_result->num_rows > 0) {
            $analista_user = $user_result->fetch_assoc();
            
            $stmt_notif = $conexao->prepare("
                INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) 
                VALUES (?, 'horario_rejeitado', 'Solicitação de Nova Data', 'Cliente solicitou mudança no horário proposto.')
            ");
            $stmt_notif->bind_param("i", $analista_user['id']);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        $stmt_user->close();
        
        registrarLog('horario_rejeitado', "Cliente rejeitou horário para relatório ID: $relatorio_id");
        exibirAlerta('success', 'Solicitação de nova data enviada! O analista entrará em contato.');
    } else {
        exibirAlerta('danger', 'Erro ao processar solicitação: ' . $conexao->error);
    }
}

$stmt->close();
$conexao->close();

header('Location: relatorios.php');
exit;
?>