# Sistema Multi-Vendedor - Flujo de Compra

## 🏗️ Arquitectura

### Estructura de Base de Datos

1. **`seller_payment_methods`** - Métodos de pago que acepta cada vendedor
2. **`order_groups`** - Agrupa múltiples órdenes de una compra
3. **`orders`** - Una orden por vendedor (incluye `seller_id` y `order_group_id`)
4. **`order_items`** - Items de cada orden
5. **`payment_transactions`** - Transacciones de pago individuales por orden

## 🔄 Flujo Completo de Compra

### 1. Comprador agrega productos al carrito
```
Productos de diferentes vendedores → Un solo carrito
- Guitarra (Vendedor A)
- Teclado (Vendedor B)  
- Amplificador (Vendedor A)
```

### 2. Preview del Carrito (antes del checkout)
**Endpoint:** `GET /api/controllers/cart/preview.php`

**Respuesta:**
```json
{
  "seller_orders": [
    {
      "seller_id": 2,
      "seller_name": "Music Store Pro",
      "payment_methods": ["stripe", "paypal"],
      "items": [
        {"product_name": "Guitarra", "price": 500, "quantity": 1},
        {"product_name": "Amplificador", "price": 300, "quantity": 1}
      ],
      "subtotal": 800
    },
    {
      "seller_id": 5,
      "seller_name": "Tech Sound",
      "payment_methods": ["mercadopago", "transferencia"],
      "items": [
        {"product_name": "Teclado", "price": 400, "quantity": 1}
      ],
      "subtotal": 400
    }
  ],
  "summary": {
    "total_sellers": 2,
    "grand_total": 1200
  }
}
```

### 3. Checkout - Crear Órdenes
**Endpoint:** `POST /api/controllers/cart/checkout.php`

**Input:**
```json
{
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "notes": "Entregar en horario de tarde"
}
```

**Proceso:**
1. ✅ Valida stock de todos los productos
2. ✅ Verifica que todos los vendedores tengan métodos de pago
3. ✅ Crea un `order_group` con número único
4. ✅ Crea UNA orden por cada vendedor
5. ✅ Asigna automáticamente el primer método de pago disponible del vendedor
6. ✅ Calcula comisión de plataforma (5%) por orden
7. ✅ Crea transacciones de pago pendientes
8. ✅ Reduce stock de productos
9. ✅ Vacía el carrito

**Respuesta:**
```json
{
  "order_group_id": 1,
  "group_number": "GRP-6548A2C1F",
  "total_amount": 1200,
  "orders": [
    {
      "order_id": 101,
      "order_number": "ORD-6548A2D5E",
      "seller_id": 2,
      "amount": 800,
      "payment_method": "stripe",
      "items_count": 2
    },
    {
      "order_id": 102,
      "order_number": "ORD-6548A2E3A",
      "seller_id": 5,
      "amount": 400,
      "payment_method": "mercadopago",
      "items_count": 1
    }
  ],
  "total_orders": 2,
  "next_step": "payment"
}
```

### 4. Procesar Pagos (Backend)
El comprador ve UNA pantalla de pago, pero internamente:

- **Orden 101** (€800) → Se procesa con Stripe (método del Vendedor A)
- **Orden 102** (€400) → Se procesa con MercadoPago (método del Vendedor B)

**Cálculo de Comisiones:**
```
Orden 101: €800
  - Comisión plataforma (5%): €40
  - Vendedor recibe: €760

Orden 102: €400
  - Comisión plataforma (5%): €20
  - Vendedor recibe: €380
```

### 5. Ver Estado del Grupo de Órdenes
**Endpoint:** `GET /api/controllers/orders/group.php?group_id=1`

**Respuesta:**
```json
{
  "group": {
    "id": 1,
    "group_number": "GRP-6548A2C1F",
    "total_amount": 1200,
    "status": "partial"
  },
  "orders": [
    {
      "order_number": "ORD-6548A2D5E",
      "seller": {"name": "Music Store Pro"},
      "amount": 800,
      "payment_status": "paid",
      "payment_method": "stripe"
    },
    {
      "order_number": "ORD-6548A2E3A",
      "seller": {"name": "Tech Sound"},
      "amount": 400,
      "payment_status": "pending",
      "payment_method": "mercadopago"
    }
  ],
  "summary": {
    "paid_orders": 1,
    "pending_orders": 1
  }
}
```

## 🎯 Ventajas del Sistema

### Para el Comprador (Transparencia Total)
✅ Ve un solo carrito  
✅ Un solo proceso de checkout  
✅ No necesita saber que hay múltiples vendedores  
✅ Recibe un número de grupo único para seguimiento  

### Para los Vendedores
✅ Cada uno recibe su propia orden  
✅ Usa su propio método de pago preferido  
✅ Recibe el dinero directamente (menos comisión)  
✅ Gestiona sus órdenes independientemente  

### Para la Plataforma
✅ Comisión automática del 5% por orden  
✅ Trazabilidad completa de pagos  
✅ Informes separados por vendedor  
✅ Control de stock centralizado  

## 📊 Gestión de Métodos de Pago

### Vendedor configura sus métodos
**Endpoint:** `POST /api/controllers/seller/payment-methods.php`

```json
{
  "payment_method": "stripe",
  "is_active": true,
  "config": {
    "account_id": "acct_xxxxx",
    "api_key": "sk_live_xxxxx"
  }
}
```

### Ver métodos disponibles
**Endpoint:** `GET /api/controllers/seller/payment-methods.php`

```json
{
  "payment_methods": [
    {"payment_method": "stripe", "is_active": true},
    {"payment_method": "paypal", "is_active": false}
  ],
  "available_methods": ["stripe", "paypal", "mercadopago", "transferencia", "efectivo"]
}
```

## 🔐 Seguridad

- ✅ Todos los endpoints requieren autenticación
- ✅ Transacciones de base de datos para consistencia
- ✅ Validación de stock antes de confirmar
- ✅ No se puede comprar productos propios
- ✅ Bloqueo de filas durante checkout (FOR UPDATE)

## 📈 Estados

### Estados de Order Group
- `pending` - Esperando pago
- `partial` - Algunos pagos completados
- `completed` - Todos los pagos completados
- `cancelled` - Cancelado

### Estados de Payment Transaction
- `pending` - Esperando procesamiento
- `processing` - En proceso
- `completed` - Pago exitoso
- `failed` - Pago fallido
- `refunded` - Reembolsado

## 🚀 Próximos Pasos

Para implementar el procesamiento de pagos real:

1. Integrar pasarelas de pago (Stripe, PayPal, MercadoPago)
2. Crear webhooks para notificaciones de pago
3. Implementar sistema de reembolsos
4. Agregar notificaciones por email
5. Panel de administración para gestionar órdenes
