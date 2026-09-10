<?php
require_once 'config.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();

// Verificar se a tabela agendamento_servicos já existe
$tabela_existe = $conexao->query("SHOW TABLES LIKE 'agendamento_servicos'")->num_rows > 0;

if (!$tabela_existe) {
    // Criar a tabela agendamento_servicos
    $sql = "CREATE TABLE agendamento_servicos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        agendamento_id INT(11) NOT NULL,
        servico_id INT(11) NOT NULL,
        PRIMARY KEY (id),
        KEY fk_agendamento_servicos_agendamento (agendamento_id),
        KEY fk_agendamento_servicos_servico (servico_id),
        CONSTRAINT fk_agendamento_servicos_agendamento FOREIGN KEY (agendamento_id) REFERENCES agendamentos (id) ON DELETE CASCADE,
        CONSTRAINT fk_agendamento_servicos_servico FOREIGN KEY (servico_id) REFERENCES servicos (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conexao->query($sql)) {
        echo "<p>Tabela 'agendamento_servicos' criada com sucesso!</p>";
    } else {
        echo "<p>Erro ao criar tabela 'agendamento_servicos': " . $conexao->error . "</p>";
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
            $stmt = $conexao->prepare("INSERT INTO agendamento_servicos (agendamento_id, servico_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $agendamento['id'], $agendamento['servico_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Remover a coluna servico_id da tabela agendamentos
        $sql = "ALTER TABLE agendamentos DROP FOREIGN KEY agendamentos_ibfk_2";
        $conexao->query($sql);
        
        $sql = "ALTER TABLE agendamentos DROP COLUMN servico_id";
        if ($conexao->query($sql)) {
            echo "<p>Coluna 'servico_id' removida da tabela 'agendamentos' com sucesso!</p>";
        } else {
            echo "<p>Erro ao remover coluna 'servico_id': " . $conexao->error . "</p>";
        }
    }
}

$conexao->close();

echo "<p>Atualização do banco de dados concluída!</p>";
echo "<p><a href='admin.php'>Voltar para o painel</a></p>";
?>