<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

if ($_POST) {
    $relatorio_id = intval($_POST['relatorio_id']);
    $diagnostico = limparDados($_POST['diagnostico']);
    $observacoes = limparDados($_POST['observacoes_diagnostico']);
    
    $conexao = conectarBD();
    
    try {
        // Verificar se já existe diagnóstico
        $stmt_check = $conexao->prepare("SELECT diagnostico FROM relatorios_mecanico WHERE relatorio_cliente_id = ?");
        $stmt_check->bind_param("i", $relatorio_id);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();
        
        if ($resultado->num_rows > 0) {
            // Atualizar diagnóstico existente
            $stmt = $conexao->prepare("UPDATE relatorios_mecanico SET diagnostico = ?, observacoes_data = ?, data_proposta = NOW() WHERE relatorio_cliente_id = ?");
            $stmt->bind_param("ssi", $diagnostico, $observacoes, $relatorio_id);
        } else {
            // Inserir novo diagnóstico
            $stmt = $conexao->prepare("INSERT INTO relatorios_mecanico (relatorio_cliente_id, diagnostico, observacoes_data, data_proposta, status) VALUES (?, ?, ?, NOW(), 'concluido')");
            $stmt->bind_param("iss", $relatorio_id, $diagnostico, $observacoes);
        }
        
        if ($stmt->execute()) {
            // Atualizar status do relatório para respondido
            $stmt2 = $conexao->prepare("UPDATE relatorios_cliente SET status = 'respondido', data_atualizacao = NOW() WHERE id = ?");
            $stmt2->bind_param("i", $relatorio_id);
            $stmt2->execute();
            $stmt2->close();
            
            // Inserir no histórico
            $stmt3 = $conexao->prepare("INSERT INTO historico_diagnostico (relatorio_id, acao, observacoes, data_acao, usuario_id) VALUES (?, ?, ?, NOW(), ?)");
            $acao = "Diagnóstico confirmado";
            $obs_historico = "Diagnóstico: " . substr($diagnostico, 0, 100) . "...";
            $stmt3->bind_param("issi", $relatorio_id, $acao, $obs_historico, $_SESSION['usuario_id']);
            $stmt3->execute();
            $stmt3->close();
            
            exibirAlerta('success', 'Diagnóstico confirmado com sucesso!');
        } else {
            exibirAlerta('error', 'Erro ao confirmar diagnóstico.');
        }
        
        $stmt->close();
        $stmt_check->close();
    } catch (Exception $e) {
        exibirAlerta('error', 'Erro: ' . $e->getMessage());
    }
    
    $conexao->close();
}

header("Location: admin-relatorios.php");
exit;
?>