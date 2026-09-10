<?php
require_once 'header.php';

// Redirecionar funcionários - eles não podem solicitar diagnósticos
if (isset($_SESSION['mecanico_id']) && $_SESSION['mecanico_id'] > 0) {
    header('Location: meus-diagnosticos.php');
    exit;
}

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
    <div class="mobile-welcome-text">Diagnóstico Gratuito</div>
    <div class="welcome-message">
        <h2><i class="fas fa-stethoscope"></i> Diagnóstico Gratuito</h2>
        <p>Solicite um diagnóstico completo e gratuito para seu veículo com nossos mecânicos especializados.</p>
    </div>
    <div class="welcome-actions">
        <a href="veiculos.php" class="btn" data-tooltip="Ver meus veículos">
            <i class="fas fa-car"></i> Meus Veículos
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-check"></i> Formulário de Solicitação</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-gift"></i> <strong>100% GRATUITO!</strong> Nossos mecânicos fazem diagnósticos completos sem nenhum custo para você.
                    </div>
                    
                    <form action="processar-diagnostico-cliente.php" method="post" enctype="multipart/form-data" id="formDiagnostico">
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
                <label for="descricao_problema">Descreva o Problema (Opcional)</label>
                <textarea name="descricao_problema" id="descricao_problema" class="form-control" rows="4" 
                          placeholder="Se souber, descreva o problema do seu veículo. Caso contrário, deixe em branco que o mecânico fará um diagnóstico completo gratuito."></textarea>
                <small class="form-text text-muted">💡 Não sabe nada de carros? Sem problema! Deixe em branco e nosso mecânico fará uma avaliação completa gratuita.</small>
            </div>
            
            <div class="form-group">
                <label for="foto_problema">Foto do Problema (Opcional)</label>
                <input type="file" name="fotos[]" id="foto_problema" class="form-control" accept="image/*" multiple onchange="showPreview(this)">
                <small class="form-text text-muted">📷 Selecione até 3 fotos do problema (JPG, PNG - máx 5MB cada)</small>
                <div id="previewContainer" class="preview-container"></div>
            </div>
            
            <div class="form-group">
                <label for="urgencia">Nível de Urgência</label>
                <select name="urgencia" id="urgencia" class="form-control">
                    <option value="baixa">Baixa - Posso aguardar</option>
                    <option value="media">Média - Preciso resolver em breve</option>
                    <option value="alta">Alta - Preciso resolver urgentemente</option>
                </select>
            </div>
            
            <div id="calendarioContainer" class="calendario-container">
                <h4><i class="fas fa-calendar"></i> Selecione Data e Horário *</h4>
                <div id="alertaAgendamento" style="display:none;margin-bottom:15px;"></div>
                
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
                
                <input type="hidden" name="data_agendamento" id="dataAgendamento">
                <input type="hidden" name="hora_agendamento" id="horaAgendamento">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-paper-plane"></i> Solicitar Diagnóstico Gratuito
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
                            <p>Selecione o veículo que precisa de diagnóstico na lista.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Selecione o Mecânico</h4>
                            <p>Escolha um mecânico especializado para seu tipo de problema.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Descreva o Problema</h4>
                            <p>Se souber, descreva o problema e anexe fotos. Caso contrário, deixe em branco.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Escolha Data/Hora</h4>
                            <p>Selecione quando é melhor para você ou deixe que entremos em contato.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>Envie a Solicitação</h4>
                            <p>Clique em "Solicitar" e aguarde nosso contato em até 2 horas.</p>
                        </div>
                    </div>
                    
                    <div class="benefits">
                        <h4><i class="fas fa-check-circle"></i> Vantagens</h4>
                        <ul>
                            <li>✅ Diagnóstico 100% gratuito</li>
                            <li>✅ Mecânicos especializados</li>
                            <li>✅ Atendimento rápido</li>
                            <li>✅ Orçamento sem compromisso</li>
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

#alertaAgendamento {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

.dia {
    text-align: center;
    padding: 8px;
    cursor: pointer;
    border: 1px solid #ddd;
    background: white;
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
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.horario-btn {
    padding: 8px 12px;
    border: 1px solid #109349;
    background: white;
    color: #109349;
    border-radius: 4px;
    cursor: pointer;
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

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
    position: relative;
}

.upload-area:hover {
    border-color: #109349;
    background: #f0f8f0;
}

.upload-area.dragover {
    border-color: #109349;
    background: #e8f5e8;
    transform: scale(1.02);
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-content {
    pointer-events: none;
}

.upload-icon {
    font-size: 2.5rem;
    color: #109349;
    margin-bottom: 15px;
    display: block;
}

.upload-text {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 8px;
    font-weight: 500;
}

.upload-hint {
    color: #666;
    font-size: 0.9rem;
}

.preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.preview-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    background: #f9f9f9;
}

.preview-image {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 5px;
}

.file-name {
    font-size: 12px;
    color: #666;
    word-break: break-all;
}

.theme-alemanha .upload-area {
    background: #2a2a2a;
    border-color: #444;
}

.theme-alemanha .upload-area:hover {
    border-color: #FFCE00;
    background: #333;
}

.theme-alemanha .upload-icon {
    color: #FFCE00;
}

.theme-alemanha .upload-text {
    color: #fff;
}

.theme-alemanha .upload-hint {
    color: #ccc;
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
        margin-top: 20px;
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
} {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-color);
    font-size: 15px;
}

.form-control {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    box-sizing: border-box;
}
.theme-alemanha .form-control{
    color: white;
}

.form-control:focus {
    border-color: #109349;
    box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.2);
    outline: none;
}

.btn-primary {
    background: #109349;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.btn-primary:hover {
    background: #0d7a3c;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.btn-lg {
    padding: 16px 32px;
    font-size: 18px;
}

.btn-block {
    width: 100%;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
    padding: 10px;
    margin-bottom: 15px;
}

.mecanico-info {
    display: none;
    margin-top: 8px;
    padding: 10px;
    background: #e8f5e8;
    border-radius: 4px;
    border-left: 4px solid #28a745;
}

.calendario-container {
    display: none;
    margin-top: 20px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.calendario-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.calendario-nav h5 {
    margin: 0;
    font-weight: 600;
}

.calendario-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 3px;
    margin-bottom: 15px;
}

.dia-header {
    text-align: center;
    font-weight: bold;
    padding: 8px;
    background: #e9ecef;
    border-radius: 4px;
    font-size: 12px;
}

.dia-calendario {
    padding: 8px;
    text-align: center;
    cursor: pointer;
    border-radius: 4px;
    border: 1px solid #dee2e6;
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
    border-color: #28a745;
    font-weight: bold;
}

.dia-disponivel:hover {
    background: #28a745;
    color: white;
}

.dia-selecionado {
    background: #109349;
    color: white;
    border-color: #109349;
}

.horarios-container {
    display: none;
    margin-top: 15px;
}

.horarios-container.show {
    display: block;
}

.horarios-container h6 {
    margin-bottom: 10px;
    font-weight: 600;
}

.horarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 8px;
    padding-bottom: 20px;
}

.horario-slot {
    padding: 8px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 13px;
}

.horario-slot:hover {
    border-color: #109349;
    background: #e8f5e8;
}

.horario-selecionado {
    background: #109349;
    color: white;
}

.horario-ocupado {
    background: #f8d7da !important;
    color: #721c24 !important;
    border-color: #dc3545 !important;
    border-width: 2px !important;
    text-decoration: line-through !important;
    opacity: 0.7 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    position: relative;
}

.horario-ocupado:hover {
    background: #f8d7da !important;
    border-color: #dc3545 !important;
    transform: none !important;
}

.horario-ocupado::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #dc3545;
    transform: translateY(-50%);
}

.horario-ocupado::after {
    content: '✖ Ocupado';
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    color: #dc3545;
    white-space: nowrap;
    font-weight: bold;
    text-decoration: none;
}

body.theme-alemanha .card-header {
    background: #000;
    color: #FFCE00;
}

body.theme-alemanha .btn-primary {
    background: #FFCE00;
    color: #000;
}

body.theme-alemanha .dia-disponivel {
    background: #FFF8DC;
    color: #B8860B;
}

body.theme-alemanha .dia-selecionado,
body.theme-alemanha .horario-selecionado {
    background: #FFCE00;
    color: #000;
}

.step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.step:last-child {
    border-bottom: none;
}

.step-number {
    width: 30px;
    height: 30px;
    background: #109349;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.step-content p {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.benefits {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #109349;
}

.benefits h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #109349;
}

.benefits ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.benefits li {
    font-size: 13px;
    margin-bottom: 5px;
    color: #666;
}

body.theme-alemanha .card-header {
    background: #000;
}

body.theme-alemanha .step-number {
    background: #FFCE00;
    color: #000;
}

body.theme-alemanha .benefits {
    border-top-color: #FFCE00;
}

body.theme-alemanha .benefits h4 {
    color: #FFCE00;
}

@media (max-width: 768px) {
    .col-md-8, .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .help-card {
        position: static;
        margin-top: 20px;
    }
    
    .calendario-nav { flex-direction: column; gap: 8px; }
    .horarios-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 480px) {
    .container-fluid { padding: 0 10px; }
    .row { margin: 0 -10px; }
    .col-md-8, .col-md-4 { padding: 0 10px; }
    .horarios-grid { grid-template-columns: 1fr; }
    .card-body, .card-header { padding: 12px; }
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
        gerarCalendario();
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
        .catch(error => console.error('Erro ao carregar disponibilidade:', error));
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
    
    const diasAnteriores = grid.querySelectorAll('.dia-calendario');
    diasAnteriores.forEach(dia => dia.remove());
    
    const primeiroDia = new Date(anoAtual, mesAtual, 1).getDay();
    const ultimoDia = new Date(anoAtual, mesAtual + 1, 0).getDate();
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    
    for (let i = 0; i < primeiroDia; i++) {
        const diaVazio = document.createElement('div');
        diaVazio.className = 'dia-calendario';
        grid.appendChild(diaVazio);
    }
    
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const dataAtual = new Date(anoAtual, mesAtual, dia);
        dataAtual.setHours(0, 0, 0, 0);
        const dataString = `${anoAtual}-${String(mesAtual + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        
        const diaElement = document.createElement('div');
        diaElement.className = 'dia-calendario';
        diaElement.textContent = dia;
        
        if (dataAtual >= hoje) {
            diaElement.classList.add('dia-disponivel');
            diaElement.onclick = () => selecionarDia(dataString, diaElement);
        }
        
        grid.appendChild(diaElement);
    }
}

function selecionarDia(data, elemento) {
    const mecanicoId = document.getElementById('mecanico_id').value;
    
    fetch(`api-horarios-ocupados.php?mecanico_id=${mecanicoId}&data=${data}`)
        .then(response => response.json())
        .then(horariosOcupados => {
            const totalHorarios = 8;
            
            if (horariosOcupados.length >= totalHorarios) {
                alert('Todos os horários deste dia já estão ocupados. Por favor, escolha outra data.');
                return;
            }
            
            document.querySelectorAll('.dia-selecionado').forEach(el => {
                el.classList.remove('dia-selecionado');
            });
            
            elemento.classList.add('dia-selecionado');
            document.getElementById('dataAgendamento').value = data;
            
            mostrarHorarios(data);
        });
}

function mostrarHorarios(data) {
    const container = document.getElementById('horariosContainer');
    const grid = document.getElementById('horariosGrid');
    const mecanicoId = document.getElementById('mecanico_id').value;
    
    grid.innerHTML = '<div style="text-align:center;padding:10px;">Carregando horários...</div>';
    
    const legendaExistente = container.querySelector('.legenda-ocupados');
    if (legendaExistente) legendaExistente.remove();
    
    console.log('Buscando horários para:', mecanicoId, data);
    
    fetch(`api-horarios-ocupados.php?mecanico_id=${mecanicoId}&data=${data}`)
        .then(response => {
            console.log('Resposta da API:', response);
            return response.json();
        })
        .then(horariosOcupados => {
            console.log('Horários ocupados recebidos:', horariosOcupados);
            const horarios = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
            
            grid.innerHTML = '';
            
            let horariosDisponiveis = 0;
            horarios.forEach(horario => {
                const slot = document.createElement('div');
                slot.className = 'horario-slot';
                slot.textContent = horario;
                
                if (horariosOcupados.includes(horario)) {
                    slot.classList.add('horario-ocupado');
                    slot.title = 'Horário já ocupado';
                    console.log('Horário ocupado:', horario);
                } else {
                    slot.onclick = () => selecionarHorario(horario, slot);
                    horariosDisponiveis++;
                }
                
                grid.appendChild(slot);
            });
            
            if (horariosDisponiveis === 0) {
                grid.innerHTML = '<div style="text-align:center;padding:10px;color:#dc3545;font-weight:bold;"><i class="fas fa-times-circle"></i> Nenhum horário disponível nesta data.</div>';
            } else if (horariosOcupados.length > 0) {
                const legenda = document.createElement('div');
                legenda.className = 'legenda-ocupados';
                legenda.style.cssText = 'text-align:center;padding:10px;color:#856404;background:#fff3cd;border-radius:4px;margin-top:10px;font-size:12px;';
                legenda.innerHTML = `<i class="fas fa-info-circle"></i> ${horariosOcupados.length} horário(s) já ocupado(s) nesta data`;
                container.appendChild(legenda);
            }
            
            container.style.display = 'block';
        })
        .catch(error => {
            console.error('Erro ao carregar horários:', error);
            grid.innerHTML = '<div style="text-align:center;padding:10px;color:#dc3545;">Erro ao carregar horários.</div>';
        });
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
        
        minutoAtual += 30;
        if (minutoAtual >= 60) {
            minutoAtual = 0;
            horaAtual++;
        }
    }
    
    return horarios;
}

function selecionarHorario(horario, elemento) {
    if (elemento.classList.contains('horario-ocupado')) {
        return;
    }
    
    document.querySelectorAll('.horario-selecionado').forEach(el => {
        el.classList.remove('horario-selecionado');
    });
    
    elemento.classList.add('horario-selecionado');
    document.getElementById('horaAgendamento').value = horario;
}

function mostrarAlerta(mensagem, tipo) {
    const alerta = document.getElementById('alertaAgendamento');
    alerta.style.cssText = `display:block;padding:15px;border-radius:8px;margin-bottom:15px;font-weight:500;`;
    
    if (tipo === 'erro') {
        alerta.style.background = '#f8d7da';
        alerta.style.color = '#721c24';
        alerta.style.border = '1px solid #f5c6cb';
        alerta.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${mensagem}`;
    } else if (tipo === 'aviso') {
        alerta.style.background = '#fff3cd';
        alerta.style.color = '#856404';
        alerta.style.border = '1px solid #ffeaa7';
        alerta.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${mensagem}`;
    }
    
    alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    setTimeout(() => {
        alerta.style.display = 'none';
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDiagnostico');
    if (form) {
        form.addEventListener('submit', function(e) {
            const dataAgendamento = document.getElementById('dataAgendamento').value;
            const horaAgendamento = document.getElementById('horaAgendamento').value;
            const mecanicoId = document.getElementById('mecanico_id').value;
            
            if (!dataAgendamento || !horaAgendamento) {
                e.preventDefault();
                mostrarAlerta('É obrigatório selecionar a DATA e o HORÁRIO do agendamento!', 'aviso');
                return;
            }
            
            if (dataAgendamento && horaAgendamento && mecanicoId) {
                e.preventDefault();
                
                console.log('Validando:', mecanicoId, dataAgendamento, horaAgendamento);
                
                fetch(`api-horarios-ocupados.php?mecanico_id=${mecanicoId}&data=${dataAgendamento}`)
                    .then(response => response.json())
                    .then(horariosOcupados => {
                        console.log('Horários ocupados:', horariosOcupados);
                        console.log('Horário selecionado:', horaAgendamento);
                        console.log('Está ocupado?', horariosOcupados.includes(horaAgendamento));
                        
                        if (horariosOcupados.includes(horaAgendamento)) {
                            mostrarAlerta('Este horário já está ocupado! Por favor, escolha outro horário disponível.', 'erro');
                            mostrarHorarios(dataAgendamento);
                        } else {
                            console.log('Enviando formulário...');
                            form.submit();
                        }
                    })
                    .catch(error => {
                        console.error('Erro na validação:', error);
                        mostrarAlerta('Erro ao validar horário. Tente novamente.', 'erro');
                    });
            }
        });
    }
});

function showPreview(input) {
    const container = document.getElementById('previewContainer');
    container.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        if (input.files.length > 3) {
            alert('Máximo 3 fotos permitidas');
            input.value = '';
            return;
        }
        
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            
            if (file.size > 5 * 1024 * 1024) {
                alert(`Arquivo ${file.name} é muito grande. Máximo 5MB.`);
                continue;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" class="preview-image" alt="Preview">
                    <span class="file-name">${file.name}</span>
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>

<?php require_once 'footer.php'; ?>