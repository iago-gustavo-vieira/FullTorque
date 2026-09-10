<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Processar exclusão de serviço
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    $conexao = conectarBD();
    
    // Verificar se o serviço está em uso
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM agendamento_itens WHERE servico_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $em_uso = $result->fetch_assoc()['total'] > 0;
    
    if ($em_uso) {
        exibirAlerta('error', 'Este serviço não pode ser excluído pois está em uso em agendamentos.');
    } else {
        $stmt = $conexao->prepare("DELETE FROM servicos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            registrarLog('servico_excluido', "Serviço ID: $id excluído");
            exibirAlerta('success', 'Serviço excluído com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível excluir o serviço.');
        }
    }
    
    $conexao->close();
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-servicos.php");
    exit;
}

// Processar alteração de status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['ativo', 'inativo'])) {
        $conexao = conectarBD();
        
        // Verificar se a coluna status existe
        if (colunaExiste($conexao, 'servicos', 'status')) {
            $stmt = $conexao->prepare("UPDATE servicos SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                registrarLog('servico_status', "Status do serviço ID: $id alterado para $status");
                exibirAlerta('success', 'Status do serviço alterado com sucesso.');
            } else {
                exibirAlerta('error', 'Não foi possível alterar o status do serviço.');
            }
        } else {
            exibirAlerta('error', 'Coluna status não existe na tabela.');
        }
        
        $conexao->close();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-servicos.php");
    exit;
}

// Processar adição/edição de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparDados($_POST['nome']);
    $descricao = limparDados($_POST['descricao']);
    $preco = str_replace(',', '.', limparDados($_POST['preco']));
    $tempo_estimado = (int)limparDados($_POST['tempo_estimado']);
    $categoria = limparDados($_POST['categoria']);
    
    $conexao = conectarBD();
    
    // Verificar quais colunas existem
    $tem_preco = colunaExiste($conexao, 'servicos', 'preco');
    $tem_tempo = colunaExiste($conexao, 'servicos', 'tempo_estimado');
    $tem_categoria = colunaExiste($conexao, 'servicos', 'categoria');
    
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        // Edição
        $id = (int)$_POST['id'];
        
        // Montar query dinamicamente baseado nas colunas existentes
        $campos = ["nome = ?", "descricao = ?"];
        $tipos = "ss";
        $valores = [$nome, $descricao];
        
        if ($tem_preco) {
            $campos[] = "preco = ?";
            $tipos .= "d";
            $valores[] = $preco;
        }
        if ($tem_tempo) {
            $campos[] = "tempo_estimado = ?";
            $tipos .= "i";
            $valores[] = $tempo_estimado;
        }
        if ($tem_categoria) {
            $campos[] = "categoria = ?";
            $tipos .= "s";
            $valores[] = $categoria;
        }
        
        $valores[] = $id;
        $tipos .= "i";
        
        $sql = "UPDATE servicos SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        
        if ($stmt->execute()) {
            registrarLog('servico_atualizado', "Serviço ID: $id atualizado");
            exibirAlerta('success', 'Serviço atualizado com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível atualizar o serviço.');
        }
    } else {
        // Adição
        $campos = ["nome", "descricao"];
        $placeholders = ["?", "?"];
        $tipos = "ss";
        $valores = [$nome, $descricao];
        
        if ($tem_preco) {
            $campos[] = "preco";
            $placeholders[] = "?";
            $tipos .= "d";
            $valores[] = $preco;
        }
        if ($tem_tempo) {
            $campos[] = "tempo_estimado";
            $placeholders[] = "?";
            $tipos .= "i";
            $valores[] = $tempo_estimado;
        }
        if ($tem_categoria) {
            $campos[] = "categoria";
            $placeholders[] = "?";
            $tipos .= "s";
            $valores[] = $categoria;
        }
        
        $sql = "INSERT INTO servicos (" . implode(", ", $campos) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        
        if ($stmt->execute()) {
            $id = $conexao->insert_id;
            registrarLog('servico_cadastrado', "Serviço ID: $id cadastrado");
            exibirAlerta('success', 'Serviço cadastrado com sucesso.');
        } else {
            exibirAlerta('error', 'Não foi possível cadastrar o serviço.');
        }
    }
    
    $conexao->close();
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-servicos.php");
    exit;
}

// Buscar serviço para edição
$servico = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    
    $conexao = conectarBD();
    $stmt = $conexao->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $servico = $result->fetch_assoc();
    }
    
    $conexao->close();
}

// Buscar todos os serviços
$conexao = conectarBD();
$order_by = colunaExiste($conexao, 'servicos', 'categoria') ? 'categoria, nome' : 'nome';
$servicos = $conexao->query("SELECT * FROM servicos ORDER BY $order_by")->fetch_all(MYSQLI_ASSOC);
$conexao->close();

// Título da página
$titulo_pagina = "Gerenciar Serviços";
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
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
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
        <div class="mobile-welcome-text">Gerenciar Serviços</div>
        <h1><i class="fas fa-tools"></i> Gerenciar Serviços</h1>
        <p>Cadastre e gerencie todos os serviços oferecidos</p>
        <div class="header-content" style="display: none;">
            <div class="header-info">
                <h1><i class="fas fa-tools"></i> Gerenciar Serviços</h1>
                <p class="header-subtitle">Cadastre e gerencie todos os serviços oferecidos</p>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <!-- Formulário de Cadastro/Edição -->
            <div class="card form-card">
                <div class="card-header">
                    <h3><i class="fas fa-<?php echo $servico ? 'edit' : 'plus-circle'; ?>"></i> <?php echo $servico ? 'Editar Serviço' : 'Novo Serviço'; ?></h3>
                </div>
                <div class="card-body">
                    <form action="admin-servicos.php" method="post" class="service-form">
                        <?php if ($servico): ?>
                            <input type="hidden" name="id" value="<?php echo $servico['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="nome"><i class="fas fa-tag"></i> Nome do Serviço</label>
                            <input type="text" id="nome" name="nome" class="form-control" value="<?php echo $servico ? $servico['nome'] : ''; ?>" required placeholder="Ex: Troca de Óleo">
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria"><i class="fas fa-folder"></i> Categoria</label>
                            <input type="text" id="categoria" name="categoria" class="form-control" value="<?php echo $servico ? ($servico['categoria'] ?? '') : ''; ?>" required placeholder="Ex: Manutenção">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="preco"><i class="fas fa-dollar-sign"></i> Preço (R$)</label>
                                <input type="text" id="preco" name="preco" class="form-control" value="<?php echo $servico ? number_format($servico['preco'] ?? 0, 2, ',', '.') : ''; ?>" required placeholder="0,00">
                            </div>
                            
                            <div class="form-group">
                                <label for="tempo_estimado"><i class="fas fa-clock"></i> Tempo (min)</label>
                                <input type="number" id="tempo_estimado" name="tempo_estimado" class="form-control" value="<?php echo $servico ? ($servico['tempo_estimado'] ?? '') : ''; ?>" required placeholder="60">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="4" required placeholder="Descreva detalhadamente o serviço..."><?php echo $servico ? $servico['descricao'] : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> <?php echo $servico ? 'Atualizar' : 'Cadastrar'; ?>
                            </button>
                            
                            <?php if ($servico): ?>
                                <a href="admin-servicos.php" class="btn btn-outline btn-block">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Lista de Serviços -->
            <div class="card services-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Serviços Cadastrados</h3>
                    <div class="header-actions">
                        <div class="search-box">
                            <input type="text" id="searchServices" placeholder="Buscar serviços..." class="form-control">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="services-container">
                        <div class="services-grid" id="servicesGrid">
                        <?php foreach ($servicos as $servico_item): ?>
                            <div class="service-card" data-name="<?php echo strtolower($servico_item['nome'] ?? ''); ?>" data-category="<?php echo strtolower($servico_item['categoria'] ?? 'Geral'); ?>">
                                <div class="service-header">
                                    <div class="service-title"><?php echo $servico_item['nome']; ?></div>
                                    <div class="service-status">
                                        <span class="status-badge status-<?php echo $servico_item['status'] ?? 'ativo'; ?>">
                                            <?php echo ($servico_item['status'] ?? 'ativo') === 'ativo' ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="service-info">
                                    <div class="service-category">
                                        <i class="fas fa-folder"></i> <?php echo $servico_item['categoria'] ?? 'Geral'; ?>
                                    </div>
                                    <div class="service-description">
                                        <?php echo substr($servico_item['descricao'], 0, 100) . (strlen($servico_item['descricao']) > 100 ? '...' : ''); ?>
                                    </div>
                                </div>
                                
                                <div class="service-details">
                                    <div class="detail-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span><?php echo formatarMoeda($servico_item['preco'] ?? 0); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $servico_item['tempo_estimado'] ?? 0; ?> min</span>
                                    </div>
                                </div>
                                
                                <div class="service-actions">
                                    <a href="admin-servicos.php?editar=<?php echo $servico_item['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if (($servico_item['status'] ?? 'ativo') === 'ativo'): ?>
                                        <a href="admin-servicos.php?status=inativo&id=<?php echo $servico_item['id']; ?>" class="btn btn-sm btn-warning" title="Desativar">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="admin-servicos.php?status=ativo&id=<?php echo $servico_item['id']; ?>" class="btn btn-sm btn-success" title="Ativar">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="admin-servicos.php?excluir=<?php echo $servico_item['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($servicos)): ?>
                            <div class="empty-state">
                                <i class="fas fa-tools"></i>
                                <h3>Nenhum serviço cadastrado</h3>
                                <p>Comece cadastrando seu primeiro serviço no formulário ao lado.</p>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
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



.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding: 0 15px;
}

.col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
    padding: 0 15px;
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

.form-card .card-body {
    min-height: 580px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.theme-alemanha .form-group label {
    color: #FFCE00;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.theme-alemanha .form-control {
    background: #1a1a1a;
    border: 1px solid #DD0100;
    color: #ffffff;
}

.theme-alemanha .form-control::placeholder {
    color: #999;
}

.form-control:focus {
    outline: none;
    border-color: #109349;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
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

.btn-outline {
    background: transparent;
    color: #109349;
    border: 2px solid #109349;
}

.btn-outline:hover {
    background: #109349;
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

.btn-block {
    width: 100%;
    margin-bottom: 10px;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 12px;
}

.search-box {
    position: relative;
    width: 220px;
}

.search-box input {
    padding: 8px 35px 8px 12px;
    background: rgba(255,255,255,0.9);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 6px;
    color: #333;
}

.search-box input::placeholder {
    color: #666;
}

.search-box i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.header-actions {
    display: flex;
    align-items: center;
}

.services-container {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 8px;
}

.services-container::-webkit-scrollbar {
    width: 6px;
}

.services-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.services-container::-webkit-scrollbar-thumb {
    background: #109349;
    border-radius: 3px;
}

.services-container::-webkit-scrollbar-thumb:hover {
    background: #0d7a3a;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.service-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 18px;
    border-left: 4px solid #109349;
    border: 1px solid #e0e0e0;
}

.theme-alemanha .service-card {
    background: #1a1a1a;
    border-left: 4px solid #FFCE00;
    border: 1px solid #DD0100;
}

.service-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.service-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.3;
}

.theme-alemanha .service-title {
    color: #FFCE00;
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

.theme-alemanha .status-ativo {
    background: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .status-inativo {
    background: rgba(221, 1, 0, 0.2);
    color: #DD0100;
}

.service-category {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.theme-alemanha .service-category {
    color: #ffffff;
}

.service-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 15px;
}

.theme-alemanha .service-description {
    color: #ffffffb0;
}

.service-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding: 10px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    color: #2c3e50;
}

.theme-alemanha .detail-item {
    color: #ffffff;
}

.service-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-state i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 15px;
}

.empty-state h3 {
    margin-bottom: 8px;
    color: #333;
}

.theme-alemanha .empty-state {
    color: #ffffffb0;
}

.theme-alemanha .empty-state h3 {
    color: #FFCE00;
}

@media (max-width: 768px) {
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
}

@media (max-width: 992px) {
    .col-md-4, .col-md-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    

    
    .card-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .search-box {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 15px;
    }
    
    .page-header {
        padding: 20px;
    }
    
    .header-info h1 {
        font-size: 1.8rem;
    }
    

    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .service-details {
        flex-direction: column;
        gap: 10px;
    }
    
    .service-actions {
        justify-content: center;
    }
    
    .card-body {
        padding: 20px;
    }
}
</style>

<?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar tema salvo
    const savedTheme = localStorage.getItem('theme') || 'default';
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
    }
    
    const searchInput = document.getElementById('searchServices');
    const serviceCards = document.querySelectorAll('.service-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            serviceCards.forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;
                
                if (name.includes(searchTerm) || category.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

</div>
</div>
</body>
</html>

