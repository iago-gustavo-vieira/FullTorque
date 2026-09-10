<?php
// Funções para o sistema de promoções e fidelização

// Adicionar pontos ao cliente após um serviço
function adicionarPontos($usuario_id, $valor_servico, $agendamento_id = null) {
    $conexao = conectarBD();
    
    // Buscar configuração de pontos
    $config = $conexao->query("SELECT * FROM programa_pontos WHERE id = 1")->fetch_assoc();
    $pontos_ganhos = floor($valor_servico * $config['pontos_por_real']);
    
    if ($pontos_ganhos > 0) {
        // Verificar se cliente já tem registro de pontos
        $cliente_pontos = $conexao->query("SELECT * FROM cliente_pontos WHERE usuario_id = $usuario_id")->fetch_assoc();
        
        if (!$cliente_pontos) {
            // Criar registro se não existir
            $conexao->query("INSERT INTO cliente_pontos (usuario_id, pontos_totais, pontos_disponiveis) VALUES ($usuario_id, $pontos_ganhos, $pontos_ganhos)");
        } else {
            // Atualizar pontos existentes
            $conexao->query("UPDATE cliente_pontos SET pontos_totais = pontos_totais + $pontos_ganhos, pontos_disponiveis = pontos_disponiveis + $pontos_ganhos WHERE usuario_id = $usuario_id");
        }
        
        // Registrar no histórico
        $descricao = "Pontos ganhos por serviço - R$ " . number_format($valor_servico, 2, ',', '.');
        $conexao->query("INSERT INTO historico_pontos (usuario_id, agendamento_id, tipo, pontos, descricao) VALUES ($usuario_id, $agendamento_id, 'ganho', $pontos_ganhos, '$descricao')");
        
        return $pontos_ganhos;
    }
    
    return 0;
}

// Aplicar promoção a um agendamento
function aplicarPromocao($promocao_id, $usuario_id, $valor_original, $agendamento_id = null) {
    $conexao = conectarBD();
    
    // Buscar promoção
    $promocao = $conexao->query("SELECT * FROM promocoes WHERE id = $promocao_id AND ativo = 1")->fetch_assoc();
    
    if (!$promocao) {
        return ['sucesso' => false, 'erro' => 'Promoção não encontrada ou inativa'];
    }
    
    // Verificar se promoção está válida
    if (date('Y-m-d') < $promocao['data_inicio'] || date('Y-m-d') > $promocao['data_fim']) {
        return ['sucesso' => false, 'erro' => 'Promoção fora do período válido'];
    }
    
    // Verificar limite de uso
    if ($promocao['limite_uso'] && $promocao['usos_atual'] >= $promocao['limite_uso']) {
        return ['sucesso' => false, 'erro' => 'Limite de uso da promoção atingido'];
    }
    
    // Calcular desconto
    $valor_desconto = 0;
    switch ($promocao['tipo']) {
        case 'desconto_percentual':
            $valor_desconto = ($valor_original * $promocao['valor']) / 100;
            break;
        case 'desconto_fixo':
            $valor_desconto = $promocao['valor'];
            break;
        case 'servico_gratis':
            $valor_desconto = $valor_original;
            break;
    }
    
    // Não permitir desconto maior que o valor original
    $valor_desconto = min($valor_desconto, $valor_original);
    
    // Registrar uso da promoção
    $conexao->query("INSERT INTO promocoes_uso (promocao_id, usuario_id, agendamento_id, valor_desconto) VALUES ($promocao_id, $usuario_id, $agendamento_id, $valor_desconto)");
    
    // Atualizar contador de usos
    $conexao->query("UPDATE promocoes SET usos_atual = usos_atual + 1 WHERE id = $promocao_id");
    
    return [
        'sucesso' => true,
        'valor_desconto' => $valor_desconto,
        'valor_final' => $valor_original - $valor_desconto,
        'promocao' => $promocao
    ];
}

// Verificar se cliente pode usar cupom de primeira revisão
function podeUsarCupomPrimeiraRevisao($usuario_id) {
    $conexao = conectarBD();
    
    // Verificar se cliente já fez algum agendamento
    $agendamentos = $conexao->query("SELECT COUNT(*) as total FROM agendamentos WHERE usuario_id = $usuario_id")->fetch_assoc();
    
    return $agendamentos['total'] == 0;
}

// Enviar notificação de promoção
function enviarNotificacaoPromocao($promocao_id, $usuario_id = null, $tipo = 'email') {
    $conexao = conectarBD();
    
    // Buscar promoção
    $promocao = $conexao->query("SELECT * FROM promocoes WHERE id = $promocao_id")->fetch_assoc();
    
    if (!$promocao) return false;
    
    $titulo = "Nova Promoção: " . $promocao['titulo'];
    $mensagem = $promocao['descricao'];
    
    if ($promocao['codigo_cupom']) {
        $mensagem .= "\n\nUse o cupom: " . $promocao['codigo_cupom'];
    }
    
    // Se usuario_id for null, enviar para todos os clientes
    if ($usuario_id === null) {
        $usuarios = $conexao->query("SELECT id FROM usuarios WHERE tipo = 'cliente'");
        while ($usuario = $usuarios->fetch_assoc()) {
            $conexao->query("INSERT INTO notificacoes_promocoes (promocao_id, usuario_id, tipo, titulo, mensagem) VALUES ($promocao_id, {$usuario['id']}, '$tipo', '$titulo', '$mensagem')");
        }
    } else {
        $conexao->query("INSERT INTO notificacoes_promocoes (promocao_id, usuario_id, tipo, titulo, mensagem) VALUES ($promocao_id, $usuario_id, '$tipo', '$titulo', '$mensagem')");
    }
    
    return true;
}

// Processar notificações pendentes (para ser executado via cron ou manualmente)
function processarNotificacoesPendentes() {
    $conexao = conectarBD();
    
    $notificacoes = $conexao->query("
        SELECT n.*, u.email, u.telefone, u.nome 
        FROM notificacoes_promocoes n 
        JOIN usuarios u ON n.usuario_id = u.id 
        WHERE n.enviado = 0 
        LIMIT 50
    ");
    
    $enviadas = 0;
    
    while ($notificacao = $notificacoes->fetch_assoc()) {
        $sucesso = false;
        
        if ($notificacao['tipo'] == 'email' && $notificacao['email']) {
            // Aqui você integraria com seu sistema de email
            // $sucesso = enviarEmail($notificacao['email'], $notificacao['titulo'], $notificacao['mensagem']);
            $sucesso = true; // Simular envio por enquanto
        } elseif ($notificacao['tipo'] == 'whatsapp' && $notificacao['telefone']) {
            // Aqui você integraria com API do WhatsApp
            // $sucesso = enviarWhatsApp($notificacao['telefone'], $notificacao['mensagem']);
            $sucesso = true; // Simular envio por enquanto
        }
        
        if ($sucesso) {
            $conexao->query("UPDATE notificacoes_promocoes SET enviado = 1, data_envio = NOW() WHERE id = {$notificacao['id']}");
            $enviadas++;
        }
    }
    
    return $enviadas;
}

// Buscar promoções aplicáveis para um cliente
function buscarPromocoesAplicaveis($usuario_id, $valor_servico = null) {
    $conexao = conectarBD();
    
    $promocoes = $conexao->query("
        SELECT * FROM promocoes 
        WHERE ativo = 1 
        AND data_inicio <= CURDATE() 
        AND data_fim >= CURDATE()
        AND (limite_uso IS NULL OR usos_atual < limite_uso)
        ORDER BY valor DESC
    ");
    
    $aplicaveis = [];
    
    while ($promocao = $promocoes->fetch_assoc()) {
        // Verificar se é cupom de primeira revisão
        if ($promocao['tipo'] == 'cupom_primeira_revisao') {
            if (!podeUsarCupomPrimeiraRevisao($usuario_id)) {
                continue;
            }
        }
        
        $aplicaveis[] = $promocao;
    }
    
    return $aplicaveis;
}

// Calcular valor com desconto de pontos
function calcularDescontoPontos($pontos_utilizados, $valor_original) {
    $conexao = conectarBD();
    $config = $conexao->query("SELECT * FROM programa_pontos WHERE id = 1")->fetch_assoc();
    
    $valor_desconto = $pontos_utilizados * $config['valor_ponto'];
    $valor_desconto = min($valor_desconto, $valor_original); // Não pode ser maior que o valor original
    
    return [
        'valor_desconto' => $valor_desconto,
        'valor_final' => $valor_original - $valor_desconto
    ];
}
?>