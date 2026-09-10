<?php

require_once 'config.php';
verificarLogin();

$conexao = conectarBD();
$usuario_id = $_SESSION['usuario_id'];

// Verificar se coluna foto_perfil existe
$colunas = $conexao->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
if ($colunas->num_rows == 0) {
    $conexao->query("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL");
}

// Buscar dados do usuário incluindo foto
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Se não encontrou, criar dados básicos
if (!$usuario) {
    $usuario = [
        'id' => $usuario_id,
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? '',
        'telefone' => '',
        'cpf' => '',
        'nivel_acesso' => $_SESSION['usuario_nivel'] ?? 'cliente',
        'data_cadastro' => date('Y-m-d H:i:s'),
        'foto_perfil' => null
    ];
}

// DEBUG TEMPORÁRIO - Descomente para testar
// echo "<pre>Foto no banco: " . ($usuario['foto_perfil'] ?? 'NULL') . "</pre>";
// echo "<pre>Arquivo existe: " . (file_exists('uploads/perfil/' . ($usuario['foto_perfil'] ?? '')) ? 'SIM' : 'NAO') . "</pre>";
// die();

// Processar upload de foto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto_perfil'])) {
    $upload_dir = 'uploads/perfil/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if ($_FILES['foto_perfil']['error'] == 0) {
        $file = $_FILES['foto_perfil'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= 5000000) {
            // Apagar foto antiga
            if (!empty($usuario['foto_perfil']) && file_exists($upload_dir . $usuario['foto_perfil'])) {
                unlink($upload_dir . $usuario['foto_perfil']);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'perfil_' . $usuario_id . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $stmt = $conexao->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->bind_param("si", $filename, $usuario_id);
                if ($stmt->execute()) {
                    header("Location: perfil.php?success=foto");
                    exit;
                }
            }
        }
    }
}

// Processar exclusão de foto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apagar_foto'])) {
    $upload_dir = 'uploads/perfil/';
    if (!empty($usuario['foto_perfil']) && file_exists($upload_dir . $usuario['foto_perfil'])) {
        unlink($upload_dir . $usuario['foto_perfil']);
    }
    $stmt = $conexao->prepare("UPDATE usuarios SET foto_perfil = NULL WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    if ($stmt->execute()) {
        $usuario['foto_perfil'] = null;
        header("Location: perfil.php?success=delete");
        exit;
    }
}

// Verifica se o formulário foi enviado
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['atualizar_perfil'])) {
        // Atualização de dados do perfil
        $nome = limparDados($_POST['nome']);
        $email = limparDados($_POST['email']);
        $telefone = limparDados($_POST['telefone']);
        
        // Validação básica
        if (empty($nome) || empty($email) || empty($telefone)) {
            $mensagem = "Preencha todos os campos obrigatórios.";
            $tipo_mensagem = "danger";
        } else {
            // Verifica se o email já está em uso por outro usuário
            $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $mensagem = "Este email já está sendo usado por outro usuário.";
                $tipo_mensagem = "danger";
            } else {
                // Atualiza os dados do usuário
                $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nome, $email, $telefone, $usuario_id);
                
                if ($stmt->execute()) {
                    // Atualiza os dados da sessão
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario_email'] = $email;
                    
                    $mensagem = "Perfil atualizado com sucesso!";
                    $tipo_mensagem = "success";
                    
                    // Registra a ação no log
                    registrarLog('perfil_atualizado', 'Dados do perfil atualizados');
                    
                    // Atualiza os dados do usuário para exibição
                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;
                    $usuario['telefone'] = $telefone;
                } else {
                    $mensagem = "Erro ao atualizar o perfil: " . $conexao->error;
                    $tipo_mensagem = "danger";
                }
            }
        }
    } elseif (isset($_POST['alterar_senha'])) {
        // Alteração de senha
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        
        // Validação básica
        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $mensagem = "Preencha todos os campos de senha.";
            $tipo_mensagem = "danger";
        } elseif ($nova_senha !== $confirmar_senha) {
            $mensagem = "A nova senha e a confirmação não coincidem.";
            $tipo_mensagem = "danger";
        } elseif (strlen($nova_senha) < 6) {
            $mensagem = "A nova senha deve ter pelo menos 6 caracteres.";
            $tipo_mensagem = "danger";
        } else {
            // Busca a senha atual do usuário
            $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $senha_hash_atual = $result->fetch_assoc()['senha'];
            
            // Verifica se a senha atual está correta
            if (password_verify($senha_atual, $senha_hash_atual)) {
                // Criptografa a nova senha
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                // Atualiza a senha no banco de dados
                $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->bind_param("si", $senha_hash, $usuario_id);
                
                if ($stmt->execute()) {
                    $mensagem = "Senha alterada com sucesso!";
                    $tipo_mensagem = "success";
                    
                    // Registra a ação no log
                    registrarLog('senha_alterada', 'Senha alterada pelo usuário');
                } else {
                    $mensagem = "Erro ao alterar a senha: " . $conexao->error;
                    $tipo_mensagem = "danger";
                }
            } else {
                $mensagem = "Senha atual incorreta.";
                $tipo_mensagem = "danger";
            }
        }
    }
}

// Conta os veículos do usuário
if (colunaExiste($conexao, 'veiculos', 'usuario_id')) {
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM veiculos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $veiculos = $result->fetch_assoc()['total'];
} else {
    $veiculos = 0;
}

// Conta os agendamentos do usuário
if (tabelaExiste($conexao, 'agendamentos') && colunaExiste($conexao, 'agendamentos', 'usuario_id')) {
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agendamentos = $result->fetch_assoc()['total'];
} else {
    $agendamentos = 0;
}

$conexao->close();

// Inclui o cabeçalho
include 'header.php';

// Exibe mensagem de alerta personalizada
if (!empty($mensagem)) {
    echo '<div class="alert alert-' . $tipo_mensagem . '">';
    echo '<i class="fas fa-' . ($tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle') . '"></i>';
    echo $mensagem;
    echo '</div>';
}
?>

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

.profile-container {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
    width: 100%;
}

.profile-sidebar {
    width: 350px;
    min-height: 600px;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    padding: 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.profile-sidebar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(135deg, #109349, #0d7a3a);
    z-index: 1;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background-color: #109349;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    position: relative;
    z-index: 2;
    border: 5px solid white;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    overflow: hidden;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.profile-avatar i {
    font-size: 60px;
    color: white;
}

.avatar-upload {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #109349;
    color: white;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 3;
    transition: all 0.3s;
}

.avatar-upload:hover {
    background: #0d7a3a;
    transform: scale(1.1);
}

.avatar-delete {
    position: absolute;
    bottom: 5px;
    left: 5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 3;
    transition: all 0.3s;
}

.avatar-delete:hover {
    background: #c82333;
    transform: scale(1.1);
}

.upload-input {
    display: none;
}

.foto-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    justify-content: center;
}

.btn-foto {
    padding: 8px 15px;
    border-radius: 8px;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
}

.btn-editar-foto {
    background: #109349;
    color: white;
}

.btn-editar-foto:hover {
    background: #0d7a3a;
}

.btn-apagar-foto {
    background: #dc3545;
    color: white;
}

.btn-apagar-foto:hover {
    background: #c82333;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
    background-color: rgba(52, 152, 219, 0.1);
    color: #109349;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--secondary-color);
    position: relative;
    z-index: 2;
}

.profile-email {
    color: #666;
    margin-bottom: 12px;
    font-size: 1rem;
    position: relative;
    z-index: 2;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 25px;
    position: relative;
    z-index: 2;
}

.profile-sidebar .stat-item {
    background: linear-gradient(135deg, #fff, #f8f9fa);
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.profile-sidebar .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #109349;
    margin-bottom: 5px;
}

.profile-sidebar .stat-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.profile-content {
    flex: 1;
    display: flex;
    gap: 30px;
    min-height: 600px;
}

.content-left {
    flex: 1.5;
    min-width: 0;
}

.content-right {
    flex: 1;
    min-width: 0;
}

.security-tips {
    display: flex;
    flex-direction: column;
    gap: 20px;
    height: 100%;
    justify-content: space-between;
}

.tip-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 18px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #109349;
    flex: 1;
}

.tip-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #109349, #0d7a3a);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.tip-content h4 {
    font-size: 14px;
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 5px;
}

.tip-content p {
    font-size: 13px;
    color: #636e72;
    line-height: 1.4;
    margin: 0;
}

.card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    overflow: hidden;
    height: fit-content;
}

.content-left .card {
    min-height: 280px;
}

.content-left .card:last-child {
    min-height: 200px;
}

.content-right .card {
    height: 100%;
}

.card-header {
    background: linear-gradient(135deg, #109349, #0d7a3a);
    padding: 18px 22px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 1.2rem;
    color: white;
    margin: 0;
    font-weight: 600;
}

.card-header i {
    margin-right: 8px;
}

.card-body {
    padding: 30px;
    height: 100%;
}

.content-left .card:last-child .card-body {
    padding: 15px 30px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--secondary-color);
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: #f8f9fa;
}

.form-group input:focus {
    border-color: #109349;
    outline: none;
    box-shadow: 0 0 0 3px rgba(16, 147, 73, 0.1);
    background: white;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.password-strength {
    height: 5px;
    margin-top: 8px;
    border-radius: 5px;
    background-color: #ddd;
    overflow: hidden;
}

.password-strength-meter {
    height: 100%;
    width: 0;
    transition: width 0.3s, background-color 0.3s;
}

.password-strength-text {
    font-size: 12px;
    margin-top: 5px;
}

.mt-3 {
    margin-top: 15px;
}

.profile-info {
    margin-top: 25px;
    position: relative;
    z-index: 2;
}

.info-section {
    margin-bottom: 25px;
    text-align: left;
}

.info-section h4 {
    font-size: 14px;
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    font-size: 13px;
    color: #636e72;
}

.activity-item i {
    width: 16px;
    color: #109349;
}

.status-item {
    margin-bottom: 8px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.verified {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
}

.status-badge.active {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.status-badge.premium {
    background: rgba(241, 196, 15, 0.1);
    color: #f1c40f;
}

.quick-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(16, 147, 73, 0.05);
    border-radius: 8px;
    text-decoration: none;
    color: #2d3436;
    font-size: 13px;
    font-weight: 500;
}

.quick-link:hover {
    background: rgba(16, 147, 73, 0.1);
    color: #109349;
}

.quick-link i {
    width: 16px;
    color: #109349;
}

.info-item {
    margin-bottom: 20px;
}

.info-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--secondary-color);
    font-size: 14px;
}

.info-value-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-value {
    padding: 12px 15px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: #495057;
    font-size: 14px;
    min-height: 44px;
    display: flex;
    align-items: center;
    flex: 1;
}

.toggle-visibility {
    cursor: pointer;
    color: #109349;
    font-size: 18px;
    transition: all 0.3s;
}

.toggle-visibility:hover {
    color: #0d7a3a;
    transform: scale(1.1);
}

.info-row {
    display: flex;
    gap: 15px;
}

.info-row .info-item {
    flex: 1;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    background: linear-gradient(135deg, #109349, #0d7a3a);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.btn:hover {
    background: linear-gradient(135deg, #0d7a3a, #0a5a2a);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
}

.btn i {
    font-size: 12px;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 992px) {
    .profile-container {
        flex-direction: column;
        padding: 15px;
    }
    
    .profile-sidebar {
        width: 100%;
    }
}

@media (max-width: 1200px) {
    .profile-container {
        max-width: 100%;
        padding: 15px;
    }
    
    .profile-sidebar {
        width: 300px;
    }
}

@media (max-width: 992px) {
    .profile-container {
        flex-direction: column;
    }
    
    .profile-sidebar {
        width: 100%;
        min-height: auto;
    }
    
    .profile-content {
        flex-direction: column;
        min-height: auto;
    }
}

/* Tema Alemanha */
.theme-alemanha .profile-sidebar {
    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
}

.theme-alemanha .profile-sidebar::before {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
}

.theme-alemanha .profile-avatar {
    background-color: #FFCE00;
    border-color: #1a1a1a;
}

.theme-alemanha .profile-avatar i { color: #000; }

.theme-alemanha .avatar-upload {
    background: #DD0100;
}

.theme-alemanha .avatar-upload:hover { background: #c00; }

.theme-alemanha .badge {
    background-color: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .profile-name { color: #FFCE00; }
.theme-alemanha .profile-email { color: #ccc; }

.theme-alemanha .profile-sidebar .stat-item {
    background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
}

.theme-alemanha .profile-sidebar .stat-value { color: #FFCE00; }
.theme-alemanha .profile-sidebar .stat-label { color: #ccc; }

.theme-alemanha .tip-item {
    background: #2a2a2a;
    border-left-color: #FFCE00;
}

.theme-alemanha .tip-icon {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.theme-alemanha .tip-content h4 { color: #FFCE00; }
.theme-alemanha .tip-content p { color: #ccc; }

.theme-alemanha .card {
    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
}

.theme-alemanha .card-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.theme-alemanha .card-header h2 { color: #000; }

.theme-alemanha .card-body { color: white; }

.theme-alemanha .form-group label { color: #FFCE00; }

.theme-alemanha .form-group input {
    background: #2a2a2a;
    border-color: #444;
    color: white;
}

.theme-alemanha .form-group input:focus {
    border-color: #FFCE00;
    box-shadow: 0 0 0 3px rgba(255, 206, 0, 0.1);
    background: #333;
}

.theme-alemanha .info-value {
    background: #2a2a2a;
    border-color: #444;
    color: white;
}

.theme-alemanha .toggle-visibility {
    color: #FFCE00;
}

.theme-alemanha .toggle-visibility:hover {
    color: #e6b800;
}

.theme-alemanha .btn {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.theme-alemanha .btn:hover {
    background: linear-gradient(135deg, #e6b800, #d4a600);
}

.theme-alemanha .btn-secondary {
    background: linear-gradient(135deg, #DD0100, #c00);
    color: white;
}

.theme-alemanha .btn-secondary:hover {
    background: linear-gradient(135deg, #c00, #a00);
}

.theme-alemanha .alert-success {
    background: linear-gradient(135deg, rgba(255, 206, 0, 0.2), rgba(255, 206, 0, 0.1));
    color: #FFCE00;
    border-color: #FFCE00;
}

.theme-alemanha .alert-danger {
    background: linear-gradient(135deg, rgba(221, 1, 0, 0.2), rgba(221, 1, 0, 0.1));
    color: #DD0100;
    border-color: #DD0100;
}

.theme-alemanha .activity-item { color: #ccc; }
.theme-alemanha .activity-item i { color: #FFCE00; }

.theme-alemanha .status-badge.verified {
    background: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .status-badge.active {
    background: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .status-badge.premium {
    background: rgba(221, 1, 0, 0.2);
    color: #DD0100;
}

.theme-alemanha .quick-link {
    background: rgba(255, 206, 0, 0.1);
    color: #ccc;
}

.theme-alemanha .quick-link:hover {
    background: rgba(255, 206, 0, 0.2);
    color: #FFCE00;
}

.theme-alemanha .quick-link i { color: #FFCE00; }

.theme-alemanha .info-section h4 { color: #FFCE00; }

.theme-alemanha .btn-editar-foto {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn-editar-foto:hover {
    background: #e6b800;
}

.theme-alemanha .btn-apagar-foto {
    background: #DD0100;
    color: white;
}

.theme-alemanha .btn-apagar-foto:hover {
    background: #c00;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
    }
    
    .profile-name {
        font-size: 1.5rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .tip-item {
        padding: 15px;
    }
}
</style>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-avatar" id="profile-avatar-main">
            <?php if (!empty($usuario['foto_perfil']) && file_exists('uploads/perfil/' . $usuario['foto_perfil'])): ?>
                <img src="uploads/perfil/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>?v=<?php echo time(); ?>" alt="Foto do perfil" style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>
        <div class="foto-actions">
            <button type="button" class="btn-foto btn-editar-foto" onclick="document.getElementById('foto-upload').click()">
                <i class="fas fa-camera"></i> <?php echo !empty($usuario['foto_perfil']) ? 'Alterar' : 'Adicionar'; ?>
            </button>
            <?php if (!empty($usuario['foto_perfil'])): ?>
            <button type="button" class="btn-foto btn-apagar-foto" onclick="confirmarExclusaoFoto()">
                <i class="fas fa-trash"></i> Apagar
            </button>
            <?php endif; ?>
        </div>
        <form id="foto-form" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="foto-upload" name="foto_perfil" accept="image/*" class="upload-input" onchange="previewFoto(event)">
        </form>
        <form id="apagar-foto-form" method="POST" style="display: none;">
            <input type="hidden" name="apagar_foto" value="1">
        </form>
        <h2 class="profile-name"><?php echo htmlspecialchars($usuario['nome'] ?? ''); ?></h2>
        <p class="profile-email"><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></p>
        <p><span class="badge"><?php echo ucfirst($usuario['nivel_acesso'] ?? 'cliente'); ?></span></p>
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo $veiculos; ?></div>
                <div class="stat-label">Veículos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $agendamentos; ?></div>
                <div class="stat-label">Agendamentos</div>
            </div>
        </div>
        <p class="mt-3">
            <small>Membro desde: <?php 
                if (!empty($usuario['data_cadastro'])) {
                    $data = new DateTime($usuario['data_cadastro']);
                    echo $data->format('Y');
                } else {
                    echo date('Y');
                }
            ?></small>
        </p>
        
        <div class="profile-info">
            <div class="info-section">
                <h4><i class="fas fa-chart-line"></i> Atividade Recente</h4>
                <div class="activity-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Último agendamento: <?php echo $agendamentos > 0 ? 'Há 2 dias' : 'Nenhum'; ?></span>
                </div>
                <div class="activity-item">
                    <i class="fas fa-car"></i>
                    <span>Veículos ativos: <?php echo $veiculos; ?></span>
                </div>
                <div class="activity-item">
                    <i class="fas fa-clock"></i>
                    <span>Último acesso: Hoje</span>
                </div>
            </div>
            
            <div class="info-section">
                <h4><i class="fas fa-award"></i> Status da Conta</h4>
                <div class="status-item">
                    <div class="status-badge verified">
                        <i class="fas fa-check-circle"></i> Verificado
                    </div>
                </div>
                <div class="status-item">
                    <div class="status-badge active">
                        <i class="fas fa-user-check"></i> Conta Ativa
                    </div>
                </div>
                <?php if ($agendamentos >= 5): ?>
                <div class="status-item">
                    <div class="status-badge premium">
                        <i class="fas fa-star"></i> Cliente Fiel
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <h4><i class="fas fa-link"></i> Links Rápidos</h4>
                <div class="quick-links">
                    <a href="agendamento-novo.php" class="quick-link">
                        <i class="fas fa-plus"></i> Novo Agendamento
                    </a>
                    <a href="veiculos.php" class="quick-link">
                        <i class="fas fa-car"></i> Meus Veículos
                    </a>
                    <a href="historico.php" class="quick-link">
                        <i class="fas fa-history"></i> Histórico
                    </a>
                    <a href="promocoes.php" class="quick-link">
                        <i class="fas fa-tags"></i> Promoções
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="content-left">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-edit"></i> Dados Pessoais</h2>
                </div>
                <div class="card-body">
                    <div id="dados-view">
                        <div class="info-item">
                            <label>Nome Completo</label>
                            <div class="info-value-wrapper">
                                <div class="info-value" id="nome-value" data-real="<?php echo htmlspecialchars($usuario['nome'] ?? 'Não informado'); ?>">**********</div>
                                <i class="fas fa-eye toggle-visibility" onclick="toggleVisibility('nome-value')"></i>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <label>Email</label>
                                <div class="info-value-wrapper">
                                    <div class="info-value" id="email-value" data-real="<?php echo htmlspecialchars($usuario['email'] ?? 'Não informado'); ?>">**********</div>
                                    <i class="fas fa-eye toggle-visibility" onclick="toggleVisibility('email-value')"></i>
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Telefone</label>
                                <div class="info-value-wrapper">
                                    <div class="info-value" id="telefone-value" data-real="<?php echo htmlspecialchars($usuario['telefone'] ?? 'Não informado'); ?>">**********</div>
                                    <i class="fas fa-eye toggle-visibility" onclick="toggleVisibility('telefone-value')"></i>
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>CPF</label>
                            <div class="info-value-wrapper">
                                <div class="info-value" id="cpf-value" data-real="<?php echo htmlspecialchars($usuario['cpf'] ?? 'Não informado'); ?>">**********</div>
                                <i class="fas fa-eye toggle-visibility" onclick="toggleVisibility('cpf-value')"></i>
                            </div>
                        </div>
                        <button type="button" class="btn" onclick="habilitarEdicao()">
                            <i class="fas fa-edit"></i> Editar Dados
                        </button>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="dados-form" style="display: none;">
                        <div class="form-group">
                            <label for="senha_verificacao">Digite sua senha para confirmar</label>
                            <input type="password" id="senha_verificacao" name="senha_verificacao" placeholder="Senha atual" required>
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario['cpf'] ?? ''); ?>" disabled>
                            <small>O CPF não pode ser alterado.</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="atualizar_perfil" class="btn">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelarEdicao()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" required>
                                <div class="password-strength">
                                    <div class="password-strength-meter"></div>
                                </div>
                                <div class="password-strength-text"></div>
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                        </div>
                        <button type="submit" name="alterar_senha" class="btn">
                            <i class="fas fa-key"></i> Alterar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="content-right">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-shield-alt"></i> Dicas de Segurança</h2>
                </div>
                <div class="card-body">
                    <div class="security-tips">
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Senha Forte</h4>
                                <p>Use pelo menos 8 caracteres com letras, números e símbolos.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-user-secret"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Não Compartilhe</h4>
                                <p>Nunca compartilhe sua senha ou dados pessoais com terceiros.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Atualize Regularmente</h4>
                                <p>Troque sua senha a cada 3-6 meses para maior segurança.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Redes Seguras</h4>
                                <p>Evite acessar sua conta em redes Wi-Fi públicas.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Sempre Saia</h4>
                                <p>Faça logout ao terminar de usar, especialmente em computadores compartilhados.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="tip-content">
                                <h4>Suspeitas</h4>
                                <p>Reporte imediatamente qualquer atividade suspeita em sua conta.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Verificador de força da senha
document.getElementById('nova_senha').addEventListener('input', function() {
    var password = this.value;
    var strength = 0;
    var meter = document.querySelector('.password-strength-meter');
    var text = document.querySelector('.password-strength-text');
    
    if (password.length > 0) {
        // Verifica o comprimento
        if (password.length >= 8) strength += 25;
        
        // Verifica letras minúsculas
        if (password.match(/[a-z]/)) strength += 25;
        
        // Verifica letras maiúsculas
        if (password.match(/[A-Z]/)) strength += 25;
        
        // Verifica números e caracteres especiais
        if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) strength += 25;
    }
    
    // Atualiza o medidor de força
    meter.style.width = strength + '%';
    
    // Define a cor e o texto com base na força
    if (strength < 25) {
        meter.style.backgroundColor = '#e74c3c';
        text.textContent = 'Muito fraca';
        text.style.color = '#e74c3c';
    } else if (strength < 50) {
        meter.style.backgroundColor = '#f39c12';
        text.textContent = 'Fraca';
        text.style.color = '#f39c12';
    } else if (strength < 75) {
        meter.style.backgroundColor = '#f1c40f';
        text.textContent = 'Média';
        text.style.color = '#f1c40f';
    } else if (strength < 100) {
        meter.style.backgroundColor = '#2ecc71';
        text.textContent = 'Forte';
        text.style.color = '#2ecc71';
    } else {
        meter.style.backgroundColor = '#27ae60';
        text.textContent = 'Muito forte';
        text.style.color = '#27ae60';
    }
});

// Validação de confirmação de senha
document.getElementById('confirmar_senha').addEventListener('input', function() {
    var senha = document.getElementById('nova_senha').value;
    var confirmar = this.value;
    
    if (senha !== confirmar) {
        this.style.borderColor = '#e74c3c';
    } else {
        this.style.borderColor = '#2ecc71';
    }
});

// Habilitar edição
function habilitarEdicao() {
    document.getElementById('dados-view').style.display = 'none';
    document.getElementById('dados-form').style.display = 'block';
}

// Cancelar edição
function cancelarEdicao() {
    document.getElementById('dados-form').style.display = 'none';
    document.getElementById('dados-view').style.display = 'block';
    document.getElementById('senha_verificacao').value = '';
}

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
    e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
});

// Toggle visibilidade dos dados
function toggleVisibility(elementId) {
    const element = document.getElementById(elementId);
    const icon = element.parentElement.querySelector('.toggle-visibility');
    const realValue = element.getAttribute('data-real');
    
    if (element.textContent === '**********') {
        element.textContent = realValue;
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        element.textContent = '**********';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Confirmar exclusão de foto
function confirmarExclusaoFoto() {
    if (confirm('Tem certeza que deseja apagar sua foto de perfil?')) {
        document.getElementById('apagar-foto-form').submit();
    }
}

// Preview da foto antes do upload
function previewFoto(event) {
    const file = event.target.files[0];
    if (file && file.type.match('image.*')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgSrc = e.target.result;
            
            // Atualizar foto no profile-avatar
            const profileAvatar = document.querySelector('.profile-sidebar .profile-avatar');
            if (profileAvatar) {
                profileAvatar.innerHTML = '<img src="' + imgSrc + '" alt="Foto do perfil" style="width:100%;height:100%;object-fit:cover;display:block;">';
            }
            
            // Atualizar foto no sidebar do header
            const sidebarAvatar = document.querySelector('.sidebar .user-avatar');
            if (sidebarAvatar) {
                sidebarAvatar.innerHTML = '<img src="' + imgSrc + '" alt="Foto do perfil" style="width:100%;height:100%;object-fit:cover;">';
            }
            
            // Submeter formulário
            setTimeout(function() {
                document.getElementById('foto-form').submit();
            }, 500);
        };
        reader.readAsDataURL(file);
    }
}

// Sincronizar foto do sidebar com profile-avatar ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const sidebarImg = document.querySelector('.sidebar .user-avatar img');
    const profileAvatar = document.querySelector('.profile-sidebar .profile-avatar');
    
    if (sidebarImg && profileAvatar && !profileAvatar.querySelector('img')) {
        const imgClone = sidebarImg.cloneNode(true);
        imgClone.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
        profileAvatar.innerHTML = '';
        profileAvatar.appendChild(imgClone);
    }
});
</script>

<?php
// Inclui o rodapé
include 'footer.php';
?>