<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado']);
    exit;
}

$cartao_id = isset($_POST['cartao_id']) ? intval($_POST['cartao_id']) : 0;
$senha = isset($_POST['senha']) ? $_POST['senha'] : '';

if (!$cartao_id || !$senha) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT senha FROM cartoes_usuario WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $cartao_id, $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Cartão não encontrado']);
    exit;
}

$cartao = $resultado->fetch_assoc();

if ($cartao['senha'] === null || $cartao['senha'] === '') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Cartão sem senha cadastrada']);
    exit;
}

if ($cartao['senha'] === $senha) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Senha correta']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Senha incorreta']);
}
?>
