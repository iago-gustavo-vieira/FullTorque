<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

$conexao = conectarBD();
$promocao_id = intval($_GET['id'] ?? 0);

if (!$promocao_id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Buscar dados da promoção
$promocao = $conexao->query("SELECT * FROM promocoes WHERE id = $promocao_id")->fetch_assoc();

if (!$promocao) {
    echo json_encode(['error' => 'Promoção não encontrada']);
    exit;
}

// Calcular estatísticas
$stats = [];

// Total de usos
$stats['total_usos'] = $conexao->query("SELECT COUNT(*) as total FROM promocoes_uso WHERE promocao_id = $promocao_id")->fetch_assoc()['total'] ?? 0;

// Clientes únicos
$stats['clientes_unicos'] = $conexao->query("SELECT COUNT(DISTINCT usuario_id) as total FROM promocoes_uso WHERE promocao_id = $promocao_id")->fetch_assoc()['total'] ?? 0;

// Valor economizado pelos clientes
$valor_economizado = 0;
if ($promocao['tipo'] == 'desconto_percentual') {
    $result = $conexao->query("SELECT SUM(valor_servico * {$promocao['valor']} / 100) as total FROM promocoes_uso WHERE promocao_id = $promocao_id");
    $valor_economizado = $result->fetch_assoc()['total'] ?? 0;
} else {
    $valor_economizado = $stats['total_usos'] * $promocao['valor'];
}
$stats['valor_economizado'] = number_format($valor_economizado, 2, ',', '.');

// Taxa de conversão (simulada - baseada em visualizações vs usos)
$visualizacoes = $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE titulo LIKE '%{$promocao['titulo']}%'")->fetch_assoc()['total'] ?? 1;
$stats['taxa_conversao'] = $visualizacoes > 0 ? round(($stats['total_usos'] / $visualizacoes) * 100, 1) : 0;

// Detalhes da promoção
$stats['data_inicio'] = date('d/m/Y', strtotime($promocao['data_inicio']));
$stats['data_fim'] = date('d/m/Y', strtotime($promocao['data_fim']));
$stats['codigo_cupom'] = $promocao['codigo_cupom'] ?: 'Sem cupom';
$stats['limite_uso'] = $promocao['limite_uso'] ?: 'Ilimitado';

// Tipo formatado
$tipos = [
    'desconto_percentual' => 'Desconto Percentual',
    'desconto_fixo' => 'Desconto Fixo',
    'servico_gratis' => 'Serviço Grátis',
    'cupom_primeira_revisao' => 'Primeira Revisão'
];
$stats['tipo'] = $tipos[$promocao['tipo']] ?? 'Outro';

// Valor formatado
if ($promocao['tipo'] == 'desconto_percentual') {
    $stats['valor'] = $promocao['valor'] . '%';
} else {
    $stats['valor'] = 'R$ ' . number_format($promocao['valor'], 2, ',', '.');
}

echo json_encode($stats);
?>