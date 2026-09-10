<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$conexao = conectarBD();

// Buscar clientes
$clientes = $conexao->query("SELECT id, nome, email FROM usuarios WHERE nivel_acesso = 'cliente' ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

// Buscar serviços
$servicos = $conexao->query("SELECT id, nome, preco FROM servicos ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

// Processar formulário
if ($_POST) {
    $usuario_id = (int)$_POST['usuario_id'];
    $veiculo_id = (int)$_POST['veiculo_id'];
    $data_agendamento = $_POST['data_agendamento'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];
    $observacoes = limparDados($_POST['observacoes']);
    
    $erros = [];
    
    if (empty($usuario_id)) $erros[] = "Cliente é obrigatório";
    if (empty($veiculo_id)) $erros[] = "Veículo é obrigatório";
    if (empty($data_agendamento)) $erros[] = "Data é obrigatória";
    if (empty($hora_inicio)) $erros[] = "Hora de início é obrigatória";
    if (empty($hora_fim)) $erros[] = "Hora de fim é obrigatória";
    
    if (empty($erros)) {
        $stmt = $conexao->prepare("INSERT INTO agendamentos (usuario_id, veiculo_id, data_agendamento, hora_inicio, hora_fim, observacoes, status, data_criacao) VALUES (?, ?, ?, ?, ?, ?, 'agendado', NOW())");
        $stmt->bind_param("iissss", $usuario_id, $veiculo_id, $data_agendamento, $hora_inicio, $hora_fim, $observacoes);
        
        if ($stmt->execute()) {
            registrarLog('agendamento_criado', "Novo agendamento criado para cliente ID: $usuario_id");
            exibirAlerta('success', 'Agendamento criado com sucesso!');
            header("Location: admin-agendamentos.php");
            exit;
        } else {
            exibirAlerta('error', 'Erro ao criar agendamento.');
        }
    } else {
        exibirAlerta('error', implode('<br>', $erros));
    }
}

$conexao->close();
$titulo = "Novo Agendamento";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="themes.css">
    <style>
        :root {
            --primary-color: #109349;
            --secondary-color: #0d7a3a;
            --tertiary-color: #f8f9fa;
            --highlight-color: #109349;
            --success-color: #109349;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --text-color: #333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.dark-mode {
            --tertiary-color: #1a1a1a;
            --text-color: #f5f5f5;
            --secondary-color: #1e1e1e;
            background-color: #1a1a1a;
            color: #f5f5f5;
        }
        
        body.dark-mode .card {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .card-header {
            background-color: #333;
            border-color: #444;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.2rem;
            color: #109349;
            font-weight: 600;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .card-header {
            background: #109349;
            color: white;
            padding: 20px 25px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.4rem;
            color: white;
            display: flex;
            align-items: center;
            font-weight: 600;
            margin: 0;
        }
        
        .card-header h2 i {
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #109349;
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0d7a3a;
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(221, 1, 0, 0.1);
            border-left: 4px solid #DD0100;
            color: #DD0100;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><i class="fas fa-calendar-plus"></i> Novo Agendamento</h1>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Dados do Agendamento</h2>
                </div>
                <div class="card-body">
                    <form method="POST" id="agendamentoForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="usuario_id">Cliente *</label>
                                <select id="usuario_id" name="usuario_id" class="form-control" required onchange="carregarVeiculos()">
                                    <option value="">Selecione um cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nome']; ?> (<?php echo $cliente['email']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="veiculo_id">Veículo *</label>
                                <select id="veiculo_id" name="veiculo_id" class="form-control" required disabled>
                                    <option value="">Selecione primeiro um cliente</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_agendamento">Data *</label>
                                <input type="date" id="data_agendamento" name="data_agendamento" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <!-- Campo vazio para manter layout -->
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="hora_inicio">Hora de Início *</label>
                                <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_fim">Hora de Fim *</label>
                                <input type="time" id="hora_fim" name="hora_fim" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea id="observacoes" name="observacoes" class="form-control" rows="4" placeholder="Observações adicionais sobre o agendamento..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="admin-agendamentos.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Criar Agendamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
        function carregarVeiculos() {
            const clienteId = document.getElementById('usuario_id').value;
            const veiculoSelect = document.getElementById('veiculo_id');
            
            if (!clienteId) {
                veiculoSelect.innerHTML = '<option value="">Selecione primeiro um cliente</option>';
                veiculoSelect.disabled = true;
                return;
            }
            
            veiculoSelect.innerHTML = '<option value="">Carregando...</option>';
            veiculoSelect.disabled = true;
            
            fetch('ajax/buscar-veiculos.php?cliente_id=' + clienteId)
                .then(response => response.json())
                .then(data => {
                    veiculoSelect.innerHTML = '<option value="">Selecione um veículo</option>';
                    
                    if (data.success && data.veiculos.length > 0) {
                        data.veiculos.forEach(veiculo => {
                            const option = document.createElement('option');
                            option.value = veiculo.id;
                            option.textContent = `${veiculo.marca} ${veiculo.modelo} (${veiculo.placa})`;
                            veiculoSelect.appendChild(option);
                        });
                        veiculoSelect.disabled = false;
                    } else {
                        veiculoSelect.innerHTML = '<option value="">Nenhum veículo encontrado</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    veiculoSelect.innerHTML = '<option value="">Erro ao carregar veículos</option>';
                });
        }
        
        // Validação de horários
        document.getElementById('hora_inicio').addEventListener('change', function() {
            const horaInicio = this.value;
            const horaFim = document.getElementById('hora_fim');
            
            if (horaInicio) {
                const [hora, minuto] = horaInicio.split(':');
                const novaHora = parseInt(hora) + 1;
                horaFim.min = `${novaHora.toString().padStart(2, '0')}:${minuto}`;
                
                if (horaFim.value && horaFim.value <= horaInicio) {
                    horaFim.value = `${novaHora.toString().padStart(2, '0')}:${minuto}`;
                }
            }
        });
        
        // Validação do formulário
        document.getElementById('agendamentoForm').addEventListener('submit', function(e) {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFim = document.getElementById('hora_fim').value;
            
            if (horaInicio && horaFim && horaFim <= horaInicio) {
                e.preventDefault();
                alert('A hora de fim deve ser posterior à hora de início.');
                return false;
            }
        });
    </script>
</body>
</html>
