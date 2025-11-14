# Configuración del Servidor Web

## ✅ Servicios Configurados

### 1. Nginx (Servidor Web)
- **Estado:** Activo y funcionando
- **Versión:** nginx/1.24.0
- **Configuración:** `/etc/nginx/sites-available/default`
- **Backup:** `/etc/nginx/sites-available/default.backup`

### 2. PHP-FPM
- **Estado:** Activo y funcionando
- **Versión:** PHP 8.3.6
- **Socket:** `/run/php/php8.3-fpm.sock`
- **Configuración personalizada:** `/etc/php/8.3/fpm/conf.d/99-moodle.ini`

### 3. MySQL
- **Estado:** Activo y funcionando
- **Usuario:** root
- **Contraseña:** password123
- **Host:** localhost

## 📁 Configuración de Directorios

- **Raíz web:** `/var/www/html`
- **Propietario:** orion:orion
- **Permisos:** 755

## 🔧 Configuraciones Importantes

### Nginx
- Soporte para archivos PHP
- Límite de tamaño de archivo: 100M
- Timeout para scripts: 300 segundos
- Index por defecto: index.php, index.html

### PHP (optimizado para Moodle)
- Memory limit: 512M
- Upload max filesize: 100M
- Post max size: 100M
- Max execution time: 300 segundos
- Max input vars: 5000

### Extensiones PHP Instaladas
✅ mysqli - Conexión a MySQL
✅ mbstring - Manejo de strings multibyte
✅ curl - Cliente HTTP
✅ zip - Compresión de archivos
✅ xml - Procesamiento XML
✅ gd - Procesamiento de imágenes
✅ intl - Internacionalización
✅ soap - Servicios web SOAP

## 🌐 Acceso al Servidor

- **Localhost:** http://localhost
- **Red local:** http://192.168.1.39
- **Archivo de prueba PHP:** http://localhost/test-php.php

## 📝 Comandos Útiles

### Reiniciar servicios
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql
```

### Ver logs
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/php8.3-fpm.log
```

### Verificar estado de servicios
```bash
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mysql
```

## 🔐 Credenciales MySQL para Moodle

Cuando configures Moodle, usa estas credenciales:

- **Servidor:** localhost
- **Usuario:** root
- **Contraseña:** password123
- **Base de datos:** (crear una nueva, ej: moodle)

### Crear base de datos para Moodle
```bash
mysql -u root -ppassword123 -e "CREATE DATABASE moodle DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## ⚠️ Seguridad

**IMPORTANTE:** La contraseña 'password123' es solo para desarrollo. 
Para producción, usa una contraseña más segura:

```bash
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';"
```

## 📌 Notas

- VS Code ahora puede guardar archivos sin solicitar permisos de superusuario
- Todos los archivos en `/var/www/html` son propiedad del usuario `orion`
- La configuración está optimizada para Moodle pero funciona con cualquier aplicación PHP
