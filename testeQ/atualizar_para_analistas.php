<?php
require_once 'config.php';

$conexao = conectarBD();

// Criar tabela de analistas
$sql_analistas = "
CREATE TABLE IF NOT EXISTS analistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    experiencia INT DEFAULT 0,
    biografia TEXT,
    foto VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conexao->query($sql_analistas);
echo "Tabela 'analistas' criada!<br>";

// Inserir 3 analistas
$analistas = [
    ['Carlos Silva', 'Diagnóstico Geral', 8, 'Especialista em diagnóstico automotivo com 8 anos de experiência.'],
    ['Maria Santos', 'Eletrônica Automotiva', 6, 'Analista especializada em sistemas eletrônicos veiculares.'],
    ['João Oliveira', 'Motor e Transmissão', 10, 'Especialista em diagnóstico de motores e sistemas de transmissão.']
];

foreach ($analistas as $analista) {
    $stmt = $conexao->prepare("INSERT INTO analistas (nome, especialidade, experiencia, biografia) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $analista[0], $analista[1], $analista[2], $analista[3]);
    $stmt->execute();
    echo "Analista {$analista[0]} criado!<br>";
}

// Atualizar tabelas existentes
$conexao->query("ALTER TABLE relatorios_cliente CHANGE mecanico_id analista_id INT NOT NULL");
$conexao->query("ALTER TABLE relatorios_mecanico CHANGE mecanico_id analista_id INT NOT NULL");
$conexao->query("ALTER TABLE relatorios_mecanico DROP COLUMN servicos_necessarios");
$conexao->query("ALTER TABLE relatorios_mecanico DROP COLUMN valor_total");
$conexao->query("ALTER TABLE usuarios ADD COLUMN analista_id INT NULL");

echo "Tabelas atualizadas para analistas!<br>";

// Criar usuários analistas
foreach ($analistas as $index => $analista) {
    $email_analista = strtolower(str_replace(' ', '', $analista[0])) . '@autoservice.com';
    $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
    $analista_id = $index + 1;
    
    $stmt = $conexao->prepare("INSERT IGNORE INTO usuarios (nome, email, telefone, cpf, senha, nivel_acesso, analista_id) VALUES (?, ?, '(00) 00000-0000', '000.000.000-00', ?, 'funcionario', ?)");
    $stmt->bind_param("sssi", $analista[0], $email_analista, $senha_hash, $analista_id);
    $stmt->execute();
    
    echo "Usuário analista criado: {$analista[0]} - Email: $email_analista - Senha: 123456<br>";
}

echo "<br><strong>Sistema atualizado para analistas com sucesso!</strong>";

$conexao->close();
?>