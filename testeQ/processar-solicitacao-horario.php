<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    $horario_inicio_solicitado = limparDados($_POST['horario_inicio_solicitado']);
    $horario_fim_solicitado = limparDados($_POST['horario_fim_solicitado']);
    $dias_trabalho_solicitado = isset($_POST['dias_trabalho_solicitado']) ? implode(',', $_POST['dias_trabalho_solicitado']) : '';
    $justificativa = limparDados($_POST['justificativa']);
    
    // Buscar configurações atuais
    $stmt_atual = $conexao->prepare("SELECT horario_inicio, horario_fim, dias_trabalho FROM analista_configuracoes WHERE analista_id = ?");
    $stmt_atual->bind_param("i", $_SESSION['analista_id']);
    $stmt_atual->execute();
    $config_atual = $stmt_atual->get_result()->fetch_assoc();
    
    if (!$config_atual) {
        $config_atual = ['horario_inicio' => '08:00', 'horario_fim' => '18:00', 'dias_trabalho' => 'segunda,terca,quarta,quinta,sexta'];
    }
    
    try {
        // Inserir solicitação
        $stmt = $conexao->prepare("INSERT INTO solicitacoes_horario 
            (analista_id, horario_inicio_atual, horario_fim_atual, dias_trabalho_atual, 
             horario_inicio_solicitado, horario_fim_solicitado, dias_trabalho_solicitado, justificativa) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("isssssss", 
            $_SESSION['analista_id'],
            $config_atual['horario_inicio'],
            $config_atual['horario_fim'], 
            $config_atual['dias_trabalho'],
            $horario_inicio_solicitado,
            $horario_fim_solicitado,
            $dias_trabalho_solicitado,
            $justificativa
        );
        
        $stmt->execute();
        
        registrarLog('solicitacao_horario', "Analista solicitou mudança de horário");
        exibirAlerta('success', 'Solicitação enviada! Os administradores irão analisar sua solicitação.');
        
    } catch (Exception $e) {
        exibirAlerta('danger', 'Erro ao enviar solicitação: ' . $e->getMessage());
    }
    
    $conexao->close();
}

header("Location: analista-configuracoes.php");
exit;
?>