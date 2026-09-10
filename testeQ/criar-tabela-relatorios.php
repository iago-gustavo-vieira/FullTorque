<?php
require_once 'config.php';

$conexao = conectarBD();

// Criar tabela relatorios_cliente
$sql = "CREATE TABLE IF NOT EXISTS relatorios_cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    analista_id INT NULL,
    descricao_problema TEXT,
    urgencia ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    status ENUM('pendente', 'analisado', 'aceito', 'rejeitado', 'realizado', 'cancelado', 'solicitou_mudanca') DEFAULT 'pendente',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id)
)";

if ($conexao->query($sql)) {
    echo "Tabela relatorios_cliente criada com sucesso!<br>";
} else {
    echo "Erro ao criar tabela relatorios_cliente: " . $conexao->error . "<br>";
}

// Criar tabela relatorios_mecanico
$sql2 = "CREATE TABLE IF NOT EXISTS relatorios_mecanico (
    id INT PRIMARY KEY AUTO_INCREMENT,
    relatorio_cliente_id INT NOT NULL,
    diagnostico TEXT,
    status ENUM('pendente', 'respondido') DEFAULT 'pendente',
    data_proposta DATETIME NULL,
    observacoes_data TEXT,
    status_agendamento ENUM('pendente', 'confirmado', 'rejeitado') DEFAULT 'pendente',
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relatorio_cliente_id) REFERENCES relatorios_cliente(id)
)";

if ($conexao->query($sql2)) {
    echo "Tabela relatorios_mecanico criada com sucesso!<br>";
} else {
    echo "Erro ao criar tabela relatorios_mecanico: " . $conexao->error . "<br>";
}

// Criar tabela analistas se não existir
$sql3 = "CREATE TABLE IF NOT EXISTS analistas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conexao->query($sql3)) {
    echo "Tabela analistas criada com sucesso!<br>";
} else {
    echo "Erro ao criar tabela analistas: " . $conexao->error . "<br>";
}

$conexao->close();
echo "<br><a href='relatorios.php'>Voltar para Meus Diagnósticos</a>";
?>