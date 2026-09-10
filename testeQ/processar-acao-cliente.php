<?php
require_once 'config.php';
verificarLogin();

if (!isset($_GET['acao']) || !isset($_GET['id'])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Parâmetros inválidos.'];
    header("Location: relatorios.php");
    exit;
}

$acao = $_GET['acao'];
$relatorio_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$conexao = conectarBD();

// Verificar e corrigir estrutura da tabela
$result = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'status'");
if ($result && $result->num_rows > 0) {
    $column = $result->fetch_assoc();
    if (strpos($column['Type'], 'cancelado') === false) {
        $conexao->query("ALTER TABLE relatorios_cliente MODIFY COLUMN status ENUM('pendente', 'analisado', 'respondido', 'aceito', 'rejeitado', 'realizado', 'cancelado', 'solicitou_mudanca') DEFAULT 'pendente'");
    }
} else {
    // Se a coluna não existe, criar com os valores corretos
    $conexao->query("ALTER TABLE relatorios_cliente ADD COLUMN status ENUM('pendente', 'analisado', 'respondido', 'aceito', 'rejeitado', 'realizado', 'cancelado', 'solicitou_mudanca') DEFAULT 'pendente'");
}

// Verificar se o relatório pertence ao usuário
$stmt = $conexao->prepare("SELECT id, status FROM relatorios_cliente WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $relatorio_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Relatório não encontrado.'];
    header("Location: relatorios.php");
    exit;
}

$relatorio = $result->fetch_assoc();

switch ($acao) {
    case 'excluir':
        header('Content-Type: application/json');
        $stmt = $conexao->prepare("DELETE FROM relatorios_cliente WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Diagnóstico excluído com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir diagnóstico']);
        }
        $conexao->close();
        exit;
        
    case 'cancelar':
        // Debug: verificar se o registro existe
        $debug_stmt = $conexao->prepare("SELECT id, status, usuario_id FROM relatorios_cliente WHERE id = ?");
        $debug_stmt->bind_param("i", $relatorio_id);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();
        
        if ($debug_result->num_rows === 0) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'DEBUG: Registro não encontrado na tabela. ID: ' . $relatorio_id];
            break;
        }
        
        $debug_row = $debug_result->fetch_assoc();
        if ($debug_row['usuario_id'] != $usuario_id) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'DEBUG: Usuário não autorizado. Esperado: ' . $usuario_id . ', Encontrado: ' . $debug_row['usuario_id']];
            break;
        }
        
        // Atualizar status para cancelado
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'cancelado' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute()) {
            $linhas_afetadas = $stmt->affected_rows;
            if ($linhas_afetadas > 0) {
                $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Diagnóstico cancelado com sucesso.'];
            } else {
                $_SESSION['alerta'] = ['tipo' => 'warning', 'mensagem' => 'DEBUG: Nenhuma linha afetada. Status atual: ' . $debug_row['status'] . ', ID: ' . $relatorio_id . ', User: ' . $usuario_id];
            }
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro SQL: ' . $stmt->error . ' | MySQL Error: ' . $conexao->error];
        }
        break;
        
    case 'aceitar':
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'aceito' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Serviço aceito! O analista entrará em contato para agendamento.'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao aceitar serviço.'];
        }
        break;
        
    case 'rejeitar':
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'rejeitado' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'info', 'mensagem' => 'Serviço recusado. Obrigado pelo seu tempo.'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao recusar serviço.'];
        }
        break;
        
    case 'realizado':
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'realizado' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Serviço marcado como realizado!'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao marcar serviço como realizado.'];
        }
        break;
        
    case 'solicitou_mudanca':
        $nova_data = $_GET['nova_data'] ?? '';
        $observacoes = $_GET['observacoes'] ?? '';
        
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'solicitou_mudanca' WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $relatorio_id, $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'info', 'mensagem' => 'Solicitação de mudança de horário enviada.'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao solicitar mudança de horário.'];
        }
        break;
        
    default:
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Ação inválida.'];
}

$conexao->close();
header("Location: relatorios.php");
exit;
?>