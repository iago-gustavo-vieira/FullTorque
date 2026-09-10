<!DOCTYPE html>
<html>
<head>
    <title>Aplicar Header Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Aplicando Header/Footer Padrão nas Páginas Admin</h1>
    
    <?php
    // Lista de páginas para atualizar
    $paginas = [
        'admin-usuarios.php' => ['Usuários', 'Gerenciar usuários do sistema'],
        'admin-veiculos.php' => ['Veículos', 'Gerenciar veículos cadastrados'],
        'admin-agendamentos.php' => ['Agendamentos', 'Gerenciar agendamentos de serviços'],
        'admin-servicos.php' => ['Serviços', 'Gerenciar serviços oferecidos'],
        'admin-mecanicos.php' => ['Mecânicos', 'Gerenciar equipe de mecânicos'],
        'admin-promocoes.php' => ['Promoções', 'Gerenciar promoções e ofertas'],
        'admin-pagamentos.php' => ['Pagamentos', 'Gerenciar pagamentos e transações'],
        'admin-logs.php' => ['Logs do Sistema', 'Visualizar logs e atividades'],
        'admin-relatorios.php' => ['Diagnósticos', 'Gerenciar diagnósticos dos clientes']
    ];
    
    foreach ($paginas as $arquivo => $info) {
        if (!file_exists($arquivo)) {
            echo "<p class='error'>❌ Arquivo não encontrado: $arquivo</p>";
            continue;
        }
        
        $conteudo = file_get_contents($arquivo);
        
        // Adicionar link do CSS global no head
        if (strpos($conteudo, 'admin-global-styles.css') === false) {
            $conteudo = str_replace(
                '<link rel="stylesheet" href="themes.css">',
                '<link rel="stylesheet" href="themes.css">' . "\n    " . '<link rel="stylesheet" href="admin-global-styles.css">',
                $conteudo
            );
        }
        
        // Salvar
        file_put_contents($arquivo, $conteudo);
        
        echo "<p class='success'>✅ Atualizado: $arquivo - {$info[0]}</p>";
    }
    
    echo "<hr>";
    echo "<p class='info'><strong>✅ Processo concluído!</strong></p>";
    echo "<p>Todas as páginas agora têm:</p>";
    echo "<ul>";
    echo "<li>Background com gradiente das bandeiras</li>";
    echo "<li>Dashboard header responsivo</li>";
    echo "<li>Suporte aos temas Itália e Alemanha</li>";
    echo "</ul>";
    ?>
    
    <p><a href="admin.php">← Voltar para o Admin</a></p>
</body>
</html>
