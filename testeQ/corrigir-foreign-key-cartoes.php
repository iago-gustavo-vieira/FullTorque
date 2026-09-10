<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a tabela cartoes_usuario existe
$result = $conexao->query("SHOW TABLES LIKE 'cartoes_usuario'");
if ($result->num_rows == 0) {
    // Criar a tabela se não existir
    $sql = "CREATE TABLE cartoes_usuario (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        numero VARCHAR(19) NOT NULL,
        nome VARCHAR(100) NOT NULL,
        validade VARCHAR(5) NOT NULL,
        tipo ENUM('credito', 'debito') NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conexao->query($sql)) {
        echo "Tabela cartoes_usuario criada com sucesso!<br>";
    } else {
        echo "Erro ao criar tabela: " . $conexao->error . "<br>";
    }
} else {
    echo "Tabela cartoes_usuario já existe.<br>";
}

// Verificar se o usuario_id da sessão existe na tabela usuarios
if (isset($_SESSION['usuario_id'])) {
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Usuário ID " . $_SESSION['usuario_id'] . " existe na tabela usuarios.<br>";
    } else {
        echo "ERRO: Usuário ID " . $_SESSION['usuario_id'] . " NÃO existe na tabela usuarios!<br>";
        echo "Isso pode causar erro de foreign key.<br>";
    }
} else {
    echo "Sessão não iniciada. Faça login primeiro.<br>";
}

// Remover foreign key se existir
$conexao->query("ALTER TABLE cartoes_usuario DROP FOREIGN KEY IF EXISTS cartoes_usuario_ibfk_1");
echo "Foreign key removida (se existia).<br>";

$conexao->close();
echo "<br><a href='pagamentos.php'>Voltar para Pagamentos</a>";
?>