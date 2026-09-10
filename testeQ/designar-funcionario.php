<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = (int)$_POST['usuario_id'];
    $mecanico_id = (int)$_POST['mecanico_id'];
    
    $conexao = conectarBD();
    
    try {
        $stmt = $conexao->prepare("UPDATE usuarios SET mecanico_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $mecanico_id, $usuario_id);
        
        if ($stmt->execute()) {
            exibirAlerta('success', 'Usuário designado como funcionário com sucesso!');
        } else {
            exibirAlerta('danger', 'Erro ao designar funcionário: ' . $conexao->error);
        }
    } catch (Exception $e) {
        exibirAlerta('danger', 'Erro: ' . $e->getMessage());
    }
    
    $conexao->close();
    header("Location: admin-usuarios.php");
    exit;
}
?>