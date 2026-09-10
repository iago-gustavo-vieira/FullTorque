<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_id'])) {
    $conexao = conectarBD();
    
    $stmt = $conexao->prepare("UPDATE usuarios SET primeiro_acesso = 0 WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $conexao->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}
?>