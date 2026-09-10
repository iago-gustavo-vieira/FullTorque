<?php
require_once 'config.php';
verificarLogin();

if (!isset($_GET['id'])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'ID do diagnóstico não informado.'];
    header("Location: relatorios.php");
    exit;
}

$relatorio_id = (int)$_GET['id'];
$conexao = conectarBD();

// Buscar o relatório
$stmt = $conexao->prepare("SELECT * FROM relatorios_cliente WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $relatorio_id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensagem' => 'Diagnóstico não encontrado.'];
    header("Location: relatorios.php");
    exit;
}

$relatorio = $result->fetch_assoc();

// Verificar se pode editar (apenas pendentes)
if ($relatorio['status'] !== 'pendente') {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensagem' => 'Este diagnóstico não pode mais ser editado.'];
    header("Location: relatorios.php");
    exit;
}

// Buscar mecânicos e veículos
$mecanicos = $conexao->query("SELECT * FROM mecanicos WHERE ativo = 1 ORDER BY nome");
$stmt = $conexao->prepare("SELECT * FROM veiculos WHERE usuario_id = ? ORDER BY marca, modelo");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$veiculos = $stmt->get_result();
$stmt->close();
$conexao->close();

require_once 'header.php';
?>

<div class="dashboard-welcome">
    <div class="welcome-message">
        <h2><i class="fas fa-edit"></i> Editar Diagnóstico</h2>
        <p>Modifique as informações do seu diagnóstico antes da análise.</p>
    </div>
    <div class="welcome-actions">
        <a href="relatorios.php" class="btn" data-tooltip="Voltar aos relatórios">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-check"></i> Editar Solicitação</h2>
                </div>
                <div class="card-body">
                    <form action="processar-edicao-diagnostico.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="relatorio_id" value="<?php echo $relatorio['id']; ?>">
                        
                        <div class="form-group">
                            <label for="veiculo_id">Selecione o Veículo *</label>
                            <select name="veiculo_id" id="veiculo_id" class="form-control" required>
                                <option value="">Escolha um veículo</option>
                                <?php while ($veiculo = $veiculos->fetch_assoc()): ?>
                                    <option value="<?php echo $veiculo['id']; ?>" <?php echo $veiculo['id'] == $relatorio['veiculo_id'] ? 'selected' : ''; ?>>
                                        <?php echo $veiculo['marca'] . ' ' . $veiculo['modelo'] . ' (' . $veiculo['placa'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mecanico_id">Selecione o Mecânico *</label>
                            <select name="mecanico_id" id="mecanico_id" class="form-control" required>
                                <option value="">Escolha um mecânico</option>
                                <?php while ($mecanico = $mecanicos->fetch_assoc()): ?>
                                    <option value="<?php echo $mecanico['id']; ?>" <?php echo $mecanico['id'] == $relatorio['mecanico_id'] ? 'selected' : ''; ?>>
                                        <?php echo $mecanico['nome']; ?> - <?php echo $mecanico['especialidade']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao_problema">Descreva o Problema (Opcional)</label>
                            <textarea name="descricao_problema" id="descricao_problema" class="form-control" rows="4" 
                                      placeholder="Se souber, descreva o problema do seu veículo."><?php echo htmlspecialchars($relatorio['descricao_problema']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="urgencia">Nível de Urgência</label>
                            <select name="urgencia" id="urgencia" class="form-control">
                                <option value="baixa" <?php echo $relatorio['urgencia'] == 'baixa' ? 'selected' : ''; ?>>Baixa - Posso aguardar</option>
                                <option value="media" <?php echo $relatorio['urgencia'] == 'media' ? 'selected' : ''; ?>>Média - Preciso resolver em breve</option>
                                <option value="alta" <?php echo $relatorio['urgencia'] == 'alta' ? 'selected' : ''; ?>>Alta - Preciso resolver urgentemente</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" style="margin-right: 10px;">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="relatorios.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informações</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Atenção:</strong> Você só pode editar diagnósticos que ainda não foram analisados.
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                        <h5><i class="fas fa-calendar"></i> Data de Criação</h5>
                        <p><?php echo formatarData($relatorio['data_envio']); ?></p>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px;">
                        <h5><i class="fas fa-lightbulb"></i> Dica</h5>
                        <p>Seja o mais específico possível na descrição do problema para um diagnóstico mais preciso.</p>
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

.welcome-message h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
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

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #CE2A37, #a91e2a);
    color: white;
    padding: 20px;
}

.card-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-success {
    background: #109349;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}
</style>

<?php require_once 'footer.php'; ?>