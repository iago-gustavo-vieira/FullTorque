<?php
require_once 'config.php';

$conexao = conectarBD();

$sql = "CREATE TABLE IF NOT EXISTS cartoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    numero VARCHAR(19) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    validade VARCHAR(5) NOT NULL,
    tipo ENUM('credito', 'debito') NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";

if ($conexao->query($sql)) {
    echo "Tabela cartoes_usuario criada com sucesso!";
} else {
    echo "Erro: " . $conexao->error;
}
?>