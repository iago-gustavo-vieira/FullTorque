<?php
// Buscar foto do perfil
$foto_perfil = null;
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

$stmt = $conexao->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $foto_perfil = $result->fetch_assoc()['foto_perfil'];
}
$stmt->close();
$conexao->close();
?>

<button class="mobile-menu-toggle" type="button" aria-label="Menu">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <div class="sidebar-header" style="text-align: center;">
        <img src="logo.png" alt="<?php echo SISTEMA_NOME; ?>" style="max-width: 250px; height: auto; margin: 0 auto 10px auto; display: block;">
    </div>
    
    <div class="user-info">
        <div class="user-info-top">
            <div class="user-avatar">
                <?php if (!empty($foto_perfil) && file_exists('uploads/perfil/' . $foto_perfil)): ?>
                    <img src="uploads/perfil/<?php echo htmlspecialchars($foto_perfil); ?>?v=<?php echo time(); ?>" alt="Foto do perfil">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['usuario_nome']; ?></div>
                <div class="user-role"><?php echo isset($_SESSION['usuario_nivel']) ? ucfirst($_SESSION['usuario_nivel']) : 'Usuário'; ?></div>
            </div>
        </div>
        
        <div class="user-actions">
            <a href="admin-perfil.php" class="user-action-btn btn-edit-profile">
                <i class="fas fa-user-edit"></i>
                <span>Editar</span>
            </a>
            <a href="#" onclick="confirmarSaida(); return false;" class="user-action-btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a href="admin.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="admin-relatorios.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-relatorios.php' ? 'active' : ''; ?>">
            <i class="fas fa-stethoscope"></i>
            <span>Diagnósticos</span>
        </a>
        
        <a href="admin-agendamentos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-agendamentos.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Agendamentos</span>
        </a>
        
        <a href="admin-servicos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-servicos.php' ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i>
            <span>Serviços</span>
        </a>
        
        <a href="admin-mecanicos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-mecanicos.php' || basename($_SERVER['PHP_SELF']) == 'admin-mecanico-form.php' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>
            <span>Mecânicos</span>
        </a>
        
        <a href="admin-promocoes.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-promocoes.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Promoções</span>
        </a>
        
        <a href="enviar_promocoes.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'enviar_promocoes.php' ? 'active' : ''; ?>">
            <i class="fas fa-paper-plane"></i>
            <span>Enviar Promoções</span>
        </a>
        
        <a href="admin-pagamentos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-pagamentos.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Pagamentos</span>
        </a>
        
        <a href="admin-usuarios.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Usuários</span>
        </a>
        
        <a href="admin-veiculos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-veiculos.php' ? 'active' : ''; ?>">
            <i class="fas fa-car"></i>
            <span>Veículos</span>
        </a>
        
        <a href="admin-logs.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Logs</span>
        </a>
        
        <a href="admin-perfil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-perfil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            <span>Meu Perfil</span>
        </a>
    </div>
    
    <!-- Botão Toggle de Tema -->
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

<!-- Modal de confirmação de saída -->
<div id="modalSair" class="modal-sair">
    <div class="modal-sair-content">
        <h3><i class="fas fa-sign-out-alt"></i> Confirmar Saída</h3>
        <p>Tem certeza que deseja sair do sistema?</p>
        <div class="modal-sair-actions">
            <button onclick="fecharModalSair()" class="btn-cancelar">Cancelar</button>
            <button onclick="window.location.href='admin-logout.php'" class="btn-sair">Sair</button>
        </div>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    background-color: #109349;
    color: white;
    padding: 20px 0;
    position: fixed;
    height: 100%;
    overflow-y: auto;
    transition: all 0.3s;
    z-index: 1000;
    font-family: 'Poppins', sans-serif;
}

.theme-alemanha .sidebar {
    background-color: #000000;
    border: none;
    box-shadow: 8px 0 20px #DD0100;
}

.sidebar-header {
    padding: 0 0 20px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.user-info {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
}

.user-info-top {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.user-actions {
    display: flex;
    gap: 8px;
}

.user-action-btn {
    flex: 1;
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s;
}

.btn-edit-profile {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.btn-edit-profile:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.btn-logout {
    background: rgba(221, 1, 0, 0.8);
    color: white;
}

.btn-logout:hover {
    background: #DD0100;
    transform: translateY(-2px);
}

.theme-alemanha .btn-logout {
    background: rgba(255, 206, 0, 0.9);
    color: #000;
}

.theme-alemanha .btn-logout:hover {
    background: #FFCE00;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #DD0100;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    font-size: 20px;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 500;
    font-size: 0.9rem;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.7;
}

.sidebar-menu {
    padding: 20px 0;
}

.menu-item {
    padding: 12px 20px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
    text-decoration: none;
    color: white;
}

.menu-item:hover, .menu-item.active {
    background-color: rgba(255, 255, 255, 0.1);
    border-left: 4px solid #CE2A37;
}

.theme-alemanha .menu-item:hover, 
.theme-alemanha .menu-item.active {
    background-color: rgba(220, 38, 38, 0.1);
    border-left: 4px solid #dc2626;
}

.menu-item i {
    margin-right: 10px;
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.theme-toggle-container {
    position: relative;
    margin-top: 20px;
    padding: 15px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
}

.theme-alemanha .theme-toggle-container {
    background: rgba(255, 206, 0, 0.2);
    border-top: 1px solid rgba(255, 206, 0, 0.3);
    box-shadow: 0 -5px 15px rgba(255, 206, 0, 0.4);
}

.theme-toggle-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.theme-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
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
    background-color: #109349;
    transition: .4s;
    border-radius: 30px;
}

.theme-alemanha .slider {
    background-color: #FFCE00;
}

.slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #000000;
}

input:checked + .slider:before {
    transform: translateX(30px);
}

.flag-icon {
    display: inline-block;
    width: 20px;
    height: 14px;
    border-radius: 2px;
    margin: 0 3px;
}

.italy-flag {
    background: linear-gradient(to right, #009246 33%, #ffffff 33%, #ffffff 66%, #ce2b37 66%) !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.germany-flag {
    background: linear-gradient(to bottom, #000000 0%, #000000 33%, #dd0000 33%, #dd0000 66%, #ffce00 66%, #ffce00 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.mobile-menu-toggle {
    display: none;
}

.modal-sair {
    display: none;
    position: fixed;
    z-index: 10001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
}

.modal-sair-content {
    background: linear-gradient(135deg, #009246 0%, #ffffff 50%, #CE2B37 100%);
    margin: 15% auto;
    padding: 3px;
    border-radius: 15px;
    width: 90%;
    max-width: 450px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: modalSlideIn 0.3s ease;
}

.modal-sair-content::before {
    content: '';
    position: absolute;
    inset: 3px;
    background-color: white;
    border-radius: 13px;
    z-index: 1;
}

.modal-sair-content > * {
    position: relative;
    z-index: 2;
}

.theme-alemanha .modal-sair-content {
    background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
}

.theme-alemanha .modal-sair-content::before {
    background-color: #1a1a1a;
}

.modal-sair-content h3 {
    background: linear-gradient(90deg, #009246, #CE2B37);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 30px 0 15px;
    font-size: 1.5rem;
}

.theme-alemanha .modal-sair-content h3 {
    background: linear-gradient(90deg, #FFCE00, #DD0100);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modal-sair-content p {
    color: #666;
    margin-bottom: 25px;
    padding: 0 20px;
}

.theme-alemanha .modal-sair-content p {
    color: #ffffffb0;
}

.modal-sair-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 0 20px 30px;
}

.btn-cancelar, .btn-sair {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-cancelar {
    background: #6c757d;
    color: white;
}

.btn-cancelar:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-sair {
    background: #CE2B37;
    color: white;
}

.theme-alemanha .btn-sair {
    background: #FFCE00;
    color: #000;
}

.btn-sair:hover {
    background: #a01e28;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(206, 43, 55, 0.4);
}

.theme-alemanha .btn-sair:hover {
    background: #daaf03;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.4);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
        position: fixed;
        z-index: 1001;
        transition: transform 0.3s ease;
        box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    }
    
    .theme-alemanha .sidebar {
        box-shadow: none;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .mobile-menu-toggle {
        display: flex;
        width: 44px;
        height: 44px;
        border-radius: 5px;
        background-color: #109349;
        color: white;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1002;
        transition: all 0.3s;
        border: none;
        font-size: 18px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    
    .mobile-menu-toggle:hover {
        background-color: #0d7a3e;
        transform: scale(1.05);
    }
    
    .theme-alemanha .mobile-menu-toggle {
        background-color: #FFCE00 !important;
        color: #000000 !important;
    }
    
    .theme-alemanha .mobile-menu-toggle:hover {
        background-color: #e6b800 !important;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 260px;
    }
    
    .user-info {
        padding: 15px;
    }
    
    .user-name {
        font-size: 0.85rem;
    }
    
    .user-role {
        font-size: 0.75rem;
    }
    
    .menu-item {
        padding: 12px 15px;
        font-size: 14px;
    }
}

@media (max-width: 360px) {
    .sidebar {
        width: 240px;
    }
    
    .mobile-menu-toggle {
        width: 40px;
        height: 40px;
        top: 15px;
        left: 15px;
        font-size: 16px;
    }
}
</style>

<script>
function confirmarSaida() {
    document.getElementById('modalSair').style.display = 'block';
}

function fecharModalSair() {
    document.getElementById('modalSair').style.display = 'none';
}

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

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('modalSair');
    if (event.target == modal) {
        fecharModalSair();
    }
}

// Aplicar tema salvo ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'default';
    const toggle = document.getElementById('themeToggle');
    
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
        if (toggle) toggle.checked = true;
    }
    
    // Inicializar menu mobile
    initMobileMenu();
});

// Função para controlar o menu mobile
function initMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            sidebar.classList.toggle('active');
            
            // Criar/remover overlay
            let overlay = document.querySelector('.mobile-overlay');
            if (sidebar.classList.contains('active')) {
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'mobile-overlay';
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 1000;
                        backdrop-filter: blur(2px);
                    `;
                    document.body.appendChild(overlay);
                    
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        overlay.remove();
                    });
                }
            } else {
                if (overlay) overlay.remove();
            }
        });
        
        // Fechar menu ao clicar em itens do menu
        const menuItems = sidebar.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    const overlay = document.querySelector('.mobile-overlay');
                    if (overlay) overlay.remove();
                }
            });
        });
    }
}

// Fechar menu ao redimensionar
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.remove();
    }
});
</script>
