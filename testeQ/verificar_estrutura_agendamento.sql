-- Script para verificar e adicionar campos de agendamento na tabela relatorios_cliente

-- Adicionar campo data_agendamento se não existir
ALTER TABLE relatorios_cliente 
ADD COLUMN IF NOT EXISTS data_agendamento DATE NULL AFTER urgencia;

-- Adicionar campo hora_agendamento se não existir
ALTER TABLE relatorios_cliente 
ADD COLUMN IF NOT EXISTS hora_agendamento TIME NULL AFTER data_agendamento;

-- Criar índice para melhorar performance das consultas de horários ocupados
CREATE INDEX IF NOT EXISTS idx_agendamento 
ON relatorios_cliente(mecanico_id, data_agendamento, hora_agendamento);

-- Verificar estrutura da tabela
DESCRIBE relatorios_cliente;
