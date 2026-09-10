<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

$titulo = "Central de Notificações";

$conexao = conectarBD();
$conexao->set_charset("utf8mb4");

// Processar envio de notificação
if ($_POST && isset($_POST['enviar_notificacao'])) {
    $titulo_notif = limparDados($_POST['titulo']);
    $mensagem = limparDados($_POST['mensagem']);
    $tipo_envio = $_POST['tipo_envio'];
    $usuarios_selecionados = $_POST['usuarios'] ?? [];
    
    if (empty($titulo_notif) || empty($mensagem)) {
        exibirAlerta('error', 'Título e mensagem são obrigatórios!');
    } else {
        $count = 0;
        
        if ($tipo_envio == 'todos') {
            $stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, lida, data_criacao) SELECT id, 'sistema', ?, ?, 0, NOW() FROM usuarios WHERE nivel_acesso != 'admin'");
            $stmt->bind_param("ss", $titulo_notif, $mensagem);
            $stmt->execute();
            $count = $stmt->affected_rows;
        } else {
            if (empty($usuarios_selecionados)) {
                exibirAlerta('error', 'Selecione pelo menos um cliente!');
                goto fim;
            }
            
            $stmt = $conexao->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, lida, data_criacao) VALUES (?, 'sistema', ?, ?, 0, NOW())");
            foreach ($usuarios_selecionados as $user_id) {
                $user_id = intval($user_id);
                $stmt->bind_param("iss", $user_id, $titulo_notif, $mensagem);
                if ($stmt->execute()) $count++;
            }
        }
        
        exibirAlerta('success', "✓ Notificação enviada para $count cliente(s)!");
    }
    fim:
}

// Buscar estatísticas
$stats = $conexao->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN lida = 0 THEN 1 ELSE 0 END) as nao_lidas,
        SUM(CASE WHEN DATE(data_criacao) = CURDATE() THEN 1 ELSE 0 END) as hoje
    FROM notificacoes
")->fetch_assoc();

$clientes = $conexao->query("SELECT id, nome, email FROM usuarios WHERE nivel_acesso != 'admin' ORDER BY nome");
$ultimas_notif = $conexao->query("SELECT n.*, u.nome FROM notificacoes n JOIN usuarios u ON n.usuario_id = u.id ORDER BY n.data_criacao DESC LIMIT 10");
?>

<?php require_once 'admin-header-helper.php'; renderAdminHeader('Central de Notificações', 'Gerencie e envie notificações para os clientes'); ?>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

.notif-container { max-width: 1400px; margin: 0 auto; padding: 20px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #109349, #0d7a3e);
    color: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s;
}

.stat-card:hover { transform: translateY(-5px); }

.theme-alemanha .stat-card {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #000;
}

.stat-icon { font-size: 2.5rem; opacity: 0.9; }

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-content p {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.theme-alemanha .card {
    background: #1a1a1a;
    border: 2px solid #DD0100;
    box-shadow: 0 5px 15px rgba(255, 206, 0, 0.2);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.theme-alemanha .card-header { border-bottom-color: #DD0100; }

.card-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #2c3e50;
}

.theme-alemanha .card-header h2 { color: white; }

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.template-btn {
    background: white;
    border: 2px solid #e0e0e0;
    padding: 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.template-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.template-btn.success { border-color: #109349; }
.template-btn.success:hover { background: #109349; color: white; }

.template-btn.warning { border-color: #ffc107; }
.template-btn.warning:hover { background: #ffc107; color: white; }

.template-btn.danger { border-color: #dc3545; }
.template-btn.danger:hover { background: #dc3545; color: white; }

.template-btn.info { border-color: #17a2b8; }
.template-btn.info:hover { background: #17a2b8; color: white; }

.theme-alemanha .template-btn {
    background: #2a2a2a;
    border-color: #DD0100;
    color: white;
}

.theme-alemanha .template-btn:hover {
    background: #FFCE00;
    border-color: #FFCE00;
    color: #000;
}

.template-icon { font-size: 2rem; margin-bottom: 10px; }

.form-group { margin-bottom: 20px; }

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.theme-alemanha .form-group label { color: #FFCE00; }

.form-control {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}

.theme-alemanha .form-control {
    background: #2a2a2a;
    border-color: #DD0100;
    color: white;
}

.form-control:focus {
    outline: none;
    border-color: #109349;
}

.theme-alemanha .form-control:focus { border-color: #FFCE00; }

.clientes-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 2px solid #109349;
    max-height: 300px;
    overflow-y: auto;
}

.theme-alemanha .clientes-box {
    background: #2a2a2a;
    border-color: #FFCE00;
}

.cliente-item {
    display: block;
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.2s;
}

.cliente-item:hover { background: white; }

.theme-alemanha .cliente-item { color: white; }
.theme-alemanha .cliente-item:hover { background: rgba(255, 206, 0, 0.1); }

.btn {
    background: #109349;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn:hover {
    background: #0d7a3e;
    transform: translateY(-2px);
}

.theme-alemanha .btn {
    background: #FFCE00;
    color: #000;
}

.theme-alemanha .btn:hover { background: #e6b800; }

.btn-secondary { background: #6c757d; }
.btn-secondary:hover { background: #5a6268; }

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.notif-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #109349;
}

.theme-alemanha .notif-item {
    background: #2a2a2a;
    border-left-color: #FFCE00;
}

.notif-item strong {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
}

.theme-alemanha .notif-item strong { color: #FFCE00; }

.notif-item small { color: #666; }
.theme-alemanha .notif-item small { color: #999; }

@media (max-width: 768px) {
    .notif-container { padding: 10px; }
    .stats-grid { grid-template-columns: 1fr; }
    .templates-grid { grid-template-columns: 1fr; }
    .card { padding: 15px; }
}
</style>

<div class="notif-container">
    <?php mostrarAlerta(); ?>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-bell"></i></div>
            <div class="stat-content">
                <h3>Total Enviadas</h3>
                <p><?php echo $stats['total']; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-content">
                <h3>Não Lidas</h3>
                <p><?php echo $stats['nao_lidas']; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-content">
                <h3>Enviadas Hoje</h3>
                <p><?php echo $stats['hoje']; ?></p>
            </div>
        </div>
    </div>

    <!-- Templates Rápidos -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bolt"></i>
            <h2>Templates Rápidos</h2>
        </div>
        <div class="templates-grid">
            <div class="template-btn success" onclick="aplicarTemplate('servico_aceito')">
                <div class="template-icon"><i class="fas fa-check-circle"></i></div>
                <strong>Serviço Aceito</strong>
                <small>Confirmação automática</small>
            </div>
            <div class="template-btn info" onclick="aplicarTemplate('agendamento_confirmado')">
                <div class="template-icon"><i class="fas fa-calendar-check"></i></div>
                <strong>Agendamento OK</strong>
                <small>Confirmação de data</small>
            </div>
            <div class="template-btn warning" onclick="aplicarTemplate('veiculo_pronto')">
                <div class="template-icon"><i class="fas fa-car"></i></div>
                <strong>Veículo Pronto</strong>
                <small>Pronto para retirada</small>
            </div>
            <div class="template-btn danger" onclick="aplicarTemplate('urgente')">
                <div class="template-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <strong>Urgente</strong>
                <small>Contato necessário</small>
            </div>
        </div>
    </div>

    <!-- Formulário de Envio -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-paper-plane"></i>
            <h2>Enviar Nova Notificação</h2>
        </div>
        <form method="POST" id="formNotificacao">
            <div class="form-group">
                <label>Título da Notificação</label>
                <input type="text" name="titulo" id="titulo" class="form-control" placeholder="Ex: Serviço Aprovado" required>
            </div>

            <div class="form-group">
                <label>Mensagem</label>
                <textarea name="mensagem" id="mensagem" class="form-control" rows="6" placeholder="Digite a mensagem..." required></textarea>
            </div>

            <div class="form-group">
                <label>Destinatários</label>
                <select name="tipo_envio" class="form-control" onchange="toggleClientes(this.value)">
                    <option value="selecionados">Clientes Específicos</option>
                    <option value="todos">Todos os Clientes</option>
                </select>
            </div>

            <div id="clientesBox" class="form-group">
                <label style="display: flex; justify-content: space-between; align-items: center;">
                    Selecionar Clientes
                    <div>
                        <button type="button" onclick="selecionarTodos()" class="btn" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;">Todos</button>
                        <button type="button" onclick="limparTodos()" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;">Limpar</button>
                    </div>
                </label>
                <div class="clientes-box">
                    <?php while ($cliente = $clientes->fetch_assoc()): ?>
                    <label class="cliente-item">
                        <input type="checkbox" name="usuarios[]" value="<?php echo $cliente['id']; ?>">
                        <strong><?php echo htmlspecialchars($cliente['nome']); ?></strong>
                        <small>(<?php echo htmlspecialchars($cliente['email']); ?>)</small>
                    </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <button type="submit" name="enviar_notificacao" class="btn" style="width: 100%; padding: 15px; font-size: 16px;">
                <i class="fas fa-paper-plane"></i> Enviar Notificação
            </button>
        </form>
    </div>

    <!-- Últimas Notificações -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i>
            <h2>Últimas Notificações Enviadas</h2>
        </div>
        <div class="notif-list">
            <?php if ($ultimas_notif->num_rows > 0): ?>
                <?php while ($notif = $ultimas_notif->fetch_assoc()): ?>
                <div class="notif-item">
                    <strong><?php echo htmlspecialchars($notif['titulo']); ?></strong>
                    <p style="margin: 5px 0;"><?php echo nl2br(htmlspecialchars(substr($notif['mensagem'], 0, 100))); ?>...</p>
                    <small>
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($notif['nome']); ?> • 
                        <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['data_criacao'])); ?>
                    </small>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">Nenhuma notificação enviada ainda</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const templates = {
    servico_aceito: {
        titulo: 'Serviço Aceito ✓',
        mensagem: 'Olá! Temos uma ótima notícia!\n\nSeu serviço foi aceito por nossa equipe e já está em andamento.\n\nNossos técnicos especializados estão trabalhando no seu veículo com todo cuidado e atenção que ele merece.\n\nEm breve você receberá atualizações sobre o andamento.\n\nQualquer dúvida, estamos à disposição!'
    },
    agendamento_confirmado: {
        titulo: 'Agendamento Confirmado ✓',
        mensagem: 'Seu agendamento foi confirmado com sucesso!\n\nEstamos aguardando você na data e horário combinados.\n\nPor favor, chegue com alguns minutos de antecedência.\n\nSe precisar remarcar, entre em contato conosco o quanto antes.\n\nAté breve!'
    },
    veiculo_pronto: {
        titulo: 'Veículo Pronto para Retirada 🚗',
        mensagem: 'Ótimas notícias!\n\nSeu veículo está pronto e aguardando sua retirada.\n\nTodos os serviços foram concluídos com sucesso e o veículo passou por uma revisão final de qualidade.\n\nVocê pode retirá-lo em nosso horário de atendimento.\n\nAguardamos você!'
    },
    urgente: {
        titulo: '⚠️ CONTATO URGENTE NECESSÁRIO',
        mensagem: 'ATENÇÃO!\n\nPrecisamos falar com você com urgência sobre seu veículo.\n\nPor favor, entre em contato conosco o mais rápido possível:\n\n📞 Telefone: [SEU TELEFONE]\n💬 WhatsApp: [SEU WHATSAPP]\n\nÉ importante que você nos procure hoje mesmo.\n\nAguardamos seu contato!'
    }
};

function aplicarTemplate(tipo) {
    const template = templates[tipo];
    if (template) {
        document.getElementById('titulo').value = template.titulo;
        document.getElementById('mensagem').value = template.mensagem;
        
        const textarea = document.getElementById('mensagem');
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
        textarea.style.borderColor = '#109349';
        setTimeout(() => textarea.style.borderColor = '', 1000);
    }
}

function toggleClientes(tipo) {
    document.getElementById('clientesBox').style.display = tipo === 'selecionados' ? 'block' : 'none';
}

function selecionarTodos() {
    document.querySelectorAll('input[name="usuarios[]"]').forEach(cb => cb.checked = true);
}

function limparTodos() {
    document.querySelectorAll('input[name="usuarios[]"]').forEach(cb => cb.checked = false);
}

document.getElementById('formNotificacao').addEventListener('submit', function(e) {
    const tipoEnvio = document.querySelector('select[name="tipo_envio"]').value;
    
    if (tipoEnvio === 'selecionados') {
        const selecionados = document.querySelectorAll('input[name="usuarios[]"]:checked');
        if (selecionados.length === 0) {
            e.preventDefault();
            alert('Selecione pelo menos um cliente!');
            return false;
        }
    }
});

// Auto-resize textarea
document.getElementById('mensagem').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

<?php renderAdminFooter(); ?>
