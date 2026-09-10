<?php
header('Content-Type: application/json');

if (!isset($_GET['cep'])) {
    echo json_encode(['erro' => 'CEP não informado']);
    exit;
}

$cep = preg_replace('/[^0-9]/', '', $_GET['cep']);

if (strlen($cep) != 8) {
    echo json_encode(['erro' => 'CEP inválido']);
    exit;
}

// Buscar CEP na API ViaCEP
$url = "https://viacep.com.br/ws/{$cep}/json/";
$response = @file_get_contents($url);

if ($response === false) {
    echo json_encode(['erro' => 'Erro ao consultar CEP']);
    exit;
}

$data = json_decode($response, true);

if (isset($data['erro'])) {
    echo json_encode(['erro' => 'CEP não encontrado']);
    exit;
}

echo json_encode([
    'cep' => $data['cep'],
    'logradouro' => $data['logradouro'],
    'bairro' => $data['bairro'],
    'localidade' => $data['localidade'],
    'uf' => $data['uf']
]);
?>