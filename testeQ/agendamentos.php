<?php
require_once 'config.php';
verificarLogin();

require_once 'header.php';

// Busca informações do usuário
$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Verifica se o nível de acesso está definido
if (!isset($_SESSION['usuario_nivel'])) {
    $stmt = $conexao->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
    } else {
        $_SESSION['usuario_nivel'] = 'cliente';
    }
    $stmt->close();
}

// Busca os agendamentos do usuário
if (tabelaExiste($conexao, 'agendamentos')) {
    $stmt = $conexao->prepare("
        SELECT a.*, v.marca, v.modelo, v.placa 
        FROM agendamentos a 
        JOIN veiculos v ON a.veiculo_id = v.id 
        WHERE a.usuario_id = ? 
        ORDER BY a.data_agendamento DESC, a.hora_inicio DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $agendamentos = $stmt->get_result();
} else {
    $agendamentos = (object)['num_rows' => 0];
}

$conexao->close();
?>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Meus Agendamentos</div>
    <div class="welcome-message">
        <h2><i class="fas fa-calendar-alt"></i> Meus Agendamentos</h2>
        <p>Gerencie seus agendamentos de serviços automotivos e acompanhe o status.</p>
    </div>
    <div class="welcome-actions">
        <div class="clock-widget">
            <i class="fas fa-clock"></i>
            <span id="current-time"></span>
        </div>
        <a href="agendamento-novo.php" class="btn" data-tooltip="Agendar novo serviço">
            <i class="fas fa-plus"></i> Agendar serviço
        </a>
    </div>
</div>

<style>
.dashboard-welcome {
    background: linear-gradient(135deg, #109349 0%, #0a7a32 20%, #ffffff 40%, #f8f8f8 60%, #DD0100 80%, #b8010a 100%);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    background-size: 200% 200%;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.dashboard-welcome::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.dashboard-welcome > * {
    position: relative;
    z-index: 2;
}

.theme-alemanha .dashboard-welcome {
    background: #000000;
    border: 1px solid #FFCE00;
    color: white;
}

.theme-alemanha .dashboard-welcome::before {
    display: none;
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
    font-family: 'Poppins', sans-serif;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    font-weight: 700;
}

.welcome-message p {
    opacity: 1;
    font-family: 'Poppins', sans-serif;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
    font-weight: 600;
    color: #fff;
}

.welcome-actions .btn {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.4);
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    backdrop-filter: blur(10px);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.welcome-actions .btn i {
    font-size: 14px;
    line-height: 1;
    vertical-align: middle;
}

.theme-alemanha .welcome-actions .btn {
    background-color: #FFCE00;
    color: #000;
    border-color: #FFCE00;
}

.welcome-actions .btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.theme-alemanha .welcome-actions .btn:hover {
    background-color: #e6b800;
}

.clock-widget {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 10px 16px;
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.theme-alemanha .clock-widget {
    background-color: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
    border-color: rgba(255, 206, 0, 0.4);
}

.clock-widget i {
    font-size: 14px;
}

#current-time {
    font-size: 14px;
    letter-spacing: 0.5px;
}

body {
    background-color: #f8f9fa;
    color: #333;
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
    line-height: 1.4;
}

.appointments-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
}

/* Ações Rápidas */
.quick-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
}

.quick-action-btn.primary {
    background: #109349;
    color: white;
}

.quick-action-btn.secondary {
    background: #6c757d;
    color: white;
}

.theme-alemanha .quick-action-btn.primary {
    background: #FFCE00;
    color: #000;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}



/* Filtros */
.filter-tabs {
    display: flex;
    background: white;
    border-radius: 10px;
    padding: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.theme-alemanha .filter-tabs {
    background: #1a1a1a;
}

.filter-tab {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    white-space: nowrap;
}

.filter-tab.active {
    background: #109349;
    color: white;
}

.theme-alemanha .filter-tab {
    color: #ccc;
}

.theme-alemanha .filter-tab.active {
    background: #FFCE00;
    color: #000;
}

/* Lista de Agendamentos */
.appointments-list {
    display: grid;
    gap: 15px;
}

.appointment-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
    border-left: 4px solid #ddd;
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.theme-alemanha .appointment-card {
    background: #1a1a1a;
    color: white;
}

.appointment-card[data-status="agendado"] {
    border-left-color: #3498db;
}

.appointment-card[data-status="confirmado"] {
    border-left-color: #f39c12;
}

.appointment-card[data-status="concluido"] {
    border-left-color: #109349;
}

.appointment-card[data-status="cancelado"] {
    border-left-color: #DD0100;
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.appointment-date,
.appointment-time {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

.appointment-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.appointment-status.status-agendado {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.appointment-status.status-confirmado {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
}

.appointment-status.status-concluido {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
}

.appointment-status.status-cancelado {
    background: rgba(221, 1, 0, 0.1);
    color: #DD0100;
}

.appointment-body {
    margin-bottom: 15px;
}

.vehicle-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.vehicle-info small {
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: auto;
}

.theme-alemanha .vehicle-info small {
    background: #333;
}

.appointment-notes {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
}

.theme-alemanha .appointment-notes {
    color: #ccc;
}

.appointment-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn.view {
    background: #3498db;
    color: white;
}

.action-btn.edit {
    background: #f39c12;
    color: white;
}

.action-btn.cancel {
    background: #DD0100;
    color: white;
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Estado Vazio */
.empty-appointments {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.theme-alemanha .empty-appointments {
    background: #1a1a1a;
    color: white;
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-appointments h3 {
    margin-bottom: 10px;
    color: #333;
}

.theme-alemanha .empty-appointments h3 {
    color: white;
}

.empty-appointments p {
    color: #666;
    margin-bottom: 25px;
}

.theme-alemanha .empty-appointments p {
    color: #ccc;
}

.btn-new-appointment {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #109349;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.theme-alemanha .btn-new-appointment {
    background: #FFCE00;
    color: #000;
}

.btn-new-appointment:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 147, 73, 0.3);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background: #109349;
    color: white;
}

.btn-primary:hover {
    background: #0d7a3a;
}

.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-primary:hover {
    background: #e6b800;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Poppins', sans-serif;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #109349;
}

.alert-danger {
    background: rgba(221, 1, 0, 0.1);
    color: #DD0100;
    border-left: 4px solid #DD0100;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border-left: 4px solid #f39c12;
}

.theme-alemanha .alert-success {
    background: #2a2a2a;
    color: #FFCE00;
    border-left-color: #FFCE00;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow-x: auto;
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
    width: 100%;
    box-sizing: border-box;
}

.theme-alemanha .table-container {
    background: #1a1a1a;
    border-color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Poppins', sans-serif;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.theme-alemanha th {
    background: #2a2a2a;
    color: #FFCE00;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background: #f8f9fa;
}

.theme-alemanha tr:hover td {
    background: #2a2a2a;
}

.theme-alemanha td {
    color: white;
}

.status {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-agendado {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    border: 1px solid rgba(52, 152, 219, 0.2);
}

.status-confirmado {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
    border: 1px solid rgba(243, 156, 18, 0.2);
}

.status-em-andamento {
    background: rgba(230, 126, 34, 0.1);
    color: #e67e22;
    border: 1px solid rgba(230, 126, 34, 0.2);
}

.status-concluido {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
    border: 1px solid rgba(16, 147, 73, 0.2);
}

.status-cancelado {
    background: rgba(221, 1, 0, 0.1);
    color: #DD0100;
    border: 1px solid rgba(221, 1, 0, 0.2);
}

.actions {
    display: flex;
    gap: 5px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.action-btn.view-btn {
    background: #3498db;
    color: white;
}

.action-btn.view-btn:hover {
    background: #2980b9;
    transform: scale(1.1);
}

.action-btn.edit-btn {
    background: #f39c12;
    color: white;
}

.action-btn.edit-btn:hover {
    background: #e67e22;
    transform: scale(1.1);
}

.action-btn.cancel-btn {
    background: #DD0100;
    color: white;
}

.action-btn.cancel-btn:hover {
    background: #b8010a;
    transform: scale(1.1);
}

.theme-alemanha .action-btn.view-btn {
    background: #3498db;
}

.theme-alemanha .action-btn.edit-btn {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .action-btn.cancel-btn {
    background: #DD0100;
}

.empty-state {
    text-align: center;
    padding: 60px 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px dashed #ddd;
}

.theme-alemanha .empty-state {
    background: #1a1a1a;
    color: white;
    border-color: #444;
}

.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #495057;
    font-family: 'Poppins', sans-serif;
}

.empty-state p {
    color: #777;
    margin-bottom: 20px;
    font-family: 'Poppins', sans-serif;
}

.tabs {
    display: flex;
    margin-bottom: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
}

.theme-alemanha .tabs {
    background: #1a1a1a;
    border-color: #333;
}

.tab {
    flex: 1;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
    color: #6c757d;
    border-bottom: 2px solid transparent;
    font-family: 'Poppins', sans-serif;
}

.tab.active {
    color: #109349;
    border-bottom-color: #109349;
    background: rgba(16, 147, 73, 0.05);
}

.tab:hover:not(.active) {
    background: #f8f9fa;
}

.theme-alemanha .tab {
    color: #ccc;
}

.theme-alemanha .tab.active {
    color: #FFCE00;
    border-bottom-color: #FFCE00;
    background: rgba(255, 206, 0, 0.1);
}

.theme-alemanha .tab:hover:not(.active) {
    background: #2a2a2a;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
    box-sizing: border-box;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border: 1px solid #e9ecef;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.theme-alemanha .stat-card {
    background: #1a1a1a;
    border-color: #333;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-info h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
}

.theme-alemanha .stat-info h3 {
    color: #FFCE00;
}

.stat-info p {
    margin: 5px 0 0 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.theme-alemanha .stat-info p {
    color: #ccc;
}

/* Responsividade para Tablets */
@media (max-width: 992px) {
    .appointments-container {
        padding: 15px;
    }
    

    
    .quick-actions {
        justify-content: center;
    }
}

/* Mobile - 768px e abaixo */
@media (max-width: 768px) {
    body {
        overflow-x: hidden;
        font-size: 14px;
    }
    
    /* Dashboard Welcome - NÃO MEXER */
    .dashboard-welcome {
        background-image: url('bem-vindo-italia-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        padding: 15px 12px;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 15px;
        flex-direction: column;
        position: relative;
        min-height: 200px;
    }
    
    .theme-alemanha .dashboard-welcome {
        background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    
    .mobile-welcome-text {
        display: flex;
        position: absolute;
        top: 65px;
        
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.9);
        color: #109349;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 18px;
        font-weight: 600;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(5px);
        z-index: 10;
        white-space: nowrap;
    }
    
    .theme-alemanha .mobile-welcome-text {
        background: rgba(0, 0, 0, 0.8);
        color: #EFC202;
    }
    
    .welcome-message {
        display: none !important;
    }
    
    .welcome-actions {
        display: none !important;
    }
    
    .clock-widget {
        display: none !important;
    }
    
    /* Container */
    .appointments-container {
        padding: 10px;
    }
    
    /* Ações Rápidas */
    .quick-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .quick-action-btn {
        justify-content: center;
        padding: 14px;
    }
    

    
    /* Filtros */
    .filter-tabs {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4px;
        padding: 4px;
    }
    
    .filter-tab {
        padding: 10px 8px;
        font-size: 0.8rem;
    }
    
    .filter-tab:nth-child(4),
    .filter-tab:nth-child(5) {
        grid-column: span 1;
    }
    
    /* Cards de Agendamento */
    .appointment-card {
        padding: 15px;
    }
    
    .appointment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .appointment-date,
    .appointment-time {
        font-size: 0.9rem;
    }
    
    .appointment-status {
        align-self: flex-end;
        margin-top: -25px;
    }
    
    .vehicle-info {
        flex-wrap: wrap;
    }
    
    .appointment-actions {
        justify-content: center;
        margin-top: 10px;
    }
    
    .action-btn {
        width: 40px;
        height: 40px;
    }
}

/* Mobile Pequeno - 480px e abaixo */
@media (max-width: 480px) {
    .appointments-container {
        padding: 8px;
    }
    
    .mobile-welcome-text {
        font-size: 16px;
        padding: 6px 12px;
    }
    

    
    /* Filtros - Layout vertical */
    .filter-tabs {
        grid-template-columns: 1fr;
        gap: 2px;
    }
    
    .filter-tab {
        padding: 12px;
        font-size: 0.9rem;
    }
    
    /* Cards mais compactos */
    .appointment-card {
        padding: 12px;
    }
    
    .appointment-header {
        gap: 6px;
    }
    
    .appointment-status {
        font-size: 0.75rem;
        padding: 4px 8px;
        margin-top: -20px;
    }
    
    .vehicle-info {
        font-size: 0.9rem;
    }
    
    .appointment-notes {
        font-size: 0.8rem;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
    }
}

/* Mobile Extra Pequeno - 360px e abaixo */
@media (max-width: 360px) {
    .appointments-container {
        padding: 6px;
    }
    
    .mobile-welcome-text {
        font-size: 14px;
        padding: 4px 8px;
        top: 60px;
    }
    
    .quick-action-btn {
        padding: 12px;
        font-size: 0.9rem;
    }
    

    
    .filter-tab {
        padding: 10px;
        font-size: 0.8rem;
    }
    
    .appointment-card {
        padding: 10px;
    }
    
    .appointment-status {
        font-size: 0.7rem;
        padding: 3px 6px;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .empty-appointments {
        padding: 40px 15px;
    }
    
    .empty-icon {
        font-size: 3rem;
    }
}
</style>

<div class="appointments-container">
    <?php mostrarAlerta(); ?>
    
    <!-- Ações Rápidas -->
    <div class="quick-actions">
        <a href="agendamento-novo.php" class="quick-action-btn primary">
            <i class="fas fa-plus"></i>
            <span>Novo Agendamento</span>
        </a>
        <button class="quick-action-btn secondary" onclick="refreshPage()">
            <i class="fas fa-sync-alt"></i>
            <span>Atualizar</span>
        </button>
    </div>



    <!-- Filtros -->
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">Todos</button>
        <button class="filter-tab" data-filter="agendado">Agendados</button>
        <button class="filter-tab" data-filter="confirmado">Confirmados</button>
        <button class="filter-tab" data-filter="concluido">Concluídos</button>
        <button class="filter-tab" data-filter="cancelado">Cancelados</button>
    </div>

    <!-- Lista de Agendamentos -->
    <div class="appointments-list">
        <?php if ($agendamentos->num_rows > 0): ?>
            <?php while ($agendamento = $agendamentos->fetch_assoc()): ?>
                <div class="appointment-card" data-status="<?php echo $agendamento['status']; ?>">
                    <div class="appointment-header">
                        <div class="appointment-date">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo formatarData($agendamento['data_agendamento'], 'd/m/Y'); ?></span>
                        </div>
                        <div class="appointment-time">
                            <i class="fas fa-clock"></i>
                            <span><?php echo substr($agendamento['hora_inicio'], 0, 5); ?></span>
                        </div>
                        <div class="appointment-status status-<?php echo $agendamento['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
                        </div>
                    </div>
                    
                    <div class="appointment-body">
                        <div class="vehicle-info">
                            <i class="fas fa-car"></i>
                            <span><?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></span>
                            <small><?php echo $agendamento['placa']; ?></small>
                        </div>
                        
                        <?php if (!empty($agendamento['observacoes'])): ?>
                        <div class="appointment-notes">
                            <i class="fas fa-comment"></i>
                            <span><?php echo $agendamento['observacoes']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="appointment-actions">
                        <button class="action-btn view" onclick="viewAppointment(<?php echo $agendamento['id']; ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($agendamento['status'] == 'agendado'): ?>
                        <button class="action-btn edit" onclick="editAppointment(<?php echo $agendamento['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn cancel" onclick="cancelAppointment(<?php echo $agendamento['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-appointments">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3>Nenhum agendamento encontrado</h3>
                <p>Você ainda não possui agendamentos cadastrados.</p>
                <a href="agendamento-novo.php" class="btn-new-appointment">
                    <i class="fas fa-plus"></i>
                    Criar Primeiro Agendamento
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>



    <script>
        // Filtros de agendamentos
        document.addEventListener('DOMContentLoaded', function() {
            initFilters();
        });
        
        function initFilters() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            const appointmentCards = document.querySelectorAll('.appointment-card');
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active de todas as abas
                    filterTabs.forEach(t => t.classList.remove('active'));
                    // Adiciona active na aba clicada
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    
                    appointmentCards.forEach(card => {
                        if (filter === 'all' || card.dataset.status === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        }
        

        
        function refreshPage() {
            location.reload();
        }
        
        function viewAppointment(id) {
            window.location.href = 'agendamento-detalhes.php?id=' + id;
        }
        
        function editAppointment(id) {
            window.location.href = 'agendamento-editar.php?id=' + id;
        }
        
        function cancelAppointment(id) {
            if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
                window.location.href = 'agendamento-cancelar.php?id=' + id;
            }
        }
    </script>
    


<?php include 'footer.php'; ?>
