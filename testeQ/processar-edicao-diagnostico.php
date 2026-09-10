<?php
require_once 'config.php';
verificarLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: relatorios.php");
    exit;
}

$relatorio_id = (int)$_POST['relatorio_id'];
$veiculo_id = (int)$_POST['veiculo_id'];
$mecanico_id = (int)$_POST['mecanico_id'];
$descricao_problema = trim($_POST['descricao_problema']);
$urgencia = $_POST['urgencia'];
$usuario_id = $_SESSION['usuario_id'];

$conexao = conectarBD();

// Verificar se o relatório pertence ao usuário e está pendente
$stmt = $conexao->prepare("SELECT status FROM relatorios_cliente WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $relatorio_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Diagnóstico não encontrado.'];
    header("Location: relatorios.php");
    exit;
}

$relatorio = $result->fetch_assoc();
if ($relatorio['status'] !== 'pendente') {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensagem' => 'Este diagnóstico não pode mais ser editado.'];
    header("Location: relatorios.php");
    exit;
}

// Atualizar o diagnóstico
$stmt = $conexao->prepare("UPDATE relatorios_cliente SET 
    veiculo_id = ?, 
    mecanico_id = ?, 
    descricao_problema = ?, 
    urgencia = ?
    WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("iissii", $veiculo_id, $mecanico_id, $descricao_problema, $urgencia, $relatorio_id, $usuario_id);

if ($stmt->execute()) {
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Diagnóstico atualizado com sucesso!'];
} else {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao atualizar diagnóstico.'];
}

$conexao->close();
header("Location: relatorios.php");
exit;
?>