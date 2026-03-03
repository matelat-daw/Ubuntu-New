-- Script para insertar un contrato de ejemplo
-- Este script asume que existe un usuario cliente (id >= 2)
-- Contrato: Usuario contrató el Plan Verde de Naturgy

-- Verificar que exista al menos un usuario cliente (además del admin)
-- Si no existe, crear uno de ejemplo
INSERT INTO users (username, email, password, first_name, last_name, is_active)
SELECT 'cliente_demo', 'cliente@demo.com', '$2y$12$EhxewlfrYNk8diFqxy/a/uBjfP9J895TkkW3pXCeqIDaXLxYJlPF6', 'Juan', 'Pérez', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE id > 1 LIMIT 1);

-- Asignar rol de usuario al cliente demo
INSERT INTO user_roles (user_id, role_id)
SELECT LAST_INSERT_ID(), 3
WHERE LAST_INSERT_ID() > 0
  AND NOT EXISTS (SELECT 1 FROM user_roles WHERE user_id = LAST_INSERT_ID() AND role_id = 3);

-- Insertar contrato del Plan Verde de Naturgy (plan_id = 2)
-- Nota: El Plan Verde de Naturgy según energy_schema.sql tiene: 
--       provider_id=1 (Iberdrola) con nombre 'Plan Verde'
-- Si necesitas Naturgy (provider_id=3), usar el Plan Eco (plan_id = 5)
INSERT INTO contracts (client_id, seller_id, plan_id, start_date, end_date, status, total_amount, commission_amount, notes)
VALUES (
    -- Usar el segundo usuario de la tabla (primer usuario no admin)
    (SELECT id FROM users WHERE id > 1 ORDER BY id LIMIT 1),
    NULL, -- Sin vendedor específico
    5, -- Plan Eco de Naturgy (100% renovable)
    CURDATE(), -- Fecha de inicio: hoy
    DATE_ADD(CURDATE(), INTERVAL 24 MONTH), -- 24 meses de duración
    'active', -- Estado activo
    3240.00, -- Total: 24 meses * 135€ mensuales aproximado
    0.00, -- Sin comisión
    'Contrato del Plan Eco de Naturgy - Energía 100% renovable'
);

-- Mostrar el contrato creado
SELECT 
    c.id as contract_id,
    u.username,
    u.email,
    p.name as plan_name,
    pr.name as provider_name,
    c.start_date,
    c.end_date,
    c.status
FROM contracts c
JOIN users u ON c.client_id = u.id
JOIN energy_plans p ON c.plan_id = p.id
JOIN energy_providers pr ON p.provider_id = pr.id
WHERE c.id = LAST_INSERT_ID();
