<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: relatorios.php");
    exit;
}

$conexao = conectarBD();

$diagnostico_id = $_POST['diagnostico_id'];
$descricao_detalhada = $_POST['descricao_detalhada'];
$urgencia_orcamento = $_POST['urgencia_orcamento'];
$observacoes_orcamento = $_POST['observacoes_orcamento'] ?? '';

// Verificar se o diagnóstico pertence ao usuário
$stmt = $conexao->prepare("SELECT rd.mecanico_id FROM relatorios_cliente rc 
                          LEFT JOIN respostas_diagnostico rd ON rc.id = rd.diagnostico_id 
                          WHERE rc.id = ? AND rc.usuario_id = ?");
$stmt->bind_param("ii", $diagnostico_id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Diagnóstico não encontrado.'];
    header("Location: relatorios.php");
    exit;
}

$mecanico_id = $result->fetch_assoc()['mecanico_id'];

// Criar tabela de orçamentos se não existir
$conexao->query("CREATE TABLE IF NOT EXISTS solicitacoes_orcamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    diagnostico_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mecanico_id INT,
    descricao_detalhada TEXT NOT NULL,
    urgencia ENUM('normal', 'urgente', 'emergencia') DEFAULT 'normal',
    observacoes TEXT,
    fotos JSON,
    status ENUM('pendente', 'em_analise', 'respondido') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (diagnostico_id) REFERENCES relatorios_cliente(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
)");

// Processar upload de fotos
$fotos_salvas = [];
if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
    $upload_dir = 'uploads/orcamentos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        if (!empty($tmp_name)) {
            $file_name = $_FILES['fotos']['name'][$key];
            $file_size = $_FILES['fotos']['size'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validações
            if ($file_size > 5 * 1024 * 1024) continue; // 5MB max
            if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) continue;
            
            $new_name = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_name;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $fotos_salvas[] = $new_name;
            }
        }
    }
}

// Inserir solicitação de orçamento
$fotos_json = json_encode($fotos_salvas);
$stmt = $conexao->prepare("INSERT INTO solicitacoes_orcamento 
    (diagnostico_id, usuario_id, mecanico_id, descricao_detalhada, urgencia, observacoes, fotos) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiissss", $diagnostico_id, $_SESSION['usuario_id'], $mecanico_id, 
                  $descricao_detalhada, $urgencia_orcamento, $observacoes_orcamento, $fotos_json);

if ($stmt->execute()) {
    // Criar notificação para o mecânico
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        mensagem TEXT NOT NULL,
        tipo ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
        lida TINYINT(1) DEFAULT 0,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");
    
    if ($mecanico_id) {
        // Buscar usuario_id do mecânico
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE mecanico_id = ?");
        $stmt->bind_param("i", $mecanico_id);
        $stmt->execute();
        $mecanico_usuario = $stmt->get_result()->fetch_assoc();
        
        if ($mecanico_usuario) {
            $titulo_notificacao = "Nova Solicitação de Orçamento";
            $mensagem_notificacao = "Você recebeu uma nova solicitação de orçamento detalhado com " . count($fotos_salvas) . " foto(s).";
            
            $stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo) VALUES (?, ?, ?, 'info')");
            $stmt->bind_param("iss", $mecanico_usuario['id'], $titulo_notificacao, $mensagem_notificacao);
            $stmt->execute();
        }
    }
    
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Solicitação de orçamento enviada com sucesso! O mecânico foi notificado.'];
} else {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao enviar solicitação. Tente novamente.'];
}

$conexao->close();
header("Location: relatorios.php");
exit;
?>