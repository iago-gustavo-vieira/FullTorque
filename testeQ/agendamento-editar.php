<?php
require_once 'config.php';
verificarLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('danger', 'ID do agendamento inválido.');
    header("Location: agendamentos.php");
    exit;
}

$agendamento_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

$stmt = $conexao->prepare("
    SELECT a.*, v.marca, v.modelo, v.placa 
    FROM agendamentos a
    JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.id = ? AND a.usuario_id = ? AND a.status = 'agendado'
");
$stmt->bind_param("ii", $agendamento_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    exibirAlerta('danger', 'Agendamento não encontrado, não pertence ao usuário ou não pode ser editado.');
    header("Location: agendamentos.php");
    exit;
}

$agendamento = $resultado->fetch_assoc();

$stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ? ORDER BY marca, modelo");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$veiculos = $stmt->get_result();

$stmt = $conexao->prepare("SELECT * FROM servicos WHERE status = 'ativo' ORDER BY nome");
$stmt->execute();
$servicos = $stmt->get_result();

$stmt = $conexao->prepare("
    SELECT ai.servico_id, ai.quantidade, ai.preco
    FROM agendamento_itens ai
    WHERE ai.agendamento_id = ?
");
$stmt->bind_param("i", $agendamento_id);
$stmt->execute();
$servicos_selecionados_result = $stmt->get_result();

$servicos_selecionados = [];
while ($servico = $servicos_selecionados_result->fetch_assoc()) {
    $servicos_selecionados[$servico['servico_id']] = $servico;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $veiculo_id = (int)$_POST['veiculo_id'];
    $data = limparDados($_POST['data']);
    $hora = limparDados($_POST['hora']);
    $servicos_form = isset($_POST['servicos']) ? $_POST['servicos'] : [];
    $observacoes = !empty($_POST['observacoes']) ? limparDados($_POST['observacoes']) : null;
    
    if (empty($veiculo_id) || empty($data) || empty($hora) || empty($servicos_form)) {
        exibirAlerta('danger', 'Preencha todos os campos obrigatórios.');
    } else {
        $stmt = $conexao->prepare("SELECT id FROM veiculos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $veiculo_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            exibirAlerta('danger', 'Veículo inválido.');
        } else {
            $tempo_total = 0;
            $servicos_info = [];
            
            foreach ($servicos_form as $servico_id) {
                $stmt = $conexao->prepare("SELECT id, nome, preco, tempo_estimado FROM servicos WHERE id = ? AND status = 'ativo'");
                $stmt->bind_param("i", $servico_id);
                $stmt->execute();
                $servico = $stmt->get_result()->fetch_assoc();
                
                if ($servico) {
                    $tempo_total += $servico['tempo_estimado'];
                    $servicos_info[] = $servico;
                }
            }
            
            $hora_inicio = $hora;
            $hora_fim = date('H:i', strtotime($hora) + $tempo_total * 60);
            $data_agendamento = date('Y-m-d', strtotime($data));
            
            $stmt = $conexao->prepare("
                SELECT id FROM agendamentos 
                WHERE data_agendamento = ? 
                AND ((hora_inicio <= ? AND hora_fim > ?) OR (hora_inicio < ? AND hora_fim >= ?))
                AND status IN ('agendado', 'confirmado', 'em_andamento')
                AND id != ?
            ");
            $stmt->bind_param("sssssi", $data_agendamento, $hora_fim, $hora_inicio, $hora_fim, $hora_inicio, $agendamento_id);
            $stmt->execute();
            $conflito = $stmt->get_result();
            
            if ($conflito->num_rows > 0) {
                exibirAlerta('danger', 'Este horário não está disponível. Por favor, escolha outro horário.');
            } else {
                $stmt = $conexao->prepare("
                    UPDATE agendamentos 
                    SET veiculo_id = ?, data_agendamento = ?, hora_inicio = ?, hora_fim = ?, observacoes = ?
                    WHERE id = ? AND usuario_id = ? AND status = 'agendado'
                ");
                $stmt->bind_param("issssii", $veiculo_id, $data_agendamento, $hora_inicio, $hora_fim, $observacoes, $agendamento_id, $usuario_id);
                
                if ($stmt->execute()) {
                    $stmt = $conexao->prepare("DELETE FROM agendamento_itens WHERE agendamento_id = ?");
                    $stmt->bind_param("i", $agendamento_id);
                    $stmt->execute();
                    
                    foreach ($servicos_info as $servico) {
                        $stmt = $conexao->prepare("
                            INSERT INTO agendamento_itens (agendamento_id, servico_id, preco, quantidade) 
                            VALUES (?, ?, ?, 1)
                        ");
                        $stmt->bind_param("iid", $agendamento_id, $servico['id'], $servico['preco']);
                        $stmt->execute();
                    }
                    
                    exibirAlerta('success', 'Agendamento atualizado com sucesso!');
                    registrarLog('agendamento_atualizado', 'Agendamento ID: ' . $agendamento_id . ' atualizado');
                    header("Location: agendamentos.php");
                    exit;
                } else {
                    exibirAlerta('danger', 'Erro ao atualizar o agendamento: ' . $conexao->error);
                }
            }
        }
    }
}

$conexao->close();

require_once 'header.php';
?>
<style>
    body {
        background: #f5f5f5;
    }
    
    .edit-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .page-title {
        background: linear-gradient(135deg, #109349 0%, #0d7a3a 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(16, 147, 73, 0.3);
    }
    
    .page-title h1 {
        margin: 0;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        transition: all 0.3s;
    }
    
    .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.12);
    }
    
    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .card-title i {
        color: #109349;
        font-size: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }
    
    .form-group select, .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
        outline: none;
        border-color: #109349;
        box-shadow: 0 0 0 3px rgba(16, 147, 73, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .service-list {
        list-style: none;
        padding: 0;
        margin-bottom: 20px;
    }
    
    .service-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .service-item:hover {
        background: #e9f7ef;
        border-color: #109349;
        transform: translateX(5px);
    }
    
    .service-checkbox {
        margin-right: 15px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #109349;
    }
    
    .service-info {
        flex: 1;
    }
    
    .service-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
        font-size: 1rem;
    }
    
    .service-description {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 8px;
    }
    
    .service-meta {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .service-price {
        background: #109349;
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .service-time {
        font-size: 0.85rem;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .summary {
        background: linear-gradient(135deg, #109349, #0d7a3a);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }
    
    .summary-title {
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid rgba(255,255,255,0.3);
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        flex: 1;
        padding: 14px 24px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn-outline {
        background: white;
        color: #109349;
        border: 2px solid #109349;
    }
    
    .btn-outline:hover {
        background: #109349;
        color: white;
    }
    
    .btn:not(.btn-outline) {
        background: linear-gradient(135deg, #109349, #0d7a3a);
        color: white;
    }
    
    .info-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .page-title h1 {
            font-size: 1.4rem;
        }
    }
</style>

<div class="edit-container">
    <div class="page-title">
        <h1><i class="fas fa-edit"></i> Editar Agendamento #<?php echo $agendamento_id; ?></h1>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $agendamento_id); ?>" method="post">
        <div class="card">
            <div class="card-title">
                <i class="fas fa-car"></i> Veículo
            </div>
            <div class="form-group">
                <label for="veiculo_id">Selecione o veículo</label>
                <select id="veiculo_id" name="veiculo_id" required>
                    <option value="">Escolha um veículo</option>
                    <?php while ($veiculo = $veiculos->fetch_assoc()): ?>
                        <option value="<?php echo $veiculo['id']; ?>" <?php echo ($veiculo['id'] == $agendamento['veiculo_id']) ? 'selected' : ''; ?>>
                            <?php echo $veiculo['marca'] . ' ' . $veiculo['modelo'] . ' (' . $veiculo['placa'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">
                <i class="fas fa-calendar-alt"></i> Data e Horário
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="data">Data do agendamento</label>
                    <input type="date" id="data" name="data" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $agendamento['data_agendamento']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="hora">Horário</label>
                    <select id="hora" name="hora" required>
                        <option value="">Escolha o horário</option>
                        <?php
                        $inicio = strtotime('08:00');
                        $fim = strtotime('17:00');
                        $intervalo = 30 * 60;
                        
                        for ($i = $inicio; $i <= $fim; $i += $intervalo) {
                            $hora_formatada = date('H:i', $i);
                            $selected = (substr($agendamento['hora_inicio'], 0, 5) == $hora_formatada) ? 'selected' : '';
                            echo "<option value=\"$hora_formatada\" $selected>$hora_formatada</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">
                <i class="fas fa-tools"></i> Serviços
            </div>
            <div class="info-badge">
                <i class="fas fa-info-circle"></i>
                Selecione os serviços que deseja incluir no agendamento
            </div>
            <ul class="service-list">
                <?php 
                $servicos->data_seek(0);
                
                while ($servico = $servicos->fetch_assoc()): 
                    $checked = isset($servicos_selecionados[$servico['id']]) ? 'checked' : '';
                ?>
                    <li class="service-item">
                        <input type="checkbox" id="servico_<?php echo $servico['id']; ?>" name="servicos[]" value="<?php echo $servico['id']; ?>" class="service-checkbox" data-price="<?php echo $servico['preco']; ?>" data-time="<?php echo $servico['tempo_estimado']; ?>" <?php echo $checked; ?>>
                        <div class="service-info">
                            <div class="service-name"><?php echo $servico['nome']; ?></div>
                            <div class="service-description"><?php echo $servico['descricao']; ?></div>
                            <div class="service-meta">
                                <div class="service-price"><?php echo formatarMoeda($servico['preco']); ?></div>
                                <div class="service-time"><i class="far fa-clock"></i> <?php echo $servico['tempo_estimado']; ?> min</div>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
            
            <div class="summary">
                <div class="summary-title"><i class="fas fa-calculator"></i> Resumo do Agendamento</div>
                <div class="summary-item">
                    <span><i class="far fa-clock"></i> Tempo estimado:</span>
                    <span id="tempo-total">0 min</span>
                </div>
                <div class="summary-total">
                    <span><i class="fas fa-dollar-sign"></i> Valor Total:</span>
                    <span id="preco-total">R$ 0,00</span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">
                <i class="fas fa-comment"></i> Observações
            </div>
            <div class="form-group">
                <label for="observacoes">Informações adicionais (opcional)</label>
                <textarea id="observacoes" name="observacoes" rows="4" placeholder="Descreva detalhes importantes sobre o serviço..."><?php echo $agendamento['observacoes']; ?></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
            <a href="agendamentos.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </form>
</div>
<script>
// Atualiza o resumo quando os serviços são selecionados
document.querySelectorAll('.service-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSummary);
});

// Função para atualizar o resumo
function updateSummary() {
    let tempoTotal = 0;
    let precoTotal = 0;
    
    document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
        tempoTotal += parseInt(checkbox.dataset.time);
        precoTotal += parseFloat(checkbox.dataset.price);
    });
    
    document.getElementById('tempo-total').textContent = tempoTotal + ' min';
    document.getElementById('preco-total').textContent = 'R$ ' + precoTotal.toFixed(2).replace('.', ',');
}

// Executa a função ao carregar a página para mostrar os valores iniciais
window.addEventListener('load', updateSummary);
</script>

<?php require_once 'footer.php'; ?>