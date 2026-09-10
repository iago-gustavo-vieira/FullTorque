<?php
require_once 'config.php';

// Script para limpar notificações antigas (executar via cron ou manualmente)

$conexao = conectarBD();

// Limpar notificações de promoções antigas (mais de 30 dias)
$result = $conexao->query("DELETE FROM notificacoes_promocoes WHERE data_criacao < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$promocoes_removidas = $conexao->affected_rows;

// Limpar registros de leitura de notificações que não existem mais
$conexao->query("DELETE nl FROM notificacoes_lidas nl 
                LEFT JOIN notificacoes_promocoes np ON nl.notificacao_promocao_id = np.id 
                WHERE np.id IS NULL");
$leituras_removidas = $conexao->affected_rows;

// Limpar notificações gerais antigas (mais de 60 dias)
$tabela_notificacoes = $conexao->query("SHOW TABLES LIKE 'notificacoes'")->num_rows > 0;
$gerais_removidas = 0;

if ($tabela_notificacoes) {
    $result = $conexao->query("DELETE FROM notificacoes WHERE data_criacao < DATE_SUB(NOW(), INTERVAL 60 DAY)");
    $gerais_removidas = $conexao->affected_rows;
}

echo "Limpeza de notificações concluída:\n";
echo "- Notificações de promoções removidas: $promocoes_removidas\n";
echo "- Registros de leitura órfãos removidos: $leituras_removidas\n";
echo "- Notificações gerais removidas: $gerais_removidas\n";

$conexao->close();
?>