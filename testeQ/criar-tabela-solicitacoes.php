<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Criando Tabela de Solicitações de Mudança de Horário</h2>";

$sql = "CREATE TABLE IF NOT EXISTS solicitacoes_horario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analista_id INT NOT NULL,
    horario_inicio_atual TIME,
    horario_fim_atual TIME,
    dias_trabalho_atual VARCHAR(255),
    horario_inicio_solicitado TIME,
    horario_fim_solicitado TIME,
    dias_trabalho_solicitado VARCHAR(255),
    justificativa TEXT,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta TIMESTAMP NULL,
    observacoes_admin TEXT,
    FOREIGN KEY (analista_id) REFERENCES analistas(id) ON DELETE CASCADE
)";

if ($conexao->query($sql) === TRUE) {
    echo "<p>✓ Tabela 'solicitacoes_horario' criada com sucesso!</p>";
} else {
    echo "<p>❌ Erro ao criar tabela: " . $conexao->error . "</p>";
}

$conexao->close();
?>

<p><a href="analista-dashboard.php">← Voltar ao Dashboard</a></p>