<?php
// Todo código PHP deve vir ANTES de qualquer HTML
require_once 'config.php';

// Verificar login
verificarLogin();

// Define o título da página baseado no tipo de usuário
if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0) {
    // Redirecionar mecânicos para seu dashboard específico
    header("Location: mecanico-dashboard.php");
    exit;
}

// Define os botões do cabeçalho
$botoes_header = [
    [
        'url' => 'agendamento-novo.php',
        'icone' => 'fas fa-plus',
        'texto' => 'Novo Agendamento'
    ],
    [
        'url' => 'veiculo-novo.php',
        'icone' => 'fas fa-car',
        'texto' => 'Adicionar Veículo',
        'classe' => 'btn-outline'
    ]
];

// Busca informações do usuário
$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Busca os veículos do usuário
if ($usuario_id > 0 && $conexao->query("SHOW TABLES LIKE 'veiculos'")->num_rows > 0) {
    $check_col = $conexao->query("SHOW COLUMNS FROM veiculos LIKE 'usuario_id'");
    if ($check_col && $check_col->num_rows > 0) {
        $stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $veiculos = $stmt->get_result();
        $stmt->close();
    } else {
        $veiculos = new stdClass();
        $veiculos->num_rows = 0;
    }
} else {
    $veiculos = new stdClass();
    $veiculos->num_rows = 0;
}

// Busca os agendamentos do usuário
if ($usuario_id > 0 && $conexao->query("SHOW TABLES LIKE 'agendamentos'")->num_rows > 0) {
    $stmt = $conexao->prepare("
        SELECT a.*, v.marca, v.modelo, v.placa 
        FROM agendamentos a 
        LEFT JOIN veiculos v ON a.veiculo_id = v.id 
        WHERE a.usuario_id = ? 
        ORDER BY a.data_agendamento DESC, a.hora_inicio DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $agendamentos = $stmt->get_result();
    $stmt->close();
} else {
    $agendamentos = new stdClass();
    $agendamentos->num_rows = 0;
}

// Busca os serviços mais populares
$servicos_populares = new stdClass();
$servicos_populares->num_rows = 0;

// Buscar promoções ativas para o carrossel
$promocoes_carousel = null;
if ($conexao->query("SHOW TABLES LIKE 'promocoes_carousel'")->num_rows > 0) {
    $promocoes_carousel = $conexao->query("
        SELECT * FROM promocoes_carousel 
        WHERE ativo = 1 
        ORDER BY id DESC
    ");
}

$conexao->close();

// Verificar se é o primeiro acesso
$conexao = conectarBD();
$check_column = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'primeiro_acesso'");
if ($check_column && $check_column->num_rows > 0) {
    $stmt = $conexao->prepare("SELECT primeiro_acesso FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $primeiro_acesso = $result['primeiro_acesso'] ?? 1;
    $stmt->close();
} else {
    $primeiro_acesso = 0;
}
$conexao->close();

// Inclui o cabeçalho (agora que todo PHP foi processado)
require_once 'header.php';
?>

<!-- Modal de Boas-Vindas -->
<?php if ($primeiro_acesso == 1): ?>
<style>
.welcome-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-modal-content {
    background: linear-gradient(135deg, #009246 0%, #ffffff 50%, #CE2B37 100%);
    border-radius: 20px;
    padding: 3px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    position: relative;
}

.welcome-modal-inner {
    background: white;
    border-radius: 18px;
    padding: 40px;
}

.theme-alemanha .welcome-modal-content {
    background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
}

.theme-alemanha .welcome-modal-inner {
    background: #1a1a1a;
}

.welcome-modal-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.welcome-modal-title {
    color: #109349;
    margin-bottom: 15px;
    font-size: 2rem;
    font-weight: 700;
}

.theme-alemanha .welcome-modal-title {
    color: #FFCE00;
}

.welcome-modal-text {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.theme-alemanha .welcome-modal-text {
    color: #ccc;
}

.welcome-modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.welcome-btn {
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.welcome-btn-secondary {
    background: #6c757d;
    color: white;
}

.welcome-btn-secondary:hover {
    background: #5a6268;
}

.welcome-btn-primary {
    background: #109349;
    color: white;
}

.theme-alemanha .welcome-btn-primary {
    background: #FFCE00;
    color: #000;
}

.welcome-btn-primary:hover {
    background: #0d7a3a;
}

.theme-alemanha .welcome-btn-primary:hover {
    background: #e6b800;
}

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    z-index: 10001;
    pointer-events: none;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}
</style>

<div id="welcomeModal" class="welcome-modal-overlay">
    <div class="welcome-modal-content">
        <div class="welcome-modal-inner">
            <div class="welcome-modal-icon">🎉</div>
            <h2 class="welcome-modal-title">Bem-vindo à FullTorque!</h2>
            <p class="welcome-modal-text">Olá <strong><?php echo isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Cliente'; ?></strong>! Estamos felizes em tê-lo conosco. Explore nossos serviços e agende sua primeira manutenção.</p>
            <div class="welcome-modal-buttons">
                <button onclick="closeWelcomeModal(false)" class="welcome-btn welcome-btn-secondary">Fechar</button>
                <button onclick="closeWelcomeModal(true)" class="welcome-btn welcome-btn-primary">Não mostrar novamente</button>
            </div>
        </div>
    </div>
</div>

<script>
function createConfetti() {
    const colors = ['#009246', '#CE2B37', '#FFCE00', '#DD0100', '#000000'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.animationDelay = (Math.random() * 2) + 's';
        confetti.style.animation = 'confetti-fall linear forwards';
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 5000);
    }
}

function closeWelcomeModal(naoMostrar) {
    document.getElementById('welcomeModal').style.display = 'none';
    if (naoMostrar) {
        fetch('processar-primeiro-acesso.php', { method: 'POST' });
    }
}

// Iniciar confetes ao carregar
if (document.getElementById('welcomeModal')) {
    createConfetti();
}
</script>
<?php endif; ?>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Bem-vindo, <?php echo isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Cliente'; ?>!</div>
    <div class="welcome-message">
        <h2><span class="text-white">Bem-vindo</span> <span class="text-user"><?php echo isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Cliente'; ?></span>!</h2>
        <p>Confira o resumo das suas atividades e serviços.</p>
    </div>
    <div class="welcome-actions">
        <div class="clock-widget">
            <i class="fas fa-clock"></i>
            <span id="current-time"></span>
        </div>
        <a href="agendamento-novo.php" class="btn" data-tooltip="Agendar um novo serviço">
            <i class="fas fa-calendar-plus"></i> Agendar Serviço
        </a>
    </div>
</div>

<!-- Carrossel de Promoções -->
<?php 
// Debug: verificar se há promoções
if ($promocoes_carousel) {
    echo "<!-- Debug: Encontradas " . $promocoes_carousel->num_rows . " promoções ativas -->";
    // Resetar ponteiro para usar novamente
    $promocoes_carousel->data_seek(0);
} else {
    echo "<!-- Debug: Nenhuma promoção encontrada ou tabela não existe -->";
}
?>
<?php 
$promocoes_exemplo = [
    [
        'titulo' => 'PREÇO DE BANANA!',
        'descricao' => 'Troca de óleo + filtro',
        'desconto_percentual' => 50,
        'codigo_cupom' => 'BANANA50',
        'imagem' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80',
        'cor' => 'rgba(221,1,1,0.7)'
    ],
    [
        'titulo' => 'TACA-LE PAU!',
        'descricao' => 'Revisão completa',
        'desconto_percentual' => 40,
        'codigo_cupom' => 'TACALEPAU40',
        'imagem' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&q=80',
        'cor' => 'rgba(16,147,73,0.7)'
    ],
    [
        'titulo' => 'TÁ BARATO DEMAIS!',
        'descricao' => 'Alinhamento + Balanceamento',
        'desconto_percentual' => 45,
        'codigo_cupom' => 'BARATO45',
        'imagem' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&q=80',
        'cor' => 'rgba(255,152,0,0.7)'
    ],
    [
        'titulo' => 'QUEIMA DE ESTOQUE!',
        'descricao' => 'Pastilhas de freio',
        'desconto_percentual' => 35,
        'codigo_cupom' => 'QUEIMA35',
        'imagem' => 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80',
        'cor' => 'rgba(33,150,243,0.7)'
    ],
    [
        'titulo' => 'IMPERDÍVEL!',
        'descricao' => 'Troca de pneus',
        'desconto_percentual' => 30,
        'codigo_cupom' => 'PNEU30',
        'imagem' => 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800&q=80',
        'cor' => 'rgba(156,39,176,0.7)'
    ]
];
?>

<div class="promocoes-carousel-container">
    <h2 class="carousel-title">🎉 PROMOÇÕES ESPECIAIS 🎉</h2>
    <div class="promocoes-carousel">
        <div class="carousel-track" id="carouselTrack">
            <?php foreach ($promocoes_exemplo as $promo): ?>
                <div class="carousel-slide">
                    <div class="promo-slide-card" style="background-image: linear-gradient(135deg, <?php echo $promo['cor']; ?> 0%, <?php echo $promo['cor']; ?> 100%), url('<?php echo $promo['imagem']; ?>'); background-size: cover; background-position: center; background-blend-mode: overlay;">
                        <img src="logo.png" alt="Full Torque" class="promo-logo">
                        <div class="promo-slide-content">
                            <h2 class="promo-slide-title"><?php echo $promo['titulo']; ?></h2>
                            <p class="promo-slide-desc"><?php echo $promo['descricao']; ?></p>
                        </div>
                        <div class="promo-bottom-left">
                            <span class="discount-badge"><?php echo $promo['desconto_percentual']; ?>% OFF</span>
                        </div>
                        <div class="promo-bottom-actions">
                            <span class="cupom-code" onclick="copiarCupom('<?php echo $promo['codigo_cupom']; ?>')" style="cursor: pointer;" title="Clique para copiar">CUPOM: <?php echo $promo['codigo_cupom']; ?></span>
                            <span class="btn-promo" onclick="abrirModalPromo('<?php echo addslashes($promo['titulo']); ?>', '<?php echo addslashes($promo['descricao']); ?>', <?php echo $promo['desconto_percentual']; ?>, '<?php echo $promo['codigo_cupom']; ?>', '<?php echo $promo['cor']; ?>')" style="cursor: pointer;">APROVEITAR OFERTA</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-btn carousel-prev" onclick="moveCarousel(-1)">❮</button>
        <button class="carousel-btn carousel-next" onclick="moveCarousel(1)">❯</button>
    </div>
    <div class="carousel-dots" id="carouselDots"></div>
</div>

<div class="dashboard-cards">
    <div class="card animate-ready">
        <div class="card-header">
            <div class="card-title">Veículos Cadastrados</div>
            <div class="card-icon">
                <i class="fas fa-car"></i>
            </div>
        </div>
        <div class="card-content">
            <div class="card-value"><?php echo $veiculos->num_rows; ?></div>
            <div class="card-label">Total de veículos</div>
        </div>
        <div class="card-footer">
            <a href="veiculos.php" class="card-link">Ver todos <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <div class="card animate-ready">
        <div class="card-header">
            <div class="card-title">Agendamentos</div>
            <div class="card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="card-content">
            <div class="card-value"><?php echo $agendamentos->num_rows; ?></div>
            <div class="card-label">Total de agendamentos</div>
        </div>
        <div class="card-footer">
            <a href="agendamentos.php" class="card-link">Ver todos <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <div class="card animate-ready">
        <div class="card-header">
            <div class="card-title">Próximo Serviço</div>
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="card-content">
            <?php
            // Reinicia o ponteiro do resultado
            $agendamentos->data_seek(0);
            
            // Busca o próximo agendamento
            $proximo_agendamento = null;
            while ($agendamento = $agendamentos->fetch_assoc()) {
                if ($agendamento['status'] == 'agendado' || $agendamento['status'] == 'confirmado') {
                    if (strtotime($agendamento['data_agendamento']) >= strtotime(date('Y-m-d'))) {
                        $proximo_agendamento = $agendamento;
                        break;
                    }
                }
            }
            
            if ($proximo_agendamento) {
                echo '<div class="next-service">';
                echo '<div class="next-service-date">' . formatarData($proximo_agendamento['data_agendamento'], 'd/m/Y') . '</div>';
                echo '<div class="next-service-time">' . substr($proximo_agendamento['hora_inicio'], 0, 5) . '</div>';
                echo '<div class="next-service-vehicle">' . $proximo_agendamento['marca'] . ' ' . $proximo_agendamento['modelo'] . '</div>';
                echo '</div>';
            } else {
                echo '<div class="no-service">Nenhum serviço agendado</div>';
            }
            ?>
        </div>
        <div class="card-footer">
            <a href="agendamento-novo.php" class="card-link">Agendar <i class="fas fa-plus"></i></a>
        </div>
    </div>
</div>

<div class="proximos-agendamentos">
    <div class="agendamentos-header">
        <h2><i class="fas fa-calendar-check"></i> Próximos Agendamentos</h2>
        <a href="agendamentos.php" class="ver-todos-btn">Ver todos</a>
    </div>
    
    <?php if ($agendamentos->num_rows > 0): ?>
        <div class="agendamentos-grid">
            <?php 
            $agendamentos->data_seek(0);
            $count = 0;
            while ($agendamento = $agendamentos->fetch_assoc()): 
                if ($count >= 3) break;
                $count++;
            ?>
                <div class="agendamento-card">
                    <div class="agendamento-data">
                        <span class="dia"><?php echo date('d', strtotime($agendamento['data_agendamento'])); ?></span>
                        <span class="mes"><?php echo strtoupper(date('M', strtotime($agendamento['data_agendamento']))); ?></span>
                    </div>
                    <div class="agendamento-info">
                        <h3><?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></h3>
                        <p><i class="fas fa-clock"></i> <?php echo substr($agendamento['hora_inicio'], 0, 5); ?></p>
                        <span class="status-badge status-<?php echo $agendamento['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?></span>
                    </div>
                    <a href="agendamento-detalhes.php?id=<?php echo $agendamento['id']; ?>" class="ver-detalhes"><i class="fas fa-arrow-right"></i></a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="sem-agendamentos">
            <i class="fas fa-calendar-times"></i>
            <p>Nenhum agendamento encontrado</p>
            <a href="agendamento-novo.php" class="btn"><i class="fas fa-plus"></i> Novo Agendamento</a>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-sections">
    <div class="section">
        <div class="section-header">
            <div class="section-title">Serviços Mais Utilizados</div>
        </div>
        
        <div class="services-chart">
            <?php if ($servicos_populares->num_rows > 0): ?>
                <div class="chart-container">
                    <div class="chart-bars">
                        <?php 
                        $max_value = 0;
                        $servicos = [];
                        
                        // Primeiro loop para encontrar o valor máximo
                        while ($servico = $servicos_populares->fetch_assoc()) {
                            $servicos[] = $servico;
                            if ($servico['total'] > $max_value) {
                                $max_value = $servico['total'];
                            }
                        }
                        
                        // Segundo loop para exibir as barras
                        foreach ($servicos as $servico) {
                            $porcentagem = ($servico['total'] / $max_value) * 100;
                            echo '<div class="chart-item">';
                            echo '<div class="chart-label">' . $servico['nome'] . '</div>';
                            echo '<div class="chart-bar-container">';
                            echo '<div class="chart-bar" style="width: ' . $porcentagem . '%"></div>';
                            echo '<div class="chart-value">' . $servico['total'] . '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <p>Nenhum serviço utilizado ainda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="section">
        <div class="section-header">
            <div class="section-title">Dicas de Manutenção</div>
        </div>
        
        <div class="tips-container">
            <div class="tip-card animate-ready">
                <div class="tip-icon">
                    <i class="fas fa-oil-can"></i>
                </div>
                <div class="tip-content">
                    <h3>Troca de Óleo</h3>
                    <p>Troque o óleo do motor a cada 5.000 a 10.000 km para garantir o bom funcionamento do motor.</p>
                </div>
            </div>
            
            <div class="tip-card animate-ready">
                <div class="tip-icon">
                    <i class="fas fa-car-battery"></i>
                </div>
                <div class="tip-content">
                    <h3>Bateria</h3>
                    <p>Verifique a bateria a cada 6 meses para evitar problemas de partida.</p>
                </div>
            </div>
            
            <div class="tip-card animate-ready">
                <div class="tip-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="tip-content">
                    <h3>Revisão Completa</h3>
                    <p>Faça uma revisão completa do veículo a cada 10.000 km ou 6 meses.</p>
                </div>
            </div>
            
            <div class="tip-card animate-ready">
                <div class="tip-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="tip-content">
                    <h3>Pneus</h3>
                    <p>Verifique a pressão dos pneus mensalmente e faça o rodízio a cada 10.000 km.</p>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
    body{
        background-color: #FFFFFF;
    }
    .btn{
        background-color: #CE2A37;
        color: white;
        border: none;
    }
    .btn:hover{
        background-color: #81000bff;
    }
    .theme-alemanha .btn{
        background-color: #FFCE00;
        color: black;
    }
     .theme-alemanha .btn:hover{
        background-color: #daaf03ff;
    }


/* CSS específico para index.php - sem animação */
.dashboard-welcome {
   background: linear-gradient(135deg, #109349 0%, #109349 33%, #FFFFFF 33%, #FFFFFF 66%, #DD0101 66%, #DD0101 100%) !important;
    color: white !important;
    border: 2px solid #109349 !important;
    border-radius: 20px !important;
    padding: 30px !important;
    margin-bottom: 30px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
    position: static !important;
    overflow: visible !important;
    background-size: auto !important;
    animation: none !important;
}

.dashboard-welcome::before {
    display: none !important;
}

.theme-alemanha .dashboard-welcome {
    background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%) !important;
    color: white !important;
    border: 2px solid #FFCE00 !important;
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
    white-space: nowrap;
}

.welcome-message h2 .text-white {
    color: #ffffff !important;
}

.welcome-message h2 .text-green {
    color: #DD0101 !important;
}

.welcome-message h2 .text-user {
    color: #ffffff !important;
    font-weight: 700 !important;
    text-shadow: 
        -1px -1px 0 rgba(221, 1, 0, 0.8),
        1px -1px 0 rgba(221, 1, 0, 0.8),
        -1px 1px 0 rgba(221, 1, 0, 0.8),
        1px 1px 0 rgba(221, 1, 0, 0.8) !important;
}

.theme-alemanha .welcome-message h2 .text-white {
    color: #ffffff !important;
}

.theme-alemanha .welcome-message h2 .text-green {
    color: #EFC202 !important;
}

.theme-alemanha .welcome-message h2 .text-user {
    color: #FFCE00 !important;
    font-weight: 700 !important;
    text-shadow: 
        -1px -1px 0 rgba(221, 1, 0, 0.8),
        1px -1px 0 rgba(221, 1, 0, 0.8),
        -1px 1px 0 rgba(221, 1, 0, 0.8),
        1px 1px 0 rgba(221, 1, 0, 0.8) !important;
}

.welcome-message p {
    opacity: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
    font-weight: 600;
    color: #fff;
}

.welcome-actions .btn {
    background-color: #109349;
    color: #fff;
    border: none;
}
.theme-alemanha .welcome-actions .btn{
    color: #fff;
    background-color: #000000;
}

.welcome-actions .btn:hover {
    background-color: #007f3790;
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
}
.theme-alemanha .welcome-actions .btn:hover{
    background-color: #00000074;
}

.welcome-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}

.clock-widget {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
    font-family: 'Courier New', monospace;
}

.theme-alemanha .clock-widget {
    background-color: rgba(0, 0, 0, 0.7);
    color: #FFD700;
}

.clock-widget i {
    font-size: 14px;
}

#current-time {
    font-size: 16px;
    letter-spacing: 1px;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 4px #DD0101, 0 4px 5px #DD0101;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    

}
.theme-alemanha .card{
    background-color: #000000;
    box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3), 0 4px 10px rgba(255, 206, 0, 0.15);
    border: none;
    color: white;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #dd0101;
}

.theme-alemanha .card:hover {
    box-shadow: 0 15px 40px rgba(255, 206, 0, 0.4), 0 8px 20px rgba(255, 206, 0, 0.25);
}

.card-header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    background-color: #DD0101;
}
.theme-alemanha .card-header{
    background-color: #FFCE00;
    border: none;
}

.card-title {
    font-weight: 600;
    color: white;
}

.card-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #DD0100;
    font-size: 1.2rem;
}
.theme-alemanha .card-icon{
    background-color: #0000003e ;
    color: #000000;
}

.card-content {
    padding: 20px 15px;
}
.theme-alemanha .card-content{
    border: none;
}

.card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 5px;
}
.theme-alemanha .card-value{
    color: #ffffffc7;
}
.card-label {
    color: #777;
    font-size: 0.9rem;
}
.theme-alemanha .card-label{
    color: #ffffffb0;
}

.card-footer {
    padding: 10px 15px;
    border-top: 1px solid #eee;
}
.theme-alemanha .card-footer{
    border:none;
}

.card-link {
    color: #DD0100 ;
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    transition: all 0.3s;
}
.theme-alemanha .card-link{
    color: #FFCE00;
}

.card-link i {
    margin-left: 5px;
    transition: all 0.3s;
}

.card-link:hover i {
    transform: translateX(3px);
}

.next-service {
    text-align: center;
}

.next-service-date {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--secondary-color);
}

.next-service-time {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 5px 0;
}

.next-service-vehicle {
    color: #777;
}

.no-service {
    text-align: center;
    color: #777;
    font-style: italic;
}
.theme-alemanha .no-service{
    color: #ffffffb0
}

.dashboard-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .dashboard-sections {
        grid-template-columns: 1fr;
        min-width: 0;
    }
}

.section {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 0;
}

@media (max-width: 768px) {
    .dashboard-welcome {
        background-image: url('bem-vindo-italia-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        justify-content: flex-end !important;
    }
    .dashboard-welcome .welcome-message {
        text-align: right !important;
        margin-left: auto !important;
        max-width: 60% !important;
        padding-right: 10px !important;
    }
    .dashboard-welcome .welcome-actions {
        text-align: center !important;
        margin-left: auto !important;
        max-width: 60% !important;
        padding-right: 10px !important;
        margin-top: 15px !important;
    }
    .dashboard-welcome .welcome-message h2 {
        margin-right: 10px !important;
    }
    .theme-alemanha .dashboard-welcome {
        background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
        background-size: cover !important;
        background-position: left center !important;
        background-repeat: no-repeat !important;
        justify-content: flex-end !important;
    }
    .theme-alemanha .dashboard-welcome .welcome-message {
        text-align: right !important;
        margin-left: auto !important;
        max-width: 60% !important;
        padding-right: 10px !important;
    }
    .theme-alemanha .dashboard-welcome .welcome-actions {
        text-align: center !important;
        margin-left: auto !important;
        max-width: 60% !important;
        padding-right: 10px !important;
        margin-top: 15px !important;
    }
    .theme-alemanha .dashboard-welcome .welcome-message h2 {
        margin-right: 10px !important;
        color: #EFC202 !important;
    }
    .theme-alemanha .dashboard-welcome .welcome-message p {
        color: #EFC202 !important;
    }
       .welcome-actions .btn {
        padding: 8px 12px !important;
        font-size: 12px !important;
    
    }
}

.section:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12), 0 6px 15px rgba(0, 0, 0, 0.08);
}

.theme-alemanha .section {
    background-color: #000000;
    box-shadow: 0 8px 25px rgba(255, 206, 0, 0.3), 0 4px 10px rgba(255, 206, 0, 0.15);
}

.theme-alemanha .section:hover {
    box-shadow: 0 12px 35px rgba(255, 206, 0, 0.4), 0 6px 15px rgba(255, 206, 0, 0.25);
}

.section-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.theme-alemanha .section-header{
    background-color: #000000;
    border: none;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--secondary-color);
}
.theme-alemanha .section-title{
    color: white;
}

.section-action {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}
.theme-alemanha .section-action{
    color: #FFCE00;
}

.section-action i {
    margin-left: 5px;
    transition: all 0.3s;
}

.section-action:hover i {
    transform: translateX(3px);
}

.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-container table {
    width: 100%;
}


table {
    width: 100%;
    border: 1px solid #fff;

}
.theme-alemanha table{
    box-shadow: 8px 0 20px #ffce00;
    background-color: #1a1a1a;
   border: 1px solid rgba(0, 0, 0, 1);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #fff;
}
 .theme-alemanha .table-container{
    background-color: #1a1a1a;
    border: 1px solid #000000;
}

th {
    background-color: #f9f9f9;
    font-weight: 600;
    color: black;
}
.theme-alemanha th{
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid #000000ff;
}
.theme-alemanha td{
    background-color: #000000;
    color: white;
    border: none;
}
.theme-alemanha tr:hover td{
    background-color: #232323ff;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background-color: #f9f9f9;
}

.status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-agendado {
    background-color: rgba(52, 152, 219, 0.1);
    color: var(--primary-color);
}

.status-confirmado {
    background-color: rgba(241, 196, 15, 0.1);
    color: #f1c40f;
}

.status-em-andamento {
    background-color: rgba(230, 126, 34, 0.1);
    color: #e67e22;
}

.status-concluido {
    background-color: rgba(46, 204, 113, 0.1);
    color: var(--success-color);
}

.status-cancelado {
    background-color: rgba(231, 76, 60, 0.1);
    color: var(--error-color);
}

.actions {
    display: flex;
    gap: 5px;
}

.action-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000000ff;
    color: #109349;
    text-decoration: none;
    transition: all 0.3s;
}
.theme-alemanha .action-btn{
    background-color: white;
    color: #000000

}
.action-btn2 {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000000ff;
    color: #FFFFFF;
    text-decoration: none;
    transition: all 0.3s;
}
.theme-alemanha .action-btn2{
    background-color: white;
    color: #DD0100;

}
.action-btn3 {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000000ff;
    color: #CE2A37;
    text-decoration: none;
    transition: all 0.3s;
}
.theme-alemanha .action-btn3{
    background-color: white;
    color: #FFCE00;

}

.theme-alemanha .action-btn:hover {
    background-color: #000000 ;
    color: white;
}
.action-btn:hover {
    background-color: #10934A;
    color: white;
}
.action-btn2:hover {
    background-color: #FFFFFF;
    color: #000000;
}
.action-btn3:hover {
    background-color: #DD0100;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 30px;
    color: #777;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #ddd;
}

.empty-state p {
    margin-bottom: 15px;
}

.chart-container {
    padding: 20px;
}

.chart-bars {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.chart-item {
    display: flex;
    align-items: center;
}

.chart-label {
    width: 150px;
    font-size: 0.9rem;
    color: var(--secondary-color);
}

.chart-bar-container {
    flex: 1;
    height: 30px;
    background-color: #f1f1f1;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
}

.chart-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), #2980b9);
    border-radius: 5px;
    transition: width 1s ease-in-out;
}

.chart-value {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 600;
    color: #333;
}

.tips-container {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.tip-card {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-radius: 5px;
    background-color: #f9f9f9;
    transition: all 0.3s;
}
.theme-alemanha .tip-card{
    background-color: #DD0100;
    color: white;
}
.theme-alemanha .tip-card:hover {
    background-color: #f74343ff;
    color: white;
}

.tip-card:hover {
    background-color: #f0f0f0;
    transform: translateY(-3px);
}

.tip-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #CE2A37;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(206, 42, 55, 0.3);
    transition: all 0.3s ease;
}
.theme-alemanha .tip-icon{
    background-color: #fff;
    color: #DD0100;
    box-shadow: 0 4px 12px rgba(255, 206, 0, 0.3);
}

.tip-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(206, 42, 55, 0.4);
}

.theme-alemanha .tip-icon:hover {
    box-shadow: 0 6px 16px rgba(255, 206, 0, 0.4);
}

.tip-content h3 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: var(--secondary-color);
}

.tip-content p {
    font-size: 0.9rem;
    color: #666;
}
.theme-alemanha .tip-content p{
    color: white;
}

/* Carrossel de Promoções */
.promocoes-carousel-container {
    margin-bottom: 40px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    padding: 0 20px;
}

.carousel-title {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 25px;
    color: #DD0101;
    font-weight: bold;
}

.theme-alemanha .carousel-title {
    color: #FFCE00;
}

.promocoes-carousel {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    height: 350px;
}

.carousel-track {
    display: flex;
    transition: transform 0.6s ease;
    height: 100%;
}

.carousel-slide {
    min-width: 100%;
    height: 100%;
}



.promo-slide-card {
    background: linear-gradient(135deg, #DD0101 0%, #8B0000 100%);
    color: white;
    padding: 40px 60px;
    height: 100%;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.theme-alemanha .promo-slide-card {
    background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
}

.promo-slide-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.promo-logo {
    position: absolute;
    top: 20px;
    right: 30px;
    max-width: 150px;
    height: auto;
    z-index: 3;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
}

.promo-slide-content {
    position: relative;
    z-index: 2;
    max-width: 700px;
}

.promo-bottom-left {
    position: absolute;
    bottom: 30px;
    left: 30px;
    z-index: 3;
}

.promo-bottom-actions {
    position: absolute;
    bottom: 30px;
    right: 30px;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 20px;
}

.promo-slide-title {
    font-size: 2rem;
    font-weight: 900;
    margin: 0 0 10px 0;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
    color: #fff;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.promo-slide-desc {
    font-size: 1rem;
    margin-bottom: 15px;
    line-height: 1.4;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.4);
    font-weight: 500;
}



.discount-badge {
    background: #FFCE00;
    color: #000;
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 1.5rem;
    font-weight: 900;
    display: inline-block;
    box-shadow: 0 5px 20px rgba(255,206,0,0.5);
}

.theme-alemanha .discount-badge {
    background: #fff;
    color: #DD0100;
}



.cupom-code {
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: bold;
    font-size: 0.95rem;
    border: 2px dashed rgba(255,255,255,0.6);
    display: inline-block;
    letter-spacing: 1px;
    backdrop-filter: blur(10px);
}





.btn-promo {
    background: #fff;
    color: #DD0101;
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: bold;
    font-size: 0.9rem;
    display: inline-block;
    letter-spacing: 1px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transition: all 0.3s;
}

.btn-promo:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

.theme-alemanha .btn-promo {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .promo-modal-btn-primary {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .promo-modal-btn-primary:hover {
    background: #e6b800;
    box-shadow: 0 5px 15px rgba(255,206,0,0.3);
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    color: #DD0101;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 10;
    border: none;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.carousel-btn:hover {
    background: #fff;
    transform: translateY(-50%) scale(1.1);
}

.carousel-prev {
    left: 15px;
}

.carousel-next {
    right: 15px;
}

.theme-alemanha .carousel-btn {
    color: #FFCE00;
}

.carousel-dots {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.carousel-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(221,1,1,0.3);
    cursor: pointer;
    transition: all 0.3s;
}

.carousel-dot.active {
    background: #DD0101;
    width: 30px;
    border-radius: 5px;
}

.theme-alemanha .carousel-dot {
    background: rgba(255,206,0,0.3);
}

.theme-alemanha .carousel-dot.active {
    background: #FFCE00;
}

/* Menu hambúrguer modo alemanha */
.theme-alemanha .mobile-menu-toggle {
    background-color: #FFCE00 !important;
    color: #000000 !important;
}

.theme-alemanha .mobile-menu-toggle:hover {
    background-color: #e6b800 !important;
}
@media (max-width: 768px) {
    .theme-alemanha .mobile-menu-toggle {
        background-color: #FFCE00 !important;
        color: #000000 !important;
    }
    
    .table-container {
        width: 100%;
        overflow-x: scroll;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-container table {
        display: block;
        width: 100%;
    }
    
    .table-container thead {
        display: block;
        width: 100%;
    }
    
    .table-container tbody {
        display: block;
        width: 100%;
    }
    
    .table-container tr {
        display: flex;
        width: max-content;
        min-width: 100%;
    }
    
    .table-container th,
    .table-container td {
        display: block;
        flex: 0 0 auto;
        padding: 10px 8px;
        font-size: 0.8rem;
    }
    
    .table-container th:nth-child(1),
    .table-container td:nth-child(1) {
        width: 90px;
    }
    
    .table-container th:nth-child(2),
    .table-container td:nth-child(2) {
        width: 80px;
    }
    
    .table-container th:nth-child(3),
    .table-container td:nth-child(3) {
        width: 200px;
    }
    
    .table-container th:nth-child(4),
    .table-container td:nth-child(4) {
        width: 100px;
    }
    
    .table-container th:nth-child(5),
    .table-container td:nth-child(5) {
        width: 110px;
    }
    
    .dashboard-welcome {
        padding: 15px 12px;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 15px;
        flex-direction: column;
        position: relative;
        min-height: 200px;
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
    
    .welcome-message h2 {
        text-align: center;
        font-size: 1.3rem;
        margin-bottom: 8px;
        color: green;
        margin-left: 0;
    }
    
    .welcome-message p {
        text-align: center;
        font-size: 11px;
        margin-bottom: 15px;
        color: green;
        margin-right: 0;
    }
    
    .welcome-actions {
        display: none !important;
    }
    
    .clock-widget {
        display: none !important;
    }
    
    .promocoes-carousel-container {
        padding: 0 10px;
    }
    
    .promocoes-carousel {
        height: 300px;
    }
    
    .promo-slide-card {
        padding: 30px 25px;
    }
    
    .promo-slide-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 70%;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        z-index: 1;
    }
    
    .promo-slide-content {
        z-index: 2;
    }
    
    .promo-bottom-left {
        bottom: 20px;
        left: 20px;
    }
    
    .promo-bottom-actions {
        bottom: 20px;
        right: 20px;
        gap: 15px;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .promo-logo {
        max-width: 100px;
        top: 15px;
        right: 15px;
    }
    
    .promo-slide-title {
        font-size: 1.5rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.9);
    }
    
    .promo-slide-desc {
        font-size: 0.9rem;
        text-shadow: 1px 1px 6px rgba(0,0,0,0.9);
    }
    
    .discount-badge {
        font-size: 1.3rem;
        padding: 8px 16px;
    }
    
    .cupom-code {
        font-size: 0.85rem;
        padding: 6px 12px;
        background: rgba(0,0,0,0.6);
    }
    
    .btn-promo {
        font-size: 0.85rem;
        padding: 10px 22px;
    }
    
    .carousel-btn {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .dashboard-cards {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .dashboard-sections {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .theme-alemanha .mobile-menu-toggle {
        background-color: #FFCE00 !important;
        color: #000000 !important;
    }
    
    .section-header {
        overflow-x: auto;
        scroll-behavior: smooth;
        display: flex;
    }
    .dashboard-welcome::before {
        font-size: 12px !important;
        padding: 6px 12px !important;
        top: 10px !important;
    }
    
    .promocoes-carousel {
        height: 280px;
    }
    
    .welcome-message h2 {
        font-size: 1.3rem !important;
        margin-left: -72px;
        margin-top: 90px;
    }
    
    .welcome-message p {
        font-size: 1.1rem !important;
    }
    
    .promo-slide-card {
        padding: 25px 20px;
    }
    
    .promo-slide-card::after {
        height: 75%;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 100%);
    }
    
    .promo-bottom-left {
        bottom: 15px;
        left: 15px;
    }
    
    .promo-bottom-actions {
        bottom: 15px;
        right: 15px;
        gap: 10px;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .promo-logo {
        max-width: 80px;
        top: 10px;
        right: 10px;
    }
    
    .promo-slide-title {
        font-size: 1.2rem;
        text-shadow: 2px 2px 10px rgba(0,0,0,1);
    }
    
    .promo-slide-desc {
        font-size: 0.8rem;
        text-shadow: 1px 1px 8px rgba(0,0,0,1);
    }
    
    .discount-badge {
        font-size: 1.1rem;
        padding: 6px 14px;
    }
    
    .cupom-code {
        font-size: 0.75rem;
        padding: 6px 10px;
        background: rgba(0,0,0,0.7);
    }
    
    .btn-promo {
        font-size: 0.75rem;
        padding: 8px 16px;
    }
    
    .carousel-btn {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .carousel-title {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    .dashboard-cards {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .dashboard-sections {
        grid-template-columns: 1fr;
        gap: 10px;
        
    }
        .welcome-actions .btn{
            display: none;
        }
}

@media (max-width: 360px) {
    .theme-alemanha .mobile-menu-toggle {
        background-color: #FFCE00 !important;
        color: #000000 !important;
    }
    
    .dashboard-welcome::before {
        font-size: 11px !important;
        padding: 5px 10px !important;
        top: 8px !important;
    }
    
    .dashboard-cards {
        gap: 8px;
    }
    .welcome-actions .btn{
            display: none;
        }
    .dashboard-sections {
        gap: 8px;
    }
}

/* Tablets em modo paisagem */
@media (max-width: 1024px) and (orientation: landscape) {
    .dashboard-welcome::before {
        font-size: 13px;
        padding: 7px 14px;
        top: 12px;
    }
}

/* Tablets em modo retrato */
@media (max-width: 768px) and (min-width: 481px) {
    .dashboard-welcome::before {
        font-size: 15px !important;
        padding: 9px 16px !important;
        top: 18px !important;
    }
}

/* Nova Seção Próximos Agendamentos */
.proximos-agendamentos {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.theme-alemanha .proximos-agendamentos {
    background: #000;
    box-shadow: 0 4px 15px rgba(255,206,0,0.3);
}

.agendamentos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #DD0101;
}

.theme-alemanha .agendamentos-header {
    border-bottom-color: #FFCE00;
}

.agendamentos-header h2 {
    color: #DD0101;
    font-size: 1.5rem;
    margin: 0;
}

.theme-alemanha .agendamentos-header h2 {
    color: #FFCE00;
}

.ver-todos-btn {
    background: #DD0101;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.theme-alemanha .ver-todos-btn {
    background: #FFCE00;
    color: #000;
}

.ver-todos-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(221,1,1,0.3);
}

.agendamentos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.agendamento-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.theme-alemanha .agendamento-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-color: #333;
}

.agendamento-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #DD0101;
}

.theme-alemanha .agendamento-card:hover {
    border-color: #FFCE00;
}

.agendamento-data {
    background: #DD0101;
    color: white;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    min-width: 70px;
}

.theme-alemanha .agendamento-data {
    background: #FFCE00;
    color: #000;
}

.agendamento-data .dia {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.agendamento-data .mes {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    margin-top: 5px;
}

.agendamento-info {
    flex: 1;
}

.agendamento-info h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.1rem;
}

.theme-alemanha .agendamento-info h3 {
    color: #fff;
}

.agendamento-info p {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 0.9rem;
}

.theme-alemanha .agendamento-info p {
    color: #ccc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-agendado {
    background: #3498db;
    color: white;
}

.status-confirmado {
    background: #f1c40f;
    color: #333;
}

.status-em_andamento {
    background: #e67e22;
    color: white;
}

.status-concluido {
    background: #2ecc71;
    color: white;
}

.status-cancelado {
    background: #e74c3c;
    color: white;
}

.ver-detalhes {
    background: #109349;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.theme-alemanha .ver-detalhes {
    background: #000;
    border: 2px solid #FFCE00;
    color: #FFCE00;
}

.ver-detalhes:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(16,147,73,0.4);
}

.sem-agendamentos {
    text-align: center;
    padding: 40px;
    color: #999;
}

.sem-agendamentos i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #ddd;
}

.theme-alemanha .sem-agendamentos {
    color: #666;
}

.theme-alemanha .sem-agendamentos i {
    color: #444;
}

@media (max-width: 768px) {
    .proximos-agendamentos {
        padding: 15px;
    }
    
    .agendamentos-header h2 {
        font-size: 1.2rem;
    }
    
    .ver-todos-btn {
        padding: 6px 15px;
        font-size: 0.85rem;
    }
    
    .agendamentos-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .agendamento-card {
        padding: 15px;
    }
    
    .agendamento-data {
        min-width: 60px;
        padding: 12px;
    }
    
    .agendamento-data .dia {
        font-size: 1.5rem;
    }
    
    .agendamento-data .mes {
        font-size: 0.8rem;
    }
    
    .agendamento-info h3 {
        font-size: 1rem;
    }
    
    .agendamento-info p {
        font-size: 0.85rem;
    }
    
    .ver-detalhes {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 480px) {
    .agendamentos-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .agendamentos-header h2 {
        font-size: 1.1rem;
    }
    
    .ver-todos-btn {
        width: 100%;
        text-align: center;
    }
}

/* Modal de Promoção */
.promo-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.promo-modal-overlay.active {
    display: flex;
}

.promo-modal {
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: slideUp 0.4s ease;
}

.theme-alemanha .promo-modal {
    background: #000;
    border: 2px solid #FFCE00;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.promo-modal-header {
    padding: 30px;
    color: white;
    position: relative;
    border-radius: 20px 20px 0 0;
}

.promo-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s;
}

.promo-modal-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.promo-modal-title {
    font-size: 2rem;
    margin: 0 0 10px 0;
    font-weight: 900;
}

.promo-modal-desc {
    font-size: 1.1rem;
    margin: 0;
    opacity: 0.9;
}

.promo-modal-body {
    padding: 30px;
}

.promo-info-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.theme-alemanha .promo-info-item {
    background: #1a1a1a;
    border-left-width: 4px;
}

.promo-info-item h3 {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
    color: #333;
}

.theme-alemanha .promo-info-item h3 {
    color: #FFCE00;
}

.promo-info-item p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.theme-alemanha .promo-info-item p {
    color: #ccc;
}

.promo-services-list {
    list-style: none;
    padding: 0;
    margin: 15px 0 0 0;
}

.promo-services-list li {
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}

.theme-alemanha .promo-services-list li {
    border-bottom-color: #333;
    color: #fff;
}

.promo-services-list li:last-child {
    border-bottom: none;
}

.promo-services-list li i {
    color: #109349;
    font-size: 1.1rem;
}

.theme-alemanha .promo-services-list li i {
    color: #FFCE00;
}

.promo-modal-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.promo-modal-btn {
    flex: 1;
    padding: 15px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.promo-modal-btn-primary {
    background: #DD0101;
    color: white;
}

.promo-modal-btn-primary:hover {
    background: #b00101;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(221,1,1,0.3);
}

.promo-modal-btn-secondary {
    background: #f8f9fa;
    color: #333;
}

.theme-alemanha .promo-modal-btn-secondary {
    background: #333;
    color: #fff;
}

.promo-modal-btn-secondary:hover {
    background: #e9ecef;
}

.theme-alemanha .promo-modal-btn-secondary:hover {
    background: #444;
}

.cupom-copy-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    margin-top: 20px;
    border: 2px dashed #ddd;
}

.theme-alemanha .cupom-copy-box {
    background: #000;
    border-color: #FFCE00;
}

.cupom-copy-code {
    font-size: 1.5rem;
    font-weight: 900;
    color: #DD0101;
    letter-spacing: 2px;
    display: block;
    margin-bottom: 10px;
}

.theme-alemanha .cupom-copy-code {
    color: #FFCE00;
}

.cupom-copy-btn {
    background: #109349;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.theme-alemanha .cupom-copy-btn {
    background: #FFCE00;
    color: #000;
}

.cupom-copy-btn:hover {
    background: #0d7a3a;
    transform: scale(1.05);
}

.theme-alemanha .cupom-copy-btn:hover {
    background: #e6b800;
}

@media (max-width: 1024px) {
    .promo-modal {
        max-width: 550px;
    }
}

@media (max-width: 768px) {
    .promo-modal {
        width: 95%;
        max-width: 500px;
    }
    
    .promo-modal-header {
        padding: 20px;
    }
    
    .promo-modal-title {
        font-size: 1.5rem;
    }
    
    .promo-modal-body {
        padding: 20px;
    }
    
    .promo-info-item {
        padding: 15px;
    }
    
    .promo-modal-actions {
        flex-direction: column;
    }
    
    .cupom-copy-code {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .promo-modal {
        width: 95%;
        max-height: 85vh;
    }
    
    .promo-modal-header {
        padding: 15px;
    }
    
    .promo-modal-title {
        font-size: 1.3rem;
    }
    
    .promo-modal-desc {
        font-size: 0.95rem;
    }
    
    .promo-modal-body {
        padding: 15px;
    }
    
    .promo-info-item {
        padding: 12px;
    }
    
    .promo-info-item h3 {
        font-size: 1rem;
    }
    
    .promo-info-item p {
        font-size: 0.9rem;
    }
    
    .promo-services-list li {
        font-size: 0.9rem;
        padding: 8px 0;
    }
    
    .cupom-copy-code {
        font-size: 1.1rem;
    }
    
    .promo-modal-btn {
        padding: 12px;
        font-size: 0.9rem;
    }
}

@media (max-width: 360px) {
    .promo-modal-title {
        font-size: 1.1rem;
    }
    
    .promo-modal-desc {
        font-size: 0.85rem;
    }
    
    .cupom-copy-code {
        font-size: 1rem;
    }
}

/* Toast Message */
.toast-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #2ecc71;
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
}

.toast-message.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-message i {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .toast-message {
        top: 10px;
        right: 10px;
        left: 10px;
        padding: 12px 20px;
        font-size: 0.9rem;
    }
}

</style>

<!-- Modal de Promoção -->
<div id="promoModal" class="promo-modal-overlay">
    <div class="promo-modal">
        <div class="promo-modal-header" id="promoModalHeader">
            <button class="promo-modal-close" onclick="fecharModalPromo()">&times;</button>
            <h2 class="promo-modal-title" id="promoModalTitle"></h2>
            <p class="promo-modal-desc" id="promoModalDesc"></p>
        </div>
        <div class="promo-modal-body">
            <div class="promo-info-item" id="promoDiscount" style="border-left-color: #DD0101;">
                <h3><i class="fas fa-tag"></i> Desconto</h3>
                <p id="promoDiscountText"></p>
            </div>
            
            <div class="promo-info-item" style="border-left-color: #109349;">
                <h3><i class="fas fa-wrench"></i> Serviços Aplicáveis</h3>
                <p>Esta promoção pode ser utilizada nos seguintes serviços:</p>
                <ul class="promo-services-list">
                    <li><i class="fas fa-check-circle"></i> Troca de óleo e filtros</li>
                    <li><i class="fas fa-check-circle"></i> Revisão completa</li>
                    <li><i class="fas fa-check-circle"></i> Alinhamento e balanceamento</li>
                    <li><i class="fas fa-check-circle"></i> Freios e suspensão</li>
                    <li><i class="fas fa-check-circle"></i> Serviços elétricos</li>
                </ul>
            </div>
            
            <div class="promo-info-item" style="border-left-color: #FF9800;">
                <h3><i class="fas fa-info-circle"></i> Como Usar</h3>
                <p>1. Copie o código do cupom abaixo<br>
                   2. Clique em "Agendar Agora"<br>
                   3. O desconto será aplicado automaticamente no seu agendamento</p>
            </div>
            
            <div class="cupom-copy-box">
                <span class="cupom-copy-code" id="promoModalCupom"></span>
                <button class="cupom-copy-btn" onclick="copiarCupomModal(event)"><i class="fas fa-copy"></i> Copiar Cupom</button>
            </div>
            
            <div class="promo-modal-actions">
                <button class="promo-modal-btn promo-modal-btn-primary" onclick="agendarComPromo()">
                    <i class="fas fa-calendar-check"></i> Agendar Agora
                </button>
                <button class="promo-modal-btn promo-modal-btn-secondary" onclick="fecharModalPromo()">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cupomAtual = '';

function abrirModalPromo(titulo, descricao, desconto, cupom, cor) {
    cupomAtual = cupom;
    document.getElementById('promoModalTitle').textContent = titulo;
    document.getElementById('promoModalDesc').textContent = descricao;
    document.getElementById('promoDiscountText').textContent = `Ganhe ${desconto}% de desconto em serviços selecionados!`;
    document.getElementById('promoModalCupom').textContent = cupom;
    document.getElementById('promoModalHeader').style.background = cor.replace('0.7', '0.9');
    document.getElementById('promoModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharModalPromo() {
    document.getElementById('promoModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function copiarCupom(cupom) {
    navigator.clipboard.writeText(cupom).then(() => {
        mostrarMensagemCopia('Copiado com sucesso!');
    });
}

function mostrarMensagemCopia(mensagem) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + mensagem;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

function copiarCupomModal(e) {
    navigator.clipboard.writeText(cupomAtual).then(() => {
        const btn = e.target.closest('button');
        const originalText = btn.innerHTML;
        const isAlemanha = document.body.classList.contains('theme-alemanha');
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
        btn.style.background = '#2ecc71';
        mostrarMensagemCopia('Copiado com sucesso!');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = isAlemanha ? '#FFCE00' : '#109349';
        }, 2000);
    }).catch(() => {
        mostrarMensagemCopia('Erro ao copiar!');
    });
}

function agendarComPromo() {
    window.location.href = 'agendamento-novo.php?cupom=' + encodeURIComponent(cupomAtual);
}

// Fechar modal ao clicar fora
document.getElementById('promoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalPromo();
    }
});

</script>

<script>
// Carrossel de Promoções
let currentSlide = 0;
let totalSlides = 0;
let carouselInterval;

function initCarousel() {
    const track = document.getElementById('carouselTrack');
    const dotsContainer = document.getElementById('carouselDots');
    
    if (!track) return;
    
    totalSlides = track.children.length;
    
    if (totalSlides === 0) return;
    
    // Criar dots
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('div');
        dot.className = 'carousel-dot';
        if (i === 0) dot.classList.add('active');
        dot.onclick = () => goToSlide(i);
        dotsContainer.appendChild(dot);
    }
    
    // Auto-play
    startAutoPlay();
}

function moveCarousel(direction) {
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    updateCarousel();
    resetAutoPlay();
}

function goToSlide(index) {
    currentSlide = index;
    updateCarousel();
    resetAutoPlay();
}

function updateCarousel() {
    const track = document.getElementById('carouselTrack');
    const dots = document.querySelectorAll('.carousel-dot');
    
    if (!track) return;
    
    track.style.transform = `translateX(-${currentSlide * 100}%)`;
    
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

function startAutoPlay() {
    carouselInterval = setInterval(() => {
        moveCarousel(1);
    }, 5000);
}

function resetAutoPlay() {
    clearInterval(carouselInterval);
    startAutoPlay();
}

// Função para atualizar o relógio
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const clockElement = document.getElementById('current-time');
    if (clockElement) {
        clockElement.textContent = timeString;
    }
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrossel
    initCarousel();
    
    // Inicializar relógio
    updateClock();
    setInterval(updateClock, 1000);
    
    // Eventos do carrossel
    const carousel = document.querySelector('.promocoes-carousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', () => {
            clearInterval(carouselInterval);
        });
        
        carousel.addEventListener('mouseleave', () => {
            startAutoPlay();
        });
    }
    

    

});
</script>

<script>
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

<?php
// Inclui o rodapé
require_once 'footer.php';
?>

</body>
</html>