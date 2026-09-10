<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Criando Sistema de Agenda dos Mecânicos</h2>";

// 1. Criar tabela de agenda dos mecânicos
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
    UNIQUE KEY unique_agenda (mecanico_id, data_disponivel, hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conexao->query($sql)) {
    echo "<p>✅ Tabela 'mecanico_agenda' criada com sucesso</p>";
} else {
    echo "<p>⚠️ Erro ao criar tabela agenda: " . $conexao->error . "</p>";
}

// 2. Criar tabela de agendamentos de diagnósticos
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
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conexao->query($sql)) {
    echo "<p>✅ Tabela 'diagnostico_agendamentos' criada com sucesso</p>";
} else {
    echo "<p>⚠️ Erro ao criar tabela agendamentos: " . $conexao->error . "</p>";
}

// 3. Verificar se já existem horários na agenda
$result = $conexao->query("SELECT COUNT(*) as total FROM mecanico_agenda");
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    // Buscar mecânicos (pode ser da tabela mecanicos ou analistas)
    $mecanicos_query = $conexao->query("SHOW TABLES LIKE 'mecanicos'");
    if ($mecanicos_query->num_rows > 0) {
        $mecanicos = $conexao->query("SELECT id FROM mecanicos WHERE ativo = 1");
    } else {
        $mecanicos = $conexao->query("SELECT id FROM analistas WHERE ativo = 1");
    }
    
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
} else {
    echo "<p>✅ Agenda já possui horários cadastrados ({$row['total']} registros)</p>";
}

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
echo "<h3>✅ Sistema de Agenda Criado!</h3>";
echo "<p>O sistema de agenda foi configurado com sucesso.</p>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Acesse <a href='agendamento-diagnostico.php'>agendamento-diagnostico.php</a> para testar</li>";
echo "<li>Acesse <a href='mecanico-dashboard.php'>mecanico-dashboard.php</a> como mecânico</li>";
echo "</ul>";
echo "</div>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #2c3e50; }
p { margin: 10px 0; }
ul { margin: 10px 0; padding-left: 20px; }
a { color: #3498db; }
</style>