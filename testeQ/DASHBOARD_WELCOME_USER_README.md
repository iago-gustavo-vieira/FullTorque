# Dashboard Welcome - Texto de Boas-vindas com Nome do Usuário

Sistema responsivo para exibir texto de boas-vindas personalizado no dashboard-welcome do FullTorque.

## 📱 Funcionalidades

- ✅ Texto de boas-vindas personalizado com nome do usuário
- ✅ Exibição apenas em modo responsivo (telas ≤ 768px)
- ✅ Posicionamento centralizado no topo do dashboard-welcome
- ✅ Cores específicas por tema:
  - **Tema Itália**: Texto verde (#109349) com fundo branco
  - **Tema Alemanha**: Texto amarelo (#EFC202) com fundo preto
- ✅ Integração automática com sessões PHP
- ✅ Fallbacks múltiplos para obter nome do usuário
- ✅ Responsivo e otimizado para mobile

## 📁 Arquivos Incluídos

1. **mobile-specific.css** - Estilos CSS atualizados
2. **dashboard-welcome-user.js** - JavaScript para gerenciar o texto
3. **dashboard-welcome-helper.php** - Helper PHP para integração
4. **dashboard-welcome-example.php** - Exemplo de uso completo

## 🚀 Como Usar

### Método 1: Integração Simples (Recomendado)

```php
<?php
// No início da sua página PHP
require_once 'dashboard-welcome-helper.php';
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Seus CSS existentes -->
    <link rel="stylesheet" href="mobile-specific.css">
    <link rel="stylesheet" href="dashboard-welcome-global.css">
</head>
<body>
    <!-- Seu dashboard-welcome existente -->
    <div class="dashboard-welcome">
        <!-- Conteúdo existente -->
    </div>
    
    <!-- JavaScript -->
    <script src="dashboard-welcome-user.js"></script>
    
    <!-- Definir nome do usuário -->
    <?php set_dashboard_welcome_user(); ?>
</body>
</html>
```

### Método 2: Integração Completa

```php
<?php
require_once 'dashboard-welcome-helper.php';

// Renderizar dashboard completo
render_dashboard_welcome($_SESSION['usuario_nome']);
?>
```

### Método 3: Apenas o Script de Nome

```php
<?php
// Se você já tem o dashboard-welcome, apenas adicione:
init_dashboard_welcome_user($_SESSION['usuario_nome']);
?>
```

## 🔧 Configuração

### Fontes de Nome do Usuário (em ordem de prioridade):

1. `$_SESSION['usuario_nome']`
2. `$_SESSION['nome']`
3. `$_SESSION['user_name']`
4. `$_SESSION['nome_usuario']`
5. Elemento `.user-name` no DOM
6. Meta tag `name="user-name"`
7. localStorage `userName`
8. sessionStorage `userName`
9. Fallback: "Usuário"

### Personalizar Nome Manualmente:

```javascript
// Via JavaScript
setUserName('João');

// Ou via PHP
set_dashboard_welcome_user('João Silva');
```

## 🎨 Personalização de Estilos

### Modificar Cores:

```css
/* Tema Itália */
.dashboard-welcome-text h3 {
    color: #109349; /* Verde */
    background: rgba(255, 255, 255, 0.95);
}

/* Tema Alemanha */
.theme-alemanha .dashboard-welcome-text h3 {
    color: #EFC202; /* Amarelo */
    background: rgba(0, 0, 0, 0.85);
}
```

### Modificar Posicionamento:

```css
.dashboard-welcome-text {
    top: 8px; /* Distância do topo */
    left: 50%; /* Centralizado */
    transform: translateX(-50%);
}
```

### Modificar Tamanho e Fonte:

```css
.dashboard-welcome-text h3 {
    font-size: 13px;
    font-weight: 600;
    padding: 6px 10px;
}
```

## 📱 Responsividade

O texto aparece apenas em:
- Telas com largura ≤ 768px
- Dispositivos móveis detectados
- Orientação portrait e landscape

## 🔍 Debug e Troubleshooting

### Ativar Debug:

```php
define('DEBUG', true);
debug_dashboard_welcome_user();
```

### Verificar se está funcionando:

```javascript
// No console do navegador
console.log(window.DashboardWelcome.getUserName());
```

### Problemas Comuns:

1. **Texto não aparece**: Verifique se está em tela ≤ 768px
2. **Nome errado**: Verifique as variáveis de sessão
3. **Estilo não aplicado**: Verifique se o CSS foi incluído
4. **JavaScript não funciona**: Verifique se o arquivo JS foi carregado

## 🔄 Atualizações Dinâmicas

### Atualizar nome em tempo real:

```javascript
// Quando o usuário alterar o perfil
window.DashboardWelcome.updateUserName('Novo Nome');
```

### Recriar elemento:

```javascript
// Se o dashboard for carregado via AJAX
window.DashboardWelcome.addWelcomeText();
```

## 🌐 Compatibilidade

- ✅ Chrome/Edge/Safari/Firefox
- ✅ iOS Safari
- ✅ Android Chrome
- ✅ Responsive design
- ✅ Touch devices
- ✅ Orientação landscape/portrait

## 📋 Checklist de Implementação

- [ ] Incluir `mobile-specific.css` atualizado
- [ ] Incluir `dashboard-welcome-user.js`
- [ ] Incluir `dashboard-welcome-helper.php`
- [ ] Definir variável de sessão com nome do usuário
- [ ] Testar em dispositivo móvel
- [ ] Verificar ambos os temas (Itália/Alemanha)
- [ ] Testar com nomes longos
- [ ] Verificar responsividade

## 🎯 Exemplo de Resultado

**Tema Itália (Mobile):**
```
┌─────────────────────────────┐
│    Bem-vindo, João!         │ ← Texto verde centralizado
├─────────────────────────────┤
│                             │
│    Dashboard Content        │
│                             │
└─────────────────────────────┘
```

**Tema Alemanha (Mobile):**
```
┌─────────────────────────────┐
│    Bem-vindo, João!         │ ← Texto amarelo centralizado
├─────────────────────────────┤
│                             │
│    Dashboard Content        │
│                             │
└─────────────────────────────┘
```

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique o console do navegador
2. Ative o modo debug
3. Teste o exemplo fornecido
4. Verifique as variáveis de sessão PHP

---

**FullTorque** - Sistema de Oficina Automotiva
*Onde a tradição italiana encontra a precisão alemã* 🇮🇹🇩🇪