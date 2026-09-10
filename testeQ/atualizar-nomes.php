<?php
require_once 'config.php';

$conexao = conectarBD();

// Buscar todos os usuários
$result = $conexao->query("SELECT id, nome FROM usuarios");

$atualizados = 0;
while ($usuario = $result->fetch_assoc()) {
    $nome_completo = $usuario['nome'];
    $primeiro_nome = explode(' ', trim($nome_completo))[0];
    
    if ($primeiro_nome != $nome_completo) {
        $stmt = $conexao->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
        $stmt->bind_param("si", $primeiro_nome, $usuario['id']);
        $stmt->execute();
        $atualizados++;
    }
}

$conexao->close();

echo "Atualização concluída! $atualizados nomes foram resumidos para o primeiro nome.";
?>
