<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Inicializar variáveis
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mecanico = [
    'id' => 0,
    'nome' => '',
    'especialidade' => '',
    'experiencia' => '',
    'biografia' => '',
    'foto' => '',
    'ativo' => 1
];

$conexao = conectarBD();

// Se for edição, buscar dados do mecânico
if ($id > 0) {
    $stmt = $conexao->prepare("SELECT * FROM mecanicos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mecanico = $result->fetch_assoc();
    } else {
        exibirAlerta("danger", "Mecânico não encontrado.");
        header("Location: admin-mecanicos.php");
        exit;
    }
    $stmt->close();
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparDados($_POST['nome']);
    $especialidade = limparDados($_POST['especialidade']);
    $experiencia = (int)$_POST['experiencia'];
    $biografia = limparDados($_POST['biografia']);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $foto_atual = $mecanico['foto'];
    
    // Validar campos obrigatórios
    if (empty($nome) || empty($especialidade)) {
        exibirAlerta("danger", "Por favor, preencha todos os campos obrigatórios.");
    } else {
        // Processar upload de foto se houver
        $foto = $foto_atual;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $diretorio_upload = "uploads/mecanicos/";
            
            // Criar diretório se não existir
            if (!file_exists($diretorio_upload)) {
                mkdir($diretorio_upload, 0777, true);
            }
            
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array(strtolower($extensao), $extensoes_permitidas)) {
                $novo_nome = md5(time() . rand(0, 9999)) . '.' . $extensao;
                $caminho_completo = $diretorio_upload . $novo_nome;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_completo)) {
                    $foto = $novo_nome;
                    
                    // Excluir foto anterior se existir e for diferente
                    if ($foto_atual && $foto_atual != $foto && file_exists($diretorio_upload . $foto_atual)) {
                        unlink($diretorio_upload . $foto_atual);
                    }
                } else {
                    exibirAlerta("danger", "Erro ao fazer upload da foto.");
                }
            } else {
                exibirAlerta("danger", "Formato de arquivo não permitido. Use apenas JPG, JPEG, PNG ou GIF.");
            }
        }
        
        // Se não houver mensagem de erro, salvar no banco
        if (!isset($_SESSION['alerta'])) {
            if ($id > 0) {
                // Atualizar mecânico existente
                $stmt = $conexao->prepare("UPDATE mecanicos SET nome = ?, especialidade = ?, experiencia = ?, biografia = ?, foto = ?, ativo = ? WHERE id = ?");
                $stmt->bind_param("ssissii", $nome, $especialidade, $experiencia, $biografia, $foto, $ativo, $id);
                
                if ($stmt->execute()) {
                    exibirAlerta("success", "Mecânico atualizado com sucesso!");
                    header("Location: admin-mecanicos.php");
                    exit;
                } else {
                    exibirAlerta("danger", "Erro ao atualizar mecânico: " . $conexao->error);
                }
            } else {
                // Inserir novo mecânico
                $stmt = $conexao->prepare("INSERT INTO mecanicos (nome, especialidade, experiencia, biografia, foto, ativo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisii", $nome, $especialidade, $experiencia, $biografia, $foto, $ativo);
                
                if ($stmt->execute()) {
                    exibirAlerta("success", "Mecânico cadastrado com sucesso!");
                    header("Location: admin-mecanicos.php");
                    exit;
                } else {
                    exibirAlerta("danger", "Erro ao cadastrar mecânico: " . $conexao->error);
                }
            }
            $stmt->close();
        }
    }
}

$conexao->close();

// Título da página
$titulo = ($id > 0) ? "Editar Mecânico" : "Novo Mecânico";
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
        
        body.dark-mode .card {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .card-header {
            background-color: #333;
            border-color: #444;
        }
        
        .sidebar {
            width: 250px;
            background-color: #109349;
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
            margin-bottom: 0;
        }
        
        .mobile-welcome-text {
            display: none;
        }
        
        .container {
            max-width: 1400px;
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
        
        .theme-alemanha .card {
            background: #000000;
            border: 2px solid #DD0100;
            box-shadow: 0 2px 10px rgba(255, 206, 0, 0.3);
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
        
        .theme-alemanha .card-body {
            background: #000000;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .theme-alemanha .form-group label {
            color: #FFCE00;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
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
            box-shadow: 0 0 0 2px rgba(16, 147, 73, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background-color: #109349;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #109349;
        }
        
        .preview-container {
            margin-top: 15px;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #109349;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                position: fixed;
                z-index: 1001;
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .page-header {
                background-image: url('bem-vindo-italia-responsivo.jpg') !important;
                background-size: 100% 180px !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                padding: 10px;
                border-radius: 15px;
                margin-bottom: 15px;
                min-height: 180px;
                height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: flex-end;
                text-align: right;
                position: relative;
                border: none;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                background-color: transparent !important;
            }
            
            .theme-alemanha .page-header {
                background-image: url('bem-vindo-alemanha-responsivo.jpg') !important;
                background-size: 100% 180px !important;
                background-position: center !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
    
    <div class="content">
        <div class="page-header">
            <div class="mobile-welcome-text"><?php echo $titulo; ?></div>
            <h1><i class="fas fa-user-cog"></i> <?php echo $titulo; ?></h1>
            <p>Preencha os dados do mecânico</p>
        </div>
        
        <div class="container">
            <?php mostrarAlerta(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-cog"></i> Dados do Mecânico</h2>
                    <a href="admin-mecanicos.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
                <div class="card-body">
                <form action="<?php echo $_SERVER['PHP_SELF'] . ($id > 0 ? "?id=$id" : ""); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="<?php echo $mecanico['nome']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="especialidade">Especialidade *</label>
                        <input type="text" id="especialidade" name="especialidade" class="form-control" value="<?php echo $mecanico['especialidade']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="experiencia">Anos de Experiência</label>
                        <input type="number" id="experiencia" name="experiencia" class="form-control" value="<?php echo $mecanico['experiencia']; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="biografia">Biografia/Descrição</label>
                        <textarea id="biografia" name="biografia" class="form-control"><?php echo $mecanico['biografia']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto">Foto</label>
                        <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
                        
                        <?php if ($mecanico['foto']): ?>
                            <div class="preview-container">
                                <p>Foto atual:</p>
                                <img src="uploads/mecanicos/<?php echo $mecanico['foto']; ?>" alt="Foto atual" class="preview-image">
                            </div>
                        <?php endif; ?>
                        
                        <div class="preview-container" id="preview-container" style="display: none;">
                            <p>Nova foto:</p>
                            <img src="" alt="Preview" id="preview-image" class="preview-image">
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="ativo" name="ativo" <?php echo $mecanico['ativo'] ? 'checked' : ''; ?>>
                        <label for="ativo">Mecânico Ativo</label>
                    </div>
                    
                    <div class="form-actions">
                        <a href="admin-mecanicos.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Mecânico
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (file_exists('components/theme-toggle.php')) include 'components/theme-toggle.php'; ?>
    
    <script>
        // Aplicar tema salvo
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'default';
            if (savedTheme === 'theme-alemanha') {
                document.body.classList.add('theme-alemanha');
            }
            
            // Script para o menu mobile
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (mobileMenuToggle && sidebar) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    sidebar.classList.toggle('active');
                    
                    // Criar/remover overlay
                    let overlay = document.querySelector('.mobile-overlay');
                    if (sidebar.classList.contains('active')) {
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'mobile-overlay';
                            overlay.style.cssText = `
                                position: fixed;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(0,0,0,0.5);
                                z-index: 1000;
                                backdrop-filter: blur(2px);
                            `;
                            document.body.appendChild(overlay);
                            
                            overlay.addEventListener('click', function() {
                                sidebar.classList.remove('active');
                                overlay.remove();
                            });
                        }
                    } else {
                        if (overlay) overlay.remove();
                    }
                });
                
                // Fechar menu ao clicar em itens do menu
                const menuItems = sidebar.querySelectorAll('.menu-item');
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('active');
                            const overlay = document.querySelector('.mobile-overlay');
                            if (overlay) overlay.remove();
                        }
                    });
                });
            }
            
            // Preview da imagem
            const inputFoto = document.getElementById('foto');
            const previewContainer = document.getElementById('preview-container');
            const previewImage = document.getElementById('preview-image');
            
            if (inputFoto) {
                inputFoto.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewContainer.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        previewContainer.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
