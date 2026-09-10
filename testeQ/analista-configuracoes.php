<?php
require_once 'config.php';
verificarLogin();

if (!isset($_SESSION['analista_id'])) {
    header("Location: dashboard.php");
    exit;
}

$conexao = conectarBD();

// Buscar configurações do analista (criar tabela se necessário)
$stmt = $conexao->prepare("SELECT * FROM analista_configuracoes WHERE analista_id = ?");
$stmt->bind_param("i", $_SESSION['analista_id']);
$stmt->execute();
$config = $stmt->get_result()->fetch_assoc();

// Configurações padrão se não existir
if (!$config) {
    $config = [
        'notificacao_email' => 1,
        'notificacao_sms' => 0,
        'horario_inicio' => '08:00',
        'horario_fim' => '18:00',
        'dias_trabalho' => 'segunda,terca,quarta,quinta,sexta',
        'auto_aceitar' => 0,
        'tempo_resposta' => 24
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Analista</title>
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
                <a class="nav-link" href="analista-perfil.php"><i class="fas fa-user me-1"></i>Perfil</a>
                <a class="nav-link active" href="analista-configuracoes.php"><i class="fas fa-cog me-1"></i>Configurações</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php mostrarAlerta(); ?>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog me-2"></i>Configurações do Analista</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="processar-configuracoes-analista.php">
                            
                            <!-- Notificações -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-bell me-2"></i>Notificações
                            </h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notificacao_email" value="1" 
                                               <?= $config['notificacao_email'] ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <i class="fas fa-envelope me-1"></i>Notificações por Email
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notificacao_sms" value="1" 
                                               <?= $config['notificacao_sms'] ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <i class="fas fa-sms me-1"></i>Notificações por SMS
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Horário de Trabalho -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-clock me-2"></i>Horário de Trabalho
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Horário de Início</label>
                                    <input type="time" class="form-control" value="<?= $config['horario_inicio'] ?>" readonly>
                                    <small class="text-muted">Para alterar, solicite aos administradores</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Horário de Fim</label>
                                    <input type="time" class="form-control" value="<?= $config['horario_fim'] ?>" readonly>
                                    <small class="text-muted">Para alterar, solicite aos administradores</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dias de Trabalho</label>
                                <div class="row">
                                    <?php 
                                    $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
                                    $dias_nomes = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
                                    $dias_selecionados = explode(',', $config['dias_trabalho']);
                                    
                                    for ($i = 0; $i < count($dias); $i++): ?>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   value="<?= $dias[$i] ?>" <?= in_array($dias[$i], $dias_selecionados) ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label"><?= $dias_nomes[$i] ?></label>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">Para alterar dias/horários, use o botão abaixo</small>
                            </div>

                            <div class="mb-4">
                                <button type="button" class="btn btn-warning" onclick="abrirModalSolicitacao()">
                                    <i class="fas fa-clock me-1"></i>Solicitar Mudança de Horário
                                </button>
                            </div>

                            <!-- Preferências de Diagnóstico -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-stethoscope me-2"></i>Preferências de Diagnóstico
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_aceitar" value="1" 
                                               <?= $config['auto_aceitar'] ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            Auto-aceitar diagnósticos urgentes
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempo de resposta (horas)</label>
                                    <select class="form-control" name="tempo_resposta">
                                        <option value="2" <?= $config['tempo_resposta'] == 2 ? 'selected' : '' ?>>2 horas</option>
                                        <option value="4" <?= $config['tempo_resposta'] == 4 ? 'selected' : '' ?>>4 horas</option>
                                        <option value="8" <?= $config['tempo_resposta'] == 8 ? 'selected' : '' ?>>8 horas</option>
                                        <option value="24" <?= $config['tempo_resposta'] == 24 ? 'selected' : '' ?>>24 horas</option>
                                        <option value="48" <?= $config['tempo_resposta'] == 48 ? 'selected' : '' ?>>48 horas</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="analista-dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Voltar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para solicitar mudança de horário -->
    <div class="modal fade" id="modalSolicitacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Solicitar Mudança de Horário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="processar-solicitacao-horario.php">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Novo Horário de Início</label>
                                <input type="time" class="form-control" name="horario_inicio_solicitado" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Novo Horário de Fim</label>
                                <input type="time" class="form-control" name="horario_fim_solicitado" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Novos Dias de Trabalho</label>
                            <div class="row">
                                <?php for ($i = 0; $i < count($dias); $i++): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias_trabalho_solicitado[]" value="<?= $dias[$i] ?>">
                                        <label class="form-check-label"><?= $dias_nomes[$i] ?></label>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Justificativa *</label>
                            <textarea class="form-control" name="justificativa" rows="3" required placeholder="Explique o motivo da solicitação..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Enviar Solicitação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirModalSolicitacao() {
            new bootstrap.Modal(document.getElementById('modalSolicitacao')).show();
        }
    </script>
</body>
</html>