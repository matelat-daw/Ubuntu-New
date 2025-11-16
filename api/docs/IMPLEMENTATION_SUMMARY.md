# ✅ Sistema de Expiración de Carritos y Gestión de Stock - IMPLEMENTADO

## 📦 Lo que se ha implementado

### 1. Base de Datos
✅ Tabla `products`:
   - `reserved_stock` - Stock reservado en carritos
   - `low_stock_alert_sent` - Bandera de alerta enviada
   - `last_stock_alert_date` - Última fecha de alerta

✅ Tabla `carts`:
   - `expires_at` - Fecha de expiración (10 días desde creación)

✅ Nueva tabla `cart_expiration_emails`:
   - Registro de emails enviados (evita duplicados)

✅ Nueva tabla `stock_alert_log`:
   - Histórico de alertas de stock bajo

### 2. Endpoints Modificados

✅ **POST /api/controllers/cart/add.php**
   - Reserva stock automáticamente al agregar items
   - Verifica stock disponible = `stock - reserved_stock`
   - Crea carritos con expiración de 10 días
   - Usa transacciones para atomicidad

✅ **DELETE /api/controllers/cart/remove.php**
   - Libera stock automáticamente al eliminar items
   - Usa transacciones

✅ **DELETE /api/controllers/cart/clear.php**
   - Libera todo el stock reservado
   - Usa transacciones

### 3. Cron Job

✅ **Archivo:** `/var/www/html/api/cron/cart_stock_manager.php`
✅ **Ejecutable:** ✓
✅ **Testado:** ✓ Funciona correctamente
✅ **Frecuencia:** Cada 48 horas a las 2:00 AM

**Tareas que realiza:**

1️⃣ **Emails de advertencia (día 7)**
   - Busca carritos que expiran en 3 días
   - Envía email con lista de productos
   - Registra email enviado

2️⃣ **Eliminar carritos expirados (día 10)**
   - Busca carritos vencidos
   - Libera stock reservado
   - Marca carrito como `abandoned`
   - Envía email de notificación

3️⃣ **Alertas de stock bajo (cada 48h)**
   - Encuentra productos con stock < umbral
   - Agrupa por vendedor
   - Envía UN email por vendedor
   - No reenvía durante 7 días

### 4. Servicio de Email

✅ **Archivo:** `/var/www/html/api/classes/EmailService.php`
✅ Usa PHP `mail()` function
✅ Soporte para texto plano y HTML
✅ Logging de emails enviados

### 5. Scripts de Instalación

✅ **Migración SQL:** `/var/www/html/api/database/migrations/cart_expiration_stock.sql`
✅ **Script instalador:** `/var/www/html/api/cron/install_cron.sh`
✅ **Documentación:** `/var/www/html/api/docs/cart-expiration-system.md`

## 🚀 Instalación del Cron

Para activar el cron job automático:

```bash
# Opción 1: Script automático
bash /var/www/html/api/cron/install_cron.sh

# Opción 2: Manual
crontab -e
# Agregar:
0 2 */2 * * /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php >> /var/www/html/api/logs/cron.log 2>&1
```

## 🧪 Testing

### Probar manualmente
```bash
php /var/www/html/api/cron/cart_stock_manager.php
```

**Resultado esperado:**
```
=== Starting Cart & Stock Management Cron ===
Checking carts that will expire in 3 days...
Found 0 carts to send 7-day warning
Checking expired carts to delete...
Found 0 expired carts to delete
Checking low stock products...
Found X products with low stock
Sent low stock alert to seller #X
=== Cron Job Completed Successfully ===
```

### Simular escenarios

**Carrito próximo a expirar (7 días):**
```sql
UPDATE carts 
SET expires_at = DATE_ADD(NOW(), INTERVAL 3 DAY + INTERVAL 30 MINUTE)
WHERE id = 1;
```

**Carrito expirado:**
```sql
UPDATE carts 
SET expires_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE id = 1;
```

**Stock bajo:**
```sql
UPDATE products 
SET stock = 3, 
    reserved_stock = 0,
    low_stock_threshold = 5,
    low_stock_alert_sent = FALSE
WHERE id = 1;
```

## 📧 Emails que se Envían

### 1. Advertencia día 7
**Para:** Comprador  
**Asunto:** ⏰ Tu carrito expirará en 3 días  
**Cuándo:** 7 días después de crear el carrito  

### 2. Carrito expirado
**Para:** Comprador  
**Asunto:** 🛒 Tu carrito ha expirado  
**Cuándo:** Al eliminar el carrito (día 10)  

### 3. Stock bajo
**Para:** Vendedor  
**Asunto:** ⚠️ Alerta de Stock Bajo  
**Cuándo:** Cada 48h si stock disponible < umbral  
**Nota:** No reenvía durante 7 días

## 📊 Flujo Completo

```
DÍA 0: Usuario agrega producto al carrito
       → Stock se reserva automáticamente
       → Carrito expires_at = +10 días
       
DÍA 7: Cron ejecuta
       → Encuentra carrito expira en 3 días
       → Envía email de advertencia
       → Registra en cart_expiration_emails
       
DÍA 10: Cron ejecuta
        → Encuentra carrito expirado
        → Libera stock reservado
        → Marca carrito como 'abandoned'
        → Envía email de notificación
        
CADA 48H: Cron ejecuta
          → Busca productos con stock bajo
          → Agrupa por vendedor
          → Envía emails de alerta
```

## 🔒 Seguridad Implementada

✅ Transacciones SQL para operaciones críticas  
✅ `FOR UPDATE` locks para prevenir race conditions  
✅ Validación de ownership en todos los endpoints  
✅ No se envían emails duplicados  
✅ Logs de todas las operaciones  

## 📈 Consultas Útiles

### Ver stock reservado
```sql
SELECT 
    p.name,
    p.stock,
    p.reserved_stock,
    (p.stock - p.reserved_stock) as available
FROM products p
WHERE p.reserved_stock > 0;
```

### Ver carritos próximos a expirar
```sql
SELECT 
    c.id,
    u.email,
    c.expires_at,
    DATEDIFF(c.expires_at, NOW()) as days_left,
    COUNT(ci.id) as items
FROM carts c
JOIN users u ON c.user_id = u.id
LEFT JOIN cart_items ci ON c.id = ci.cart_id
WHERE c.status = 'active' AND c.expires_at > NOW()
GROUP BY c.id
ORDER BY c.expires_at;
```

### Histórico de alertas enviadas
```sql
SELECT 
    p.name,
    u.name as seller,
    sal.stock_level,
    sal.alert_sent_at
FROM stock_alert_log sal
JOIN products p ON sal.product_id = p.id
JOIN users u ON sal.seller_id = u.id
ORDER BY sal.alert_sent_at DESC
LIMIT 50;
```

## ⚙️ Configuración

### Cambiar tiempo de expiración
En `/var/www/html/api/controllers/cart/add.php` línea ~75:
```php
// Cambiar de 10 a 14 días
$expiresAt = date('Y-m-d H:i:s', strtotime('+14 days'));
```

Y en cron job línea ~45 cambiar:
```php
// De 3 días a 5 días antes
AND c.expires_at BETWEEN NOW() + INTERVAL 5 DAY AND NOW() + INTERVAL 5 DAY + INTERVAL 1 HOUR
```

### Cambiar frecuencia del cron
```bash
# Cada 24 horas
0 2 * * * /usr/bin/php ...

# Cada 6 horas
0 */6 * * * /usr/bin/php ...
```

## 🎯 Estado Actual

✅ **Migración aplicada:** Todas las tablas creadas  
✅ **Endpoints funcionando:** Add, Remove, Clear con reserva de stock  
✅ **Cron testado:** Ejecuta correctamente  
✅ **Email service:** Funcionando (envió email de prueba)  
✅ **Documentación:** Completa  

## 🚦 Próximos Pasos

Para activar en producción:

1. **Instalar el cron:**
   ```bash
   bash /var/www/html/api/cron/install_cron.sh
   ```

2. **Verificar instalación:**
   ```bash
   crontab -l | grep cart_stock_manager
   ```

3. **Monitorear logs:**
   ```bash
   tail -f /var/www/html/api/logs/cron.log
   ```

4. **Opcional - Configurar SMTP:**
   Actualmente usa `mail()`. Para producción considera usar SMTP real editando `EmailService.php`.

---

**Todo listo para usar! 🎉**
