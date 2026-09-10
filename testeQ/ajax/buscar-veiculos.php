<?php
require_once '../config.php';
verificarLogin();

header('Content-Type: application/json');

if (!isset($_GET['cliente_id']) || !is_numeric($_GET['cliente_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do cliente inválido']);
    exit;
}

$cliente_id = (int)$_GET['cliente_id'];

try {
    $conexao = conectarBD();
    $stmt = $conexao->prepare("SELECT id, marca, modelo, placa FROM veiculos WHERE usuario_id = ? ORDER BY marca, modelo");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $veiculos = [];
    while ($veiculo = $result->fetch_assoc()) {
        $veiculos[] = $veiculo;
    }
    
    $conexao->close();
    
    echo json_encode(['success' => true, 'veiculos' => $veiculos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar veículos']);
}
?>