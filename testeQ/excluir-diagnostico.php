<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

if (!isset($_GET['id'])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'ID do diagnóstico não informado.'];
    header("Location: admin-relatorios.php");
    exit;
}

$relatorio_id = (int)$_GET['id'];
$conexao = conectarBD();

try {
    // Iniciar transação
    $conexao->autocommit(false);
    
    // Excluir registros relacionados primeiro
    $conexao->query("DELETE FROM relatorios_mecanico WHERE relatorio_cliente_id = $relatorio_id");
    $conexao->query("DELETE FROM respostas_diagnostico WHERE diagnostico_id = $relatorio_id");
    
    // Excluir o relatório principal
    $stmt = $conexao->prepare("DELETE FROM relatorios_cliente WHERE id = ?");
    $stmt->bind_param("i", $relatorio_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $conexao->commit();
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Diagnóstico excluído com sucesso.'];
        } else {
            $conexao->rollback();
            $_SESSION['alerta'] = ['tipo' => 'warning', 'mensagem' => 'Diagnóstico não encontrado.'];
        }
    } else {
        $conexao->rollback();
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao excluir diagnóstico.'];
    }
    
} catch (Exception $e) {
    $conexao->rollback();
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao excluir diagnóstico: ' . $e->getMessage()];
}

$conexao->close();
header("Location: admin-relatorios.php");
exit;
?>