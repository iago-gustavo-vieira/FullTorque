
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - <?php echo SISTEMA_NOME; ?></title>
     <link rel="icon" type="image/jpeg" href="icone.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo COR_PRIMARIA; ?>;
            --secondary-color: <?php echo COR_SECUNDARIA; ?>;
            --tertiary-color: <?php echo COR_TERCIARIA; ?>;
            --accent-color: <?php echo COR_DESTAQUE; ?>;
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
            background-color: var(--tertiary-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
            padding: 40px;
        }
        
        .error-icon {
            font-size: 5rem;
            color: var(--error-color);
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            margin-left: 10px;
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-exclamation-circle error-icon"></i>
        <h1>Acesso Negado</h1>
        <p>Você não tem permissão para acessar esta página. Por favor, entre em contato com o administrador se acredita que isso é um erro.</p>
        <div>
            <a href="index.php" class="btn">
                <i class="fas fa-home"></i> Voltar para o Dashboard
            </a>
            <a href="logout.php" class="btn btn-outline">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</body>
</html>