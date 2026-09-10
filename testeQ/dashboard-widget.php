<?php
// Widget adicional para o dashboard - Estatísticas em tempo real
?>

<!-- Widget de Estatísticas Rápidas -->
<div class="quick-stats-widget">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-chart-line"></i>
            Estatísticas Rápidas
        </h3>
        <div class="widget-actions">
            <button class="widget-btn" onclick="refreshStats()" title="Atualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="widget-btn" onclick="toggleWidget()" title="Minimizar">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    
    <div class="widget-content">
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="agendamentos-hoje">
                    <?php
                    // Contar agendamentos de hoje
                    $hoje = date('Y-m-d');
                    $query_hoje = "SELECT COUNT(*) as total FROM agendamentos WHERE DATE(data_agendamento) = '$hoje' AND status IN ('agendado', 'confirmado')";
                    $result_hoje = $conn->query($query_hoje);
                    $agendamentos_hoje = $result_hoje->fetch_assoc()['total'];
                    echo $agendamentos_hoje;
                    ?>
                </div>
                <div class="stat-label">Agendamentos Hoje</div>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="servicos-andamento">
                    <?php
                    // Contar serviços em andamento
                    $query_andamento = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'em_andamento'";
                    $result_andamento = $conn->query($query_andamento);
                    $servicos_andamento = $result_andamento->fetch_assoc()['total'];
                    echo $servicos_andamento;
                    ?>
                </div>
                <div class="stat-label">Em Andamento</div>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="concluidos-semana">
                    <?php
                    // Contar serviços concluídos esta semana
                    $inicio_semana = date('Y-m-d', strtotime('monday this week'));
                    $fim_semana = date('Y-m-d', strtotime('sunday this week'));
                    $query_semana = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'concluido' AND DATE(data_agendamento) BETWEEN '$inicio_semana' AND '$fim_semana'";
                    $result_semana = $conn->query($query_semana);
                    $concluidos_semana = $result_semana->fetch_assoc()['total'];
                    echo $concluidos_semana;
                    ?>
                </div>
                <div class="stat-label">Concluídos (Semana)</div>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="satisfacao">
                    <?php
                    // Calcular satisfação média (simulado)
                    echo "4.8";
                    ?>
                </div>
                <div class="stat-label">Satisfação Média</div>
            </div>
        </div>
    </div>
</div>

<!-- Widget de Ações Rápidas -->
<div class="quick-actions-widget">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-bolt"></i>
            Ações Rápidas
        </h3>
    </div>
    
    <div class="widget-content">
        <div class="quick-actions-grid">
            <a href="agendamento-novo.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="action-text">Novo Agendamento</div>
            </a>
            
            <a href="veiculo-novo.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="action-text">Cadastrar Veículo</div>
            </a>
            
            <a href="promocoes.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="action-text">Ver Promoções</div>
            </a>
            
            <a href="historico.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="action-text">Histórico</div>
            </a>
            
            <a href="perfil.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="action-text">Meu Perfil</div>
            </a>
            
            <a href="pagamentos.php" class="quick-action-btn">
                <div class="action-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="action-text">Pagamentos</div>
            </a>
        </div>
    </div>
</div>

<!-- Widget de Notificações -->
<div class="notifications-widget">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-bell"></i>
            Notificações Recentes
        </h3>
        <div class="notification-badge" id="notification-count">
            <?php
            // Contar notificações não lidas
            $query_notif = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = {$_SESSION['usuario_id']} AND lida = 0";
            $result_notif = $conn->query($query_notif);
            $notif_count = $result_notif->fetch_assoc()['total'];
            echo $notif_count > 0 ? $notif_count : '';
            ?>
        </div>
    </div>
    
    <div class="widget-content">
        <div class="notifications-list">
            <?php
            // Buscar últimas notificações
            $query_notificacoes = "SELECT * FROM notificacoes WHERE usuario_id = {$_SESSION['usuario_id']} ORDER BY data_criacao DESC LIMIT 3";
            $result_notificacoes = $conn->query($query_notificacoes);
            
            if ($result_notificacoes->num_rows > 0):
                while ($notif = $result_notificacoes->fetch_assoc()):
            ?>
                <div class="notification-item <?php echo $notif['lida'] ? '' : 'unread'; ?>">
                    <div class="notification-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title"><?php echo htmlspecialchars($notif['titulo']); ?></div>
                        <div class="notification-message"><?php echo htmlspecialchars($notif['mensagem']); ?></div>
                        <div class="notification-time"><?php echo date('d/m/Y H:i', strtotime($notif['data_criacao'])); ?></div>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>Nenhuma notificação recente</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="widget-footer">
            <a href="notificacoes.php" class="view-all-link">
                Ver todas as notificações <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Widget de Clima (Opcional) -->
<div class="weather-widget">
    <div class="widget-header">
        <h3 class="widget-title">
            <i class="fas fa-cloud-sun"></i>
            Condições do Tempo
        </h3>
    </div>
    
    <div class="widget-content">
        <div class="weather-info">
            <div class="weather-icon">
                <i class="fas fa-sun"></i>
            </div>
            <div class="weather-details">
                <div class="temperature">25°C</div>
                <div class="weather-desc">Ensolarado</div>
                <div class="weather-location">São Paulo, SP</div>
            </div>
        </div>
        <div class="weather-tip">
            <i class="fas fa-lightbulb"></i>
            <span>Ótimo dia para lavagem externa!</span>
        </div>
    </div>
</div>

<style>
/* Estilos para os widgets */
.quick-stats-widget,
.quick-actions-widget,
.notifications-widget,
.weather-widget {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.05));
    backdrop-filter: blur(15px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 25px;
    margin-bottom: 25px;
    animation: fadeInUp 0.6s ease-out;
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.widget-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.widget-actions {
    display: flex;
    gap: 8px;
}

.widget-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.widget-btn:hover {
    background: linear-gradient(135deg, #667eea, #764ba2);
    transform: scale(1.1);
}

.notification-badge {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 10px;
}

/* Estatísticas rápidas */
.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.2rem;
}

.stat-item:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #f093fb, #f5576c);
}

.stat-item:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.stat-item:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}

/* Ações rápidas */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 15px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-decoration: none;
    color: #fff;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    transform: translateY(-5px);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.1));
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.action-text {
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
}

/* Notificações */
.notification-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item.unread {
    background: linear-gradient(135deg, rgba(240, 147, 251, 0.1), rgba(245, 87, 108, 0.05));
    border-radius: 10px;
    padding: 12px;
    margin: 5px 0;
}

.notification-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.notification-title {
    font-weight: 600;
    color: #fff;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.notification-message {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.8rem;
    margin-bottom: 4px;
}

.notification-time {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.7rem;
}

.no-notifications {
    text-align: center;
    padding: 30px 20px;
    color: rgba(255, 255, 255, 0.6);
}

.no-notifications i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.view-all-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    margin-top: 15px;
    transition: all 0.3s ease;
}

.view-all-link:hover {
    color: #f093fb;
}

/* Widget do clima */
.weather-info {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
}

.weather-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    background: linear-gradient(135deg, #ffecd2, #fcb69f);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #ff6b35;
}

.temperature {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.weather-desc {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

.weather-location {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.weather-tip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
    border-radius: 10px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
}

.weather-tip i {
    color: #ffd700;
}

/* Responsividade dos widgets */
@media (max-width: 768px) {
    .quick-stats-widget,
    .quick-actions-widget,
    .notifications-widget,
    .weather-widget {
        padding: 20px 15px;
        margin-bottom: 20px;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .quick-action-btn {
        padding: 15px 10px;
    }
    
    .action-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .action-text {
        font-size: 0.8rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .weather-info {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
}
</style>

<script>
// Funções JavaScript para os widgets
function refreshStats() {
    // Simular atualização das estatísticas
    const buttons = document.querySelectorAll('.widget-btn i.fa-sync-alt');
    buttons.forEach(btn => {
        btn.style.animation = 'spin 1s linear infinite';
        setTimeout(() => {
            btn.style.animation = '';
        }, 1000);
    });
    
    // Aqui você pode fazer uma requisição AJAX para atualizar os dados
    setTimeout(() => {
        showNotification('Estatísticas atualizadas!', 'success');
    }, 1000);
}

function toggleWidget() {
    // Implementar minimizar/maximizar widget
    const widget = event.target.closest('.quick-stats-widget, .quick-actions-widget, .notifications-widget, .weather-widget');
    const content = widget.querySelector('.widget-content');
    const icon = event.target;
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-minus';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-plus';
    }
}

// Atualizar notificações em tempo real
setInterval(() => {
    // Aqui você pode fazer uma requisição AJAX para verificar novas notificações
    // Por enquanto, apenas simular
}, 30000); // A cada 30 segundos
</script>