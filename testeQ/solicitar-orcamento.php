<?php
$titulo = "Solicitar Orçamento Detalhado";
require_once 'header.php';

if (!isset($_GET['diagnostico_id'])) {
    header("Location: relatorios.php");
    exit;
}

$diagnostico_id = $_GET['diagnostico_id'];
$conexao = conectarBD();

// Verificar se o diagnóstico pertence ao usuário
$stmt = $conexao->prepare("SELECT rc.*, v.marca, v.modelo, v.placa, m.nome as mecanico_nome 
                          FROM relatorios_cliente rc 
                          LEFT JOIN veiculos v ON rc.veiculo_id = v.id
                          LEFT JOIN respostas_diagnostico rd ON rc.id = rd.diagnostico_id
                          LEFT JOIN mecanicos m ON rd.mecanico_id = m.id
                          WHERE rc.id = ? AND rc.usuario_id = ?");
$stmt->bind_param("ii", $diagnostico_id, $_SESSION['usuario_id']);
$stmt->execute();
$diagnostico = $stmt->get_result()->fetch_assoc();

if (!$diagnostico) {
    header("Location: relatorios.php");
    exit;
}

$conexao->close();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calculator"></i> Solicitar Orçamento Detalhado</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Orçamento Gratuito!</strong> Envie fotos do problema para receber um orçamento mais preciso.
                    </div>
                    
                    <div class="veiculo-info">
                        <h4><i class="fas fa-car"></i> Veículo</h4>
                        <p><?php echo $diagnostico['marca'] . ' ' . $diagnostico['modelo'] . ' (' . $diagnostico['placa'] . ')'; ?></p>
                        <?php if ($diagnostico['mecanico_nome']): ?>
                        <p><strong>Mecânico:</strong> <?php echo $diagnostico['mecanico_nome']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <form action="processar-orcamento.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="diagnostico_id" value="<?php echo $diagnostico_id; ?>">
                        
                        <div class="form-group">
                            <label for="descricao_detalhada">Descrição Detalhada do Problema *</label>
                            <textarea name="descricao_detalhada" id="descricao_detalhada" class="form-control" rows="4" required
                                      placeholder="Descreva em detalhes o problema, sintomas, ruídos, quando acontece, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="fotos">Fotos do Problema</label>
                            <input type="file" name="fotos[]" id="fotos" class="form-control" multiple accept="image/*">
                            <small class="form-text text-muted">
                                <i class="fas fa-camera"></i> Envie até 5 fotos (máx. 5MB cada). Formatos: JPG, PNG, GIF
                            </small>
                            <div id="preview-fotos" class="preview-container"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="urgencia_orcamento">Urgência do Orçamento</label>
                            <select name="urgencia_orcamento" id="urgencia_orcamento" class="form-control">
                                <option value="normal">Normal - Até 24 horas</option>
                                <option value="urgente">Urgente - Até 4 horas</option>
                                <option value="emergencia">Emergência - Até 1 hora</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoes_orcamento">Observações Adicionais</label>
                            <textarea name="observacoes_orcamento" id="observacoes_orcamento" class="form-control" rows="3"
                                      placeholder="Informações adicionais, preferências de peças, orçamento máximo, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-paper-plane"></i> Solicitar Orçamento Detalhado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card help-card">
                <div class="card-header">
                    <h3><i class="fas fa-lightbulb"></i> Dicas para Fotos</h3>
                </div>
                <div class="card-body">
                    <div class="tip">
                        <div class="tip-icon">📸</div>
                        <div class="tip-content">
                            <h4>Tire Fotos Claras</h4>
                            <p>Use boa iluminação e foque no problema específico.</p>
                        </div>
                    </div>
                    
                    <div class="tip">
                        <div class="tip-icon">🔍</div>
                        <div class="tip-content">
                            <h4>Diferentes Ângulos</h4>
                            <p>Fotografe de vários ângulos para mostrar o contexto completo.</p>
                        </div>
                    </div>
                    
                    <div class="tip">
                        <div class="tip-icon">📏</div>
                        <div class="tip-content">
                            <h4>Inclua Referências</h4>
                            <p>Use objetos como moedas para mostrar o tamanho do problema.</p>
                        </div>
                    </div>
                    
                    <div class="tip">
                        <div class="tip-icon">⚠️</div>
                        <div class="tip-content">
                            <h4>Segurança Primeiro</h4>
                            <p>Não tire fotos com o motor ligado ou em locais perigosos.</p>
                        </div>
                    </div>
                    
                    <div class="benefits">
                        <h4><i class="fas fa-check-circle"></i> Com Fotos Você Recebe</h4>
                        <ul>
                            <li>✅ Orçamento mais preciso</li>
                            <li>✅ Diagnóstico mais rápido</li>
                            <li>✅ Menos surpresas no valor</li>
                            <li>✅ Melhor planejamento</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
    padding: 0 15px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding: 0 15px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background: #109349;
    color: white;
    padding: 20px;
}

.card-header h2, .card-header h3 {
    margin: 0;
    font-size: 1.5rem;
}

.card-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
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

.form-control:focus {
    border-color: #109349;
    box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.2);
    outline: none;
}

.btn-primary {
    background: #109349;
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 6px;
    font-size: 18px;
    cursor: pointer;
    width: 100%;
}

.btn-primary:hover {
    background: #0d7a3c;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
    padding: 15px;
    margin-bottom: 20px;
}

.veiculo-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #109349;
}

.preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.preview-item img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
    font-size: 12px;
}

.tip {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.tip:last-child {
    border-bottom: none;
}

.tip-icon {
    font-size: 24px;
    margin-right: 15px;
    flex-shrink: 0;
}

.tip-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.tip-content p {
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
    color: #FFCE00;
}

body.theme-alemanha .btn-primary {
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
}
</style>

<script>
document.getElementById('fotos').addEventListener('change', function(e) {
    const files = e.target.files;
    const preview = document.getElementById('preview-fotos');
    preview.innerHTML = '';
    
    if (files.length > 5) {
        alert('Máximo 5 fotos permitidas');
        e.target.value = '';
        return;
    }
    
    Array.from(files).forEach((file, index) => {
        if (file.size > 5 * 1024 * 1024) {
            alert('Arquivo muito grande: ' + file.name + '. Máximo 5MB por foto.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" onclick="removePreview(this, ${index})">×</button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function removePreview(btn, index) {
    btn.parentElement.remove();
    const input = document.getElementById('fotos');
    const dt = new DataTransfer();
    const files = input.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    input.files = dt.files;
}
</script>

<?php require_once 'footer.php'; ?>