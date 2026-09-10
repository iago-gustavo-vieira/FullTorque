# Dashboard Welcome Mobile - Texto de Boas-vindas Responsivo

## Descrição
Sistema de texto de boas-vindas responsivo para o dashboard-welcome que exibe o nome do usuário logado apenas em modo mobile (telas ≤ 768px).

## Características
- **Tema Itália**: Texto verde (#109349) com fundo branco semi-transparente
- **Tema Alemanha**: Texto amarelo (#EFC202) com fundo preto semi-transparente
- **Posicionamento**: Centralizado no topo do dashboard-welcome
- **Responsivo**: Aparece apenas em dispositivos móveis
- **Dinâmico**: Nome do usuário obtido automaticamente

## Arquivos Criados/Modificados

### 1. `dashboard-welcome-mobile.js`
Script JavaScript principal que:
- Detecta se está em modo mobile (≤ 768px)
- Obtém o nome do usuário de diferentes fontes
- Cria e posiciona o texto de boas-vindas
- Remove o texto em modo desktop
- Responde a mudanças de tema e redimensionamento

### 2. `mobile-specific.css` (modificado)
Adicionados estilos CSS para:
```css
.dashboard-welcome-text {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    z-index: 10;
    width: 90%;
}

.dashboard-welcome-text h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    padding: 8px 12px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.9);
    color: #109349; /* Verde para tema Itália */
    text-shadow: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
}

/* Tema Alemanha - texto amarelo */
.theme-alemanha .dashboard-welcome-text h3 {
    color: #EFC202;
    background: rgba(0, 0, 0, 0.8);
}
```

### 3. `dashboard-welcome-global.css` (modificado)
Adicionados estilos globais para garantir compatibilidade em todas as páginas.

## Como Implementar em Outras Páginas

### Método 1: Inclusão Direta
Adicione antes do fechamento do `</body>`:

```html
<!-- CSS para boas-vindas responsivo -->
<link rel="stylesheet" href="mobile-specific.css">
<link rel="stylesheet" href="dashboard-welcome-global.css">

<!-- Script para texto de boas-vindas responsivo -->
<script src="dashboard-welcome-mobile.js"></script>
<script>
// Definir o nome do usuário para o script de boas-vindas
if (typeof window.setUserName === 'function') {
    window.setUserName('<?php echo addslashes($_SESSION['usuario_nome']); ?>');
}
</script>
```

### Método 2: Via Header/Footer Global
Se você tem um sistema de header/footer global, adicione no footer:

```php
<!-- footer.php -->
<script src="dashboard-welcome-mobile.js"></script>
<script>
// Definir nome do usuário se estiver logado
<?php if (isset($_SESSION['usuario_nome'])): ?>
if (typeof window.setUserName === 'function') {
    window.setUserName('<?php echo addslashes($_SESSION['usuario_nome']); ?>');
}
<?php endif; ?>
</script>
```

### Método 3: Para Diferentes Tipos de Usuário
```php
<script>
// Definir nome baseado no tipo de usuário
<?php 
$nomeUsuario = '';
if (isset($_SESSION['usuario_nome'])) {
    $nomeUsuario = $_SESSION['usuario_nome'];
} elseif (isset($_SESSION['mecanico_nome'])) {
    $nomeUsuario = $_SESSION['mecanico_nome'];
} elseif (isset($_SESSION['analista_nome'])) {
    $nomeUsuario = $_SESSION['analista_nome'];
}
?>

if (typeof window.setUserName === 'function' && '<?php echo $nomeUsuario; ?>') {
    window.setUserName('<?php echo addslashes($nomeUsuario); ?>');
}
</script>
```

## Fontes de Nome do Usuário

O script tenta obter o nome do usuário na seguinte ordem:

1. **Elemento `.user-name`** na sidebar
2. **Variável global** `window.usuarioNome`
3. **SessionStorage** `usuarioNome`
4. **Fallback** para "Usuário"

## Personalização

### Alterar Cores
Modifique no `mobile-specific.css`:

```css
/* Tema personalizado */
.theme-custom .dashboard-welcome-text h3 {
    color: #sua-cor;
    background: rgba(0, 0, 0, 0.8);
}
```

### Alterar Posicionamento
```css
.dashboard-welcome-text {
    top: 20px; /* Mover mais para baixo */
    /* ou */
    bottom: 10px; /* Posicionar na parte inferior */
    top: auto;
}
```

### Alterar Tamanho da Fonte
```css
.dashboard-welcome-text h3 {
    font-size: 16px; /* Aumentar fonte */
    padding: 10px 15px; /* Ajustar padding */
}
```

## Compatibilidade

- ✅ **Mobile**: Funciona em telas ≤ 768px
- ✅ **Temas**: Suporta tema Itália e Alemanha
- ✅ **Responsivo**: Remove automaticamente em desktop
- ✅ **Dinâmico**: Atualiza com mudanças de tema
- ✅ **Performance**: Leve e otimizado

## Exemplo de Uso Completo

```php
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="mobile-specific.css">
    <link rel="stylesheet" href="dashboard-welcome-global.css">
</head>
<body>
    <!-- Seu dashboard-welcome existente -->
    <div class="dashboard-welcome">
        <!-- Conteúdo existente -->
    </div>
    
    <!-- Scripts no final -->
    <script src="dashboard-welcome-mobile.js"></script>
    <script>
    // Configurar nome do usuário
    if (typeof window.setUserName === 'function') {
        window.setUserName('<?php echo addslashes($_SESSION['usuario_nome']); ?>');
    }
    </script>
</body>
</html>
```

## Troubleshooting

### Texto não aparece
1. Verifique se está em modo mobile (≤ 768px)
2. Confirme se o elemento `.dashboard-welcome` existe
3. Verifique se o nome do usuário foi definido corretamente

### Cores não funcionam
1. Verifique se os temas estão aplicados corretamente
2. Confirme se o CSS foi carregado
3. Verifique a ordem de carregamento dos arquivos CSS

### Performance
O script é otimizado e só executa quando necessário, mas você pode desabilitar em produção se não precisar:

```javascript
// Desabilitar em produção
if (window.location.hostname !== 'localhost') {
    return;
}
```