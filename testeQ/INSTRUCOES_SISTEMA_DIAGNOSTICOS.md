# Sistema de Diagnósticos Gratuitos - Instruções de Uso

## Visão Geral
O sistema foi atualizado para oferecer diagnósticos gratuitos realizados por analistas especializados. Os clientes podem solicitar diagnósticos sem custo para identificar problemas em seus veículos.

## Como Funciona

### Para Clientes:

1. **Solicitar Diagnóstico:**
   - Acesse "Diagnósticos" no menu lateral
   - Clique em "Novo Diagnóstico"
   - Selecione o veículo
   - Escolha um analista especializado
   - Informe seu endereço completo
   - Descreva o problema (opcional - se não souber, deixe em branco)
   - Clique em "Solicitar Diagnóstico"

2. **Acompanhar Diagnósticos:**
   - Na página "Diagnósticos", veja todas as suas solicitações
   - Status possíveis:
     - **Pendente:** Aguardando análise do especialista
     - **Analisado:** Diagnóstico realizado
     - **Respondido:** Diagnóstico enviado

3. **Receber Diagnóstico:**
   - Quando o analista enviar o diagnóstico, você receberá uma notificação
   - O diagnóstico é **100% gratuito**
   - Não há cobrança pelo serviço de análise

### Para Analistas:

1. **Login:**
   - Usuários analistas criados automaticamente:
     - Carlos Silva: carlossilva@autoservice.com (senha: 123456)
     - Maria Santos: mariasantos@autoservice.com (senha: 123456)
     - João Oliveira: joãooliveira@autoservice.com (senha: 123456)

2. **Ver Solicitações:**
   - Acesse "Diagnósticos Clientes" no menu
   - Veja todas as solicitações enviadas para você
   - Informações incluem dados do cliente, veículo e problema relatado

3. **Fazer Diagnóstico:**
   - Clique em "Fazer Diagnóstico" na solicitação
   - Descreva o diagnóstico detalhado
   - Clique em "Enviar Diagnóstico"
   - **Não há cobrança** - o serviço é gratuito

### Para Administradores:

1. **Gerenciar Sistema:**
   - Acesse "Diagnósticos" no painel administrativo
   - Veja estatísticas completas do sistema
   - Monitore todos os diagnósticos e seus status
   - Visualize detalhes completos de qualquer diagnóstico

## Analistas Disponíveis

### Carlos Silva
- **Especialidade:** Diagnóstico Geral
- **Experiência:** 8 anos
- **Descrição:** Especialista em diagnóstico automotivo com ampla experiência

### Maria Santos
- **Especialidade:** Eletrônica Automotiva
- **Experiência:** 6 anos
- **Descrição:** Analista especializada em sistemas eletrônicos veiculares

### João Oliveira
- **Especialidade:** Motor e Transmissão
- **Experiência:** 10 anos
- **Descrição:** Especialista em diagnóstico de motores e sistemas de transmissão

## Notificações

- **Modal Automático:** Ao fazer login, um modal aparece automaticamente se houver notificações não lidas
- **Sino de Notificações:** Clique no sino no cabeçalho para ver notificações
- **Tipos de Notificações:**
  - Cliente recebe quando analista envia diagnóstico
  - Analista recebe quando cliente solicita diagnóstico

## Principais Mudanças

### ✅ **O que mudou:**
- **Mecânicos → Analistas:** Profissionais especializados em diagnóstico
- **Orçamentos → Diagnósticos Gratuitos:** Sem cobrança pelo serviço
- **Serviços → Análise:** Foco na identificação do problema
- **Revisão → Diagnóstico:** Terminologia mais adequada

### ✅ **O que permaneceu:**
- Sistema de notificações
- Interface intuitiva
- Controle administrativo
- Histórico completo

## Arquivos Atualizados

### Novos Arquivos:
- `atualizar_para_analistas.php` - Script de migração
- `INSTRUCOES_SISTEMA_DIAGNOSTICOS.md` - Instruções atualizadas

### Arquivos Modificados:
- `agendamento-novo.php` - Atualizado para diagnósticos
- `relatorios.php` - Removido sistema de orçamentos
- `mecanico-relatorios.php` - Convertido para analistas
- `processar-diagnostico.php` - Simplificado para diagnósticos gratuitos
- `admin-relatorios.php` - Atualizado para analistas
- `header.php` - Menu atualizado
- `admin-menu.php` - Links atualizados

### Tabelas Criadas/Atualizadas:
- `analistas` - Nova tabela com especialistas
- `relatorios_cliente` - Campo `mecanico_id` → `analista_id`
- `relatorios_mecanico` - Removidos campos de orçamento
- `usuarios` - Adicionado campo `analista_id`

## Credenciais de Acesso

### Analistas:
- **Carlos Silva:** carlossilva@autoservice.com (senha: 123456)
- **Maria Santos:** mariasantos@autoservice.com (senha: 123456)
- **João Oliveira:** joãooliveira@autoservice.com (senha: 123456)

## Observações Importantes

- **Serviço 100% Gratuito:** Não há cobrança pelos diagnósticos
- **Especialistas Qualificados:** Cada analista tem sua área de especialização
- **Sistema Simplificado:** Foco na identificação do problema, não na venda de serviços
- **Notificações em Tempo Real:** Cliente e analista são notificados automaticamente
- **Controle Administrativo:** Administradores podem monitorar todo o processo

O sistema agora oferece um serviço de diagnóstico automotivo gratuito e especializado!