<?php
ob_start();
require_once 'header.php';

// Verifica se o ID do veículo foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exibirAlerta('error', 'ID do veículo inválido.');
    header("Location: veiculos.php");
    exit;
}

$veiculo_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$conexao = conectarBD();

// Verifica se o veículo pertence ao usuário
$stmt = $conexao->prepare("SELECT * FROM veiculos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $veiculo_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    exibirAlerta('error', 'Veículo não encontrado ou não pertence ao usuário.');
    header("Location: veiculos.php");
    exit;
}

$veiculo = $resultado->fetch_assoc();

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $marca = limparDados($_POST['marca']);
    $modelo = limparDados($_POST['modelo']);
    $placa = limparDados($_POST['placa']);
    $ano = (int)$_POST['ano'];
    $cor = limparDados($_POST['cor']);
    $quilometragem = !empty($_POST['quilometragem']) ? (int)$_POST['quilometragem'] : null;
    $observacoes = !empty($_POST['observacoes']) ? limparDados($_POST['observacoes']) : null;
    
    if (empty($marca) || empty($modelo) || empty($placa) || empty($ano) || empty($cor)) {
        exibirAlerta('error', 'Preencha todos os campos obrigatórios.');
    } else {
        $stmt = $conexao->prepare("SELECT id FROM veiculos WHERE placa = ? AND id != ? AND usuario_id != ?");
        $stmt->bind_param("sii", $placa, $veiculo_id, $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            exibirAlerta('error', 'Esta placa já está cadastrada no sistema.');
        } else {
            // Montar UPDATE dinâmico verificando colunas existentes
            $campos = ['marca' => $marca, 'modelo' => $modelo, 'placa' => $placa, 'ano' => $ano];
            $tipos = 'sssi';
            $valores = [$marca, $modelo, $placa, $ano];
            
            if (colunaExiste($conexao, 'veiculos', 'cor')) {
                $campos['cor'] = $cor;
                $tipos .= 's';
                $valores[] = $cor;
            }
            if (colunaExiste($conexao, 'veiculos', 'quilometragem')) {
                $campos['quilometragem'] = $quilometragem;
                $tipos .= 'i';
                $valores[] = $quilometragem;
            }
            if (colunaExiste($conexao, 'veiculos', 'observacoes')) {
                $campos['observacoes'] = $observacoes;
                $tipos .= 's';
                $valores[] = $observacoes;
            }
            
            $set_clause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
            $sql = "UPDATE veiculos SET $set_clause WHERE id = ? AND usuario_id = ?";
            
            $tipos .= 'ii';
            $valores[] = $veiculo_id;
            $valores[] = $usuario_id;
            
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param($tipos, ...$valores);
            
            if ($stmt->execute()) {
                exibirAlerta('success', 'Veículo atualizado com sucesso!');
                registrarLog('veiculo_atualizado', 'Veículo ID: ' . $veiculo_id . ' atualizado');
                header("Location: veiculos.php");
                exit;
            } else {
                exibirAlerta('error', 'Erro ao atualizar o veículo: ' . $conexao->error);
            }
        }
    }
}

$conexao->close();
?>

<div class="dashboard-welcome">
    <div class="welcome-message">
        <h2><i class="fas fa-edit"></i> Editar Veículo</h2>
        <p>Atualize as informações do seu veículo para manter os dados sempre corretos.</p>
    </div>
    <div class="welcome-actions">
        <a href="veiculos.php" class="btn" data-tooltip="Ver meus veículos">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-car"></i> Informações do Veículo</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $veiculo_id); ?>" method="post">
                        <div class="form-group">
                            <label for="marca">Marca *</label>
                            <input type="text" id="marca" name="marca" class="form-control" value="<?php echo htmlspecialchars($veiculo['marca']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo">Modelo *</label>
                            <input type="text" id="modelo" name="modelo" class="form-control" value="<?php echo htmlspecialchars($veiculo['modelo']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="placa">Placa *</label>
                            <input type="text" id="placa" name="placa" class="form-control" value="<?php echo htmlspecialchars($veiculo['placa']); ?>" required maxlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label for="ano">Ano *</label>
                            <input type="number" id="ano" name="ano" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo $veiculo['ano']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cor">Cor *</label>
                            <input type="text" id="cor" name="cor" class="form-control" value="<?php echo htmlspecialchars($veiculo['cor']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quilometragem">Quilometragem (km)</label>
                            <input type="number" id="quilometragem" name="quilometragem" class="form-control" min="0" value="<?php echo $veiculo['quilometragem']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea id="observacoes" name="observacoes" class="form-control" rows="4" placeholder="Informações adicionais sobre o veículo..."><?php echo htmlspecialchars($veiculo['observacoes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card help-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Dicas</h3>
                </div>
                <div class="card-body">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Mantenha Atualizado</h4>
                            <p>Sempre mantenha as informações do seu veículo atualizadas.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Quilometragem</h4>
                            <p>Atualize a quilometragem regularmente para melhor controle.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Observações</h4>
                            <p>Use o campo observações para anotar informações importantes.</p>
                        </div>
                    </div>
                    
                    <div class="benefits">
                        <h4><i class="fas fa-check-circle"></i> Benefícios</h4>
                        <ul>
                            <li>✅ Histórico completo</li>
                            <li>✅ Melhor atendimento</li>
                            <li>✅ Controle de manutenção</li>
                            <li>✅ Dados sempre corretos</li>
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
    opacity: 0.8;
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
        flex-direction: column;
        gap: 20px;
        text-align: center;
        padding: 20px;
    }
    
    .welcome-actions {
        margin-top: 20px;
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
</style>

<script>
// Máscara para placa
document.getElementById('placa').addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    if (value.length <= 3) {
        e.target.value = value;
    } else if (value.length <= 7) {
        e.target.value = value.substring(0, 3) + '-' + value.substring(3);
    } else {
        e.target.value = value.substring(0, 3) + '-' + value.substring(3, 7);
    }
});
</script>

<?php require_once 'footer.php'; ?>