<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: index.php");
    exit;
}

function gerarCupom() {
    $prefixos = ['PROMO', 'DESC', 'OFERTA', 'SAVE', 'MEGA'];
    return $prefixos[array_rand($prefixos)] . rand(10, 99);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexao = conectarBD();
    
    $conexao->query("CREATE TABLE IF NOT EXISTS promocoes_carousel (
        id INT PRIMARY KEY AUTO_INCREMENT,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        desconto_percentual INT,
        codigo_cupom VARCHAR(50),
        imagem VARCHAR(255),
        data_inicio DATE NOT NULL,
        data_fim DATE NOT NULL,
        ativo TINYINT DEFAULT 1,
        ordem INT DEFAULT 0,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
        $promo_id = $_POST['promo_id'];
        $stmt = $conexao->prepare("SELECT imagem FROM promocoes_carousel WHERE id = ?");
        $stmt->bind_param("i", $promo_id);
        $stmt->execute();
        $promo_atual = $stmt->get_result()->fetch_assoc();
        $imagem = $promo_atual['imagem'];
        
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $upload_dir = 'uploads/promocoes/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $nome_arquivo = 'promo_' . time() . '.' . pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $nome_arquivo)) {
                if ($imagem && file_exists($upload_dir . $imagem)) unlink($upload_dir . $imagem);
                $imagem = $nome_arquivo;
            }
        }
        
        $titulo = $_POST['titulo'];
        $descricao = $_POST['descricao'];
        $desconto = $_POST['desconto_percentual'];
        $cupom = $_POST['codigo_cupom'] ?: gerarCupom();
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $conexao->prepare("UPDATE promocoes_carousel SET titulo = ?, descricao = ?, desconto_percentual = ?, codigo_cupom = ?, imagem = ?, data_inicio = ?, data_fim = ?, ativo = ? WHERE id = ?");
        $stmt->bind_param("ssissssii", $titulo, $descricao, $desconto, $cupom, $imagem, $data_inicio, $data_fim, $ativo, $promo_id);
        $stmt->execute();
    } else {
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $upload_dir = 'uploads/promocoes/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $nome_arquivo = 'promo_' . time() . '.' . pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $nome_arquivo)) $imagem = $nome_arquivo;
        }
        
        $titulo = $_POST['titulo'];
        $descricao = $_POST['descricao'];
        $desconto = $_POST['desconto_percentual'];
        $cupom = $_POST['codigo_cupom'] ?: gerarCupom();
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $conexao->prepare("INSERT INTO promocoes_carousel (titulo, descricao, desconto_percentual, codigo_cupom, imagem, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssi", $titulo, $descricao, $desconto, $cupom, $imagem, $data_inicio, $data_fim, $ativo);
        $stmt->execute();
    }
    
    $conexao->close();
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Operação realizada com sucesso!'];
    header("Location: admin-promocoes.php");
    exit;
}

if (isset($_GET['deletar'])) {
    $conexao = conectarBD();
    $stmt = $conexao->prepare("SELECT imagem FROM promocoes_carousel WHERE id = ?");
    $stmt->bind_param("i", $_GET['deletar']);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    if ($promo && $promo['imagem'] && file_exists('uploads/promocoes/' . $promo['imagem'])) {
        unlink('uploads/promocoes/' . $promo['imagem']);
    }
    $stmt = $conexao->prepare("DELETE FROM promocoes_carousel WHERE id = ?");
    $stmt->bind_param("i", $_GET['deletar']);
    $stmt->execute();
    $conexao->close();
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Promoção deletada com sucesso!'];
    header("Location: admin-promocoes.php");
    exit;
}

$promo_editar = null;
if (isset($_GET['editar'])) {
    $conexao = conectarBD();
    $stmt = $conexao->prepare("SELECT * FROM promocoes_carousel WHERE id = ?");
    $stmt->bind_param("i", $_GET['editar']);
    $stmt->execute();
    $promo_editar = $stmt->get_result()->fetch_assoc();
    $conexao->close();
}

$conexao = conectarBD();
$promocoes = $conexao->query("SELECT * FROM promocoes_carousel ORDER BY data_criacao DESC");
$conexao->close();

$titulo = "Gerenciar Promoções";
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
    <link rel="stylesheet" href="admin-responsive.css">
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
        
        body.dark-mode {
            --tertiary-color: #1a1a1a;
            --text-color: #f5f5f5;
            --secondary-color: #1e1e1e;
            background-color: #1a1a1a;
            color: #f5f5f5;
        }
        
        body.dark-mode .card,
        body.dark-mode .stat-card {
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
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
        }
        
        .page-header p {
            opacity: 1;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 8px rgba(0, 0, 0, 0.6);
            font-weight: 600;
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

<style>
    .search-bar {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .theme-alemanha .search-bar {
        background: #000000;
        border: 2px solid #DD0100;
    }
    
    .search-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #eee;
        border-radius: 8px;
        font-size: 16px;
    }
    
    .theme-alemanha .search-input {
        background: #1a1a1a;
        border: 2px solid #DD0100;
        color: #ffffff;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #109349;
    }
    
    .promocoes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .promocao-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .theme-alemanha .promocao-card {
        background: #000000;
        border: 2px solid #DD0100;
        box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
    }
    
    .promocao-card:hover {
        transform: translateY(-5px);
    }
    
    .promocao-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .promocao-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #109349;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-right: 15px;
    }
    
    .promocao-info h3 {
        margin: 0 0 5px 0;
        color: #2c3e50;
    }
    
    .theme-alemanha .promocao-info h3 {
        color: #FFCE00;
    }
    
    .promocao-desconto {
        color: #109349;
        font-weight: bold;
        font-size: 0.9rem;
    }
    
    .theme-alemanha .promocao-desconto {
        color: #FFCE00;
    }
    
    .promocao-detalhes {
        margin: 15px 0;
    }
    
    .promocao-detalhes p {
        margin: 5px 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .theme-alemanha .promocao-detalhes p {
        color: #ffffffb0;
    }
    
    .promocao-cupom {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        font-family: monospace;
        font-weight: bold;
        color: #109349;
        border: 1px solid #e9ecef;
        display: inline-block;
        margin: 10px 0;
    }
    
    .theme-alemanha .promocao-cupom {
        background: #1a1a1a;
        color: #FFCE00;
        border: 1px solid #DD0100;
    }
    
    .promocao-periodo {
        font-size: 0.8rem;
        color: #666;
        margin: 10px 0;
    }
    
    .theme-alemanha .promocao-periodo {
        color: #ffffff;
    }
    
    .promocao-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-ativo {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .status-inativo {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .theme-alemanha .status-ativo {
        background: rgba(255, 206, 0, 0.2);
        color: #FFCE00;
    }
    
    .theme-alemanha .status-inativo {
        background: rgba(221, 1, 0, 0.2);
        color: #DD0100;
    }
    
    .promocao-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-action {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        text-align: center;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: #109349;
        color: white;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #000;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .form-modal {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .theme-alemanha .form-modal {
        background: #000000;
        border: 2px solid #DD0100;
        box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
    }
    
    .form-modal h3 {
        margin: 0 0 20px 0;
        color: #2c3e50;
    }
    
    .theme-alemanha .form-modal h3 {
        color: #FFCE00;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .theme-alemanha .form-group label {
        color: #FFCE00;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
    }
    
    .theme-alemanha .form-group input,
    .theme-alemanha .form-group textarea {
        background: #1a1a1a;
        border: 2px solid #DD0100;
        color: #ffffff;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #109349;
    }
    
    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 15px 0;
    }
    
    .btn {
        background: #109349;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn:hover {
        background: #0d7a3a;
        transform: translateY(-2px);
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
        
        .page-header h1,
        .page-header p {
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
        
        .header {
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .container {
            padding: 10px;
        }
        
        .search-container {
            flex-direction: column !important;
        }
        
        .search-bar {
            padding: 15px;
            width: 100% !important;
        }
        
        .btn-nova-promo {
            width: 100%;
            justify-content: center;
        }
        
        .promocoes-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .promocao-card {
            padding: 15px;
        }
        
        .promocao-actions {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .form-modal {
            padding: 20px;
        }
        
        #modalFormulario > div {
            margin: 20px 10px !important;
            padding: 20px !important;
        }
        
        #modalPreview > div {
            margin: 20px 10px !important;
        }
        
        .promo-slide-card {
            flex-direction: column !important;
            padding: 30px 20px !important;
            height: auto !important;
        }
        
        .promo-slide-content {
            width: 100% !important;
            margin-bottom: 20px;
        }
        
        .promo-slide-desc {
            font-size: 1rem !important;
        }
        
        .discount-badge {
            font-size: 1.3rem !important;
            padding: 10px 20px !important;
        }
        
        .cupom-code {
            font-size: 1rem !important;
            padding: 10px 15px !important;
        }
        
        .promo-slide-image {
            width: 100% !important;
            height: 150px !important;
        }
        
        .btn {
            padding: 10px 16px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .header h1 {
            font-size: 1.2rem;
        }
        
        .promocao-header {
            flex-direction: column;
            text-align: center;
        }
        
        .promocao-avatar {
            margin: 0 0 10px 0;
        }
        
        .promocao-info h3 {
            font-size: 1rem;
        }
        
        .form-modal h3 {
            font-size: 1.1rem;
        }
        
        .btn {
            padding: 10px 16px;
            font-size: 14px;
        }
        
        .btn-nova-promo {
            padding: 12px 20px;
        }
        
        .promo-slide-desc {
            font-size: 0.9rem !important;
        }
        
        .discount-badge {
            font-size: 1.1rem !important;
        }
    }
</style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text">Gerenciar Promoções</div>
            <h1><i class="fas fa-gift"></i> Gerenciar Promoções</h1>
            <p>Crie e gerencie promoções e cupons de desconto</p>
        </div>
        <div class="container">

<?php if ($promo_editar): ?>
<div class="form-modal">
    <h3><i class="fas fa-edit"></i> Editar Promoção</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="promo_id" value="<?php echo $promo_editar['id']; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($promo_editar['titulo']); ?>" required>
            </div>
            <div class="form-group">
                <label>Desconto (%)</label>
                <input type="number" name="desconto_percentual" value="<?php echo $promo_editar['desconto_percentual']; ?>" min="1" max="100">
            </div>
        </div>
        <div class="form-group">
            <label>Descrição</label>
            <textarea name="descricao" rows="3" required><?php echo htmlspecialchars($promo_editar['descricao']); ?></textarea>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Código do Cupom</label>
                <input type="text" name="codigo_cupom" value="<?php echo htmlspecialchars($promo_editar['codigo_cupom']); ?>">
            </div>
            <div class="form-group">
                <label>Imagem <?php echo $promo_editar['imagem'] ? '<small>(deixe vazio para manter)</small>' : ''; ?></label>
                <input type="file" name="imagem" accept="image/*">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Data Início</label>
                <input type="date" name="data_inicio" value="<?php echo $promo_editar['data_inicio']; ?>" required>
            </div>
            <div class="form-group">
                <label>Data Fim</label>
                <input type="date" name="data_fim" value="<?php echo $promo_editar['data_fim']; ?>" required>
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" name="ativo" id="ativo" <?php echo $promo_editar['ativo'] ? 'checked' : ''; ?>>
            <label for="ativo">Promoção Ativa</label>
        </div>
        <button type="submit" class="btn"><i class="fas fa-save"></i> Atualizar</button>
        <a href="admin-promocoes.php" class="btn" style="background: #6c757d;"><i class="fas fa-times"></i> Cancelar</a>
    </form>
</div>
<?php else: ?>
<div class="search-container" style="display: flex; gap: 15px; margin-bottom: 20px;">
    <div class="search-bar" style="flex: 1; margin: 0;">
        <input type="text" class="search-input" placeholder="🔍 Buscar promoção..." onkeyup="filtrarPromocoes(this.value)">
    </div>
    <button onclick="abrirFormulario()" class="btn btn-nova-promo" style="white-space: nowrap;">
        <i class="fas fa-plus"></i> Nova Promoção
    </button>
</div>

<!-- Modal Formulário Nova Promoção -->
<div id="modalFormulario" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 800px; margin: 50px auto; background: white; border-radius: 15px; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3><i class="fas fa-plus"></i> Nova Promoção</h3>
            <button onclick="fecharFormulario()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="formPromocao">
            <div class="form-grid">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" id="titulo" required onkeyup="atualizarPreview()">
                </div>
                <div class="form-group">
                    <label>Desconto (%)</label>
                    <input type="number" name="desconto_percentual" id="desconto" min="1" max="100" onkeyup="atualizarPreview()">
                </div>
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" id="descricao" rows="3" required onkeyup="atualizarPreview()"></textarea>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Código do Cupom</label>
                    <input type="text" name="codigo_cupom" id="cupom" onkeyup="atualizarPreview()">
                </div>
                <div class="form-group">
                    <label>Imagem</label>
                    <input type="file" name="imagem" accept="image/*" onchange="previewImagem(this)">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" id="dataInicio" required onchange="atualizarPreview()">
                </div>
                <div class="form-group">
                    <label>Data Fim</label>
                    <input type="date" name="data_fim" id="dataFim" required onchange="atualizarPreview()">
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="ativo" id="ativoNovo" checked>
                <label for="ativoNovo">Promoção Ativa</label>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="mostrarPreview()" class="btn" style="background: #17a2b8;">
                    <i class="fas fa-eye"></i> Pré-visualizar
                </button>
                <button type="submit" class="btn"><i class="fas fa-save"></i> Salvar</button>
                <button type="button" onclick="fecharFormulario()" class="btn" style="background: #6c757d;"><i class="fas fa-times"></i> Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview -->
<div id="modalPreview" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 99999; overflow-y: auto;">
    <div style="max-width: 1200px; margin: 50px auto; padding: 20px; position: relative; z-index: 100000;">
        <div style="text-align: right; margin-bottom: 20px;">
            <button type="button" onclick="fecharPreview()" style="background: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; position: relative; z-index: 100001;">
                <i class="fas fa-times"></i> Fechar Preview
            </button>
        </div>
        <div class="promocoes-carousel" style="height: 400px; border-radius: 20px; overflow: hidden;">
            <div class="promo-slide-card" id="previewCard" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 50px; height: 400px; display: flex; align-items: center; justify-content: space-between;">
                <div class="promo-slide-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); z-index: 1;"></div>
                <div class="promo-slide-content" style="position: relative; z-index: 2; width: 60%; color: white;">
                    <p class="promo-slide-desc" id="previewDesc" style="font-size: 1.3rem; margin-bottom: 20px; line-height: 1.6; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"></p>
                    <div class="promo-slide-discount" id="previewDesconto" style="margin-bottom: 15px;">
                        <span class="discount-badge" style="background: linear-gradient(135deg, #ff6b6b, #feca57); color: white; padding: 12px 24px; border-radius: 30px; font-size: 1.8rem; font-weight: bold; border: 3px solid rgba(255,255,255,0.8); box-shadow: 0 8px 20px rgba(0,0,0,0.3); display: inline-block;"></span>
                    </div>
                    <div class="promo-slide-cupom" id="previewCupom" style="margin-bottom: 15px;">
                        <span class="cupom-code" style="background: linear-gradient(135deg, #feca57, #f39c12); color: #2c3e50; padding: 12px 20px; border-radius: 25px; font-weight: bold; font-size: 1.3rem; border: 3px dashed rgba(255,255,255,0.8); display: inline-block; letter-spacing: 2px;"></span>
                    </div>
                    <div class="promo-slide-validity" id="previewValidade" style="font-size: 1rem; background: rgba(0,0,0,0.8); color: #fff; padding: 8px 15px; border-radius: 20px; display: inline-block;"></div>
                </div>
                <div class="promo-slide-image" id="previewImagem" style="position: relative; z-index: 2; width: 35%; height: 200px; background: rgba(255,255,255,0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-gift" style="font-size: 4rem; color: rgba(255,255,255,0.8);"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="margin-top: 30px;">
<?php mostrarAlerta(); ?>
</div>

<div class="promocoes-grid" id="promocoesGrid">
    <?php if ($promocoes && $promocoes->num_rows > 0): ?>
        <?php while ($promo = $promocoes->fetch_assoc()): ?>
            <div class="promocao-card" data-titulo="<?php echo strtolower($promo['titulo']); ?>" data-cupom="<?php echo strtolower($promo['codigo_cupom']); ?>">
                <div class="promocao-header">
                    <div class="promocao-avatar">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="promocao-info">
                        <h3><?php echo htmlspecialchars($promo['titulo']); ?></h3>
                        <div class="promocao-desconto"><?php echo $promo['desconto_percentual']; ?>% OFF</div>
                    </div>
                </div>
                
                <div class="promocao-detalhes">
                    <p><?php echo htmlspecialchars($promo['descricao']); ?></p>
                    
                    <div class="promocao-cupom">
                        <?php echo $promo['codigo_cupom']; ?>
                    </div>
                    
                    <div class="promocao-periodo">
                        <i class="far fa-calendar"></i> 
                        <?php echo date('d/m/Y', strtotime($promo['data_inicio'])); ?> até 
                        <?php echo date('d/m/Y', strtotime($promo['data_fim'])); ?>
                    </div>
                    
                    <span class="promocao-status <?php echo $promo['ativo'] ? 'status-ativo' : 'status-inativo'; ?>">
                        <?php echo $promo['ativo'] ? 'Ativa' : 'Inativa'; ?>
                    </span>
                </div>
                
                <div class="promocao-actions">
                    <a href="admin-promocoes.php?editar=<?php echo $promo['id']; ?>" class="btn-action btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="admin-promocoes.php?deletar=<?php echo $promo['id']; ?>" class="btn-action btn-danger" onclick="return confirm('Deletar esta promoção?')">
                        <i class="fas fa-trash"></i> Deletar
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-gift fa-3x" style="margin-bottom: 15px;"></i>
            <h3>Nenhuma promoção encontrada</h3>
            <p>Crie sua primeira promoção.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'default';
    if (savedTheme === 'theme-alemanha') {
        document.body.classList.add('theme-alemanha');
    }
});

function filtrarPromocoes(termo) {
    const cards = document.querySelectorAll('.promocao-card');
    const termoBusca = termo.toLowerCase();
    
    cards.forEach(card => {
        const titulo = card.getAttribute('data-titulo');
        const cupom = card.getAttribute('data-cupom');
        
        if (titulo.includes(termoBusca) || cupom.includes(termoBusca)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function abrirFormulario() {
    document.getElementById('modalFormulario').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function fecharFormulario() {
    document.getElementById('modalFormulario').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function mostrarPreview() {
    atualizarPreview();
    document.getElementById('modalPreview').style.display = 'block';
}

function fecharPreview() {
    document.getElementById('modalPreview').style.display = 'none';
    document.getElementById('modalFormulario').style.display = 'block';
}

function atualizarPreview() {
    const titulo = document.getElementById('titulo').value || 'Título da Promoção';
    const descricao = document.getElementById('descricao').value || 'Descrição da promoção';
    const desconto = document.getElementById('desconto').value || '0';
    const cupom = document.getElementById('cupom').value || 'CUPOM';
    const dataInicio = document.getElementById('dataInicio').value;
    const dataFim = document.getElementById('dataFim').value;
    
    document.getElementById('previewDesc').textContent = descricao;
    document.querySelector('#previewDesconto .discount-badge').textContent = desconto + '% OFF';
    document.querySelector('#previewCupom .cupom-code').textContent = cupom;
    
    if (dataFim) {
        const data = new Date(dataFim);
        document.getElementById('previewValidade').textContent = 'Válido até ' + data.toLocaleDateString('pt-BR');
    }
}

function previewImagem(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewCard = document.getElementById('previewCard');
            previewCard.style.backgroundImage = 'url(' + e.target.result + ')';
            previewCard.style.backgroundSize = 'cover';
            previewCard.style.backgroundPosition = 'center';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
</body>
</html>
