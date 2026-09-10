<?php
require_once 'config.php';
verificarLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: agendamento-diagnostico.php');
    exit;
}

$conexao = conectarBD();

// Validar dados obrigatórios
$veiculo_id = (int)$_POST['veiculo_id'];
$mecanico_id = (int)$_POST['mecanico_id'];
$descricao_problema = limparDados($_POST['descricao_problema']);
$urgencia = limparDados($_POST['urgencia']);
$data_agendamento = $_POST['data_agendamento'] ?? null;
$hora_agendamento = $_POST['hora_agendamento'] ?? null;

// Validações
if (empty($veiculo_id) || empty($mecanico_id)) {
    exibirAlerta('danger', 'Veículo e mecânico são obrigatórios.');
    header('Location: agendamento-diagnostico.php');
    exit;
}

// Verificar se o veículo pertence ao usuário
$stmt = $conexao->prepare("SELECT id FROM veiculos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $veiculo_id, $_SESSION['usuario_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    exibirAlerta('danger', 'Veículo não encontrado.');
    header('Location: agendamento-diagnostico.php');
    exit;
}

// Processar upload de fotos
$fotos_salvas = [];
if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
    $upload_dir = 'uploads/diagnosticos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $max_fotos = 3;
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    
    for ($i = 0; $i < min(count($_FILES['fotos']['name']), $max_fotos); $i++) {
        if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
            $file_type = $_FILES['fotos']['type'][$i];
            $file_size = $_FILES['fotos']['size'][$i];
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $extensao = pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid('diag_') . '.' . $extensao;
                $caminho_completo = $upload_dir . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $caminho_completo)) {
                    $fotos_salvas[] = $caminho_completo;
                }
            }
        }
    }
}

$fotos_json = !empty($fotos_salvas) ? json_encode($fotos_salvas) : null;

// Verificar se a tabela existe e se os campos existem
$has_agendamento_fields = false;
if (tabelaExiste($conexao, 'relatorios_cliente')) {
    $check_columns = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'data_agendamento'");
    $has_agendamento_fields = $check_columns && $check_columns->num_rows > 0;
} else {
    exibirAlerta('danger', 'Tabela relatorios_cliente não existe. Execute o script corrigir_erros_bd.php');
    header('Location: agendamento-diagnostico.php');
    exit;
}

// Validar se horário já está ocupado (SEMPRE, mesmo sem campos)
if ($data_agendamento && $hora_agendamento) {
    if ($has_agendamento_fields) {
        $check = $conexao->prepare("SELECT id FROM relatorios_cliente WHERE mecanico_id = ? AND data_agendamento = ? AND hora_agendamento = ? AND status != 'cancelado'");
        $check->bind_param("iss", $mecanico_id, $data_agendamento, $hora_agendamento);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $check->close();
            $conexao->close();
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => '❌ Este horário já está ocupado! Escolha outro horário.'];
            header('Location: agendamento-diagnostico.php');
            exit;
        }
        $check->close();
    }
}

try {
    
    if ($has_agendamento_fields) {
        // Criar relatório com campos de agendamento
        $stmt = $conexao->prepare("
            INSERT INTO relatorios_cliente (usuario_id, veiculo_id, mecanico_id, descricao_problema, urgencia, fotos, data_agendamento, hora_agendamento, data_envio) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiisssss", $_SESSION['usuario_id'], $veiculo_id, $mecanico_id, $descricao_problema, $urgencia, $fotos_json, $data_agendamento, $hora_agendamento);
    } else {
        // Criar relatório sem campos de agendamento
        $stmt = $conexao->prepare("
            INSERT INTO relatorios_cliente (usuario_id, veiculo_id, mecanico_id, descricao_problema, urgencia, fotos, data_envio) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiisss", $_SESSION['usuario_id'], $veiculo_id, $mecanico_id, $descricao_problema, $urgencia, $fotos_json);
    }
    $stmt->execute();
    $relatorio_id = $stmt->insert_id;
    
    // Se os campos não existem mas há data/hora, salvar em observações
    if (!$has_agendamento_fields && $data_agendamento && $hora_agendamento) {
        $obs_agendamento = "Agendamento solicitado: $data_agendamento às $hora_agendamento";
        $stmt_obs = $conexao->prepare("UPDATE relatorios_cliente SET descricao_problema = CONCAT(descricao_problema, '\n\n', ?) WHERE id = ?");
        $stmt_obs->bind_param("si", $obs_agendamento, $relatorio_id);
        $stmt_obs->execute();
        $stmt_obs->close();
    }
    
    // Registrar log
    $data_info = $data_agendamento ? " para $data_agendamento" : '';
    $hora_info = $hora_agendamento ? " às $hora_agendamento" : '';
    registrarLog('diagnostico_solicitado', "Diagnóstico solicitado para veículo ID: $veiculo_id$data_info$hora_info");
    
    exibirAlerta('success', 'Diagnóstico GRATUITO solicitado com sucesso! O mecânico entrará em contato em breve.');
    header('Location: relatorios.php');
    
} catch (Exception $e) {
    exibirAlerta('danger', 'Erro ao processar solicitação: ' . $e->getMessage());
    header('Location: agendamento-diagnostico.php');
}

$conexao->close();
?>