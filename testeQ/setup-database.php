<?php
require_once 'config.php';

// Conecta ao servidor MySQL
$conexao = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Verifica se houve erro na conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Cria o banco de dados se não existir
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conexao->query($sql) === TRUE) {
    echo "Banco de dados criado com sucesso.<br>";
} else {
    die("Erro ao criar banco de dados: " . $conexao->error);
}

// Seleciona o banco de dados
$conexao->select_db(DB_NAME);

// Define o charset para utf8
$conexao->set_charset("utf8");

// Cria a tabela de usuários
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'funcionario', 'cliente') NOT NULL DEFAULT 'cliente',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    telefone VARCHAR(20) DEFAULT NULL,
    cpf VARCHAR(14) DEFAULT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conexao->query($sql) === TRUE) {
    echo "Tabela usuarios criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela usuarios: " . $conexao->error);
}

// Cria a tabela de veículos
$sql = "CREATE TABLE IF NOT EXISTS veiculos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    placa VARCHAR(10) NOT NULL,
    ano INT(4) NOT NULL,
    cor VARCHAR(30) NOT NULL,
    quilometragem INT(11) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    imagem_url VARCHAR(255) DEFAULT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY placa (placa),
    KEY usuario_id (usuario_id),
    CONSTRAINT fk_veiculos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conexao->query($sql) === TRUE) {
    echo "Tabela veiculos criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela veiculos: " . $conexao->error);
}

// Cria a tabela de agendamentos
$sql = "CREATE TABLE IF NOT EXISTS agendamentos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    veiculo_id INT(11) NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    status ENUM('agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    observacoes TEXT DEFAULT NULL,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY usuario_id (usuario_id),
    KEY veiculo_id (veiculo_id),
    CONSTRAINT fk_agendamentos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_agendamentos_veiculos FOREIGN KEY (veiculo_id) REFERENCES veiculos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conexao->query($sql) === TRUE) {
    echo "Tabela agendamentos criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela agendamentos: " . $conexao->error);
}

// Cria a tabela de ordens de serviço
$sql = "CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    veiculo_id INT(11) NOT NULL,
    data_abertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATETIME DEFAULT NULL,
    status ENUM('aberta', 'em_andamento', 'aguardando_aprovacao', 'aguardando_pecas', 'concluida', 'cancelada') NOT NULL DEFAULT 'aberta',
    valor_total DECIMAL(10,2) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    KEY usuario_id (usuario_id),
    KEY veiculo_id (veiculo_id),
    CONSTRAINT fk_ordens_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_ordens_veiculos FOREIGN KEY (veiculo_id) REFERENCES veiculos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conexao->query($sql) === TRUE) {
    echo "Tabela ordens_servico criada com sucesso.<br>";
} else {
    die("Erro ao criar tabela ordens_servico: " . $conexao->error);
}

// Cria um usuário administrador padrão se não existir
$email_admin = 'admin@admin.com';
$senha_admin = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES ('Administrador', ?, ?, 'admin')");
    $stmt->bind_param("ss", $email_admin, $senha_admin);
    
    if ($stmt->execute()) {
        echo "Usuário administrador criado com sucesso.<br>";
        echo "Email: admin@admin.com<br>";
        echo "Senha: admin123<br>";
    } else {
        echo "Erro ao criar usuário administrador: " . $stmt->error . "<br>";
    }
}

$conexao->close();

echo "<br>Setup concluído!";
echo "<br><a href='login.php'>Ir para a página de login</a>";
?>