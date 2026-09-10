<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if ($_POST && isset($_POST['pagamento_id'])) {
    $pagamento_id = intval($_POST['pagamento_id']);
    $conexao = conectarBD();
    
    // Buscar dados do pagamento
    $stmt = $conexao->prepare("SELECT * FROM pagamentos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pagamento_id, $_SESSION['usuario_id']);
    $stmt->execute();
    $pagamento = $stmt->get_result()->fetch_assoc();
    
    if (!$pagamento) {
        echo json_encode(['success' => false, 'message' => 'Pagamento não encontrado']);
        exit;
    }
    
    // Buscar configurações PIX
    $config = $conexao->query("SELECT * FROM config_pagamentos WHERE id = 1")->fetch_assoc();
    
    // Gerar código PIX (simulado)
    $pix_code = "00020126580014br.gov.bcb.pix0136" . ($config['chave_pix'] ?? 'contato@autoservice.com') . "5204000053039865802BR5925Auto Service6009SAO PAULO62070503***6304";
    
    // Atualizar status para processando
    $stmt = $conexao->prepare("UPDATE pagamentos SET status = 'processando', metodo_pagamento = 'pix' WHERE id = ?");
    $stmt->bind_param("i", $pagamento_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'pix_code' => $pix_code,
        'valor' => $pagamento['valor'],
        'chave_pix' => $config['chave_pix'] ?? 'contato@autoservice.com'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}
?>