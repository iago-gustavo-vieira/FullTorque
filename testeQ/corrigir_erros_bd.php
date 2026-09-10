<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>Corrigir Banco de Dados</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { color: #109349; }
        a { color: #109349; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php
require_once 'config.php';

$conexao = conectarBD();
echo "<h2>Corrigindo Banco de Dados</h2>";

// 1. Verificar e criar tabela veiculos
if (!tabelaExiste($conexao, 'veiculos')) {
    $sql = "CREATE TABLE veiculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        marca VARCHAR(100) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        placa VARCHAR(10) NOT NULL UNIQUE,
        ano INT NOT NULL,
        cor VARCHAR(50) NULL,
        quilometragem INT NULL,
        observacoes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    echo "✅ Tabela 'veiculos' criada<br>";
} else {
    // Adicionar colunas faltantes
    if (!colunaExiste($conexao, 'veiculos', 'usuario_id')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN usuario_id INT NULL AFTER id");
        echo "✅ Coluna 'usuario_id' adicionada na tabela veiculos<br>";
    }
    if (!colunaExiste($conexao, 'veiculos', 'cor')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN cor VARCHAR(50) NULL AFTER ano");
        echo "✅ Coluna 'cor' adicionada na tabela veiculos<br>";
    }
    if (!colunaExiste($conexao, 'veiculos', 'quilometragem')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN quilometragem INT NULL AFTER cor");
        echo "✅ Coluna 'quilometragem' adicionada na tabela veiculos<br>";
    }
    if (!colunaExiste($conexao, 'veiculos', 'observacoes')) {
        $conexao->query("ALTER TABLE veiculos ADD COLUMN observacoes TEXT NULL AFTER quilometragem");
        echo "✅ Coluna 'observacoes' adicionada na tabela veiculos<br>";
    }
    echo "✅ Tabela 'veiculos' verificada<br>";
}

// 2. Verificar e criar tabela agendamentos
if (!tabelaExiste($conexao, 'agendamentos')) {
    $sql = "CREATE TABLE agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        veiculo_id INT NOT NULL,
        mecanico_id INT NULL,
        data_agendamento DATE NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fim TIME NULL,
        servico VARCHAR(255) NOT NULL,
        descricao TEXT NULL,
        status ENUM('agendado','confirmado','em_andamento','concluido','cancelado') DEFAULT 'agendado',
        valor DECIMAL(10,2) NULL,
        observacoes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    echo "✅ Tabela 'agendamentos' criada<br>";
} else {
    echo "✅ Tabela 'agendamentos' verificada<br>";
}

// 3. Verificar e criar tabela promocoes_carousel
if (!tabelaExiste($conexao, 'promocoes_carousel')) {
    $sql = "CREATE TABLE promocoes_carousel (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NULL,
        desconto_percentual INT NULL,
        codigo_cupom VARCHAR(50) NULL,
        imagem VARCHAR(500) NULL,
        cor VARCHAR(50) NULL,
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    echo "✅ Tabela 'promocoes_carousel' criada<br>";
} else {
    echo "✅ Tabela 'promocoes_carousel' verificada<br>";
}

// 4. Verificar coluna primeiro_acesso na tabela usuarios
if (tabelaExiste($conexao, 'usuarios')) {
    if (!colunaExiste($conexao, 'usuarios', 'primeiro_acesso')) {
        $conexao->query("ALTER TABLE usuarios ADD COLUMN primeiro_acesso TINYINT(1) DEFAULT 1 AFTER senha");
        echo "✅ Coluna 'primeiro_acesso' adicionada na tabela usuarios<br>";
    }
    echo "✅ Tabela 'usuarios' verificada<br>";
}

// 5. Verificar e criar tabela logs
if (!tabelaExiste($conexao, 'logs')) {
    $sql = "CREATE TABLE logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        acao VARCHAR(100) NOT NULL,
        descricao TEXT NULL,
        ip VARCHAR(45) NULL,
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    echo "✅ Tabela 'logs' criada<br>";
} else {
    echo "✅ Tabela 'logs' verificada<br>";
}

// 6. Verificar e criar tabela relatorios_cliente
if (!tabelaExiste($conexao, 'relatorios_cliente')) {
    $sql = "CREATE TABLE relatorios_cliente (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        veiculo_id INT NULL,
        mecanico_id INT NULL,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        problema_relatado TEXT NULL,
        diagnostico TEXT NULL,
        solucao TEXT NULL,
        status ENUM('pendente','em_analise','concluido','cancelado') DEFAULT 'pendente',
        prioridade ENUM('baixa','media','alta','urgente') DEFAULT 'media',
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_conclusao TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conexao->query($sql);
    echo "✅ Tabela 'relatorios_cliente' criada<br>";
} else {
    echo "✅ Tabela 'relatorios_cliente' verificada<br>";
}

$conexao->close();
echo "<br><strong>✅ Todas as correções foram concluídas!</strong><br>";
echo "<br><a href='index.php'>Voltar para o sistema</a>";
?>
</body>
</html>
