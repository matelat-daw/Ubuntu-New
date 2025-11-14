# Base de Datos Business - E-commerce

## Instalación

### 1. Ejecutar el script SQL

```bash
# Desde la terminal
mysql -u root -p < business.sql

# O desde MySQL
mysql -u root -p
source /var/www/html/api/database/business.sql
```

### 2. Habilitar el event scheduler

```sql
SET GLOBAL event_scheduler = ON;
```

Para que sea permanente, agregar en `/etc/mysql/my.cnf`:

```ini
[mysqld]
event_scheduler = ON
```

### 3. Crear usuario para la API

```sql
CREATE USER 'business_api'@'localhost' IDENTIFIED BY 'TuPasswordSeguro123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON business.* TO 'business_api'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Generar JWT Secret

```bash
# Generar un secret seguro de 64 bytes
openssl rand -base64 64

# Actualizar en la base de datos
mysql -u root -p business
UPDATE settings SET value = 'tu_jwt_secret_generado' WHERE `key` = 'jwt_secret';
```

## Estructura de la Base de Datos

### Tablas Principales

1. **users** - Usuarios del sistema
2. **addresses** - Direcciones de envío/facturación
3. **sessions** - Sesiones activas y tokens JWT
4. **products** - Catálogo de productos
5. **categories** - Categorías de productos
6. **carts** - Carritos de compra
7. **orders** - Órdenes de compra
8. **reviews** - Reseñas de productos
9. **audit_log** - Registro de auditoría

### Campos Importantes de Usuario

- `password`: Hash Argon2id (máxima seguridad)
- `email_hash`: SHA-256 del email para verificación
- `path`: Ruta a imagen de perfil (`assets/profiles/{id}/profile.webp`)
- `email_verified`: Verificación de email
- `role`: customer, admin, vendor, moderator
- `login_attempts`: Contador de intentos fallidos
- `locked_until`: Bloqueo temporal por seguridad

### Usuario Administrador por Defecto

- **Email**: admin@business.local
- **Password**: Admin@123
- ⚠️ **CAMBIAR INMEDIATAMENTE EN PRODUCCIÓN**

## Seguridad Implementada

### Hashing de Contraseñas

- Algoritmo: **Argon2id** (compatible con ASP.NET Core)
- Mismo nivel de seguridad que ASP.NET Identity
- Resistente a ataques de fuerza bruta y GPU

### JWT Tokens

- Tokens encriptados con algoritmo HS256 o RS256
- Refresh tokens para renovación segura
- Hash SHA-256 almacenado en base de datos
- Expiración configurable (default: 1 hora)

### Protección contra Ataques

- Rate limiting: máximo 5 intentos de login
- Bloqueo temporal: 15 minutos después de intentos fallidos
- Registro de auditoría completo
- Sesiones con revocación manual

## Mantenimiento

### Procedimientos Almacenados

```sql
-- Limpiar sesiones expiradas
CALL clean_expired_sessions();

-- Limpiar carritos abandonados
CALL clean_abandoned_carts();
```

### Eventos Automáticos

- Limpieza de sesiones: cada 1 hora
- Limpieza de carritos: cada 1 día

### Consultas Útiles

```sql
-- Ver usuarios activos
SELECT * FROM user_summary WHERE active = 1;

-- Ver sesiones activas
SELECT s.*, u.email 
FROM sessions s 
JOIN users u ON s.user_id = u.id 
WHERE s.expires_at > NOW() AND s.revoked = 0;

-- Ver órdenes pendientes
SELECT * FROM orders WHERE status = 'pending' ORDER BY creation_date DESC;

-- Ver productos con bajo stock
SELECT * FROM products WHERE stock < low_stock_threshold AND active = 1;

-- Auditoría de último día
SELECT * FROM audit_log WHERE creation_date > DATE_SUB(NOW(), INTERVAL 1 DAY);
```

## Estructura de Directorios para Assets

```
/var/www/html/api/assets/
├── profiles/           # Imágenes de perfil de usuario
│   ├── 1/
│   │   └── profile.webp
│   ├── 2/
│   │   └── profile.webp
│   └── ...
├── products/           # Imágenes de productos
│   ├── 1/
│   │   ├── 1.webp     # Imagen principal
│   │   ├── 2.webp     # Imagen adicional
│   │   └── ...
│   └── ...
└── categories/         # Imágenes de categorías
    ├── 1.webp
    ├── 2.webp
    └── ...
```

## Backup y Restauración

### Backup Completo

```bash
mysqldump -u root -p business > backup_business_$(date +%Y%m%d).sql
```

### Backup Solo Estructura

```bash
mysqldump -u root -p --no-data business > structure_business.sql
```

### Restaurar

```bash
mysql -u root -p business < backup_business_20251113.sql
```

## Índices y Optimización

Todos los campos frecuentemente consultados tienen índices:

- Búsquedas por email (UNIQUE)
- Filtros por estado (active, status)
- Búsquedas de productos (slug, sku)
- Relaciones (foreign keys con índices)
- Auditoría (dates, actions)

## Próximos Pasos

1. ✅ Crear la base de datos
2. ⏭️ Implementar la API REST en PHP
3. ⏭️ Configurar autenticación JWT
4. ⏭️ Implementar endpoints CRUD
5. ⏭️ Agregar validaciones y sanitización
6. ⏭️ Implementar upload de imágenes
7. ⏭️ Configurar CORS y rate limiting
