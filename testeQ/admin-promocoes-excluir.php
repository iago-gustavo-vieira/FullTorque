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
    
    // Buscar a promoção para excluir a imagem
    $stmt = $conexao->prepare("SELECT imagem FROM promocoes_carousel WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($promo = $result->fetch_assoc()) {
        // Excluir imagem se existir
        if ($promo['imagem'] && file_exists('uploads/promocoes/' . $promo['imagem'])) {
            unlink('uploads/promocoes/' . $promo['imagem']);
        }
        
        // Excluir promoção do banco
        $stmt = $conexao->prepare("DELETE FROM promocoes_carousel WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Promoção excluída com sucesso!'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao excluir promoção!'];
        }
    }
    
    $conexao->close();
}

header("Location: admin-promocoes.php");
exit;
?>