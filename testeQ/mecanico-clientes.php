<?php
$titulo = "Meus Clientes";
require_once 'header.php';

if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Buscar clientes únicos que já solicitaram diagnósticos
$stmt = $conexao->prepare("
    SELECT u.*, COUNT(r.id) as total_diagnosticos,
           MAX(r.data_envio) as ultimo_diagnostico
    FROM usuarios u
    JOIN relatorios_cliente r ON u.id = r.usuario_id
    WHERE r.mecanico_id = ?
    GROUP BY u.id
    ORDER BY ultimo_diagnostico DESC
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$clientes = $stmt->get_result();

$conexao->close();
?>

<style>
    .clientes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .cliente-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .cliente-card:hover {
        transform: translateY(-5px);
    }
    
    .cliente-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .cliente-avatar {
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
    }
    
    .cliente-info h3 {
        margin: 0 0 5px 0;
        color: #2c3e50;
    }
    
    .cliente-email {
        color: #666;
        font-size: 0.9rem;
    }
    
    .cliente-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 15px 0;
    }
    
    .stat-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .stat-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #109349;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #666;
    }
    
    .cliente-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-action {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        text-align: center;
        font-size: 0.8rem;
        cursor: pointer;
    }
    
    .btn-primary {
        background: #109349;
        color: white;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .search-bar {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .search-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #eee;
        border-radius: 8px;
        font-size: 16px;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #109349;
    }
</style>

<div class="search-bar">
    <input type="text" class="search-input" placeholder="🔍 Buscar cliente por nome ou email..." onkeyup="filtrarClientes(this.value)">
</div>

<div class="clientes-grid" id="clientesGrid">
    <?php if ($clientes->num_rows > 0): ?>
        <?php while ($cliente = $clientes->fetch_assoc()): ?>
            <div class="cliente-card" data-nome="<?php echo strtolower($cliente['nome']); ?>" data-email="<?php echo strtolower($cliente['email']); ?>">
                <div class="cliente-header">
                    <div class="cliente-avatar">
                        <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                    </div>
                    <div class="cliente-info">
                        <h3><?php echo $cliente['nome']; ?></h3>
                        <div class="cliente-email"><?php echo $cliente['email']; ?></div>
                    </div>
                </div>
                
                <div class="cliente-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $cliente['total_diagnosticos']; ?></div>
                        <div class="stat-label">Diagnósticos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php 
                            $dias = floor((time() - strtotime($cliente['ultimo_diagnostico'])) / (60 * 60 * 24));
                            echo $dias;
                            ?>
                        </div>
                        <div class="stat-label">Dias atrás</div>
                    </div>
                </div>
                
                <div style="margin: 10px 0; font-size: 0.9rem; color: #666;">
                    <i class="fas fa-phone"></i> <?php echo $cliente['telefone'] ?? 'Não informado'; ?>
                </div>
                
                <div style="margin: 10px 0; font-size: 0.8rem; color: #888;">
                    Último diagnóstico: <?php echo formatarData($cliente['ultimo_diagnostico'], 'd/m/Y'); ?>
                </div>
                
                <div class="cliente-actions">
                    <a href="tel:<?php echo $cliente['telefone']; ?>" class="btn-action btn-primary">
                        <i class="fas fa-phone"></i> Ligar
                    </a>
                    <a href="mailto:<?php echo $cliente['email']; ?>" class="btn-action btn-secondary">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <h3>Nenhum cliente encontrado</h3>
            <p>Você ainda não atendeu nenhum cliente.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function filtrarClientes(termo) {
    const cards = document.querySelectorAll('.cliente-card');
    const termoBusca = termo.toLowerCase();
    
    cards.forEach(card => {
        const nome = card.getAttribute('data-nome');
        const email = card.getAttribute('data-email');
        
        if (nome.includes(termoBusca) || email.includes(termoBusca)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>