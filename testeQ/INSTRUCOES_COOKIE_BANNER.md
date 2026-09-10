# 🍪 Sistema de Banner de Cookies - FullTorque

## 📋 Descrição
Sistema de consentimento de cookies que persiste em todas as páginas do site até que o usuário aceite ou recuse explicitamente.

## ✨ Características
- ✅ Banner aparece em **TODAS as páginas** até o usuário responder
- ✅ Não desaparece automaticamente
- ✅ Persiste por 1 ano após aceitar/recusar
- ✅ Compatível com LGPD
- ✅ Design responsivo (mobile e desktop)
- ✅ Suporta tema Itália e Alemanha
- ✅ Animações suaves

## 📁 Arquivos Criados

### 1. `cookie-consent.js`
Script JavaScript global que gerencia o consentimento de cookies.

### 2. `components/cookie-banner.php`
Componente PHP reutilizável com HTML, CSS e integração do script.

## 🚀 Como Implementar

### Opção 1: Incluir em Todas as Páginas Manualmente

Adicione antes do fechamento da tag `</body>` em cada página:

```php
<?php include 'components/cookie-banner.php'; ?>
```

### Opção 2: Incluir no Footer Global

Se você tem um arquivo `footer.php` usado em todas as páginas, adicione lá:

```php
<!-- No final do footer.php, antes de </body> -->
<?php include 'components/cookie-banner.php'; ?>
```

### Opção 3: Incluir no Header (para páginas internas)

No arquivo `header.php`, adicione antes do fechamento da tag `</body>` ou no final do arquivo:

```php
<!-- No final do header.php -->
<?php include 'components/cookie-banner.php'; ?>
```

## 📝 Exemplo de Implementação

### Para a página home.php (já implementado):
```php
<!-- Antes do fechamento </body> -->
<?php include 'components/cookie-banner.php'; ?>
</body>
</html>
```

### Para páginas internas (index.php, veiculos.php, etc.):
```php
<?php include 'header.php'; ?>

<!-- Conteúdo da página -->

<?php include 'components/cookie-banner.php'; ?>
</body>
</html>
```

## 🎨 Personalização

### Alterar Tempo de Expiração
No arquivo `cookie-consent.js`, linha 10:
```javascript
const COOKIE_EXPIRY_DAYS = 365; // Altere para o número de dias desejado
```

### Alterar Cores
No arquivo `components/cookie-banner.php`, seção `<style>`:
```css
.cookie-banner {
    background: linear-gradient(135deg, #DD0101 0%, #a01e28 100%);
    /* Altere as cores aqui */
}
```

### Alterar Texto
No arquivo `components/cookie-banner.php`, seção HTML:
```html
<p><strong>Este site utiliza cookies</strong> para melhorar sua experiência...</p>
```

## 🔧 Funções Disponíveis

### JavaScript Global
- `acceptCookies()` - Aceita os cookies
- `rejectCookies()` - Recusa os cookies
- `checkCookieConsent()` - Verifica se o usuário já respondeu

## 📱 Responsividade

O banner é totalmente responsivo:
- **Desktop**: Banner horizontal na parte inferior
- **Tablet**: Layout adaptado com espaçamento otimizado
- **Mobile**: Layout vertical com botões em coluna

## 🎯 Comportamento

1. **Primeira Visita**: Banner aparece após 0.5 segundos
2. **Aceitar**: Cookie salvo por 1 ano, banner desaparece
3. **Recusar**: Cookie salvo por 1 ano, banner desaparece
4. **Sem Resposta**: Banner continua aparecendo em todas as páginas
5. **Após Expiração**: Banner volta a aparecer após 1 ano

## ✅ Checklist de Implementação

- [ ] Arquivo `cookie-consent.js` criado na raiz
- [ ] Arquivo `components/cookie-banner.php` criado
- [ ] Banner incluído em `home.php`
- [ ] Banner incluído em `header.php` (páginas internas)
- [ ] Banner incluído em outras páginas públicas
- [ ] Testado em desktop
- [ ] Testado em mobile
- [ ] Testado tema Itália
- [ ] Testado tema Alemanha
- [ ] Verificado funcionamento dos botões
- [ ] Verificado persistência entre páginas

## 🐛 Solução de Problemas

### Banner não aparece
1. Verifique se o arquivo `cookie-consent.js` está na raiz
2. Verifique se o componente está incluído na página
3. Limpe os cookies do navegador
4. Verifique o console do navegador por erros

### Banner aparece mesmo após aceitar
1. Limpe os cookies do navegador
2. Verifique se o nome do cookie está correto
3. Verifique se o domínio está configurado corretamente

### Estilos não aplicados
1. Verifique se não há conflitos de CSS
2. Verifique se o z-index está correto (10000)
3. Limpe o cache do navegador

## 📞 Suporte

Para dúvidas ou problemas, verifique:
1. Console do navegador (F12)
2. Cookies do navegador (F12 > Application > Cookies)
3. Arquivo de log do servidor

## 🔄 Atualizações Futuras

Possíveis melhorias:
- [ ] Painel de configuração de cookies
- [ ] Cookies por categoria (essenciais, analytics, marketing)
- [ ] Integração com Google Analytics
- [ ] Relatório de consentimento
- [ ] Exportação de dados LGPD

---

**Desenvolvido para FullTorque** 🏎️
Versão 1.0 - 2024
