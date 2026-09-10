<?php
require_once 'config.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();

// Verificar se a tabela mecanicos já existe
$tabela_existe = $conexao->query("SHOW TABLES LIKE 'mecanicos'")->num_rows > 0;

if (!$tabela_existe) {
    // Criar a tabela mecanicos
    $sql = "CREATE TABLE mecanicos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        especialidade VARCHAR(100) NOT NULL,
        experiencia INT(3) DEFAULT 0,
        biografia TEXT,
        foto VARCHAR(255),
        ativo TINYINT(1) DEFAULT 1,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conexao->query($sql)) {
        echo "<p>Tabela 'mecanicos' criada com sucesso!</p>";
    } else {
        echo "<p>Erro ao criar tabela 'mecanicos': " . $conexao->error . "</p>";
    }
}

// Verificar se a coluna mecanico_id existe na tabela agendamentos
$coluna_existe = false;
$colunas = $conexao->query("SHOW COLUMNS FROM agendamentos");
while ($coluna = $colunas->fetch_assoc()) {
    if ($coluna['Field'] == 'mecanico_id') {
        $coluna_existe = true;
        break;
    }
}

if (!$coluna_existe) {
    // Adicionar a coluna mecanico_id à tabela agendamentos
    $sql = "ALTER TABLE agendamentos ADD COLUMN mecanico_id INT(11) NULL AFTER servico_id, 
            ADD CONSTRAINT fk_agendamento_mecanico FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE SET NULL";
    
    if ($conexao->query($sql)) {
        echo "<p>Coluna 'mecanico_id' adicionada à tabela 'agendamentos' com sucesso!</p>";
    } else {
        echo "<p>Erro ao adicionar coluna 'mecanico_id': " . $conexao->error . "</p>";
    }
}

$conexao->close();

echo "<p>Atualização do banco de dados concluída!</p>";
echo "<p><a href='admin.php'>Voltar para o painel</a></p>";
?>