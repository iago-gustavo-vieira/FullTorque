<?php
$titulo = "Meus Diagnósticos";
require_once 'header.php';

// Verificar se o usuário é um mecânico
if (!isset($_SESSION['mecanico_id']) || $_SESSION['mecanico_id'] <= 0) {
    header("Location: index.php");
    exit;
}

$conexao = conectarBD();
$mecanico_id = $_SESSION['mecanico_id'];

// Buscar diagnósticos direcionados para este mecânico
$stmt = $conexao->prepare("
    SELECT r.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone,
           v.marca, v.modelo, v.placa, v.ano
    FROM relatorios_cliente r
    JOIN usuarios u ON r.usuario_id = u.id
    LEFT JOIN veiculos v ON r.veiculo_id = v.id
    WHERE r.mecanico_id = ? OR r.mecanico_id IS NULL
    ORDER BY r.data_envio DESC
");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$diagnosticos = $stmt->get_result();

// Buscar informações do mecânico
$stmt = $conexao->prepare("SELECT nome, especialidade FROM mecanicos WHERE id = ?");
$stmt->bind_param("i", $mecanico_id);
$stmt->execute();
$mecanico_info = $stmt->get_result()->fetch_assoc();

$conexao->close();
?>

<style>
    .page-header {
        background: linear-gradient(135deg, #109349 0%, #ffffff 50%, #DD0100 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .theme-alemanha .page-header {
        background: linear-gradient(135deg, #000000 0%, #DD0100 50%, #FFCE00 100%);
    }
    
    .mecanico-info {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 5px solid #109349;
    }
    
    .theme-alemanha .mecanico-info {
        background: #1a1a1a;
        color: white;
        border-left-color: #FFCE00;
    }
    
    .diagnosticos-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .theme-alemanha .diagnosticos-container {
        background: #1a1a1a;
        color: white;
    }
    
    .diagnostico-card {
        border-bottom: 1px solid #eee;
        padding: 25px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .diagnostico-card:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .theme-alemanha .diagnostico-card {
        border-bottom-color: #333;
    }
    
    .theme-alemanha .diagnostico-card:hover {
        background-color: #2a2a2a;
    }
    
    .diagnostico-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .cliente-info {
        flex: 1;
    }
    
    .cliente-nome {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .theme-alemanha .cliente-nome {
        color: #FFCE00;
    }
    
    .veiculo-info {
        color: #666;
        font-size: 0.9rem;
    }
    
    .theme-alemanha .veiculo-info {
        color: #ccc;
    }
    
    .data-envio {
        text-align: right;
        color: #888;
        font-size: 0.85rem;
    }
    
    .problema-descricao {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        border-left: 4px solid #109349;
    }
    
    .theme-alemanha .problema-descricao {
        background: #2a2a2a;
        border-left-color: #FFCE00;
    }
    
    .urgencia-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .urgencia-baixa {
        background: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
    }
    
    .urgencia-media {
        background: rgba(241, 196, 15, 0.1);
        color: #f1c40f;
    }
    
    .urgencia-alta {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    
    .contato-info {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        font-size: 0.9rem;
    }
    
    .contato-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #666;
    }
    
    .theme-alemanha .contato-item {
        color: #ccc;
    }
    
    .contato-item i {
        color: #109349;
    }
    
    .theme-alemanha .contato-item i {
        color: #FFCE00;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #ddd;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
        border-left: 4px solid #109349;
    }
    
    .theme-alemanha .stat-card {
        background: #1a1a1a;
        color: white;
        border-left-color: #FFCE00;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #109349;
        margin-bottom: 5px;
    }
    
    .theme-alemanha .stat-number {
        color: #FFCE00;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .theme-alemanha .stat-label {
        color: #ccc;
    }
    
    .fotos-diagnostico {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #109349;
    }
    
    .theme-alemanha .fotos-diagnostico {
        background: #2a2a2a;
        border-left-color: #FFCE00;
    }
    
    .fotos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    
    .foto-thumb {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
        border: 2px solid #ddd;
    }
    
    .foto-thumb:hover {
        transform: scale(1.05);
        border-color: #109349;
    }
    
    .theme-alemanha .foto-thumb:hover {
        border-color: #FFCE00;
    }
    
    #modalFoto {
        display: none;
        position: fixed;
        z-index: 10002;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
    }
    
    #modalFoto img {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .fechar-modal {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<div class="page-header">
    <h1><i class="fas fa-stethoscope"></i> <?php echo $titulo; ?></h1>
    <p>Diagnósticos direcionados para você</p>
</div>

<?php if ($mecanico_info): ?>
<div class="mecanico-info">
    <h3><i class="fas fa-user-tie"></i> Informações do Mecânico</h3>
    <p><strong>Nome:</strong> <?php echo $mecanico_info['nome']; ?></p>
    <p><strong>Especialidade:</strong> <?php echo $mecanico_info['especialidade']; ?></p>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $diagnosticos->num_rows; ?></div>
        <div class="stat-label">Total de Diagnósticos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <?php 
            $hoje = 0;
            $diagnosticos->data_seek(0);
            while ($diag = $diagnosticos->fetch_assoc()) {
                if (date('Y-m-d', strtotime($diag['data_envio'])) == date('Y-m-d')) {
                    $hoje++;
                }
            }
            echo $hoje;
            ?>
        </div>
        <div class="stat-label">Hoje</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <?php 
            $urgentes = 0;
            $diagnosticos->data_seek(0);
            while ($diag = $diagnosticos->fetch_assoc()) {
                if ($diag['urgencia'] == 'alta') {
                    $urgentes++;
                }
            }
            echo $urgentes;
            ?>
        </div>
        <div class="stat-label">Urgentes</div>
    </div>
</div>

<div class="diagnosticos-container">
    <?php if ($diagnosticos->num_rows > 0): ?>
        <?php 
        $diagnosticos->data_seek(0);
        while ($diagnostico = $diagnosticos->fetch_assoc()): 
        ?>
            <div class="diagnostico-card" onclick="expandirDiagnostico(<?php echo $diagnostico['id']; ?>)">
                <div class="diagnostico-header">
                    <div class="cliente-info">
                        <div class="cliente-nome">
                            <i class="fas fa-user"></i> <?php echo $diagnostico['cliente_nome']; ?>
                        </div>
                        <?php if ($diagnostico['marca']): ?>
                        <div class="veiculo-info">
                            <i class="fas fa-car"></i> 
                            <?php echo $diagnostico['marca'] . ' ' . $diagnostico['modelo'] . ' (' . $diagnostico['placa'] . ') - ' . $diagnostico['ano']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="data-envio">
                        <i class="fas fa-clock"></i> 
                        <?php echo formatarData($diagnostico['data_envio'], 'd/m/Y H:i'); ?>
                    </div>
                </div>
                
                <?php if ($diagnostico['descricao_problema']): ?>
                <div class="problema-descricao">
                    <strong><i class="fas fa-exclamation-triangle"></i> Problema Relatado:</strong><br>
                    <?php echo nl2br(htmlspecialchars($diagnostico['descricao_problema'])); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($diagnostico['fotos'])): ?>
                    <?php $fotos = json_decode($diagnostico['fotos'], true); ?>
                    <?php if (is_array($fotos) && count($fotos) > 0): ?>
                    <div class="fotos-diagnostico">
                        <strong><i class="fas fa-camera"></i> Fotos do Problema:</strong>
                        <div class="fotos-grid">
                            <?php foreach ($fotos as $foto): ?>
                                <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto do problema" class="foto-thumb" onclick="abrirFoto('<?php echo htmlspecialchars($foto); ?>')">
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span class="urgencia-badge urgencia-<?php echo $diagnostico['urgencia']; ?>">
                        <i class="fas fa-flag"></i> <?php echo ucfirst($diagnostico['urgencia']); ?>
                    </span>
                    
                    <div style="display: flex; gap: 10px;">
                        <button onclick="responderDiagnostico(<?php echo $diagnostico['id']; ?>)" 
                                class="btn btn-sm" style="background: #109349; padding: 8px 15px; font-size: 0.8rem;">
                            <i class="fas fa-reply"></i> Responder
                        </button>
                        <a href="tel:<?php echo $diagnostico['cliente_telefone']; ?>" 
                           class="btn btn-sm" style="background: #28a745; padding: 8px 15px; font-size: 0.8rem;">
                            <i class="fas fa-phone"></i> Ligar
                        </a>
                        <a href="mailto:<?php echo $diagnostico['cliente_email']; ?>" 
                           class="btn btn-sm" style="background: #17a2b8; padding: 8px 15px; font-size: 0.8rem;">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>
                
                <div class="contato-info">
                    <div class="contato-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo $diagnostico['cliente_telefone']; ?></span>
                    </div>
                    <div class="contato-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo $diagnostico['cliente_email']; ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>Nenhum diagnóstico encontrado</h3>
            <p>Você ainda não possui diagnósticos direcionados para você.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Foto -->
<div id="modalFoto" onclick="fecharFoto()">
    <span class="fechar-modal" onclick="fecharFoto()">&times;</span>
    <img id="imagemModal" src="" alt="Foto ampliada">
</div>

<!-- Modal de Resposta -->
<div id="modalResposta" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: white; margin: 5% auto; padding: 0; border-radius: 10px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <div style="background: #109349; color: white; padding: 20px; border-radius: 10px 10px 0 0;">
            <h3 style="margin: 0;"><i class="fas fa-reply"></i> Responder Diagnóstico</h3>
        </div>
        <div style="padding: 20px;">
            <form id="formResposta" action="processar-resposta-diagnostico.php" method="post">
                <input type="hidden" id="diagnostico_id" name="diagnostico_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Diagnóstico Inicial *</label>
                    <textarea name="diagnostico_inicial" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px; box-sizing: border-box;" 
                              placeholder="Descreva sua análise inicial do problema..."></textarea>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Peças Necessárias</label>
                    <textarea name="pecas_necessarias" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px; box-sizing: border-box;" 
                              placeholder="Liste as peças que podem ser necessárias (opcional)..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Estimativa de Tempo</label>
                        <select name="tempo_estimado" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Selecione...</option>
                            <option value="1-2 horas">1-2 horas</option>
                            <option value="meio-dia">Meio dia</option>
                            <option value="1 dia">1 dia</option>
                            <option value="2-3 dias">2-3 dias</option>
                            <option value="1 semana">1 semana</option>
                            <option value="mais-1-semana">Mais de 1 semana</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Estimativa de Custo</label>
                        <input type="text" name="custo_estimado" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" 
                               placeholder="Ex: R$ 150,00 - R$ 300,00">
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Prioridade do Reparo</label>
                    <select name="prioridade" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="baixa">Baixa - Pode aguardar</option>
                        <option value="media">Média - Recomendado em breve</option>
                        <option value="alta">Alta - Urgente</option>
                        <option value="critica">Crítica - Não usar o veículo</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Observações Adicionais</label>
                    <textarea name="observacoes" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px; box-sizing: border-box;" 
                              placeholder="Informações adicionais para o cliente..."></textarea>
                </div>
                
                <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                    <button type="button" onclick="fecharModalResposta()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="background: #109349; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-paper-plane"></i> Enviar Resposta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirFoto(src) {
    event.stopPropagation();
    document.getElementById('imagemModal').src = src;
    document.getElementById('modalFoto').style.display = 'block';
}

function fecharFoto() {
    document.getElementById('modalFoto').style.display = 'none';
}

function expandirDiagnostico(id) {
    console.log('Expandir diagnóstico ID:', id);
}

function responderDiagnostico(id) {
    document.getElementById('diagnostico_id').value = id;
    document.getElementById('modalResposta').style.display = 'block';
}

function fecharModalResposta() {
    document.getElementById('modalResposta').style.display = 'none';
    document.getElementById('formResposta').reset();
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('modalResposta');
    if (event.target == modal) {
        fecharModalResposta();
    }
}
</script>

<?php require_once 'footer.php'; ?>