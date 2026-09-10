<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$titulo = "Meu Perfil";
$usuario_id = $_SESSION['usuario_id'];

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexao = conectarBD();
    
    $nome = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $telefone = limparDados($_POST['telefone']);
    $token_recuperacao = limparDados($_POST['token_recuperacao']);
    
    // Atualizar dados básicos
    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nome, $email, $telefone, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['usuario_nome'] = $nome;
        
        // Processar foto de perfil
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            $upload_dir = 'uploads/perfil/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extensao;
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_dir . $nome_arquivo)) {
                $stmt = $conexao->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->bind_param("si", $nome_arquivo, $usuario_id);
                $stmt->execute();
            }
        }
        
        // Atualizar senha se fornecida
        if (!empty($_POST['nova_senha'])) {
            $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->bind_param("si", $nova_senha, $usuario_id);
            $stmt->execute();
        }
        
        exibirAlerta('success', 'Perfil atualizado com sucesso!');
    } else {
        exibirAlerta('error', 'Erro ao atualizar perfil.');
    }
    
    $conexao->close();
    header("Location: admin-perfil.php");
    exit;
}

// Buscar dados do usuário
$conexao = conectarBD();
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - <?php echo SISTEMA_NOME; ?></title>
    <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgba(16, 147, 73, 0.15) 0%, rgba(16, 147, 73, 0.15) 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 66%, rgba(221, 1, 1, 0.15) 66%, rgba(221, 1, 1, 0.15) 100%) !important;
            background-color: #f5f5f5 !important;
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
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .theme-alemanha .card {
            background: #000000;
            box-shadow: 0 5px 15px rgba(255, 206, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, #109349, #0d7a3a);
            color: white;
            padding: 20px;
        }
        
        .theme-alemanha .card-header {
            background: linear-gradient(135deg, #000000, #DD0100);
        }
        
        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .theme-alemanha .card-body {
            color: white;
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
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .theme-alemanha .form-control {
            background: #1a1a1a;
            border-color: #333;
            color: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #109349;
        }
        
        .theme-alemanha .form-control:focus {
            border-color: #FFCE00;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #109349;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d7a3a;
            transform: translateY(-2px);
        }
        
        .theme-alemanha .btn-primary {
            background: #FFCE00;
            color: #000;
        }
        
        .theme-alemanha .btn-primary:hover {
            background: #daaf03;
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
        }
    </style>
</head>
<body>
<?php require_once 'admin-menu.php'; ?>

<div class="content">
    <div class="page-header">
        <div class="mobile-welcome-text">Meu Perfil</div>
        <h1><i class="fas fa-user-cog"></i> Meu Perfil</h1>
        <p>Gerencie suas informações pessoais e configurações</p>
    </div>
    
    <?php mostrarAlerta(); ?>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Token de Recuperação (4 dígitos)</label>
                    <input type="text" name="token_recuperacao" class="form-control" maxlength="4" pattern="[0-9]{4}" value="<?php echo htmlspecialchars($usuario['token_recuperacao'] ?? ''); ?>" placeholder="Ex: 1234">
                    <small style="color: #666; font-size: 0.85rem;">Use este token para recuperar sua senha</small>
                </div>
                
                <div class="form-group">
                    <label>Foto de Perfil</label>
                    <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Nova Senha (deixe em branco para manter a atual)</label>
                    <input type="password" name="nova_senha" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
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
</script>
</body>
</html>

