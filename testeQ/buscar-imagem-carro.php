<?php
require_once 'config.php';

/**
 * Busca uma imagem de carro com base na marca e modelo
 * 
 * @param string $marca Marca do carro
 * @param string $modelo Modelo do carro
 * @return string URL da imagem do carro
 */
function buscarImagemCarro($marca, $modelo) {
    // Normaliza os parâmetros
    $marca = strtolower(trim($marca));
    $modelo = strtolower(trim($modelo));
    
    // Conecta ao banco de dados
    $conexao = conectarBD();
    
    // Busca a imagem no banco de dados
    $stmt = $conexao->prepare("SELECT url FROM imagens_carros WHERE marca = ? AND modelo = ? LIMIT 1");
    $stmt->bind_param("ss", $marca, $modelo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conexao->close();
        return $row['url'];
    }
    
    // Se não encontrou uma imagem específica, busca uma imagem da mesma marca
    $stmt = $conexao->prepare("SELECT url FROM imagens_carros WHERE marca = ? LIMIT 1");
    $stmt->bind_param("s", $marca);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conexao->close();
        return $row['url'];
    }
    
    // Se não encontrou nenhuma imagem, busca na API externa
    $imagem_url = buscarImagemAPI($marca, $modelo);
    
    // Se encontrou uma imagem na API, salva no banco de dados
    if ($imagem_url && $imagem_url !== 'https://example.com/images/default-car.jpg') {
        $stmt = $conexao->prepare("INSERT IGNORE INTO imagens_carros (marca, modelo, url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $marca, $modelo, $imagem_url);
        $stmt->execute();
    }
    
    $conexao->close();
    return $imagem_url;
}

/**
 * Busca uma imagem de carro da API externa
 * 
 * @param string $marca Marca do carro
 * @param string $modelo Modelo do carro
 * @return string URL da imagem do carro
 */
function buscarImagemAPI($marca, $modelo) {
    // Aqui você pode implementar a chamada para uma API real de imagens de carros
    // Por enquanto, vamos usar um mapeamento simples
    
    // Mapeamento de marcas e modelos para imagens
    $imagensCarros = [
        'volkswagen' => [
            'gol' => 'https://example.com/images/volkswagen-gol.jpg',
            'golf' => 'https://example.com/images/volkswagen-golf.jpg',
            'polo' => 'https://example.com/images/volkswagen-polo.jpg',
            'jetta' => 'https://example.com/images/volkswagen-jetta.jpg',
            'default' => 'https://example.com/images/volkswagen-default.jpg'
        ],
        'fiat' => [
            'uno' => 'https://example.com/images/fiat-uno.jpg',
            'palio' => 'https://example.com/images/fiat-palio.jpg',
            'argo' => 'https://example.com/images/fiat-argo.jpg',
            'cronos' => 'https://example.com/images/fiat-cronos.jpg',
            'default' => 'https://example.com/images/fiat-default.jpg'
        ],
        'chevrolet' => [
            'onix' => 'https://example.com/images/chevrolet-onix.jpg',
            'cruze' => 'https://example.com/images/chevrolet-cruze.jpg',
            'prisma' => 'https://example.com/images/chevrolet-prisma.jpg',
            'tracker' => 'https://example.com/images/chevrolet-tracker.jpg',
            'default' => 'https://example.com/images/chevrolet-default.jpg'
        ],
        'ford' => [
            'ka' => 'https://example.com/images/ford-ka.jpg',
            'fiesta' => 'https://example.com/images/ford-fiesta.jpg',
            'focus' => 'https://example.com/images/ford-focus.jpg',
            'ecosport' => 'https://example.com/images/ford-ecosport.jpg',
            'default' => 'https://example.com/images/ford-default.jpg'
        ],
        'toyota' => [
            'corolla' => 'https://example.com/images/toyota-corolla.jpg',
            'yaris' => 'https://example.com/images/toyota-yaris.jpg',
            'hilux' => 'https://example.com/images/toyota-hilux.jpg',
            'etios' => 'https://example.com/images/toyota-etios.jpg',
            'default' => 'https://example.com/images/toyota-default.jpg'
        ],
        'honda' => [
            'civic' => 'https://example.com/images/honda-civic.jpg',
            'fit' => 'https://example.com/images/honda-fit.jpg',
            'hr-v' => 'https://example.com/images/honda-hrv.jpg',
            'city' => 'https://example.com/images/honda-city.jpg',
            'default' => 'https://example.com/images/honda-default.jpg'
        ],
        'hyundai' => [
            'hb20' => 'https://example.com/images/hyundai-hb20.jpg',
            'creta' => 'https://example.com/images/hyundai-creta.jpg',
            'tucson' => 'https://example.com/images/hyundai-tucson.jpg',
            'i30' => 'https://example.com/images/hyundai-i30.jpg',
            'default' => 'https://example.com/images/hyundai-default.jpg'
        ],
        'renault' => [
            'kwid' => 'https://example.com/images/renault-kwid.jpg',
            'sandero' => 'https://example.com/images/renault-sandero.jpg',
            'logan' => 'https://example.com/images/renault-logan.jpg',
            'duster' => 'https://example.com/images/renault-duster.jpg',
            'default' => 'https://example.com/images/renault-default.jpg'
        ],
        'nissan' => [
            'versa' => 'https://example.com/images/nissan-versa.jpg',
            'kicks' => 'https://example.com/images/nissan-kicks.jpg',
            'sentra' => 'https://example.com/images/nissan-sentra.jpg',
            'march' => 'https://example.com/images/nissan-march.jpg',
            'default' => 'https://example.com/images/nissan-default.jpg'
        ],
        'default' => 'https://example.com/images/default-car.jpg'
    ];
    
    // Busca a imagem específica do modelo
    if (isset($imagensCarros[$marca][$modelo])) {
        return $imagensCarros[$marca][$modelo];
    }
    
    // Busca a imagem padrão da marca
    if (isset($imagensCarros[$marca]['default'])) {
        return $imagensCarros[$marca]['default'];
    }
    
    // Retorna a imagem padrão
    return $imagensCarros['default'];
}

/**
 * Atualiza a imagem de um veículo no banco de dados
 * 
 * @param int $veiculo_id ID do veículo
 * @param string $imagem_url URL da imagem
 * @return bool True se atualizou com sucesso, false caso contrário
 */
function atualizarImagemVeiculo($veiculo_id, $imagem_url) {
    $conexao = conectarBD();
    $stmt = $conexao->prepare("UPDATE veiculos SET imagem_url = ? WHERE id = ?");
    $stmt->bind_param("si", $imagem_url, $veiculo_id);
    $resultado = $stmt->execute();
    $conexao->close();
    
    return $resultado;
}

// Se o arquivo for chamado diretamente, processa a requisição AJAX
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
    
    if (isset($_GET['marca']) && isset($_GET['modelo'])) {
        $marca = $_GET['marca'];
        $modelo = $_GET['modelo'];
        
        $imagem_url = buscarImagemCarro($marca, $modelo);
        
        echo json_encode(['url' => $imagem_url]);
    } elseif (isset($_GET['veiculo_id'])) {
        $veiculo_id = (int)$_GET['veiculo_id'];
        
        // Busca informações do veículo
        $conexao = conectarBD();
        $stmt = $conexao->prepare("SELECT marca, modelo FROM veiculos WHERE id = ?");
        $stmt->bind_param("i", $veiculo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $veiculo = $result->fetch_assoc();
            $imagem_url = buscarImagemCarro($veiculo['marca'], $veiculo['modelo']);
            
            // Atualiza a imagem do veículo
            atualizarImagemVeiculo($veiculo_id, $imagem_url);
            
            echo json_encode(['url' => $imagem_url]);
        } else {
            echo json_encode(['error' => 'Veículo não encontrado']);
        }
        
        $conexao->close();
    } else {
        echo json_encode(['error' => 'Parâmetros inválidos']);
    }
}
?>