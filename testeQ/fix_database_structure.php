<?php
require_once 'config.php';

echo "<h2>Verificando e corrigindo estrutura do banco de dados...</h2>";

$conexao = conectarBD();

// 1. Verificar se a tabela usuarios existe
$result = $conexao->query("SHOW TABLES LIKE 'usuarios'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>✗ Tabela usuarios não encontrada!</p>";
    
    // Criar tabela usuarios
    $sql = "CREATE TABLE usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        telefone VARCHAR(20),
        nivel_acesso ENUM('admin', 'gerente', 'funcionario', 'cliente') DEFAULT 'cliente',
        status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo',
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conexao->query($sql)) {
        echo "<p style='color: green;'>✓ Tabela usuarios criada!</p>";
        
        // Criar usuário admin padrão
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $admin_sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES ('Administrador', 'admin@admin.com', '$senha_hash', 'admin')";
        if ($conexao->query($admin_sql)) {
            echo "<p style='color: green;'>✓ Usuário admin criado (email: admin@admin.com, senha: admin123)</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Erro ao criar tabela usuarios: " . $conexao->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Tabela usuarios existe</p>";
}

// 2. Verificar se a tabela logs existe
$result = $conexao->query("SHOW TABLES LIKE 'logs'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>✗ Tabela logs não encontrada!</p>";
    
    // Criar tabela logs
    $sql = "CREATE TABLE logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        acao VARCHAR(100) NOT NULL,
        descricao TEXT,
        ip VARCHAR(45),
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_id (usuario_id),
        INDEX idx_data_hora (data_hora)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conexao->query($sql)) {
        echo "<p style='color: green;'>✓ Tabela logs criada!</p>";
        
        // Adicionar foreign key apenas se a tabela usuarios existir
        $fk_sql = "ALTER TABLE logs ADD CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL";
        if ($conexao->query($fk_sql)) {
            echo "<p style='color: green;'>✓ Foreign key adicionada à tabela logs</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Aviso: Não foi possível adicionar foreign key: " . $conexao->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Erro ao criar tabela logs: " . $conexao->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Tabela logs existe</p>";
    
    // Verificar se há registros órfãos
    $orphan_check = $conexao->query("
        SELECT COUNT(*) as total 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        WHERE l.usuario_id IS NOT NULL AND u.id IS NULL
    ");
    
    if ($orphan_check) {
        $orphan_count = $orphan_check->fetch_assoc()['total'];
        if ($orphan_count > 0) {
            echo "<p style='color: orange;'>⚠ Encontrados $orphan_count registros órfãos na tabela logs</p>";
            
            // Corrigir registros órfãos
            if ($conexao->query("UPDATE logs SET usuario_id = NULL WHERE usuario_id NOT IN (SELECT id FROM usuarios)")) {
                echo "<p style='color: green;'>✓ Registros órfãos corrigidos</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ Nenhum registro órfão encontrado</p>";
        }
    }
}

// 3. Verificar se a tabela usuario_permissoes existe
$result = $conexao->query("SHOW TABLES LIKE 'usuario_permissoes'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>✗ Tabela usuario_permissoes não encontrada!</p>";
    
    // Criar tabela usuario_permissoes
    $sql = "CREATE TABLE usuario_permissoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        permissao VARCHAR(50) NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_permission (usuario_id, permissao),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conexao->query($sql)) {
        echo "<p style='color: green;'>✓ Tabela usuario_permissoes criada!</p>";
        
        // Adicionar permissões para o admin
        $admin_permissions = ['admin', 'gerenciar_usuarios', 'gerenciar_servicos', 'gerenciar_agendamentos', 'gerenciar_ordens'];
        foreach ($admin_permissions as $perm) {
            $conexao->query("INSERT IGNORE INTO usuario_permissoes (usuario_id, permissao) VALUES (1, '$perm')");
        }
        echo "<p style='color: green;'>✓ Permissões do admin configuradas</p>";
    } else {
        echo "<p style='color: red;'>✗ Erro ao criar tabela usuario_permissoes: " . $conexao->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Tabela usuario_permissoes existe</p>";
}

// 4. Verificar integridade geral
echo "<h3>Verificação de integridade:</h3>";

$user_count = $conexao->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
echo "<p>Total de usuários: $user_count</p>";

$log_count = $conexao->query("SELECT COUNT(*) as total FROM logs")->fetch_assoc()['total'];
echo "<p>Total de logs: $log_count</p>";

$perm_count = $conexao->query("SELECT COUNT(*) as total FROM usuario_permissoes")->fetch_assoc()['total'];
echo "<p>Total de permissões: $perm_count</p>";

$conexao->close();

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";
echo "<p><a href='admin-usuarios.php'>← Voltar para Gerenciar Usuários</a></p>";
echo "<p><a href='admin.php'>← Voltar para Dashboard</a></p>";
?>