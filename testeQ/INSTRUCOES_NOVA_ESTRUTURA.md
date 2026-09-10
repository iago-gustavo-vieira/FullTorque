# 📱 Nova Estrutura Responsiva Global - FullTorque

## 🎯 Objetivo
Implementar um sistema responsivo global com header fixo e sidebar que funciona em todas as páginas do sistema.

## 🚀 Funcionalidades Implementadas

### ✅ Header Global
- **Logo da FullTorque** centralizada
- **Menu hamburguer** no lado direito (mobile)
- **Botão de notificações** com contador
- **Responsivo** para todos os dispositivos
- **Oculta automaticamente** quando sidebar abre

### ✅ Sidebar Global
- **Abre do lado esquerdo** quando menu hamburguer é clicado
- **Menu dinâmico** baseado no tipo de usuário (cliente/mecânico/admin)
- **Toggle de tema** (Itália/Alemanha)
- **Informações do usuário**
- **Fecha automaticamente** ao clicar em links ou overlay

### ✅ Sistema Responsivo
- **Mobile First** - otimizado para dispositivos móveis
- **Breakpoints** para tablet e desktop
- **Animações suaves** e transições
- **Acessibilidade** completa

## 📁 Arquivos Criados

### 1. `global-header.php`
Header global com sidebar integrado. Inclui:
- Estrutura HTML do header e sidebar
- Estilos CSS completos
- JavaScript para controle do menu
- Sistema de temas
- Modal de confirmação de saída

### 2. `global-styles.css`
Estilos globais responsivos:
- Reset CSS
- Variáveis CSS para temas
- Classes utilitárias
- Grid responsivo
- Componentes (botões, cards, formulários, tabelas)
- Animações

### 3. `exemplo-nova-estrutura.php`
Página de exemplo mostrando como usar o novo sistema.

## 🔧 Como Implementar em Páginas Existentes

### Método 1: Substituição Completa (Recomendado)

```php
<?php
require_once 'config.php';

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$titulo = "Nome da Página";
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
    <!-- Header global -->
    <?php include 'global-header.php'; ?>
    
    <!-- Conteúdo principal -->
    <div class="main-container">
        <!-- Seu conteúdo aqui -->
    </div>
</body>
</html>
```

### Método 2: Adaptação Gradual

Para páginas que já usam o `header.php` atual:

1. **Substitua** `<?php include 'header.php'; ?>` por `<?php include 'global-header.php'; ?>`
2. **Adicione** `<link rel="stylesheet" href="global-styles.css">` no `<head>`
3. **Envolva** o conteúdo em `<div class="main-container">`
4. **Remova** estilos CSS conflitantes

## 🎨 Classes CSS Disponíveis

### Layout
```css
.main-container    /* Container principal */
.card             /* Card com sombra */
.grid             /* Grid responsivo */
.grid-2           /* 2 colunas */
.grid-3           /* 3 colunas */
.grid-4           /* 4 colunas */
```

### Componentes
```css
.btn              /* Botão padrão */
.btn-secondary    /* Botão secundário */
.btn-outline      /* Botão outline */
.form-group       /* Grupo de formulário */
.table-container  /* Container de tabela */
.alert            /* Alerta */
.alert-success    /* Alerta de sucesso */
.alert-danger     /* Alerta de erro */
.alert-warning    /* Alerta de aviso */
.alert-info       /* Alerta de informação */
```

### Utilitários
```css
.text-center      /* Texto centralizado */
.text-right       /* Texto à direita */
.text-left        /* Texto à esquerda */
.d-flex           /* Display flex */
.d-grid           /* Display grid */
.d-none           /* Display none */
.justify-center   /* Justify center */
.justify-between  /* Justify between */
.align-center     /* Align center */
.gap-1, .gap-2    /* Gaps */
.mt-1, .mt-2      /* Margins top */
.mb-1, .mb-2      /* Margins bottom */
.p-1, .p-2        /* Padding */
```

### Animações
```css
.fade-in          /* Animação de entrada */
.slide-in         /* Animação de slide */
```

## 📱 Breakpoints Responsivos

```css
/* Mobile First */
/* Até 480px - Mobile pequeno */
/* 481px - 768px - Mobile/Tablet */
/* 769px - 1200px - Tablet/Desktop pequeno */
/* 1201px+ - Desktop */
```

## 🎯 Funcionalidades do Header/Sidebar

### JavaScript Disponível
```javascript
// Funções globais disponíveis
toggleTheme()        // Alternar tema
confirmarSaida()     // Modal de confirmação
fecharModalSair()    // Fechar modal
```

### Eventos Automáticos
- **Menu hamburguer** - abre/fecha sidebar
- **Overlay** - fecha sidebar ao clicar
- **Links de navegação** - fecham sidebar automaticamente
- **Redimensionamento** - fecha sidebar em desktop
- **Tema** - salva preferência no localStorage

## 🔄 Migração de Páginas Existentes

### Páginas Prioritárias para Migração:
1. `index.php` - Dashboard principal
2. `veiculos.php` - Gestão de veículos
3. `agendamentos.php` - Agendamentos
4. `relatorios.php` - Relatórios
5. `perfil.php` - Perfil do usuário

### Checklist de Migração:
- [ ] Substituir include do header
- [ ] Adicionar estilos globais
- [ ] Envolver conteúdo em main-container
- [ ] Testar responsividade
- [ ] Verificar funcionalidades
- [ ] Testar em diferentes dispositivos

## 🎨 Personalização de Temas

### Tema Padrão (Itália)
```css
--primary-color: #109349;    /* Verde */
--secondary-color: #DD0100;  /* Vermelho */
--tertiary-color: #f8f9fa;   /* Cinza claro */
```

### Tema Alemanha
```css
--primary-color: #FFCE00;    /* Amarelo */
--secondary-color: #DD0100;  /* Vermelho */
--tertiary-color: #000000;   /* Preto */
```

## 🚀 Próximos Passos

1. **Testar** o exemplo (`exemplo-nova-estrutura.php`)
2. **Migrar** páginas uma por vez
3. **Testar** em diferentes dispositivos
4. **Ajustar** estilos específicos se necessário
5. **Remover** arquivos antigos após migração completa

## 📞 Suporte

Para dúvidas ou problemas na implementação:
- Verificar console do navegador para erros JavaScript
- Testar em modo responsivo do navegador
- Verificar se todos os arquivos foram incluídos corretamente

---

**✅ Sistema implementado com sucesso!**
**📱 Totalmente responsivo e pronto para uso!**