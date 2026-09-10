<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Analistas Existentes:</h2>";

// Buscar usuários que podem ser analistas
$stmt = $conexao->prepare("SELECT id, nome, email, analista_id FROM usuarios WHERE email LIKE '%analista%' OR analista_id IS NOT NULL");
$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($usuarios)) {
    foreach ($usuarios as $usuario) {
        echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>👨🔧 " . $usuario['nome'] . "</h3>";
        echo "<p><strong>Email:</strong> " . $usuario['email'] . "</p>";
        echo "<p><strong>ID:</strong> " . $usuario['id'] . "</p>";
        if ($usuario['analista_id']) {
            echo "<p><strong>Analista ID:</strong> " . $usuario['analista_id'] . "</p>";
        }
        echo "</div>";
    }
} else {
    echo "<p>Nenhum analista encontrado no sistema.</p>";
}

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔑 DADOS DE LOGIN PARA TESTE:</h3>";
echo "<p><strong>Email:</strong> analista@autoservice.com</p>";
echo "<p><strong>Senha:</strong> 123456</p>";
echo "<p><strong>URL Login:</strong> <a href='login.php'>login.php</a></p>";
echo "<p><em>Se não funcionar, use qualquer usuário existente acima.</em></p>";
echo "</div>";

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #2c3e50; }
a { color: #3498db; }
</style>