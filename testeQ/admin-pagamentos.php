<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Processar ações
if ($_POST) {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'atualizar_status':
                $id = intval($_POST['id']);
                $status = limparDados($_POST['status']);
                
                $stmt = $conexao->prepare("UPDATE pagamentos SET status = ?, data_atualizacao = NOW() WHERE id = ?");
                $stmt->bind_param("si", $status, $id);
                
                if ($stmt->execute()) {
                    if ($status == 'aprovado') {
                        $stmt = $conexao->prepare("UPDATE pagamentos SET data_pagamento = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                    }
                    exibirAlerta('success', 'Status do pagamento atualizado!');
                } else {
                    exibirAlerta('error', 'Erro ao atualizar status.');
                }
                break;
                
            case 'criar_cobranca':
                $usuario_id = intval($_POST['usuario_id']);
                $valor = floatval($_POST['valor']);
                
                // Limitar valor máximo para evitar erro de overflow
                if ($valor > 999999.99) {
                    $valor = 999999.99;
                }
                
                $descricao = limparDados($_POST['descricao']);
                $data_vencimento = $_POST['data_vencimento'];
                
                // Gerar link de pagamento falso
                $link_pagamento = 'https://pagamento-fake.com/' . uniqid('pay_', true) . '?token=' . bin2hex(random_bytes(16));
                
                $stmt = $conexao->prepare("INSERT INTO pagamentos (usuario_id, valor, descricao, data_vencimento, metodo_pagamento, link_pagamento) VALUES (?, ?, ?, ?, 'pix', ?)");
                $stmt->bind_param("idsss", $usuario_id, $valor, $descricao, $data_vencimento, $link_pagamento);
                
                if ($stmt->execute()) {
                    $pagamento_id = $conexao->insert_id;
                    
                    // Criar notificação para o cliente
                    $titulo = 'Nova Cobrança Recebida';
                    $mensagem = "Você tem uma nova cobrança de R$ " . number_format($valor, 2, ',', '.') . ". Acesse a área de pagamentos para pagar.";
                    $stmt_notif = $conexao->prepare("INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, data_criacao) VALUES (?, ?, ?, 'cobranca', NOW())");
                    $stmt_notif->bind_param("iss", $usuario_id, $titulo, $mensagem);
                    $stmt_notif->execute();
                    
                    exibirAlerta('success', 'Cobrança criada e notificação enviada ao cliente!');
                } else {
                    exibirAlerta('error', 'Erro ao criar cobrança.');
                }
                break;
        }
    }
}

// Buscar pagamentos
$pagamentos = $conexao->query("
    SELECT p.*, u.nome as cliente_nome, u.email as cliente_email 
    FROM pagamentos p 
    LEFT JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.data_criacao DESC
");

// Buscar usuários para criar cobrança
$usuarios = $conexao->query("SELECT id, nome, email FROM usuarios WHERE nivel_acesso = 'cliente' ORDER BY nome");

// Estatísticas
$stats = [];
$stats['total_recebido'] = $conexao->query("SELECT SUM(valor) as total FROM pagamentos WHERE status = 'aprovado'")->fetch_assoc()['total'] ?? 0;
$stats['pendentes'] = $conexao->query("SELECT COUNT(*) as total FROM pagamentos WHERE status = 'pendente'")->fetch_assoc()['total'];
$stats['vencidos'] = $conexao->query("SELECT COUNT(*) as total FROM pagamentos WHERE status = 'pendente' AND data_vencimento < CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pagamentos - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --accent-color: <?php echo COR_DESTAQUE; ?>;
            --success-color: <?php echo COR_SUCESSO; ?>;
            --warning-color: <?php echo COR_ALERTA; ?>;
            --error-color: <?php echo COR_ERRO; ?>;
            --text-color: <?php echo COR_TEXTO; ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }
        
        body.theme-alemanha {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.2) 33%, rgba(221, 1, 0, 0.15) 33%, rgba(221, 1, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 66%, rgba(255, 206, 0, 0.15) 100%) !important;
            background-color: #1a1a1a !important;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, #109349 0%, #109349 33%, #FFFFFF 33%, #FFFFFF 66%, #DD0101 66%, #DD0101 100%) !important;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(16, 147, 73, 0.2);
            border: 2px solid #109349;
        }
        
        .theme-alemanha .page-header {
            background: linear-gradient(135deg, #000000 0%, #000000 33%, #DD0100 33%, #DD0100 66%, #FFCE00 66%, #FFCE00 100%) !important;
            border: 2px solid #FFCE00;
        }
        
        .page-header > h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
        }
        
        .page-header > p {
            opacity: 1;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
            font-weight: 600;
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-info h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-subtitle {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        

        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 8px rgba(255, 206, 0, 0.3);
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
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .theme-alemanha .card-body {
            background: #000000;
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
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .theme-alemanha .stat-card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 8px rgba(255, 206, 0, 0.3);
        }
        
        .stat-card-number {
            font-size: 2rem;
            font-weight: 600;
            color: #109349;
            margin-bottom: 6px;
        }
        
        .theme-alemanha .stat-card-number {
            color: #FFCE00;
        }
        
        .stat-card-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        .theme-alemanha .stat-card-label {
            color: #ffffff;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .theme-alemanha .form-control {
            background: #1a1a1a;
            border: 2px solid #DD0100;
            color: #ffffff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #109349;
            box-shadow: 0 0 0 3px rgba(16, 147, 73, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
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
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background: #109349;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d7a3a;
            color: white;
            text-decoration: none;
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .theme-alemanha table {
            background: #000000;
        }
        
        th, td {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .theme-alemanha th {
            background: #1a1a1a;
            color: #FFCE00;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .theme-alemanha tr:hover {
            background: #1a1a1a;
        }
        
        .theme-alemanha td {
            color: #ffffff;
            border-bottom-color: #333;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-aprovado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-recusado, .status-cancelado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .theme-alemanha .status-aprovado {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
        }
        
        .theme-alemanha .status-pendente {
            background: rgba(221, 1, 0, 0.2);
            color: #DD0100;
        }
        
        .theme-alemanha .status-recusado,
        .theme-alemanha .status-cancelado {
            background: rgba(221, 1, 0, 0.3);
            color: #DD0100;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .page-header {
                background-image: url('bem-vindo-italia-responsivo.jpg') !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                padding: 15px 12px;
                border-radius: 10px;
                margin-bottom: 15px;
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: flex-end;
                text-align: right;
                position: relative;
            }
            
            .theme-alemanha .page-header {
                background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
                background-position: center !important;
            }
            
            .page-header > h1,
            .page-header > p {
                display: none !important;
            }
            
            .mobile-welcome-text {
                display: flex;
                position: absolute;
                top: 35px;
                left: 50%;
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
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-info h1 {
                font-size: 1.5rem;
            }
            
            .header-subtitle {
                font-size: 0.9rem;
            }
            

            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }
            
            .card-header h3 {
                font-size: 1.1rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 800px;
                font-size: 13px;
            }
            
            th, td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            
            .status-badge {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                padding: 15px 10px;
            }
            
            .header-info h1 {
                font-size: 1.2rem;
            }
            

            
            .card-header h3 {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 10px;
                font-size: 13px;
            }
            
            table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<?php require_once 'admin-menu.php'; ?>

<div class="content">
    <?php mostrarAlerta(); ?>
    
    <div class="container-fluid">
        <!-- Cabeçalho da Página -->
        <div class="page-header">
            <div class="mobile-welcome-text">Gerenciar Pagamentos</div>
            <h1><i class="fas fa-credit-card"></i> Gerenciar Pagamentos</h1>
            <p>Controle e gerencie todos os pagamentos da oficina</p>
            <div class="header-content" style="display: none;">
                <div class="header-info">
                    <h1><i class="fas fa-credit-card"></i> Gerenciar Pagamentos</h1>
                    <p class="header-subtitle">Controle e gerencie todos os pagamentos da oficina</p>
                </div>

            </div>
        </div>

        <!-- Estatísticas Detalhadas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-number">R$ <?php echo number_format($stats['total_recebido'], 2, ',', '.'); ?></div>
                <div class="stat-card-label">Total Recebido</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-number"><?php echo $stats['pendentes']; ?></div>
                <div class="stat-card-label">Pagamentos Pendentes</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #dc3545;">
                <div class="stat-card-number" style="color: #dc3545;"><?php echo $stats['vencidos']; ?></div>
                <div class="stat-card-label">Pagamentos Vencidos</div>
            </div>
        </div>

        <!-- Criar Nova Cobrança -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Criar Nova Cobrança</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="acao" value="criar_cobranca">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_id"><i class="fas fa-user"></i> Cliente</label>
                            <select name="usuario_id" id="usuario_id" class="form-control" required>
                                <option value="">Selecione um cliente...</option>
                                <?php 
                                $usuarios->data_seek(0); // Reset pointer
                                while ($usuario = $usuarios->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="valor"><i class="fas fa-dollar-sign"></i> Valor (R$)</label>
                            <input type="number" step="0.01" name="valor" id="valor" class="form-control" placeholder="0,00" required>
                        </div>
                        <div class="form-group">
                            <label for="data_vencimento"><i class="fas fa-calendar"></i> Data de Vencimento</label>
                            <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Descrição do serviço ou produto..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Cobrança
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de Pagamentos -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Pagamentos Cadastrados</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Método</th>
                                <th>Status</th>
                                <th>Vencimento</th>
                                <th>Link</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pagamentos->data_seek(0); // Reset pointer
                            if ($pagamentos->num_rows == 0): 
                            ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-credit-card" style="font-size: 3rem; color: #ddd; margin-bottom: 15px; display: block;"></i>
                                        <h3 style="color: #666; margin-bottom: 10px;">Nenhum pagamento encontrado</h3>
                                        <p style="color: #999;">Crie sua primeira cobrança usando o formulário acima.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($pagamento = $pagamentos->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pagamento['cliente_nome']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($pagamento['cliente_email']); ?></small>
                                    </td>
                                    <td><strong>R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></strong></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $pagamento['metodo_pagamento'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $pagamento['status']; ?>">
                                            <?php echo ucfirst($pagamento['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($pagamento['data_vencimento']) {
                                            echo date('d/m/Y', strtotime($pagamento['data_vencimento']));
                                            if ($pagamento['status'] == 'pendente' && $pagamento['data_vencimento'] < date('Y-m-d')) {
                                                echo ' <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-left: 5px;" title="Vencido"></i>';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($pagamento['link_pagamento']): ?>
                                            <a href="<?php echo $pagamento['link_pagamento']; ?>" target="_blank" style="color: #109349; text-decoration: none;" title="Abrir link de pagamento">
                                                <i class="fas fa-external-link-alt"></i> Abrir
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="acao" value="atualizar_status">
                                            <input type="hidden" name="id" value="<?php echo $pagamento['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 8px; font-size: 12px;">
                                                <option value="pendente" <?php echo $pagamento['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                                <option value="aprovado" <?php echo $pagamento['status'] == 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                                <option value="recusado" <?php echo $pagamento['status'] == 'recusado' ? 'selected' : ''; ?>>Recusado</option>
                                                <option value="cancelado" <?php echo $pagamento['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar tema salvo
    const savedTheme = localStorage.getItem('theme') || 'default';
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
    }
    
    // Formatação automática do valor
    const valorInput = document.getElementById('valor');
    if (valorInput) {
        valorInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9.,]/g, '');
            e.target.value = value;
        });
    }
    
    // Definir data mínima como hoje
    const dataVencimentoInput = document.getElementById('data_vencimento');
    if (dataVencimentoInput) {
        const today = new Date().toISOString().split('T')[0];
        dataVencimentoInput.min = today;
    }
    
    // Confirmação antes de alterar status
    const statusSelects = document.querySelectorAll('select[name="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function(e) {
            const newStatus = e.target.value;
            const currentStatus = e.target.querySelector('option[selected]')?.value || 'pendente';
            
            if (newStatus !== currentStatus) {
                const confirmMessage = `Tem certeza que deseja alterar o status para "${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}"?`;
                if (!confirm(confirmMessage)) {
                    e.target.value = currentStatus;
                    return false;
                }
            }
        });
    });
});
</script>
</body>
</html>
