<?php
require_once 'header.php';

$conexao = conectarBD();
$mecanicos = $conexao->query("SELECT * FROM mecanicos WHERE ativo = 1 ORDER BY nome");

if (colunaExiste($conexao, 'veiculos', 'usuario_id')) {
    $stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ? ORDER BY marca, modelo");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $veiculos = $stmt->get_result();
} else {
    $veiculos = $conexao->query("SELECT * FROM veiculos ORDER BY marca, modelo");
}
$conexao->close();
?>

<div class="dashboard-welcome">
    <div class="mobile-welcome-text">Novo Agendamento</div>
    <div class="welcome-message">
        <h2><i class="fas fa-calendar-plus"></i> Novo Agendamento</h2>
        <p>Agende um horário para manutenção do seu veículo com nossos mecânicos especializados.</p>
    </div>
    <div class="welcome-actions">
        <a href="agendamentos.php" class="btn" data-tooltip="Ver meus agendamentos">
            <i class="fas fa-calendar-alt"></i> Meus Agendamentos
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-check"></i> Formulário de Agendamento</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i> <strong>Agende seu horário!</strong> Escolha o melhor dia e horário para a manutenção do seu veículo.
                    </div>
                    
                    <form action="processar-agendamento.php" method="post">
            <div class="form-group">
                <label for="veiculo_id">Selecione o Veículo *</label>
                <select name="veiculo_id" id="veiculo_id" class="form-control" required>
                    <option value="">Escolha um veículo</option>
                    <?php while ($veiculo = $veiculos->fetch_assoc()): ?>
                        <option value="<?php echo $veiculo['id']; ?>">
                            <?php echo $veiculo['marca'] . ' ' . $veiculo['modelo'] . ' (' . $veiculo['placa'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="mecanico_id">Selecione o Mecânico *</label>
                <select name="mecanico_id" id="mecanico_id" class="form-control" required onchange="mostrarCalendario()">
                    <option value="">Escolha um mecânico</option>
                    <?php while ($mecanico = $mecanicos->fetch_assoc()): ?>
                        <option value="<?php echo $mecanico['id']; ?>" data-especialidade="<?php echo $mecanico['especialidade']; ?>">
                            <?php echo $mecanico['nome']; ?> - <?php echo $mecanico['especialidade']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div id="mecanicoInfo" class="mecanico-info">
                    <strong>Especialidade:</strong> <span id="especialidadeTexto"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="tipo_servico">Tipo de Serviço</label>
                <select name="tipo_servico" id="tipo_servico" class="form-control">
                    <option value="manutencao">Manutenção Preventiva</option>
                    <option value="reparo">Reparo</option>
                    <option value="revisao">Revisão</option>
                    <option value="diagnostico">Diagnóstico</option>
                    <option value="outros">Outros</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cupom_desconto">Cupom de Desconto (Opcional)</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="cupom_desconto" id="cupom_desconto" class="form-control" 
                           placeholder="Digite ou cole o código do cupom" style="flex: 1;">
                    <button type="button" class="btn btn-secondary" onclick="aplicarCupom()" style="white-space: nowrap;">
                        <i class="fas fa-tag"></i> Aplicar
                    </button>
                </div>
                <small class="form-text text-muted">🎟️ Possui um cupom de desconto? Cole aqui para aplicar ao agendamento.</small>
                <div id="cupomStatus" style="margin-top: 10px; display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações (Opcional)</label>
                <textarea name="observacoes" id="observacoes" class="form-control" rows="4" 
                          placeholder="Descreva o serviço que precisa ou alguma observação importante."></textarea>
                <small class="form-text text-muted">📝 Informe detalhes sobre o serviço que precisa ou problemas específicos.</small>
            </div>
            
            <div id="calendarioContainer" class="calendario-container">
                <h4><i class="fas fa-calendar"></i> Selecione Data e Horário *</h4>
                
                <div class="calendario-nav">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="navegarMes(-1)">← Anterior</button>
                    <h5 id="mesAtual"></h5>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="navegarMes(1)">Próximo →</button>
                </div>
                
                <div id="calendarioGrid" class="calendario-grid">
                    <div class="dia-header">Dom</div>
                    <div class="dia-header">Seg</div>
                    <div class="dia-header">Ter</div>
                    <div class="dia-header">Qua</div>
                    <div class="dia-header">Qui</div>
                    <div class="dia-header">Sex</div>
                    <div class="dia-header">Sáb</div>
                </div>
                
                <div id="horariosContainer" class="horarios-container">
                    <h6>Horários Disponíveis:</h6>
                    <div id="horariosGrid" class="horarios-grid"></div>
                </div>
                
                <input type="hidden" name="data_agendamento" id="dataAgendamento" required>
                <input type="hidden" name="hora_agendamento" id="horaAgendamento" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-calendar-check"></i> Confirmar Agendamento
                </button>
            </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card help-card">
                <div class="card-header">
                    <h3><i class="fas fa-question-circle"></i> Como Funciona?</h3>
                </div>
                <div class="card-body">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Escolha seu Veículo</h4>
                            <p>Selecione o veículo que precisa de manutenção na lista.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Selecione o Mecânico</h4>
                            <p>Escolha um mecânico especializado para seu tipo de serviço.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Tipo de Serviço</h4>
                            <p>Informe que tipo de serviço você precisa realizar.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Escolha Data/Hora</h4>
                            <p>Selecione o dia e horário que melhor se adequa à sua agenda.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>Confirme o Agendamento</h4>
                            <p>Revise os dados e confirme seu agendamento.</p>
                        </div>
                    </div>
                    
                    <div class="benefits">
                        <h4><i class="fas fa-check-circle"></i> Vantagens</h4>
                        <ul>
                            <li>✅ Agendamento rápido e fácil</li>
                            <li>✅ Mecânicos especializados</li>
                            <li>✅ Horários flexíveis</li>
                            <li>✅ Confirmação automática</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-welcome {
    background: linear-gradient(135deg, #109349 0%, #109349 33%, #ffffff 33%, #ffffff 66%, #DD0100 66%, #DD0100 100%);
    color: white;
    border: 2px solid #109349;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.theme-alemanha .dashboard-welcome {
    background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%);
    color: white;
    border: 2px solid #FFCE00;
}

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
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
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.theme-alemanha .welcome-actions .btn {
    color: #fff;
    background-color: #000000;
}

.welcome-actions .btn:hover {
    background-color: #0d7a3a;
    transform: translateY(-1px);
}

.theme-alemanha .welcome-actions .btn:hover {
    background-color: #333;
}

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
    gap: 20px;
}

.col-md-8 {
    flex: 1;
    min-width: 630px;
    padding: 0 15px;
}

.col-md-4 {
    flex: 0 0 400px;
    padding: 0 15px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
    height: fit-content;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.12);
}

.theme-alemanha .card {
    background: #1a1a1a;
    color: white;
    box-shadow: 0 10px 30px rgba(255,206,0,0.2);
}

.card-header {
    background: linear-gradient(135deg, #CE2A37, #a91e2a);
    color: white;
    padding: 20px;
    border-bottom: none;
}

.theme-alemanha .card-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.card-header h2, .card-header h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 30px;
}

.theme-alemanha .card-body {
    background: #1a1a1a;
    color: white;
}

.alert {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #109349;
}

.theme-alemanha .alert {
    background: #2a2a2a;
    border-color: #FFCE00;
    color: #FFCE00;
    border-left-color: #FFCE00;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}
.theme-alemanha .form-group label{
    color: white;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #109349;
    box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
}

.theme-alemanha .form-control {
    background: #2a2a2a;
    border-color: #444;
    color: white;
}

.theme-alemanha .form-control:focus {
    border-color: #FFCE00;
    box-shadow: 0 0 0 2px rgba(255, 206, 0, 0.1);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #109349;
    color: white;
}

.btn-primary:hover {
    background: #0d7a3a;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-primary:hover {
    background: #e6b800;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

.btn-block {
    width: 100%;
}

.mecanico-info {
    margin-top: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #109349;
    display: none;
}

.theme-alemanha .mecanico-info {
    background: #2a2a2a;
    border-left-color: #FFCE00;
    color: white;
}

.calendario-container {
    margin-top: 20px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    display: none;
}

.theme-alemanha .calendario-container {
    background: #2a2a2a;
    border-color: #444;
}

.calendario-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.calendario-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 15px;
}

.dia-header {
    text-align: center;
    font-weight: 500;
    padding: 8px;
    background: #109349;
    color: white;
    font-size: 12px;
}

.dia-calendario {
    text-align: center;
    padding: 8px;
    cursor: pointer;
    border: 1px solid #ddd;
    background: white;
    min-height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.dia-disponivel {
    background: #d4edda;
    color: #155724;
    border-color: #109349;
    font-weight: bold;
}

.dia-disponivel:hover {
    background: #109349;
    color: white;
}

.dia-selecionado {
    background: #109349 !important;
    color: white !important;
}

.dia-indisponivel {
    background: #f5f5f5;
    color: #999;
    cursor: not-allowed;
}

.horarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 8px;
    margin-top: 10px;
}

.horarios-container {
    display: none;
    margin-top: 15px;
}

.horarios-container h6 {
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
}

.horario-btn {
    padding: 8px 12px;
    border: 1px solid #109349;
    background: white;
    color: #109349;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
}

.horario-btn:hover {
    background: #109349;
    color: white;
}

.horario-selecionado {
    background: #109349 !important;
    color: white !important;
}

.step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
    border-left: 4px solid #109349;
}

.step:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.theme-alemanha .step {
    background: #2a2a2a;
    border-left-color: #FFCE00;
}

.step-number {
    background: #109349;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    margin-right: 15px;
    flex-shrink: 0;
}

.theme-alemanha .step-number {
    background: #FFCE00;
    color: #000;
}
.theme-alemanha .step-content h4{
    color: #ffffffdf;
}
.theme-alemanha .step-content p {
    color: #ffffff88;
    
}
.step-content h4 {
    margin-bottom: 5px;
    font-size: 14px;
    color: #333;
}

.step-content p {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.benefits {
    margin-top: 20px;
    padding: 20px;
    background: #d4edda;
    border-radius: 8px;
    border-left: 4px solid #109349;
}

.theme-alemanha .benefits {
    background: #2a2a2a;
    border-left-color: #FFCE00;
}

.benefits h4 {
    color: #109349;
    margin-bottom: 15px;
    font-size: 16px;
    font-weight: 600;
}

.theme-alemanha .benefits h4 {
    color: #FFCE00;
}

.benefits ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
.theme-alemanha .benefits li{
    color: #ffffffa2;
}
.benefits li {
    padding: 3px 0;
    font-size: 13px;
    color: #155724;
}

.form-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

@media (max-width: 1200px) {
    .container-fluid {
        max-width: 100%;
        padding: 15px;
    }
    
    .row {
        flex-direction: column;
        gap: 15px;
    }
    
    .col-md-8, .col-md-4 {
        flex: none;
        min-width: auto;
        max-width: 100%;
        padding: 0;
    }
}

@media (max-width: 768px) {
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
    
    .calendario-grid {
        gap: 1px;
    }
    
    .horarios-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .step {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .card-body {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .mobile-welcome-text {
        font-size: 16px;
        padding: 6px 12px;
    }
}

@media (max-width: 360px) {
    .mobile-welcome-text {
        font-size: 14px;
        padding: 4px 8px;
        top: 60px;
    }
}
</style>

<script>
let mesAtual = new Date().getMonth();
let anoAtual = new Date().getFullYear();
let disponibilidade = {};

const meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

function mostrarCalendario() {
    const mecanicoId = document.getElementById('mecanico_id').value;
    const mecanicoInfo = document.getElementById('mecanicoInfo');
    const calendarioContainer = document.getElementById('calendarioContainer');
    
    if (mecanicoId) {
        const select = document.getElementById('mecanico_id');
        const especialidade = select.options[select.selectedIndex].getAttribute('data-especialidade');
        document.getElementById('especialidadeTexto').textContent = especialidade;
        mecanicoInfo.style.display = 'block';
        calendarioContainer.style.display = 'block';
        
        carregarDisponibilidade(mecanicoId);
    } else {
        mecanicoInfo.style.display = 'none';
        calendarioContainer.style.display = 'none';
    }
}

function carregarDisponibilidade(mecanicoId) {
    fetch(`api-disponibilidade.php?mecanico_id=${mecanicoId}`)
        .then(response => response.json())
        .then(data => {
            disponibilidade = data;
            gerarCalendario();
        })
        .catch(error => {
            console.error('Erro ao carregar disponibilidade:', error);
            // Usar disponibilidade padrão em caso de erro
            disponibilidade = {
                1: {hora_inicio: '08:00', hora_fim: '17:00'},
                2: {hora_inicio: '08:00', hora_fim: '17:00'},
                3: {hora_inicio: '08:00', hora_fim: '17:00'},
                4: {hora_inicio: '08:00', hora_fim: '17:00'},
                5: {hora_inicio: '08:00', hora_fim: '17:00'}
            };
            gerarCalendario();
        });
}

function navegarMes(direcao) {
    mesAtual += direcao;
    if (mesAtual > 11) {
        mesAtual = 0;
        anoAtual++;
    } else if (mesAtual < 0) {
        mesAtual = 11;
        anoAtual--;
    }
    gerarCalendario();
}

function gerarCalendario() {
    const grid = document.getElementById('calendarioGrid');
    const mesTexto = document.getElementById('mesAtual');
    
    mesTexto.textContent = `${meses[mesAtual]} ${anoAtual}`;
    
    // Remover dias anteriores
    const diasAnteriores = grid.querySelectorAll('.dia-calendario');
    diasAnteriores.forEach(dia => dia.remove());
    
    const primeiroDia = new Date(anoAtual, mesAtual, 1).getDay();
    const ultimoDia = new Date(anoAtual, mesAtual + 1, 0).getDate();
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    
    // Adicionar dias vazios no início
    for (let i = 0; i < primeiroDia; i++) {
        const diaVazio = document.createElement('div');
        diaVazio.className = 'dia-calendario';
        grid.appendChild(diaVazio);
    }
    
    // Adicionar dias do mês
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const dataAtual = new Date(anoAtual, mesAtual, dia);
        const diaSemana = dataAtual.getDay();
        const dataString = `${anoAtual}-${String(mesAtual + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        
        const diaElement = document.createElement('div');
        diaElement.className = 'dia-calendario';
        diaElement.textContent = dia;
        
        // Verificar se o mecânico trabalha neste dia da semana e se não é passado
        if (dataAtual >= hoje && disponibilidade[diaSemana]) {
            diaElement.classList.add('dia-disponivel');
            diaElement.onclick = () => selecionarDia(dataString, diaElement);
        } else {
            diaElement.classList.add('dia-indisponivel');
        }
        
        grid.appendChild(diaElement);
    }
}

function selecionarDia(data, elemento) {
    // Remover seleção anterior
    document.querySelectorAll('.dia-selecionado').forEach(el => {
        el.classList.remove('dia-selecionado');
    });
    
    elemento.classList.add('dia-selecionado');
    document.getElementById('dataAgendamento').value = data;
    mostrarHorarios(data);
}

function mostrarHorarios(data) {
    const container = document.getElementById('horariosContainer');
    const grid = document.getElementById('horariosGrid');
    const diaSemana = new Date(data).getDay();
    
    grid.innerHTML = '';
    
    if (disponibilidade[diaSemana]) {
        const inicio = disponibilidade[diaSemana].hora_inicio;
        const fim = disponibilidade[diaSemana].hora_fim;
        const horarios = gerarHorarios(inicio, fim);
        
        horarios.forEach(horario => {
            const slot = document.createElement('button');
            slot.type = 'button';
            slot.className = 'horario-btn';
            slot.textContent = horario;
            slot.onclick = () => selecionarHorario(horario, slot);
            grid.appendChild(slot);
        });
        
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function gerarHorarios(inicio, fim) {
    const horarios = [];
    const [horaInicio, minutoInicio] = inicio.split(':').map(Number);
    const [horaFim, minutoFim] = fim.split(':').map(Number);
    
    let horaAtual = horaInicio;
    let minutoAtual = minutoInicio;
    
    while (horaAtual < horaFim || (horaAtual === horaFim && minutoAtual < minutoFim)) {
        const horarioFormatado = `${String(horaAtual).padStart(2, '0')}:${String(minutoAtual).padStart(2, '0')}`;
        horarios.push(horarioFormatado);
        
        minutoAtual += 60; // Intervalos de 1 hora
        if (minutoAtual >= 60) {
            minutoAtual = 0;
            horaAtual++;
        }
    }
    
    return horarios;
}

function selecionarHorario(horario, elemento) {
    document.querySelectorAll('.horario-selecionado').forEach(el => {
        el.classList.remove('horario-selecionado');
    });
    
    elemento.classList.add('horario-selecionado');
    document.getElementById('horaAgendamento').value = horario;
}

function aplicarCupom() {
    const cupomInput = document.getElementById('cupom_desconto');
    const cupomStatus = document.getElementById('cupomStatus');
    const cupom = cupomInput.value.trim();
    
    if (!cupom) {
        cupomStatus.innerHTML = '<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 6px; border-left: 4px solid #dc3545;"><i class="fas fa-exclamation-circle"></i> Digite um cupom válido</div>';
        cupomStatus.style.display = 'block';
        return;
    }
    
    // Simular validação do cupom
    const cuponsValidos = ['BANANA50', 'TACALEPAU40', 'BARATO45', 'QUEIMA35', 'PNEU30'];
    
    if (cuponsValidos.includes(cupom.toUpperCase())) {
        cupomStatus.innerHTML = '<div style="color: #155724; padding: 10px; background: #d4edda; border-radius: 6px; border-left: 4px solid #28a745;"><i class="fas fa-check-circle"></i> Cupom aplicado com sucesso!</div>';
        cupomStatus.style.display = 'block';
        cupomInput.style.borderColor = '#28a745';
    } else {
        cupomStatus.innerHTML = '<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 6px; border-left: 4px solid #dc3545;"><i class="fas fa-times-circle"></i> Cupom inválido ou expirado</div>';
        cupomStatus.style.display = 'block';
        cupomInput.style.borderColor = '#dc3545';
    }
}

// Verificar se há cupom na URL ao carregar a página
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const cupom = urlParams.get('cupom');
    
    if (cupom) {
        document.getElementById('cupom_desconto').value = cupom;
        aplicarCupom();
    }
});
</script>

<?php require_once 'footer.php'; ?>