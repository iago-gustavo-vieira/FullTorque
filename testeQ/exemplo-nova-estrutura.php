<?php
// Exemplo de como usar a nova estrutura responsiva global
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$titulo = "Dashboard - Exemplo";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - FullTorque</title>
    
    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Estilos globais -->
    <link rel="stylesheet" href="global-styles.css">
</head>
<body>
    <!-- Incluir header global -->
    <?php include 'global-header.php'; ?>
    
    <!-- Conteúdo principal -->
    <div class="main-container">
        <div class="card fade-in">
            <h1><i class="fas fa-tachometer-alt"></i> <?php echo $titulo; ?></h1>
            <p>Esta é uma página de exemplo usando a nova estrutura responsiva global.</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card fade-in">
                <h3><i class="fas fa-car"></i> Veículos</h3>
                <p>Gerencie seus veículos cadastrados</p>
                <a href="veiculos.php" class="btn">
                    <i class="fas fa-arrow-right"></i> Ver Veículos
                </a>
            </div>
            
            <div class="card fade-in">
                <h3><i class="fas fa-stethoscope"></i> Diagnósticos</h3>
                <p>Acompanhe seus diagnósticos</p>
                <a href="relatorios.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> Ver Diagnósticos
                </a>
            </div>
            
            <div class="card fade-in">
                <h3><i class="fas fa-calendar-alt"></i> Agendamentos</h3>
                <p>Seus próximos agendamentos</p>
                <a href="agendamentos.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> Ver Agenda
                </a>
            </div>
        </div>
        
        <div class="card fade-in">
            <h2>Exemplo de Formulário</h2>
            <form>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite seu nome">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mensagem">Mensagem:</label>
                    <textarea id="mensagem" name="mensagem" rows="4" placeholder="Digite sua mensagem"></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-undo"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card fade-in">
            <h2>Exemplo de Tabela</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>João Silva</td>
                            <td>joao@email.com</td>
                            <td><span class="badge badge-success">Ativo</span></td>
                            <td>
                                <button class="btn btn-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Maria Santos</td>
                            <td>maria@email.com</td>
                            <td><span class="badge badge-warning">Pendente</span></td>
                            <td>
                                <button class="btn btn-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="alert alert-info fade-in">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Informação:</strong> Esta é uma página de exemplo mostrando como usar a nova estrutura responsiva.
            </div>
        </div>
    </div>
    
    <style>
        /* Estilos específicos desta página */
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
    </style>
    
    <script>
        // Animação de entrada dos cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>