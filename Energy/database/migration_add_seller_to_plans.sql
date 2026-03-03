-- Migración: Agregar vendedor a planes de energía
-- Fecha: 2026-02-25
-- Descripción: Vincula cada plan de energía a un vendedor específico

-- Agregar columna seller_id a energy_plans
ALTER TABLE energy_plans
ADD COLUMN seller_id INT DEFAULT NULL AFTER provider_id,
ADD FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;

-- Crear índice para optimizar consultas
CREATE INDEX idx_plans_seller ON energy_plans(seller_id);

-- Comentario: Cada plan ahora puede estar asociado a un vendedor específico
-- Cuando un cliente contrata un plan, el contrato se vinculará automáticamente
-- al vendedor que ofrece ese plan

