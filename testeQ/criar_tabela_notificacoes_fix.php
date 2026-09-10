<?php
require_once 'config.php';

$conexao = conectarBD();

// Criar tabela notificacoes se não existir
$sql = "CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo VARCHAR(50) DEFAULT 'geral',
    link TEXT NULL,
    lida TINYINT(1) DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";

if ($conexao->query($sql)) {
    echo "Tabela notificacoes criada/verificada com sucesso!";
} else {
    echo "Erro: " . $conexao->error;
}
?>