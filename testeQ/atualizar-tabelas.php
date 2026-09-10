<?php
require_once 'config.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();

// Verificar se a tabela agendamento_itens já existe
$tabela_existe = $conexao->query("SHOW TABLES LIKE 'agendamento_itens'")->num_rows > 0;

if (!$tabela_existe) {
    // Criar a tabela agendamento_itens
    $sql = "CREATE TABLE agendamento_itens (
        id INT(11) NOT NULL AUTO_INCREMENT,
        agendamento_id INT(11) NOT NULL,
        servico_id INT(11) NOT NULL,
        PRIMARY KEY (id),
        KEY fk_agendamento_itens_agendamento (agendamento_id),
        KEY fk_agendamento_itens_servico (servico_id),
        CONSTRAINT fk_agendamento_itens_agendamento FOREIGN KEY (agendamento_id) REFERENCES agendamentos (id) ON DELETE CASCADE,
        CONSTRAINT fk_agendamento_itens_servico FOREIGN KEY (servico_id) REFERENCES servicos (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conexao->query($sql)) {
        echo "<p>Tabela 'agendamento_itens' criada com sucesso!</p>";
    } else {
        echo "<p>Erro ao criar tabela 'agendamento_itens': " . $conexao->error . "</p>";
    }
    
    // Verificar se a coluna servico_id existe na tabela agendamentos
    $coluna_existe = false;
    $colunas = $conexao->query("SHOW COLUMNS FROM agendamentos");
    while ($coluna = $colunas->fetch_assoc()) {
        if ($coluna['Field'] == 'servico_id') {
            $coluna_existe = true;
            break;
        }
    }
    
    if ($coluna_existe) {
        // Migrar dados existentes para a nova tabela
        $agendamentos = $conexao->query("SELECT id, servico_id FROM agendamentos WHERE servico_id IS NOT NULL");
        
        while ($agendamento = $agendamentos->fetch_assoc()) {
            $stmt = $conexao->prepare("INSERT INTO agendamento_itens (agendamento_id, servico_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $agendamento['id'], $agendamento['servico_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        echo "<p>Dados migrados para a tabela 'agendamento_itens' com sucesso!</p>";
    }
}

// Verificar se a tabela agendamento_mecanicos já existe
$tabela_existe = $conexao->query("SHOW TABLES LIKE 'agendamento_mecanicos'")->num_rows > 0;

if (!$tabela_existe) {
    // Criar a tabela agendamento_mecanicos
    $sql = "CREATE TABLE agendamento_mecanicos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        agendamento_id INT(11) NOT NULL,
        mecanico_id INT(11) NOT NULL,
        PRIMARY KEY (id),
        KEY fk_agendamento_mecanicos_agendamento (agendamento_id),
        KEY fk_agendamento_mecanicos_mecanico (mecanico_id),
        CONSTRAINT fk_agendamento_mecanicos_agendamento FOREIGN KEY (agendamento_id) REFERENCES agendamentos (id) ON DELETE CASCADE,
        CONSTRAINT fk_agendamento_mecanicos_mecanico FOREIGN KEY (mecanico_id) REFERENCES mecanicos (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conexao->query($sql)) {
        echo "<p>Tabela 'agendamento_mecanicos' criada com sucesso!</p>";
    } else {
        echo "<p>Erro ao criar tabela 'agendamento_mecanicos': " . $conexao->error . "</p>";
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
    
    if ($coluna_existe) {
        // Migrar dados existentes para a nova tabela
        $agendamentos = $conexao->query("SELECT id, mecanico_id FROM agendamentos WHERE mecanico_id IS NOT NULL");
        
        while ($agendamento = $agendamentos->fetch_assoc()) {
            $stmt = $conexao->prepare("INSERT INTO agendamento_mecanicos (agendamento_id, mecanico_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $agendamento['id'], $agendamento['mecanico_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        echo "<p>Dados migrados para a tabela 'agendamento_mecanicos' com sucesso!</p>";
    }
}

$conexao->close();

echo "<p>Atualização do banco de dados concluída!</p>";
echo "<p><a href='admin.php'>Voltar para o painel</a></p>";
?>