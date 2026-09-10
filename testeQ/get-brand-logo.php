<?php
function getBrandLogo($marca) {
    $marca = strtolower(trim($marca));
    
    // Normalizar nomes de marcas comuns
    $normalizacao = [
        'vw' => 'volkswagen',
        'gm' => 'chevrolet',
        'mercedes' => 'mercedes-benz',
        'benz' => 'mercedes-benz'
    ];
    
    if (isset($normalizacao[$marca])) {
        $marca = $normalizacao[$marca];
    }
    
    // Usar API do Logo.dev que é mais confiável
    $apiUrl = "https://img.logo.dev/{$marca}.com?token=pk_X-1ZO13ESEOOkMW57_bA&format=png&size=200";
    
    // Lista de marcas suportadas
    $marcasSuportadas = [
        'bmw', 'mercedes', 'mercedes-benz', 'audi', 'volkswagen', 'vw', 'ford', 
        'chevrolet', 'toyota', 'honda', 'nissan', 'hyundai', 'kia', 'fiat', 
        'renault', 'peugeot', 'citroen', 'jeep', 'mitsubishi', 'subaru', 'mazda', 
        'volvo', 'porsche', 'ferrari', 'lamborghini', 'jaguar', 'mini', 'lexus', 
        'tesla', 'suzuki', 'seat', 'skoda', 'opel', 'smart', 'dacia', 'lada'
    ];
    
    // Fallback para logos locais se a API não funcionar
    $logosFallback = [
        'bmw' => 'https://cdn.worldvectorlogo.com/logos/bmw.svg',
        'mercedes' => 'https://cdn.worldvectorlogo.com/logos/mercedes-benz-9.svg',
        'mercedes-benz' => 'https://cdn.worldvectorlogo.com/logos/mercedes-benz-9.svg',
        'audi' => 'https://cdn.worldvectorlogo.com/logos/audi-2.svg',
        'volkswagen' => 'https://cdn.worldvectorlogo.com/logos/volkswagen-vw.svg',
        'vw' => 'https://cdn.worldvectorlogo.com/logos/volkswagen-vw.svg',
        'ford' => 'https://cdn.worldvectorlogo.com/logos/ford-6.svg',
        'chevrolet' => 'https://cdn.worldvectorlogo.com/logos/chevrolet-2.svg',
        'toyota' => 'https://cdn.worldvectorlogo.com/logos/toyota-6.svg',
        'honda' => 'https://cdn.worldvectorlogo.com/logos/honda-2.svg',
        'nissan' => 'https://cdn.worldvectorlogo.com/logos/nissan-6.svg',
        'hyundai' => 'https://cdn.worldvectorlogo.com/logos/hyundai-motor-company.svg',
        'kia' => 'https://cdn.worldvectorlogo.com/logos/kia-motors-1.svg',
        'fiat' => 'https://cdn.worldvectorlogo.com/logos/fiat.svg',
        'renault' => 'https://cdn.worldvectorlogo.com/logos/renault-2.svg',
        'peugeot' => 'https://cdn.worldvectorlogo.com/logos/peugeot-2.svg',
        'citroen' => 'https://cdn.worldvectorlogo.com/logos/citroen-2.svg',
        'jeep' => 'https://cdn.worldvectorlogo.com/logos/jeep.svg',
        'mitsubishi' => 'https://cdn.worldvectorlogo.com/logos/mitsubishi-motors.svg',
        'subaru' => 'https://cdn.worldvectorlogo.com/logos/subaru-2.svg',
        'mazda' => 'https://cdn.worldvectorlogo.com/logos/mazda-2.svg',
        'volvo' => 'https://cdn.worldvectorlogo.com/logos/volvo-2.svg',
        'porsche' => 'https://cdn.worldvectorlogo.com/logos/porsche-3.svg',
        'ferrari' => 'https://cdn.worldvectorlogo.com/logos/ferrari-2.svg',
        'lamborghini' => 'https://cdn.worldvectorlogo.com/logos/lamborghini.svg',
        'jaguar' => 'https://cdn.worldvectorlogo.com/logos/jaguar-2.svg',
        'mini' => 'https://cdn.worldvectorlogo.com/logos/mini-3.svg',
        'lexus' => 'https://cdn.worldvectorlogo.com/logos/lexus-2.svg',
        'tesla' => 'https://cdn.worldvectorlogo.com/logos/tesla-9.svg',
        'suzuki' => 'https://cdn.worldvectorlogo.com/logos/suzuki-2.svg',
        // Marcas brasileiras e populares
        'gm' => 'https://cdn.worldvectorlogo.com/logos/general-motors-gm.svg',
        'general motors' => 'https://cdn.worldvectorlogo.com/logos/general-motors-gm.svg',
        'land rover' => 'https://cdn.worldvectorlogo.com/logos/land-rover.svg',
        'range rover' => 'https://cdn.worldvectorlogo.com/logos/land-rover.svg',
        'acura' => 'https://cdn.worldvectorlogo.com/logos/acura-3.svg',
        'infiniti' => 'https://cdn.worldvectorlogo.com/logos/infiniti-2.svg',
        'cadillac' => 'https://cdn.worldvectorlogo.com/logos/cadillac-2.svg',
        'buick' => 'https://cdn.worldvectorlogo.com/logos/buick-2.svg',
        'dodge' => 'https://cdn.worldvectorlogo.com/logos/dodge-2.svg',
        'chrysler' => 'https://cdn.worldvectorlogo.com/logos/chrysler-2.svg',
        'ram' => 'https://cdn.worldvectorlogo.com/logos/ram-trucks.svg',
        'lincoln' => 'https://cdn.worldvectorlogo.com/logos/lincoln-motor-company.svg',
        'alfa romeo' => 'https://cdn.worldvectorlogo.com/logos/alfa-romeo.svg',
        'maserati' => 'https://cdn.worldvectorlogo.com/logos/maserati-2.svg',
        'bentley' => 'https://cdn.worldvectorlogo.com/logos/bentley-2.svg',
        'rolls royce' => 'https://cdn.worldvectorlogo.com/logos/rolls-royce-2.svg',
        'aston martin' => 'https://cdn.worldvectorlogo.com/logos/aston-martin-2.svg',
        'mclaren' => 'https://cdn.worldvectorlogo.com/logos/mclaren-2.svg',
        'bugatti' => 'https://cdn.worldvectorlogo.com/logos/bugatti-2.svg'
    ];
    
    // Primeiro tenta o fallback
    if (isset($logosFallback[$marca])) {
        return $logosFallback[$marca];
    }
    
    // Se não encontrar, retorna null
    return null;
}
?>