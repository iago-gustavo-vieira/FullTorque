<?php
// EXEMPLO DE CONFIGURAÇÃO — copie este arquivo para config.remote.php (produção)
// ou config.local.php (desenvolvimento) e preencha com seus dados reais.
// NUNCA suba config.remote.php ou config.local.php preenchidos para o GitHub.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do banco de dados
define('DB_HOST', 'seu_host_aqui');
define('DB_USER', 'seu_usuario_aqui');
define('DB_PASS', 'sua_senha_aqui');
define('DB_NAME', 'seu_banco_aqui');

// Configurações gerais do sistema
define('SISTEMA_NOME', 'Full Torque');
define('SISTEMA_VERSAO', '1.0.0');
define('TIMEZONE', 'America/Sao_Paulo');
define('DEBUG_MODE', false); // deixe true apenas em ambiente local

// Paleta de cores do sistema
define('COR_PRIMARIA', '#34c2dbff');
define('COR_SECUNDARIA', '#2c3e50');
define('COR_TERCIARIA', '#ecf0f1');
define('COR_DESTAQUE', '#e74c3c');
define('COR_SUCESSO', '#2ecc71');
define('COR_ALERTA', '#f39c12');
define('COR_ERRO', '#e74c3c');
define('COR_TEXTO', '#333333');

date_default_timezone_set(TIMEZONE);

function conectarBD() {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conexao->set_charset("utf8");
        return $conexao;
    } catch (Exception $e) {
        die("Erro: " . $e->getMessage());
    }
}

function limparDados($dados) {
    if ($dados === null) return '';
    return htmlspecialchars(stripslashes(trim($dados)));
}

function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: home.php");
        exit;
    }
}

function verificarPermissao($permissao_necessaria) {
    $tem_permissao_array = isset($_SESSION['usuario_permissoes']) && in_array($permissao_necessaria, $_SESSION['usuario_permissoes']);
    $tem_permissao_nivel = isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === $permissao_necessaria;

    if (!$tem_permissao_array && !$tem_permissao_nivel) {
        header("Location: acesso-negado.php");
        exit;
    }
}

function registrarLog($acao, $descricao, $usuario_id = null) {
    if ($usuario_id === null && isset($_SESSION['usuario_id'])) {
        $usuario_id = $_SESSION['usuario_id'];
    }

    try {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("INSERT INTO logs (usuario_id, acao, descricao, ip, data_hora) VALUES (?, ?, ?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $usuario_id, $acao, $descricao, $ip);
        $stmt->execute();
        $conexao->close();
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

function exibirAlerta($tipo, $mensagem) {
    $_SESSION['alerta'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function mostrarAlerta() {
    if (isset($_SESSION['alerta'])) {
        echo "<div class='alert alert-{$_SESSION['alerta']['tipo']}'>{$_SESSION['alerta']['mensagem']}</div>";
        unset($_SESSION['alerta']);
    }
}

function colunaExiste($conexao, $tabela, $coluna) {
    $result = $conexao->query("SHOW COLUMNS FROM `$tabela` LIKE '$coluna'");
    return $result && $result->num_rows > 0;
}

function tabelaExiste($conexao, $tabela) {
    $result = $conexao->query("SHOW TABLES LIKE '$tabela'");
    return $result && $result->num_rows > 0;
}

function formatarData($data, $formato = 'd/m/Y H:i') {
    return empty($data) ? '-' : date($formato, strtotime($data));
}

function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function gerarSlug($string) {
    $string = preg_replace('/[áàãâä]/ui', 'a', $string);
    $string = preg_replace('/[éèêë]/ui', 'e', $string);
    $string = preg_replace('/[íìîï]/ui', 'i', $string);
    $string = preg_replace('/[óòõôö]/ui', 'o', $string);
    $string = preg_replace('/[úùûü]/ui', 'u', $string);
    $string = preg_replace('/[ç]/ui', 'c', $string);
    $string = preg_replace('/[^a-z0-9]/i', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return strtolower(trim($string, '-'));
}

function verificarEmailReal($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

function tratarErro($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>Erro [$errno]:</strong> $errstr<br>Arquivo: $errfile na linha $errline</div>";
    }
    return true;
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    set_error_handler("tratarErro");
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
