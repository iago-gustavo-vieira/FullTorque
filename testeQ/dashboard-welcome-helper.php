<?php
/**
 * Dashboard Welcome Helper - FullTorque
 * Helper para integrar o texto de boas-vindas com nome do usuário
 */

/**
 * Inclui os arquivos CSS e JS necessários para o dashboard welcome
 */
function include_dashboard_welcome_assets() {
    echo '<link rel="stylesheet" href="dashboard-welcome-global.css">' . "\n";
    echo '<script src="dashboard-welcome-user.js"></script>' . "\n";
}

/**
 * Gera o script para definir o nome do usuário
 * @param string $userName Nome do usuário logado
 */
function set_dashboard_welcome_user($userName = '') {
    // Tentar obter o nome do usuário de diferentes fontes
    if (empty($userName)) {
        if (isset($_SESSION['usuario_nome'])) {
            $userName = $_SESSION['usuario_nome'];
        } elseif (isset($_SESSION['nome'])) {
            $userName = $_SESSION['nome'];
        } elseif (isset($_SESSION['user_name'])) {
            $userName = $_SESSION['user_name'];
        } elseif (isset($_SESSION['nome_usuario'])) {
            $userName = $_SESSION['nome_usuario'];
        }
    }
    
    // Limpar e formatar o nome
    $userName = trim($userName);
    if (!empty($userName)) {
        // Pegar apenas o primeiro nome para o texto de boas-vindas
        $firstName = explode(' ', $userName)[0];
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        
        echo '<script>' . "\n";
        echo 'document.addEventListener("DOMContentLoaded", function() {' . "\n";
        echo '    if (typeof setUserName === "function") {' . "\n";
        echo '        setUserName("' . $firstName . '");' . "\n";
        echo '    } else {' . "\n";
        echo '        localStorage.setItem("userName", "' . $firstName . '");' . "\n";
        echo '    }' . "\n";
        echo '});' . "\n";
        echo '</script>' . "\n";
        
        // Também adicionar como meta tag para fallback
        echo '<meta name="user-name" content="' . $firstName . '">' . "\n";
    }
}

/**
 * Gera o HTML completo do dashboard welcome com texto de boas-vindas
 * @param string $userName Nome do usuário (opcional)
 * @param array $options Opções adicionais (classe, estilo, etc.)
 */
function render_dashboard_welcome($userName = '', $options = []) {
    $defaultOptions = [
        'class' => 'dashboard-welcome',
        'show_welcome_message' => true,
        'show_actions' => true
    ];
    
    $options = array_merge($defaultOptions, $options);
    
    echo '<div class="' . htmlspecialchars($options['class']) . '">' . "\n";
    
    // Texto de boas-vindas será adicionado via JavaScript
    
    if ($options['show_welcome_message']) {
        echo '    <div class="welcome-message">' . "\n";
        echo '        <h2><span class="text-white">Bem-vindo à</span> <span class="text-green">FullTorque</span></h2>' . "\n";
        echo '        <p>Sua oficina de confiança com qualidade italiana</p>' . "\n";
        echo '    </div>' . "\n";
    }
    
    if ($options['show_actions']) {
        echo '    <div class="welcome-actions">' . "\n";
        echo '        <a href="agendamento-novo.php" class="btn">' . "\n";
        echo '            <i class="fas fa-calendar-plus"></i> Novo Agendamento' . "\n";
        echo '        </a>' . "\n";
        echo '        <a href="veiculos.php" class="btn">' . "\n";
        echo '            <i class="fas fa-car"></i> Meus Veículos' . "\n";
        echo '        </a>' . "\n";
        echo '    </div>' . "\n";
    }
    
    echo '</div>' . "\n";
    
    // Definir o nome do usuário
    set_dashboard_welcome_user($userName);
}

/**
 * Função para uso em templates/páginas existentes
 * Adiciona apenas o script de nome do usuário
 */
function init_dashboard_welcome_user($userName = '') {
    set_dashboard_welcome_user($userName);
}

/**
 * Verifica se está em modo mobile/responsivo
 */
function is_mobile_view() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone'];
    
    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Adiciona os estilos CSS inline para o dashboard welcome (caso necessário)
 */
function add_dashboard_welcome_inline_styles() {
    echo '<style>' . "\n";
    echo '@media (max-width: 768px) {' . "\n";
    echo '    .dashboard-welcome-text { display: block !important; }' . "\n";
    echo '}' . "\n";
    echo '@media (min-width: 769px) {' . "\n";
    echo '    .dashboard-welcome-text { display: none !important; }' . "\n";
    echo '}' . "\n";
    echo '</style>' . "\n";
}

/**
 * Função para debug - mostra informações sobre o usuário
 */
function debug_dashboard_welcome_user() {
    if (defined('DEBUG') && DEBUG) {
        echo '<!-- Dashboard Welcome Debug -->' . "\n";
        echo '<!-- Session Data: ' . print_r($_SESSION, true) . ' -->' . "\n";
        echo '<!-- User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . ' -->' . "\n";
        echo '<!-- Is Mobile: ' . (is_mobile_view() ? 'Yes' : 'No') . ' -->' . "\n";
    }
}
?>