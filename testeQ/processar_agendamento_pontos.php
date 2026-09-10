<?php
// Arquivo para processar pontos após conclusão de agendamento
require_once 'config.php';
require_once 'funcoes_promocoes.php';

// Este arquivo deve ser incluído quando um agendamento for marcado como concluído

function processarAgendamentoConcluido($agendamento_id) {
    $conexao = conectarBD();
    
    // Buscar dados do agendamento
    $stmt = $conexao->prepare("
        SELECT a.*, s.preco, s.nome as servico_nome 
        FROM agendamentos a 
        JOIN servicos s ON a.servico_id = s.id 
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $agendamento_id);
    $stmt->execute();
    $agendamento = $stmt->get_result()->fetch_assoc();
    
    if (!$agendamento) {
        return false;
    }
    
    $usuario_id = $agendamento['usuario_id'];
    $valor_servico = $agendamento['preco'];
    
    // Adicionar pontos ao cliente
    $pontos_ganhos = adicionarPontos($usuario_id, $valor_servico, $agendamento_id);
    
    // Registrar log
    registrarLog('pontos_adicionados', "Adicionados $pontos_ganhos pontos para agendamento #$agendamento_id", $usuario_id);
    
    // Verificar se é primeira revisão para cupom especial
    if (podeUsarCupomPrimeiraRevisao($usuario_id)) {
        // Criar notificação sobre cupom de primeira revisão
        $conexao->query("
            INSERT INTO notificacoes_promocoes (promocao_id, usuario_id, tipo, titulo, mensagem) 
            SELECT id, $usuario_id, 'sistema', 'Cupom de Primeira Revisão Disponível!', 
                   'Parabéns! Você tem direito ao nosso cupom especial de primeira revisão com 20% de desconto!' 
            FROM promocoes 
            WHERE tipo = 'cupom_primeira_revisao' AND ativo = 1 
            LIMIT 1
        ");
    }
    
    return $pontos_ganhos;
}

// Função para aplicar desconto de pontos em um agendamento
function aplicarDescontoPontos($agendamento_id, $pontos_utilizados) {
    $conexao = conectarBD();
    
    // Buscar agendamento
    $stmt = $conexao->prepare("SELECT * FROM agendamentos WHERE id = ?");
    $stmt->bind_param("i", $agendamento_id);
    $stmt->execute();
    $agendamento = $stmt->get_result()->fetch_assoc();
    
    if (!$agendamento) {
        return false;
    }
    
    $usuario_id = $agendamento['usuario_id'];
    
    // Verificar se cliente tem pontos suficientes
    $pontos_cliente = $conexao->query("SELECT * FROM cliente_pontos WHERE usuario_id = $usuario_id")->fetch_assoc();
    
    if (!$pontos_cliente || $pontos_cliente['pontos_disponiveis'] < $pontos_utilizados) {
        return false;
    }
    
    // Calcular desconto
    $desconto = calcularDescontoPontos($pontos_utilizados, $agendamento['valor_total']);
    
    // Atualizar agendamento com desconto
    $novo_valor = $desconto['valor_final'];
    $conexao->query("UPDATE agendamentos SET valor_total = $novo_valor, desconto_pontos = {$desconto['valor_desconto']} WHERE id = $agendamento_id");
    
    // Debitar pontos do cliente
    $conexao->query("UPDATE cliente_pontos SET pontos_disponiveis = pontos_disponiveis - $pontos_utilizados, pontos_utilizados = pontos_utilizados + $pontos_utilizados WHERE usuario_id = $usuario_id");
    
    // Registrar no histórico
    $conexao->query("INSERT INTO historico_pontos (usuario_id, agendamento_id, tipo, pontos, descricao) VALUES ($usuario_id, $agendamento_id, 'resgate', -$pontos_utilizados, 'Desconto aplicado no agendamento #$agendamento_id - R$ " . number_format($desconto['valor_desconto'], 2, ',', '.') . "')");
    
    return $desconto;
}

// Exemplo de uso - chamar quando agendamento for concluído
if (isset($_POST['concluir_agendamento'])) {
    $agendamento_id = intval($_POST['agendamento_id']);
    $pontos_ganhos = processarAgendamentoConcluido($agendamento_id);
    
    if ($pontos_ganhos > 0) {
        exibirAlerta('success', "Agendamento concluído! Você ganhou $pontos_ganhos pontos de fidelidade!");
    }
}
?>