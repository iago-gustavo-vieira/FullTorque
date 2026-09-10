<?php
require_once 'config.php';

$conexao = conectarBD();

// Criar tabela se não existir
$conexao->query("CREATE TABLE IF NOT EXISTS promocoes_admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    desconto_percentual INT,
    desconto_valor DECIMAL(10,2),
    codigo_cupom VARCHAR(50),
    data_inicio DATE,
    data_fim DATE,
    ativo BOOLEAN DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Inserir promoções de exemplo
$promocoes = [
    [
        'titulo' => 'SUPER DESCONTO',
        'descricao' => 'Troca de óleo + filtro com desconto especial!',
        'desconto_percentual' => 40,
        'codigo_cupom' => 'OLEO40',
        'data_inicio' => date('Y-m-d'),
        'data_fim' => date('Y-m-d', strtotime('+30 days'))
    ],
    [
        'titulo' => 'CLIENTE VIP',
        'descricao' => 'Revisão completa com preço especial para clientes fiéis!',
        'desconto_valor' => 50.00,
        'codigo_cupom' => 'VIP50',
        'data_inicio' => date('Y-m-d'),
        'data_fim' => date('Y-m-d', strtotime('+7 days'))
    ],
    [
        'titulo' => 'PACOTE COMPLETO',
        'descricao' => 'Alinhamento + Balanceamento + Calibragem',
        'desconto_percentual' => 30,
        'codigo_cupom' => 'COMBO30',
        'data_inicio' => date('Y-m-d'),
        'data_fim' => date('Y-m-d', strtotime('+60 days'))
    ]
];

foreach ($promocoes as $promo) {
    $stmt = $conexao->prepare("INSERT INTO promocoes_admin (titulo, descricao, desconto_percentual, desconto_valor, codigo_cupom, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidsss", $promo['titulo'], $promo['descricao'], $promo['desconto_percentual'], $promo['desconto_valor'], $promo['codigo_cupom'], $promo['data_inicio'], $promo['data_fim']);
    $stmt->execute();
}

echo "Promoções de exemplo criadas com sucesso!";
$conexao->close();
?>