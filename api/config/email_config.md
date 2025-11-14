# Configuración de Email para API Business

## Estado Actual

Sendmail está configurado pero tiene problemas de permisos persistentes. 

## Configuración Realizada

### 1. Sendmail

**Archivos configurados:**
- `/etc/mail/authinfo` - Credenciales Gmail
- `/etc/mail/sendmail.mc` - Configuración principal
- `/etc/php/8.3/fpm/php.ini` - sendmail_path descomentado
- `/etc/php/8.3/cli/php.ini` - sendmail_path descomentado

**Credenciales Gmail:**
- SMTP: smtp.gmail.com:587
- Usuario: matelat@gmail.com
- Password: cmpazrwvngjpmbhw (App Password)

## ⚠️ Problema Actual

Sendmail tiene errores de permisos en `/var/spool/mqueue`:
```
Permission denied: Cannot write ./df5ADKPRDZ021925 (bfcommit, uid=0, gid=126)
```

## ✅ Solución Recomendada: PHPMailer

Para un e-commerce profesional, se recomienda usar **PHPMailer** en lugar de sendmail nativo.

### Ventajas de PHPMailer:

1. ✅ Más confiable y estable
2. ✅ Mejor manejo de errores
3. ✅ Soporte completo de HTML/CSS
4. ✅ Adjuntos y attachments
5. ✅ Sin problemas de permisos
6. ✅ SMTP directo sin sendmail
7. ✅ Compatible con todos los proveedores (Gmail, SendGrid, Mailgun, etc.)

### Instalación de PHPMailer

```bash
cd /var/www/html/api
composer require phpmailer/phpmailer
```

### Ejemplo de Uso con PHPMailer

```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendEmail($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->SMTPDebug = 0;                      // Nivel de debug (0 = off, 2 = verbose)
        $mail->isSMTP();                           // Usar SMTP
        $mail->Host       = 'smtp.gmail.com';      // Servidor SMTP
        $mail->SMTPAuth   = true;                  // Habilitar autenticación
        $mail->Username   = 'matelat@gmail.com';   // Usuario SMTP
        $mail->Password   = 'cmpazrwvngjpmbhw';    // Password SMTP (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port       = 587;                   // Puerto TCP
        
        // Configuración del charset
        $mail->CharSet = 'UTF-8';
        
        // Remitente
        $mail->setFrom('matelat@gmail.com', 'Business E-commerce');
        
        // Destinatario
        $mail->addAddress($to);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email enviado correctamente'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error al enviar email: {$mail->ErrorInfo}"];
    }
}

// Ejemplo de uso
$result = sendEmail(
    'user@example.com',
    'Verifica tu cuenta',
    '<h1>Bienvenido</h1><p>Haz clic para verificar tu email</p>',
    'Bienvenido. Haz clic para verificar tu email'
);

if ($result['success']) {
    echo "✅ Email enviado!";
} else {
    echo "❌ Error: " . $result['message'];
}
?>
```

### Clase Email Helper Recomendada

```php
<?php
// /var/www/html/api/classes/EmailHelper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_user = 'matelat@gmail.com';
    private $smtp_pass = 'cmpazrwvngjpmbhw';
    private $from_email = 'matelat@gmail.com';
    private $from_name = 'Business E-commerce';
    
    public function sendVerificationEmail($to, $name, $verificationToken) {
        $verificationUrl = "https://localhost/api/verify-email.php?token=" . $verificationToken;
        
        $subject = "Verifica tu cuenta - Business E-commerce";
        $body = $this->getVerificationTemplate($name, $verificationUrl);
        
        return $this->send($to, $subject, $body);
    }
    
    public function sendPasswordReset($to, $name, $resetToken) {
        $resetUrl = "https://localhost/reset-password.php?token=" . $resetToken;
        
        $subject = "Restablece tu contraseña - Business E-commerce";
        $body = $this->getPasswordResetTemplate($name, $resetUrl);
        
        return $this->send($to, $subject, $body);
    }
    
    public function sendOrderConfirmation($to, $name, $orderNumber, $orderDetails) {
        $subject = "Confirmación de pedido #$orderNumber - Business E-commerce";
        $body = $this->getOrderConfirmationTemplate($name, $orderNumber, $orderDetails);
        
        return $this->send($to, $subject, $body);
    }
    
    private function send($to, $subject, $body, $altBody = '') {
        $mail = new PHPMailer(true);
        
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_user;
            $mail->Password   = $this->smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtp_port;
            $mail->CharSet    = 'UTF-8';
            
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);
            
            $mail->send();
            return ['success' => true, 'message' => 'Email enviado correctamente'];
            
        } catch (Exception $e) {
            error_log("Error sending email: " . $mail->ErrorInfo);
            return ['success' => false, 'message' => $mail->ErrorInfo];
        }
    }
    
    private function getVerificationTemplate($name, $verificationUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #28a745; color: white; 
                         text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Business E-commerce</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola, $name!</h2>
                    <p>Gracias por registrarte en Business E-commerce. Para activar tu cuenta, por favor verifica tu dirección de email haciendo clic en el botón de abajo:</p>
                    <p style='text-align: center;'>
                        <a href='$verificationUrl' class='button'>Verificar mi email</a>
                    </p>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all;'><a href='$verificationUrl'>$verificationUrl</a></p>
                    <p>Este enlace expirará en 24 horas.</p>
                    <p>Si no creaste esta cuenta, puedes ignorar este email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Business E-commerce. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getPasswordResetTemplate($name, $resetUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #dc3545; color: white; 
                         text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Restablecer Contraseña</h1>
                </div>
                <div class='content'>
                    <h2>Hola, $name</h2>
                    <p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón de abajo para crear una nueva contraseña:</p>
                    <p style='text-align: center;'>
                        <a href='$resetUrl' class='button'>Restablecer Contraseña</a>
                    </p>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all;'><a href='$resetUrl'>$resetUrl</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p><strong>Si no solicitaste restablecer tu contraseña, ignora este email.</strong></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Business E-commerce. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getOrderConfirmationTemplate($name, $orderNumber, $orderDetails) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Pedido Confirmado!</h1>
                </div>
                <div class='content'>
                    <h2>Hola, $name</h2>
                    <p>Tu pedido <strong>#$orderNumber</strong> ha sido confirmado exitosamente.</p>
                    $orderDetails
                    <p>Te notificaremos cuando tu pedido sea enviado.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Business E-commerce. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
```

## Próximos Pasos

1. Instalar PHPMailer con Composer
2. Crear la clase EmailHelper
3. Integrar en la API de registro de usuarios
4. Probar envío de emails de verificación
5. Configurar templates profesionales

## Alternativas a Gmail (Recomendadas para Producción)

- **SendGrid** - 100 emails/día gratis
- **Mailgun** - 5,000 emails/mes gratis  
- **Amazon SES** - Muy económico
- **Postmark** - Especializado en transaccionales
