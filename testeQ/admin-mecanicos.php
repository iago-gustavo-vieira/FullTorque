<?php
require_once 'config.php';
verificarLogin();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] != 'admin') {
    header("Location: acesso-negado.php");
    exit;
}

// Processar exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $conexao = conectarBD();
    
    // Verificar se o mecânico está associado a algum agendamento
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM agendamentos WHERE mecanico_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        setMensagem("Não é possível excluir este mecânico pois ele está associado a agendamentos.", "erro");
    } else {
        // Buscar informações da foto para excluir
        $stmt = $conexao->prepare("SELECT foto FROM mecanicos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($foto);
        $stmt->fetch();
        $stmt->close();
        
        // Excluir o registro
        $stmt = $conexao->prepare("DELETE FROM mecanicos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Se houver foto, excluir o arquivo
            if ($foto && file_exists("uploads/mecanicos/" . $foto)) {
                unlink("uploads/mecanicos/" . $foto);
            }
            setMensagem("Mecânico excluído com sucesso!", "sucesso");
        } else {
            setMensagem("Erro ao excluir mecânico: " . $conexao->error, "erro");
        }
        $stmt->close();
    }
    
    $conexao->close();
    header("Location: admin-mecanicos.php");
    exit;
}

// Buscar mecânicos
$conexao = conectarBD();
$mecanicos = $conexao->query("SELECT * FROM mecanicos ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$conexao->close();

// Título da página
$titulo = "Gerenciar Mecânicos";
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
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .mecanico-foto {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
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
                min-width: 700px;
                font-size: 13px;
            }
            
            th, td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            
            .mecanico-foto {
                width: 45px;
                height: 45px;
            }
            
            .actions {
                flex-direction: row;
                gap: 5px;
            }
            
            .btn-sm {
                padding: 6px 10px;
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
            
            .mecanico-foto {
                width: 40px;
                height: 40px;
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
            <div class="mobile-welcome-text">Gerenciar Mecânicos</div>
            <h1><i class="fas fa-users-cog"></i> Gerenciar Mecânicos</h1>
            <p>Cadastre e gerencie todos os mecânicos da oficina</p>
            <div class="header-content" style="display: none;">
                <div class="header-info">
                    <h1><i class="fas fa-users-cog"></i> Gerenciar Mecânicos</h1>
                    <p class="header-subtitle">Cadastre e gerencie todos os mecânicos da oficina</p>
                </div>

            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Mecânicos Cadastrados</h3>
                <a href="admin-mecanico-form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Mecânico
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Especialidade</th>
                                <th>Experiência</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mecanicos)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px 15px;">
                                        <i class="fas fa-users-cog" style="font-size: 3rem; color: #ddd; margin-bottom: 15px; display: block;"></i>
                                        <h3 style="color: #666; margin-bottom: 10px;">Nenhum mecânico cadastrado</h3>
                                        <p style="color: #999;">Comece cadastrando seu primeiro mecânico.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mecanicos as $mecanico): ?>
                                    <tr>
                                        <td>
                                            <?php if ($mecanico['foto']): ?>
                                                <img src="uploads/mecanicos/<?php echo $mecanico['foto']; ?>" alt="Foto de <?php echo $mecanico['nome']; ?>" class="mecanico-foto">
                                            <?php else: ?>
                                                <div class="mecanico-foto" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="font-size: 24px; color: #888;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $mecanico['nome']; ?></td>
                                        <td><?php echo $mecanico['especialidade']; ?></td>
                                        <td><?php echo $mecanico['experiencia']; ?> anos</td>
                                        <td>
                                            <span class="status-badge <?php echo $mecanico['ativo'] == 1 ? 'status-ativo' : 'status-inativo'; ?>">
                                                <?php echo $mecanico['ativo'] == 1 ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="admin-mecanico-form.php?id=<?php echo $mecanico['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin-mecanicos.php?excluir=<?php echo $mecanico['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este mecânico?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
