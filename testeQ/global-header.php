<?php
// Header global responsivo para todas as páginas
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Contar notificações não lidas
$notificacoes_nao_lidas = 0;
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

// Verificar se tabelas existem
$tabela_notificacoes = $conexao->query("SHOW TABLES LIKE 'notificacoes'")->num_rows > 0;
$tabela_promocoes = $conexao->query("SHOW TABLES LIKE 'notificacoes_promocoes'")->num_rows > 0;

// Contar notificações gerais não lidas
if ($tabela_notificacoes) {
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacoes_nao_lidas += $result->fetch_assoc()['total'];
}

// Contar notificações de promoções não lidas
if ($tabela_promocoes) {
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_lidas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        notificacao_promocao_id INT NOT NULL,
        data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_leitura (usuario_id, notificacao_promocao_id)
    )");
    
    $conexao->query("CREATE TABLE IF NOT EXISTS notificacoes_excluidas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        notificacao_promocao_id INT NOT NULL,
        data_exclusao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_exclusao (usuario_id, notificacao_promocao_id)
    )");
    
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total 
        FROM notificacoes_promocoes np 
        WHERE np.enviado = 1 
        AND (np.usuario_id = ? OR np.usuario_id IS NULL)
        AND np.id NOT IN (
            SELECT notificacao_promocao_id 
            FROM notificacoes_lidas 
            WHERE usuario_id = ?
        )
        AND np.id NOT IN (
            SELECT notificacao_promocao_id 
            FROM notificacoes_excluidas 
            WHERE usuario_id = ?
        )
    ");
    $stmt->bind_param("iii", $usuario_id, $usuario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacoes_nao_lidas += $result->fetch_assoc()['total'];
}

$conexao->close();
?>

<!-- Header Global Responsivo -->
<div id="global-header" class="global-header">
    <div class="header-content">
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="header-logo">
            <img src="logo.png" alt="FullTorque" class="logo-img">
        </div>
        
        <div class="header-actions">
            <a href="notificacoes.php" class="notification-btn <?php echo $notificacoes_nao_lidas > 0 ? 'has-notifications' : ''; ?>" 
               <?php echo $notificacoes_nao_lidas > 0 ? 'data-count="' . $notificacoes_nao_lidas . '"' : ''; ?>>
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>
</div>

<!-- Sidebar Global -->
<div id="global-sidebar" class="global-sidebar">
    <div class="sidebar-content">
        <div class="sidebar-header">
            <img src="logo.png" alt="FullTorque" class="sidebar-logo">
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['usuario_nome']; ?></div>
                <div class="user-role"><?php echo isset($_SESSION['usuario_nivel']) ? ucfirst($_SESSION['usuario_nivel']) : 'Usuário'; ?></div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <?php if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0): ?>
                <!-- Menu para Mecânicos/Funcionários -->
                <a href="mecanico-dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="mecanico-diagnosticos.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-diagnosticos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-stethoscope"></i>
                    <span>Meus Diagnósticos</span>
                </a>
                <a href="mecanico-orcamentos.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-orcamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calculator"></i>
                    <span>Solicitações de Orçamento</span>
                </a>
                <a href="mecanico-agenda.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-agenda.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Minha Agenda</span>
                </a>
                <a href="mecanico-clientes.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-clientes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Meus Clientes</span>
                </a>
                <a href="mecanico-relatorios.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'mecanico-relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Relatórios</span>
                </a>
                <a href="perfil.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Meu Perfil</span>
                </a>
            <?php else: ?>
                <!-- Menu para Clientes -->
                <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Início</span>
                </a>
                <a href="veiculos.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'veiculos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span>Meus Veículos</span>
                </a>
                <a href="agendamento-diagnostico.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'agendamento-diagnostico.php' ? 'active' : ''; ?>">
                    <i class="fas fa-stethoscope"></i>
                    <span>Solicitar Diagnóstico</span>
                </a>
                <a href="relatorios.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Meus Diagnósticos</span>
                </a>
                <a href="agendamentos.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'agendamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Agendamentos</span>
                </a>
                <a href="pagamentos.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'pagamentos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Pagamentos</span>
                </a>
                <a href="historico.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'historico.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                </a>
                <a href="promocoes.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'promocoes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-gift"></i>
                    <span>Promoções</span>
                </a>
                <a href="perfil.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Meu Perfil</span>
                </a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] == 'admin'): ?>
            <a href="admin.php" class="nav-item <?php echo strpos(basename($_SERVER['PHP_SELF']), 'admin') === 0 ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                <span>Painel Admin</span>
            </a>
            <?php endif; ?>
            
            <a href="#" onclick="confirmarSaida()" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </nav>
        
        <!-- Toggle de Tema -->
        <div class="theme-toggle-container">
            <div class="theme-toggle-wrapper">
                <span class="flag-icon italy-flag"></span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
                    <span class="slider"></span>
                </label>
                <span class="flag-icon germany-flag"></span>
            </div>
        </div>
    </div>
</div>

<!-- Overlay para mobile -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<!-- Modal de confirmação de saída -->
<div id="modalSair" class="modal-overlay">
    <div class="modal-content">
        <h3><i class="fas fa-sign-out-alt"></i> Confirmar Saída</h3>
        <p>Tem certeza que deseja sair do sistema?</p>
        <div class="modal-actions">
            <button onclick="fecharModalSair()" class="btn-cancel">Cancelar</button>
            <button onclick="window.location.href='logout.php'" class="btn-confirm">Sair</button>
        </div>
    </div>
</div>

<style>
/* Reset e variáveis globais */
:root {
    --primary-color: #109349;
    --secondary-color: #DD0100;
    --tertiary-color: #f8f9fa;
    --text-color: #333;
    --header-height: 60px;
    --sidebar-width: 280px;
    --transition: all 0.3s ease;
}

.theme-alemanha {
    --primary-color: #FFCE00;
    --secondary-color: #DD0100;
    --tertiary-color: #000000;
    --text-color: #ffffff;
}

/* Header Global */
.global-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--header-height);
    background: var(--primary-color);
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: var(--transition);
}

.global-header.hidden {
    transform: translateY(-100%);
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 0 20px;
    max-width: 100%;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: var(--transition);
}

.mobile-menu-btn:hover {
    background: rgba(255,255,255,0.1);
}

.header-logo {
    flex: 1;
    text-align: center;
}

.logo-img {
    height: 40px;
    width: auto;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
}

.notification-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.05);
}

.notification-btn.has-notifications::after {
    content: attr(data-count);
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--secondary-color);
    color: white;
    font-size: 10px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Sidebar Global */
.global-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--primary-color);
    z-index: 999;
    transform: translateX(-100%);
    transition: var(--transition);
    overflow-y: auto;
}

.global-sidebar.active {
    transform: translateX(0);
}

.sidebar-content {
    padding: 20px 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    text-align: center;
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-logo {
    max-width: 200px;
    height: auto;
}

.user-info {
    display: flex;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: white;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--secondary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 2px;
}

.user-role {
    font-size: 12px;
    opacity: 0.8;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 0;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(255,255,255,0.1);
    border-left-color: var(--secondary-color);
}

.nav-item i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
}

.theme-toggle-container {
    margin-top: auto;
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.2);
}

.theme-toggle-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.flag-icon {
    width: 20px;
    height: 14px;
    border-radius: 2px;
}

.italy-flag {
    background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%);
}

.germany-flag {
    background: linear-gradient(to bottom, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%);
}

.theme-switch {
    position: relative;
    width: 50px;
    height: 24px;
}

.theme-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.3);
    transition: var(--transition);
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background: white;
    transition: var(--transition);
    border-radius: 50%;
}

input:checked + .slider {
    background: var(--secondary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.modal-content h3 {
    color: var(--secondary-color);
    margin-bottom: 15px;
}

.modal-content p {
    margin-bottom: 25px;
    color: #666;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-cancel,
.btn-confirm {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: var(--transition);
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-confirm {
    background: var(--secondary-color);
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
}

.btn-confirm:hover {
    background: #c82333;
}

/* Responsividade */
@media (max-width: 768px) {
    .mobile-menu-btn {
        display: block;
    }
    
    .header-logo {
        flex: none;
    }
    
    .logo-img {
        height: 35px;
    }
    
    .global-sidebar {
        width: 280px;
    }
}

@media (max-width: 480px) {
    .header-content {
        padding: 0 15px;
    }
    
    .logo-img {
        height: 30px;
    }
    
    .global-sidebar {
        width: 260px;
    }
    
    .notification-btn {
        width: 35px;
        height: 35px;
    }
}

/* Tema Alemanha */
.theme-alemanha .global-header {
    background: var(--primary-color);
}

.theme-alemanha .global-sidebar {
    background: #000000;
    box-shadow: 2px 0 20px var(--secondary-color);
}

.theme-alemanha .theme-toggle-container {
    background: rgba(255, 206, 0, 0.2);
    border-top-color: rgba(255, 206, 0, 0.3);
}
</style>

<script>
// Controle do menu mobile e sidebar
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('global-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const header = document.getElementById('global-header');
    
    // Toggle do menu mobile
    mobileMenuBtn.addEventListener('click', function() {
        const isActive = sidebar.classList.contains('active');
        
        if (isActive) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
    
    // Fechar sidebar ao clicar no overlay
    overlay.addEventListener('click', closeSidebar);
    
    // Fechar sidebar ao clicar em links de navegação
    const navItems = sidebar.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!this.getAttribute('onclick')) {
                closeSidebar();
            }
        });
    });
    
    // Fechar sidebar ao redimensionar para desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        header.classList.add('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        header.classList.remove('hidden');
        document.body.style.overflow = '';
    }
    
    // Aplicar tema salvo
    const savedTheme = localStorage.getItem('theme') || 'default';
    const toggle = document.getElementById('themeToggle');
    
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
        if (toggle) toggle.checked = true;
    }
});

// Funções globais
function toggleTheme() {
    const toggle = document.getElementById('themeToggle');
    const isGerman = toggle.checked;
    
    document.body.classList.remove('theme-alemanha');
    if (isGerman) {
        document.body.classList.add('theme-alemanha');
        localStorage.setItem('theme', 'theme-alemanha');
    } else {
        localStorage.setItem('theme', 'default');
    }
}

function confirmarSaida() {
    const modal = document.getElementById('modalSair');
    modal.style.display = 'flex';
}

function fecharModalSair() {
    const modal = document.getElementById('modalSair');
    modal.style.display = 'none';
}

// Fechar modal ao clicar fora
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalSair');
    if (event.target === modal) {
        fecharModalSair();
    }
});
</script>