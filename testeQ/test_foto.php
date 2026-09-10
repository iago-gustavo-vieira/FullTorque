<?php
require_once 'config.php';
verificarLogin();

$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Buscar foto atual
$stmt = $conexao->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo "<h2>Teste de Foto de Perfil</h2>";
echo "<p><strong>Usuario ID:</strong> " . $usuario_id . "</p>";
echo "<p><strong>Foto no banco:</strong> " . ($result['foto_perfil'] ?? 'NULL') . "</p>";

if (!empty($result['foto_perfil'])) {
    $caminho = 'uploads/perfil/' . $result['foto_perfil'];
    echo "<p><strong>Caminho:</strong> " . $caminho . "</p>";
    echo "<p><strong>Arquivo existe:</strong> " . (file_exists($caminho) ? 'SIM' : 'NAO') . "</p>";
    
    if (file_exists($caminho)) {
        echo "<p><strong>Tamanho:</strong> " . filesize($caminho) . " bytes</p>";
        echo "<p><img src='" . $caminho . "' style='max-width: 200px; border: 2px solid #000;'></p>";
    }
}

// Listar arquivos na pasta
echo "<h3>Arquivos na pasta uploads/perfil/:</h3>";
if (is_dir('uploads/perfil/')) {
    $files = scandir('uploads/perfil/');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>Pasta não existe!</p>";
}

$conexao->close();
?>
