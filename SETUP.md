# Configuração do projeto

Este repositório não inclui credenciais reais nem dados de usuários. Antes de rodar o projeto:

## 1. Banco de dados

Copie o arquivo de exemplo e preencha com suas próprias credenciais:

```
cp testeQ/config.example.php testeQ/config.local.php   # para desenvolvimento local
cp testeQ/config.example.php testeQ/config.remote.php  # para produção
```

Edite o arquivo copiado e preencha `DB_HOST`, `DB_USER`, `DB_PASS` e `DB_NAME` com os dados reais do seu banco.
Esses arquivos (`config.local.php` e `config.remote.php`) estão no `.gitignore` e nunca devem ser commitados.

## 2. Estrutura do banco

Os arquivos `*.schema.sql` na pasta `testeQ/` contêm apenas a estrutura das tabelas (sem dados de usuários).
Importe-os no seu banco para criar as tabelas necessárias:

```
mysql -u seu_usuario -p seu_banco < testeQ/database.schema.sql
mysql -u seu_usuario -p seu_banco < testeQ/auto_center.schema.sql
mysql -u seu_usuario -p seu_banco < testeQ/auto_service.schema.sql
```

## 3. Pasta de uploads

As pastas `testeQ/uploads/` e `testeQ/uploadsprofile_photos/` não estão no repositório (continham fotos reais de usuários).
Crie-as manualmente antes de rodar o sistema:

```
mkdir -p testeQ/uploads/perfil testeQ/uploads/diagnosticos
```

## ⚠️ Nota de segurança

Se em algum momento uma credencial real (senha de banco, chave de API etc.) foi commitada por engano em um
repositório público, troque essa credencial imediatamente no painel do provedor — remover o arquivo depois
não invalida o que já ficou exposto no histórico do Git.
