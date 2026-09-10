<?php
require_once 'config.php';
verificarLogin();

// Busca informações do usuário
$conexao = conectarBD();
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;

// Verifica se foi solicitada a exclusão de um veículo
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $veiculo_id = $_GET['excluir'];
    
    // Verifica se o veículo pertence ao usuário
    $stmt = $conexao->prepare("SELECT id FROM veiculos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $veiculo_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Remove registros relacionados primeiro (apenas se as tabelas existirem)
        if (tabelaExiste($conexao, 'respostas_diagnostico') && tabelaExiste($conexao, 'relatorios_cliente')) {
            $conexao->query("DELETE rd FROM respostas_diagnostico rd INNER JOIN relatorios_cliente rc ON rd.diagnostico_id = rc.id WHERE rc.veiculo_id = $veiculo_id");
        }
        if (tabelaExiste($conexao, 'solicitacoes_orcamento') && tabelaExiste($conexao, 'relatorios_cliente')) {
            $conexao->query("DELETE so FROM solicitacoes_orcamento so INNER JOIN relatorios_cliente rc ON so.diagnostico_id = rc.id WHERE rc.veiculo_id = $veiculo_id");
        }
        if (tabelaExiste($conexao, 'relatorios_cliente')) {
            $conexao->query("DELETE FROM relatorios_cliente WHERE veiculo_id = $veiculo_id");
        }
        if (tabelaExiste($conexao, 'agendamentos')) {
            $conexao->query("DELETE FROM agendamentos WHERE veiculo_id = $veiculo_id");
        }
        
        // Exclui o veículo
        $stmt = $conexao->prepare("DELETE FROM veiculos WHERE id = ?");
        $stmt->bind_param("i", $veiculo_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Veículo excluído com sucesso!'];
            registrarLog('veiculo_excluido', 'Veículo ID: ' . $veiculo_id . ' excluído');
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Erro ao excluir o veículo.'];
        }
    } else {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Veículo não encontrado ou não pertence ao usuário.'];
    }
    
    // Redireciona para evitar reenvio do formulário
    header("Location: veiculos.php");
    exit;
}

// Busca os veículos do usuário
$veiculos = new stdClass();
$veiculos->num_rows = 0;
if ($usuario_id > 0 && colunaExiste($conexao, 'veiculos', 'usuario_id')) {
    $stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ? ORDER BY marca, modelo");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $veiculos = $stmt->get_result();
    $stmt->close();
}

$conexao->close();

require_once 'header.php';

include 'get-brand-logo.php';
?>

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
    background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
    color: white;
    border: 2px solid #FFCE00;
}

.theme-alemanha .dashboard-welcome::before {
    display: none;
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    font-weight: 700;

}

.welcome-message p {
    opacity: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
    font-weight: 600;
    color: #fff;
}

.welcome-actions .btn {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}
.theme-alemanha .welcome-actions .btn{
    color: #fff;
    background-color: #000000;
    border: none;
}

.welcome-actions .btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}
.theme-alemanha .welcome-actions .btn:hover{
    background-color: #00000074;
}

.vehicle-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.vehicle-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
}

.vehicle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.12);
}

.theme-alemanha .vehicle-card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 10px 30px rgba(255,206,0,0.2);
}

.vehicle-header {
    background: linear-gradient(135deg, #CE2A37, #a91e2a);
    color: white;
    padding: 20px;
    position: relative;
    text-align: center;
}

.theme-alemanha .vehicle-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.vehicle-header h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.vehicle-plate {
    display: inline-block;
    background: rgba(255,255,255,0.95);
    color: #333;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.95rem;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    letter-spacing: 1px;
    margin-top: 5px;
}

.vehicle-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.95);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #CE2A37;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    transition: transform 0.3s;
}

.vehicle-card:hover .vehicle-icon {
    transform: scale(1.05);
}

.brand-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 50%;
    background: white;
    padding: 6px;
}

.vehicle-body {
    padding: 20px;
}

.theme-alemanha .vehicle-body {
    background: #1a1a1a;
}

.vehicle-info {
    margin-bottom: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

.theme-alemanha .vehicle-info {
    background: #2a2a2a;
}

.info-item {
    display: flex;
    margin-bottom: 12px;
    align-items: center;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-label {
    width: 100px;
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
}

.theme-alemanha .info-label {
    color: #FFCE00;
}

.info-value {
    flex: 1;
    padding-left: 12px;
    color: #333;
    font-weight: 500;
    font-size: 0.9rem;
}

.theme-alemanha .info-value {
    color: #fff;
}

.vehicle-actions {
    display: flex;
    gap: 10px;
}

.btn-edit {
    flex: 1;
    background: transparent;
    color: #CE2A37;
    border: 2px solid #CE2A37;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.9rem;
}

.btn-edit:hover {
    background: #CE2A37;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.theme-alemanha .btn-edit {
    color: #FFCE00;
    border-color: #FFCE00;
}

.theme-alemanha .btn-edit:hover {
    background: #FFCE00;
    color: #000;
}

.btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.9rem;
}

.btn-delete:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
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
    margin-bottom: 25px;
    animation: pulse 2s infinite;
}

.theme-alemanha .empty-state i {
    color: #666;
}

.empty-state h3 {
    font-size: 1.6rem;
    margin-bottom: 15px;
    color: #333;
}

.theme-alemanha .empty-state h3 {
    color: #FFCE00;
}

.empty-state p {
    color: #666;
    margin-bottom: 25px;
    font-size: 1rem;
    line-height: 1.5;
}

.theme-alemanha .empty-state p {
    color: #ccc;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal {
    background: white;
    border-radius: 15px;
    padding: 30px;
    max-width: 450px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    transform: scale(0.7);
    transition: transform 0.3s ease;
}

.theme-alemanha .modal {
    background: #1a1a1a;
    color: white;
}

.modal-overlay.show {
    display: flex;
}

.modal-overlay.show .modal {
    transform: scale(1);
}

.modal h3 {
    color: #dc3545;
    margin-bottom: 15px;
    font-size: 1.6rem;
}

.modal-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel:hover {
    background: #5a6268;
}

.btn-confirm {
    background: #dc3545;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s;
    display: inline-block;
}

.btn-confirm:hover {
    background: #c82333;
    color: white;
    text-decoration: none;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin: 0 auto 40px auto;
    max-width: 1500px;
}

.tip-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
    display: flex;
    align-items: flex-start;
}

.theme-alemanha .tip-card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 5px 15px rgba(255,206,0,0.2);
}

.tip-card:hover {
    transform: translateY(-5px);
}

.theme-alemanha .tip-card:hover {
    box-shadow: 0 8px 25px rgba(255,206,0,0.3);
}

.tip-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #109349;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.theme-alemanha .tip-icon {
    background: #FFCE00;
    color: #000;
}

.tip-content h3 {
    margin: 0 0 10px 0;
    color: #009246;
    font-size: 1.1rem;
    font-weight: 600;
}

.theme-alemanha .tip-content h3 {
    color: #FFCE00;
}

.tip-content p {
    color: #666;
    line-height: 1.5;
    font-size: 0.9rem;
    margin: 0;
}

.theme-alemanha .tip-content p {
    color: #ccc;
}

.curiosities-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin: 0 auto 30px auto;
    max-width: 1800px;
}

.curiosity-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.theme-alemanha .curiosity-card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 5px 15px rgba(221,1,0,0.2);
}

.curiosity-card:hover {
    transform: translateY(-5px);
}

.theme-alemanha .curiosity-card:hover {
    box-shadow: 0 8px 25px rgba(221,1,0,0.3);
}

.curiosity-card h3 {
    margin: 0 0 10px 0;
    color: #CE2B37;
    font-size: 1.1rem;
    font-weight: 600;
}

.theme-alemanha .curiosity-card h3 {
    color: #DD0100;
}

.curiosity-card p {
    color: #666;
    line-height: 1.5;
    font-size: 0.9rem;
    margin: 0;
}

.theme-alemanha .curiosity-card p {
    color: #ccc;
}

.content > h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.5rem;
    font-weight: 700;
}

.content > h2:first-of-type {
    color: #CE2B37;
}

.content > h2:last-of-type {
    color: #009246;
}

/* Botão Novo Veículo Mobile */
.mobile-new-vehicle-btn {
    display: none;
    margin-bottom: 20px;
    text-align: center;
}

.btn-new-vehicle-mobile {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #109349;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(16, 147, 73, 0.3);
    transition: all 0.3s;
}

.theme-alemanha .btn-new-vehicle-mobile {
    background: #FFCE00;
    color: #000;
    box-shadow: 0 4px 12px rgba(255, 206, 0, 0.3);
}

.btn-new-vehicle-mobile:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 147, 73, 0.4);
    text-decoration: none;
    color: white;
}

.theme-alemanha .btn-new-vehicle-mobile:hover {
    box-shadow: 0 6px 16px rgba(255, 206, 0, 0.4);
    color: #000;
}

@media (max-width: 768px) {
    .mobile-new-vehicle-btn {
        display: block;
    }
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
    
    /* Texto de boas-vindas mobile */
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
    
    .welcome-actions {
        margin-top: 10px;
    }
    
    .welcome-actions .btn{
        background-color: #DD0100;
        padding: 8px 15px;
        font-size: 14px;
    }
    
    .welcome-message p{
        text-align: center;
        margin-top: 70px;
        font-size: 5vw;
    }
    
    .vehicle-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .vehicle-card {
        max-width: 100%;
    }
    
    .vehicle-header {
        padding: 15px;
    }
    
    .vehicle-header h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
    }
    
    .vehicle-plate {
        padding: 5px 10px;
        font-size: 0.85rem;
    }
    
    .vehicle-icon {
        width: 35px;
        height: 35px;
        font-size: 16px;
        top: 10px;
        right: 10px;
    }
    
    .brand-logo {
        width: 30px;
        height: 30px;
        padding: 4px;
    }
    
    .vehicle-body {
        padding: 15px;
    }
    
    .vehicle-info {
        padding: 10px;
        margin-bottom: 12px;
    }
    
    .info-item {
        margin-bottom: 6px;
        font-size: 0.8rem;
    }
    
    .info-item:last-child {
        margin-bottom: 0;
    }
    
    .info-label {
        width: 70px;
        font-size: 0.8rem;
    }
    
    .info-value {
        font-size: 0.8rem;
        padding-left: 6px;
    }
    
    .vehicle-actions {
        flex-direction: row;
        gap: 8px;
    }
    
    .btn-edit,
    .btn-delete {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    
    .tips-grid,
    .curiosities-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .tip-card,
    .curiosity-card {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .mobile-welcome-text {
        font-size: 16px;
        padding: 6px 12px;
    }
    
    .btn-new-vehicle-mobile {
        padding: 10px 20px;
        font-size: 14px;
    }
}

@media (max-width: 360px) {
    .mobile-welcome-text {
        font-size: 14px;
        padding: 4px 8px;
        top: 60px;
    }
    
    .btn-new-vehicle-mobile {
        padding: 8px 16px;
        font-size: 13px;
    }
}
</style>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Meus Veículos</div>
    <div class="welcome-message">
        <h2><i class="fas fa-car"></i> Meus Veículos</h2>
        <p>Gerencie seus veículos cadastrados e adicione novos.</p>
    </div>
    <div class="welcome-actions">
        <a href="veiculo-novo.php" class="btn" data-tooltip="Adicionar um novo veículo">
            <i class="fas fa-plus"></i> Novo Veículo
        </a>
    </div>
</div>

<?php mostrarAlerta(); ?>

<!-- Botão Novo Veículo Mobile -->
<div class="mobile-new-vehicle-btn">
    <a href="veiculo-novo.php" class="btn-new-vehicle-mobile">
        <i class="fas fa-plus"></i> Novo Veículo
    </a>
</div>

<div class="vehicle-grid">
    <?php if ($veiculos->num_rows > 0): ?>
        <?php while ($veiculo = $veiculos->fetch_assoc()): ?>
            <div class="vehicle-card">
                <div class="vehicle-header">
                    <h3><?php echo $veiculo['marca'] . ' ' . $veiculo['modelo']; ?></h3>
                    <div class="vehicle-plate"><?php echo $veiculo['placa']; ?></div>
                    <?php 
                    $logoUrl = getBrandLogo($veiculo['marca']);
                    if ($logoUrl): 
                    ?>
                        <img src="<?php echo $logoUrl; ?>" alt="<?php echo $veiculo['marca']; ?>" class="vehicle-icon brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-car vehicle-icon" style="display: none;"></i>
                    <?php else: ?>
                        <i class="fas fa-car vehicle-icon"></i>
                    <?php endif; ?>
                </div>
                <div class="vehicle-body">
                    <div class="vehicle-info">
                        <div class="info-item">
                            <div class="info-label">Ano:</div>
                            <div class="info-value"><?php echo $veiculo['ano']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Cor:</div>
                            <div class="info-value"><?php echo $veiculo['cor']; ?></div>
                        </div>
                        <?php if (!empty($veiculo['quilometragem'])): ?>
                        <div class="info-item">
                            <div class="info-label">KM:</div>
                            <div class="info-value"><?php echo number_format($veiculo['quilometragem'], 0, ',', '.') . ' km'; ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($veiculo['observacoes'])): ?>
                        <div class="info-item">
                            <div class="info-label">Observações:</div>
                            <div class="info-value"><?php echo $veiculo['observacoes']; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="vehicle-actions">
                        <a href="veiculo-editar.php?id=<?php echo $veiculo['id']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button class="btn-delete" onclick="showDeleteModal(<?php echo $veiculo['id']; ?>, '<?php echo $veiculo['marca'] . ' ' . $veiculo['modelo']; ?>')">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-car-side"></i>
            <h3>Nenhum veículo cadastrado</h3>
            <p>Você ainda não possui veículos cadastrados. Adicione seu primeiro veículo para agendar serviços.</p>
            <a href="veiculo-novo.php" class="btn-new">
                <i class="fas fa-plus"></i> Novo Veículo
            </a>
        </div>
    <?php endif; ?>
</div>

<h2><i class="fas fa-tools"></i> Dicas de Cuidado com seu Veículo</h2>
<div class="tips-grid">
    <div class="tip-card">
        <div class="tip-icon">
            <i class="fas fa-oil-can"></i>
        </div>
        <div class="tip-content">
            <h3>Economize com Óleo</h3>
            <p>Troque o óleo a cada 10.000 km e economize até R$ 500 por ano! Óleo sintético dura mais e protege melhor seu motor.</p>
        </div>
    </div>
    <div class="tip-card">
        <div class="tip-icon">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <div class="tip-content">
            <h3>Pneus = Economia</h3>
            <p>Pneus calibrados corretamente podem economizar até 15% de combustível! Verifique a pressão semanalmente.</p>
        </div>
    </div>
    <div class="tip-card">
        <div class="tip-icon">
            <i class="fas fa-hand-paper"></i>
        </div>
        <div class="tip-content">
            <h3>Freios Salvam Vidas</h3>
            <p>Ruído nos freios? Não ignore! Trocar pastilhas custa R$ 200, trocar discos custa R$ 800. Previna-se!</p>
        </div>
    </div>
    <div class="tip-card">
        <div class="tip-icon">
            <i class="fas fa-car-battery"></i>
        </div>
        <div class="tip-content">
            <h3>Bateria Durável</h3>
            <p>Limpe os terminais mensalmente e sua bateria pode durar até 5 anos! Corrosão reduz a vida útil pela metade.</p>
        </div>
    </div>
</div>

<h2><i class="fas fa-lightbulb"></i> Curiosidades Automotivas</h2>
<div class="curiosities-grid">
    <div class="curiosity-card">
        <h3>Ferrari vs Lamborghini</h3>
        <p>A Lamborghini só existe porque Enzo Ferrari insultou Ferruccio Lamborghini! Ele disse que Lamborghini "sabia fazer tratores, não carros esportivos".</p>
    </div>
    <div class="curiosity-card">
        <h3>Carro Mais Caro</h3>
        <p>O carro mais caro já vendido foi uma Ferrari 250 GTO de 1962 por US$ 70 milhões! Mais caro que muitos aviões particulares!</p>
    </div>
    <div class="curiosity-card">
        <h3>Carros Elétricos do Passado</h3>
        <p>Em 1900, 38% dos carros americanos eram elétricos! O primeiro carro a quebrar 100 km/h foi elétrico em 1899!</p>
    </div>
    <div class="curiosity-card">
        <h3>Velocidade Insana</h3>
        <p>O Bugatti Chiron acelera de 0 a 100 km/h em apenas 2,4 segundos! Mais rápido que você pisca os olhos 3 vezes!</p>
    </div>
    <div class="curiosity-card">
        <h3>Volvo e Segurança</h3>
        <p>A Volvo inventou o cinto de segurança de 3 pontos e liberou a patente de graça para salvar vidas! Já salvou mais de 1 milhão de pessoas!</p>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
        <p>Tem certeza que deseja excluir o veículo <strong id="vehicleName"></strong>?</p>
        <p style="font-size: 14px; color: #e74c3c;">Esta ação não pode ser desfeita.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="hideDeleteModal()">Cancelar</button>
            <a href="#" class="btn-confirm" id="confirmDelete">Excluir</a>
        </div>
    </div>
</div>

<script>
function showDeleteModal(id, name) {
    document.getElementById('vehicleName').textContent = name;
    document.getElementById('confirmDelete').href = 'veiculos.php?excluir=' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Fechar modal ao clicar fora
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});
</script>

<?php include 'footer.php'; ?>