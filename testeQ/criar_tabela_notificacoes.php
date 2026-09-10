<?php
require_once 'config.php';
$conexao = conectarBD();

$sql = "CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'sistema',
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    lida TINYINT(1) DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Definir charset para suportar emojis
$conexao->set_charset("utf8mb4");

if ($conexao->query($sql)) {
    echo "Tabela 'notificacoes' criada com sucesso com suporte a emojis!";
} else {
    echo "Erro: " . $conexao->error;
}
?>