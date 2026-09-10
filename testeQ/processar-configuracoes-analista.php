<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    $notificacao_email = isset($_POST['notificacao_email']) ? 1 : 0;
    $notificacao_sms = isset($_POST['notificacao_sms']) ? 1 : 0;
    $auto_aceitar = isset($_POST['auto_aceitar']) ? 1 : 0;
    $tempo_resposta = (int)$_POST['tempo_resposta'];
    
    try {
        // Verificar se já existe configuração
        $stmt_check = $conexao->prepare("SELECT id FROM analista_configuracoes WHERE analista_id = ?");
        $stmt_check->bind_param("i", $_SESSION['analista_id']);
        $stmt_check->execute();
        $existe = $stmt_check->get_result()->num_rows > 0;
        
        if ($existe) {
            // Atualizar apenas configurações permitidas
            $stmt = $conexao->prepare("UPDATE analista_configuracoes SET 
                notificacao_email = ?, notificacao_sms = ?, auto_aceitar = ?, tempo_resposta = ?
                WHERE analista_id = ?");
            $stmt->bind_param("iiiii", $notificacao_email, $notificacao_sms, $auto_aceitar, $tempo_resposta, $_SESSION['analista_id']);
        } else {
            // Criar configurações com valores padrão para horários
            $stmt = $conexao->prepare("INSERT INTO analista_configuracoes 
                (analista_id, notificacao_email, notificacao_sms, auto_aceitar, tempo_resposta) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiii", $_SESSION['analista_id'], $notificacao_email, $notificacao_sms, $auto_aceitar, $tempo_resposta);
        }
        
        $stmt->execute();
        
        registrarLog('configuracoes_atualizadas', "Analista atualizou configurações");
        exibirAlerta('success', 'Configurações salvas com sucesso!');
        
    } catch (Exception $e) {
        exibirAlerta('danger', 'Erro ao salvar configurações: ' . $e->getMessage());
    }
    
    $conexao->close();
}

header("Location: analista-configuracoes.php");
exit;
?>