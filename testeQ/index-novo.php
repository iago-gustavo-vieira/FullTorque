<?php
// Index.php migrado para nova estrutura responsiva global
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Define o título da página baseado no tipo de usuário
if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0) {
    // Redirecionar mecânicos para seu dashboard específico
    header("Location: mecanico-dashboard.php");
    exit;
}

$titulo = 'Dashboard';

// Busca informações do usuário
$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Busca os veículos do usuário
$stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$veiculos = $stmt->get_result();

// Busca os agendamentos do usuário
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

// Busca os serviços mais populares
$stmt = $conexao->prepare("
    SELECT s.nome, COUNT(*) as total
    FROM agendamento_itens ai
    JOIN servicos s ON ai.servico_id = s.id
    JOIN agendamentos a ON ai.agendamento_id = a.id
    WHERE a.usuario_id = ?
    GROUP BY s.id
    ORDER BY total DESC
    LIMIT 5
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$servicos_populares = $stmt->get_result();

// Desativar promoções vencidas automaticamente
if ($conexao->query("SHOW TABLES LIKE 'promocoes_carousel'")->num_rows > 0) {
    $conexao->query("UPDATE promocoes_carousel SET ativo = 0 WHERE data_fim < CURDATE()");
}

// Buscar promoções ativas para o carrossel
$promocoes_carousel = null;
if ($conexao->query("SHOW TABLES LIKE 'promocoes_carousel'")->num_rows > 0) {
    $promocoes_carousel = $conexao->query("
        SELECT * FROM promocoes_carousel 
        WHERE ativo = 1 AND data_inicio <= CURDATE() AND data_fim >= CURDATE() 
        ORDER BY ordem ASC, data_criacao DESC
    ");
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - FullTorque</title>
    
    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Estilos globais -->
    <link rel="stylesheet" href="global-styles.css">
    
    <style>
        /* Estilos específicos do dashboard */
        body {
            background-image: url('body_italia.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .theme-alemanha body {
            background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
        }
        
        .dashboard-welcome {
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.9), rgba(16, 147, 73, 0.7));
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .theme-alemanha .dashboard-welcome {
            background: linear-gradient(135deg, rgba(255, 206, 0, 0.9), rgba(221, 1, 0, 0.7));
            color: #000;
        }
        
        .welcome-message h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .welcome-message p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .theme-alemanha .stat-card {
            background: #1a1a1a;
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
        }
        
        .theme-alemanha .stat-title {
            color: #ccc;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.85rem;
        }
        
        .theme-alemanha .stat-label {
            color: #aaa;
        }
        
        .stat-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .stat-link:hover {
            gap: 8px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .section-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .theme-alemanha .section-card {
            background: #1a1a1a;
            color: white;
        }
        
        .section-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-alemanha .section-header {
            border-bottom-color: #333;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .theme-alemanha .section-title {
            color: white;
        }
        
        .section-action {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .section-content {
            padding: 20px;
        }
        
        .next-service {
            text-align: center;
            padding: 20px;
        }
        
        .next-service-date {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .next-service-time {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .next-service-vehicle {
            color: #666;
            font-size: 0.9rem;
        }
        
        .theme-alemanha .next-service-vehicle {
            color: #ccc;
        }
        
        .no-service {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 40px 20px;
        }
        
        .theme-alemanha .no-service {
            color: #aaa;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .theme-alemanha th,
        .theme-alemanha td {
            border-bottom-color: #333;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .theme-alemanha th {
            background: #2a2a2a;
            color: white;
        }
        
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-agendado {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .status-confirmado {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }
        
        .status-concluido {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .status-cancelado {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }
        
        .theme-alemanha .empty-state {
            color: #aaa;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .theme-alemanha .empty-state i {
            color: #555;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .dashboard-welcome {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .stat-value {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-welcome {
                padding: 20px;
            }
            
            .welcome-message h2 {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .section-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header global -->
    <?php include 'global-header.php'; ?>
    
    <!-- Conteúdo principal -->
    <div class="main-container">
        <!-- Mensagem de boas-vindas -->
        <div class="dashboard-welcome fade-in">
            <div class="welcome-message">
                <h2>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h2>
                <p>Confira o resumo das suas atividades e serviços.</p>
            </div>
            <div class="welcome-actions">
                <a href="agendamento-novo.php" class="btn">
                    <i class="fas fa-calendar-plus"></i> Agendar Serviço
                </a>
            </div>
        </div>
        
        <!-- Cards de estatísticas -->
        <div class="stats-grid">
            <div class="stat-card fade-in">
                <div class="stat-header">
                    <div class="stat-title">Veículos Cadastrados</div>
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $veiculos->num_rows; ?></div>
                <div class="stat-label">Total de veículos</div>
                <a href="veiculos.php" class="stat-link">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card fade-in">
                <div class="stat-header">
                    <div class="stat-title">Agendamentos</div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $agendamentos->num_rows; ?></div>
                <div class="stat-label">Total de agendamentos</div>
                <a href="agendamentos.php" class="stat-link">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card fade-in">
                <div class="stat-header">
                    <div class="stat-title">Próximo Serviço</div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <?php
                // Busca o próximo agendamento
                $agendamentos->data_seek(0);
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
                    echo '<div class="next-service-date">' . date('d/m/Y', strtotime($proximo_agendamento['data_agendamento'])) . '</div>';
                    echo '<div class="next-service-time">' . substr($proximo_agendamento['hora_inicio'], 0, 5) . '</div>';
                    echo '<div class="next-service-vehicle">' . $proximo_agendamento['marca'] . ' ' . $proximo_agendamento['modelo'] . '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="no-service">Nenhum serviço agendado</div>';
                }
                ?>
                <a href="agendamento-novo.php" class="stat-link">
                    Agendar <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>
        
        <!-- Grid de conteúdo -->
        <div class="content-grid">
            <!-- Próximos agendamentos -->
            <div class="section-card fade-in">
                <div class="section-header">
                    <div class="section-title">Próximos Agendamentos</div>
                    <a href="agendamentos.php" class="section-action">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="section-content">
                    <?php if ($agendamentos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Veículo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $agendamentos->data_seek(0);
                                    $count = 0;
                                    while ($agendamento = $agendamentos->fetch_assoc()): 
                                        if ($count >= 3) break;
                                        $count++;
                                    ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                            <td><?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></td>
                                            <td>
                                                <span class="status status-<?php echo $agendamento['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Nenhum agendamento encontrado.</p>
                            <a href="agendamento-novo.php" class="btn">
                                <i class="fas fa-plus"></i> Agendar Serviço
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Serviços mais utilizados -->
            <div class="section-card fade-in">
                <div class="section-header">
                    <div class="section-title">Serviços Mais Utilizados</div>
                </div>
                <div class="section-content">
                    <?php if ($servicos_populares->num_rows > 0): ?>
                        <div class="chart-container">
                            <?php 
                            $max_value = 0;
                            $servicos = [];
                            
                            while ($servico = $servicos_populares->fetch_assoc()) {
                                $servicos[] = $servico;
                                if ($servico['total'] > $max_value) {
                                    $max_value = $servico['total'];
                                }
                            }
                            
                            foreach ($servicos as $servico) {
                                $porcentagem = ($servico['total'] / $max_value) * 100;
                                echo '<div class="chart-item" style="margin-bottom: 15px;">';
                                echo '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
                                echo '<span style="font-size: 0.9rem; color: #666;">' . $servico['nome'] . '</span>';
                                echo '<span style="font-weight: 600; color: var(--primary-color);">' . $servico['total'] . '</span>';
                                echo '</div>';
                                echo '<div style="background: #f1f1f1; height: 8px; border-radius: 4px; overflow: hidden;">';
                                echo '<div style="background: var(--primary-color); height: 100%; width: ' . $porcentagem . '%; border-radius: 4px; transition: width 1s ease;"></div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <p>Nenhum serviço utilizado ainda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Dicas de manutenção -->
        <div class="section-card fade-in" style="margin-bottom: 30px;">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-tools"></i> Dicas de Manutenção
                </div>
            </div>
            <div class="section-content">
                <div class="grid grid-3">
                    <div class="card">
                        <div style="text-align: center; padding: 20px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3498db, #2980b9); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 1.5rem;">
                                <i class="fas fa-oil-can"></i>
                            </div>
                            <h4 style="margin-bottom: 10px; color: #333;">Troca de Óleo</h4>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">Troque o óleo a cada 5.000-10.000 km para manter o motor saudável.</p>
                            <a href="agendamento-novo.php?servico=Troca de Óleo" class="btn btn-sm">
                                <i class="fas fa-calendar-plus"></i> Agendar
                            </a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div style="text-align: center; padding: 20px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #e74c3c, #c0392b); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 1.5rem;">
                                <i class="fas fa-stop-circle"></i>
                            </div>
                            <h4 style="margin-bottom: 10px; color: #333;">Sistema de Freios</h4>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">Verifique pastilhas e fluido regularmente para sua segurança.</p>
                            <a href="agendamento-novo.php?servico=Revisão de Freios" class="btn btn-sm">
                                <i class="fas fa-calendar-plus"></i> Agendar
                            </a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div style="text-align: center; padding: 20px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f1c40f, #f39c12); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 1.5rem;">
                                <i class="fas fa-car-battery"></i>
                            </div>
                            <h4 style="margin-bottom: 10px; color: #333;">Bateria</h4>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">Teste a bateria a cada 6 meses para evitar surpresas.</p>
                            <a href="agendamento-novo.php?servico=Teste de Bateria" class="btn btn-sm">
                                <i class="fas fa-calendar-plus"></i> Agendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }
        
        .theme-alemanha .card {
            background: #1a1a1a;
            color: white;
        }
        
        .theme-alemanha h4 {
            color: white !important;
        }
        
        .theme-alemanha p {
            color: #ccc !important;
        }
    </style>
    
    <script>
        // Animação de entrada dos elementos
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            elements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(element);
            });
        });
    </script>
</body>
</html>