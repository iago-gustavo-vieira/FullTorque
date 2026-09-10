<?php
require_once 'config.php';

$conexao = conectarBD();

// Criar tabela de relatórios de clientes
$sql_relatorios_cliente = "
CREATE TABLE IF NOT EXISTS relatorios_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mecanico_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    descricao_problema TEXT,
    endereco_completo TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'analisado', 'respondido') DEFAULT 'pendente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
)";

// Criar tabela de relatórios de mecânicos (resposta)
$sql_relatorios_mecanico = "
CREATE TABLE IF NOT EXISTS relatorios_mecanico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relatorio_cliente_id INT NOT NULL,
    mecanico_id INT NOT NULL,
    diagnostico TEXT NOT NULL,
    servicos_necessarios JSON,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enviado', 'aceito', 'rejeitado') DEFAULT 'enviado',
    FOREIGN KEY (relatorio_cliente_id) REFERENCES relatorios_cliente(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE CASCADE
)";

// Criar tabela de notificações
$sql_notificacoes = "
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('relatorio_cliente', 'relatorio_mecanico', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";

// Executar as queries
try {
    $conexao->query($sql_relatorios_cliente);
    echo "Tabela 'relatorios_cliente' criada com sucesso!<br>";
    
    $conexao->query($sql_relatorios_mecanico);
    echo "Tabela 'relatorios_mecanico' criada com sucesso!<br>";
    
    $conexao->query($sql_notificacoes);
    echo "Tabela 'notificacoes' criada com sucesso!<br>";
    
    // Adicionar campo mecanico_id na tabela usuarios para identificar mecânicos
    $conexao->query("ALTER TABLE usuarios ADD COLUMN mecanico_id INT NULL");
    echo "Campo 'mecanico_id' adicionado à tabela usuarios!<br>";
    
    // Criar usuários mecânicos baseados na tabela mecanicos
    $mecanicos = $conexao->query("SELECT * FROM mecanicos WHERE ativo = 1")->fetch_all(MYSQLI_ASSOC);
    
    foreach ($mecanicos as $mecanico) {
        $email_mecanico = strtolower(str_replace(' ', '', $mecanico['nome'])) . '@autoservice.com';
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        
        $stmt = $conexao->prepare("INSERT IGNORE INTO usuarios (nome, email, telefone, cpf, senha, nivel_acesso, mecanico_id) VALUES (?, ?, '(00) 00000-0000', '000.000.000-00', ?, 'funcionario', ?)");
        $stmt->bind_param("sssi", $mecanico['nome'], $email_mecanico, $senha_hash, $mecanico['id']);
        $stmt->execute();
        
        echo "Usuário mecânico criado: {$mecanico['nome']} - Email: $email_mecanico - Senha: 123456<br>";
    }
    
    echo "<br><strong>Sistema de relatórios criado com sucesso!</strong>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

$conexao->close();
?>