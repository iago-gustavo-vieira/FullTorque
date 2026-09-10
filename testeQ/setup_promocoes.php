<?php
require_once 'config.php';

$conexao = conectarBD();

// SQL para criar as tabelas
$sql = "
-- Tabela de configurações do programa de pontos
CREATE TABLE IF NOT EXISTS programa_pontos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pontos_por_real DECIMAL(5,2) DEFAULT 1.00,
    valor_ponto DECIMAL(5,2) DEFAULT 0.10,
    pontos_minimos_resgate INT DEFAULT 100,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de pontos dos clientes
CREATE TABLE IF NOT EXISTS cliente_pontos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    pontos_totais INT DEFAULT 0,
    pontos_disponiveis INT DEFAULT 0,
    pontos_utilizados INT DEFAULT 0,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de histórico de pontos
CREATE TABLE IF NOT EXISTS historico_pontos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    agendamento_id INT NULL,
    tipo ENUM('ganho', 'resgate', 'expiracao') NOT NULL,
    pontos INT NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de promoções
CREATE TABLE IF NOT EXISTS promocoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo ENUM('desconto_percentual', 'desconto_fixo', 'servico_gratis', 'cupom_primeira_revisao') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    codigo_cupom VARCHAR(50) UNIQUE,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    limite_uso INT DEFAULT NULL,
    usos_atual INT DEFAULT 0,
    servicos_aplicaveis TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de uso de promoções
CREATE TABLE IF NOT EXISTS promocoes_uso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    promocao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    agendamento_id INT NULL,
    valor_desconto DECIMAL(10,2),
    data_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de notificações de promoções
CREATE TABLE IF NOT EXISTS notificacoes_promocoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    promocao_id INT NOT NULL,
    usuario_id INT NULL,
    tipo ENUM('email', 'whatsapp', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    enviado BOOLEAN DEFAULT FALSE,
    data_envio TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
";

// Executar cada comando SQL separadamente
$comandos = explode(';', $sql);
$sucesso = true;

foreach ($comandos as $comando) {
    $comando = trim($comando);
    if (!empty($comando)) {
        if (!$conexao->query($comando)) {
            echo "Erro ao executar: " . $comando . "<br>";
            echo "Erro MySQL: " . $conexao->error . "<br><br>";
            $sucesso = false;
        }
    }
}

if ($sucesso) {
    // Inserir dados iniciais
    $conexao->query("INSERT IGNORE INTO programa_pontos (pontos_por_real, valor_ponto, pontos_minimos_resgate) VALUES (1.00, 0.10, 100)");
    
    $conexao->query("INSERT IGNORE INTO promocoes (titulo, descricao, tipo, valor, codigo_cupom, data_inicio, data_fim) VALUES ('Cupom Primeira Revisão', 'Desconto especial de 20% para novos clientes na primeira revisão', 'desconto_percentual', 20.00, 'PRIMEIRA20', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
    
    echo "<h2>✅ Tabelas de promoções criadas com sucesso!</h2>";
    echo "<p><a href='admin-promocoes.php'>Ir para Gerenciar Promoções</a></p>";
} else {
    echo "<h2>❌ Erro ao criar algumas tabelas</h2>";
}

$conexao->close();
?>