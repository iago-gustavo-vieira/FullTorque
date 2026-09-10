<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

if ($_POST) {
    $relatorio_id = intval($_POST['relatorio_id']);
    $novo_status = limparDados($_POST['novo_status']);
    $observacoes = limparDados($_POST['observacoes']);
    
    $conexao = conectarBD();
    
    try {
        // Atualizar status do relatório
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = ?, data_atualizacao = NOW() WHERE id = ?");
        $stmt->bind_param("si", $novo_status, $relatorio_id);
        
        if ($stmt->execute()) {
            // Se há observações, inserir no histórico
            if (!empty($observacoes)) {
                $stmt2 = $conexao->prepare("INSERT INTO historico_diagnostico (relatorio_id, acao, observacoes, data_acao, usuario_id) VALUES (?, ?, ?, NOW(), ?)");
                $acao = "Status alterado para: " . $novo_status;
                $stmt2->bind_param("issi", $relatorio_id, $acao, $observacoes, $_SESSION['usuario_id']);
                $stmt2->execute();
                $stmt2->close();
            }
            
            exibirAlerta('success', 'Status do diagnóstico alterado com sucesso!');
        } else {
            exibirAlerta('error', 'Erro ao alterar status do diagnóstico.');
        }
        
        $stmt->close();
    } catch (Exception $e) {
        exibirAlerta('error', 'Erro: ' . $e->getMessage());
    }
    
    $conexao->close();
}

header("Location: admin-relatorios.php");
exit;
?>