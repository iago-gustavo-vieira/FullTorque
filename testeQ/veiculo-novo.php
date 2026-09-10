<?php
ob_start();
require_once 'header.php';

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
        $conexao = conectarBD();
        $usuario_id = (int)$_SESSION['usuario_id'];
        
        $stmt = $conexao->prepare("SELECT id FROM veiculos WHERE placa = ?");
        $stmt->bind_param("s", $placa);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            exibirAlerta('error', 'Esta placa já está cadastrada no sistema.');
        } else {
            $tem_usuario_id = colunaExiste($conexao, 'veiculos', 'usuario_id');
            $tem_cor = colunaExiste($conexao, 'veiculos', 'cor');
            $tem_quilometragem = colunaExiste($conexao, 'veiculos', 'quilometragem');
            $tem_observacoes = colunaExiste($conexao, 'veiculos', 'observacoes');
            
            // Montar query dinamicamente baseado nas colunas existentes
            $colunas = ['marca', 'modelo', 'placa', 'ano'];
            $valores = [$marca, $modelo, $placa, $ano];
            $tipos = 'sssi';
            
            if ($tem_usuario_id) {
                array_unshift($colunas, 'usuario_id');
                array_unshift($valores, $usuario_id);
                $tipos = 'i' . $tipos;
            }
            if ($tem_cor) {
                $colunas[] = 'cor';
                $valores[] = $cor;
                $tipos .= 's';
            }
            if ($tem_quilometragem) {
                $colunas[] = 'quilometragem';
                $valores[] = $quilometragem;
                $tipos .= 'i';
            }
            if ($tem_observacoes) {
                $colunas[] = 'observacoes';
                $valores[] = $observacoes;
                $tipos .= 's';
            }
            
            $sql = "INSERT INTO veiculos (" . implode(', ', $colunas) . ") VALUES (" . str_repeat('?,', count($colunas) - 1) . "?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param($tipos, ...$valores);
            
            try {
                if ($stmt->execute()) {
                    registrarLog('veiculo_cadastrado', "Veículo cadastrado: $marca $modelo ($placa)");
                    exibirAlerta('success', 'Veículo cadastrado com sucesso!');
                    header("Location: veiculos.php");
                    exit;
                } else {
                    exibirAlerta('error', 'Erro ao cadastrar o veículo.');
                }
            } catch (Exception $e) {
                exibirAlerta('error', 'Erro ao cadastrar: ' . $e->getMessage());
            }
        }
        $conexao->close();
    }
}
?>

<div class="dashboard-welcome">
    <div class="welcome-message">
        <h2><i class="fas fa-plus-circle"></i> Novo Veículo</h2>
        <p>Cadastre um novo veículo para ter acesso a todos os nossos serviços automotivos.</p>
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
                    <h2><i class="fas fa-clipboard-check"></i> Dados do Veículo</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i> <strong>Cadastro Rápido!</strong> Preencha os dados do seu veículo para começar a usar nossos serviços.
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="marca">Marca *</label>
                            <input type="text" id="marca" name="marca" class="form-control" required placeholder="Ex: Toyota, Honda, Volkswagen...">
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo">Modelo *</label>
                            <input type="text" id="modelo" name="modelo" class="form-control" required placeholder="Ex: Corolla, Civic, Golf...">
                        </div>
                        
                        <div class="form-group">
                            <label for="placa">Placa *</label>
                            <input type="text" id="placa" name="placa" class="form-control" required maxlength="8" placeholder="ABC-1234">
                            <small class="form-text text-muted">📝 Digite apenas letras e números, a formatação será automática.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="ano">Ano *</label>
                            <input type="number" id="ano" name="ano" class="form-control" required min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="Digite o ano (ex: <?php echo date('Y'); ?>)" step="1">
                            <small class="form-text text-muted">📅 Digite o ano de fabricação do veículo (entre 1900 e <?php echo date('Y') + 1; ?>)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="cor">Cor *</label>
                            <input type="text" id="cor" name="cor" class="form-control" required placeholder="Ex: Branco, Preto, Prata...">
                        </div>
                        
                        <div class="form-group">
                            <label for="quilometragem">Quilometragem (km)</label>
                            <input type="number" id="quilometragem" name="quilometragem" class="form-control" min="0" placeholder="Ex: 50000">
                            <small class="form-text text-muted">📊 Campo opcional - nos ajuda a oferecer melhor atendimento.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea id="observacoes" name="observacoes" class="form-control" rows="4" placeholder="Informações adicionais sobre o veículo, modificações, problemas conhecidos, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-plus-circle"></i> Cadastrar Veículo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card help-card">
                <div class="card-header">
                    <h3><i class="fas fa-question-circle"></i> Como Cadastrar?</h3>
                </div>
                <div class="card-body">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Dados Básicos</h4>
                            <p>Preencha marca, modelo, placa, ano e cor do seu veículo.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Quilometragem</h4>
                            <p>Informe a quilometragem atual (opcional, mas recomendado).</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Observações</h4>
                            <p>Adicione informações importantes sobre o veículo.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Finalizar</h4>
                            <p>Clique em "Cadastrar" e seu veículo estará pronto!</p>
                        </div>
                    </div>
                    
                    <div class="benefits">
                        <h4><i class="fas fa-check-circle"></i> Benefícios</h4>
                        <ul>
                            <li>✅ Agendamentos rápidos</li>
                            <li>✅ Histórico de serviços</li>
                            <li>✅ Diagnósticos gratuitos</li>
                            <li>✅ Atendimento personalizado</li>
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

// Validação de ano
document.getElementById('ano').addEventListener('input', function(e) {
    let value = parseInt(e.target.value);
    let min = 1900;
    let max = new Date().getFullYear() + 1;
    
    if (value < min) e.target.value = min;
    if (value > max) e.target.value = max;
});
</script>

<?php require_once 'footer.php'; ?>