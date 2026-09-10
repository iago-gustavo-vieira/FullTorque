<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

$conexao = conectarBD();

// Buscar dados do analista
$stmt = $conexao->prepare("SELECT u.*, a.especialidade FROM usuarios u 
                          LEFT JOIN analistas a ON u.analista_id = a.id 
                          WHERE u.id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$analista = $stmt->get_result()->fetch_assoc();

// Buscar estatísticas
$stmt_stats = $conexao->prepare("SELECT 
    COUNT(*) as total_diagnosticos,
    COUNT(CASE WHEN status = 'analisado' THEN 1 END) as pendentes_resposta,
    COUNT(CASE WHEN status = 'aceito' THEN 1 END) as aceitos,
    COUNT(CASE WHEN status = 'realizado' THEN 1 END) as realizados
    FROM relatorios_cliente WHERE analista_id = ?");
$stmt_stats->bind_param("i", $_SESSION['analista_id']);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Analista</title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="analista-dashboard.php">
                <i class="fas fa-tools me-2"></i>Auto Service - Analista
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="analista-dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                <a class="nav-link active" href="analista-perfil.php"><i class="fas fa-user me-1"></i>Perfil</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h5><?= htmlspecialchars($analista['nome']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($analista['especialidade'] ?? 'Analista Geral') ?></p>
                        <p class="small text-muted">Membro desde <?= formatarData($analista['data_cadastro'], 'd/m/Y') ?></p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?= $stats['total_diagnosticos'] ?></h4>
                                <small>Total</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning"><?= $stats['pendentes_resposta'] ?></h4>
                                <small>Pendentes</small>
                            </div>
                            <div class="col-6 mt-2">
                                <h4 class="text-success"><?= $stats['aceitos'] ?></h4>
                                <small>Aceitos</small>
                            </div>
                            <div class="col-6 mt-2">
                                <h4 class="text-info"><?= $stats['realizados'] ?></h4>
                                <small>Realizados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit me-2"></i>Editar Perfil</h5>
                    </div>
                    <div class="card-body">
                        <form id="formPerfil" method="POST" action="processar-perfil-analista.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($analista['nome']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($analista['email']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($analista['telefone']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Especialidade</label>
                                        <select class="form-control" name="especialidade">
                                            <option value="Diagnóstico Geral" <?= $analista['especialidade'] == 'Diagnóstico Geral' ? 'selected' : '' ?>>Diagnóstico Geral</option>
                                            <option value="Motor" <?= $analista['especialidade'] == 'Motor' ? 'selected' : '' ?>>Motor</option>
                                            <option value="Transmissão" <?= $analista['especialidade'] == 'Transmissão' ? 'selected' : '' ?>>Transmissão</option>
                                            <option value="Freios" <?= $analista['especialidade'] == 'Freios' ? 'selected' : '' ?>>Freios</option>
                                            <option value="Suspensão" <?= $analista['especialidade'] == 'Suspensão' ? 'selected' : '' ?>>Suspensão</option>
                                            <option value="Elétrica" <?= $analista['especialidade'] == 'Elétrica' ? 'selected' : '' ?>>Elétrica</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6>Alterar Senha</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nova Senha</label>
                                        <input type="password" class="form-control" name="nova_senha" placeholder="Deixe em branco para manter atual">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Nova Senha</label>
                                        <input type="password" class="form-control" name="confirmar_senha" placeholder="Confirme a nova senha">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="analista-dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Voltar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('formPerfil').addEventListener('submit', function(e) {
            const novaSenha = document.querySelector('input[name="nova_senha"]').value;
            const confirmarSenha = document.querySelector('input[name="confirmar_senha"]').value;
            
            if (novaSenha && novaSenha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>