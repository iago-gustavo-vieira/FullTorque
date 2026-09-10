<?php
require_once 'config.php';

echo "<h2>Correção da Estrutura do Banco - FullTorque</h2>";

try {
    $conexao = conectarBD();
    
    // Verificar se a coluna primeiro_acesso existe
    $result = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'primeiro_acesso'");
    if ($result->num_rows == 0) {
        echo "<p>➕ Adicionando coluna 'primeiro_acesso'...</p>";
        $conexao->query("ALTER TABLE usuarios ADD COLUMN primeiro_acesso TINYINT(1) DEFAULT 1");
        echo "<p style='color: green;'>✅ Coluna 'primeiro_acesso' adicionada!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Coluna 'primeiro_acesso' já existe.</p>";
    }
    
    // Verificar se existe um usuário admin
    $result = $conexao->query("SELECT * FROM usuarios WHERE id = 1 OR email = 'admin@fulltorque.com'");
    if ($result->num_rows == 0) {
        echo "<p>➕ Criando usuário administrador...</p>";
        
        $nome = "Administrador";
        $email = "admin@fulltorque.com";
        $cpf = "111.111.111-11";
        $celular = "(12) 98821-1304";
        $senha = password_hash("admin123", PASSWORD_DEFAULT);
        $data_cadastro = date('Y-m-d H:i:s');
        
        $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, cpf, celular, senha, data_cadastro, primeiro_acesso) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssss", $nome, $email, $cpf, $celular, $senha, $data_cadastro);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Usuário administrador criado!</p>";
            echo "<p><strong>Email:</strong> admin@fulltorque.com</p>";
            echo "<p><strong>Senha:</strong> admin123</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar administrador: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuário administrador já existe.</p>";
    }
    
    // Criar tabela de logs se não existir
    $result = $conexao->query("SHOW TABLES LIKE 'logs'");
    if ($result->num_rows == 0) {
        echo "<p>➕ Criando tabela 'logs'...</p>";
        $sql = "CREATE TABLE logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            acao VARCHAR(100) NOT NULL,
            descricao TEXT,
            ip VARCHAR(45),
            data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario (usuario_id),
            INDEX idx_data (data_hora)
        )";
        
        if ($conexao->query($sql)) {
            echo "<p style='color: green;'>✅ Tabela 'logs' criada!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar tabela logs: " . $conexao->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Tabela 'logs' já existe.</p>";
    }
    
    $conexao->close();
    echo "<p style='color: green; font-weight: bold;'>🎉 Correções concluídas!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<br><a href='teste-conexao.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Testar Conexão</a>";
echo "<a href='admin-login.php' style='background: #109349; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Admin</a>";
?>