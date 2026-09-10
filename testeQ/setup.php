<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auto_service');

// Configurações gerais do sistema
define('SISTEMA_NOME', 'Auto Service');
define('SISTEMA_VERSAO', '1.0.0');

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para exibir mensagens
function exibirMensagem($tipo, $mensagem) {
    echo "<div class='alert alert-$tipo'>$mensagem</div>";
}

// Verifica se o formulário foi enviado
$mensagem = '';
$tipo_mensagem = '';
$etapa = isset($_GET['etapa']) ? (int)$_GET['etapa'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['criar_banco'])) {
        // Etapa 1: Criar o banco de dados
        $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conexao->connect_error) {
            $mensagem = "Falha na conexão com o servidor MySQL: " . $conexao->connect_error;
            $tipo_mensagem = "danger";
        } else {
            // Cria o banco de dados
            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            
            if ($conexao->query($sql) === TRUE) {
                $mensagem = "Banco de dados criado com sucesso!";
                $tipo_mensagem = "success";
                $etapa = 2;
            } else {
                $mensagem = "Erro ao criar o banco de dados: " . $conexao->error;
                $tipo_mensagem = "danger";
            }
            
            $conexao->close();
        }
    } elseif (isset($_POST['criar_tabelas'])) {
        // Etapa 2: Criar as tabelas
        $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conexao->connect_error) {
            $mensagem = "Falha na conexão com o banco de dados: " . $conexao->connect_error;
            $tipo_mensagem = "danger";
        } else {
            // Define o charset para utf8
            $conexao->set_charset("utf8mb4");
            
            // Tabela de usuários
            $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                senha VARCHAR(255) NOT NULL,
                telefone VARCHAR(20) DEFAULT NULL,
                cpf VARCHAR(14) DEFAULT NULL,
                nivel_acesso ENUM('admin', 'funcionario', 'cliente') NOT NULL DEFAULT 'cliente',
                status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
                data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de veículos
            $sql_veiculos = "CREATE TABLE IF NOT EXISTS veiculos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                marca VARCHAR(50) NOT NULL,
                modelo VARCHAR(50) NOT NULL,
                ano INT(4) NOT NULL,
                placa VARCHAR(10) NOT NULL,
                cor VARCHAR(30) NOT NULL,
                quilometragem INT(11) DEFAULT NULL,
                observacoes TEXT DEFAULT NULL,
                data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY usuario_id (usuario_id),
                CONSTRAINT fk_veiculos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de serviços
            $sql_servicos = "CREATE TABLE IF NOT EXISTS servicos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT DEFAULT NULL,
                preco DECIMAL(10,2) NOT NULL,
                tempo_estimado INT(11) NOT NULL COMMENT 'Tempo em minutos',
                categoria VARCHAR(50) DEFAULT NULL,
                status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
                data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de agendamentos
            $sql_agendamentos = "CREATE TABLE IF NOT EXISTS agendamentos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                veiculo_id INT(11) NOT NULL,
                data_agendamento DATE NOT NULL,
                hora_inicio TIME NOT NULL,
                hora_fim TIME NOT NULL,
                status ENUM('agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
                observacoes TEXT DEFAULT NULL,
                data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY usuario_id (usuario_id),
                KEY veiculo_id (veiculo_id),
                CONSTRAINT fk_agendamentos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
                CONSTRAINT fk_agendamentos_veiculos FOREIGN KEY (veiculo_id) REFERENCES veiculos (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de itens do agendamento
            $sql_agendamento_itens = "CREATE TABLE IF NOT EXISTS agendamento_itens (
                id INT(11) NOT NULL AUTO_INCREMENT,
                agendamento_id INT(11) NOT NULL,
                servico_id INT(11) NOT NULL,
                preco DECIMAL(10,2) NOT NULL,
                quantidade INT(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                KEY agendamento_id (agendamento_id),
                KEY servico_id (servico_id),
                CONSTRAINT fk_agendamento_itens_agendamentos FOREIGN KEY (agendamento_id) REFERENCES agendamentos (id) ON DELETE CASCADE,
                CONSTRAINT fk_agendamento_itens_servicos FOREIGN KEY (servico_id) REFERENCES servicos (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de ordens de serviço
            $sql_ordens = "CREATE TABLE IF NOT EXISTS ordens_servico (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                veiculo_id INT(11) NOT NULL,
                agendamento_id INT(11) DEFAULT NULL,
                responsavel_id INT(11) DEFAULT NULL,
                data_abertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_conclusao DATETIME DEFAULT NULL,
                status ENUM('aberta', 'em_andamento', 'aguardando_aprovacao', 'aguardando_pecas', 'concluida', 'cancelada') NOT NULL DEFAULT 'aberta',
                diagnostico TEXT DEFAULT NULL,
                observacoes TEXT DEFAULT NULL,
                valor_total DECIMAL(10,2) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY usuario_id (usuario_id),
                KEY veiculo_id (veiculo_id),
                KEY agendamento_id (agendamento_id),
                KEY responsavel_id (responsavel_id),
                CONSTRAINT fk_ordens_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
                CONSTRAINT fk_ordens_veiculos FOREIGN KEY (veiculo_id) REFERENCES veiculos (id) ON DELETE CASCADE,
                CONSTRAINT fk_ordens_agendamentos FOREIGN KEY (agendamento_id) REFERENCES agendamentos (id) ON DELETE SET NULL,
                CONSTRAINT fk_ordens_responsaveis FOREIGN KEY (responsavel_id) REFERENCES usuarios (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de itens da ordem de serviço
            $sql_ordem_itens = "CREATE TABLE IF NOT EXISTS ordem_servico_itens (
                id INT(11) NOT NULL AUTO_INCREMENT,
                ordem_servico_id INT(11) NOT NULL,
                tipo ENUM('servico', 'peca') NOT NULL,
                descricao VARCHAR(255) NOT NULL,
                valor_unitario DECIMAL(10,2) NOT NULL,
                quantidade INT(11) NOT NULL DEFAULT 1,
                valor_total DECIMAL(10,2) NOT NULL,
                PRIMARY KEY (id),
                KEY ordem_servico_id (ordem_servico_id),
                CONSTRAINT fk_ordem_itens_ordens FOREIGN KEY (ordem_servico_id) REFERENCES ordens_servico (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de logs
            $sql_logs = "CREATE TABLE IF NOT EXISTS logs (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) DEFAULT NULL,
                acao VARCHAR(50) NOT NULL,
                descricao TEXT NOT NULL,
                ip VARCHAR(45) NOT NULL,
                data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY usuario_id (usuario_id),
                CONSTRAINT fk_logs_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de notificações
            $sql_notificacoes = "CREATE TABLE IF NOT EXISTS notificacoes (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                tipo ENUM('agendamento', 'ordem', 'veiculo', 'sistema') NOT NULL,
                titulo VARCHAR(100) NOT NULL,
                mensagem TEXT NOT NULL,
                lida TINYINT(1) NOT NULL DEFAULT 0,
                data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY usuario_id (usuario_id),
                CONSTRAINT fk_notificacoes_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de preferências do usuário
            $sql_preferencias = "CREATE TABLE IF NOT EXISTS preferencias_usuario (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                cor_primaria VARCHAR(7) DEFAULT '#3498db',
                cor_secundaria VARCHAR(7) DEFAULT '#2c3e50',
                modo_escuro TINYINT(1) NOT NULL DEFAULT 0,
                data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY usuario_id (usuario_id),
                CONSTRAINT fk_preferencias_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Tabela de permissões de usuário
            $sql_permissoes = "CREATE TABLE IF NOT EXISTS usuario_permissoes (
                id INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) NOT NULL,
                permissao VARCHAR(50) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY usuario_permissao (usuario_id, permissao),
                CONSTRAINT fk_permissoes_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Executa as consultas SQL
            $tabelas = [
                'usuarios' => $sql_usuarios,
                'veiculos' => $sql_veiculos,
                'servicos' => $sql_servicos,
                'agendamentos' => $sql_agendamentos,
                'agendamento_itens' => $sql_agendamento_itens,
                'ordens_servico' => $sql_ordens,
                'ordem_servico_itens' => $sql_ordem_itens,
                'logs' => $sql_logs,
                'notificacoes' => $sql_notificacoes,
                'preferencias_usuario' => $sql_preferencias,
                'usuario_permissoes' => $sql_permissoes
            ];
            
            $sucesso = true;
            $erros = [];
            
            foreach ($tabelas as $nome => $sql) {
                if ($conexao->query($sql) !== TRUE) {
                    $sucesso = false;
                    $erros[] = "Erro ao criar a tabela $nome: " . $conexao->error;
                }
            }
            
            if ($sucesso) {
                $mensagem = "Tabelas criadas com sucesso!";
                $tipo_mensagem = "success";
                $etapa = 3;
            } else {
                $mensagem = "Ocorreram erros ao criar as tabelas:<br>" . implode("<br>", $erros);
                $tipo_mensagem = "danger";
            }
            
            $conexao->close();
        }
    } elseif (isset($_POST['criar_admin'])) {
        // Etapa 3: Criar usuário administrador
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);
        $confirmar_senha = trim($_POST['confirmar_senha']);
        
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
            $mensagem = "Preencha todos os campos.";
            $tipo_mensagem = "danger";
        } elseif ($senha !== $confirmar_senha) {
            $mensagem = "As senhas não coincidem.";
            $tipo_mensagem = "danger";
        } elseif (strlen($senha) < 6) {
            $mensagem = "A senha deve ter pelo menos 6 caracteres.";
            $tipo_mensagem = "danger";
        } else {
            $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conexao->connect_error) {
                $mensagem = "Falha na conexão com o banco de dados: " . $conexao->connect_error;
                $tipo_mensagem = "danger";
            } else {
                // Define o charset para utf8
                $conexao->set_charset("utf8mb4");
                
                // Verifica se já existe um usuário com este email
                $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows > 0) {
                    $mensagem = "Este email já está cadastrado.";
                    $tipo_mensagem = "danger";
                } else {
                    // Criptografa a senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    // Insere o usuário administrador
                    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, 'admin')");
                    $stmt->bind_param("sss", $nome, $email, $senha_hash);
                    
                    if ($stmt->execute()) {
                        $admin_id = $conexao->insert_id;
                        
                        // Adiciona permissões de administrador
                        $permissoes = [
                            'gerenciar_usuarios',
                            'gerenciar_servicos',
                            'gerenciar_agendamentos',
                            'gerenciar_ordens',
                            'ver_relatorios',
                            'configurar_sistema'
                        ];
                        
                        $stmt = $conexao->prepare("INSERT INTO usuario_permissoes (usuario_id, permissao) VALUES (?, ?)");
                        
                        foreach ($permissoes as $permissao) {
                            $stmt->bind_param("is", $admin_id, $permissao);
                            $stmt->execute();
                        }
                        
                        // Insere alguns serviços de exemplo
                        $servicos = [
                            ['Troca de Óleo', 'Troca de óleo do motor e filtro de óleo', 120.00, 60, 'Manutenção'],
                            ['Alinhamento', 'Alinhamento computadorizado das rodas', 80.00, 45, 'Manutenção'],
                            ['Balanceamento', 'Balanceamento das rodas', 70.00, 30, 'Manutenção'],
                            ['Revisão Completa', 'Revisão completa do veículo com verificação de mais de 30 itens', 350.00, 180, 'Revisão'],
                            ['Troca de Pastilhas de Freio', 'Substituição das pastilhas de freio dianteiras', 150.00, 60, 'Freios'],
                            ['Troca de Filtro de Ar', 'Substituição do filtro de ar do motor', 50.00, 20, 'Filtros'],
                            ['Higienização do Ar Condicionado', 'Limpeza e higienização do sistema de ar condicionado', 120.00, 60, 'Ar Condicionado']
                        ];
                        
                        $stmt = $conexao->prepare("INSERT INTO servicos (nome, descricao, preco, tempo_estimado, categoria) VALUES (?, ?, ?, ?, ?)");
                        
                        foreach ($servicos as $servico) {
                            $stmt->bind_param("ssdis", $servico[0], $servico[1], $servico[2], $servico[3], $servico[4]);
                            $stmt->execute();
                        }
                        
                        $mensagem = "Usuário administrador criado com sucesso! Serviços de exemplo adicionados. Instalação concluída.";
                        $tipo_mensagem = "success";
                        $etapa = 4;
                    } else {
                        $mensagem = "Erro ao criar o usuário administrador: " . $conexao->error;
                        $tipo_mensagem = "danger";
                    }
                }
                
                $conexao->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - <?php echo SISTEMA_NOME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #ecf0f1;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.8;
        }
        
        .content {
            padding: 30px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #ddd;
            z-index: 1;
        }
        
        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #ddd;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }
        
        .step.active {
            background-color: #3498db;
            color: white;
        }
        
        .step.completed {
            background-color: #2ecc71;
            color: white;
        }
        
        .step-label {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
            color: #777;
            white-space: nowrap;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #2ecc71;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .success-icon {
            font-size: 5rem;
            color: #2ecc71;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Instalação do <?php echo SISTEMA_NOME; ?></h1>
            <p>Versão <?php echo SISTEMA_VERSAO; ?></p>
        </div>
        
        <div class="content">
            <div class="steps">
                <div class="step <?php echo $etapa >= 1 ? 'active' : ''; ?> <?php echo $etapa > 1 ? 'completed' : ''; ?>">
                    1
                    <div class="step-label">Banco de Dados</div>
                </div>
                <div class="step <?php echo $etapa >= 2 ? 'active' : ''; ?> <?php echo $etapa > 2 ? 'completed' : ''; ?>">
                    2
                    <div class="step-label">Tabelas</div>
                </div>
                <div class="step <?php echo $etapa >= 3 ? 'active' : ''; ?> <?php echo $etapa > 3 ? 'completed' : ''; ?>">
                    3
                    <div class="step-label">Administrador</div>
                </div>
                <div class="step <?php echo $etapa >= 4 ? 'active' : ''; ?>">
                    4
                    <div class="step-label">Concluído</div>
                </div>
            </div>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <div class="step-content <?php echo $etapa == 1 ? 'active' : ''; ?>">
                <h2>Criar Banco de Dados</h2>
                <p>Esta etapa irá criar o banco de dados necessário para o sistema.</p>
                <p>Certifique-se de que o servidor MySQL está em execução e que as credenciais estão corretas.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label>Host: <?php echo DB_HOST; ?></label>
                    </div>
                    <div class="form-group">
                        <label>Usuário: <?php echo DB_USER; ?></label>
                    </div>
                    <div class="form-group">
                        <label>Banco de Dados: <?php echo DB_NAME; ?></label>
                    </div>
                    
                    <button type="submit" name="criar_banco" class="btn">Criar Banco de Dados</button>
                </form>
            </div>
            
            <div class="step-content <?php echo $etapa == 2 ? 'active' : ''; ?>">
                <h2>Criar Tabelas</h2>
                <p>Esta etapa irá criar as tabelas necessárias no banco de dados.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?etapa=2'); ?>">
                    <button type="submit" name="criar_tabelas" class="btn">Criar Tabelas</button>
                </form>
            </div>
            
            <div class="step-content <?php echo $etapa == 3 ? 'active' : ''; ?>">
                <h2>Criar Usuário Administrador</h2>
                <p>Crie um usuário administrador para gerenciar o sistema.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?etapa=3'); ?>">
                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>
                    
                    <button type="submit" name="criar_admin" class="btn">Criar Administrador</button>
                </form>
            </div>
            
            <div class="step-content <?php echo $etapa == 4 ? 'active' : ''; ?>">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h2 class="text-center">Instalação Concluída!</h2>
                <p class="text-center">O sistema foi instalado com sucesso. Você pode agora fazer login com o usuário administrador que você criou.</p>
                
                <div class="text-center" style="margin-top: 30px;">
                    <a href="login.php" class="btn">Ir para o Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>