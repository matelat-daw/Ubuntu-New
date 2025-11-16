# Sistema de Expiración de Carritos y Gestión de Stock

## 📋 Características Implementadas

### 1. Reserva de Stock en Carrito
✅ Al agregar productos al carrito, el stock se **reserva automáticamente**
✅ Stock reservado = Stock no disponible para otros compradores
✅ Stock disponible = `stock - reserved_stock`
✅ Al eliminar items del carrito, el stock se **libera automáticamente**

### 2. Expiración de Carritos
✅ Carritos expiran después de **10 días** desde su creación
✅ A los **7 días** (3 días antes de expirar): Email de advertencia
✅ A los **10 días**: Carrito se elimina y stock se libera automáticamente

### 3. Alertas de Stock Bajo
✅ Cada **48 horas** el sistema verifica productos con stock bajo
✅ Si `(stock - reserved_stock) < low_stock_threshold`: Envía email al vendedor
✅ No envía alertas duplicadas durante 7 días

## 🗄️ Cambios en Base de Datos

### Tabla `products`
```sql
ALTER TABLE products
    ADD COLUMN reserved_stock INT DEFAULT 0,
    ADD COLUMN low_stock_alert_sent BOOLEAN DEFAULT FALSE,
    ADD COLUMN last_stock_alert_date TIMESTAMP NULL;
```

### Tabla `carts`
```sql
ALTER TABLE carts
    ADD COLUMN expires_at TIMESTAMP NULL;
```

### Nueva Tabla: `cart_expiration_emails`
Registra emails enviados para evitar duplicados:
- warning_7days (a los 7 días)
- final_3days (nunca usado, para futuras mejoras)

### Nueva Tabla: `stock_alert_log`
Registra histórico de alertas de stock enviadas a vendedores.

## 🔧 Endpoints Modificados

### POST /api/controllers/cart/add.php
**Cambios:**
- Ahora usa transacciones para atomicidad
- Verifica `stock - reserved_stock` en lugar de solo `stock`
- Incrementa `reserved_stock` al agregar items
- Crea carritos con `expires_at = NOW() + 10 days`

### DELETE /api/controllers/cart/remove.php
**Cambios:**
- Libera `reserved_stock` al eliminar items
- Usa transacciones

### DELETE /api/controllers/cart/clear.php
**Cambios:**
- Libera todo el `reserved_stock` de todos los items
- Usa transacciones

## 🤖 Cron Job

### Archivo: `/var/www/html/api/cron/cart_stock_manager.php`

**Ejecución:** Cada 48 horas a las 2:00 AM

**Tareas:**
1. **Enviar emails de advertencia (7 días)**
   - Busca carritos que expiran en 3 días
   - Envía email con lista de productos y total
   - Registra en `cart_expiration_emails`

2. **Eliminar carritos expirados (10 días)**
   - Busca carritos con `expires_at < NOW()`
   - Libera `reserved_stock` de todos los items
   - Marca carrito como `abandoned`
   - Envía email de notificación

3. **Alertas de stock bajo**
   - Busca productos donde `(stock - reserved_stock) < low_stock_threshold`
   - Agrupa por vendedor
   - Envía UN email por vendedor con todos sus productos
   - No reenvía si ya se envió en los últimos 7 días

### Logs
Ubicación: `/var/www/html/api/logs/cron.log`

Ejemplo:
```
[2025-11-16 02:00:01] === Starting Cart & Stock Management Cron ===
[2025-11-16 02:00:02] Found 3 carts to send 7-day warning
[2025-11-16 02:00:05] Sent 7-day warning to user #12 (user@example.com)
[2025-11-16 02:00:10] Found 2 expired carts to delete
[2025-11-16 02:00:12] Deleted cart #45, released 3 items
[2025-11-16 02:00:15] Found 5 products with low stock
[2025-11-16 02:00:18] Sent low stock alert to seller #8 for 2 products
[2025-11-16 02:00:20] === Cron Job Completed Successfully ===
```

## 📧 Emails Enviados

### 1. Advertencia 7 días
**Asunto:** ⏰ Tu carrito expirará en 3 días
**Cuándo:** 7 días después de crear el carrito
**Contenido:**
- Lista de productos
- Cantidad y precio total
- Fecha de expiración
- Link para completar compra

### 2. Carrito expirado
**Asunto:** 🛒 Tu carrito ha expirado
**Cuándo:** Cuando se elimina el carrito (10 días)
**Contenido:**
- Notificación de eliminación
- Link para ver productos nuevamente

### 3. Stock bajo (vendedor)
**Asunto:** ⚠️ Alerta de Stock Bajo
**Cuándo:** Cada 48 horas si stock < umbral
**Contenido:**
- Lista de productos con stock bajo
- Stock disponible vs reservado
- Umbral configurado
- Link para gestionar productos

## 🚀 Instalación del Cron

### Opción 1: Crontab manual
```bash
# Editar crontab
sudo crontab -e

# Agregar línea (cada 48 horas a las 2 AM)
0 2 */2 * * /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php >> /var/www/html/api/logs/cron.log 2>&1
```

### Opción 2: Script de instalación
```bash
# Dar permisos de ejecución
chmod +x /var/www/html/api/cron/install_cron.sh

# Ejecutar
sudo /var/www/html/api/cron/install_cron.sh
```

## 🧪 Testing

### Probar cron manualmente
```bash
php /var/www/html/api/cron/cart_stock_manager.php
```

### Simular carrito próximo a expirar
```sql
-- Crear carrito que expira en 3 días
UPDATE carts 
SET expires_at = DATE_ADD(NOW(), INTERVAL 3 DAY)
WHERE id = 1;
```

### Simular carrito expirado
```sql
-- Crear carrito expirado
UPDATE carts 
SET expires_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE id = 1;
```

### Simular stock bajo
```sql
-- Producto con stock bajo
UPDATE products 
SET stock = 3, 
    reserved_stock = 0,
    low_stock_threshold = 5,
    low_stock_alert_sent = FALSE
WHERE id = 1;
```

## 📊 Consultas Útiles

### Ver carritos próximos a expirar
```sql
SELECT 
    c.id,
    u.email,
    c.expires_at,
    TIMESTAMPDIFF(DAY, NOW(), c.expires_at) as days_remaining,
    COUNT(ci.id) as items
FROM carts c
JOIN users u ON c.user_id = u.id
LEFT JOIN cart_items ci ON c.id = ci.cart_id
WHERE c.status = 'active' AND c.expires_at > NOW()
GROUP BY c.id
ORDER BY c.expires_at;
```

### Ver stock reservado por producto
```sql
SELECT 
    p.id,
    p.name,
    p.stock,
    p.reserved_stock,
    (p.stock - p.reserved_stock) as available,
    COUNT(ci.id) as carts_with_item
FROM products p
LEFT JOIN cart_items ci ON p.id = ci.product_id
LEFT JOIN carts c ON ci.cart_id = c.id AND c.status = 'active'
GROUP BY p.id
HAVING reserved_stock > 0;
```

### Ver productos con stock bajo
```sql
SELECT 
    p.id,
    p.name,
    p.sku,
    u.name as seller,
    p.stock,
    p.reserved_stock,
    (p.stock - p.reserved_stock) as available,
    p.low_stock_threshold,
    p.low_stock_alert_sent,
    p.last_stock_alert_date
FROM products p
JOIN users u ON p.seller_id = u.id
WHERE (p.stock - p.reserved_stock) < p.low_stock_threshold
ORDER BY available;
```

## ⚙️ Configuración

### Cambiar tiempo de expiración
Editar `/var/www/html/api/controllers/cart/add.php`:
```php
// Cambiar de 10 días a 14 días
$expiresAt = date('Y-m-d H:i:s', strtotime('+14 days'));
```

### Cambiar frecuencia de cron
```bash
# Cada 24 horas
0 2 * * * /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php

# Cada 12 horas
0 */12 * * * /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php

# Cada semana
0 2 * * 0 /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php
```

### Cambiar días antes de advertencia
Editar `/var/www/html/api/cron/cart_stock_manager.php`:
```php
// Línea 42: Cambiar de 3 a 5 días
AND c.expires_at BETWEEN NOW() + INTERVAL 5 DAY AND NOW() + INTERVAL 5 DAY + INTERVAL 1 HOUR
```

## 🔒 Seguridad

- ✅ Transacciones para evitar inconsistencias
- ✅ `FOR UPDATE` locks para prevenir race conditions
- ✅ Validación de ownership en todos los endpoints
- ✅ Log de todas las operaciones críticas
- ✅ No se envían emails duplicados

## 📈 Métricas

### Queries para reportes
```sql
-- Carritos abandonados por mes
SELECT 
    DATE_FORMAT(modification_date, '%Y-%m') as month,
    COUNT(*) as abandoned_carts,
    SUM((SELECT SUM(quantity * price) FROM cart_items WHERE cart_id = c.id)) as lost_revenue
FROM carts c
WHERE status = 'abandoned'
GROUP BY month
ORDER BY month DESC;

-- Productos más abandonados
SELECT 
    p.name,
    COUNT(*) as times_abandoned,
    SUM(ci.quantity) as total_quantity
FROM cart_items ci
JOIN carts c ON ci.cart_id = c.id
JOIN products p ON ci.product_id = p.id
WHERE c.status = 'abandoned'
GROUP BY p.id
ORDER BY times_abandoned DESC
LIMIT 20;
```
