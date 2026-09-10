<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Criando Tabela de Configurações dos Analistas</h2>";

$sql = "CREATE TABLE IF NOT EXISTS analista_configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analista_id INT NOT NULL,
    notificacao_email TINYINT(1) DEFAULT 1,
    notificacao_sms TINYINT(1) DEFAULT 0,
    horario_inicio TIME DEFAULT '08:00:00',
    horario_fim TIME DEFAULT '18:00:00',
    dias_trabalho VARCHAR(255) DEFAULT 'segunda,terca,quarta,quinta,sexta',
    auto_aceitar TINYINT(1) DEFAULT 0,
    tempo_resposta INT DEFAULT 24,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (analista_id) REFERENCES analistas(id) ON DELETE CASCADE
)";

if ($conexao->query($sql) === TRUE) {
    echo "<p>✓ Tabela 'analista_configuracoes' criada com sucesso!</p>";
} else {
    echo "<p>❌ Erro ao criar tabela: " . $conexao->error . "</p>";
}

$conexao->close();
?>

<p><a href="analista-dashboard.php">← Voltar ao Dashboard</a></p>