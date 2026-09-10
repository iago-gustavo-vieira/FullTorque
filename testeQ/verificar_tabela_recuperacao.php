<?php
require_once 'config.php';

$conexao = conectarBD();

// Verifica se a tabela existe
$result = $conexao->query("SHOW TABLES LIKE 'recuperacao_senha'");

if ($result->num_rows == 0) {
    // Cria a tabela se não existir
    $sql = "CREATE TABLE IF NOT EXISTS `recuperacao_senha` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usuario_id` int(11) NOT NULL,
      `token` varchar(64) NOT NULL,
      `expira` datetime NOT NULL,
      `usado` tinyint(1) DEFAULT 0,
      `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `usuario_id` (`usuario_id`),
      KEY `token` (`token`),
      CONSTRAINT `fk_recuperacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conexao->query($sql)) {
        echo "Tabela 'recuperacao_senha' criada com sucesso!";
    } else {
        echo "Erro ao criar tabela: " . $conexao->error;
    }
} else {
    echo "Tabela 'recuperacao_senha' já existe!";
}

$conexao->close();
?>
