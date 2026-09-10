<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Migrando Sistema de Analistas para Mecânicos</h2>";

// 1. Renomear tabela analistas para mecanicos
$sql = "RENAME TABLE analistas TO mecanicos";
if ($conexao->query($sql)) {
    echo "<p>✅ Tabela 'analistas' renomeada para 'mecanicos'</p>";
} else {
    echo "<p>⚠️ Erro ao renomear tabela: " . $conexao->error . "</p>";
}

// 2. Atualizar coluna analista_id para mecanico_id na tabela usuarios
$sql = "ALTER TABLE usuarios CHANGE analista_id mecanico_id INT(11) NULL";
if ($conexao->query($sql)) {
    echo "<p>✅ Coluna 'analista_id' renomeada para 'mecanico_id' na tabela usuarios</p>";
} else {
    echo "<p>⚠️ Erro ao renomear coluna: " . $conexao->error . "</p>";
}

// 3. Atualizar coluna analista_id para mecanico_id na tabela relatorios_cliente
$sql = "ALTER TABLE relatorios_cliente CHANGE analista_id mecanico_id INT(11) NULL";
if ($conexao->query($sql)) {
    echo "<p>✅ Coluna 'analista_id' renomeada para 'mecanico_id' na tabela relatorios_cliente</p>";
} else {
    echo "<p>⚠️ Erro ao renomear coluna: " . $conexao->error . "</p>";
}

// 4. Criar tabela de agenda dos mecânicos
$sql = "CREATE TABLE IF NOT EXISTS mecanico_agenda (
    id INT(11) NOT NULL AUTO_INCREMENT,
    mecanico_id INT(11) NOT NULL,
    data_disponivel DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    disponivel TINYINT(1) DEFAULT 1,
    observacoes TEXT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_agenda (mecanico_id, data_disponivel, hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conexao->query($sql)) {
    echo "<p>✅ Tabela 'mecanico_agenda' criada com sucesso</p>";
} else {
    echo "<p>⚠️ Erro ao criar tabela agenda: " . $conexao->error . "</p>";
}

// 5. Criar tabela de agendamentos de diagnósticos
$sql = "CREATE TABLE IF NOT EXISTS diagnostico_agendamentos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    relatorio_cliente_id INT(11) NOT NULL,
    mecanico_id INT(11) NOT NULL,
    agenda_id INT(11) NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    status ENUM('agendado', 'confirmado', 'realizado', 'cancelado') DEFAULT 'agendado',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (relatorio_cliente_id) REFERENCES relatorios_cliente(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE CASCADE,
    FOREIGN KEY (agenda_id) REFERENCES mecanico_agenda(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conexao->query($sql)) {
    echo "<p>✅ Tabela 'diagnostico_agendamentos' criada com sucesso</p>";
} else {
    echo "<p>⚠️ Erro ao criar tabela agendamentos: " . $conexao->error . "</p>";
}

// 6. Inserir horários padrão para os mecânicos existentes
$mecanicos = $conexao->query("SELECT id FROM mecanicos WHERE ativo = 1");
if ($mecanicos && $mecanicos->num_rows > 0) {
    while ($mecanico = $mecanicos->fetch_assoc()) {
        // Criar agenda para os próximos 30 dias, de segunda a sexta, das 8h às 17h
        for ($i = 0; $i < 30; $i++) {
            $data = date('Y-m-d', strtotime("+$i days"));
            $dia_semana = date('N', strtotime($data)); // 1=segunda, 7=domingo
            
            // Apenas dias úteis (segunda a sexta)
            if ($dia_semana >= 1 && $dia_semana <= 5) {
                // Horários das 8h às 17h, com intervalos de 1 hora
                for ($hora = 8; $hora < 17; $hora++) {
                    $hora_inicio = sprintf('%02d:00:00', $hora);
                    $hora_fim = sprintf('%02d:00:00', $hora + 1);
                    
                    $stmt = $conexao->prepare("INSERT IGNORE INTO mecanico_agenda (mecanico_id, data_disponivel, hora_inicio, hora_fim, disponivel) VALUES (?, ?, ?, ?, 1)");
                    $stmt->bind_param("isss", $mecanico['id'], $data, $hora_inicio, $hora_fim);
                    $stmt->execute();
                }
            }
        }
    }
    echo "<p>✅ Agenda padrão criada para todos os mecânicos</p>";
}

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
echo "<h3>✅ Migração Concluída!</h3>";
echo "<p>O sistema foi migrado de 'analistas' para 'mecânicos' com sucesso.</p>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Atualizar arquivos PHP para usar 'mecanico' ao invés de 'analista'</li>";
echo "<li>Implementar sistema de calendário para seleção de horários</li>";
echo "<li>Testar o novo sistema de agendamentos</li>";
echo "</ul>";
echo "</div>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #2c3e50; }
p { margin: 10px 0; }
ul { margin: 10px 0; padding-left: 20px; }
</style>