# Instruções para Aplicar Header/Footer Padrão nas Páginas Admin

## Páginas que precisam ser atualizadas:

1. admin-relatorios.php
2. admin-agendamentos.php
3. admin-servicos.php
4. admin-mecanicos.php
5. admin-promocoes.php
6. admin-pagamentos.php
7. admin-usuarios.php
8. admin-veiculos.php
9. admin-logs.php

## Como aplicar:

### 1. No início do arquivo (após o PHP de verificação):

**ANTES:**
```php
<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

// Código PHP...
?>
<!DOCTYPE html>
<html>
<head>
    <!-- head content -->
</head>
<body>
<?php require_once 'admin-menu.php'; ?>
<div class="content">
    <h1>Título</h1>
```

**DEPOIS:**
```php
<?php
require_once 'config.php';
verificarLogin();
verificarPermissao('admin');

// Código PHP...

require_once 'admin-header-helper.php';
renderAdminHeader('Título da Página', 'Subtítulo opcional');
?>

<!-- Seu conteúdo HTML aqui -->
```

### 2. No final do arquivo:

**ANTES:**
```php
</div>
</body>
</html>
```

**DEPOIS:**
```php
<?php
renderAdminFooter();
?>
```

### 3. Remover:
- Todo o `<!DOCTYPE html>` até `<body>`
- `<?php require_once 'admin-menu.php'; ?>`
- `<div class="content">` (o helper já cria)
- Tags de fechamento `</div>`, `</body>`, `</html>`

## Títulos sugeridos para cada página:

- **admin-relatorios.php**: 'Diagnósticos', 'Gerenciar diagnósticos dos clientes'
- **admin-agendamentos.php**: 'Agendamentos', 'Gerenciar agendamentos de serviços'
- **admin-servicos.php**: 'Serviços', 'Gerenciar serviços oferecidos'
- **admin-mecanicos.php**: 'Mecânicos', 'Gerenciar equipe de mecânicos'
- **admin-promocoes.php**: 'Promoções', 'Gerenciar promoções e ofertas'
- **admin-pagamentos.php**: 'Pagamentos', 'Gerenciar pagamentos e transações'
- **admin-usuarios.php**: 'Usuários', 'Gerenciar usuários do sistema'
- **admin-veiculos.php**: 'Veículos', 'Gerenciar veículos cadastrados'
- **admin-logs.php**: 'Logs do Sistema', 'Visualizar logs e atividades'

## Benefícios:

✅ Background padronizado com gradiente das bandeiras
✅ Dashboard header responsivo
✅ Suporte automático aos temas Itália e Alemanha
✅ Mobile-welcome-text em dispositivos móveis
✅ Código mais limpo e manutenível
