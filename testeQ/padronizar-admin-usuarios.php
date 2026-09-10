<?php
// Script para adicionar o header padrão no admin-usuarios.php
$file = 'admin-usuarios.php';
$content = file_get_contents($file);

// Verificar se já tem o mobile-welcome-text
if (strpos($content, 'mobile-welcome-text') === false) {
    // Procurar pela div page-header ou similar e adicionar o mobile-welcome-text
    $search = '<div class="page-header">';
    $replace = '<div class="page-header">
        <div class="mobile-welcome-text">Gerenciar Usuários</div>';
    
    $content = str_replace($search, $replace, $content);
    
    file_put_contents($file, $content);
    echo "✅ Header mobile adicionado com sucesso!<br>";
} else {
    echo "✓ Header mobile já existe<br>";
}

echo "<br><a href='admin-usuarios.php'>→ Ir para Admin Usuários</a>";
?>
