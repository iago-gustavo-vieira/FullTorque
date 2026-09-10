<?php
require_once 'config.php';

// Registra o logout
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    
    try {
        registrarLog('logout', 'Logout realizado com sucesso', $usuario_id);
    } catch (Exception $e) {
        // Ignora erros de log para não impedir o logout
    }
}

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói o cookie da sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();
session_write_close();

// Headers para prevenir cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Redireciona para a página home
header("Location: home.php");
exit;
?>