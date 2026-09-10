<?php
require_once 'config.php';

// Verificar se o usuário está logado e é um mecânico
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    $diagnostico_id = $_POST['diagnostico_id'];
    $diagnostico_inicial = $_POST['diagnostico_inicial'];
    $pecas_necessarias = $_POST['pecas_necessarias'] ?? '';
    $tempo_estimado = $_POST['tempo_estimado'] ?? '';
    $custo_estimado = $_POST['custo_estimado'] ?? '';
    $prioridade = $_POST['prioridade'];
    $observacoes = $_POST['observacoes'] ?? '';
    $mecanico_id = $_SESSION['mecanico_id'];
    
    // Verificar se o diagnóstico pertence ao mecânico
    $stmt = $conexao->prepare("SELECT usuario_id FROM relatorios_cliente WHERE id = ? AND mecanico_id = ?");
    $stmt->bind_param("ii", $diagnostico_id, $mecanico_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro: Diagnóstico não encontrado ou não autorizado.'];
        header("Location: mecanico-diagnosticos.php");
        exit;
    }
    
    $cliente_id = $result->fetch_assoc()['usuario_id'];
    
    // Criar tabela de respostas se não existir
    $conexao->query("CREATE TABLE IF NOT EXISTS respostas_diagnostico (
        id INT PRIMARY KEY AUTO_INCREMENT,
        diagnostico_id INT NOT NULL,
        mecanico_id INT NOT NULL,
        diagnostico_inicial TEXT NOT NULL,
        pecas_necessarias TEXT,
        tempo_estimado VARCHAR(50),
        custo_estimado VARCHAR(100),
        prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
        observacoes TEXT,
        data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (diagnostico_id) REFERENCES relatorios_cliente(id),
        FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id)
    )");
    
    // Inserir resposta
    $stmt = $conexao->prepare("INSERT INTO respostas_diagnostico 
        (diagnostico_id, mecanico_id, diagnostico_inicial, pecas_necessarias, tempo_estimado, custo_estimado, prioridade, observacoes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $diagnostico_id, $mecanico_id, $diagnostico_inicial, $pecas_necessarias, 
                      $tempo_estimado, $custo_estimado, $prioridade, $observacoes);
    
    if ($stmt->execute()) {
        // Verificar e atualizar coluna status se necessário
        $conexao->query("ALTER TABLE relatorios_cliente MODIFY COLUMN status ENUM('pendente', 'analisado', 'respondido', 'aceito', 'rejeitado', 'realizado', 'cancelado', 'solicitou_mudanca') DEFAULT 'pendente'");
        
        // Atualizar status do diagnóstico
        $stmt = $conexao->prepare("UPDATE relatorios_cliente SET status = 'respondido' WHERE id = ?");
        $stmt->bind_param("i", $diagnostico_id);
        $stmt->execute();
        
        // Criar notificação para o cliente
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
        
        $titulo_notificacao = "Diagnóstico Respondido";
        $mensagem_notificacao = "Seu diagnóstico foi analisado pelo mecânico. Acesse 'Meus Diagnósticos' para ver a resposta completa.";
        
        $stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo) VALUES (?, ?, ?, 'success')");
        $stmt->bind_param("iss", $cliente_id, $titulo_notificacao, $mensagem_notificacao);
        $stmt->execute();
        
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Resposta enviada com sucesso! O cliente foi notificado.'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao enviar resposta. Tente novamente.'];
    }
    
    $conexao->close();
}

header("Location: mecanico-diagnosticos.php");
exit;
?>