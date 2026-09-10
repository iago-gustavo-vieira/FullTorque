<?php
// Script para criar o banco de dados automaticamente

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'fulltorque';

try {
    // Conectar sem especificar banco
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("❌ Erro: MySQL não está rodando! Inicie o MySQL no XAMPP.");
    }
    
    // Criar banco se não existir
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Banco '$dbname' criado/verificado com sucesso!<br>";
        
        // Selecionar o banco
        $conn->select_db($dbname);
        
        // Criar tabela usuarios se não existir
        $sql_usuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(100) NOT NULL,
            `sobrenome` varchar(100) DEFAULT NULL,
            `email` varchar(100) NOT NULL,
            `senha` varchar(255) NOT NULL,
            `cpf` varchar(14) DEFAULT NULL,
            `celular` varchar(15) DEFAULT NULL,
            `nivel` enum('cliente','admin','mecanico') DEFAULT 'cliente',
            `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `foto_perfil` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($sql_usuarios) === TRUE) {
            echo "✅ Tabela 'usuarios' criada/verificada!<br>";
        }
        
        // Criar tabela logs se não existir
        $sql_logs = "CREATE TABLE IF NOT EXISTS `logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) DEFAULT NULL,
            `acao` varchar(100) NOT NULL,
            `descricao` text,
            `ip` varchar(45) DEFAULT NULL,
            `data_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($sql_logs) === TRUE) {
            echo "✅ Tabela 'logs' criada/verificada!<br>";
        }
        
        echo "<br><strong>🎉 Banco configurado com sucesso!</strong><br>";
        echo "<a href='home.php' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#2ecc71;color:white;text-decoration:none;border-radius:5px;'>Ir para Home</a>";
        
    } else {
        echo "❌ Erro ao criar banco: " . $conn->error;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    die("❌ Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Configurar Banco de Dados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        div {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div>
        <h2>🔧 Configuração do Banco de Dados</h2>
        <hr>
    </div>
</body>
</html>
