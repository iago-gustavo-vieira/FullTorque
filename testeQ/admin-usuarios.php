<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Processar exclusão de usuário
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    // Não permitir excluir o próprio usuário admin
    if ($id == $_SESSION['usuario_id']) {
        exibirAlerta('error', 'Você não pode excluir seu próprio usuário.');
    } else {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ? AND id != 1");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            try {
                registrarLog('usuario_excluido', "Usuário ID: $id excluído");
            } catch (Exception $e) {
                // Log error but continue
                if (DEBUG_MODE) error_log("Erro ao registrar log: " . $e->getMessage());
            }
            exibirAlerta('success', 'Usuário excluído com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível excluir o usuário.');
        }
        
        $conexao->close();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-usuarios.php");
    exit;
}

// Processar alteração de status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['ativo', 'inativo', 'bloqueado'])) {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("UPDATE usuarios SET status = ? WHERE id = ? AND id != 1");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            try {
                registrarLog('usuario_status', "Status do usuário ID: $id alterado para $status");
            } catch (Exception $e) {
                // Log error but continue
                if (DEBUG_MODE) error_log("Erro ao registrar log: " . $e->getMessage());
            }
            exibirAlerta('success', 'Status do usuário alterado com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível alterar o status do usuário.');
        }
        
        $conexao->close();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-usuarios.php");
    exit;
}

// Processar alteração de nível
if (isset($_GET['nivel']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $nivel = $_GET['nivel'];
    
    if (in_array($nivel, ['admin', 'gerente', 'funcionario', 'cliente'])) {
        $conexao = conectarBD();
        $stmt = $conexao->prepare("UPDATE usuarios SET nivel_acesso = ? WHERE id = ? AND id != 1");
        $stmt->bind_param("si", $nivel, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Atualizar permissões
            $stmt = $conexao->prepare("DELETE FROM usuario_permissoes WHERE usuario_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Inserir nova permissão base
            $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $nivel);
            $stmt->execute();
            
            // Adicionar permissões extras para admin e gerente
            if ($nivel == 'admin' || $nivel == 'gerente') {
                $permissoes = ['gerenciar_usuarios', 'gerenciar_servicos', 'gerenciar_agendamentos'];
                
                if ($nivel == 'admin') {
                    $permissoes[] = 'gerenciar_ordens';
                }
                
                foreach ($permissoes as $permissao) {
                    $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                    $stmt->bind_param("is", $id, $permissao);
                    $stmt->execute();
                }
            }
            
            try {
                registrarLog('usuario_nivel', "Nível do usuário ID: $id alterado para $nivel");
            } catch (Exception $e) {
                // Log error but continue
                if (DEBUG_MODE) error_log("Erro ao registrar log: " . $e->getMessage());
            }
            exibirAlerta('success', 'Nível do usuário alterado com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível alterar o nível do usuário.');
        }
        
        $conexao->close();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-usuarios.php");
    exit;
}

// Buscar todos os usuários
$conexao = conectarBD();
try {
    $result = $conexao->query("SELECT * FROM usuarios ORDER BY nome");
    $usuarios = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    $usuarios = [];
    exibirAlerta('error', 'Erro ao carregar usuários: ' . $e->getMessage());
}
$conexao->close();

// Título da página
$titulo_pagina = "Gerenciar Usuários";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin-responsive.css">
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
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            color: #212529;
            text-decoration: none;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
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
        
        .status-ativo {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inativo {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .status-bloqueado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .theme-alemanha .status-ativo {
            background: rgba(255, 206, 0, 0.2);
            color: #FFCE00;
        }
        
        .theme-alemanha .status-inativo {
            background: rgba(221, 1, 0, 0.2);
            color: #DD0100;
        }
        
        .theme-alemanha .status-bloqueado {
            background: rgba(221, 1, 0, 0.3);
            color: #DD0100;
        }
        
        .nivel-admin {
            background: #f8d7da;
            color: #721c24;
        }
        
        .nivel-gerente {
            background: #fff3cd;
            color: #856404;
        }
        
        .nivel-funcionario {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .nivel-cliente {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 180px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            right: 0;
        }
        
        .theme-alemanha .dropdown-content {
            background-color: #1a1a1a;
            border: 1px solid #DD0100;
        }
        
        .dropdown-content a {
            color: var(--text-color);
            padding: 10px 14px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .theme-alemanha .dropdown-content a {
            color: #ffffff;
        }
        
        .dropdown-content a:hover {
            background-color: #f8f9fa;
        }
        
        .theme-alemanha .dropdown-content a:hover {
            background-color: #DD0100;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
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
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 900px;
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
            
            .actions {
                flex-direction: row;
                gap: 5px;
            }
            
            .btn-sm {
                padding: 6px 10px;
            }
            
            .dropdown-content {
                min-width: 160px;
            }
            
            #modalDesignarMecanico > div,
            #modalExcluirUsuario > div {
                width: 95%;
                margin: 5% auto;
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
            
            table {
                font-size: 12px;
            }
            
            .btn {
                padding: 6px 10px;
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
            <div class="mobile-welcome-text">Gerenciar Usuários</div>
            <h1><i class="fas fa-users"></i> Gerenciar Usuários</h1>
            <p>Controle e gerencie todos os usuários do sistema</p>
            <div class="header-content" style="display: none;">
                <div class="header-info">
                    <h1><i class="fas fa-users"></i> Gerenciar Usuários</h1>
                    <p class="header-subtitle">Controle e gerencie todos os usuários do sistema</p>
                </div>

            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Usuários Cadastrados</h3>
                <a href="admin-usuario-novo.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Usuário
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Nível</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-users" style="font-size: 3rem; color: #ddd; margin-bottom: 15px; display: block;"></i>
                                        <h3 style="color: #666; margin-bottom: 10px;">Nenhum usuário encontrado</h3>
                                        <p style="color: #999;">Comece cadastrando seu primeiro usuário.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #109349; display: flex; align-items: center; justify-content: center; color: white; font-weight: 500; font-size: 14px;">
                                                    <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                                                </div>
                                                <strong><?php echo $usuario['nome']; ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo $usuario['email']; ?></td>
                                        <td><?php echo $usuario['telefone'] ?: '-'; ?></td>
                                        <td>
                                            <span class="status-badge nivel-<?php echo $usuario['nivel_acesso']; ?>">
                                                <?php echo ucfirst($usuario['nivel_acesso'] ?? 'cliente'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $usuario['status'] ?? 'ativo'; ?>">
                                                <?php echo ucfirst($usuario['status'] ?? 'ativo'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatarData($usuario['data_cadastro'], 'd/m/Y'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="admin-usuario-editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-primary btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php if ($usuario['id'] != 1 && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-warning btn-sm" title="Opções">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <div class="dropdown-content">
                                                            <a href="admin-usuarios.php?nivel=admin&id=<?php echo $usuario['id']; ?>">Tornar Admin</a>
                                                            <a href="admin-usuarios.php?nivel=gerente&id=<?php echo $usuario['id']; ?>">Tornar Gerente</a>
                                                            <a href="admin-usuarios.php?nivel=funcionario&id=<?php echo $usuario['id']; ?>">Tornar Funcionário</a>
                                                            <a href="admin-usuarios.php?nivel=cliente&id=<?php echo $usuario['id']; ?>">Tornar Cliente</a>
                                                            <a href="#" onclick="designarMecanico(<?php echo $usuario['id']; ?>, '<?php echo $usuario['nome']; ?>')">Designar como Mecânico</a>
                                                            <div style="border-top: 1px solid #eee; margin: 4px 0;"></div>
                                                            <a href="admin-usuarios.php?status=ativo&id=<?php echo $usuario['id']; ?>">Ativar</a>
                                                            <a href="admin-usuarios.php?status=inativo&id=<?php echo $usuario['id']; ?>">Inativar</a>
                                                            <a href="admin-usuarios.php?status=bloqueado&id=<?php echo $usuario['id']; ?>">Bloquear</a>
                                                        </div>
                                                    </div>
                                                    
                                                    <button onclick="confirmarExclusao(<?php echo $usuario['id']; ?>, '<?php echo addslashes($usuario['nome']); ?>')" class="btn btn-danger btn-sm" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Modal para designar mecânico -->
    <div id="modalDesignarMecanico" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 10% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h3 style="color: #333; margin-bottom: 20px; text-align: center;">Designar como Mecânico</h3>
            
            <form id="formDesignarMecanico" method="post" action="designar-funcionario.php">
                <input type="hidden" id="usuario_id" name="usuario_id">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Usuário:</label>
                    <input type="text" id="usuario_nome" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Selecione o Mecânico:</label>
                    <select name="mecanico_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: white;">
                        <option value="">Escolha um mecânico...</option>
                        <?php
                        $conexao = conectarBD();
                        $mecanicos = $conexao->query("SELECT * FROM mecanicos WHERE ativo = 1 ORDER BY nome");
                        if ($mecanicos) {
                            while ($mecanico = $mecanicos->fetch_assoc()) {
                                echo '<option value="' . $mecanico['id'] . '">' . $mecanico['nome'] . ' - ' . $mecanico['especialidade'] . '</option>';
                            }
                        }
                        $conexao->close();
                        ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="fecharModalMecanico()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                        Cancelar
                    </button>
                    <button type="submit" style="background: #109349; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                        Designar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para confirmar exclusão -->
    <div id="modalExcluirUsuario" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background-color: white; padding: 0; border-radius: 10px; width: 90%; max-width: 450px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); overflow: hidden;">
            <div style="background: #e74c3c; color: white; padding: 20px; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                <h3 style="margin: 0; font-size: 1.3rem; font-weight: 600;">Confirmar Exclusão</h3>
            </div>
            
            <div style="padding: 25px; text-align: center;">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #856404; margin: 0; font-weight: 500;">Esta ação não pode ser desfeita!</p>
                </div>
                
                <p style="color: #555; margin-bottom: 15px;">Tem certeza que deseja excluir o usuário:</p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 2px solid #e74c3c;">
                    <strong id="nomeUsuarioExcluir" style="color: #e74c3c; font-size: 1.1rem;"></strong>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button onclick="fecharModalExcluir()" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button onclick="executarExclusao()" style="background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'default';
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
        });
        
        let usuarioParaExcluir = null;
        
        function confirmarExclusao(usuarioId, usuarioNome) {
            usuarioParaExcluir = usuarioId;
            document.getElementById('nomeUsuarioExcluir').textContent = usuarioNome;
            document.getElementById('modalExcluirUsuario').style.display = 'flex';
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluirUsuario').style.display = 'none';
            usuarioParaExcluir = null;
        }
        
        function executarExclusao() {
            if (usuarioParaExcluir) {
                window.location.href = 'admin-usuarios.php?excluir=' + usuarioParaExcluir;
            }
        }
        
        function designarMecanico(usuarioId, usuarioNome) {
            document.getElementById('usuario_id').value = usuarioId;
            document.getElementById('usuario_nome').value = usuarioNome;
            document.getElementById('modalDesignarMecanico').style.display = 'block';
        }
        
        function fecharModalMecanico() {
            document.getElementById('modalDesignarMecanico').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modalMecanico = document.getElementById('modalDesignarMecanico');
            const modalExcluir = document.getElementById('modalExcluirUsuario');
            
            if (event.target == modalMecanico) {
                fecharModalMecanico();
            }
            if (event.target == modalExcluir) {
                fecharModalExcluir();
            }
        }
    </script>
</body>
</html>
