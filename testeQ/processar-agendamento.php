<?php
require_once 'config.php';
verificarLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $veiculo_id = $_POST['veiculo_id'];
    $mecanico_id = $_POST['mecanico_id'];
    $data_agendamento = $_POST['data_agendamento'];
    $hora_inicio = $_POST['hora_agendamento'];
    $observacoes = $_POST['observacoes'] ?? '';
    
    // Calcular hora_fim (1 hora após hora_inicio)
    $hora_fim = date('H:i', strtotime($hora_inicio . ' +1 hour'));
    
    $conexao = conectarBD();
    
    // Verificar se já existe agendamento com mesmo profissional, data e hora
    $check = $conexao->prepare("SELECT id FROM agendamentos WHERE mecanico_id = ? AND data_agendamento = ? AND hora_inicio = ? AND status != 'cancelado'");
    $check->bind_param("iss", $mecanico_id, $data_agendamento, $hora_inicio);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => '❌ Este horário já está ocupado com este profissional. Escolha outro horário.'
        ];
        $check->close();
        $conexao->close();
        header("Location: agendamento-novo.php");
        exit;
    }
    $check->close();
    
    // Montar INSERT dinâmico verificando colunas existentes
    $campos = ['usuario_id', 'veiculo_id', 'mecanico_id', 'data_agendamento', 'hora_inicio', 'hora_fim', 'observacoes', 'status'];
    $valores = [$usuario_id, $veiculo_id, $mecanico_id, $data_agendamento, $hora_inicio, $hora_fim, $observacoes, 'agendado'];
    $tipos = 'iiisssss';
    $placeholders = '?, ?, ?, ?, ?, ?, ?, ?';
    
    if (colunaExiste($conexao, 'agendamentos', 'data_criacao')) {
        $campos[] = 'data_criacao';
        $placeholders .= ', NOW()';
    }
    
    $sql = "INSERT INTO agendamentos (" . implode(', ', $campos) . ") VALUES ($placeholders)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param($tipos, ...$valores);
    
    if ($stmt->execute()) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => '✅ Agendamento realizado com sucesso!'
        ];
        header("Location: agendamentos.php");
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'error',
            'mensagem' => '❌ Erro ao realizar agendamento. Tente novamente.'
        ];
        header("Location: agendamento-novo.php");
    }
    
    $conexao->close();
    exit;
}
?>
