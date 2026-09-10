<?php
require_once 'config.php';

// Este arquivo é apenas para demonstração do sistema de cobranças
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo - Sistema de Cobranças</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="demo-cobrancas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="demo-header">
            <h1><i class="fas fa-credit-card"></i> Sistema de Cobranças Implementado!</h1>
            <p>Funcionalidades adicionadas com sucesso</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3>Geração de Links</h3>
                <p>Ao criar uma cobrança no admin, o sistema gera automaticamente um link de pagamento falso para simulação.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Notificações</h3>
                <p>O cliente recebe uma notificação automática quando uma nova cobrança é criada.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Múltiplas Formas de Pagamento</h3>
                <p>PIX, Boleto, Cartão de Crédito e Débito - todas simuladas para demonstração.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Destaque na Página</h3>
                <p>Cobranças pendentes aparecem em destaque quando o cliente acessa a área de pagamentos.</p>
            </div>
        </div>

        <div class="demo-instructions">
            <h2>Como Testar:</h2>
            <ol>
                <li>Acesse a área administrativa (admin-pagamentos.php)</li>
                <li>Clique em "+ Criar Cobrança"</li>
                <li>Preencha os dados e crie a cobrança</li>
                <li>O sistema gerará um link de pagamento automaticamente</li>
                <li>Uma notificação será enviada ao cliente</li>
                <li>O cliente verá a cobrança em destaque ao acessar pagamentos.php</li>
                <li>Clicando no link, será direcionado para a página de pagamento simulada</li>
            </ol>
        </div>

        <div class="demo-links">
            <a href="admin-pagamentos.php" class="btn btn-primary">
                <i class="fas fa-cog"></i> Área Administrativa
            </a>
            <a href="pagamentos.php" class="btn btn-success">
                <i class="fas fa-credit-card"></i> Área do Cliente
            </a>
            <a href="notificacoes.php" class="btn btn-info">
                <i class="fas fa-bell"></i> Notificações
            </a>
        </div>
    </div>

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .demo-header {
        text-align: center;
        color: white;
        margin-bottom: 40px;
    }

    .demo-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .demo-header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 3rem;
        color: #3498db;
        margin-bottom: 20px;
    }

    .feature-card h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
    }

    .demo-instructions {
        background: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .demo-instructions h2 {
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .demo-instructions ol {
        color: #555;
        line-height: 1.8;
    }

    .demo-instructions li {
        margin-bottom: 8px;
    }

    .demo-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 15px 25px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .btn-primary {
        background: #3498db;
        color: white;
    }

    .btn-success {
        background: #2ecc71;
        color: white;
    }

    .btn-info {
        background: #17a2b8;
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    @media (max-width: 768px) {
        .demo-header h1 {
            font-size: 2rem;
        }
        
        .demo-links {
            flex-direction: column;
            align-items: center;
        }
        
        .btn {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
    }
    </style>
</body>
</html>