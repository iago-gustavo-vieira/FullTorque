<?php
require_once 'config.php';

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

echo "<h2>Criando tabelas do sistema de pagamentos...</h2>";

// Tabela de pagamentos
$sql_pagamentos = "CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    agendamento_id INT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo_pagamento ENUM('dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'transferencia') NOT NULL,
    status ENUM('pendente', 'processando', 'aprovado', 'recusado', 'cancelado') DEFAULT 'pendente',
    descricao TEXT,
    data_vencimento DATE NULL,
    data_pagamento DATETIME NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexao->query($sql_pagamentos)) {
    echo "<p style='color: green;'>✓ Tabela 'pagamentos' criada com sucesso!</p>";
} else {
    echo "<p style='color: red;'>✗ Erro ao criar tabela 'pagamentos': " . $conexao->error . "</p>";
}

// Tabela de métodos de pagamento dos usuários
$sql_metodos = "CREATE TABLE IF NOT EXISTS metodos_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('cartao_credito', 'cartao_debito', 'conta_bancaria', 'pix') NOT NULL,
    nome VARCHAR(100) NOT NULL,
    dados_criptografados TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexao->query($sql_metodos)) {
    echo "<p style='color: green;'>✓ Tabela 'metodos_pagamento' criada com sucesso!</p>";
} else {
    echo "<p style='color: red;'>✗ Erro ao criar tabela 'metodos_pagamento': " . $conexao->error . "</p>";
}

// Tabela de configurações de pagamento
$sql_config = "CREATE TABLE IF NOT EXISTS config_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave_pix VARCHAR(255),
    taxa_cartao DECIMAL(5,2) DEFAULT 3.50,
    dias_vencimento INT DEFAULT 7,
    mensagem_cobranca TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexao->query($sql_config)) {
    echo "<p style='color: green;'>✓ Tabela 'config_pagamentos' criada com sucesso!</p>";
    
    // Inserir configuração padrão
    $config_default = "INSERT IGNORE INTO config_pagamentos (id, chave_pix, mensagem_cobranca) VALUES 
    (1, 'contato@autoservice.com', 'Pagamento referente aos serviços prestados pela Auto Service.')";
    
    if ($conexao->query($config_default)) {
        echo "<p style='color: blue;'>ℹ Configuração padrão inserida!</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Erro ao criar tabela 'config_pagamentos': " . $conexao->error . "</p>";
}

$conexao->close();

echo "<hr>";
echo "<p><strong>Sistema de pagamentos configurado!</strong></p>";
echo "<p><a href='admin-pagamentos.php'>← Ir para Painel de Pagamentos</a></p>";
echo "<p><a href='admin.php'>← Voltar para Dashboard</a></p>";
?>