<!DOCTYPE html>
<html>
<head>
    <title>Atualização do Banco de Dados</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #109349; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #109349; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Atualização do Banco de Dados</h1>
        <p>Adicionando campos para agendamento de diagnósticos...</p>
        
<?php
require_once 'config.php';

$conexao = conectarBD();

// Verificar se a tabela existe
$check_table = $conexao->query("SHOW TABLES LIKE 'relatorios_cliente'");
if ($check_table->num_rows == 0) {
    echo "<div class='error'>❌ Tabela 'relatorios_cliente' não encontrada!</div>";
    $conexao->close();
    echo "</div></body></html>";
    exit;
}

echo "<div class='info'>ℹ️ Tabela 'relatorios_cliente' encontrada.</div>";
echo "<div class='info'>🔍 Verificando campos existentes...</div>";

// Verificar se os campos já existem
$check_data = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'data_agendamento'");
$check_hora = $conexao->query("SHOW COLUMNS FROM relatorios_cliente LIKE 'hora_agendamento'");

if ($check_data->num_rows > 0) {
    echo "<div class='info'>ℹ️ Campo data_agendamento já existe.</div>";
} else {
    if ($conexao->query("ALTER TABLE relatorios_cliente ADD COLUMN data_agendamento DATE NULL")) {
        echo "<div class='success'>✅ Campo data_agendamento adicionado com sucesso!</div>";
    } else {
        echo "<div class='error'>❌ Erro ao adicionar data_agendamento: " . $conexao->error . "</div>";
    }
}

if ($check_hora->num_rows > 0) {
    echo "<div class='info'>ℹ️ Campo hora_agendamento já existe.</div>";
} else {
    if ($conexao->query("ALTER TABLE relatorios_cliente ADD COLUMN hora_agendamento TIME NULL")) {
        echo "<div class='success'>✅ Campo hora_agendamento adicionado com sucesso!</div>";
    } else {
        echo "<div class='error'>❌ Erro ao adicionar hora_agendamento: " . $conexao->error . "</div>";
    }
}

$conexao->close();
echo "<div class='success'><strong>✅ Atualização concluída com sucesso!</strong></div>";
echo "<p>Agora você pode usar o sistema de agendamento com validação de horários.</p>";
echo "<a href='agendamento-diagnostico.php' class='btn'>Ir para Agendamento de Diagnóstico</a>";
echo "</div></body></html>";
?>
