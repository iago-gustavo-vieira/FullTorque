<?php
require_once 'config.php';
verificarLogin();

// Verificar se é analista
if (!isset($_SESSION['analista_id']) || $_SESSION['analista_id'] <= 0) {
    exibirAlerta('danger', 'Acesso negado. Apenas analistas podem acessar esta página.');
    header('Location: index.php');
    exit;
}

$conexao = conectarBD();

// Buscar relatórios APENAS para este analista específico
$stmt = $conexao->prepare("
    SELECT rc.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone,
           v.marca, v.modelo, v.placa, v.ano, v.cor, v.quilometragem,
           rm.diagnostico, rm.status as resposta_status
    FROM relatorios_cliente rc
    JOIN usuarios u ON rc.usuario_id = u.id
    JOIN veiculos v ON rc.veiculo_id = v.id
    LEFT JOIN relatorios_mecanico rm ON rc.id = rm.relatorio_cliente_id
    WHERE rc.analista_id = ? AND rc.analista_id IS NOT NULL
    ORDER BY rc.data_envio DESC
");
$stmt->bind_param("i", $_SESSION['analista_id']);
$stmt->execute();
$relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conexao->close();

$titulo = "Dashboard do Analista";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: var(--secondary-color);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .relatorio-item {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .relatorio-header {
            background: #f8f9fa;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .relatorio-body {
            padding: 15px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pendente { background-color: #ffeaa7; color: #d63031; }
        .status-analisado { background-color: #74b9ff; color: white; }
        .status-respondido { background-color: #00b894; color: white; }
        
        .urgencia-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .urgencia-baixa { background-color: #d4edda; color: #155724; }
        .urgencia-media { background-color: #fff3cd; color: #856404; }
        .urgencia-alta { background-color: #f8d7da; color: #721c24; }
        
        .cliente-info, .veiculo-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-success { background-color: var(--success-color); color: white; }
        .btn-warning { background-color: var(--warning-color); color: white; }
        .btn-danger { background-color: var(--error-color); color: white; }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        
        .logout-btn {
            background: var(--error-color);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-stethoscope"></i> <?php echo SISTEMA_NOME; ?> - Analista
            </div>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?php echo $_SESSION['usuario_nome']; ?></span>
                <a href="analista-perfil.php" style="color: white; text-decoration: none; margin-right: 15px;">
                    <i class="fas fa-user-cog"></i> Perfil
                </a>
                <a href="analista-configuracoes.php" style="color: white; text-decoration: none; margin-right: 15px;">
                    <i class="fas fa-cog"></i> Configurações
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="dashboard-header">
            <h1><?php echo $titulo; ?></h1>
        </div>
        
        <?php mostrarAlerta(); ?>
        
        <div class="stats">
            <div class="stat-card">
                <i class="fas fa-clipboard-list" style="color: var(--primary-color);"></i>
                <h3><?php echo count($relatorios); ?></h3>
                <p>Total de Relatórios</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock" style="color: var(--warning-color);"></i>
                <h3><?php echo count(array_filter($relatorios, function($r) { return $r['status'] == 'pendente'; })); ?></h3>
                <p>Pendentes</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                <h3><?php echo count(array_filter($relatorios, function($r) { return $r['diagnostico']; })); ?></h3>
                <p>Com Diagnóstico</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-thumbs-up" style="color: var(--success-color);"></i>
                <h3><?php echo count(array_filter($relatorios, function($r) { return $r['status'] == 'aceito'; })); ?></h3>
                <p>Aceitos</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-trophy" style="color: #ffd700;"></i>
                <h3><?php echo count(array_filter($relatorios, function($r) { return $r['status'] == 'realizado'; })); ?></h3>
                <p>Realizados</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Relatórios dos Clientes
            </div>
            <div class="card-body">
                <?php if (empty($relatorios)): ?>
                    <p>Nenhum relatório de cliente encontrado.</p>
                <?php else: ?>
                    <?php foreach ($relatorios as $relatorio): ?>
                        <div class="relatorio-item">
                            <div class="relatorio-header">
                                <div>
                                    <strong>Relatório #<?php echo $relatorio['id']; ?></strong>
                                    <span class="status-badge status-<?php echo $relatorio['status']; ?>">
                                        <?php 
                                        switch($relatorio['status']) {
                                            case 'pendente': echo 'Pendente'; break;
                                            case 'analisado': echo 'Analisado'; break;
                                            case 'respondido': echo 'Respondido'; break;
                                        }
                                        ?>
                                    </span>
                                    <?php if ($relatorio['urgencia']): ?>
                                        <span class="urgencia-badge urgencia-<?php echo $relatorio['urgencia']; ?>">
                                            <?php echo ucfirst($relatorio['urgencia']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <br>
                                    <small>Recebido em: <?php echo formatarData($relatorio['data_envio']); ?></small>
                                </div>
                            </div>
                            <div class="relatorio-body">
                                <div class="cliente-info">
                                    <h4><i class="fas fa-user"></i> Cliente</h4>
                                    <p><strong>Nome:</strong> <?php echo $relatorio['cliente_nome']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $relatorio['cliente_email']; ?></p>
                                    <p><strong>Telefone:</strong> <?php echo $relatorio['cliente_telefone']; ?></p>
                                </div>
                                
                                <div class="veiculo-info">
                                    <h4><i class="fas fa-car"></i> Veículo</h4>
                                    <p><strong>Veículo:</strong> <?php echo $relatorio['marca'] . ' ' . $relatorio['modelo'] . ' ' . $relatorio['ano']; ?></p>
                                    <p><strong>Placa:</strong> <?php echo $relatorio['placa']; ?></p>
                                    <p><strong>Quilometragem:</strong> <?php echo number_format($relatorio['quilometragem']); ?> km</p>
                                </div>
                                
                                <?php if ($relatorio['descricao_problema']): ?>
                                    <div style="margin: 15px 0;">
                                        <h4><i class="fas fa-exclamation-triangle"></i> Problema Relatado</h4>
                                        <p><?php echo $relatorio['descricao_problema']; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($relatorio['diagnostico']): ?>
                                    <div style="margin: 15px 0; padding: 15px; background: #e8f5e8; border-radius: 5px;">
                                        <h4><i class="fas fa-clipboard-check"></i> Diagnóstico Enviado</h4>
                                        <p><?php echo $relatorio['diagnostico']; ?></p>
                                        
                                        <?php if ($relatorio['status'] == 'aceito'): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #d4edda; border-radius: 5px;">
                                                <strong style="color: var(--success-color);"><i class="fas fa-thumbs-up"></i> Cliente aceitou o serviço!</strong>
                                                <p style="margin: 5px 0 0 0; font-size: 14px;">Entre em contato para agendar o atendimento.</p>
                                            </div>
                                        <?php elseif ($relatorio['status'] == 'rejeitado'): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                                                <strong style="color: var(--error-color);"><i class="fas fa-thumbs-down"></i> Cliente não tem interesse</strong>
                                                <p style="margin: 5px 0 0 0; font-size: 14px;">Diagnóstico concluído.</p>
                                            </div>
                                        <?php elseif ($relatorio['status'] == 'solicitou_mudanca'): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px;">
                                                <strong style="color: var(--warning-color);"><i class="fas fa-clock"></i> Cliente solicita mudança de horário</strong>
                                                <p style="margin: 5px 0 10px 0; font-size: 14px;">Cliente quer prosseguir mas prefere outro horário.</p>
                                                
                                                <div style="margin-top: 10px;">
                                                    <button onclick="abrirModalNovaData(<?php echo $relatorio['id']; ?>)" class="btn btn-success" style="margin-right: 10px;">
                                                        <i class="fas fa-calendar"></i> Propor Nova Data
                                                    </button>
                                                    <button onclick="rejeitarSolicitacao(<?php echo $relatorio['id']; ?>)" class="btn btn-danger">
                                                        <i class="fas fa-times"></i> Recusar Solicitação
                                                    </button>
                                                </div>
                                            </div>
                                        <?php elseif ($relatorio['status'] == 'realizado'): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #d1ecf1; border-radius: 5px; border: 2px solid #ffd700;">
                                                <strong style="color: #ffd700;"><i class="fas fa-trophy"></i> Serviço realizado com sucesso!</strong>
                                                <p style="margin: 5px 0 0 0; font-size: 14px;">Cliente confirmou a conclusão do serviço.</p>
                                            </div>
                                        <?php else: ?>
                                            <small style="color: #666;"><i class="fas fa-hourglass-half"></i> Aguardando resposta do cliente</small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="margin-top: 15px;">
                                        <button onclick="abrirModalDiagnostico(<?php echo $relatorio['id']; ?>)" class="btn btn-primary">
                                            <i class="fas fa-stethoscope"></i> Fazer Diagnóstico
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

    <!-- Modal para diagnóstico -->
    <div id="modalDiagnostico" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 2% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h3>Fazer Diagnóstico e Propor Horário</h3>
            <form id="formDiagnostico" action="processar-diagnostico-analista.php" method="post">
                <input type="hidden" id="relatorio_id" name="relatorio_id" value="">
                
                <div style="margin: 15px 0;">
                    <label>Diagnóstico *</label>
                    <textarea name="diagnostico" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 120px;" placeholder="Descreva o diagnóstico detalhado do veículo..."></textarea>
                </div>
                
                <div style="margin: 15px 0;">
                    <label>Data e Hora Proposta para Atendimento *</label>
                    <input type="datetime-local" name="data_proposta" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin: 15px 0;">
                    <label>Observações sobre o Atendimento</label>
                    <textarea name="observacoes" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 80px;" placeholder="Observações adicionais sobre o atendimento..."></textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="fecharModalDiagnostico()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">Enviar Diagnóstico e Proposta</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModalDiagnostico(relatorioId) {
            document.getElementById('relatorio_id').value = relatorioId;
            document.getElementById('modalDiagnostico').style.display = 'block';
        }
        
        function fecharModalDiagnostico() {
            document.getElementById('modalDiagnostico').style.display = 'none';
        }
        
        function abrirModalNovaData(relatorioId) {
            const novaData = prompt('Digite a nova data e horário (formato: AAAA-MM-DD HH:MM):');
            if (novaData) {
                window.location.href = 'processar-resposta-analista.php?acao=aceitar_mudanca&id=' + relatorioId + '&nova_data=' + encodeURIComponent(novaData);
            }
        }
        
        function rejeitarSolicitacao(relatorioId) {
            if (confirm('Tem certeza que deseja recusar a solicitação de mudança?')) {
                window.location.href = 'processar-resposta-analista.php?acao=recusar_mudanca&id=' + relatorioId;
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modalDiagnostico');
            if (event.target == modal) {
                fecharModalDiagnostico();
            }
        }
    </script>
</body>
</html>