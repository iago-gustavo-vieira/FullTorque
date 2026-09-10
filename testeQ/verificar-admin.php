<?php
require_once 'config.php';

$conexao = conectarBD();

echo "<h2>Verificando usuários admin no banco:</h2>";

$result = $conexao->query("SELECT id, nome, email, nivel_acesso FROM usuarios WHERE email = 'admin@fulltorque.com' OR nivel_acesso = 'admin'");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p>Nenhum usuário admin encontrado!</p>";
    echo "<p><a href='criar-admin.php'>Criar usuário admin</a></p>";
}

$conexao->close();
?>
