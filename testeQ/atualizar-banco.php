<?php
require_once 'config.php';

// Conecta ao banco de dados
$conexao = conectarBD();

// Adiciona a coluna imagem_url à tabela veiculos se ela não existir
$sql = "SHOW COLUMNS FROM veiculos LIKE 'imagem_url'";
$result = $conexao->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE veiculos ADD COLUMN imagem_url VARCHAR(255) DEFAULT NULL AFTER observacoes";
    if ($conexao->query($sql) === TRUE) {
        echo "Coluna imagem_url adicionada com sucesso à tabela veiculos.<br>";
    } else {
        echo "Erro ao adicionar coluna imagem_url: " . $conexao->error . "<br>";
    }
}

// Cria a tabela de imagens de carros se ela não existir
$sql = "CREATE TABLE IF NOT EXISTS imagens_carros (
    id INT(11) NOT NULL AUTO_INCREMENT,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY marca_modelo (marca, modelo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conexao->query($sql) === TRUE) {
    echo "Tabela imagens_carros criada com sucesso.<br>";
} else {
    echo "Erro ao criar tabela imagens_carros: " . $conexao->error . "<br>";
}

// Insere algumas imagens de carros populares
$imagens = [
    ['volkswagen', 'gol', 'https://example.com/images/volkswagen-gol.jpg'],
    ['volkswagen', 'golf', 'https://example.com/images/volkswagen-golf.jpg'],
    ['fiat', 'uno', 'https://example.com/images/fiat-uno.jpg'],
    ['fiat', 'palio', 'https://example.com/images/fiat-palio.jpg'],
    ['chevrolet', 'onix', 'https://example.com/images/chevrolet-onix.jpg'],
    ['chevrolet', 'cruze', 'https://example.com/images/chevrolet-cruze.jpg'],
    ['ford', 'ka', 'https://example.com/images/ford-ka.jpg'],
    ['ford', 'fiesta', 'https://example.com/images/ford-fiesta.jpg'],
    ['toyota', 'corolla', 'https://example.com/images/toyota-corolla.jpg'],
    ['toyota', 'hilux', 'https://example.com/images/toyota-hilux.jpg'],
    ['honda', 'civic', 'https://example.com/images/honda-civic.jpg'],
    ['honda', 'fit', 'https://example.com/images/honda-fit.jpg'],
    ['hyundai', 'hb20', 'https://example.com/images/hyundai-hb20.jpg'],
    ['hyundai', 'creta', 'https://example.com/images/hyundai-creta.jpg'],
    ['renault', 'kwid', 'https://example.com/images/renault-kwid.jpg'],
    ['renault', 'sandero', 'https://example.com/images/renault-sandero.jpg']
];

$stmt = $conexao->prepare("INSERT IGNORE INTO imagens_carros (marca, modelo, url) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $marca, $modelo, $url);

foreach ($imagens as $imagem) {
    $marca = strtolower($imagem[0]);
    $modelo = strtolower($imagem[1]);
    $url = $imagem[2];
    $stmt->execute();
}

echo "Imagens de carros inseridas com sucesso.<br>";

// Atualiza as imagens dos veículos existentes
$sql = "UPDATE veiculos v 
        LEFT JOIN imagens_carros i ON LOWER(v.marca) = i.marca AND LOWER(v.modelo) = i.modelo 
        SET v.imagem_url = i.url 
        WHERE v.imagem_url IS NULL AND i.url IS NOT NULL";

if ($conexao->query($sql) === TRUE) {
    echo "Imagens dos veículos atualizadas com sucesso.<br>";
} else {
    echo "Erro ao atualizar imagens dos veículos: " . $conexao->error . "<br>";
}

// Fecha a conexão
$conexao->close();

echo "<br>Atualização do banco de dados concluída!";
echo "<br><a href='veiculos.php'>Voltar para a página de veículos</a>";
?>