<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-agendamentos.php");
    exit;
}

$id = (int)$_GET['id'];
$conexao = conectarBD();

// Buscar detalhes do agendamento
$stmt = $conexao->prepare("
    SELECT a.*, 
           u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone,
           v.marca, v.modelo, v.placa, v.ano, v.cor,
           s.nome as servico_nome, s.descricao as servico_descricao, s.preco as servico_preco,
           m.nome as mecanico_nome, m.especialidade as mecanico_especialidade, 
           m.experiencia as mecanico_experiencia, m.foto as mecanico_foto
    FROM agendamentos a
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN veiculos v ON a.veiculo_id = v.id
    JOIN servicos s ON a.servico_id = s.id
    LEFT JOIN mecanicos m ON a.mecanico_id = m.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setMensagem("Agendamento não encontrado.", "erro");
    header("Location: admin-agendamentos.php");
    exit;
}

$agendamento = $result->fetch_assoc();
$stmt->close();

// Formatar data e hora
$data_hora = new DateTime($agendamento['data_hora']);
$data = $data_hora->format('d/m/Y');
$hora = $data_hora->format('H:i');

// Processar alteração de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $novo_status = limparDados($_POST['status']);
    $status_validos = ['pendente', 'confirmado', 'concluido', 'cancelado'];
    
    if (in_array($novo_status, $status_validos)) {
        $stmt = $conexao->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_status, $id);
        
        if ($stmt->execute()) {
            // Atualizar o status na variável
            $agendamento['status'] = $novo_status;
            
            // Registrar log
            registrarLog("Status do agendamento #$id alterado para '$novo_status'");
            
            setMensagem("Status atualizado com sucesso!", "sucesso");
        } else {
            setMensagem("Erro ao atualizar status: " . $conexao->error, "erro");
        }
        $stmt->close();
    }
}

// Processar alteração de mecânico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mecanico_id'])) {
    $mecanico_id = (int)$_POST['mecanico_id'];
    
    // Verificar se o mecânico existe
    if ($mecanico_id > 0) {
        $stmt = $conexao->prepare("SELECT COUNT(*) FROM mecanicos WHERE id = ?");
        $stmt->bind_param("i", $mecanico_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            setMensagem("Mecânico não encontrado.", "erro");
        } else {
            $stmt = $conexao->prepare("UPDATE agendamentos SET mecanico_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $mecanico_id, $id);
            
            if ($stmt->execute()) {
                // Buscar informações do novo mecânico
                $stmt = $conexao->prepare("SELECT nome, especialidade, experiencia, foto FROM mecanicos WHERE id = ?");
                $stmt->bind_param("i", $mecanico_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $mecanico = $result->fetch_assoc();
                $stmt->close();
                
                // Atualizar as informações na variável
                $agendamento['mecanico_id'] = $mecanico_id;
                $agendamento['mecanico_nome'] = $mecanico['nome'];
                $agendamento['mecanico_especialidade'] = $mecanico['especialidade'];
                $agendamento['mecanico_experiencia'] = $mecanico['experiencia'];
                $agendamento['mecanico_foto'] = $mecanico['foto'];
                
                // Registrar log
                registrarLog("Mecânico do agendamento #$id alterado para '{$mecanico['nome']}'");
                
                setMensagem("Mecânico atualizado com sucesso!", "sucesso");
            } else {
                setMensagem("Erro ao atualizar mecânico: " . $conexao->error, "erro");
            }
        }
    } else {
        // Remover mecânico
        $stmt = $conexao->prepare("UPDATE agendamentos SET mecanico_id = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Atualizar as informações na variável
            $agendamento['mecanico_id'] = null;
            $agendamento['mecanico_nome'] = null;
            $agendamento['mecanico_especialidade'] = null;
            $agendamento['mecanico_experiencia'] = null;
            $agendamento['mecanico_foto'] = null;
            
            // Registrar log
            registrarLog("Mecânico removido do agendamento #$id");
            
            setMensagem("Mecânico removido com sucesso!", "sucesso");
        } else {
            setMensagem("Erro ao remover mecânico: " . $conexao->error, "erro");
        }
        $stmt->close();
    }
}

// Buscar mecânicos disponíveis
$mecanicos = $conexao->query("SELECT * FROM mecanicos WHERE ativo = 1 ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

$conexao->close();

// Título da página
$titulo = "Detalhes do Agendamento";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --highlight-color: <?php echo COR_DESTAQUE; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
            --text-color: <?php echo COR_TEXTO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }
        
        .info-value {
            padding: 8px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pendente {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-confirmado {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-concluido {
            background-color: var(--primary-color);
            color: white;
        }
        
        .status-cancelado {
            background-color: var(--error-color);
            color: white;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #109349;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #109349;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title:first-child {
            margin-top: 0;
        }
        
        .section-content {
            margin-bottom: 20px;
        }
        
        .mecanico-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }
        
        .mecanico-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .mecanico-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .mecanico-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(var(--primary-color-rgb), 0.05);
        }
        
        .mecanico-foto {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px;
            border: 2px solid #ddd;
        }
        
        .mecanico-nome {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .mecanico-especialidade {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .mecanico-experiencia {
            font-size: 12px;
            color: #666;
        }
        
        .mecanico-atual {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        
        .mecanico-detalhes h3 {
            margin-bottom: 5px;
            color: var(--secondary-color);
        }
        
        .mecanico-detalhes p {
            color: #666;
            margin-bottom: 3px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .mobile-menu-toggle.active {
                left: 270px;
            }
            

            
            .mecanico-atual {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1><?php echo $titulo; ?></h1>
            <a href="admin-agendamentos.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
        
        <?php mostrarAlerta(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-check"></i> Detalhes do Agendamento</h2>
                <span class="status-badge status-<?php echo $agendamento['status']; ?>">
                    <?php echo ucfirst($agendamento['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <!-- Detalhes do Agendamento -->
                <div class="section-title"><i class="fas fa-calendar-check"></i> Detalhes do Agendamento</div>
                <div class="section-content">
                    <div class="info-group">
                        <div class="info-label">Código do Agendamento</div>
                        <div class="info-value">#<?php echo str_pad($agendamento['id'], 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Data e Hora</div>
                        <div class="info-value"><?php echo $data; ?> às <?php echo $hora; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Alterar Status</div>
                        <div class="info-value">
                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post" style="display: flex; gap: 10px;">
                                <button type="submit" name="status" value="pendente" class="btn btn-warning">Pendente</button>
                                <button type="submit" name="status" value="confirmado" class="btn btn-success">Confirmado</button>
                                <button type="submit" name="status" value="concluido" class="btn btn-primary">Concluído</button>
                                <button type="submit" name="status" value="cancelado" class="btn btn-danger">Cancelado</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Cliente -->
                <div class="section-title"><i class="fas fa-user"></i> Informações do Cliente</div>
                <div class="section-content">
                    <div class="info-group">
                        <div class="info-label">Nome</div>
                        <div class="info-value"><?php echo $agendamento['usuario_nome']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $agendamento['usuario_email']; ?></div>
                    </div>
                    <?php if ($agendamento['usuario_telefone']): ?>
                        <div class="info-group">
                            <div class="info-label">Telefone</div>
                            <div class="info-value"><?php echo $agendamento['usuario_telefone']; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Informações do Veículo -->
                <div class="section-title"><i class="fas fa-car"></i> Informações do Veículo</div>
                <div class="section-content">
                    <div class="info-group">
                        <div class="info-label">Veículo</div>
                        <div class="info-value"><?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Placa</div>
                        <div class="info-value"><?php echo $agendamento['placa']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Ano</div>
                        <div class="info-value"><?php echo $agendamento['ano']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Cor</div>
                        <div class="info-value"><?php echo $agendamento['cor']; ?></div>
                    </div>
                </div>
                
                <!-- Observações -->
                <?php if (!empty($agendamento['observacoes'])): ?>
                    <div class="section-title"><i class="fas fa-sticky-note"></i> Observações</div>
                    <div class="section-content">
                        <div class="info-group">
                            <div class="info-value"><?php echo nl2br($agendamento['observacoes']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Serviços Agendados -->
                <div class="section-title"><i class="fas fa-tools"></i> Serviços Agendados</div>
                <div class="section-content">
                    <div class="info-group">
                        <div class="info-label">Serviço</div>
                        <div class="info-value"><?php echo $agendamento['servico_nome']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Descrição</div>
                        <div class="info-value"><?php echo $agendamento['servico_descricao']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Valor</div>
                        <div class="info-value">R$ <?php echo number_format($agendamento['servico_preco'], 2, ',', '.'); ?></div>
                    </div>
                </div>
                
                <!-- Mecânico Responsável -->
                <div class="section-title"><i class="fas fa-user-cog"></i> Mecânico Responsável</div>
                <div class="section-content">
                    <?php if ($agendamento['mecanico_id']): ?>
                        <div class="info-group">
                            <div class="info-label">Mecânico Atual</div>
                            <div class="mecanico-atual">
                                <?php if ($agendamento['mecanico_foto']): ?>
                                    <img src="uploads/mecanicos/<?php echo $agendamento['mecanico_foto']; ?>" alt="Foto de <?php echo $agendamento['mecanico_nome']; ?>" class="mecanico-foto">
                                <?php else: ?>
                                    <div class="mecanico-foto" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="font-size: 24px; color: #888;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="mecanico-detalhes">
                                    <h3><?php echo $agendamento['mecanico_nome']; ?></h3>
                                    <p><i class="fas fa-tools"></i> <?php echo $agendamento['mecanico_especialidade']; ?></p>
                                    <p><i class="fas fa-star"></i> <?php echo $agendamento['mecanico_experiencia']; ?> anos de experiência</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="info-group">
                            <div class="info-value">Nenhum mecânico designado para este agendamento.</div>
                        </div>
                    <?php endif; ?>
                    <div class="info-group">
                        <div class="info-label">Alterar Mecânico</div>
                        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
                            <div class="mecanico-grid">
                                <?php if (empty($mecanicos)): ?>
                                    <p>Nenhum mecânico disponível no momento.</p>
                                <?php else: ?>
                                    <?php foreach ($mecanicos as $mecanico): ?>
                                        <div class="mecanico-card <?php echo $agendamento['mecanico_id'] == $mecanico['id'] ? 'selected' : ''; ?>" data-id="<?php echo $mecanico['id']; ?>" onclick="selecionarMecanico(this, <?php echo $mecanico['id']; ?>)">
                                            <?php if ($mecanico['foto']): ?>
                                                <img src="uploads/mecanicos/<?php echo $mecanico['foto']; ?>" alt="Foto de <?php echo $mecanico['nome']; ?>" class="mecanico-foto">
                                            <?php else: ?>
                                                <div class="mecanico-foto" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="font-size: 24px; color: #888;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="mecanico-nome"><?php echo $mecanico['nome']; ?></div>
                                            <div class="mecanico-especialidade"><?php echo $mecanico['especialidade']; ?></div>
                                            <div class="mecanico-experiencia"><?php echo $mecanico['experiencia']; ?> anos de experiência</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" id="mecanico_id" name="mecanico_id" value="<?php echo $agendamento['mecanico_id']; ?>">
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">Atualizar Mecânico</button>
                                <?php if ($agendamento['mecanico_id']): ?>
                                    <button type="button" class="btn btn-danger" onclick="removerMecanico()">Remover Mecânico</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Script para o menu mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    this.classList.toggle('active');
                });
            }
        });
        
        function selecionarMecanico(element, id) {
            // Remover seleção anterior
            document.querySelectorAll('.mecanico-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Adicionar seleção ao card clicado
            element.classList.add('selected');
            
            // Atualizar o valor do campo hidden
            document.getElementById('mecanico_id').value = id;
        }
        
        function removerMecanico() {
            // Remover seleção de todos os cards
            document.querySelectorAll('.mecanico-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Definir o valor do campo hidden como 0 (remover mecânico)
            document.getElementById('mecanico_id').value = 0;
            
            // Enviar o formulário
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>