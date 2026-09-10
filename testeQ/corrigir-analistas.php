<?php
require_once 'config.php';

$conexao = conectarBD();

// Atualizar nível de acesso dos analistas
$stmt = $conexao->prepare("UPDATE usuarios SET nivel_acesso = 'funcionario' WHERE analista_id IS NOT NULL AND analista_id > 0");
$stmt->execute();

echo "<h2>Correção de Analistas</h2>";
echo "<p>✅ Nível de acesso dos analistas atualizado para 'funcionario'</p>";

// Verificar analistas atualizados
$stmt = $conexao->prepare("SELECT id, nome, email, nivel_acesso, analista_id FROM usuarios WHERE analista_id IS NOT NULL AND analista_id > 0");
$stmt->execute();
$analistas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h3>Analistas Atualizados:</h3>";
foreach ($analistas as $analista) {
    echo "<div style='background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
    echo "<strong>" . $analista['nome'] . "</strong><br>";
    echo "Email: " . $analista['email'] . "<br>";
    echo "Nível: " . $analista['nivel_acesso'] . "<br>";
    echo "Analista ID: " . $analista['analista_id'];
    echo "</div>";
}

$conexao->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2, h3 { color: #2c3e50; }
</style>