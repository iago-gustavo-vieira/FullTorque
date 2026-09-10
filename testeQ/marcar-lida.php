<?php
require_once 'config.php';

// Verifica se o usuário está logado
verificarLogin();

// Verifica se o ID da notificação foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('danger', 'ID da notificação inválido.');
    header("Location: notificacoes.php");
    exit;
}

$notificacao_id = (int)$_GET['id'];
$origem = $_GET['origem'] ?? 'geral';
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

if ($origem == 'promocao') {
    // Criar tabela se não existir
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_lidas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        notificacao_promocao_id INT NOT NULL,
        data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_leitura (usuario_id, notificacao_promocao_id)
    )");
    
    // Verificar se a notificação de promoção existe
    $stmt = $conexao->prepare("SELECT id FROM notificacoes_promocoes WHERE id = ? AND enviado = 1");
    $stmt->bind_param("i", $notificacao_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        exibirAlerta('danger', 'Notificação não encontrada.');
        header("Location: notificacoes.php");
        exit;
    }
    
    // Marcar como lida
    $stmt = $conexao->prepare("INSERT IGNORE INTO notificacoes_lidas (usuario_id, notificacao_promocao_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $notificacao_id);
    
    if ($stmt->execute()) {
        exibirAlerta('success', 'Notificação marcada como lida.');
    } else {
        exibirAlerta('danger', 'Erro ao marcar a notificação como lida: ' . $conexao->error);
    }
} else {
    // Notificação geral
    // Verifica se a notificação pertence ao usuário
    $stmt = $conexao->prepare("SELECT id FROM notificacoes WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $notificacao_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        exibirAlerta('danger', 'Notificação não encontrada ou não pertence ao usuário.');
        header("Location: notificacoes.php");
        exit;
    }
    
    // Marca a notificação como lida
    $stmt = $conexao->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ?");
    $stmt->bind_param("i", $notificacao_id);
    
    if ($stmt->execute()) {
        exibirAlerta('success', 'Notificação marcada como lida.');
    } else {
        exibirAlerta('danger', 'Erro ao marcar a notificação como lida: ' . $conexao->error);
    }
}

$conexao->close();

// Redireciona para a página de notificações
header("Location: notificacoes.php");
exit;
?>