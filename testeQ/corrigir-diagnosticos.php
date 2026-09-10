<?php
require_once 'config.php';

echo "<h2>Correção da Estrutura de Diagnósticos</h2>";

$conexao = conectarBD();

// Criar tabela relatorios_cliente se não existir
$sql_relatorios_cliente = "
CREATE TABLE IF NOT EXISTS relatorios_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    analista_id INT NULL,
    descricao_problema TEXT,
    urgencia ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    endereco_completo TEXT,
    status ENUM('pendente', 'analisado', 'respondido', 'aceito', 'solicitou_mudanca', 'cancelado') DEFAULT 'pendente',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
)";

// Criar tabela relatorios_mecanico se não existir
$sql_relatorios_mecanico = "
CREATE TABLE IF NOT EXISTS relatorios_mecanico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relatorio_cliente_id INT NOT NULL,
    mecanico_id INT NULL,
    diagnostico TEXT,
    observacoes TEXT,
    status ENUM('pendente', 'em_analise', 'concluido') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (relatorio_cliente_id) REFERENCES relatorios_cliente(id) ON DELETE CASCADE
)";

// Criar tabela mecanicos se não existir
$sql_mecanicos = "
CREATE TABLE IF NOT EXISTS mecanicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Criar tabela analistas se não existir
$sql_analistas = "
CREATE TABLE IF NOT EXISTS analistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Executar as queries
$tabelas = [
    'mecanicos' => $sql_mecanicos,
    'analistas' => $sql_analistas,
    'relatorios_cliente' => $sql_relatorios_cliente,
    'relatorios_mecanico' => $sql_relatorios_mecanico
];

foreach ($tabelas as $nome => $sql) {
    try {
        if ($conexao->query($sql)) {
            echo "<p style='color: green;'>✓ Tabela '$nome' criada/verificada com sucesso</p>";
        } else {
            echo "<p style='color: red;'>✗ Erro ao criar tabela '$nome': " . $conexao->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Exceção ao criar tabela '$nome': " . $e->getMessage() . "</p>";
    }
}

// Inserir alguns dados de exemplo se as tabelas estiverem vazias
echo "<h3>Inserindo Dados de Exemplo</h3>";

// Verificar se existem mecânicos
$count_mecanicos = $conexao->query("SELECT COUNT(*) as total FROM mecanicos")->fetch_assoc()['total'];
if ($count_mecanicos == 0) {
    $mecanicos_exemplo = [
        ['João Silva', 'joao@autoservice.com', '(11) 99999-1111', 'Motor e Transmissão'],
        ['Maria Santos', 'maria@autoservice.com', '(11) 99999-2222', 'Freios e Suspensão'],
        ['Pedro Costa', 'pedro@autoservice.com', '(11) 99999-3333', 'Elétrica Automotiva']
    ];
    
    foreach ($mecanicos_exemplo as $mecanico) {
        $stmt = $conexao->prepare("INSERT INTO mecanicos (nome, email, telefone, especialidade) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $mecanico[0], $mecanico[1], $mecanico[2], $mecanico[3]);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Mecânico '{$mecanico[0]}' inserido</p>";
        }
    }
}

// Verificar se existem analistas
$count_analistas = $conexao->query("SELECT COUNT(*) as total FROM analistas")->fetch_assoc()['total'];
if ($count_analistas == 0) {
    $analistas_exemplo = [
        ['Ana Oliveira', 'ana@autoservice.com', '(11) 99999-4444', 'Diagnóstico Geral'],
        ['Carlos Ferreira', 'carlos@autoservice.com', '(11) 99999-5555', 'Análise Técnica']
    ];
    
    foreach ($analistas_exemplo as $analista) {
        $stmt = $conexao->prepare("INSERT INTO analistas (nome, email, telefone, especialidade) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $analista[0], $analista[1], $analista[2], $analista[3]);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Analista '{$analista[0]}' inserido</p>";
        }
    }
}

echo "<h3>Estrutura Corrigida!</h3>";
echo "<p><a href='admin-relatorios.php'>Ir para Página de Diagnósticos</a></p>";
echo "<p><a href='teste-diagnosticos.php'>Testar Estrutura</a></p>";

$conexao->close();
?>