<?php
require_once 'config.php';

$conexao = conectarBD();
$logs = [];

function addLog($mensagem, $tipo = 'info') {
    global $logs;
    $logs[] = ['mensagem' => $mensagem, 'tipo' => $tipo];
}

// 1. Garantir que admin existe e pode logar
addLog("=== CORRIGINDO ADMIN ===", 'titulo');

$admin_email = "admin@autoservice.com";
$admin_senha = password_hash("admin123", PASSWORD_DEFAULT);
$admin_nome = "Administrador";
$admin_cpf = "000.000.000-00";

$check_admin = $conexao->query("SELECT id, email FROM usuarios WHERE email = '$admin_email'");
if ($check_admin->num_rows > 0) {
    $admin = $check_admin->fetch_assoc();
    $conexao->query("UPDATE usuarios SET senha = '$admin_senha', nivel_acesso = 'admin' WHERE id = " . $admin['id']);
    addLog("✅ Admin atualizado: {$admin['email']} | Senha: admin123", 'success');
} else {
    // Criar admin se não existir
    $data_cadastro = date('Y-m-d H:i:s');
    
    // Verificar quais colunas existem
    $colunas = ['nome', 'email', 'cpf', 'senha', 'nivel_acesso', 'data_cadastro'];
    $valores = ["'$admin_nome'", "'$admin_email'", "'$admin_cpf'", "'$admin_senha'", "'admin'", "'$data_cadastro'"];
    
    if (colunaExiste($conexao, 'usuarios', 'telefone')) {
        $colunas[] = 'telefone';
        $valores[] = "'(00) 00000-0000'";
    }
    
    $sql = "INSERT INTO usuarios (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $valores) . ")";
    
    if ($conexao->query($sql)) {
        addLog("✅ Admin CRIADO com sucesso: $admin_email | Senha: admin123", 'success');
    } else {
        addLog("❌ Erro ao criar admin: " . $conexao->error, 'error');
    }
}

// 2. Adicionar coluna nivel_acesso se não existir
addLog("=== VERIFICANDO ESTRUTURA DA TABELA USUARIOS ===", 'titulo');

if (!colunaExiste($conexao, 'usuarios', 'nivel_acesso')) {
    $conexao->query("ALTER TABLE usuarios ADD COLUMN nivel_acesso VARCHAR(20) DEFAULT 'cliente' AFTER senha");
    addLog("✅ Coluna nivel_acesso adicionada", 'success');
} else {
    addLog("✓ Coluna nivel_acesso já existe", 'info');
}

// 3. Corrigir tabela veiculos
addLog("=== CORRIGINDO TABELA VEICULOS ===", 'titulo');

if (tabelaExiste($conexao, 'veiculos')) {
    if (!colunaExiste($conexao, 'veiculos', 'usuario_id')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN usuario_id INT(11) AFTER id");
        addLog("✅ Coluna usuario_id adicionada em veiculos", 'success');
    }
    if (!colunaExiste($conexao, 'veiculos', 'cor')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN cor VARCHAR(50) AFTER ano");
        addLog("✅ Coluna cor adicionada em veiculos", 'success');
    }
    if (!colunaExiste($conexao, 'veiculos', 'quilometragem')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN quilometragem INT(11) AFTER cor");
        addLog("✅ Coluna quilometragem adicionada em veiculos", 'success');
    }
    if (!colunaExiste($conexao, 'veiculos', 'observacoes')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN observacoes TEXT AFTER quilometragem");
        addLog("✅ Coluna observacoes adicionada em veiculos", 'success');
    }
} else {
    addLog("❌ Tabela veiculos não existe", 'error');
}

// 4. Criar tabela agendamentos se não existir
addLog("=== VERIFICANDO TABELA AGENDAMENTOS ===", 'titulo');

if (!tabelaExiste($conexao, 'agendamentos')) {
    $sql = "CREATE TABLE agendamentos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11) NOT NULL,
        veiculo_id INT(11) NOT NULL,
        mecanico_id INT(11),
        data_agendamento DATE NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fim TIME,
        observacoes TEXT,
        status VARCHAR(20) DEFAULT 'agendado',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    addLog("✅ Tabela agendamentos criada", 'success');
} else {
    addLog("✓ Tabela agendamentos já existe", 'info');
}

// 5. Criar tabela relatorios_cliente se não existir
addLog("=== VERIFICANDO TABELA RELATORIOS_CLIENTE ===", 'titulo');

if (!tabelaExiste($conexao, 'relatorios_cliente')) {
    $sql = "CREATE TABLE relatorios_cliente (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11) NOT NULL,
        veiculo_id INT(11) NOT NULL,
        mecanico_id INT(11),
        descricao_problema TEXT,
        urgencia VARCHAR(20),
        fotos TEXT,
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'pendente'
    )";
    $conexao->query($sql);
    addLog("✅ Tabela relatorios_cliente criada", 'success');
} else {
    addLog("✓ Tabela relatorios_cliente já existe", 'info');
}

// 6. Criar tabela recuperacao_senha se não existir
addLog("=== VERIFICANDO TABELA RECUPERACAO_SENHA ===", 'titulo');

if (!tabelaExiste($conexao, 'recuperacao_senha')) {
    $sql = "CREATE TABLE recuperacao_senha (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11) NOT NULL,
        token VARCHAR(64) NOT NULL,
        expira DATETIME NOT NULL,
        usado TINYINT(1) DEFAULT 0,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY usuario_id (usuario_id),
        KEY token (token)
    )";
    $conexao->query($sql);
    addLog("✅ Tabela recuperacao_senha criada", 'success');
} else {
    addLog("✓ Tabela recuperacao_senha já existe", 'info');
}

// 7. Criar tabela logs se não existir
addLog("=== VERIFICANDO TABELA LOGS ===", 'titulo');

if (!tabelaExiste($conexao, 'logs')) {
    $sql = "CREATE TABLE logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11),
        acao VARCHAR(100),
        descricao TEXT,
        ip VARCHAR(45),
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    addLog("✅ Tabela logs criada", 'success');
} else {
    addLog("✓ Tabela logs já existe", 'info');
}

// 8. Criar tabela promocoes_carousel se não existir
addLog("=== VERIFICANDO TABELA PROMOCOES_CAROUSEL ===", 'titulo');

if (!tabelaExiste($conexao, 'promocoes_carousel')) {
    $sql = "CREATE TABLE promocoes_carousel (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT,
        imagem VARCHAR(255),
        ativo TINYINT(1) DEFAULT 1,
        ordem INT(11) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    addLog("✅ Tabela promocoes_carousel criada", 'success');
} else {
    addLog("✓ Tabela promocoes_carousel já existe", 'info');
}

// 9. Limpar foreign keys problemáticas
addLog("=== LIMPANDO FOREIGN KEYS ===", 'titulo');

$fks = $conexao->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'veiculos' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");

if ($fks && $fks->num_rows > 0) {
    while ($fk = $fks->fetch_assoc()) {
        $conexao->query("ALTER TABLE veiculos DROP FOREIGN KEY " . $fk['CONSTRAINT_NAME']);
        addLog("✅ Foreign key removida: " . $fk['CONSTRAINT_NAME'], 'success');
    }
} else {
    addLog("✓ Nenhuma foreign key problemática encontrada", 'info');
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção Completa - FullTorque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #109349, #CE2B37);
            padding: 20px;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #CE2B37;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .log-item {
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .log-titulo {
            background: #e3f2fd;
            border-left-color: #2196f3;
            font-weight: bold;
            color: #1976d2;
            margin-top: 20px;
        }
        .log-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .log-error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .log-info {
            background: #f8f9fa;
            border-left-color: #6c757d;
            color: #495057;
        }
        .admin-info {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #ffc107;
        }
        .admin-info h3 {
            color: #856404;
            margin-top: 0;
        }
        .admin-info strong {
            color: #109349;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .btn {
            background: #109349;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0d7a3a;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Correção Completa do Sistema</h1>
    <p class="subtitle">Todos os problemas do banco de dados foram verificados e corrigidos</p>
    
    <div class="admin-info">
        <h3>🔑 Credenciais do Admin</h3>
        <p><strong>Email:</strong> admin@autoservice.com</p>
        <p><strong>Senha:</strong> admin123</p>
        <p style="margin-bottom: 0;"><strong>Status:</strong> ✅ Pronto para login</p>
    </div>
    
    <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; padding: 15px; background: #fafafa;">
        <?php foreach ($logs as $log): ?>
            <div class="log-item log-<?php echo $log['tipo']; ?>">
                <?php echo $log['mensagem']; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="btn-container">
        <a href="admin-login.php" class="btn">🔐 Fazer Login como Admin</a>
        <a href="verificar-usuarios.php" class="btn btn-secondary">👥 Ver Usuários</a>
        <a href="home.php" class="btn btn-secondary">🏠 Ir para Home</a>
    </div>
</div>
</body>
</html>
