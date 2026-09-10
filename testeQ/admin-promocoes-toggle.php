<?php
require_once 'config.php';
verificarLogin();

// Verificar se é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $conexao = conectarBD();
    $id = (int)$_GET['id'];
    
    // Buscar status atual
    $stmt = $conexao->prepare("SELECT ativo FROM promocoes_carousel WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($promo = $result->fetch_assoc()) {
        // Inverter status
        $novo_status = $promo['ativo'] ? 0 : 1;
        
        $stmt = $conexao->prepare("UPDATE promocoes_carousel SET ativo = ? WHERE id = ?");
        $stmt->bind_param("ii", $novo_status, $id);
        
        if ($stmt->execute()) {
            $status_texto = $novo_status ? 'ativada' : 'desativada';
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => "Promoção $status_texto com sucesso!"];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao alterar status da promoção!'];
        }
    }
    
    $conexao->close();
}

header("Location: admin-promocoes.php");
exit;
?>