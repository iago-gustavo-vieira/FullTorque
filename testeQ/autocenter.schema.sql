-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: auto_service
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agendamento_itens`
--

DROP TABLE IF EXISTS `agendamento_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamento_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agendamento_id` int NOT NULL,
  `servico_id` int NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `agendamento_id` (`agendamento_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `agendamento_itens_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agendamento_itens_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendamento_itens`
--

LOCK TABLES `agendamento_itens` WRITE;
/*!40000 ALTER TABLE `agendamento_itens` DISABLE KEYS */;
/*!40000 ALTER TABLE `agendamento_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agendamento_servicos`
--

DROP TABLE IF EXISTS `agendamento_servicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamento_servicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agendamento_id` int NOT NULL,
  `servico_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agendamento_id` (`agendamento_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `agendamento_servicos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agendamento_servicos_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendamento_servicos`
--

LOCK TABLES `agendamento_servicos` WRITE;
/*!40000 ALTER TABLE `agendamento_servicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `agendamento_servicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agendamentos`
--

DROP TABLE IF EXISTS `agendamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `data_agendamento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `status` enum('agendado','confirmado','em_andamento','concluido','cancelado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'agendado',
  `observacoes` text COLLATE utf8mb4_general_ci,
  `data_criacao` datetime NOT NULL,
  `mecanico_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `veiculo_id` (`veiculo_id`),
  CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendamentos`
--

LOCK TABLES `agendamentos` WRITE;
/*!40000 ALTER TABLE `agendamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `agendamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `analistas`
--

DROP TABLE IF EXISTS `analistas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analistas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especialidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `analistas`
--

LOCK TABLES `analistas` WRITE;
/*!40000 ALTER TABLE `analistas` DISABLE KEYS */;
/*!40000 ALTER TABLE `analistas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cartoes_usuario`
--

DROP TABLE IF EXISTS `cartoes_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cartoes_usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `numero` varchar(19) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validade` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `cartoes_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cartoes_usuario`
--

LOCK TABLES `cartoes_usuario` WRITE;
/*!40000 ALTER TABLE `cartoes_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `cartoes_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente_pontos`
--

DROP TABLE IF EXISTS `cliente_pontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente_pontos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `pontos_totais` int DEFAULT '0',
  `pontos_disponiveis` int DEFAULT '0',
  `pontos_utilizados` int DEFAULT '0',
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente_pontos`
--

LOCK TABLES `cliente_pontos` WRITE;
/*!40000 ALTER TABLE `cliente_pontos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente_pontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_pagamentos`
--

DROP TABLE IF EXISTS `config_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_pagamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chave_pix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taxa_cartao` decimal(5,2) DEFAULT '3.50',
  `dias_vencimento` int DEFAULT '7',
  `mensagem_cobranca` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_pagamentos`
--

LOCK TABLES `config_pagamentos` WRITE;
/*!40000 ALTER TABLE `config_pagamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `config_pagamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_pontos`
--

DROP TABLE IF EXISTS `historico_pontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_pontos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `agendamento_id` int DEFAULT NULL,
  `tipo` enum('ganho','resgate','expiracao') NOT NULL,
  `pontos` int NOT NULL,
  `descricao` text,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_pontos`
--

LOCK TABLES `historico_pontos` WRITE;
/*!40000 ALTER TABLE `historico_pontos` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_pontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `acao` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `data_hora` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=575 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mecanico_disponibilidade`
--

DROP TABLE IF EXISTS `mecanico_disponibilidade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mecanico_disponibilidade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mecanico_id` int NOT NULL,
  `dia_semana` tinyint NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_mecanico` (`mecanico_id`),
  KEY `idx_dia` (`dia_semana`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mecanico_disponibilidade`
--

LOCK TABLES `mecanico_disponibilidade` WRITE;
/*!40000 ALTER TABLE `mecanico_disponibilidade` DISABLE KEYS */;
/*!40000 ALTER TABLE `mecanico_disponibilidade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mecanicos`
--

DROP TABLE IF EXISTS `mecanicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mecanicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `especialidade` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `experiencia` int DEFAULT '0',
  `biografia` text COLLATE utf8mb4_general_ci,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mecanicos`
--

LOCK TABLES `mecanicos` WRITE;
/*!40000 ALTER TABLE `mecanicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `mecanicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metodos_pagamento`
--

DROP TABLE IF EXISTS `metodos_pagamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metodos_pagamento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo` enum('cartao_credito','cartao_debito','conta_bancaria','pix') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dados_criptografados` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  CONSTRAINT `metodos_pagamento_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metodos_pagamento`
--

LOCK TABLES `metodos_pagamento` WRITE;
/*!40000 ALTER TABLE `metodos_pagamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `metodos_pagamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacoes`
--

DROP TABLE IF EXISTS `notificacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes`
--

LOCK TABLES `notificacoes` WRITE;
/*!40000 ALTER TABLE `notificacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacoes_excluidas`
--

DROP TABLE IF EXISTS `notificacoes_excluidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes_excluidas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `notificacao_promocao_id` int NOT NULL,
  `data_exclusao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_exclusao` (`usuario_id`,`notificacao_promocao_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes_excluidas`
--

LOCK TABLES `notificacoes_excluidas` WRITE;
/*!40000 ALTER TABLE `notificacoes_excluidas` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacoes_excluidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacoes_lidas`
--

DROP TABLE IF EXISTS `notificacoes_lidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes_lidas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `notificacao_promocao_id` int NOT NULL,
  `data_leitura` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_leitura` (`usuario_id`,`notificacao_promocao_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes_lidas`
--

LOCK TABLES `notificacoes_lidas` WRITE;
/*!40000 ALTER TABLE `notificacoes_lidas` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacoes_lidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacoes_promocoes`
--

DROP TABLE IF EXISTS `notificacoes_promocoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes_promocoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `promocao_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `tipo` enum('email','whatsapp','sistema') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `enviado` tinyint(1) DEFAULT '0',
  `data_envio` timestamp NULL DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes_promocoes`
--

LOCK TABLES `notificacoes_promocoes` WRITE;
/*!40000 ALTER TABLE `notificacoes_promocoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacoes_promocoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordem_servico_itens`
--

DROP TABLE IF EXISTS `ordem_servico_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_servico_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordem_servico_id` int NOT NULL,
  `tipo` enum('servico','peca') COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  `valor_unitario` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ordem_servico_id` (`ordem_servico_id`),
  CONSTRAINT `ordem_servico_itens_ibfk_1` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordem_servico_itens`
--

LOCK TABLES `ordem_servico_itens` WRITE;
/*!40000 ALTER TABLE `ordem_servico_itens` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordem_servico_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordens_servico`
--

DROP TABLE IF EXISTS `ordens_servico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordens_servico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agendamento_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `responsavel_id` int DEFAULT NULL,
  `data_abertura` datetime NOT NULL,
  `data_conclusao` datetime DEFAULT NULL,
  `status` enum('aberta','em_andamento','aguardando_aprovacao','aguardando_pecas','concluida','cancelada') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aberta',
  `diagnostico` text COLLATE utf8mb4_general_ci,
  `observacoes` text COLLATE utf8mb4_general_ci,
  `valor_total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agendamento_id` (`agendamento_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `veiculo_id` (`veiculo_id`),
  KEY `responsavel_id` (`responsavel_id`),
  CONSTRAINT `ordens_servico_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordens_servico_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordens_servico_ibfk_3` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordens_servico_ibfk_4` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordens_servico`
--

LOCK TABLES `ordens_servico` WRITE;
/*!40000 ALTER TABLE `ordens_servico` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordens_servico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagamentos`
--

DROP TABLE IF EXISTS `pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `agendamento_id` int DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `metodo_pagamento` enum('dinheiro','cartao_credito','cartao_debito','pix','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pendente','processando','aprovado','recusado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `data_vencimento` date DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `link_pagamento` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_vencimento` (`data_vencimento`),
  CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagamentos`
--

LOCK TABLES `pagamentos` WRITE;
/*!40000 ALTER TABLE `pagamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programa_pontos`
--

DROP TABLE IF EXISTS `programa_pontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programa_pontos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pontos_por_real` decimal(5,2) DEFAULT '1.00',
  `valor_ponto` decimal(5,2) DEFAULT '0.10',
  `pontos_minimos_resgate` int DEFAULT '100',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programa_pontos`
--

LOCK TABLES `programa_pontos` WRITE;
/*!40000 ALTER TABLE `programa_pontos` DISABLE KEYS */;
/*!40000 ALTER TABLE `programa_pontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocoes`
--

DROP TABLE IF EXISTS `promocoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('desconto_percentual','desconto_fixo','servico_gratis','cupom_primeira_revisao') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `codigo_cupom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `limite_uso` int DEFAULT NULL,
  `usos_atual` int DEFAULT '0',
  `servicos_aplicaveis` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_cupom` (`codigo_cupom`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocoes`
--

LOCK TABLES `promocoes` WRITE;
/*!40000 ALTER TABLE `promocoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocoes_admin`
--

DROP TABLE IF EXISTS `promocoes_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocoes_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desconto_percentual` int DEFAULT NULL,
  `desconto_valor` decimal(10,2) DEFAULT NULL,
  `codigo_cupom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocoes_admin`
--

LOCK TABLES `promocoes_admin` WRITE;
/*!40000 ALTER TABLE `promocoes_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocoes_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocoes_uso`
--

DROP TABLE IF EXISTS `promocoes_uso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocoes_uso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `promocao_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `agendamento_id` int DEFAULT NULL,
  `valor_desconto` decimal(10,2) DEFAULT NULL,
  `data_uso` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocoes_uso`
--

LOCK TABLES `promocoes_uso` WRITE;
/*!40000 ALTER TABLE `promocoes_uso` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocoes_uso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relatorios_cliente`
--

DROP TABLE IF EXISTS `relatorios_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relatorios_cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `analista_id` int DEFAULT NULL,
  `descricao_problema` text COLLATE utf8mb4_unicode_ci,
  `urgencia` enum('baixa','media','alta') COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `status` enum('pendente','analisado','respondido','aceito','rejeitado','realizado','cancelado','solicitou_mudanca') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mecanico_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `veiculo_id` (`veiculo_id`),
  CONSTRAINT `relatorios_cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `relatorios_cliente_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relatorios_cliente`
--

LOCK TABLES `relatorios_cliente` WRITE;
/*!40000 ALTER TABLE `relatorios_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `relatorios_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relatorios_mecanico`
--

DROP TABLE IF EXISTS `relatorios_mecanico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relatorios_mecanico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `relatorio_cliente_id` int NOT NULL,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pendente','respondido') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_proposta` datetime DEFAULT NULL,
  `observacoes_data` text COLLATE utf8mb4_unicode_ci,
  `status_agendamento` enum('pendente','confirmado','rejeitado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_resposta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `relatorio_cliente_id` (`relatorio_cliente_id`),
  CONSTRAINT `relatorios_mecanico_ibfk_1` FOREIGN KEY (`relatorio_cliente_id`) REFERENCES `relatorios_cliente` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relatorios_mecanico`
--

LOCK TABLES `relatorios_mecanico` WRITE;
/*!40000 ALTER TABLE `relatorios_mecanico` DISABLE KEYS */;
/*!40000 ALTER TABLE `relatorios_mecanico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respostas_diagnostico`
--

DROP TABLE IF EXISTS `respostas_diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respostas_diagnostico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `diagnostico_id` int NOT NULL,
  `mecanico_id` int NOT NULL,
  `diagnostico_inicial` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pecas_necessarias` text COLLATE utf8mb4_unicode_ci,
  `tempo_estimado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custo_estimado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prioridade` enum('baixa','media','alta','critica') COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_resposta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `diagnostico_id` (`diagnostico_id`),
  KEY `mecanico_id` (`mecanico_id`),
  CONSTRAINT `respostas_diagnostico_ibfk_1` FOREIGN KEY (`diagnostico_id`) REFERENCES `relatorios_cliente` (`id`),
  CONSTRAINT `respostas_diagnostico_ibfk_2` FOREIGN KEY (`mecanico_id`) REFERENCES `mecanicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respostas_diagnostico`
--

LOCK TABLES `respostas_diagnostico` WRITE;
/*!40000 ALTER TABLE `respostas_diagnostico` DISABLE KEYS */;
/*!40000 ALTER TABLE `respostas_diagnostico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicos`
--

DROP TABLE IF EXISTS `servicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_general_ci NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `tempo_estimado` int NOT NULL COMMENT 'Tempo estimado em minutos',
  `categoria` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('ativo','inativo') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicos`
--

LOCK TABLES `servicos` WRITE;
/*!40000 ALTER TABLE `servicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitacoes_orcamento`
--

DROP TABLE IF EXISTS `solicitacoes_orcamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitacoes_orcamento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `diagnostico_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `mecanico_id` int DEFAULT NULL,
  `descricao_detalhada` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urgencia` enum('normal','urgente','emergencia') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `fotos` json DEFAULT NULL,
  `status` enum('pendente','em_analise','respondido') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_solicitacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `diagnostico_id` (`diagnostico_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `solicitacoes_orcamento_ibfk_1` FOREIGN KEY (`diagnostico_id`) REFERENCES `relatorios_cliente` (`id`),
  CONSTRAINT `solicitacoes_orcamento_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitacoes_orcamento`
--

LOCK TABLES `solicitacoes_orcamento` WRITE;
/*!40000 ALTER TABLE `solicitacoes_orcamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitacoes_orcamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_permissoes`
--

DROP TABLE IF EXISTS `usuario_permissoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_permissoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `permissao` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `usuario_permissoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_permissoes`
--

LOCK TABLES `usuario_permissoes` WRITE;
/*!40000 ALTER TABLE `usuario_permissoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_permissoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel_acesso` enum('admin','gerente','funcionario','cliente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cliente',
  `status` enum('ativo','inativo','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `data_cadastro` datetime NOT NULL,
  `ultimo_acesso` datetime DEFAULT NULL,
  `mecanico_id` int DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veiculos`
--

DROP TABLE IF EXISTS `veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `placa` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `marca` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `modelo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ano` int NOT NULL,
  `cor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `quilometragem` int DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veiculos`
--

LOCK TABLES `veiculos` WRITE;
/*!40000 ALTER TABLE `veiculos` DISABLE KEYS */;
/*!40000 ALTER TABLE `veiculos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-26 10:48:58