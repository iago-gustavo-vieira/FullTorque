<?php
// Dicas de Manutenção Aprimoradas - Seção melhorada para o dashboard
?>

<div class="section maintenance-tips-section">
    <div class="section-header">
        <div class="section-title">
            <i class="fas fa-tools"></i>
            Dicas de Manutenção Preventiva
        </div>
        <div class="tips-controls">
            <button class="tip-filter active" data-category="all">Todas</button>
            <button class="tip-filter" data-category="motor">Motor</button>
            <button class="tip-filter" data-category="pneus">Pneus</button>
            <button class="tip-filter" data-category="eletrica">Elétrica</button>
            <button class="tip-filter" data-category="freios">Freios</button>
        </div>
    </div>
    
    <div class="tips-container enhanced">
        <!-- Dicas de Motor -->
        <div class="tip-card enhanced animate-ready" data-category="motor" data-priority="high">
            <div class="tip-priority">
                <span class="priority-badge high">Alta</span>
            </div>
            <div class="tip-icon motor">
                <i class="fas fa-oil-can"></i>
            </div>
            <div class="tip-content">
                <h3>Troca de Óleo do Motor</h3>
                <p>Troque o óleo a cada 5.000-10.000 km ou conforme manual do fabricante. Use sempre óleo da viscosidade recomendada.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 5.000-10.000 km</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 80 - R$ 150</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('troca-oleo')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('oleo-motor')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <div class="tip-card enhanced animate-ready" data-category="motor" data-priority="medium">
            <div class="tip-priority">
                <span class="priority-badge medium">Média</span>
            </div>
            <div class="tip-icon motor">
                <i class="fas fa-filter"></i>
            </div>
            <div class="tip-content">
                <h3>Filtros do Motor</h3>
                <p>Substitua filtro de ar, combustível e óleo regularmente. Filtros sujos reduzem performance e aumentam consumo.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 10.000-15.000 km</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 50 - R$ 120</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('filtros')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('filtros')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <!-- Dicas de Pneus -->
        <div class="tip-card enhanced animate-ready" data-category="pneus" data-priority="high">
            <div class="tip-priority">
                <span class="priority-badge high">Alta</span>
            </div>
            <div class="tip-icon pneus">
                <i class="fas fa-tire"></i>
            </div>
            <div class="tip-content">
                <h3>Pressão dos Pneus</h3>
                <p>Verifique mensalmente a pressão. Pneus murchos aumentam consumo e desgaste irregular. Calibre sempre com pneus frios.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>Mensal</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Gratuito</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('calibragem')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('pressao-pneus')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <div class="tip-card enhanced animate-ready" data-category="pneus" data-priority="medium">
            <div class="tip-priority">
                <span class="priority-badge medium">Média</span>
            </div>
            <div class="tip-icon pneus">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="tip-content">
                <h3>Rodízio de Pneus</h3>
                <p>Faça rodízio a cada 10.000 km para desgaste uniforme. Alinhamento e balanceamento também são essenciais.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 10.000 km</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 30 - R$ 80</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('rodizio-pneus')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('rodizio')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <!-- Dicas Elétricas -->
        <div class="tip-card enhanced animate-ready" data-category="eletrica" data-priority="high">
            <div class="tip-priority">
                <span class="priority-badge high">Alta</span>
            </div>
            <div class="tip-icon eletrica">
                <i class="fas fa-car-battery"></i>
            </div>
            <div class="tip-content">
                <h3>Bateria do Veículo</h3>
                <p>Teste a bateria a cada 6 meses. Limpe os terminais e verifique o nível da água (baterias convencionais).</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 6 meses</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 200 - R$ 400</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('teste-bateria')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('bateria')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <div class="tip-card enhanced animate-ready" data-category="eletrica" data-priority="low">
            <div class="tip-priority">
                <span class="priority-badge low">Baixa</span>
            </div>
            <div class="tip-icon eletrica">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="tip-content">
                <h3>Sistema de Iluminação</h3>
                <p>Verifique regularmente faróis, lanternas e setas. Lâmpadas queimadas comprometem a segurança.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>Mensal</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 10 - R$ 50</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('iluminacao')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('iluminacao')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <!-- Dicas de Freios -->
        <div class="tip-card enhanced animate-ready" data-category="freios" data-priority="high">
            <div class="tip-priority">
                <span class="priority-badge high">Alta</span>
            </div>
            <div class="tip-icon freios">
                <i class="fas fa-stop-circle"></i>
            </div>
            <div class="tip-content">
                <h3>Sistema de Freios</h3>
                <p>Verifique pastilhas e fluido de freio regularmente. Ruídos ou pedal mole indicam necessidade de manutenção.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 20.000 km</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 150 - R$ 400</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('freios')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('freios')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <!-- Dicas Gerais -->
        <div class="tip-card enhanced animate-ready" data-category="motor" data-priority="medium">
            <div class="tip-priority">
                <span class="priority-badge medium">Média</span>
            </div>
            <div class="tip-icon motor">
                <i class="fas fa-thermometer-half"></i>
            </div>
            <div class="tip-content">
                <h3>Sistema de Arrefecimento</h3>
                <p>Verifique nível e qualidade do líquido de arrefecimento. Superaquecimento pode causar danos graves ao motor.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 3 meses</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 40 - R$ 100</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('arrefecimento')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('arrefecimento')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <div class="tip-card enhanced animate-ready" data-category="motor" data-priority="low">
            <div class="tip-priority">
                <span class="priority-badge low">Baixa</span>
            </div>
            <div class="tip-icon motor">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div class="tip-content">
                <h3>Revisão Completa</h3>
                <p>Faça revisões periódicas conforme manual. Manutenção preventiva evita problemas maiores e mais caros.</p>
                <div class="tip-frequency">
                    <i class="fas fa-clock"></i>
                    <span>A cada 10.000 km</span>
                </div>
                <div class="tip-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>R$ 200 - R$ 500</span>
                </div>
            </div>
            <div class="tip-actions">
                <button class="tip-btn schedule" onclick="scheduleService('revisao-completa')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar
                </button>
                <button class="tip-btn info" onclick="showTipDetails('revisao')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Seção de Dicas Sazonais -->
    <div class="seasonal-tips">
        <h4><i class="fas fa-calendar-alt"></i> Dicas Sazonais</h4>
        <div class="seasonal-grid">
            <div class="seasonal-card">
                <div class="seasonal-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <h5>Verão</h5>
                <p>Verifique ar-condicionado, nível de água e pressão dos pneus com mais frequência.</p>
            </div>
            <div class="seasonal-card">
                <div class="seasonal-icon">
                    <i class="fas fa-cloud-rain"></i>
                </div>
                <h5>Inverno</h5>
                <p>Teste bateria, palhetas do limpador e sistema de aquecimento antes do frio.</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos aprimorados para as dicas de manutenção */
.maintenance-tips-section {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.4rem;
    font-weight: 700;
    color: #2c3e50;
}

.tips-controls {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tip-filter {
    padding: 8px 16px;
    border: 2px solid #e74c3c;
    background: transparent;
    color: #e74c3c;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.tip-filter:hover,
.tip-filter.active {
    background: #e74c3c;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

.tips-container.enhanced {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.tip-card.enhanced {
    background: linear-gradient(135deg, #fff, #f8f9fa);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.tip-card.enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e74c3c, #c0392b);
}

.tip-card.enhanced:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.tip-priority {
    position: absolute;
    top: 15px;
    right: 15px;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge.high {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.priority-badge.medium {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: white;
}

.priority-badge.low {
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
}

.tip-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.tip-icon.motor {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
}

.tip-icon.pneus {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
    color: white;
}

.tip-icon.eletrica {
    background: linear-gradient(135deg, #f1c40f, #f39c12);
    color: white;
}

.tip-icon.freios {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.tip-card.enhanced:hover .tip-icon {
    transform: scale(1.1) rotate(5deg);
}

.tip-content h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.tip-content p {
    color: #5d6d7e;
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.tip-frequency,
.tip-cost {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: #7f8c8d;
}

.tip-frequency i,
.tip-cost i {
    color: #e74c3c;
    width: 16px;
}

.tip-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}

.tip-btn {
    flex: 1;
    padding: 10px 15px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9rem;
}

.tip-btn.schedule {
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
}

.tip-btn.schedule:hover {
    background: linear-gradient(135deg, #229954, #1e8449);
    transform: translateY(-2px);
}

.tip-btn.info {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    flex: 0 0 auto;
    width: 40px;
    padding: 10px;
}

.tip-btn.info:hover {
    background: linear-gradient(135deg, #2980b9, #21618c);
    transform: translateY(-2px);
}

/* Dicas Sazonais */
.seasonal-tips {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #ecf0f1;
}

.seasonal-tips h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
}

.seasonal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.seasonal-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.seasonal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.seasonal-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 15px;
}

.seasonal-card h5 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.seasonal-card p {
    color: #5d6d7e;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Responsividade */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .tips-controls {
        width: 100%;
        justify-content: center;
    }
    
    .tips-container.enhanced {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .tip-card.enhanced {
        padding: 20px;
    }
    
    .tip-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .tip-btn.info {
        width: 100%;
    }
    
    .seasonal-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .tip-filter {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
    
    .tip-card.enhanced {
        padding: 15px;
    }
    
    .tip-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .tip-content h3 {
        font-size: 1.1rem;
    }
    
    .tip-content p {
        font-size: 0.9rem;
    }
}

/* Animações de filtro */
.tip-card.enhanced.hidden {
    opacity: 0;
    transform: scale(0.8);
    pointer-events: none;
}

.tip-card.enhanced.visible {
    opacity: 1;
    transform: scale(1);
    pointer-events: all;
}
</style>

<script>
// JavaScript para funcionalidade das dicas aprimoradas
document.addEventListener('DOMContentLoaded', function() {
    initTipsFilter();
    initTipsAnimations();
});

function initTipsFilter() {
    const filterButtons = document.querySelectorAll('.tip-filter');
    const tipCards = document.querySelectorAll('.tip-card.enhanced');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Atualizar botões ativos
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrar cards
            tipCards.forEach(card => {
                const cardCategory = card.dataset.category;
                
                if (category === 'all' || cardCategory === category) {
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                } else {
                    card.classList.add('hidden');
                    card.classList.remove('visible');
                }
            });
        });
    });
}

function initTipsAnimations() {
    const tipCards = document.querySelectorAll('.tip-card.enhanced');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    tipCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        observer.observe(card);
    });
}

function scheduleService(serviceType) {
    // Redirecionar para agendamento com serviço pré-selecionado
    const serviceMap = {
        'troca-oleo': 'Troca de Óleo',
        'filtros': 'Troca de Filtros',
        'calibragem': 'Calibragem de Pneus',
        'rodizio-pneus': 'Rodízio de Pneus',
        'teste-bateria': 'Teste de Bateria',
        'iluminacao': 'Verificação de Iluminação',
        'freios': 'Revisão de Freios',
        'arrefecimento': 'Sistema de Arrefecimento',
        'revisao-completa': 'Revisão Completa'
    };
    
    const serviceName = serviceMap[serviceType] || 'Serviço';
    const url = `agendamento-novo.php?servico=${encodeURIComponent(serviceName)}`;
    
    // Mostrar feedback visual
    const button = event.target.closest('.tip-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecionando...';
    button.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
    
    setTimeout(() => {
        window.location.href = url;
    }, 1000);
}

function showTipDetails(tipType) {
    const tipDetails = {
        'oleo-motor': {
            title: 'Troca de Óleo do Motor',
            content: `
                <h4>Por que é importante?</h4>
                <p>O óleo lubrifica as peças móveis do motor, reduz o atrito e remove impurezas.</p>
                
                <h4>Sinais de que precisa trocar:</h4>
                <ul>
                    <li>Óleo escuro ou com partículas</li>
                    <li>Nível baixo no reservatório</li>
                    <li>Ruídos estranhos no motor</li>
                    <li>Quilometragem atingida</li>
                </ul>
                
                <h4>Tipos de óleo:</h4>
                <ul>
                    <li><strong>Mineral:</strong> Mais barato, troca mais frequente</li>
                    <li><strong>Semissintético:</strong> Boa relação custo-benefício</li>
                    <li><strong>Sintético:</strong> Maior durabilidade e proteção</li>
                </ul>
            `
        },
        'filtros': {
            title: 'Filtros do Motor',
            content: `
                <h4>Tipos de filtros:</h4>
                <ul>
                    <li><strong>Filtro de Ar:</strong> Impede entrada de sujeira no motor</li>
                    <li><strong>Filtro de Óleo:</strong> Remove impurezas do óleo</li>
                    <li><strong>Filtro de Combustível:</strong> Limpa o combustível</li>
                </ul>
                
                <h4>Sinais de filtros sujos:</h4>
                <ul>
                    <li>Perda de potência</li>
                    <li>Aumento do consumo</li>
                    <li>Fumaça escura no escapamento</li>
                </ul>
            `
        },
        'pressao-pneus': {
            title: 'Pressão dos Pneus',
            content: `
                <h4>Importância da pressão correta:</h4>
                <ul>
                    <li>Maior segurança na direção</li>
                    <li>Economia de combustível</li>
                    <li>Maior vida útil dos pneus</li>
                    <li>Melhor aderência</li>
                </ul>
                
                <h4>Como verificar:</h4>
                <ul>
                    <li>Use calibrador confiável</li>
                    <li>Verifique com pneus frios</li>
                    <li>Consulte manual do veículo</li>
                    <li>Não esqueça do estepe</li>
                </ul>
            `
        }
        // Adicionar mais detalhes conforme necessário
    };
    
    const details = tipDetails[tipType];
    if (!details) return;
    
    // Criar modal com detalhes
    const modal = document.createElement('div');
    modal.className = 'tip-modal';
    modal.innerHTML = `
        <div class="tip-modal-content">
            <div class="tip-modal-header">
                <h3>${details.title}</h3>
                <button class="tip-modal-close" onclick="closeTipModal()">&times;</button>
            </div>
            <div class="tip-modal-body">
                ${details.content}
            </div>
            <div class="tip-modal-footer">
                <button class="tip-btn schedule" onclick="scheduleService('${tipType}')">
                    <i class="fas fa-calendar-plus"></i>
                    Agendar Serviço
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Animar entrada
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
}

function closeTipModal() {
    const modal = document.querySelector('.tip-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

// CSS para o modal
const modalCSS = `
.tip-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.tip-modal.active {
    opacity: 1;
    visibility: visible;
}

.tip-modal-content {
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    transform: scale(0.8);
    transition: all 0.3s ease;
}

.tip-modal.active .tip-modal-content {
    transform: scale(1);
}

.tip-modal-header {
    padding: 25px;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.tip-modal-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.4rem;
}

.tip-modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: #95a5a6;
    cursor: pointer;
    transition: color 0.3s ease;
}

.tip-modal-close:hover {
    color: #e74c3c;
}

.tip-modal-body {
    padding: 25px;
}

.tip-modal-body h4 {
    color: #e74c3c;
    margin-top: 20px;
    margin-bottom: 10px;
}

.tip-modal-body ul {
    margin-left: 20px;
    margin-bottom: 15px;
}

.tip-modal-body li {
    margin-bottom: 5px;
    color: #5d6d7e;
}

.tip-modal-footer {
    padding: 25px;
    border-top: 1px solid #ecf0f1;
    text-align: center;
}
`;

// Adicionar CSS do modal
const style = document.createElement('style');
style.textContent = modalCSS;
document.head.appendChild(style);
</script>