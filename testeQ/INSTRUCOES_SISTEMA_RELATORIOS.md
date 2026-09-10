# Sistema de Relatórios - Instruções de Uso

## Visão Geral
O sistema de relatórios foi implementado para substituir o antigo sistema de agendamento de serviços. Agora os clientes enviam relatórios sobre problemas em seus veículos para os mecânicos, que fazem diagnósticos e enviam orçamentos.

## Como Funciona

### Para Clientes:

1. **Enviar Relatório:**
   - Acesse "Relatórios" no menu lateral
   - Clique em "Novo Relatório"
   - Selecione o veículo
   - Escolha um mecânico
   - Informe seu endereço completo
   - Descreva o problema (opcional - se não souber, deixe em branco para revisão geral)
   - Clique em "Enviar Relatório"

2. **Acompanhar Relatórios:**
   - Na página "Relatórios", veja todos os seus relatórios enviados
   - Status possíveis:
     - **Pendente:** Aguardando análise do mecânico
     - **Analisado:** Mecânico fez o diagnóstico
     - **Respondido:** Cliente respondeu ao orçamento

3. **Responder Orçamentos:**
   - Quando o mecânico enviar o diagnóstico, você receberá uma notificação
   - Veja os serviços necessários e valores
   - Clique em "Aceitar Orçamento" ou "Rejeitar"

### Para Mecânicos:

1. **Login:**
   - Usuários mecânicos foram criados automaticamente:
     - Samuel: samuel@autoservice.com (senha: 123456)
     - Roger: roger@autoservice.com (senha: 123456)
     - Anderson: anderson@autoservice.com (senha: 123456)

2. **Ver Relatórios:**
   - Acesse "Relatórios Clientes" no menu
   - Veja todos os relatórios enviados para você
   - Informações incluem dados do cliente, veículo e problema relatado

3. **Fazer Diagnóstico:**
   - Clique em "Fazer Diagnóstico" no relatório
   - Descreva o diagnóstico detalhado
   - Adicione os serviços necessários com preços
   - Clique em "Enviar Diagnóstico"

4. **Acompanhar Respostas:**
   - Veja se o cliente aceitou ou rejeitou o orçamento
   - Se aceito, entre em contato com o cliente

### Para Administradores:

1. **Gerenciar Sistema:**
   - Acesse "Relatórios" no painel administrativo
   - Veja estatísticas completas do sistema
   - Monitore todos os relatórios e seus status
   - Visualize detalhes completos de qualquer relatório

## Notificações

- **Modal Automático:** Ao fazer login, um modal aparece automaticamente se houver notificações não lidas
- **Sino de Notificações:** Clique no sino no cabeçalho para ver notificações
- **Tipos de Notificações:**
  - Cliente recebe quando mecânico envia diagnóstico
  - Mecânico recebe quando cliente envia relatório
  - Mecânico recebe quando cliente responde orçamento

## Arquivos Criados/Modificados

### Novos Arquivos:
- `criar_sistema_relatorios.php` - Script de configuração das tabelas
- `relatorios.php` - Página de relatórios para clientes
- `processar-orcamento.php` - Processa aceitação/rejeição de orçamentos
- `mecanico-relatorios.php` - Página de relatórios para mecânicos
- `processar-diagnostico.php` - Processa diagnósticos dos mecânicos
- `admin-relatorios.php` - Página administrativa de relatórios
- `verificar-notificacoes.php` - API para verificar notificações
- `marcar-notificacao-lida.php` - API para marcar notificações como lidas

### Arquivos Modificados:
- `agendamento-novo.php` - Convertido para sistema de relatórios
- `header.php` - Adicionado sistema de notificações e links do menu
- `admin-menu.php` - Adicionado link para relatórios

### Tabelas Criadas:
- `relatorios_cliente` - Armazena relatórios enviados pelos clientes
- `relatorios_mecanico` - Armazena diagnósticos e orçamentos dos mecânicos
- `notificacoes` - Sistema de notificações

## Próximos Passos

1. Teste o sistema com diferentes cenários
2. Ajuste as credenciais dos mecânicos se necessário
3. Configure envio de emails (opcional)
4. Personalize as notificações conforme necessário

## Observações Importantes

- O sistema mantém compatibilidade com o banco de dados existente
- Usuários mecânicos foram criados automaticamente
- O sistema de notificações funciona em tempo real
- Administradores têm controle total sobre todos os relatórios
- O sistema é responsivo e funciona em dispositivos móveis